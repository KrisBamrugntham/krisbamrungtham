<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('connectdb.php');

function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->group_name) || empty($data->created_by)) {
    send_json_error("Group name and creator ID are required.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. สร้างกลุ่มใหม่
    $sql_group = "INSERT INTO `groups` (group_name, description, created_by) VALUES (:group_name, :description, :created_by)";
    $stmt_group = $pdo->prepare($sql_group);
    $stmt_group->execute([
        ':group_name' => $data->group_name,
        ':description' => $data->description ?? '', // ใช้ค่าว่างถ้าไม่มี description
        ':created_by' => $data->created_by
    ]);

    $new_group_id = $pdo->lastInsertId();

    // 2. เพิ่มผู้สร้างเป็นสมาชิกคนแรกและเป็น admin ของกลุ่ม
    $sql_member = "INSERT INTO group_members (group_id, user_id, role) VALUES (:group_id, :user_id, 'admin')";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([
        ':group_id' => $new_group_id,
        ':user_id' => $data->created_by
    ]);


    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Group created successfully", "group_id" => $new_group_id]);

} catch (PDOException $e) {
    send_json_error("Database Error: " . $e->getMessage(), 500);
}
?>