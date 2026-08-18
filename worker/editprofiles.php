<?php
require_once __DIR__ . '/../config/config.php';
require_login('worker');

$pdo = db();
$workerId = user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $resumeFile = upload_file('resume_file', ['pdf', 'doc', 'docx'], 'resumes');
        $portfolioFile = upload_file('portfolio_file', ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'zip'], 'portfolios');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');

        if ($firstName === '' || $lastName === '') {
            throw new RuntimeException('กรุณากรอกชื่อและนามสกุล');
        }

        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET first_name=?, last_name=?, phone=? WHERE user_id=?')
            ->execute([$firstName, $lastName, trim($_POST['phone'] ?? ''), $workerId]);

        $pdo->prepare('INSERT INTO worker_profiles (user_id, professional_headline, biography, skills, resume_file_path, portfolio_file_path, portfolio_url) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE professional_headline=VALUES(professional_headline), biography=VALUES(biography), skills=VALUES(skills), resume_file_path=COALESCE(VALUES(resume_file_path), resume_file_path), portfolio_file_path=COALESCE(VALUES(portfolio_file_path), portfolio_file_path), portfolio_url=VALUES(portfolio_url)')
            ->execute([$workerId, trim($_POST['headline'] ?? ''), trim($_POST['introduce'] ?? ''), trim($_POST['skills'] ?? ''), $resumeFile, $portfolioFile, trim($_POST['portfolio_url'] ?? '')]);

        $pdo->commit();
        $_SESSION['user']['name'] = $firstName . ' ' . $lastName;
        flash('success', 'บันทึกข้อมูลโปรไฟล์แล้ว');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', $e->getMessage());
    }
    redirect('worker/editprofiles.php');
}

$profileStmt = $pdo->prepare('SELECT u.first_name, u.last_name, u.email, u.phone, wp.professional_headline, wp.biography, wp.skills, wp.resume_file_path, wp.portfolio_file_path, wp.portfolio_url FROM users u LEFT JOIN worker_profiles wp ON wp.user_id=u.user_id WHERE u.user_id=?');
$profileStmt->execute([$workerId]);
$profile = $profileStmt->fetch() ?: [];
$pageTitle = 'แก้ไขโปรไฟล์ | FLEXJOB';
require APP_ROOT . '/partials/header.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-2">WORKER PROFILE</p>
            <h1 class="h2 mb-1">แก้ไขโปรไฟล์</h1>
            <p class="text-secondary mb-0">ข้อมูลนี้จะแสดงให้ผู้ว่าจ้างเห็นเมื่อพิจารณาใบสมัครของคุณ</p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h2 class="h5 mb-3">ข้อมูลส่วนตัว</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6"><label class="form-label" for="first_name">ชื่อ</label><input id="first_name" class="form-control" name="first_name" value="<?= e($profile['first_name'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="last_name">นามสกุล</label><input id="last_name" class="form-control" name="last_name" value="<?= e($profile['last_name'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="phone">เบอร์โทรศัพท์</label><input id="phone" class="form-control" name="phone" value="<?= e($profile['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label" for="email">อีเมล</label><input id="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>" disabled></div>
            </div>

            <h2 class="h5 mb-3">แนะนำตัวและผลงาน</h2>
            <div class="row g-3 mb-4">
                <div class="col-12"><label class="form-label" for="introduce">แนะนำตัว</label><textarea id="introduce" class="form-control" name="introduce" rows="4" placeholder="เล่าประสบการณ์ จุดเด่น หรือประเภทงานที่สนใจ"><?= e($profile['biography'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label" for="skills">ทักษะ</label><input id="skills" class="form-control" name="skills" value="<?= e($profile['skills'] ?? '') ?>" placeholder="เช่น Canva, Excel, การสื่อสาร"></div>
                <div class="col-md-6"><label class="form-label" for="portfolio_file">Portfolio file</label><?php if (!empty($profile['portfolio_file_path'])): ?><div class="small text-success mb-2">✓ <a class="link-success" href="<?= BASE_URL . '/' . e($profile['portfolio_file_path']) ?>" target="_blank" rel="noopener">เปิดไฟล์ Portfolio ที่อัปโหลดแล้ว</a></div><?php endif ?><input id="portfolio_file" class="form-control" type="file" name="portfolio_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.zip">
                    <div class="form-text">PDF หรือ รูปภาพ — รวม Certificate ไว้ใน Portfolio ได้</div>
                </div>
                <div class="col-md-6"><label class="form-label" for="portfolio_url">Portfolio URL</label><input id="portfolio_url" class="form-control" type="url" name="portfolio_url" value="<?= e($profile['portfolio_url'] ?? '') ?>" placeholder="https://...">
                    <div class="form-text">วางลิงก์ Google Drive, Behance, เว็บไซต์ หรือผลงานออนไลน์ และแนบ Certificate ไว้ในลิงก์เดียวกันได้</div>
                </div>
            </div>

            <h2 class="h5 mb-3">Resume</h2>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="resume_file">Resume</label><?php if (!empty($profile['resume_file_path'])): ?><div class="small text-success mb-2">✓ <a class="link-success" href="<?= BASE_URL . '/' . e($profile['resume_file_path']) ?>" target="_blank" rel="noopener">เปิด Resume ที่อัปโหลดแล้ว</a></div><?php endif ?><input id="resume_file" class="form-control" type="file" name="resume_file" accept=".pdf,.doc,.docx">
                    <div class="form-text">PDF, DOC หรือ DOCX</div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4"><button class="btn btn-success px-4" type="submit">บันทึกโปรไฟล์</button></div>
        </div>
    </form>
</main>

<?php require APP_ROOT . '/partials/footer.php'; ?>