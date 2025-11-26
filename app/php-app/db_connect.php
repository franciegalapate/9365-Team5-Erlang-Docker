<?php
$host = getenv("DB_HOST") ?: "db";
$user = getenv("DB_USER") ?: "appuser";
$pass = getenv("DB_PASS") ?: "apppass";
$db   = getenv("DB_NAME") ?: "db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
