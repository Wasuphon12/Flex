<?php
require_once __DIR__ . '/../config/config.php';
if (user()) redirect(dashboard_path(user()['role']));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($_POST['email']))]);
    $found = $stmt->fetch();
    if ($found && password_verify($_POST['password'], $found['password_hash']) && $found['account_status'] === 'active') {
        $_SESSION['user'] = ['id' => $found['user_id'], 'name' => trim($found['first_name'] . ' ' . $found['last_name']), 'role' => $found['role'], 'email' => $found['email']];
        redirect(dashboard_path($found['role']));
    }
    flash('error', $found && password_verify($_POST['password'], $found['password_hash']) ? 'บัญชีนี้ถูกระงับการใช้งาน' : 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
}
$pageTitle = 'เข้าสู่ระบบ | FLEXJOB';
require APP_ROOT . '/partials/header.php'; ?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
            <form class="card border-0 shadow-sm" method="post">
                <div class="card-body p-4 p-md-5">
                    <p class="eyebrow">WELCOME BACK</p>
                    <h1 class="h2 mb-2">เข้าสู่ระบบ</h1>
                    <p class="text-secondary mb-4">เข้าสู่ FLEXJOB เพื่อจัดการงานของคุณ</p>

                    <div class="mb-3">
                        <label class="form-label" for="email">อีเมล</label>
                        <input id="email" class="form-control" type="email" name="email" required autocomplete="email">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">รหัสผ่าน</label>
                        <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
                    </div>

                    <button class="btn btn-success w-100" type="submit">เข้าสู่ระบบ</button>
                    <p class="text-center text-secondary small mt-4 mb-0">ยังไม่มีบัญชี? <a class="link-primary fw-semibold" href="<?= BASE_URL ?>/auth/register.php">สมัครใช้งาน</a></p>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require APP_ROOT . '/partials/footer.php'; ?>
