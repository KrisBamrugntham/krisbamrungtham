<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('connectdb.php');

if (!isset($_GET['user_id']) || !isset($_GET['friend_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User ID and Friend ID are required."]);
    exit();
}

$userId = (int)$_GET['user_id'];
$friendId = (int)$_GET['friend_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT chat_id, sender_id, receiver_id, message, sent_at
        FROM private_chats
        WHERE (sender_id = :user_id AND receiver_id = :friend_id)
           OR (sender_id = :friend_id AND receiver_id = :user_id)
        ORDER BY sent_at ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId, ':friend_id' => $friendId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $messages]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>