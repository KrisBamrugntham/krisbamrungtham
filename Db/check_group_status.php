<?php
// ... (ส่วน header CORS เหมือนเดิม) ...
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'connectdb.php';
// ... (ส่วนรับ group_id, user_id เหมือนเดิม) ...
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

    // --- 💡 จุดแก้ไข ---
    // ดึง role มาด้วย
    $sql_member = "SELECT role FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $member = $stmt_member->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        // ถ้าเป็นสมาชิก ให้ตอบกลับ role ไปด้วย
        echo json_encode(["status" => "member", "role" => $member['role']]);
        exit();
    }
    // --- จบจุดแก้ไข ---

    // ส่วนที่เหลือเหมือนเดิม
    $sql_request = "SELECT status FROM group_join_requests WHERE group_id = :group_id AND user_id = :user_id";
    $stmt_request = $pdo->prepare($sql_request);
    $stmt_request->execute([':group_id' => $groupId, ':user_id' => $userId]);
    $request = $stmt_request->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        echo json_encode(["status" => $request['status']]);
    } else {
        echo json_encode(["status" => "not_member"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>