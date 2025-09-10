<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'connectdb.php';

if (!isset($_GET['group_id'])) {
    // ในสถานการณ์จริง ควรมีการตรวจสอบด้วยว่าคนที่เรียก API นี้เป็นเจ้าของกลุ่มหรือไม่
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Group ID is required."]);
    exit();
}
$groupId = $_GET['group_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "
        SELECT r.request_id, u.user_id, u.username, u.avatar_url, r.requested_at
        FROM group_join_requests r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.group_id = :group_id AND r.status = 'pending'
        ORDER BY r.requested_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $requests]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>