<?php
// เปิดการแสดงข้อผิดพลาด (สำหรับตอนพัฒนา)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 💡 ส่วนจัดการ CORS ที่จำเป็น ---
header("Access-Control-Allow-Origin: *"); // อนุญาตให้ทุก Origin เข้าถึงได้
header("Access-Control-Allow-Methods: POST, OPTIONS"); // อนุญาตเฉพาะเมธอด POST และ OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// จัดการกับ Preflight Request ที่เบราว์เซอร์ส่งมาถามก่อน
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- จบส่วนจัดการ CORS ---

include 'connectdb.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->user_id) || empty($data->post_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data: user_id and post_id are required."]);
    exit();
}

$userId = $data->user_id;
$postId = $data->post_id;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. ตรวจสอบว่าผู้ใช้เคยกดไลค์โพสต์นี้แล้วหรือยัง
    $sql_check = "SELECT 1 FROM likes WHERE user_id = :user_id AND post_id = :post_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':user_id' => $userId, ':post_id' => $postId]);

    if ($stmt_check->fetch()) {
        // ถ้าเคยกดแล้ว -> ให้ลบไลค์ออก (Unlike)
        $sql_unlike = "DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id";
        $stmt_unlike = $pdo->prepare($sql_unlike);
        $stmt_unlike->execute([':user_id' => $userId, ':post_id' => $postId]);
        $liked = false;
    } else {
        // ถ้ายังไม่เคยกด -> ให้เพิ่มไลค์ (Like)
        $sql_like = "INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)";
        $stmt_like = $pdo->prepare($sql_like);
        $stmt_like->execute([':user_id' => $userId, ':post_id' => $postId]);
        $liked = true;
    }

    // 2. ดึงจำนวนไลค์ล่าสุด
    $sql_count = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = :post_id";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':post_id' => $postId]);
    $result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $likeCount = $result['like_count'];

    echo json_encode(["success" => true, "liked" => $liked, "likeCount" => $likeCount]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>