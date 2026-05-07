<?php
$DB_HOST = 'localhost';
$DB_NAME = 'link_locker';
$DB_USER = 'root';
$DB_PASS = '';

function db() {
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
  static $conn;
  if ($conn instanceof mysqli) return $conn;
  mysqli_report(MYSQLI_REPORT_OFF);
  $conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  if ($conn->connect_errno) {
    die('Database connection failed. Import sql/schema.sql first and check config/db.php');
  }
  $conn->set_charset('utf8mb4');
  return $conn;
}
