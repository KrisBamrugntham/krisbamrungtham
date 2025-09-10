<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

// ตรวจสอบว่ามี user_id และไฟล์รูปภาพส่งมาหรือไม่
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    send_json_error("User ID is required.");
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    send_json_error("No avatar image uploaded or upload error.");
}

$userId = $_POST['user_id'];
$file = $_FILES['avatar'];

// --- ส่วนของการจัดการไฟล์ (เหมือนกับ upload_image.php) ---
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($fileExt, $allowed)) {
    send_json_error("Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.");
}
if ($file['size'] > 5000000) { // 5MB limit
    send_json_error("Your file is too large. Max 5MB.");
}

$fileNameNew = 'avatar_' . $userId . '_' . uniqid('', true) . "." . $fileExt;
$uploadDir = '../static/uploads/avatars/'; // โฟลเดอร์สำหรับเก็บรูปโปรไฟล์โดยเฉพาะ

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileDestination = $uploadDir . $fileNameNew;
// --- จบส่วนจัดการไฟล์ ---

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ย้ายไฟล์ที่อัปโหลด
    if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
        $avatarUrl = '/uploads/avatars/' . $fileNameNew;

        // อัปเดต avatar_url ในฐานข้อมูล
        $sql = "UPDATE users SET avatar_url = :avatar_url WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':avatar_url' => $avatarUrl,
            ':user_id' => $userId
        ]);

        echo json_encode(["success" => true, "message" => "Avatar updated successfully", "avatar_url" => $avatarUrl]);
    } else {
        send_json_error("Failed to move uploaded file.", 500);
    }

} catch (PDOException $e) {
    send_json_error("Database Error: " . $e->getMessage(), 500);
}
?>