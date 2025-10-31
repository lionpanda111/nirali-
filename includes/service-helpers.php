<?php
/**
 * Get icon class based on service category name
 * 
 * @param string $categoryName Name of the category
 * @return string Font Awesome icon class
 */
function get_category_icon($categoryName) {
    $icons = [
        'bridal' => 'fas fa-heart',
        'bridal makeup' => 'fas fa-heart',
        'wedding' => 'fas fa-heart',
        'party' => 'fas fa-glass-cheers',
        'party makeup' => 'fas fa-glass-cheers',
        'hairstyling' => 'fas fa-cut',
        'hair' => 'fas fa-cut',
        'hair styling' => 'fas fa-cut',
        'skincare' => 'fas fa-spa',
        'skin care' => 'fas fa-spa',
        'facial' => 'fas fa-spa',
        'traditional' => 'fas fa-palette',
        'mehndi' => 'fas fa-palette',
        'photoshoot' => 'fas fa-camera',
        'makeup' => 'fas fa-paint-brush',
        'eyebrows' => 'fas fa-eye',
        'eyelashes' => 'fas fa-eye',
        'nails' => 'fas fa-hand-sparkles',
        'waxing' => 'fas fa-fire',
        'threading' => 'fas fa-cut',
        'massage' => 'fas fa-hands',
    ];
    
    $categoryLower = strtolower($categoryName);
    
    // Check for exact matches first
    foreach ($icons as $key => $icon) {
        if (strpos($categoryLower, $key) !== false) {
            return $icon;
        }
    }
    
    // Default icon
    return 'fas fa-star';
}

/**
 * Get all service categories
 * 
 * @param PDO $pdo Database connection
 * @return array Array of categories
 */
function get_service_categories($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM service_categories WHERE status = 1 ORDER BY display_order, name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching service categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get services by category ID
 * 
 * @param PDO $pdo Database connection
 * @param int $categoryId Category ID
 * @return array Array of services
 */
function get_services_by_category_id($pdo, $categoryId) {
    try {
        $stmt = $pdo->prepare("
            SELECT s.* 
            FROM services s
            LEFT JOIN service_category_mapping scm ON s.id = scm.service_id
            WHERE scm.category_id = :category_id AND s.status = 1
            ORDER BY s.display_order, s.title
        ");
        $stmt->execute([':category_id' => $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching services by category ID: " . $e->getMessage());
        return [];
    }
}
?>
