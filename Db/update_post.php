<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include('connectdb.php');
$data = json_decode(file_get_contents("php://input"));

if (empty($data->post_id) || empty($data->content) || empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Incomplete data"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE posts SET content = :content WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':content' => $data->content,
        ':post_id' => $data->post_id,
        ':user_id' => $data->user_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Post updated successfully"]);
    } else {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized or post not found"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>