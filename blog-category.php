<?php
require_once __DIR__ . '/includes/config.php';

if (empty($_GET['slug'])) {
    header('Location: blog.php');
    exit();
}

$slug = $_GET['slug'];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

try {
    $db = getDBConnection();
    
    // Get category details
    $stmt = $db->prepare("SELECT * FROM blog_categories WHERE slug = ?");
    $stmt->execute([$slug]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit();
    }
    
    // Get total posts in this category
    $total_posts = $db->prepare("
        SELECT COUNT(DISTINCT p.id)
        FROM blog_posts p
        JOIN blog_post_categories pc ON p.id = pc.post_id
        WHERE p.status = 'published' 
          AND (p.published_at IS NULL OR p.published_at <= NOW())
          AND pc.category_id = ?
    ");
    $total_posts->execute([$category['id']]);
    $total_posts = $total_posts->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);
    
    // Get posts in this category with pagination
    $stmt = $db->prepare("
        SELECT p.*, 
               GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names
        FROM blog_posts p
        JOIN blog_post_categories pc ON p.id = pc.post_id
        LEFT JOIN blog_categories c ON pc.category_id = c.id
        WHERE p.status = 'published' 
          AND (p.published_at IS NULL OR p.published_at <= NOW())
          AND pc.category_id = ?
        GROUP BY p.id
        ORDER BY p.published_at DESC, p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bindValue(1, $category['id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent posts for sidebar
    $recent_posts = $db->query("
        SELECT id, title, slug, created_at 
        FROM blog_posts 
        WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())
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
    
    $page_title = $category['name'] . ' - Blog';
    $meta_description = 'Browse all posts in the ' . $category['name'] . ' category.';
    
} catch (PDOException $e) {
    error_log('Database error in blog category: ' . $e->getMessage());
    $error = 'An error occurred while loading the category. Please try again later.';
}

include 'includes/header.php';
?>

<!-- Page Header -->
<header class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-dark"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="lead text-muted">
                    <?php echo $total_posts; ?> post<?php echo $total_posts != 1 ? 's' : ''; ?> in this category
                </p>
                <?php if (!empty($category['description'])): ?>
                    <p class="mb-0"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Blog Posts -->
<section class="blog-section py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif (empty($posts)): ?>
                    <div class="alert alert-info">No posts found in this category. Please check back later!</div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6 col-lg-6 mb-4">
                                <article class="card h-100 border-0 shadow-sm">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <a href="blog/<?php echo htmlspecialchars($post['slug']); ?>">
                                            <img src="uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <?php if (!empty($post['category_names'])): ?>
                                            <div class="mb-2">
                                                <?php 
                                                $category_links = [];
                                                $category_names = explode(', ', $post['category_names']);
                                                foreach ($category_names as $category_name) {
                                                    $category_slug = strtolower(str_replace(' ', '-', $category_name));
                                                    $category_links[] = '<a href="blog/category/' . htmlspecialchars($category_slug) . '" class="badge bg-primary text-decoration-none">' . 
                                                                      htmlspecialchars($category_name) . '</a>';
                                                }
                                                echo implode(' ', $category_links);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h2 class="h5 card-title">
                                            <a href="blog/<?php echo htmlspecialchars($post['slug']); ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h2>
                                        
                                        <p class="card-text text-muted">
                                            <?php 
                                            $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : strip_tags($post['content']);
                                            echo mb_substr($excerpt, 0, 150) . (mb_strlen($excerpt) > 150 ? '...' : '');
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i> 
                                            <?php echo date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?>
                                        </small>
                                        <a href="blog/<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-sm btn-outline-primary">
                                            Read More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Blog pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $page - 1; ?>" 
                                           aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $page + 1; ?>" 
                                           aria-label="Next">
                                            <span aria-hidden="true">Next &raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Search</h5>
                    </div>
                    <div class="card-body">
                        <form action="blog-search.php" method="get" class="search-form">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Search posts..." required>
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($recent_posts)): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Recent Posts</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_posts as $recent): ?>
                                <a href="blog/<?php echo htmlspecialchars($recent['slug']); ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($recent['title']); ?>
                                    <small class="text-muted">
                                        <?php echo date('M j', strtotime($recent['created_at'])); ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($categories)): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Categories</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $cat): ?>
                                <a href="blog-category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $cat['id'] == $category['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $cat['post_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Subscribe</h5>
                    </div>
                    <div class="card-body">
                        <p>Stay updated with our latest posts and beauty tips!</p>
                        <form id="subscribeForm" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
