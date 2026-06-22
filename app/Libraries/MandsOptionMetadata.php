<?php

namespace App\Libraries;

class MandsOptionMetadata
{
    private const PROMPT_LEVELS = [
        '1' => [
            'label' => 'FPP',
            'tooltip' => 'Full Physical Prompt',
            'dom_id_suffix' => '1',
        ],
        '2' => [
            'label' => 'PPP',
            'tooltip' => 'Partial Physical Prompt',
            'dom_id_suffix' => '2',
        ],
        '3' => [
            'label' => 'GP',
            'tooltip' => 'Gestural Prompt',
            'dom_id_suffix' => '3',
        ],
        '4' => [
            'label' => 'V',
            'tooltip' => 'Vocal Prompt',
            'dom_id_suffix' => '4',
        ],
        '5' => [
            'label' => 'IV',
            'tooltip' => 'Intraverbal Prompt',
            'dom_id_suffix' => '5',
        ],
        '6' => [
            'label' => 'Item',
            'tooltip' => 'Item',
            'dom_id_suffix' => '8',
        ],
        '7' => [
            'label' => 'MO',
            'tooltip' => 'Motivating Operation',
            'dom_id_suffix' => '6',
        ],
        '8' => [
            'label' => 'TMO',
            'tooltip' => 'Transitive Motivating Operation',
            'dom_id_suffix' => '7',
        ],
    ];

    private const MAND_ERRORS = [
        '' => [
            'label' => '',
            'tooltip' => '',
        ],
        'null' => [
            'label' => '',
            'tooltip' => '',
        ],
        '1' => [
            'label' => 'N/A',
            'tooltip' => '',
        ],
        '2' => [
            'label' => 'S',
            'tooltip' => 'Scrolling',
            'dom_id_suffix' => '3',
        ],
        '3' => [
            'label' => 'R',
            'tooltip' => 'Repetitive',
            'dom_id_suffix' => '4',
        ],
        '4' => [
            'label' => 'IA',
            'tooltip' => 'Inappropriate Autoclitic',
            'dom_id_suffix' => '5',
        ],
    ];

    private const VOCAL_RESPONSES = [
        '' => [
            'label' => '',
            'tooltip' => '',
        ],
        'null' => [
            'label' => '',
            'tooltip' => '',
        ],
        '1' => [
            'label' => 'N/A',
            'tooltip' => '',
        ],
        '2' => [
            'label' => 'SS',
            'tooltip' => 'Speech Sound',
            'dom_id_suffix' => '2',
        ],
        '3' => [
            'label' => 'WA',
            'tooltip' => 'Word Approximation',
            'dom_id_suffix' => '3',
        ],
        '4' => [
            'label' => 'IW',
            'tooltip' => 'Intelligible Word',
            'dom_id_suffix' => '4',
        ],
        '5' => [
            'label' => 'AF',
            'tooltip' => 'Adult Form',
            'dom_id_suffix' => '5',
        ],
    ];

    private const RESPONSE_COMPARISONS = [
        '' => '',
        'null' => '',
        '1' => 'Worsened',
        '2' => 'Remained',
        '3' => 'Improved',
    ];

    private const PROMPT_LEVEL_FORM_KEYS = ['1', '2', '3', '4', '5', '6', '7', '8'];
    private const MAND_ERROR_FORM_KEYS = ['2', '3', '4'];
    private const VOCAL_RESPONSE_FORM_KEYS = ['2', '3', '4', '5'];

    public static function promptLevelOptions(): array
    {
        return self::buildOptionList(self::PROMPT_LEVELS, self::PROMPT_LEVEL_FORM_KEYS);
    }

    public static function mandErrorOptions(): array
    {
        return self::buildOptionList(self::MAND_ERRORS, self::MAND_ERROR_FORM_KEYS);
    }

    public static function vocalResponseOptions(): array
    {
        return self::buildOptionList(self::VOCAL_RESPONSES, self::VOCAL_RESPONSE_FORM_KEYS);
    }

    public static function promptLevelLabel($value): string
    {
        return self::lookupMetadata(self::PROMPT_LEVELS, $value, 'label');
    }

    public static function promptLevelTooltip($value): string
    {
        return self::lookupMetadata(self::PROMPT_LEVELS, $value, 'tooltip');
    }

    public static function mandErrorLabel($value): string
    {
        return self::lookupMetadata(self::MAND_ERRORS, $value, 'label');
    }

    public static function mandErrorTooltip($value): string
    {
        return self::lookupMetadata(self::MAND_ERRORS, $value, 'tooltip');
    }

    public static function vocalResponseLabel($value): string
    {
        return self::lookupMetadata(self::VOCAL_RESPONSES, $value, 'label');
    }

    public static function vocalResponseTooltip($value): string
    {
        return self::lookupMetadata(self::VOCAL_RESPONSES, $value, 'tooltip');
    }

    public static function responseComparisonLabel($value): string
    {
        $key = self::normalizeKey($value);

        return self::RESPONSE_COMPARISONS[$key] ?? '';
    }

    private static function buildOptionList(array $metadata, array $keys): array
    {
        $options = [];

        foreach ($keys as $key) {
            $options[] = [
                'value' => $key,
                'label' => $metadata[$key]['label'] ?? '',
                'tooltip' => $metadata[$key]['tooltip'] ?? '',
                'dom_id_suffix' => $metadata[$key]['dom_id_suffix'] ?? $key,
            ];
        }

        return $options;
    }

    private static function lookupMetadata(array $metadata, $value, string $field): string
    {
        $key = self::normalizeKey($value);

        return $metadata[$key][$field] ?? '';
    }

    private static function normalizeKey($value): string
    {
        if ($value === null) {
            return 'null';
        }

        return trim((string) $value);
    }
}
