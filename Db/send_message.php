<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('connectdb.php');

function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->sender_id) || !isset($data->receiver_id) || !isset($data->message) || trim($data->message) === '') {
    send_json_error("Incomplete or empty data provided.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO private_chats (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sender_id' => $data->sender_id,
        ':receiver_id' => $data->receiver_id,
        ':message' => $data->message
    ]);

    if ($stmt->rowCount() > 0) {
        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Message sent successfully"]);
    } else {
        send_json_error("Failed to save the message.", 500);
    }

} catch (PDOException $e) {
    send_json_error("Database Error: " . $e->getMessage(), 500);
}
?>