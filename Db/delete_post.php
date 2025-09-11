<?php
// --- Add this CORS section ---
header("Access-Control-Allow-Origin: *"); // Allows all origins. For production, you might want to restrict this to 'http://localhost:3000'
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- End of CORS section ---

header('Content-Type: application/json');
include 'connectdb.php';

$data = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'error' => 'Invalid request'];

if (isset($data['post_id']) && isset($data['user_id'])) {
    $post_id = filter_var($data['post_id'], FILTER_VALIDATE_INT);
    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);

    if ($post_id && $user_id) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get user role and post owner
            $user_sql = "SELECT role FROM users WHERE user_id = :user_id";
            $user_stmt = $pdo->prepare($user_sql);
            $user_stmt->execute([':user_id' => $user_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            $post_sql = "SELECT user_id FROM posts WHERE post_id = :post_id";
            $post_stmt = $pdo->prepare($post_sql);
            $post_stmt->execute([':post_id' => $post_id]);
            $post = $post_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !$post) {
                throw new Exception('User or Post not found.');
            }

            $is_owner = ($post['user_id'] == $user_id);
            $is_admin = ($user['role'] === 'admin');

            if ($is_owner || $is_admin) {
                // Authorized to delete
                $pdo->beginTransaction();

                // 1. Delete likes associated with the post. 
                // Note: The table name in your SQL schema is `likes`, not `post_likes`
                $likes_sql = "DELETE FROM likes WHERE post_id = :post_id";
                $likes_stmt = $pdo->prepare($likes_sql);
                $likes_stmt->execute([':post_id' => $post_id]);

                // 2. Delete comments associated with the post
                $comments_sql = "DELETE FROM comments WHERE post_id = :post_id";
                $comments_stmt = $pdo->prepare($comments_sql);
                $comments_stmt->execute([':post_id' => $post_id]);

                // 3. Delete the post itself
                $delete_sql = "DELETE FROM posts WHERE post_id = :post_id";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->execute([':post_id' => $post_id]);

                $pdo->commit();
                $response = ['success' => true, 'message' => 'Post deleted successfully.'];

            } else {
                // Not authorized
                http_response_code(403);
                $response['error'] = 'You are not authorized to delete this post.';
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            $response['error'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['error'] = 'Invalid post ID or user ID.';
    }
}

echo json_encode($response);
?>