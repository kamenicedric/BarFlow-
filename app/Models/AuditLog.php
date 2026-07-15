<?php

declare(strict_types=1);

namespace App\Models;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function listWithUser(array $filters = [], int $limit = 200): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['action'])) {
            $conditions[] = 'a.action_type = :action';
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['table'])) {
            $conditions[] = 'a.table_name = :table';
            $params['table'] = $filters['table'];
        }
        if (!empty($filters['date_debut'])) {
            $conditions[] = 'a.created_at >= :date_debut';
            $params['date_debut'] = $filters['date_debut'] . ' 00:00:00';
        }
        if (!empty($filters['date_fin'])) {
            $conditions[] = 'a.created_at <= :date_fin';
            $params['date_fin'] = $filters['date_fin'] . ' 23:59:59';
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $limit = max(1, min($limit, 1000));

        $sql = "SELECT a.*, u.nom AS user_nom, u.username AS user_username
                FROM audit_logs a
                LEFT JOIN users u ON u.id = a.user_id
                {$where}
                ORDER BY a.id DESC
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function distinctActions(): array
    {
        return $this->db->query('SELECT DISTINCT action_type FROM audit_logs ORDER BY action_type ASC')
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function distinctTables(): array
    {
        return $this->db->query('SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name ASC')
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
