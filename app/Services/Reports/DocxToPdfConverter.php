<?php

namespace App\Services\Reports;

use RuntimeException;

class DocxToPdfConverter
{
    public function convert(string $docxPath, string $pdfPath): void
    {
        if (!is_file($docxPath)) {
            throw new RuntimeException('DOCX input file not found: ' . $docxPath);
        }

        $binary = $this->resolveBinary();
        if ($binary === null) {
            throw new RuntimeException(
                'PDF converter not found. Install LibreOffice and set REPORT_SOFFICE_PATH in .env.'
            );
        }

        $outDir = dirname($pdfPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $cmd = escapeshellarg($binary)
            . ' --headless --convert-to pdf --outdir '
            . escapeshellarg($outDir)
            . ' '
            . escapeshellarg($docxPath);

        $output = [];
        $code = 0;
        exec($cmd . ' 2>&1', $output, $code);

        $generatedPath = $outDir . DIRECTORY_SEPARATOR . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';
        if ($code !== 0 || !is_file($generatedPath)) {
            throw new RuntimeException('DOCX to PDF conversion failed: ' . implode(' | ', $output));
        }

        if (realpath($generatedPath) !== realpath($pdfPath)) {
            if (is_file($pdfPath)) {
                unlink($pdfPath);
            }
            rename($generatedPath, $pdfPath);
        }
    }

    private function resolveBinary(): ?string
    {
        $candidates = [];
        $envBinary = getenv('REPORT_SOFFICE_PATH');
        if (is_string($envBinary) && trim($envBinary) !== '') {
            $candidates[] = trim($envBinary);
        }

        $candidates[] = 'soffice';
        $candidates[] = 'libreoffice';
        $candidates[] = 'C:\\Program Files\\LibreOffice\\program\\soffice.com';
        $candidates[] = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
        $candidates[] = 'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe';

        foreach ($candidates as $candidate) {
            if ($this->isExecutableCandidate($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isExecutableCandidate(string $candidate): bool
    {
        if (str_contains($candidate, '\\') || str_contains($candidate, '/')) {
            return is_file($candidate);
        }

        $checkOutput = [];
        $checkCode = 0;
        if (PHP_OS_FAMILY === 'Windows') {
            exec('where ' . escapeshellarg($candidate) . ' 2>NUL', $checkOutput, $checkCode);
        } else {
            exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null', $checkOutput, $checkCode);
        }
        return $checkCode === 0 && !empty($checkOutput);
    }
}
