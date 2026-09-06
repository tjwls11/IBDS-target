<?php
// PHP 8.1+ 기본값(예외 throw) 대신 예전 방식(false 반환 + mysqli_error())을 사용
mysqli_report(MYSQLI_REPORT_OFF);

$db_host = getenv('DB_HOST') ?: 'db';
$db_name = getenv('DB_NAME') ?: 'target_web';
$db_user = getenv('DB_USER') ?: 'target_user';
$db_pass = getenv('DB_PASS') ?: 'target_pass';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('DB 연결 실패: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
