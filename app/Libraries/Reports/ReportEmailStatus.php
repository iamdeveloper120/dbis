<?php

namespace App\Libraries\Reports;

class ReportEmailStatus
{
    public const NOT_SENT = 'NOT_SENT';
    public const PENDING = 'PENDING';
    public const SENT = 'SENT';
    public const FAILED = 'FAILED';

    public static function label(?string $status): string
    {
        $value = strtoupper(trim((string) $status));

        if ($value === '' || $value === self::NOT_SENT) {
            return 'Not Sent';
        }

        if ($value === self::PENDING) {
            return 'Pending';
        }

        if ($value === self::SENT) {
            return 'Sent';
        }

        if ($value === self::FAILED) {
            return 'Failed';
        }

        return ucwords(strtolower(str_replace('_', ' ', $value)));
    }
}

