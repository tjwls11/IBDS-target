<?php
require_once __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$category = $_GET['category'] ?? 'free';

$sql = "SELECT posts.id, posts.title, posts.view_count, posts.created_at, users.nickname
        FROM posts JOIN users ON posts.user_id = users.id
        WHERE posts.category='" . $category . "'
        ORDER BY posts.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
<h2 class="page-title" style="margin:0;"><?php echo $category === 'notice' ? '공지사항' : '자유게시판'; ?></h2>
<a class="btn btn-sm" href="/board/write.php">글쓰기</a>
</div>
<div class="card" style="padding:8px 24px;">
<table class="board-table">
<tr><th class="num">번호</th><th>제목</th><th>작성자</th><th class="num">조회수</th><th>작성일</th></tr>
<?php if ($result): $n = 1; while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td class="num"><?php echo $n++; ?></td>
  <td><a href="/board/view.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?></a></td>
  <td><?php echo htmlspecialchars($row['nickname'], ENT_QUOTES); ?></td>
  <td class="num"><?php echo (int) $row['view_count']; ?></td>
  <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="5">오류: <?php echo mysqli_error($conn); ?></td></tr>
<?php endif; ?>
</table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
