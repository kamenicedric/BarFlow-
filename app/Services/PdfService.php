<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Encapsule Dompdf pour generer de vrais fichiers PDF a partir de HTML.
 */
class PdfService
{
    public function render(string $html, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $tempDir = dirname(__DIR__, 2) . '/storage/tmp';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }
        $options->set('tempDir', $tempDir);
        $options->set('fontDir', $tempDir);
        $options->set('fontCache', $tempDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Genere le PDF et l'envoie directement au navigateur en telechargement.
     */
    public function stream(string $filename, string $html, string $paper = 'A4', string $orientation = 'portrait'): void
    {
        $output = $this->render($html, $paper, $orientation);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($output));
        echo $output;
    }
}
