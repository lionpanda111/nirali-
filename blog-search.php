<?php
require_once __DIR__ . '/includes/config.php';

if (empty($_GET['q'])) {
    header('Location: blog.php');
    exit();
}

$search_query = trim($_GET['q']);
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

try {
    $db = getDBConnection();
    
    // Prepare search term for SQL LIKE
    $search_term = '%' . $search_query . '%';
    
    // Get total matching posts count
    $total_posts = $db->prepare("
        SELECT COUNT(DISTINCT p.id)
        FROM blog_posts p
        LEFT JOIN blog_post_categories pc ON p.id = pc.post_id
        LEFT JOIN blog_categories c ON pc.category_id = c.id
        WHERE p.status = 'published' 
          AND (p.published_at IS NULL OR p.published_at <= NOW())
          AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ? OR c.name LIKE ?)
    ");
    
    $total_posts->execute([$search_term, $search_term, $search_term, $search_term]);
    $total_posts = $total_posts->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);
    
    // Get matching posts with pagination
    $stmt = $db->prepare("
        SELECT p.*, 
               GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names
        FROM blog_posts p
        LEFT JOIN blog_post_categories pc ON p.id = pc.post_id
        LEFT JOIN blog_categories c ON pc.category_id = c.id
        WHERE p.status = 'published' 
          AND (p.published_at IS NULL OR p.published_at <= NOW())
          AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ? OR c.name LIKE ?)
        GROUP BY p.id
        ORDER BY 
            CASE 
                WHEN p.title LIKE ? THEN 1
                WHEN p.excerpt LIKE ? THEN 2
                WHEN p.content LIKE ? THEN 3
                ELSE 4
            END,
            p.published_at DESC, p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bindValue(1, $search_term);
    $stmt->bindValue(2, $search_term);
    $stmt->bindValue(3, $search_term);
    $stmt->bindValue(4, $search_term);
    $stmt->bindValue(5, $search_term);
    $stmt->bindValue(6, $search_term);
    $stmt->bindValue(7, $search_term);
    $stmt->bindValue(8, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(9, $offset, PDO::PARAM_INT);
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
    
    $page_title = 'Search Results for "' . htmlspecialchars($search_query) . '"';
    $meta_description = 'Search results for ' . htmlspecialchars($search_query) . ' in our blog.';
    
} catch (PDOException $e) {
    error_log('Database error in blog search: ' . $e->getMessage());
    $error = 'An error occurred while performing the search. Please try again later.';
}

include 'includes/header.php';
?>

<!-- Page Header -->
<header class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-dark">Search Results</h1>
                <p class="lead text-muted">
                    Found <?php echo $total_posts; ?> result<?php echo $total_posts != 1 ? 's' : ''; ?> for 
                    "<?php echo htmlspecialchars($search_query); ?>"
                </p>
                
                <div class="mt-4">
                    <form action="blog-search.php" method="get" class="search-form">
                        <div class="input-group input-group-lg" style="max-width: 600px; margin: 0 auto;">
                            <input type="text" name="q" class="form-control" 
                                   placeholder="Search the blog..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
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
                    <div class="alert alert-info">
                        No posts found matching "<?php echo htmlspecialchars($search_query); ?>". 
                        Try different keywords or check out our <a href="blog.php" class="alert-link">latest posts</a>.
                    </div>
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
                                                <?php 
                                                // Highlight search term in title
                                                echo preg_replace(
                                                    '/(' . preg_quote($search_query, '/') . ')/i', 
                                                    '<span class="bg-warning">$1</span>', 
                                                    htmlspecialchars($post['title'])
                                                );
                                                ?>
                                            </a>
                                        </h2>
                                        
                                        <p class="card-text text-muted">
                                            <?php 
                                            $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : strip_tags($post['content']);
                                            // Highlight search term in excerpt
                                            $excerpt = preg_replace(
                                                '/(' . preg_quote($search_query, '/') . ')/i', 
                                                '<span class="bg-warning">$1</span>', 
                                                $excerpt
                                            );
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
                        <nav aria-label="Search pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>" 
                                           aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>" 
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
                                <input type="text" name="q" class="form-control" 
                                       placeholder="Search posts..." 
                                       value="<?php echo htmlspecialchars($search_query); ?>" required>
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
                            <?php foreach ($categories as $category): ?>
                                <a href="blog-category.php?slug=<?php echo htmlspecialchars($category['slug']); ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $category['post_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
