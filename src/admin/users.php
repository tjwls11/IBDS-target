<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['is_admin'])) {
    header('Location: /login.php');
    exit;
}

require __DIR__ . '/../includes/header.php';

$result = mysqli_query($conn, 'SELECT id, username, email, password, is_admin, created_at FROM users ORDER BY id ASC');
?>
<h2 class="page-title">관리자 - 전체 회원 목록</h2>
<div class="card" style="padding:8px 24px;">
<table class="board-table">
<tr><th class="num">ID</th><th>아이디</th><th>이메일</th><th>비밀번호</th><th>관리자</th><th>가입일</th></tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td class="num"><?php echo (int) $row['id']; ?></td>
  <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?></td>
  <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?></td>
  <td><?php echo htmlspecialchars($row['password'], ENT_QUOTES); ?></td>
  <td><?php echo $row['is_admin'] ? '<span class="badge badge-notice">Y</span>' : 'N'; ?></td>
  <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
