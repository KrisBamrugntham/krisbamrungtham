<?php
session_start();
include 'connectdb.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'error' => 'Invalid request'];

if (isset($data['post_id']) && isset($data['user_id'])) {
    $post_id = filter_var($data['post_id'], FILTER_VALIDATE_INT);
    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);

    if ($post_id && $user_id) {
        try {
            // Check if the user has already liked the post
            $check_sql = "SELECT like_id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
            $existing_like = $check_stmt->fetch();

            if ($existing_like) {
                // User has liked, so unlike it
                $delete_sql = "DELETE FROM post_likes WHERE like_id = :like_id";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->execute([':like_id' => $existing_like['like_id']]);
                $response = ['success' => true, 'liked' => false];
            } else {
                // User has not liked, so like it
                $insert_sql = "INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
                $response = ['success' => true, 'liked' => true];
            }
        } catch (PDOException $e) {
            $response['error'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['error'] = 'Invalid post ID or user ID.';
    }
}

echo json_encode($response);
?>
