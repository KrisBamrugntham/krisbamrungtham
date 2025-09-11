<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

if (empty($data->group_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Group ID is required."]);
    exit();
}

$groupId = $data->group_id;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();

    // Delete from group_members
    $sql_members = "DELETE FROM group_members WHERE group_id = :group_id";
    $stmt_members = $pdo->prepare($sql_members);
    $stmt_members->execute([':group_id' => $groupId]);

    // Delete from group_messages
    $sql_messages = "DELETE FROM group_messages WHERE group_id = :group_id";
    $stmt_messages = $pdo->prepare($sql_messages);
    $stmt_messages->execute([':group_id' => $groupId]);

    // Delete from groups
    $sql_group = "DELETE FROM groups WHERE group_id = :group_id";
    $stmt_group = $pdo->prepare($sql_group);
    $stmt_group->execute([':group_id' => $groupId]);

    if ($stmt_group->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Group deleted successfully."]);
    } else {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Group not found or could not be deleted."]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
