<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

if (empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid request. Post ID is required.';
    header('Location: blog.php');
    exit();
}

try {
    $db = getDBConnection();
    
    // Get post details to delete the featured image
    $stmt = $db->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        // Delete the featured image if it exists
        if (!empty($post['featured_image']) && file_exists('../uploads/blog/' . $post['featured_image'])) {
            unlink('../uploads/blog/' . $post['featured_image']);
        }
        
        // Delete the post (foreign key constraints will handle the blog_post_categories entries)
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        $_SESSION['success_message'] = 'Blog post deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'Blog post not found.';
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error deleting blog post: ' . $e->getMessage();
}

header('Location: blog.php');
exit();
