<?php

declare(strict_types=1);

namespace App\Models;

class Setting extends Model
{
    protected string $table = 'settings';

    public function current(): ?array
    {
        $row = $this->db
            ->query('SELECT * FROM settings WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 1')
            ->fetch();

        return $row ?: null;
    }
}
