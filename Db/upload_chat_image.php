<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

if (!isset($_FILES['image'])) {
    send_json_error("No image uploaded.");
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    send_json_error("File upload error: " . $file['error']);
}

$uploadDir = '../../uploads/chat_images/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        send_json_error("Failed to create upload directory.", 500);
    }
}

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($fileExtension, $allowedExtensions)) {
    send_json_error("Invalid file type. Allowed types: " . implode(', ', $allowedExtensions));
}

$uniqueName = uniqid('', true) . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueName;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Construct the URL accessible by the client
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // This part needs to be adjusted based on the actual project structure accessible from the web
    $baseUrl = $protocol . $host . '/krisbamrungtham/uploads/chat_images/';
    $imageUrl = $baseUrl . $uniqueName;

    echo json_encode(["success" => true, "image_url" => $imageUrl]);
} else {
    send_json_error("Failed to move uploaded file.", 500);
}
?>
