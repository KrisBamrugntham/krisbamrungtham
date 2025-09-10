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

include('connectdb.php');

function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->user_id) || !isset($data->content)) {
    send_json_error("Incomplete data: user_id and content are required.");
}

if (trim($data->content) === '' && empty($data->image_url)) {
    send_json_error("Post content or image is required.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO posts (user_id, content, image_url) VALUES (:user_id, :content, :image_url)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $data->user_id, PDO::PARAM_INT);
    $stmt->bindParam(':content', $data->content, PDO::PARAM_STR);
    $stmt->bindParam(':image_url', $data->image_url, PDO::PARAM_STR); 
    $stmt->execute();

    $new_post_id = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Post created successfully", "post_id" => $new_post_id]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>