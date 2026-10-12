<?php
require __DIR__ . '/_common.php';

$message = null;
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $lecturer_id = filter_input(INPUT_POST, 'lecturer_id', FILTER_VALIDATE_INT);

    if (!$internship_id || !$lecturer_id) {
        $message = 'Vui lòng chọn giảng viên hướng dẫn.';
        $message_type = 'error';
    } else {
        $stmt = $con->prepare('SELECT id FROM lecturers WHERE id = ?');
        $stmt->bind_param('i', $lecturer_id);
        $stmt->execute();
        $lecturer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $con->prepare('SELECT id FROM internships WHERE id = ? AND status <> "cancelled"');
        $stmt->bind_param('i', $internship_id);
        $stmt->execute();
        $internship = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$lecturer || !$internship) {
            $message = 'Giảng viên hoặc kỳ thực tập không hợp lệ.';
            $message_type = 'error';
        } else {
            $stmt = $con->prepare('UPDATE internships SET lecturer_id = ? WHERE id = ? AND status <> "cancelled"');
            $stmt->bind_param('ii', $lecturer_id, $internship_id);
            $saved = $stmt->execute();
            $stmt->close();

            if ($saved) {
                $message = 'Đã lưu phân công giảng viên hướng dẫn.';
            } else {
                $message = 'Không thể lưu phân công.';
                $message_type = 'error';
            }
        }
    }
}

$stmt = $con->prepare('SELECT id, lecturer_code, full_name FROM lecturers ORDER BY full_name ASC');
$stmt->execute();
$lecturers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $con->prepare('SELECT i.id, i.status, i.lecturer_id, s.student_code, s.full_name AS student_name, c.company_name, p.title AS position_title, l.full_name AS lecturer_name FROM internships i JOIN students s ON s.id = i.student_id JOIN companies c ON c.id = i.company_id JOIN internship_posts p ON p.id = i.post_id LEFT JOIN lecturers l ON l.id = i.lecturer_id WHERE i.status <> "cancelled" ORDER BY i.created_at DESC');
$stmt->execute();
$internships = $stmt->get_result();
$stmt->close();

$status_labels = ['planned' => 'Chưa bắt đầu', 'ongoing' => 'Đang thực tập', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];

admin_header('Phân công giảng viên');
?>

<div class="page-title"><h2>Phân công giảng viên hướng dẫn</h2><p>Phân công hoặc thay đổi giảng viên hướng dẫn cho sinh viên thực tập.</p></div>

<?php if ($message): ?><p class="upload-message <?=admin_e($message_type)?>"><?=admin_e($message)?></p><?php endif; ?>

<div class="block">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Sinh viên</th>
                    <th>Doanh nghiệp</th>
                    <th>Vị trí thực tập</th>
                    <th>Trạng thái</th>
                    <th>Giảng viên hiện tại</th>
                    <th>Phân công</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($internships->num_rows === 0): ?>
                    <tr><td colspan="6">Chưa có kỳ thực tập nào.</td></tr>
                <?php else: while ($internship = $internships->fetch_assoc()): ?>
                    <tr>
                        <td><?=admin_e($internship['student_name'])?><br><small><?=admin_e($internship['student_code'])?></small></td>
                        <td><?=admin_e($internship['company_name'])?></td>
                        <td><?=admin_e($internship['position_title'])?></td>
                        <td><?=admin_e($status_labels[$internship['status']] ?? $internship['status'])?></td>
                        <td><?=admin_e($internship['lecturer_name'] ?: 'Chưa phân công')?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="internship_id" value="<?=admin_e($internship['id'])?>">
                                <select class="form-input" name="lecturer_id" required>
                                    <option value="">Chọn giảng viên</option>
                                    <?php foreach ($lecturers as $lecturer): ?>
                                        <option value="<?=admin_e($lecturer['id'])?>" <?=((int)$internship['lecturer_id'] === (int)$lecturer['id']) ? 'selected' : ''?>><?=admin_e($lecturer['full_name'])?> (<?=admin_e($lecturer['lecturer_code'])?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn" type="submit">Lưu phân công</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>