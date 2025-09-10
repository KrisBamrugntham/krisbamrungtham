<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
include('connectdb.php');

$data = json_decode(file_get_contents("php://input"));
if (empty($data->user_id) || empty($data->group_id) || empty($data->action)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($data->action === 'join') {
        $sql = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $data->group_id, ':user_id' => $data->user_id]);
        echo json_encode(["success" => true, "message" => "Joined group successfully."]);
    } elseif ($data->action === 'leave') {
        $sql = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $data->group_id, ':user_id' => $data->user_id]);
        echo json_encode(["success" => true, "message" => "Left group successfully."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>