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

if (!$data || empty($data->user_id_1) || empty($data->user_id_2) || empty($data->action)) {
    send_json_error("Incomplete data provided.");
}

$userId1 = $data->user_id_1; // ผู้ใช้ที่กำลังล็อกอิน
$userId2 = $data->user_id_2; // ผู้ใช้เป้าหมาย
$action = $data->action;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $message = "";

    switch ($action) {
        case 'add':
            $sql = "INSERT INTO friendships (user_id_1, user_id_2, status) VALUES (:user_id_1, :user_id_2, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id_1' => $userId1, ':user_id_2' => $userId2]);
            $message = "Friend request sent.";
            break;

        case 'cancel':
        case 'unfriend':
            $sql = "DELETE FROM friendships WHERE (user_id_1 = :user_id_1 AND user_id_2 = :user_id_2) OR (user_id_1 = :user_id_2 AND user_id_2 = :user_id_1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id_1' => $userId1, ':user_id_2' => $userId2]);
            $message = $action === 'cancel' ? "Request cancelled." : "Friend removed.";
            break;

        case 'accept':
            // คนรับคำขอคือ user_id_1 (current user) คนส่งคือ user_id_2 (target user)
            $sql = "UPDATE friendships SET status = 'accepted' WHERE user_id_1 = :user_id_2 AND user_id_2 = :user_id_1 AND status = 'pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id_1' => $userId1, ':user_id_2' => $userId2]);
            $message = "Friend request accepted.";
            break;

        case 'reject':
            // คนปฏิเสธคือ user_id_1 (current user) คนส่งคือ user_id_2 (target user)
            $sql = "DELETE FROM friendships WHERE user_id_1 = :user_id_2 AND user_id_2 = :user_id_1 AND status = 'pending'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id_1' => $userId1, ':user_id_2' => $userId2]);
            $message = "Friend request rejected.";
            break;

        default:
            send_json_error("Invalid action.");
            break;
    }

    echo json_encode(["success" => true, "message" => $message]);

} catch (PDOException $e) {
    // ตรวจสอบ lỗi duplicate entry
    if ($e->errorInfo[1] == 1062) {
        send_json_error("A friend request already exists between these users.", 409);
    }
    send_json_error("Database Error: " . $e->getMessage(), 500);
}
?>