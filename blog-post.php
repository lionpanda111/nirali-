<?php
require_once __DIR__ . '/includes/config.php';

if (empty($_GET['slug'])) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

$slug = $_GET['slug'];

try {
    $db = getDBConnection();
    
    // Get the blog post
    $stmt = $db->prepare("
        SELECT p.*, 
               GROUP_CONCAT(DISTINCT c.id, '::', c.name, '::', c.slug SEPARATOR '|||') as categories
        FROM blog_posts p
        LEFT JOIN blog_post_categories pc ON p.id = pc.post_id
        LEFT JOIN blog_categories c ON pc.category_id = c.id
        WHERE p.slug = ? AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
        GROUP BY p.id
    ");
    
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit();
    }
    
    // Parse categories
    $post_categories = [];
    if (!empty($post['categories'])) {
        $category_items = explode('|||', $post['categories']);
        foreach ($category_items as $item) {
            if (!empty($item)) {
                list($id, $name, $cat_slug) = explode('::', $item);
                $post_categories[] = [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $cat_slug
                ];
            }
        }
    }
    
    // Get recent posts for sidebar
    $recent_posts = $db->query("
        SELECT id, title, slug, created_at 
        FROM blog_posts 
        WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW()) AND id != " . $post['id'] . "
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories with post counts
    $categories = $db->query("
        SELECT c.id, c.name, c.slug, COUNT(pc.post_id) as post_count
        FROM blog_categories c
        LEFT JOIN blog_post_categories pc ON c.id = pc.category_id
        LEFT JOIN blog_posts p ON pc.post_id = p.id AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
        GROUP BY c.id, c.name, c.slug
        HAVING post_count > 0
        ORDER BY c.name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get related posts (from same categories)
    $related_posts = [];
    if (!empty($post_categories)) {
        $category_ids = array_column($post_categories, 'id');
        $placeholders = rtrim(str_repeat('?,', count($category_ids)), ',');
        
        $stmt = $db->prepare("
            SELECT DISTINCT p.id, p.title, p.slug, p.featured_image, p.created_at
            FROM blog_posts p
            JOIN blog_post_categories pc ON p.id = pc.post_id
            WHERE p.status = 'published' 
              AND (p.published_at IS NULL OR p.published_at <= NOW())
              AND p.id != ?
              AND pc.category_id IN ($placeholders)
            ORDER BY p.created_at DESC
            LIMIT 3
        ");
        
        $params = array_merge([$post['id']], $category_ids);
        $stmt->execute($params);
        $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update view count (you might want to implement this with a separate table for better analytics)
    // $db->exec("UPDATE blog_posts SET views = views + 1 WHERE id = " . $post['id']);
    
    $page_title = $post['title'];
    $meta_description = !empty($post['excerpt']) ? $post['excerpt'] : strip_tags(substr($post['content'], 0, 160)) . '...';
    
    // Add Open Graph meta tags for better social sharing
    $og_image = !empty($post['featured_image']) ? SITE_URL . '/uploads/blog/' . $post['featured_image'] : SITE_URL . '/assets/images/og-default.jpg';
    
} catch (PDOException $e) {
    error_log('Database error in blog post: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    die('An error occurred while loading the blog post. Please try again later.');
}

include 'includes/header.php';
?>

<!-- Blog Post Header -->
<header class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <?php if (!empty($post_categories)): ?>
                    <div class="mb-3">
                        <?php foreach ($post_categories as $category): ?>
                            <a href="blog/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                               class="badge bg-primary text-decoration-none me-2">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h1 class="display-4 fw-bold text-dark"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="text-muted mb-3">
                    <span class="me-3">
                        <i class="far fa-calendar-alt me-1"></i> 
                        <?php echo date('F j, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?>
                    </span>
                    <span>
                        <i class="far fa-user me-1"></i> 
                        By Admin
                    </span>
                </div>
                
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="mt-4">
                        <img src="uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="img-fluid rounded shadow">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Blog Post Content -->
<article class="blog-post pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="blog-content">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Tags (if any) -->
                <?php if (!empty($post['tags'])): ?>
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="mb-3">Tags:</h6>
                        <div class="tag-cloud">
                            <?php 
                            $tags = explode(',', $post['tags']);
                            foreach ($tags as $tag): 
                                $tag_slug = trim(strtolower(str_replace(' ', '-', $tag)));
                            ?>
                                <a href="blog/tag/<?php echo htmlspecialchars($tag_slug); ?>" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Share Buttons -->
                <div class="mt-5 pt-4 border-top">
                    <h6 class="mb-3">Share this post:</h6>
                    <div class="social-share">
                        <?php 
                        $share_url = urlencode(SITE_URL . '/blog/' . $post['slug']);
                        $share_title = urlencode($post['title']);
                        $share_text = urlencode('Check out this post: ' . $post['title']);
                        $share_image = !empty($post['featured_image']) ? urlencode(SITE_URL . '/uploads/blog/' . $post['featured_image']) : '';
                        ?>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2" title="Share on Facebook">
                            <i class="fab fa-facebook-f me-1"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" 
                           target="_blank" class="btn btn-sm btn-outline-info me-2 mb-2" title="Share on Twitter">
                            <i class="fab fa-twitter me-1"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in me-1"></i> LinkedIn
                        </a>
                        <a href="https://pinterest.com/pin/create/button/?url=<?php echo $share_url; ?>&media=<?php echo $share_image; ?>&description=<?php echo $share_title; ?>" 
                           target="_blank" class="btn btn-sm btn-outline-danger me-2 mb-2" title="Pin on Pinterest">
                            <i class="fab fa-pinterest-p me-1"></i> Pinterest
                        </a>
                        <a href="mailto:?subject=<?php echo $share_title; ?>&body=<?php echo $share_text . ' ' . $share_url; ?>" 
                           class="btn btn-sm btn-outline-secondary mb-2" title="Share via Email">
                            <i class="far fa-envelope me-1"></i> Email
                        </a>
                    </div>
                </div>
                
                <!-- Author Box (if needed) -->
                <!--
                <div class="mt-5 pt-4 border-top">
                    <div class="d-flex align-items-center">
                        <img src="assets/images/author.jpg" alt="Author" class="rounded-circle me-3" width="80">
                        <div>
                            <h5 class="mb-1">Author Name</h5>
                            <p class="text-muted mb-2">Makeup Artist & Beauty Expert</p>
                            <p class="mb-0">With over 10 years of experience in the beauty industry, [Author] specializes in bridal and editorial makeup.</p>
                        </div>
                    </div>
                </div>
                -->
                
                <!-- Related Posts -->
                <?php if (!empty($related_posts)): ?>
                    <div class="mt-5 pt-4 border-top">
                        <h4 class="mb-4">You May Also Like</h4>
                        <div class="row">
                            <?php foreach ($related_posts as $related): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <?php if (!empty($related['featured_image'])): ?>
                                            <a href="blog/<?php echo htmlspecialchars($related['slug']); ?>">
                                                <img src="uploads/blog/<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                                     class="card-img-top" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                            </a>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="h6 card-title">
                                                <a href="blog/<?php echo htmlspecialchars($related['slug']); ?>" class="text-dark text-decoration-none">
                                                    <?php echo htmlspecialchars($related['title']); ?>
                                                </a>
                                            </h5>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i> 
                                                <?php echo date('M j, Y', strtotime($related['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?n                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Comments Section (optional) -->
                <!--
                <div class="mt-5 pt-4 border-top" id="comments">
                    <h4 class="mb-4">Comments (3)</h4>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <img src="assets/images/user1.jpg" alt="User" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h6 class="mb-1">John Doe</h6>
                                    <div class="text-muted small">2 days ago</div>
                                </div>
                            </div>
                            <p class="mb-0">Great post! I learned a lot from this. Keep up the good work!</p>
                            <button class="btn btn-sm btn-link text-decoration-none p-0 mt-2">Reply</button>
                            
                            <div class="mt-3 ps-4 border-start">
                                <div class="d-flex mb-2">
                                    <img src="assets/images/author.jpg" alt="Author" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0">Author Name</h6>
                                        <div class="text-muted small">1 day ago</div>
                                    </div>
                                </div>
                                <p class="mb-0">Thank you, John! I'm glad you found it helpful.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Leave a Comment</h5>
                            <form>
                                <div class="mb-3">
                                    <label for="commentName" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="commentName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="commentEmail" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="commentEmail" required>
                                    <div class="form-text">Your email will not be published.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="commentMessage" class="form-label">Comment *</label>
                                    <textarea class="form-control" id="commentMessage" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                    </div>
                </div>
                -->
            </div>
        </div>
    </div>
</article>

<?php include 'includes/footer.php'; ?>
