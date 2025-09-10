<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ส่วนจัดการ CORS ที่ถูกต้อง ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- จบส่วนจัดการ CORS ---

include('connectdb.php');

$data = json_decode(file_get_contents("php://input"));

if (empty($data->group_id) || !isset($data->sender_id) || empty(trim($data->message))) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // เราได้เปลี่ยนไปใช้ตาราง group_messages ตั้งแต่แรกแล้ว
    // ตรวจสอบให้แน่ใจว่าตารางนี้มีอยู่จริง
    $sql = "INSERT INTO group_messages (group_id, sender_id, message) VALUES (:group_id, :sender_id, :message)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $data->group_id,
        ':sender_id' => $data->sender_id,
        ':message' => $data->message
    ]);

    echo json_encode(["success" => true, "message" => "Message sent successfully."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>