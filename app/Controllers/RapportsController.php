<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

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

        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="UTF-8"><title>Rapport financier</title></head><body>';
        echo '<h2>BarFlow - Rapport financier</h2>';
        echo '<p>Periode: ' . htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') . ' -> ' . htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<table border="1" cellspacing="0" cellpadding="6">';
        echo '<tr><th>Indicateur</th><th>Valeur</th></tr>';
        echo '<tr><td>Total ventes</td><td>' . number_format($data['ventes'], 2, ',', ' ') . '</td></tr>';
        echo '<tr><td>Approvisionnements</td><td>' . number_format($data['approvisionnements'], 2, ',', ' ') . '</td></tr>';
        echo '<tr><td>Depenses</td><td>' . number_format($data['depenses'], 2, ',', ' ') . '</td></tr>';
        echo '<tr><td>Pertes</td><td>' . number_format($data['pertes'], 2, ',', ' ') . '</td></tr>';
        echo '<tr><td>Dons</td><td>' . number_format($data['dons'], 2, ',', ' ') . '</td></tr>';
        echo '<tr><td><strong>Benefice net</strong></td><td><strong>' . number_format($data['benefice'], 2, ',', ' ') . '</strong></td></tr>';
        echo '</table>';
        echo '<p style="margin-top: 16px;">Utilise Ctrl+P pour enregistrer en PDF.</p>';
        echo '</body></html>';
        exit;
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
