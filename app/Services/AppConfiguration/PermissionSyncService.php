<?php

namespace App\Services\AppConfiguration;

use Config\AuthGroups;

class PermissionSyncService
{
    private const META_CLASS = 'PermissionSync';

    /**
     * Syncs auth groups and permissions from config to settings table.
     * Existing matrix assignments are preserved; missing default matrix entries are merged.
     *
     * @return array<string, mixed>
     */
    public function sync(?int $userId = null, ?string $username = null): array
    {
        $config = new AuthGroups();

        $configGroups = $this->normalizeArray($config->groups ?? []);
        $configPermissions = $this->normalizeArray($config->permissions ?? []);
        $configMatrix = $this->normalizeArray($config->matrix ?? []);

        $storedGroups = $this->normalizeArray(setting('AuthGroups.groups') ?? []);
        $storedPermissions = $this->normalizeArray(setting('AuthGroups.permissions') ?? []);
        $storedMatrix = $this->normalizeArray(setting('AuthGroups.matrix') ?? []);
        $targetMatrix = $this->buildTargetMatrix($storedMatrix, $configMatrix, $configGroups);

        $payload = [
            'groups' => $configGroups,
            'permissions' => $configPermissions,
            'matrix' => $targetMatrix,
        ];
        $payloadHash = $this->buildPayloadHash($payload);

        $hasChanges = $storedGroups !== $configGroups
            || $storedPermissions !== $configPermissions
            || $storedMatrix !== $targetMatrix;

        if (!$hasChanges) {
            $this->setMeta('last_hash', $payloadHash);
            $this->setMeta('last_status', 'skipped');
            $this->setMeta('last_message', 'No changes detected. Permissions are already in sync and matrix assignments were preserved.');
            $this->setMeta('last_attempt_at', date('Y-m-d H:i:s'));
            $this->setMeta('last_attempt_by_user_id', $userId);
            $this->setMeta('last_attempt_by_username', $username ?? '');

            return [
                'status' => 'skipped',
                'message' => 'No changes detected. Permissions are already in sync and matrix assignments were preserved.',
                'hash' => $payloadHash,
                'counts' => [
                    'groups' => count($configGroups),
                    'permissions' => count($configPermissions),
                    'matrix_groups' => count($targetMatrix),
                ],
                'meta' => $this->getStatus(),
            ];
        }

        setting('AuthGroups.groups', $configGroups);
        setting('AuthGroups.permissions', $configPermissions);
        setting('AuthGroups.matrix', $targetMatrix);

        $now = date('Y-m-d H:i:s');
        $this->setMeta('last_hash', $payloadHash);
        $this->setMeta('last_status', 'synced');
        $this->setMeta('last_message', 'Permissions synced successfully. Existing matrix assignments were preserved.');
        $this->setMeta('last_synced_at', $now);
        $this->setMeta('last_synced_by_user_id', $userId);
        $this->setMeta('last_synced_by_username', $username ?? '');
        $this->setMeta('last_attempt_at', $now);
        $this->setMeta('last_attempt_by_user_id', $userId);
        $this->setMeta('last_attempt_by_username', $username ?? '');

        return [
            'status' => 'synced',
            'message' => 'Permissions synced successfully. Existing matrix assignments were preserved.',
            'hash' => $payloadHash,
            'counts' => [
                'groups' => count($configGroups),
                'permissions' => count($configPermissions),
                'matrix_groups' => count($targetMatrix),
            ],
            'meta' => $this->getStatus(),
        ];
    }

    /**
     * Returns latest sync metadata.
     *
     * @return array<string, mixed>
     */
    public function getStatus(): array
    {
        return [
            'last_hash' => (string) (setting(self::META_CLASS . '.last_hash') ?? ''),
            'last_status' => (string) (setting(self::META_CLASS . '.last_status') ?? 'never'),
            'last_message' => (string) (setting(self::META_CLASS . '.last_message') ?? ''),
            'last_synced_at' => (string) (setting(self::META_CLASS . '.last_synced_at') ?? ''),
            'last_synced_by_user_id' => setting(self::META_CLASS . '.last_synced_by_user_id'),
            'last_synced_by_username' => (string) (setting(self::META_CLASS . '.last_synced_by_username') ?? ''),
            'last_attempt_at' => (string) (setting(self::META_CLASS . '.last_attempt_at') ?? ''),
            'last_attempt_by_user_id' => setting(self::META_CLASS . '.last_attempt_by_user_id'),
            'last_attempt_by_username' => (string) (setting(self::META_CLASS . '.last_attempt_by_username') ?? ''),
        ];
    }

    /**
     * @param mixed $value
     * @return array<mixed>
     */
    private function normalizeArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function buildPayloadHash(array $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            $encoded = serialize($payload);
        }

        return hash('sha256', $encoded);
    }

    /**
     * Preserves current matrix assignments and only merges missing defaults from AuthGroups config.
     */
    private function buildTargetMatrix(array $storedMatrix, array $configMatrix, array $configGroups): array
    {
        $targetMatrix = $storedMatrix;

        foreach ($configGroups as $groupAlias => $_groupData) {
            if (!is_string($groupAlias)) {
                continue;
            }

            if (!isset($targetMatrix[$groupAlias]) || !is_array($targetMatrix[$groupAlias])) {
                $targetMatrix[$groupAlias] = [];
            }

            $defaultGroupMatrix = $this->normalizeArray($configMatrix[$groupAlias] ?? []);
            foreach ($defaultGroupMatrix as $permissionKey) {
                if (!in_array($permissionKey, $targetMatrix[$groupAlias], true)) {
                    $targetMatrix[$groupAlias][] = $permissionKey;
                }
            }
        }

        return $targetMatrix;
    }

    /**
     * @param mixed $value
     */
    private function setMeta(string $property, $value): void
    {
        setting(self::META_CLASS . '.' . $property, $value);
    }
}
