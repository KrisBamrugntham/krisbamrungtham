<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include('connectdb.php');

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงกลุ่มทั้งหมด และเช็คว่า user คนปัจจุบัน join แล้วหรือยัง
    $sql = "
        SELECT 
            g.group_id, 
            g.group_name, 
            g.description,
            (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id) as member_count,
            EXISTS(SELECT 1 FROM group_members gm2 WHERE gm2.group_id = g.group_id AND gm2.user_id = :user_id) as is_member
        FROM `groups` g
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $groups]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>