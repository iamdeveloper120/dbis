<?php

namespace App\Controllers\AppConfiguration;

use App\Controllers\AdminController;
use App\Services\AppConfiguration\PermissionSyncService;
use Throwable;

class PermissionSyncController extends AdminController
{
    public function index()
    {
        if (!$this->isSuperadmin()) {
            return redirect()->to('/access-denied');
        }

        $data = [
            'page_title' => 'Permission Sync',
        ];

        return view('AppConfiguration/PermissionSync/index', $data);
    }

    public function sync()
    {
        $user = auth()->user();
        if (!$this->isSuperadmin()) {
            $response = $this->getResponseObject(
                'error',
                'Access Denied',
                'Only Super Administrator can run permission sync.',
                [],
                []
            );

            return $this->response->setStatusCode(403)->setJSON($response);
        }

        try {
            $service = new PermissionSyncService();
            $result = $service->sync((int) $user->id, (string) ($user->username ?? ''));

            $status = $result['status'] === 'synced' ? 'success' : 'info';
            $statusText = $result['status'] === 'synced' ? 'Permission Sync Completed' : 'Permission Sync Skipped';

            $response = $this->getResponseObject(
                $status,
                $statusText,
                (string) $result['message'],
                [],
                $result
            );

            return $this->response->setJSON($response);
        } catch (Throwable $e) {
            log_message('error', 'Permission sync failed: {message}', ['message' => $e->getMessage()]);

            $response = $this->getResponseObject(
                'error',
                'Sync Failed',
                'Permission sync failed. Please contact system administrator.',
                [],
                []
            );

            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    private function isSuperadmin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->inGroup('superadmin');
    }
}
