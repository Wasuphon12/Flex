<?php

declare(strict_types=1);
session_start();

const DB_HOST = '127.0.0.1';
const DB_NAME = 'db_flexjob';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = '/Flex';
const APP_ROOT = __DIR__ . '/..';

function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function is_role(string $role): bool
{
    return user() && user()['role'] === $role;
}
function redirect(string $path): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}
function require_login(?string $role = null): void
{
    if (!user() || ($role && !is_role($role))) redirect('auth/login.php');

    $statusStmt = db()->prepare('SELECT account_status FROM users WHERE user_id=?');
    $statusStmt->execute([user()['id']]);
    if ($statusStmt->fetchColumn() !== 'active') {
        $_SESSION = [];
        session_destroy();
        redirect('auth/login.php');
    }
}
function dashboard_path(string $role): string
{
    return ['worker' => 'worker/index.php', 'employer' => 'employer/index.php', 'admin' => 'admin/dashboard.php'][$role] ?? 'index.php';
}
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}
function job_type(string $type): string
{
    return ['part_time' => 'พาร์ทไทม์', 'event' => 'งานอีเวนต์', 'freelance' => 'ฟรีแลนซ์'][$type] ?? $type;
}
function pay_text(array $job): string
{
    return number_format((float)$job['pay_amount']) . ' บาท/' . ['hour' => 'ชม.', 'day' => 'วัน', 'project' => 'โปรเจกต์'][$job['pay_unit']];
}
function upload_file(string $field, array $allowed, string $folder): ?string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) throw new RuntimeException('ชนิดไฟล์ไม่ถูกต้อง');
    $directory = APP_ROOT . '/uploads/' . $folder;
    if (!is_dir($directory)) mkdir($directory, 0775, true);
    $filename = bin2hex(random_bytes(10)) . '.' . $extension;
    move_uploaded_file($_FILES[$field]['tmp_name'], $directory . '/' . $filename);
    return 'uploads/' . $folder . '/' . $filename;
}
