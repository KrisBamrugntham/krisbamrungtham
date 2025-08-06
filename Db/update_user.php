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

function send_json_error($message, $code = 500, $details = null) {
    http_response_code($code);
    $response = ["success" => false, "error" => $message];
    if ($details) $response['details'] = $details;
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

$data = json_decode(file_get_contents("php://input"));
if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_error("Invalid JSON", 400);
}
if (empty($data->user_id) || empty($data->username) || empty($data->email)) {
    send_json_error("Missing required data", 400);
}

include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- 💡 จุดแก้ไข: เพิ่มการอัปเดต status และ suspended_until ---
    $sql = "UPDATE users SET 
                username = :username, 
                email = :email, 
                gender = :gender, 
                interest = :interest,
                role = :role,
                status = :status,
                suspended_until = :suspended_until
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);

    // ถ้าสถานะไม่ใช่ 'suspended' ให้ตั้งค่า suspended_until เป็น NULL
    $suspended_date = ($data->status === 'suspended' && !empty($data->suspended_until)) ? $data->suspended_until : NULL;

    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':gender', $data->gender);
    $stmt->bindParam(':interest', $data->interest);
    $stmt->bindParam(':role', $data->role);
    $stmt->bindParam(':status', $data->status);
    $stmt->bindParam(':suspended_until', $suspended_date);
    $stmt->bindParam(':user_id', $data->user_id);
    // --- จบจุดแก้ไข ---

    $stmt->execute();

    echo json_encode(["success" => true, "message" => "User updated successfully"]);

} catch (PDOException $e) {
    send_json_error("Database Error", 500, $e->getMessage());
}
?>