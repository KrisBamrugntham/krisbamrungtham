<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include('connectdb.php');

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User ID is required."]);
    exit();
}
$current_user_id = (int)$_GET['user_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. หาเพื่อนทั้งหมด
    $sql_friends = "
        SELECT u.user_id, u.username, u.avatar_url, 'friend' as status
        FROM users u
        JOIN friendships f ON (u.user_id = f.user_id_1 OR u.user_id = f.user_id_2)
        WHERE (f.user_id_1 = :user_id OR f.user_id_2 = :user_id)
          AND f.status = 'accepted'
          AND u.user_id != :user_id
    ";
    $stmt_friends = $pdo->prepare($sql_friends);
    $stmt_friends->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_friends->execute();
    $friends = $stmt_friends->fetchAll(PDO::FETCH_ASSOC);

    // 2. หาคำขอที่ส่งไป
    $sql_sent = "
        SELECT u.user_id, u.username, u.avatar_url, 'sent' as status
        FROM users u JOIN friendships f ON u.user_id = f.user_id_2
        WHERE f.user_id_1 = :user_id AND f.status = 'pending'
    ";
    $stmt_sent = $pdo->prepare($sql_sent);
    $stmt_sent->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_sent->execute();
    $sent_requests = $stmt_sent->fetchAll(PDO::FETCH_ASSOC);

    // 3. หาคำขอที่ได้รับ
    $sql_received = "
        SELECT u.user_id, u.username, u.avatar_url, 'received' as status
        FROM users u JOIN friendships f ON u.user_id = f.user_id_1
        WHERE f.user_id_2 = :user_id AND f.status = 'pending'
    ";
    $stmt_received = $pdo->prepare($sql_received);
    $stmt_received->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_received->execute();
    $received_requests = $stmt_received->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => [
            "friends" => $friends,
            "sent_requests" => $sent_requests,
            "received_requests" => $received_requests
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>