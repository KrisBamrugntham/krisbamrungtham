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

if (empty($data->comment_id) || empty($data->user_id)) {
    send_json_error("Incomplete data: comment_id and user_id are required.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL นี้จะตรวจสอบว่า user_id ที่ส่งมา เป็นเจ้าของโพสต์ของคอมเมนต์นี้หรือไม่
    $sql = "
        DELETE c FROM comments c
        JOIN posts p ON c.post_id = p.post_id
        WHERE c.comment_id = :comment_id AND p.user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':comment_id' => $data->comment_id,
        ':user_id' => $data->user_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Comment deleted successfully"]);
    } else {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized: You are not the owner of this post or comment not found."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>