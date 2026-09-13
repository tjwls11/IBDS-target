<?php
require_once __DIR__ . '/config/db.php';
require __DIR__ . '/includes/header.php';

$q = $_GET['q'] ?? '';
$result = null;

if ($q !== '') {
    $sql = "SELECT posts.id, posts.title, posts.category, posts.created_at, users.nickname
            FROM posts JOIN users ON posts.user_id = users.id
            WHERE posts.title LIKE '%" . $q . "%'
            ORDER BY posts.created_at DESC";
    $result = mysqli_query($conn, $sql);
}

// 최근 검색어 저장: 응답엔 raw로 에코, 저장은 escape (목록에는 실행 불가 형태로만 남음)
$saved_keyword = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = $_POST['keyword'] ?? '';
    $saved_keyword = $keyword;                              // 응답에 raw 에코 (실행 가능 - 의도된 에코백)
    $safe_keyword = htmlspecialchars($keyword, ENT_QUOTES);  // 저장은 escape
    $stmt = mysqli_prepare($conn, 'INSERT INTO search_keywords (content) VALUES (?)');
    mysqli_stmt_bind_param($stmt, 's', $safe_keyword);
    mysqli_stmt_execute($stmt);
}
?>
<h2 class="page-title">게시글 검색</h2>
<form method="get" action="/search.php" class="search-bar">
  <input type="text" name="q" value="<?php echo $q; ?>" placeholder="검색어를 입력하세요">
  <button type="submit" class="btn">검색</button>
</form>

<?php if ($q !== ''): ?>
<p class="helper-text">'<?php echo $q; ?>'에 대한 검색결과</p>
<?php endif; ?>

<?php if ($result): ?>
<div class="card" style="padding:8px 24px;">
<table class="board-table">
<tr><th>제목</th><th>카테고리</th><th>작성자</th><th>작성일</th></tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><a href="/board/view.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?></a></td>
  <td><span class="badge badge-<?php echo $row['category'] === 'notice' ? 'notice' : 'free'; ?>"><?php echo $row['category'] === 'notice' ? '공지' : '자유'; ?></span></td>
  <td><?php echo htmlspecialchars($row['nickname'], ENT_QUOTES); ?></td>
  <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php elseif ($q !== ''): ?>
<div class="alert alert-error">쿼리 오류: <?php echo mysqli_error($conn); ?></div>
<?php endif; ?>

<h3 class="page-title" style="margin-top:32px;">최근 검색어</h3>
<?php if ($saved_keyword !== null): ?>
<div class="alert alert-success">저장된 검색어: <?php echo $saved_keyword; ?></div>
<?php endif; ?>
<form method="post" action="/search.php" class="search-bar">
  <input type="text" name="keyword" placeholder="검색어를 저장하세요">
  <button type="submit" class="btn">저장</button>
</form>
<div class="card" style="padding:8px 24px;">
<?php
$kw = mysqli_query($conn, 'SELECT content, created_at FROM search_keywords ORDER BY id DESC LIMIT 20');
while ($krow = mysqli_fetch_assoc($kw)):
?>
  <div class="comment"><div class="comment-body"><?php echo $krow['content']; ?></div></div>
<?php endwhile; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
