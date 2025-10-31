<?php
/**
 * Get featured services for the home page
 * 
 * @param PDO $pdo Database connection
 * @param int $limit Number of services to return (default: 3)
 * @return array Array of featured services
 */
function get_featured_services($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM services 
            WHERE status = 1 
            ORDER BY is_featured DESC, display_order ASC, title ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching featured services: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all services grouped by category
 * 
 * @param PDO $pdo Database connection
 * @return array Array of services grouped by category
 */
function get_services_by_category($pdo) {
    try {
        // First, get all categories
        $stmt = $pdo->query("SELECT * FROM service_categories WHERE status = 1 ORDER BY display_order, name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        
        // Get services for each category
        foreach ($categories as $category) {
            $stmt = $pdo->prepare("
                SELECT s.* 
                FROM services s
                LEFT JOIN service_category_mapping scm ON s.id = scm.service_id
                WHERE scm.category_id = :category_id AND s.status = 1
                ORDER BY s.display_order, s.title
            ");
            $stmt->execute([':category_id' => $category['id']]);
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($services)) {
                $result[] = [
                    'category' => $category,
                    'services' => $services
                ];
            }
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching services by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single service by slug
 * 
 * @param PDO $pdo Database connection
 * @param string $slug Service slug
 * @return array|bool Service data or false if not found
 */
function get_service_by_slug($pdo, $slug) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE slug = :slug AND status = 1 LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching service by slug: " . $e->getMessage());
        return false;
    }
}

/**
 * Update service featured status
 * 
 * @param PDO $pdo Database connection
 * @param int $serviceId Service ID
 * @param bool $isFeatured Whether the service should be featured
 * @return bool True on success, false on failure
 */
function update_service_featured_status($pdo, $serviceId, $isFeatured) {
    try {
        $stmt = $pdo->prepare("UPDATE services SET is_featured = :is_featured WHERE id = :id");
        return $stmt->execute([
            ':is_featured' => $isFeatured ? 1 : 0,
            ':id' => $serviceId
        ]);
    } catch (PDOException $e) {
        error_log("Error updating service featured status: " . $e->getMessage());
        return false;
    }
}
?>
