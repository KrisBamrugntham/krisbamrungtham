<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- 💡 จุดแก้ไข ---
    // เพิ่ม WHERE email != 'Admin@gmail.com' เพื่อกรองบัญชีแอดมินออก
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, gender, role, status, suspended_until, interest 
        FROM users 
        WHERE email != 'Admin@gmail.com' 
        ORDER BY created_at DESC
    ");
    // --- จบจุดแก้ไข ---

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $users
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>