<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include 'connectdb.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->request_id) || empty($data->action)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Request ID and action are required."]);
    exit();
}

$requestId = $data->request_id;
$action = $data->action; // 'approve' or 'reject'

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // 1. ดึงข้อมูล group_id และ user_id จาก request_id
    $sql_get_info = "SELECT group_id, user_id FROM group_join_requests WHERE request_id = :request_id";
    $stmt_info = $pdo->prepare($sql_get_info);
    $stmt_info->execute([':request_id' => $requestId]);
    $request_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
    
    if (!$request_info) {
        throw new Exception("Invalid Request ID");
    }

    $groupId = $request_info['group_id'];
    $userId = $request_info['user_id'];

    // 2. อัปเดตสถานะในตาราง group_join_requests
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $sql_update = "UPDATE group_join_requests SET status = :status WHERE request_id = :request_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([':status' => $new_status, ':request_id' => $requestId]);

    // 3. ถ้าอนุมัติ (approve), ให้เพิ่ม user เข้าไปในตาราง group_members
    if ($action === 'approve') {
        $sql_insert_member = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'member')";
        $stmt_insert = $pdo->prepare($sql_insert_member);
        $stmt_insert->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Action completed successfully."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Transaction Failed: " . $e->getMessage()]);
}
?>