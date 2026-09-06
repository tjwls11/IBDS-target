<?php
require_once __DIR__ . '/config/db.php';
require __DIR__ . '/includes/header.php';

$username = $_GET['user'] ?? '';

$sql = "SELECT * FROM users WHERE username='" . $username . "'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo '<div class="alert alert-error">쿼리 오류: ' . mysqli_error($conn) . '</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo '<div class="card">사용자를 찾을 수 없습니다.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM posts WHERE user_id=" . (int) $user['id']);
$postCount = mysqli_fetch_assoc($countResult)['cnt'];
?>
<div class="card">
<div class="profile-header">
<h2 class="page-title" style="margin:0;"><?php echo htmlspecialchars($user['nickname'], ENT_QUOTES); ?></h2>
<span class="username">@<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?></span>
</div>
<p class="profile-stat">작성한 게시글 <?php echo (int) $postCount; ?>개</p>
<h3 style="font-size:0.95rem;margin-bottom:8px;">자기소개</h3>
<div class="bio-box"><?php echo $user['bio']; ?></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
