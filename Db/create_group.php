<?php
// เปิดการแสดงข้อผิดพลาด (เหมาะสำหรับตอนพัฒนา)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ส่วนจัดการ CORS ---
// อนุญาตให้ Request มาจากทุกที่ (Origin)
header("Access-Control-Allow-Origin: *");
// อนุญาตให้ใช้เมธอด POST, GET, และ OPTIONS
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
// อนุญาตให้มี Headers อะไรบ้างที่ส่งมาได้
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// ตั้งค่าประเภทของเนื้อหาที่จะตอบกลับไป เป็น JSON
header("Content-Type: application/json; charset=UTF-8");

// ส่วนสำคัญ: จัดการกับ Preflight Request (OPTIONS) ที่เบราว์เซอร์ส่งมาถามก่อน
// ถ้าเป็นเมธอด OPTIONS ให้ตอบกลับไปด้วยสถานะ "OK" (200) แล้วจบการทำงานทันที
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- จบส่วนจัดการ CORS ---

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
include 'connectdb.php';

// รับข้อมูลที่ส่งมาแบบ JSON
$data = json_decode(file_get_contents("php://input"));

// ตรวจสอบข้อมูลเบื้องต้น
if (empty($data->group_name) || empty($data->created_by)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data: Group name and creator ID are required."]);
    exit();
}

try {
    // เชื่อมต่อฐานข้อมูลด้วย PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // เริ่ม Transaction เพื่อให้แน่ใจว่าทั้ง 2 คำสั่งจะสำเร็จไปด้วยกัน
    $pdo->beginTransaction();

    // 1. สร้างกลุ่มใหม่ในตาราง 'groups'
    $sql_create_group = "INSERT INTO `groups` (group_name, description, created_by) VALUES (:group_name, :description, :created_by)";
    $stmt_create_group = $pdo->prepare($sql_create_group);
    $stmt_create_group->execute([
        ':group_name' => $data->group_name,
        ':description' => $data->description ?? '', // ใช้ Null Coalescing Operator กัน Error ถ้าไม่มี description
        ':created_by' => $data->created_by
    ]);

    // ดึง group_id ของกลุ่มที่เพิ่งสร้างล่าสุด
    $new_group_id = $pdo->lastInsertId();

    // 2. เพิ่มผู้สร้างกลุ่มเข้าไปเป็นสมาชิกคนแรกในตาราง 'group_members' โดยให้มี role เป็น 'admin'
    $sql_add_admin = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'admin')";
    $stmt_add_admin = $pdo->prepare($sql_add_admin);
    $stmt_add_admin->execute([
        ':group_id' => $new_group_id,
        ':user_id' => $data->created_by
    ]);

    // ยืนยันการทำ Transaction (บันทึกข้อมูลทั้งหมดลงฐานข้อมูลจริง)
    $pdo->commit();

    // ตอบกลับไปว่าสำเร็จ พร้อมกับ group_id ใหม่
    http_response_code(201); // 201 Created
    echo json_encode(["success" => true, "message" => "Group created successfully.", "group_id" => $new_group_id]);

} catch (PDOException $e) {
    // หากเกิดข้อผิดพลาดขึ้นระหว่างทาง ให้ยกเลิกการกระทำทั้งหมด
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // ตอบกลับไปว่ามีข้อผิดพลาดเกิดขึ้นที่ฝั่งเซิร์ฟเวอร์
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["success" => false, "error" => "Database Transaction Failed: " . $e->getMessage()]);
}
?>