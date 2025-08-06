<?php
// ตั้งค่า header สำหรับ API
header("Content-Type: application/json; charset=UTF-8");

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = "localhost";
$dbname = "education_db";
$username = "root";
$password = "";

try {
    // สร้าง PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // สร้างคำสั่ง SQL
    $sql = "SELECT * FROM users"; // เปลี่ยน 'users' เป็นชื่อตารางของคุณ

    // เตรียมและ execute
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // ดึงข้อมูลทั้งหมด
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งผลลัพธ์เป็น JSON
    echo json_encode([
        "status" => "success",
        "data" => $result
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
