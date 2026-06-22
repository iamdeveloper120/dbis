<?php

namespace App\Controllers\AppConfiguration;

use App\Controllers\AdminController;

class ModuleSettingsController extends AdminController
{
    private const IMAGE_TYPES_AVAILABLE = ['jpg', 'jpeg', 'png', 'webp'];
    private const VIDEO_TYPES_AVAILABLE = ['mp4', 'webm', 'mov'];
    private const DEFAULT_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'webp'];
    private const DEFAULT_VIDEO_TYPES = ['mp4', 'webm', 'mov'];
    private const DEFAULT_IMAGE_MAX_SIZE_MB = 5;
    private const DEFAULT_VIDEO_MAX_SIZE_MB = 25;
    private const DEFAULT_IMAGE_MAX_COUNT = 5;
    private const DEFAULT_VIDEO_MAX_COUNT = 5;
    private const IMAGE_MAX_SIZE_LIMIT_MB = 20;
    private const VIDEO_MAX_SIZE_LIMIT_MB = 500;
    private const IMAGE_MAX_COUNT_LIMIT = 100;
    private const VIDEO_MAX_COUNT_LIMIT = 100;

    public function index()
    {
        $this->page_title = 'Module Settings';
        return view('AppConfiguration/module_settings', [
            'page_title' => $this->page_title,
            'settings' => $this->loadCurrentMandMediaSettings(),
            'imageTypeOptions' => self::IMAGE_TYPES_AVAILABLE,
            'videoTypeOptions' => self::VIDEO_TYPES_AVAILABLE,
        ]);
    }

    public function save()
    {
        $imageTypes = $this->sanitizePostedTypes(
            $this->request->getPost('image_types'),
            self::IMAGE_TYPES_AVAILABLE
        );
        $videoTypes = $this->sanitizePostedTypes(
            $this->request->getPost('video_types'),
            self::VIDEO_TYPES_AVAILABLE
        );

        $imageMaxSizeMb = $this->parsePostedPositiveInteger($this->request->getPost('image_max_size_mb'));
        $videoMaxSizeMb = $this->parsePostedPositiveInteger($this->request->getPost('video_max_size_mb'));
        $imageMaxCount = $this->parsePostedPositiveInteger($this->request->getPost('image_max_count'));
        $videoMaxCount = $this->parsePostedPositiveInteger($this->request->getPost('video_max_count'));

        $validationErrors = [];
        if (count($imageTypes) === 0) {
            $validationErrors['image_types'] = 'Select at least one allowed image type.';
        }
        if (count($videoTypes) === 0) {
            $validationErrors['video_types'] = 'Select at least one allowed video type.';
        }
        if ($imageMaxSizeMb === null || $imageMaxSizeMb < 1 || $imageMaxSizeMb > self::IMAGE_MAX_SIZE_LIMIT_MB) {
            $validationErrors['image_max_size_mb'] = 'Max image size must be between 1 and ' . self::IMAGE_MAX_SIZE_LIMIT_MB . ' MB.';
        }
        if ($videoMaxSizeMb === null || $videoMaxSizeMb < 1 || $videoMaxSizeMb > self::VIDEO_MAX_SIZE_LIMIT_MB) {
            $validationErrors['video_max_size_mb'] = 'Max video size must be between 1 and ' . self::VIDEO_MAX_SIZE_LIMIT_MB . ' MB.';
        }
        if ($imageMaxCount === null || $imageMaxCount < 1 || $imageMaxCount > self::IMAGE_MAX_COUNT_LIMIT) {
            $validationErrors['image_max_count'] = 'Max image files must be between 1 and ' . self::IMAGE_MAX_COUNT_LIMIT . '.';
        }
        if ($videoMaxCount === null || $videoMaxCount < 1 || $videoMaxCount > self::VIDEO_MAX_COUNT_LIMIT) {
            $validationErrors['video_max_count'] = 'Max video files must be between 1 and ' . self::VIDEO_MAX_COUNT_LIMIT . '.';
        }

        if (!empty($validationErrors)) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                implode('<br>', array_values($validationErrors)),
                $validationErrors,
                []
            );
            return $this->response->setJSON($response);
        }

        setting('ClientMandReinforcer.imageAllowedTypes', json_encode($imageTypes));
        setting('ClientMandReinforcer.videoAllowedTypes', json_encode($videoTypes));
        setting('ClientMandReinforcer.imageMaxSizeMb', (string) $imageMaxSizeMb);
        setting('ClientMandReinforcer.videoMaxSizeMb', (string) $videoMaxSizeMb);
        setting('ClientMandReinforcer.imageMaxCount', (string) $imageMaxCount);
        setting('ClientMandReinforcer.videoMaxCount', (string) $videoMaxCount);

        $response = $this->getResponseObject(
            'success',
            'Module Settings',
            'Module settings updated successfully.',
            [],
            []
        );

        return $this->response->setJSON($response);
    }

    private function loadCurrentMandMediaSettings(): array
    {
        $imageTypes = $this->sanitizeStoredTypes(
            setting('ClientMandReinforcer.imageAllowedTypes'),
            self::IMAGE_TYPES_AVAILABLE,
            self::DEFAULT_IMAGE_TYPES
        );
        $videoTypes = $this->sanitizeStoredTypes(
            setting('ClientMandReinforcer.videoAllowedTypes'),
            self::VIDEO_TYPES_AVAILABLE,
            self::DEFAULT_VIDEO_TYPES
        );

        $imageMaxSizeMb = (int) (setting('ClientMandReinforcer.imageMaxSizeMb') ?? self::DEFAULT_IMAGE_MAX_SIZE_MB);
        $videoMaxSizeMb = (int) (setting('ClientMandReinforcer.videoMaxSizeMb') ?? self::DEFAULT_VIDEO_MAX_SIZE_MB);
        $imageMaxCount = (int) (setting('ClientMandReinforcer.imageMaxCount') ?? self::DEFAULT_IMAGE_MAX_COUNT);
        $videoMaxCount = (int) (setting('ClientMandReinforcer.videoMaxCount') ?? self::DEFAULT_VIDEO_MAX_COUNT);

        if ($imageMaxSizeMb < 1 || $imageMaxSizeMb > self::IMAGE_MAX_SIZE_LIMIT_MB) {
            $imageMaxSizeMb = self::DEFAULT_IMAGE_MAX_SIZE_MB;
        }
        if ($videoMaxSizeMb < 1 || $videoMaxSizeMb > self::VIDEO_MAX_SIZE_LIMIT_MB) {
            $videoMaxSizeMb = self::DEFAULT_VIDEO_MAX_SIZE_MB;
        }
        if ($imageMaxCount < 1 || $imageMaxCount > self::IMAGE_MAX_COUNT_LIMIT) {
            $imageMaxCount = self::DEFAULT_IMAGE_MAX_COUNT;
        }
        if ($videoMaxCount < 1 || $videoMaxCount > self::VIDEO_MAX_COUNT_LIMIT) {
            $videoMaxCount = self::DEFAULT_VIDEO_MAX_COUNT;
        }

        return [
            'image_types' => $imageTypes,
            'video_types' => $videoTypes,
            'image_max_size_mb' => $imageMaxSizeMb,
            'video_max_size_mb' => $videoMaxSizeMb,
            'image_max_count' => $imageMaxCount,
            'video_max_count' => $videoMaxCount,
            'image_max_size_limit_mb' => self::IMAGE_MAX_SIZE_LIMIT_MB,
            'video_max_size_limit_mb' => self::VIDEO_MAX_SIZE_LIMIT_MB,
            'image_max_count_limit' => self::IMAGE_MAX_COUNT_LIMIT,
            'video_max_count_limit' => self::VIDEO_MAX_COUNT_LIMIT,
        ];
    }

    private function sanitizeStoredTypes($input, array $allowed, array $default): array
    {
        $normalized = [];

        if (is_string($input)) {
            $trimmed = trim($input);
            if ($trimmed !== '') {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                } else {
                    $input = explode(',', $trimmed);
                }
            } else {
                $input = [];
            }
        }

        if (!is_array($input)) {
            $input = [];
        }

        foreach ($input as $item) {
            $ext = strtolower(trim((string) $item));
            if ($ext === '' || !in_array($ext, $allowed, true)) {
                continue;
            }
            if (!in_array($ext, $normalized, true)) {
                $normalized[] = $ext;
            }
        }

        if (!empty($normalized)) {
            return $normalized;
        }

        $fallback = [];
        foreach ($default as $item) {
            $ext = strtolower(trim((string) $item));
            if ($ext === '' || !in_array($ext, $allowed, true)) {
                continue;
            }
            if (!in_array($ext, $fallback, true)) {
                $fallback[] = $ext;
            }
        }

        return $fallback;
    }

    private function sanitizePostedTypes($input, array $allowed): array
    {
        if (!is_array($input)) {
            return [];
        }

        $normalized = [];
        foreach ($input as $item) {
            $ext = strtolower(trim((string) $item));
            if ($ext === '' || !in_array($ext, $allowed, true)) {
                continue;
            }
            if (!in_array($ext, $normalized, true)) {
                $normalized[] = $ext;
            }
        }

        return $normalized;
    }

    private function parsePostedPositiveInteger($input): ?int
    {
        if ($input === null) {
            return null;
        }

        $value = trim((string) $input);
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }
}
