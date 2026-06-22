<?php

namespace App\Libraries\Reports;

use RuntimeException;

class HtmlTemplateRenderer
{
    /**
     * Tokens that may intentionally contain HTML markup (tables, formatted blocks).
     */
    private const RAW_HTML_TOKENS = [
        'program_probes_table',
        'mand_data_table',
        'problem_behavior_table',
        'uploaded_images_html',
    ];

    public function render(string $templatePath, array $tokenValues): string
    {
        if (!is_file($templatePath)) {
            throw new RuntimeException('HTML template file not found: ' . $templatePath);
        }

        $html = file_get_contents($templatePath);
        if ($html === false) {
            throw new RuntimeException('Unable to read HTML template file.');
        }

        $html = $this->applyConditionalSections($html, $tokenValues);

        $replacements = [];
        foreach (DailyReportTokenMap::placeholders() as $key => $placeholder) {
            $value = isset($tokenValues[$key]) ? (string) $tokenValues[$key] : '';
            if (in_array($key, self::RAW_HTML_TOKENS, true)) {
                $replacements[$placeholder] = $value;
            } else {
                $replacements[$placeholder] = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }

        return strtr($html, $replacements);
    }

    private function applyConditionalSections(string $html, array $tokenValues): string
    {
        $wow = trim((string) ($tokenValues['wow_moments'] ?? ''));
        if ($wow === '' || strcasecmp($wow, 'N/A') === 0) {
            // Remove the full "Wow Moments" block from the report when there is no content.
            $pattern = '/<div class="comment-section">\s*<h2 class="mt-10">Wow Moments<\/h2>\s*<div class="comment-box">\{\{wow_moments\}\}<\/div>\s*<\/div>/i';
            $html = preg_replace($pattern, '', $html) ?? $html;
        }

        return $html;
    }
}
