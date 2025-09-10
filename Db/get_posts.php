<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include('connectdb.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงโพสต์ทั้งหมดพร้อมข้อมูลผู้เขียนและ image_url
    $post_sql = "
        SELECT p.post_id, p.content, p.image_url, p.created_at, u.user_id, u.username, u.avatar_url
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        ORDER BY p.created_at DESC
    ";
    $post_stmt = $pdo->query($post_sql);
    $posts = $post_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงคอมเมนต์ทั้งหมดสำหรับแต่ละโพสต์พร้อมข้อมูลผู้เขียนคอมเมนต์และ avatar_url
    $comment_sql = "
        SELECT c.comment_id, c.post_id, c.content, c.created_at, u.user_id, u.username, u.avatar_url
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.post_id = :post_id
        ORDER BY c.created_at ASC
    ";
    $comment_stmt = $pdo->prepare($comment_sql);

    foreach ($posts as &$post) {
        $comment_stmt->execute([':post_id' => $post['post_id']]);
        $post['comments'] = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(["status" => "success", "data" => $posts]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>