<?php

namespace App\Libraries\Reports;

use RuntimeException;
use ZipArchive;

class DocxTemplateMerger
{
    public function merge(string $templatePath, string $outputPath, array $tokenValues): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException(
                'PHP Zip extension is required for DOCX merge. Enable extension=zip in php.ini and restart Apache/PHP.'
            );
        }

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template file not found: ' . $templatePath);
        }

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        if (!copy($templatePath, $outputPath)) {
            throw new RuntimeException('Unable to copy template to output path.');
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException('Unable to open output DOCX for token merge.');
        }

        $replacements = [];
        foreach (DailyReportTokenMap::placeholders() as $key => $placeholder) {
            $value = isset($tokenValues[$key]) ? (string) $tokenValues[$key] : '';
            $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
            $replacements[$placeholder] = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isWordXmlPart($name)) {
                continue;
            }

            $xml = $zip->getFromName($name);
            if ($xml === false) {
                continue;
            }

            $newXml = strtr($xml, $replacements);
            if ($newXml !== $xml) {
                $zip->addFromString($name, $newXml);
            }
        }

        $zip->close();
    }

    private function isWordXmlPart(string $name): bool
    {
        if (strpos($name, 'word/') !== 0) {
            return false;
        }

        return preg_match('/^(word\/document\.xml|word\/header\d+\.xml|word\/footer\d+\.xml|word\/footnotes\.xml)$/', $name) === 1;
    }
}
