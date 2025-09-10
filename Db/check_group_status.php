<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'connectdb.php';

if (!isset($_GET['group_id']) || !isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Group ID and User ID are required."]);
    exit();
}

$groupId = $_GET['group_id'];
$userId = $_GET['user_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check if user is already a member
    $sql_member = "SELECT * FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([':group_id' => $groupId, ':user_id' => $userId]);

    if ($stmt_member->rowCount() > 0) {
        echo json_encode(["status" => "member"]);
        exit();
    }

    // 2. Check for an existing join request
    $sql_request = "SELECT status FROM group_join_requests WHERE group_id = :group_id AND user_id = :user_id";
    $stmt_request = $pdo->prepare($sql_request);
    $stmt_request->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $request = $stmt_request->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        echo json_encode(["status" => $request['status']]); // 'pending', 'approved', 'rejected'
    } else {
        echo json_encode(["status" => "not_member"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>