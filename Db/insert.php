<?php
// CORS headers ต้องอยู่บนสุดก่อนส่งอะไรกลับ
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// ✅ ตอบ OPTIONS request ให้เร็วที่สุด แล้วจบ
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ ป้องกัน method ที่ไม่ใช่ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

// ====== โค้ดเชื่อมฐานข้อมูลด้านล่างนี้ OK แล้ว ======
include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"));

    $sql = "INSERT INTO users (username, email, password_hash, gender, interest, avatar_url) 
            VALUES (:username, :email, :password, :gender, :interest, :avatar_url)";

    $stmt = $pdo->prepare($sql);

    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT); 
    
    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':gender', $data->gender);
    $stmt->bindParam(':interest', $data->interests);
    $stmt->bindParam(':avatar_url', $data->avatar_url);

    $stmt->execute();

    echo json_encode(["success" => true, "message" => "User registered successfully"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
