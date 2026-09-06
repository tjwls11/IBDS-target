<?php
require_once __DIR__ . '/config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    $sql = "INSERT INTO users (username, password, email, nickname) VALUES ('"
        . $username . "', '" . $password . "', '" . $email . "', '" . $username . "')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header('Location: /login.php?registered=1');
        exit;
    } else {
        $message = '가입 실패: ' . mysqli_error($conn);
    }
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="page-title">회원가입</h2>
<div class="card" style="max-width:380px;margin:0 auto;">
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message; ?></div>
<?php endif; ?>
<form method="post" action="/register.php">
  <div class="field"><label>아이디</label><input type="text" name="username"></div>
  <div class="field"><label>비밀번호</label><input type="password" name="password"></div>
  <div class="field"><label>이메일</label><input type="text" name="email"></div>
  <button type="submit" class="btn" style="width:100%;">가입하기</button>
</form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
