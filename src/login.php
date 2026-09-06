<?php
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username='" . $username . "' AND password='" . $password . "'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (int) $user['is_admin'];
        header('Location: /index.php');
        exit;
    } else {
        $error = '로그인 실패';
    }
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="page-title">로그인</h2>
<div class="card" style="max-width:380px;margin:0 auto;">
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>
<form method="post" action="/login.php">
  <div class="field"><label>아이디</label><input type="text" name="username"></div>
  <div class="field"><label>비밀번호</label><input type="password" name="password"></div>
  <button type="submit" class="btn" style="width:100%;">로그인</button>
</form>
<p class="helper-text">계정이 없으신가요? <a href="/register.php">회원가입</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
