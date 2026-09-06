<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$post_id = (int) ($_POST['post_id'] ?? 0);
$content = $_POST['content'] ?? '';
$user_id = (int) $_SESSION['user_id'];

// 단순 블랙리스트 필터: <script> 태그만 대소문자 무시하고 제거 (다른 벡터는 미검사)
$content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);

$sql = "INSERT INTO comments (post_id, user_id, content) VALUES ("
    . $post_id . ", " . $user_id . ", '" . $content . "')";
mysqli_query($conn, $sql);

header('Location: /board/view.php?id=' . $post_id);
exit;
