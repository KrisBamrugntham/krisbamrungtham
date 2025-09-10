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

if (!isset($_GET['group_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Group ID is required."]);
    exit();
}
$groupId = (int)$_GET['group_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- 💡 จุดแก้ไข: เปลี่ยนไปใช้ตาราง group_messages และ WHERE m.group_id ---
    $sql = "
        SELECT m.message_id, m.message, m.sent_at, u.user_id, u.username, u.avatar_url
        FROM group_messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.group_id = :group_id
        ORDER BY m.sent_at ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $messages]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>