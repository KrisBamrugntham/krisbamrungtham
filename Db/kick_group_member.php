<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'connectdb.php';

$data = json_decode(file_get_contents("php://input"));

// *** ในระบบจริง ควรมีการตรวจสอบว่าคนที่สั่งเตะเป็น admin ของกลุ่มนั้นจริงๆ ***
if (empty($data->group_id) || empty($data->user_to_kick_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data."]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $data->group_id,
        ':user_id' => $data->user_to_kick_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Member removed successfully."]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Member not found in this group."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>