<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and functions
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in and is admin
checkAdminAccess();

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete_service_image':
            // Handle service image deletion
            $imageId = (int)($_POST['image_id'] ?? 0);
            $imagePath = $_POST['image_path'] ?? '';
            
            if ($imageId > 0 && !empty($imagePath)) {
                try {
                    // Get database connection
                    $pdo = getDBConnection();
                    
                    // Check if the image exists and belongs to a service the user can access
                    $stmt = $pdo->prepare(
                        "DELETE FROM service_images 
                         WHERE id = ? 
                         AND (SELECT COUNT(*) FROM services s WHERE s.id = service_id) > 0"
                    );
                    $stmt->execute([$imageId]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Delete the actual file
                        $fullPath = '../' . ltrim($imagePath, '/');
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        
                        $response = [
                            'success' => true,
                            'message' => 'Image deleted successfully.'
                        ];
                    } else {
                        $response['message'] = 'Image not found or you do not have permission to delete it.';
                    }
                } catch (PDOException $e) {
                    $response['message'] = 'Database error: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Invalid image ID or path.';
            }
            break;
            
        default:
            $response['message'] = 'Invalid action specified.';
            break;
    }
}

// Return JSON response
echo json_encode($response);
exit;
