<?php
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $role = $_POST['role'] === 'employer' ? 'employer' : 'worker';
        $pdo = db();
        $pdo->beginTransaction();
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $companyName = trim($_POST['company_name'] ?? '');
        if ($role === 'employer' && $companyName === '') throw new RuntimeException('กรุณากรอกชื่อบริษัทหรือชื่อผู้ว่าจ้าง');
        $pdo->prepare('INSERT INTO users (first_name,last_name,email,password_hash,role,phone) VALUES (?,?,?,?,?,?)')->execute([$firstName, $lastName, strtolower(trim($_POST['email'])), password_hash($_POST['password'], PASSWORD_DEFAULT), $role, trim($_POST['phone'])]);
        $id = (int)$pdo->lastInsertId();
        if ($role === 'employer') $pdo->prepare('INSERT INTO employer_profiles (user_id,company_name) VALUES (?,?)')->execute([$id, $companyName]);
        else $pdo->prepare('INSERT INTO worker_profiles (user_id) VALUES (?)')->execute([$id]);
        $pdo->commit();
        $_SESSION['user'] = ['id' => $id, 'name' => trim($firstName . ' ' . $lastName), 'role' => $role, 'email' => strtolower(trim($_POST['email']))];
        redirect(dashboard_path($role));
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'ไม่สามารถสมัครได้ อีเมลนี้อาจถูกใช้แล้ว');
    }
}
$pageTitle = 'สมัครใช้งาน | FLEXJOB';
require APP_ROOT . '/partials/header.php'; ?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-6">
            <form class="card border-0 shadow-sm" method="post">
                <div class="card-body p-4 p-md-5">
                    <p class="eyebrow">JOIN FLEXJOB</p>
                    <h1 class="h2 mb-4">สร้างบัญชีใหม่</h1>

                    <div class="mb-4">
                        <p class="form-label mb-2">ประเภทบัญชี</p>
                        <input class="btn-check" id="role-worker" type="radio" name="role" value="worker" checked>
                        <label class="btn btn-outline-primary me-2" for="role-worker">ผู้หางาน</label>
                        <input class="btn-check" id="role-employer" type="radio" name="role" value="employer">
                        <label class="btn btn-outline-primary" for="role-employer">ผู้ว่าจ้าง</label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="first_name">ชื่อ</label><input id="first_name" class="form-control" name="first_name" required autocomplete="given-name"></div>
                        <div class="col-md-6"><label class="form-label" for="last_name">นามสกุล</label><input id="last_name" class="form-control" name="last_name" required autocomplete="family-name"></div>
                        <div class="col-12 company-field d-none"><label class="form-label" for="company_name">ชื่อบริษัท / ผู้ว่าจ้าง</label><input id="company_name" class="form-control" name="company_name"></div>
                        <div class="col-12"><label class="form-label" for="email">อีเมล</label><input id="email" class="form-control" type="email" name="email" required autocomplete="email"></div>
                        <div class="col-12"><label class="form-label" for="phone">เบอร์โทรศัพท์</label><input id="phone" class="form-control" name="phone" autocomplete="tel"></div>
                        <div class="col-12"><label class="form-label" for="password">รหัสผ่าน</label><input id="password" class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
                    </div>

                    <button class="btn btn-success w-100 mt-4" type="submit">สร้างบัญชี</button>
                    <p class="text-center text-secondary small mt-4 mb-0">มีบัญชีอยู่แล้ว? <a class="link-primary fw-semibold" href="<?= BASE_URL ?>/auth/login.php">เข้าสู่ระบบ</a></p>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
    const companyField = document.querySelector('.company-field');
    const companyInput = companyField.querySelector('input');
    const toggleCompanyField = () => {
        const isEmployer = document.querySelector('input[name=role]:checked').value === 'employer';
        companyField.classList.toggle('d-none', !isEmployer);
        companyInput.required = isEmployer;
    };
    document.querySelectorAll('input[name=role]').forEach(input => input.addEventListener('change', toggleCompanyField));
    toggleCompanyField();
</script><?php require APP_ROOT . '/partials/footer.php'; ?>
