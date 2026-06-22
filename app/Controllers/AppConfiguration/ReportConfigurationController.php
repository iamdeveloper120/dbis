<?php

namespace App\Controllers\AppConfiguration;

use App\Controllers\AdminController;

class ReportConfigurationController extends AdminController
{
    private const SETTING_MAP = [
        'header_line_1' => 'Report.headerLine1',
        'header_line_2' => 'Report.headerLine2',
        'header_line_3' => 'Report.headerLine3',
        'header_line_4' => 'Report.headerLine4',
        'header_center_caption' => 'Report.headerCenterCaption',
        'phone' => 'Report.phone',
        'website' => 'Report.website',
        'location_line' => 'Report.locationLine',
        'footer_company' => 'Report.footerCompany',
        'footer_address_line_1' => 'Report.footerAddressLine1',
        'footer_address_line_2' => 'Report.footerAddressLine2',
        'logo_path' => 'Report.logoPath',
        'progress_image_max_size_mb' => 'Report.progressImageMaxSizeMb',
        'progress_image_max_count' => 'Report.progressImageMaxCount',
    ];

    public function index()
    {
        $this->page_title = 'Report Settings';

        return view('AppConfiguration/report_settings', [
            'page_title' => $this->page_title,
            'settings' => $this->getReportSettings(),
        ]);
    }

    public function save()
    {
        $rules = [
            'header_line_1' => 'permit_empty|string|max_length[255]',
            'header_line_2' => 'permit_empty|string|max_length[255]',
            'header_line_3' => 'permit_empty|string|max_length[255]',
            'header_line_4' => 'permit_empty|string|max_length[255]',
            'header_center_caption' => 'permit_empty|string|max_length[255]',
            'phone' => 'permit_empty|string|max_length[100]',
            'website' => 'permit_empty|string|max_length[255]',
            'location_line' => 'permit_empty|string|max_length[255]',
            'footer_company' => 'permit_empty|string|max_length[255]',
            'footer_address_line_1' => 'permit_empty|string|max_length[255]',
            'footer_address_line_2' => 'permit_empty|string|max_length[255]',
            'logo_path' => 'permit_empty|string|max_length[255]',
            'progress_image_max_size_mb' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[10]',
            'progress_image_max_count' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[20]',
        ];

        $data = [];
        foreach (self::SETTING_MAP as $field => $settingKey) {
            $posted = $this->request->getPost($field);
            if ($posted === null) {
                $data[$field] = (string) (setting($settingKey) ?? '');
            } else {
                $data[$field] = trim((string) $posted);
            }
        }

        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $allowedExt = ['png', 'jpg', 'jpeg', 'webp'];
            $ext = strtolower((string) $logoFile->getExtension());

            if (!in_array($ext, $allowedExt, true)) {
                $response = $this->getResponseObject(
                    'error',
                    'Validation Error',
                    'Logo must be an image file: png, jpg, jpeg, or webp.',
                    [],
                    ''
                );
                return $this->response->setJSON($response);
            }

            if ($logoFile->getSizeByUnit('kb') > 2048) {
                $response = $this->getResponseObject(
                    'error',
                    'Validation Error',
                    'Logo file size must be 2MB or less.',
                    [],
                    ''
                );
                return $this->response->setJSON($response);
            }

            $targetDir = WRITEPATH . 'uploads/report-branding/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            $newName = 'report_logo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $logoFile->move($targetDir, $newName, true);
            $data['logo_path'] = 'uploads/report-branding/' . $newName;
        }

        if (!$this->validateData($data, $rules)) {
            $response = $this->getResponseObject(
                'error',
                'Validation Error',
                $this->validator->listErrors('custom_list'),
                [],
                ''
            );
            return $this->response->setJSON($response);
        }

        foreach (self::SETTING_MAP as $inputKey => $settingKey) {
            setting($settingKey, $data[$inputKey]);
        }

        $response = $this->getResponseObject(
            'success',
            '',
            'Report settings updated successfully',
            [],
            ''
        );
        return $this->response->setJSON($response);
    }

    public function logo()
    {
        $relativePath = (string) (setting('Report.logoPath') ?? '');
        if ($relativePath === '') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Logo not configured.');
        }

        $normalized = str_replace(['\\', '..'], ['/', ''], ltrim($relativePath, '/'));
        $fullPath = WRITEPATH . $normalized;

        if (!is_file($fullPath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Logo file not found.');
        }

        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read logo file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($content);
    }

    private function getReportSettings(): array
    {
        $defaults = [
            'header_line_1' => '',
            'header_line_2' => '',
            'header_line_3' => '',
            'header_line_4' => '',
            'header_center_caption' => '',
            'phone' => '',
            'website' => '',
            'location_line' => '',
            'footer_company' => '',
            'footer_address_line_1' => '',
            'footer_address_line_2' => '',
            'logo_path' => '',
            'progress_image_max_size_mb' => '',
            'progress_image_max_count' => '',
        ];

        $settings = [];
        foreach (self::SETTING_MAP as $inputKey => $settingKey) {
            $stored = setting($settingKey);
            $settings[$inputKey] = ($stored !== null && $stored !== '') ? (string) $stored : (string) $defaults[$inputKey];
        }

        return $settings;
    }
}
