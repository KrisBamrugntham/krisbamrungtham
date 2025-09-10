<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'connectdb.php';

if (!isset($_GET['group_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Group ID is required."]);
    exit();
}
$groupId = $_GET['group_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "
        SELECT u.user_id, u.username, u.avatar_url, gm.role
        FROM group_members gm
        JOIN users u ON gm.user_id = u.user_id
        WHERE gm.group_id = :group_id
        ORDER BY gm.role, u.username
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $members]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>