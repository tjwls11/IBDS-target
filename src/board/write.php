<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'free';
    $user_id = (int) $_SESSION['user_id'];

    $sql = "INSERT INTO posts (user_id, title, content, category) VALUES ("
        . $user_id . ", '" . $title . "', '" . $content . "', '" . $category . "')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $new_id = mysqli_insert_id($conn);
        header('Location: /board/view.php?id=' . $new_id);
        exit;
    } else {
        $error = '작성 실패: ' . mysqli_error($conn);
    }
}

require __DIR__ . '/../includes/header.php';
?>
<h2 class="page-title">게시글 작성</h2>
<div class="card">
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>
<form method="post" action="/board/write.php">
  <div class="field">
    <label>카테고리</label>
    <select name="category">
      <option value="free">자유</option>
      <option value="notice">공지</option>
    </select>
  </div>
  <div class="field"><label>제목</label><input type="text" name="title"></div>
  <div class="field"><label>내용</label><textarea name="content" rows="8"></textarea></div>
  <button type="submit" class="btn">등록</button>
</form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
