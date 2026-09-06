<?php
require_once __DIR__ . '/config/db.php';
require __DIR__ . '/includes/header.php';

$sql = "SELECT posts.id, posts.title, posts.category, posts.created_at, users.nickname
        FROM posts JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC LIMIT 10";
$result = mysqli_query($conn, $sql);
?>
<h2 class="page-title">최신 게시글</h2>
<div class="card" style="padding:8px 24px;">
<table class="board-table">
<tr><th>제목</th><th>카테고리</th><th>작성자</th><th>작성일</th></tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><a href="/board/view.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?></a></td>
  <td><span class="badge badge-<?php echo $row['category'] === 'notice' ? 'notice' : 'free'; ?>"><?php echo htmlspecialchars($row['category'] === 'notice' ? '공지' : '자유', ENT_QUOTES); ?></span></td>
  <td><?php echo htmlspecialchars($row['nickname'], ENT_QUOTES); ?></td>
  <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
