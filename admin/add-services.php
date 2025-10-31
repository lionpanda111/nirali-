<?php
require_once '../includes/config.php';

// Database connection
$pdo = getDBConnection();
if ($pdo === null) {
    die('Could not connect to the database. Please try again later.');
}

// Categories and their services
$services = [
    'Makeup' => [
        'Bridal Makeup',
        'Party Makeup',
        'Engagement Makeup',
        'Reception Makeup',
        'Sangeet Makeup',
        'Haldi Makeup',
        'Mehndi Makeup'
    ],
    'Hair' => [
        'Hair Styling',
        'Hair Cutting',
        'Hair Treatment',
        'Hair Coloring',
        'Hair Highlighting',
        'Hair Spa',
        'Hair Blow Dry',
        'Hair Wash',
        'Hair Oil Massage',
        'Hair Ironing'
    ],
    'Skin Care' => [
        'Facial',
        'Clean Up',
        'Threading',
        'Waxing',
        'Body Polishing',
        'Body Massage',
        'Heel Peel Pedicure'
    ],
    'Nails' => [
        'Manicure',
        'Pedicure',
        'Nail Art',
        'Nail Extensions'
    ]
];

try {
    $pdo->beginTransaction();

    // First, ensure categories exist
    $categoryIds = [];
    $categoryStmt = $pdo->prepare("INSERT IGNORE INTO service_categories (name, slug, description, display_order, status) 
                                 VALUES (?, ?, ?, ?, 1)");
    
    $displayOrder = 1;
    foreach (array_keys($services) as $categoryName) {
        $slug = strtolower(str_replace(' ', '-', $categoryName));
        $categoryStmt->execute([$categoryName, $slug, $categoryName . ' Services', $displayOrder]);
        $categoryId = $pdo->lastInsertId();
        if ($categoryId == 0) {
            // Category already exists, get its ID
            $stmt = $pdo->prepare("SELECT id FROM service_categories WHERE slug = ?");
            $stmt->execute([$slug]);
            $categoryId = $stmt->fetchColumn();
        }
        $categoryIds[$categoryName] = $categoryId;
        $displayOrder++;
    }

    // First, get all existing slugs to avoid duplicates
    $existingSlugs = [];
    $slugStmt = $pdo->query("SELECT slug FROM services");
    while ($row = $slugStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingSlugs[$row['slug']] = true;
    }

    // Add services with transaction support
    $pdo->beginTransaction();
    
    try {
        // Prepare statements
        $serviceStmt = $pdo->prepare("
            INSERT INTO services 
            (title, slug, description, short_description, duration, status, display_order, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 60, 1, ?, NOW(), NOW())
        ");
        
        $serviceCategoryStmt = $pdo->prepare("
            INSERT IGNORE INTO service_category_mapping 
            (service_id, category_id) 
            VALUES (?, ?)
        ");
        
        $displayOrder = 1;
        $addedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        foreach ($services as $categoryName => $categoryServices) {
            if (!isset($categoryIds[$categoryName])) {
                error_log("Category not found: $categoryName");
                continue;
            }
            
            $categoryId = $categoryIds[$categoryName];
            
            foreach ($categoryServices as $serviceName) {
                // Generate base slug
                $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $serviceName), '-'));
                $slug = $baseSlug;
                $attempt = 0;
                $maxAttempts = 3;
                $success = false;
                
                // Try to find an available slug
                while ($attempt < $maxAttempts) {
                    if ($attempt > 0) {
                        $slug = $baseSlug . '-' . ($attempt === 1 ? uniqid() : uniqid('', true));
                    }
                    
                    // Check if slug is already in use
                    if (!isset($existingSlugs[$slug])) {
                        $existingSlugs[$slug] = true; // Mark as used
                        $success = true;
                        break;
                    }
                    
                    $attempt++;
                }
                
                if (!$success) {
                    $skippedCount++;
                    error_log("Skipping service - could not generate unique slug: $serviceName");
                    echo "<span style='color: orange'>Skipping service - could not generate unique slug: $serviceName</span><br>";
                    continue;
                }
                
                try {
                    $description = "Professional $serviceName service at Nirali Makeup Studio";
                    
                    // Insert the service
                    $serviceStmt->execute([
                        $serviceName,
                        $slug,
                        $description,
                        $description,
                        $displayOrder
                    ]);
                    
                    $serviceId = $pdo->lastInsertId();
                    
                    if (!$serviceId) {
                        throw new Exception("Failed to get service ID after insert");
                    }
                    
                    // Map service to category
                    $serviceCategoryStmt->execute([$serviceId, $categoryId]);
                    
                    $addedCount++;
                    echo "Added service: $serviceName (Slug: $slug)<br>";
                    $displayOrder++;
                    
                } catch (PDOException $e) {
                    $errorCount++;
                    error_log("Error adding service $serviceName: " . $e->getMessage());
                    echo "<span style='color: red'>Error adding service: $serviceName - " . 
                         htmlspecialchars($e->getMessage()) . "</span><br>";
                    
                    // If it's a duplicate entry, add to existing slugs to prevent future conflicts
                    if (strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
                        $existingSlugs[$slug] = true;
                    }
                }
            }
        }
        
        $pdo->commit();
        
        echo "<br>Operation completed.<br>";
        echo "Successfully added: $addedCount services<br>";
        echo "Skipped (duplicate slugs): $skippedCount services<br>";
        echo "Errors: $errorCount services<br>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Fatal error in service import: " . $e->getMessage());
        die("<span style='color: red'>Fatal error: " . htmlspecialchars($e->getMessage()) . "</span>");
    }
}
?>
