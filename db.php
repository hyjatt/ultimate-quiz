<?php
/*
 * Use environment variables in production. The fallback values keep local
 * XAMPP development working, but must not be used on the hosted server.
 *
 * Required production variables:
 * DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, and optionally DB_PORT
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db = getenv('DB_NAME') ?: 'ultimatequiz';
$user = getenv('DB_USER') ?: 'ultimatequiza_user';
$pass = getenv('DB_PASSWORD') ?: 'Izzatjek966_';
$port = (int) (getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_errno) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(503);
    exit('The quiz is temporarily unavailable. Please try again later.');
}

$conn->set_charset('utf8mb4');

// Function to log user actions for admin monitoring
function logUserAction($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}
?>
