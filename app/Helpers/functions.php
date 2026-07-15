<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function app_base_path(): string
{
    $appUrl = env('APP_URL', '');
    $path = (string) parse_url($appUrl, PHP_URL_PATH);
    $path = rtrim($path, '/');
    return $path === '' ? '' : $path;
}

function url(string $path = ''): string
{
    $base = app_base_path();
    $normalized = '/' . ltrim($path, '/');
    return $base . ($normalized === '/' ? '' : $normalized);
}

function ui_page_header(string $title, string $subtitle = '', ?string $actionHtml = null): string
{
    $titleSafe = e($title);
    $subtitleSafe = e($subtitle);
    $action = $actionHtml ?? '';

    return '<div class="section-header">'
        . '<div><h5 class="section-title mb-1">' . $titleSafe . '</h5>'
        . '<p class="section-subtitle mb-0">' . $subtitleSafe . '</p></div>'
        . '<div class="section-action">' . $action . '</div>'
        . '</div>';
}

function ui_stat_card(string $label, string $value, string $icon = 'bi-graph-up'): string
{
    return '<div class="stat-card">'
        . '<div class="stat-card-icon"><i class="bi ' . e($icon) . '"></i></div>'
        . '<div><small>' . e($label) . '</small><h6>' . e($value) . '</h6></div>'
        . '</div>';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['_old'][$key] ?? $default;
}

function with_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function audit_log(string $action, string $table, ?int $recordId, ?array $oldValue, ?array $newValue): void
{
    if (!Auth::check()) {
        return;
    }

    $sql = 'INSERT INTO audit_logs (user_id, action_type, table_name, record_id, old_value, new_value, ip_address, created_at)
            VALUES (:user_id, :action_type, :table_name, :record_id, :old_value, :new_value, :ip_address, NOW())';

    $statement = Database::connection()->prepare($sql);
    $statement->execute([
        'user_id' => Auth::user()['id'],
        'action_type' => $action,
        'table_name' => $table,
        'record_id' => $recordId,
        'old_value' => $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
        'new_value' => $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    ]);
}
