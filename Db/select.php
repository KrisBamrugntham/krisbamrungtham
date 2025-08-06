<?php
// ปิด error
ini_set('display_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "education_db");
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Connection failed"]);
  exit;
}

// ดึงข้อมูลจากตาราง users
$sql = "SELECT user_id, username, email, password_hash, avatar_url, created_at FROM users";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}

// ส่ง JSON กลับ
echo json_encode($users);

// ห้ามมีอะไรหลังจากนี้เด็ดขาด
$conn->close();
exit;
