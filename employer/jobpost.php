<?php
require_once __DIR__ . '/../config/config.php';
require_login('employer');

$pdo = db();
$verificationStmt = $pdo->prepare("SELECT COALESCE((SELECT ed.document_status FROM employer_documents ed WHERE ed.employer_user_id=ep.user_id ORDER BY ed.submitted_at DESC LIMIT 1), 'not_submitted') AS verification_status FROM employer_profiles ep WHERE ep.user_id=?");
$verificationStmt->execute([user()['id']]);
$verificationStatus = $verificationStmt->fetchColumn() ?: 'not_submitted';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($verificationStatus !== 'approved') {
            throw new RuntimeException('บัญชีของคุณยังไม่ผ่านการยืนยัน จึงยังโพสต์งานไม่ได้');
        }

        $pdo->prepare("INSERT INTO jobs (employer_user_id,job_category_id,job_title,job_description,work_location,work_province,work_schedule,pay_amount,pay_unit,open_positions) SELECT ?,job_category_id,?,?,?,?,?,?,?,? FROM job_categories WHERE category_slug=?")
            ->execute([
                user()['id'],
                trim($_POST['title'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['address'] ?? ''),
                trim($_POST['province'] ?? ''),
                trim($_POST['work_date'] ?? ''),
                (float) ($_POST['pay_amount'] ?? 0),
                $_POST['pay_unit'] ?? 'hour',
                (int) ($_POST['positions'] ?? 1),
                $_POST['job_type'] ?? 'part_time',
            ]);

        $jobId = (int) $pdo->lastInsertId();
        $image = upload_file('job_image', ['jpg', 'jpeg', 'png', 'webp'], 'jobs');
        if ($image) {
            $pdo->prepare('INSERT INTO job_images (job_id,image_file_path) VALUES (?,?)')
                ->execute([$jobId, $image]);
        }

        flash('success', 'โพสต์งานสำเร็จ ประกาศแสดงทันทีและจะถูกตรวจสอบย้อนหลังโดย Admin');
        redirect('employer/dashboard.php');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
}

$pageTitle = 'สร้างประกาศงาน | FLEXJOB';
require APP_ROOT . '/partials/header.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <p class="eyebrow">CREATE JOB POST</p>
            <h1 class="h2 mb-1">สร้างประกาศงานใหม่</h1>
            <p class="text-secondary mb-0">เพิ่มรายละเอียดงานและรูปประกอบเพื่อให้ผู้สมัครตัดสินใจได้ง่ายขึ้น</p>
        </div>
        <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/employer/dashboard.php">กลับไปจัดการประกาศ</a>
    </div>

    <?php if ($verificationStatus !== 'approved'): ?>
        <div class="alert alert-warning">คุณสร้างประกาศได้หลังจาก Admin อนุมัติเอกสารเท่านั้น</div>
    <?php else: ?>
        <form class="card border-0 shadow-sm" method="post" enctype="multipart/form-data">
            <div class="card-body p-4 p-md-5">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="title">ชื่องาน</label><input id="title" class="form-control" name="title" required></div>
                    <div class="col-md-6"><label class="form-label" for="job_type">ประเภทงาน</label><select id="job_type" class="form-select" name="job_type"><option value="part_time">พาร์ทไทม์</option><option value="event">งานอีเวนต์</option><option value="freelance">ฟรีแลนซ์</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="positions">จำนวนคน</label><input id="positions" class="form-control" type="number" name="positions" min="1" value="1" required></div>
                    <div class="col-12"><label class="form-label" for="description">รายละเอียดงาน</label><textarea id="description" class="form-control" name="description" rows="5" required></textarea></div>
                    <div class="col-md-6"><label class="form-label" for="province">จังหวัด</label><input id="province" class="form-control" name="province" placeholder="เช่น กรุงเทพมหานคร" required></div>
                    <div class="col-md-6"><label class="form-label" for="address">ที่อยู่ / สถานที่ทำงาน</label><input id="address" class="form-control" name="address" placeholder="เช่น สยามสแควร์ ซอย..." required></div>
                    <div class="col-md-6"><label class="form-label" for="work_date">วัน/ช่วงเวลาทำงาน</label><input id="work_date" class="form-control" name="work_date"></div>
                    <div class="col-md-6"><label class="form-label" for="pay_amount">ค่าจ้าง</label><input id="pay_amount" class="form-control" type="number" name="pay_amount" min="1" required></div>
                    <div class="col-md-6"><label class="form-label" for="pay_unit">หน่วย</label><select id="pay_unit" class="form-select" name="pay_unit"><option value="hour">ต่อชั่วโมง</option><option value="day">ต่อวัน</option><option value="project">ต่อโปรเจกต์</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="job_image">รูปประกอบประกาศ</label><input id="job_image" class="form-control" type="file" name="job_image" accept=".jpg,.jpeg,.png,.webp"><div class="form-text">JPG, PNG หรือ WEBP</div></div>
                </div>
                <div class="d-flex justify-content-end mt-4"><button class="btn btn-success px-4" type="submit">เผยแพร่ประกาศ</button></div>
            </div>
        </form>
    <?php endif ?>
</main>

<?php require APP_ROOT . '/partials/footer.php'; ?>
