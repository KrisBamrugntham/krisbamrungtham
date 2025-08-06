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

function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error("Method Not Allowed", 405);
}

$data = json_decode(file_get_contents("php://input"));
if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_error("Invalid JSON", 400);
}
if (empty($data->email) || empty($data->password)) {
    send_json_error("กรุณากรอกอีเมลและรหัสผ่าน");
}

include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $data->email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['password_hash'])) {
        // --- 💡 จุดแก้ไข: ตรวจสอบสถานะการระงับใช้งาน ---
        if ($user['status'] === 'suspended') {
            $today = new DateTime();
            $suspended_until = new DateTime($user['suspended_until']);
            if ($today <= $suspended_until) {
                send_json_error("บัญชีของคุณถูกระงับการใช้งานจนถึงวันที่ " . $suspended_until->format('Y-m-d'), 403); // 403 Forbidden
            }
        }
        // --- จบจุดแก้ไข ---

        unset($user['password_hash']);
        
        if ($user['email'] === 'Admin@gmail.com') {
            $user['role'] = 'admin';
        }
        
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        send_json_error("อีเมลหรือรหัสผ่านไม่ถูกต้อง", 401);
    }

} catch (PDOException $e) {
    send_json_error("Database Error: " . $e->getMessage(), 500);
}
?>