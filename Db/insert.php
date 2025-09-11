<?php
// เปิดการแสดงข้อผิดพลาดทั้งหมดเพื่อการดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
    if ($details !== null) {
        $response['details'] = $details;
    }
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

$data = json_decode(file_get_contents("php://input"));
if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_error("Invalid JSON format: " . json_last_error_msg(), 400);
}

include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check for existing email
    $sql_check = "SELECT user_id FROM users WHERE email = :email";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':email', $data->email);
    $stmt_check->execute();

    if ($stmt_check->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode(["success" => false, "error" => "เมลนี้ได้ถูกลงทะเบียนไว้แล้ว"]);
        exit();
    }

    // ไม่จำเป็นต้องใส่ role ในคำสั่ง INSERT เพราะฐานข้อมูลจะใส่ค่า default 'member' ให้
    $sql = "INSERT INTO users (username, email, password_hash, gender, interest, avatar_url) 
            VALUES (:username, :email, :password, :gender, :interest, :avatar_url)";

    $stmt = $pdo->prepare($sql);

    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
    
    $interests_value = isset($data->interests) ? $data->interests : '';
    $avatar_url_value = isset($data->avatar_url) ? $data->avatar_url : 'default.png';

    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':gender', $data->gender);
    $stmt->bindParam(':interest', $interests_value);
    $stmt->bindParam(':avatar_url', $avatar_url_value);

    $stmt->execute();
    
    $user_id = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "User registered successfully", "user_id" => $user_id, "avatar_url" => $avatar_url_value]);

} catch (PDOException $e) {
    send_json_error("Database Error", 500, $e->getMessage());
}
?>