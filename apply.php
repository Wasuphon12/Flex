<?php
require_once __DIR__ . '/config/config.php';
require_login('worker');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('jobs.php');
$jobId = (int)$_POST['job_id'];
$check = db()->prepare("SELECT job_id FROM jobs WHERE job_id=? AND job_status='published'");
$check->execute([$jobId]);
if (!$check->fetch()) {
    flash('error', 'งานนี้ไม่เปิดรับสมัครแล้ว');
    redirect('jobs.php');
}
$profile = db()->prepare('SELECT resume_file_path FROM worker_profiles WHERE user_id=?');
$profile->execute([user()['id']]);
try {
    db()->prepare('INSERT INTO applications (job_id,worker_user_id,cover_note,resume_file_path) VALUES (?,?,?,?)')->execute([$jobId, user()['id'], trim($_POST['cover_note']), $profile->fetchColumn()]);
    flash('success', 'ส่งใบสมัครเรียบร้อยแล้ว ผู้ว่าจ้างจะติดต่อกลับผ่านระบบ');
} catch (PDOException) {
    flash('error', 'คุณสมัครงานนี้ไปแล้ว');
}
redirect('worker/dashboard.php');
