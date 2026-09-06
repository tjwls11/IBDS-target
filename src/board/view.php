<?php
require_once __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? '';

$sql = "SELECT posts.*, users.username, users.nickname
        FROM posts JOIN users ON posts.user_id = users.id
        WHERE posts.id=" . $id;
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo '<div class="alert alert-error">쿼리 오류: ' . mysqli_error($conn) . '</div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo '<div class="card">게시글을 찾을 수 없습니다.</div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

mysqli_query($conn, "UPDATE posts SET view_count = view_count + 1 WHERE id=" . $id);
?>
<div class="card">
<div class="post-header">
<span class="badge badge-<?php echo $post['category'] === 'notice' ? 'notice' : 'free'; ?>"><?php echo $post['category'] === 'notice' ? '공지' : '자유'; ?></span>
<input type="text" class="post-title-input" value="<?php echo $post['title']; ?>" readonly>
<div class="post-meta">
  <span>작성자 <a href="/profile.php?user=<?php echo urlencode($post['username']); ?>"><?php echo htmlspecialchars($post['nickname'], ENT_QUOTES); ?></a></span>
  <span>조회 <?php echo (int) $post['view_count'] + 1; ?></span>
  <span><?php echo htmlspecialchars($post['created_at'], ENT_QUOTES); ?></span>
</div>
</div>
<div class="post-content">
<h1><?php echo $post['title']; ?></h1>
<p><?php echo nl2br($post['content']); ?></p>
</div>

<div class="comment-section">
<h3>댓글</h3>
<?php
$cid = (int) $post['id'];
$csql = "SELECT comments.content, comments.created_at, users.nickname
         FROM comments JOIN users ON comments.user_id = users.id
         WHERE comments.post_id=" . $cid . " ORDER BY comments.created_at ASC";
$cresult = mysqli_query($conn, $csql);
while ($crow = mysqli_fetch_assoc($cresult)):
?>
<div class="comment">
  <span class="comment-author"><?php echo htmlspecialchars($crow['nickname'], ENT_QUOTES); ?></span>
  <span class="comment-date"><?php echo htmlspecialchars($crow['created_at'], ENT_QUOTES); ?></span>
  <div class="comment-body"><?php echo $crow['content']; ?></div>
</div>
<?php endwhile; ?>

<?php if (isset($_SESSION['user_id'])): ?>
<form method="post" action="/board/comment.php" style="margin-top:16px;">
  <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
  <textarea name="content" rows="3" placeholder="댓글을 입력하세요"></textarea>
  <div style="margin-top:8px;"><button type="submit" class="btn btn-sm">댓글 작성</button></div>
</form>
<?php else: ?>
<p class="helper-text"><a href="/login.php">로그인</a> 후 댓글을 작성할 수 있습니다.</p>
<?php endif; ?>
</div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
