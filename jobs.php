<?php require_once __DIR__ . '/config/config.php';
$where = ["j.job_status='published'"];
$params = [];
if (!empty($_GET['q'])) {
    $where[] = '(j.job_title LIKE ? OR j.job_description LIKE ? OR ep.company_name LIKE ?)';
    $term = '%' . $_GET['q'] . '%';
    $params = [$term, $term, $term];
}
if (!empty($_GET['province'])) {
    $where[] = "COALESCE(NULLIF(j.work_province, ''), j.work_location) LIKE ?";
    $params[] = '%' . $_GET['province'] . '%';
}
if (!empty($_GET['type']) && in_array($_GET['type'], ['part_time', 'event', 'freelance'], true)) {
    $where[] = 'jc.category_slug=?';
    $params[] = $_GET['type'];
}
$stmt = db()->prepare('SELECT j.job_id AS id,j.job_title AS title,jc.category_slug AS job_type,j.work_location AS location,j.work_schedule AS work_date,j.pay_amount,j.pay_unit,ep.company_name,ep.company_logo_path AS company_logo,(SELECT ji.image_file_path FROM job_images ji WHERE ji.job_id=j.job_id ORDER BY ji.display_order, ji.job_image_id LIMIT 1) AS cover_image FROM jobs j JOIN employer_profiles ep ON ep.user_id=j.employer_user_id JOIN job_categories jc ON jc.job_category_id=j.job_category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY j.created_at DESC');
$stmt->execute($params);
$jobs = $stmt->fetchAll();
$pageTitle = 'ค้นหางาน | FLEXJOB';
require APP_ROOT . '/partials/header.php'; ?>
<main class="listing container py-5">
    <div class="page-intro mb-4">
        <p class="eyebrow">FIND YOUR NEXT JOB</p>
        <h1>ค้นหางานที่ยืดหยุ่นสำหรับคุณ</h1>
        <form class="row g-2 mt-3" method="get">
            <div class="col-12 col-md"><input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="ชื่อตำแหน่ง หรือบริษัท"></div>
            <div class="col-12 col-md"><input class="form-control" name="province" value="<?= e($_GET['province'] ?? '') ?>" placeholder="จังหวัด เช่น กรุงเทพมหานคร"></div>
            <div class="col-12 col-md"><select class="form-select" name="type"><option value="">ทุกประเภทงาน</option><?php foreach (['part_time' => 'พาร์ทไทม์', 'event' => 'งานอีเวนต์', 'freelance' => 'ฟรีแลนซ์'] as $value => $label): ?><option value="<?= $value ?>" <?= ($_GET['type'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></div>
            <div class="col-12 col-md-auto"><button class="btn btn-success w-100" type="submit">ค้นหา</button></div>
        </form>
    </div>
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 text-secondary small mb-4"><span>พบ <?= count($jobs) ?> งาน</span><span>เฉพาะผู้ว่าจ้างที่ยืนยันแล้ว ✓</span></div>
    <div class="row g-4">
        <?php foreach ($jobs as $job): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <a class="card h-100 job-card" href="<?= BASE_URL ?>/job.php?id=<?= $job['id'] ?>">
                    <div class="job-image">
                        <?php if (!empty($job['cover_image'])): ?>
                            <img src="<?= BASE_URL . '/' . e($job['cover_image']) ?>" alt="<?= e($job['title']) ?>">
                        <?php else: ?>
                            <?= $job['job_type'] === 'event' ? '✦' : ($job['job_type'] === 'freelance' ? '⌁' : '◷') ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <span class="tag mt-0 align-self-start"><?= job_type($job['job_type']) ?></span>
                        <h3><?= e($job['title']) ?></h3>
                        <p><?php if ($job['company_logo']): ?><img class="company-logo" src="<?= BASE_URL . '/' . e($job['company_logo']) ?>" alt="โลโก้ <?= e($job['company_name']) ?>"><?php endif; ?><?= e($job['company_name']) ?> <em>✓</em></p>
                        <div class="job-meta">⌖ <?= e($job['location']) ?><br>◷ <?= e($job['work_date']) ?></div>
                        <strong class="pay mt-auto"><?= pay_text($job) ?></strong>
                    </div>
                </a>
            </div>
        <?php endforeach ?>
    </div>
    <?php if (!$jobs): ?><p class="text-secondary mt-4">ไม่พบงานที่ตรงกับเงื่อนไข ลองค้นหาใหม่อีกครั้ง</p><?php endif ?>
</main><?php require APP_ROOT . '/partials/footer.php'; ?>
