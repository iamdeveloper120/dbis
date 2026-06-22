<?php

namespace App\Libraries\Reports;

class DailyReportTokenMap
{
    public const TOKENS = [
        'report_logo_data_uri' => '{{report_logo_data_uri}}',
        'report_header_line_1' => '{{report_header_line_1}}',
        'report_header_line_2' => '{{report_header_line_2}}',
        'report_header_line_3' => '{{report_header_line_3}}',
        'report_header_line_4' => '{{report_header_line_4}}',
        'report_header_center_caption' => '{{report_header_center_caption}}',
        'report_phone' => '{{report_phone}}',
        'report_website' => '{{report_website}}',
        'report_location_line' => '{{report_location_line}}',
        'report_footer_company' => '{{report_footer_company}}',
        'report_footer_address_line_1' => '{{report_footer_address_line_1}}',
        'report_footer_address_line_2' => '{{report_footer_address_line_2}}',
        'learner_name' => '{{learner_name}}',
        'report_date' => '{{report_date}}',
        'tutor_names' => '{{tutor_names}}',
        'net_vs_dti' => '{{net_vs_dti}}',
        'program_probes_table' => '{{program_probes_table}}',
        'mands_frequency' => '{{mands_frequency}}',
        'mands_variety' => '{{mands_variety}}',
        'problem_behavior_frequency' => '{{problem_behavior_frequency}}',
        'problem_behavior_duration' => '{{problem_behavior_duration}}',
        'mand_data_table' => '{{mand_data_table}}',
        'problem_behavior_table' => '{{problem_behavior_table}}',
        'tutor_comments' => '{{tutor_comments}}',
        'uploaded_images_html' => '{{uploaded_images_html}}',
        'wow_moments' => '{{wow_moments}}',
    ];

    public static function keys(): array
    {
        return array_keys(self::TOKENS);
    }

    public static function placeholders(): array
    {
        return self::TOKENS;
    }
}
