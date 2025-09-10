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

function send_json_error($message, $code = 400, $details = null) {
    http_response_code($code);
    $response = ["success" => false, "error" => $message];
    if ($details) $response['details'] = $details;
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    send_json_error("No image uploaded or upload error.", 400, $_FILES['image']['error'] ?? 'N/A');
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($fileExt, $allowed)) {
    send_json_error("Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.", 400);
}

if ($fileError !== 0) {
    send_json_error("There was an error uploading your file.", 500);
}

if ($fileSize > 5000000) { // 5MB limit
    send_json_error("Your file is too large. Max 5MB.", 400);
}

// --- 💡 จุดแก้ไขหลัก ---
// เปลี่ยนที่เก็บไฟล์ให้อยู่ใน /static/uploads/
$fileNameNew = uniqid('', true) . "." . $fileExt;
$uploadDir = '../static/uploads/'; // <--- แก้ไขตรงนี้

// ตรวจสอบและสร้างโฟลเดอร์ถ้ายังไม่มี
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileDestination = $uploadDir . $fileNameNew;

if (move_uploaded_file($fileTmpName, $fileDestination)) {
    // URL ที่ส่งกลับไปให้ Frontend ยังคงเป็น /uploads/ เหมือนเดิม
    // เพราะ Nuxt จะมองหาจาก root ของ static
    $imageUrl = '/uploads/' . $fileNameNew; 
    echo json_encode(["success" => true, "message" => "Image uploaded successfully", "image_url" => $imageUrl]);
} else {
    send_json_error("Failed to move uploaded file.", 500);
}
// --- จบจุดแก้ไข ---
?>