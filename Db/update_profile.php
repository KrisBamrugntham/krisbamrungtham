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

// ผู้ใช้งานทั่วไปสามารถแก้ไขได้แค่ username, email, gender, interest
// ไม่สามารถแก้ไข role, status, suspended_until ได้
if (isset($data->role) || isset($data->status) || isset($data->suspended_until)) {
    send_json_error("Unauthorized to update role or status.", 403);
}


include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE users SET 
                username = :username, 
                email = :email, 
                gender = :gender, 
                interest = :interest
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':gender', $data->gender);
    $stmt->bindParam(':interest', $data->interest);
    $stmt->bindParam(':user_id', $data->user_id);

    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);

} catch (PDOException $e) {
    send_json_error("Database Error", 500, $e->getMessage());
}
?>