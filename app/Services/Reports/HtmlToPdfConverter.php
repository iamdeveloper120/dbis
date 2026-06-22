<?php

namespace App\Services\Reports;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

class HtmlToPdfConverter
{
    public function convert(string $html, string $pdfPath, array $footerLines = []): void
    {
        $outDir = dirname($pdfPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $this->renderFooterOnEachPage($dompdf, $footerLines);

        $bytes = $dompdf->output();
        if (file_put_contents($pdfPath, $bytes) === false) {
            throw new RuntimeException('Unable to write PDF file: ' . $pdfPath);
        }
    }

    private function renderFooterOnEachPage(Dompdf $dompdf, array $footerLines): void
    {
        $leftLine = trim((string) ($footerLines['left'] ?? ''));
        $rightLine1 = trim((string) ($footerLines['right_line_1'] ?? ''));
        $rightLine2 = trim((string) ($footerLines['right_line_2'] ?? ''));

        if ($leftLine === '' && $rightLine1 === '' && $rightLine2 === '') {
            return;
        }

        $canvas = $dompdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($leftLine, $rightLine1, $rightLine2): void {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $size = 8;
            $width = $canvas->get_width();
            $height = $canvas->get_height();

            $xPadding = 40;
            $lineY = $height - 48;
            $firstTextY = $height - 34;
            $secondTextY = $height - 22;

            $canvas->line($xPadding, $lineY, $width - $xPadding, $lineY, [0.27, 0.27, 0.27], 0.5);

            if ($leftLine !== '') {
                $canvas->text($xPadding, $firstTextY, $leftLine, $font, $size, [0, 0, 0]);
            }

            $pageText = 'Page ' . (int) $pageNumber . ' of ' . (int) $pageCount;
            $pageTextWidth = $fontMetrics->getTextWidth($pageText, $font, $size);
            $canvas->text(($width - $pageTextWidth) / 2, $firstTextY, $pageText, $font, $size, [0, 0, 0]);

            if ($rightLine1 !== '') {
                $line1Width = $fontMetrics->getTextWidth($rightLine1, $font, $size);
                $canvas->text($width - $xPadding - $line1Width, $firstTextY, $rightLine1, $font, $size, [0, 0, 0]);
            }

            if ($rightLine2 !== '') {
                $line2Width = $fontMetrics->getTextWidth($rightLine2, $font, $size);
                $canvas->text($width - $xPadding - $line2Width, $secondTextY, $rightLine2, $font, $size, [0, 0, 0]);
            }
        });
    }
}
