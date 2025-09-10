<?php
// เปิดการแสดงข้อผิดพลาด (เหมาะสำหรับตอนพัฒนา)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 💡 ส่วนจัดการ CORS ที่จำเป็น ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// จัดการกับ Preflight Request (OPTIONS) ที่เบราว์เซอร์ส่งมาถามก่อน
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- จบส่วนจัดการ CORS ---

include 'connectdb.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->group_id) || empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ใช้ INSERT ... ON DUPLICATE KEY UPDATE
    // เพื่อจัดการกรณีที่ผู้ใช้เคยส่งคำขอแล้วถูกปฏิเสธ แล้วต้องการส่งใหม่อีกครั้ง
    $sql = "
        INSERT INTO group_join_requests (group_id, user_id, status) 
        VALUES (:group_id, :user_id, 'pending')
        ON DUPLICATE KEY UPDATE status = 'pending', requested_at = CURRENT_TIMESTAMP";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $data->group_id,
        ':user_id' => $data->user_id
    ]);

    echo json_encode(["success" => true, "message" => "Request sent successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>