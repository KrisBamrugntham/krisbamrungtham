<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include('connectdb.php');

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User ID is required."]);
    exit();
}
$current_user_id = (int)$_GET['user_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คิวรีสำหรับหาคนที่ไม่ใช่เพื่อนและยังไม่มีคำขอค้างอยู่
    $sql = "
        SELECT u.user_id, u.username, u.avatar_url
        FROM users u
        WHERE u.user_id != :user_id AND u.role != 'admin' AND NOT EXISTS (
            SELECT 1 FROM friendships f
            WHERE (f.user_id_1 = :user_id AND f.user_id_2 = u.user_id)
               OR (f.user_id_1 = u.user_id AND f.user_id_2 = :user_id)
        )
        ORDER BY RAND()
        LIMIT 5
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $suggestions
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>