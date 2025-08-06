<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ฟังก์ชันสำหรับส่ง error
function send_json_error($message, $code = 500) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

// รับข้อมูล user_id ที่จะลบ
$data = json_decode(file_get_contents("php://input"));
if (json_last_error() !== JSON_ERROR_NONE || empty($data->user_id)) {
    send_json_error("Invalid or missing user_id", 400);
}

// เชื่อมต่อฐานข้อมูล
include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $data->user_id, PDO::PARAM_INT);
    
    $stmt->execute();

    // ตรวจสอบว่ามีแถวที่ถูกลบจริงหรือไม่
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "User deleted successfully."]);
    } else {
        send_json_error("User not found or already deleted.", 404);
    }

} catch (PDOException $e) {
    send_json_error("Database Error: " . $e->getMessage());
}
?>