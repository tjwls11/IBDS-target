<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logged_in = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IBDS 커뮤니티</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
<div class="inner">
<a class="brand" href="/index.php">IBDS 커뮤니티</a>
<nav class="main-nav">
<a href="/board/list.php?category=free">자유게시판</a>
<a href="/board/list.php?category=notice">공지사항</a>
<a href="/search.php">검색</a>
<span class="divider"></span>
<?php if ($logged_in): ?>
<a href="/board/write.php">글쓰기</a>
<a href="/mypage.php">마이페이지</a>
<?php if (!empty($_SESSION['is_admin'])): ?>
<a href="/admin/users.php">관리자</a>
<?php endif; ?>
<a href="/logout.php">로그아웃</a>
<span class="user-chip"><?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES); ?></span>
<?php else: ?>
<a href="/login.php">로그인</a>
<a href="/register.php">회원가입</a>
<?php endif; ?>
</nav>
</div>
</header>
<main class="container">
