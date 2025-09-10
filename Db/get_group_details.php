<?php
header("Access-control-allow-origin: *");
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

    $sql = "SELECT group_id, group_name, description, created_by, created_at FROM `groups` WHERE group_id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($group) {
        echo json_encode(["status" => "success", "group" => $group]);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Group not found."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>