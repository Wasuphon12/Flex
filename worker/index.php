<?php
require_once __DIR__ . '/../config/config.php';
require_login('worker');
$jobs = db()->query("SELECT j.job_id AS id, j.job_title AS title, jc.category_slug AS job_type, j.work_location AS location, j.work_schedule AS work_date, j.pay_amount, j.pay_unit, ep.company_name, ep.company_logo_path AS company_logo, (SELECT ji.image_file_path FROM job_images ji WHERE ji.job_id = j.job_id ORDER BY ji.display_order LIMIT 1) AS cover_image FROM jobs j JOIN employer_profiles ep ON ep.user_id = j.employer_user_id JOIN job_categories jc ON jc.job_category_id = j.job_category_id WHERE j.job_status = 'published' ORDER BY j.created_at DESC LIMIT 6")->fetchAll();
$pageTitle = 'งานสำหรับคุณ | FLEXJOB';
$pageStyles = ['worker-index', 'worker-profile-guide', 'worker-how'];
require APP_ROOT . '/partials/header.php'; ?>

<main class="worker-home container">
    <section class="profile-guide-banner">
        <img src="<?= BASE_URL ?>/assets/images/worker-profile-guide-v2.png" alt="แนะนำการเพิ่ม Resume และ Portfolio ในโปรไฟล์">
        <div class="profile-guide-copy"><p class="eyebrow">COMPLETE YOUR PROFILE</p><h2>เพิ่มโปรไฟล์ให้โดดเด่น<br>เพื่อให้ผู้ว่าจ้างเห็นคุณมากขึ้น</h2><p>อัปโหลด Resume และ Portfolio โดยรวม Certificate ไว้ใน Portfolio เพื่อเพิ่มโอกาสได้รับการติดต่อ</p></div>
    </section>
    <div class="worker-welcome d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3">
        <div>
            <p class="eyebrow">WORKER HOME</p>
            <p>ค้นหาโอกาสใหม่ที่เข้ากับเวลาและทักษะของคุณ</p>
        </div><a class="btn btn-success px-4 py-2" href="<?= BASE_URL ?>/jobs.php">ค้นหางานทั้งหมด</a>
    </div>
    <section class="mt-5">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4">
            <div>
                <h2>งานแนะนำสำหรับคุณ</h2>
            </div><a class="text-link green" href="<?= BASE_URL ?>/jobs.php">ดูงานทั้งหมด →</a>
        </div>
        <div class="row g-4"><?php foreach ($jobs as $job): $icon = $job['job_type'] === 'event' ? '✦' : ($job['job_type'] === 'freelance' ? '⌁' : '◷'); ?><div class="col-12 col-md-6 col-xl-4"><a class="card h-100 worker-job-card" href="<?= BASE_URL ?>/job.php?id=<?= $job['id'] ?>"><?php if ($job['cover_image']): ?><img class="card-img-top worker-job-image" src="<?= BASE_URL . '/' . e($job['cover_image']) ?>" alt="<?= e($job['title']) ?>"><?php else: ?><div class="worker-job-image worker-job-fallback"><?= $icon ?></div><?php endif ?><div class="card-body worker-job-body d-flex flex-column">
                        <h3><?= e($job['title']) ?></h3>
                        <p><?php if ($job['company_logo']): ?><img class="company-logo" src="<?= BASE_URL . '/' . e($job['company_logo']) ?>" alt="โลโก้ <?= e($job['company_name']) ?>"><?php endif; ?><?= e($job['company_name']) ?> · ✓ ยืนยันแล้ว</p>
                        <p class="worker-job-meta">⌖ <?= e($job['location']) ?><br>◷ <?= e($job['work_date']) ?></p>
                        <div class="worker-job-bottom mt-auto"><strong><?= pay_text($job) ?></strong><span><?= job_type($job['job_type']) ?></span></div>
                    </div></a></div><?php endforeach ?></div>
    </section>

    <section class="worker-how" aria-labelledby="worker-how-title">
        <div class="row align-items-center g-5">
        <div class="col-12 col-md-5">
        <div class="worker-how-visual" aria-hidden="true">
            <div class="worker-how-phone">
                <b>FLEXJOB</b>
                <h3>สวัสดี 👋</h3>
                <p>งานใหม่สำหรับคุณ</p>
                <div><span>✦</span><b>Event Staff</b><small>฿900 / วัน</small></div>
                <div><span>⌁</span><b>Content Creator</b><small>฿1,500 / งาน</small></div>
            </div>
        </div>
        </div>
        <div class="col-12 col-md-7">
        <div class="worker-how-content">
            <p class="eyebrow">HOW FLEXJOB WORKS</p>
            <h2 id="worker-how-title">หางานง่าย<br>จบในไม่กี่ขั้นตอน</h2>
            <ol>
                <li><span>01</span><div><b>สร้างโปรไฟล์ของคุณ</b><p>เพิ่มข้อมูล ทักษะ และ Resume เพื่อให้ผู้ว่าจ้างรู้จักคุณ</p></div></li>
                <li><span>02</span><div><b>เลือกงานที่สนใจ</b><p>ค้นหาและกรองงานตามเวลา พื้นที่ และค่าจ้าง</p></div></li>
                <li><span>03</span><div><b>สมัคร แล้วเริ่มงาน</b><p>ติดตามผลสมัครและรับข้อเสนอผ่านหน้าเดียว</p></div></li>
            </ol>
        </div>
        </div>
        </div>
    </section>

</main>

<?php require APP_ROOT . '/partials/footer.php'; ?>
