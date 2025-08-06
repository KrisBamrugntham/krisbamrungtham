<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงข้อมูลทั้งหมดจากตาราง users
    // แก้ไขจาก `id` เป็น `user_id`
    $stmt = $pdo->prepare("SELECT user_id, username, email, gender, interest FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
}
?>