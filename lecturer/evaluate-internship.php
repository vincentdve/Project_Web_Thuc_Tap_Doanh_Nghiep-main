<?php
require __DIR__ . '/_common.php';

$user_id = (int)$_SESSION['account_id'];
$internship_id = filter_input(INPUT_GET, 'internship_id', FILTER_VALIDATE_INT);
$message = null;
$message_type = 'success';

if (!$internship_id) {
    header('Location: internships.php');
    exit;
}

$stmt = $con->prepare('SELECT i.id, i.status, s.full_name AS student_name, s.student_code, c.company_name, p.title AS position_title, e.score, e.comments FROM internships i JOIN lecturers l ON l.id = i.lecturer_id JOIN students s ON s.id = i.student_id JOIN companies c ON c.id = i.company_id JOIN internship_posts p ON p.id = i.post_id LEFT JOIN evaluations e ON e.internship_id = i.id AND e.evaluator_user_id = ? AND e.evaluator_type = "lecturer" WHERE i.id = ? AND l.user_id = ? AND i.status <> "cancelled"');
$stmt->bind_param('iii', $user_id, $internship_id, $user_id);
$stmt->execute();
$internship = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$internship) {
    header('Location: internships.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = trim($_POST['score'] ?? '');
    $comments = trim($_POST['comments'] ?? '');

    if ($score === '' || !is_numeric($score) || (float)$score < 0 || (float)$score > 10 || $comments === '') {
        $message = 'Vui lòng nhập điểm từ 0 đến 10 và nhận xét.';
        $message_type = 'error';
    } else {
        $score_value = number_format((float)$score, 2, '.', '');

        $stmt = $con->prepare('INSERT INTO evaluations (internship_id, evaluator_user_id, evaluator_type, score, comments, status) VALUES (?, ?, "lecturer", ?, ?, "submitted") ON DUPLICATE KEY UPDATE score = VALUES(score), comments = VALUES(comments), report_id = NULL, status = "submitted"');
        $stmt->bind_param('iiss', $internship_id, $user_id, $score_value, $comments);
        $saved = $stmt->execute();
        $stmt->close();

        if ($saved) {
            $message = 'Đã lưu điểm tổng kết kỳ thực tập.';
            $internship['score'] = $score_value;
            $internship['comments'] = $comments;
        } else {
            $message = 'Không thể lưu điểm tổng kết.';
            $message_type = 'error';
        }
    }
}

lecturer_header('Đánh giá tổng kết');
?>

<div class="page-title"><h2>Đánh giá tổng kết thực tập</h2><p>Giảng viên đánh giá kết quả thực tập của sinh viên.</p></div>

<?php if ($message): ?><p class="upload-message <?=lecturer_e($message_type)?>"><?=lecturer_e($message)?></p><?php endif; ?>

<div class="block">
    <h3>Thông tin sinh viên</h3>
    <p><strong>Họ tên:</strong> <?=lecturer_e($internship['student_name'])?></p>
    <p><strong>Mã sinh viên:</strong> <?=lecturer_e($internship['student_code'])?></p>
    <p><strong>Doanh nghiệp:</strong> <?=lecturer_e($internship['company_name'])?></p>
    <p><strong>Vị trí thực tập:</strong> <?=lecturer_e($internship['position_title'])?></p>
</div>

<div class="block">
    <h3>Điểm tổng kết của giảng viên</h3>
    <form method="post">
        <div class="form-group">
            <label>Điểm tổng kết (0–10)</label>
            <input class="form-input" type="number" name="score" min="0" max="10" step="0.01" value="<?=lecturer_e($internship['score'] ?? '')?>" required>
        </div>

        <div class="form-group">
            <label style="display:block; margin-bottom:8px;">Nhận xét tổng kết</label>
            <textarea class="form-input" name="comments" rows="5" style="display:block; width:100%; box-sizing:border-box; padding:10px;" required><?=lecturer_e($internship['comments'] ?? '')?></textarea>
        </div>

        <button class="btn" type="submit">Lưu điểm tổng kết</button>
        <a class="btn" href="internships.php">Quay lại danh sách</a>
    </form>
</div>

<?php lecturer_footer(); ?>