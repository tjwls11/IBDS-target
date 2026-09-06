<?php
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST['nickname'] ?? '';
    $bio = $_POST['bio'] ?? '';

    $stmt = mysqli_prepare($conn, 'UPDATE users SET nickname = ?, bio = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'ssi', $nickname, $bio, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $message = '수정이 완료되었습니다.';
}

$stmt = mysqli_prepare($conn, 'SELECT nickname, bio, email FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

require __DIR__ . '/includes/header.php';
?>
<h2 class="page-title">마이페이지</h2>
<div class="card" style="max-width:480px;">
<?php if ($message): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES); ?></div>
<?php endif; ?>
<p class="helper-text" style="margin-top:0;">이메일: <?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?></p>
<form method="post" action="/mypage.php">
  <div class="field"><label>닉네임</label><input type="text" name="nickname" value="<?php echo htmlspecialchars($user['nickname'], ENT_QUOTES); ?>"></div>
  <div class="field"><label>자기소개</label><textarea name="bio" rows="5"><?php echo htmlspecialchars($user['bio'] ?? '', ENT_QUOTES); ?></textarea></div>
  <button type="submit" class="btn">저장</button>
</form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
