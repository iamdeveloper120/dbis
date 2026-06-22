<?php

if (!function_exists('report_email_status_label')) {
    function report_email_status_label(?string $status): string
    {
        $normalized = strtoupper(trim((string) $status));

        $map = [
            '' => 'Not Sent',
            'NOT_SENT' => 'Not Sent',
            'PENDING' => 'Pending',
            'SENT' => 'Sent',
            'FAILED' => 'Failed',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return ucwords(strtolower(str_replace('_', ' ', $normalized)));
    }
}

