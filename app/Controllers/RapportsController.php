<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\PdfService;

class RapportsController extends Controller
{
    public function index(): void
    {
        [$startDate, $endDate] = $this->dates();
        $data = $this->financialData($startDate, $endDate);
        $data = array_merge($this->defaultFinancialData(), $data);

        $this->view('rapports/index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'data' => $data,
        ]);
    }

    public function exportExcel(): void
    {
        [$startDate, $endDate] = $this->dates();
        $data = array_merge($this->defaultFinancialData(), $this->financialData($startDate, $endDate));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rapport-financier.csv');

        $output = fopen('php://output', 'wb');
        fputcsv($output, ['Indicateur', 'Valeur']);
        fputcsv($output, ['Date debut', $startDate]);
        fputcsv($output, ['Date fin', $endDate]);
        fputcsv($output, ['Total ventes', (string) $data['ventes']]);
        fputcsv($output, ['Approvisionnements', (string) $data['approvisionnements']]);
        fputcsv($output, ['Depenses', (string) $data['depenses']]);
        fputcsv($output, ['Pertes', (string) $data['pertes']]);
        fputcsv($output, ['Dons', (string) $data['dons']]);
        fputcsv($output, ['Benefice net', (string) $data['benefice']]);
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        [$startDate, $endDate] = $this->dates();
        $data = array_merge($this->defaultFinancialData(), $this->financialData($startDate, $endDate));

        $html = $this->pdfHtml($startDate, $endDate, $data);

        (new PdfService())->stream('rapport-financier.pdf', $html);
        exit;
    }

    private function pdfHtml(string $startDate, string $endDate, array $data): string
    {
        $barName = e((string) setting('nom_bar', 'BarFlow'));
        $devise = e((string) setting('devise', 'FCFA'));
        $generatedAt = date('d/m/Y H:i');
        $periode = e($startDate) . ' &rarr; ' . e($endDate);

        $money = static fn (float $value): string => number_format($value, 2, ',', ' ');
        $beneficePositif = $data['benefice'] >= 0;

        $rows = [
            ['Total ventes', $money($data['ventes']), '#0d6efd'],
            ['Approvisionnements', '- ' . $money($data['approvisionnements']), '#6c757d'],
            ['Depenses', '- ' . $money($data['depenses']), '#6c757d'],
            ['Pertes', '- ' . $money($data['pertes']), '#6c757d'],
            ['Dons', '- ' . $money($data['dons']), '#6c757d'],
        ];

        $rowsHtml = '';
        foreach ($rows as [$label, $value, $color]) {
            $rowsHtml .= '<tr>'
                . '<td>' . e($label) . '</td>'
                . '<td style="text-align:right;color:' . $color . ';">' . $value . ' ' . $devise . '</td>'
                . '</tr>';
        }

        $beneficeColor = $beneficePositif ? '#198754' : '#dc3545';

        return '<!doctype html><html><head><meta charset="UTF-8">
            <style>
                * { font-family: "DejaVu Sans", sans-serif; }
                body { color: #1f2937; font-size: 12px; }
                .header { border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 20px; }
                .brand { font-size: 22px; font-weight: bold; color: #0d6efd; }
                .subtitle { color: #6b7280; font-size: 12px; margin-top: 2px; }
                .meta { margin: 14px 0 20px; font-size: 12px; }
                .meta span { display: inline-block; margin-right: 24px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; }
                th { background: #0d6efd; color: #fff; text-align: left; padding: 10px; font-size: 12px; }
                td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
                .total-row td { font-size: 15px; font-weight: bold; border-top: 2px solid #111827; background: #f9fafb; }
                .footer { margin-top: 28px; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
            </style></head><body>
            <div class="header">
                <div class="brand">' . $barName . '</div>
                <div class="subtitle">Rapport financier</div>
            </div>
            <div class="meta">
                <span><strong>Periode :</strong> ' . $periode . '</span>
                <span><strong>Genere le :</strong> ' . e($generatedAt) . '</span>
            </div>
            <table>
                <thead><tr><th>Indicateur</th><th style="text-align:right;">Valeur</th></tr></thead>
                <tbody>
                    ' . $rowsHtml . '
                    <tr class="total-row">
                        <td>Benefice net</td>
                        <td style="text-align:right;color:' . $beneficeColor . ';">' . $money($data['benefice']) . ' ' . $devise . '</td>
                    </tr>
                </tbody>
            </table>
            <div class="footer">Document genere automatiquement par BarFlow - ' . $barName . '</div>
            </body></html>';
    }

    private function dates(): array
    {
        $startDate = (string) ($_GET['start'] ?? date('Y-m-01'));
        $endDate = (string) ($_GET['end'] ?? date('Y-m-d'));

        return [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    }

    private function financialData(string $startDate, string $endDate): array
    {
        try {
            $db = Database::connection();

            $sum = static function (string $sql, array $params) use ($db): float {
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                return (float) (($stmt->fetch()['total'] ?? 0) ?: 0);
            };

            $params = ['start' => $startDate, 'end' => $endDate];

            $ventes = $sum('SELECT COALESCE(SUM(total),0) AS total FROM ventes WHERE deleted_at IS NULL AND created_at BETWEEN :start AND :end', $params);
            $appro = $sum('SELECT COALESCE(SUM(prix_total),0) AS total FROM approvisionnements WHERE deleted_at IS NULL AND date_approvisionnement BETWEEN :start AND :end', $params);
            $depenses = $sum('SELECT COALESCE(SUM(montant),0) AS total FROM depenses WHERE deleted_at IS NULL AND date_depense BETWEEN :start AND :end', $params);
            $pertes = $sum('SELECT COALESCE(SUM(valeur_totale),0) AS total FROM pertes WHERE deleted_at IS NULL AND date_perte BETWEEN :start AND :end', $params);
            $dons = $sum('SELECT COALESCE(SUM(valeur_totale),0) AS total FROM dons WHERE deleted_at IS NULL AND date_don BETWEEN :start AND :end', $params);

            return [
                'ventes' => $ventes,
                'approvisionnements' => $appro,
                'depenses' => $depenses,
                'pertes' => $pertes,
                'dons' => $dons,
                'benefice' => $ventes - $appro - $depenses - $pertes - $dons,
            ];
        } catch (\Throwable $exception) {
            error_log('RapportsController financialData error: ' . $exception->getMessage());
            return $this->defaultFinancialData();
        }
    }

    private function defaultFinancialData(): array
    {
        return [
            'ventes' => 0.0,
            'approvisionnements' => 0.0,
            'depenses' => 0.0,
            'pertes' => 0.0,
            'dons' => 0.0,
            'benefice' => 0.0,
        ];
    }
}
