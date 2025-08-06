<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('connectdb.php');

// ตรวจสอบว่ามี user_id ส่งมาใน URL query string หรือไม่
if (!isset($_GET['user_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "User ID is required."]);
    exit();
}

$current_user_id = (int)$_GET['user_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL ที่ซับซ้อนขึ้นเพื่อดึงข้อมูลเพื่อนจากทั้งสองฝั่งของความสัมพันธ์
    $sql = "
        SELECT u.user_id, u.username, u.avatar_url
        FROM users u
        JOIN friendships f ON (u.user_id = f.user_id_1 OR u.user_id = f.user_id_2)
        WHERE (f.user_id_1 = :user_id OR f.user_id_2 = :user_id)
          AND f.status = 'accepted'
          AND u.user_id != :user_id
        GROUP BY u.user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $friends
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>