<?php
require_once __DIR__ . '/config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare('DELETE FROM notifications WHERE user_id=?')->execute([user()['id']]);
    flash('success', 'ล้างการแจ้งเตือนทั้งหมดแล้ว');
}

$returnPath = ltrim((string) ($_POST['return_path'] ?? ''), '/');
$basePath = trim(BASE_URL, '/');
if (str_starts_with($returnPath, $basePath . '/')) {
    $returnPath = substr($returnPath, strlen($basePath) + 1);
}
if (!preg_match('/^[a-zA-Z0-9_\/-]+\.php$/', $returnPath)) {
    $returnPath = dashboard_path(user()['role']);
}
redirect($returnPath);
