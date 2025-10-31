<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/config.php';  // This contains the database connection

$pageTitle = 'Instagram Reels Management';

// Initialize variables
$error = '';
$success = '';

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed. Please check your database configuration.");
}

// File upload configuration
$uploadDir = __DIR__ . '/../../uploads/reels/';
$thumbnailDir = $uploadDir . 'thumbnails/';
$allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
$maxFileSize = 100 * 1024 * 1024; // 100MB

// Create directories if they don't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (!file_exists($thumbnailDir)) {
    mkdir($thumbnailDir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_reel') {
            // Initialize form fields with default values
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $caption = isset($_POST['caption']) ? trim($_POST['caption']) : '';
            $instagramUrl = isset($_POST['instagram_url']) ? trim($_POST['instagram_url']) : '';
            $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $displayLocation = isset($_POST['display_location']) ? $_POST['display_location'] : 'home';
            
            if (empty($title)) {
                $error = 'Title is required';
            } elseif (empty($_FILES['video']['name']) && empty($instagramUrl)) {
                $error = 'Either upload a video or provide an Instagram URL';
            } else {
                $videoPath = '';
                $thumbnailPath = '';
                $isUploaded = 0;
                
                // Handle file upload
                if (!empty($_FILES['video']['name'])) {
                    $file = $_FILES['video'];
                    $fileType = mime_content_type($file['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        $error = 'Invalid video format. Allowed formats: MP4, WebM, OGG';
                    } elseif ($file['size'] > $maxFileSize) {
                        $error = 'File is too large. Maximum size is 100MB';
                    } else {
                        // Generate unique filename
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('reel_') . '.' . $extension;
                        $targetFile = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                            $videoPath = 'uploads/reels/' . $filename;
                            $isUploaded = 1;
                            
                            // Generate thumbnail
                            $thumbnailFile = $thumbnailDir . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                            if (function_exists('generateVideoThumbnail')) {
                                if (generateVideoThumbnail($targetFile, $thumbnailFile)) {
                                    $thumbnailPath = 'uploads/reels/thumbnails/' . basename($thumbnailFile);
                                }
                            } else {
                                // Fallback if function doesn't exist
                                $thumbnailPath = '';
                            }
                        } else {
                            $error = 'Error uploading file. Please try again.';
                        }
                    }
                }
                
                if (!isset($error)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO nirali_reels 
                            (title, description, instagram_url, video_path, thumbnail_path, display_order, is_active, display_location) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title,
                            $caption,
                            !empty($instagramUrl) ? $instagramUrl : null,
                            $videoPath,
                            $thumbnailPath,
                            $displayOrder,
                            $isActive,
                            $displayLocation
                        ]);
                        $success = 'Reel added successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error adding reel: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete_reel' && !empty($_POST['reel_id'])) {
            try {
                // First, get the reel to delete its files
                $stmt = $pdo->prepare("SELECT video_path, thumbnail_path FROM nirali_reels WHERE id = ?");
                $stmt->execute([$_POST['reel_id']]);
                $reel = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($reel) {
                    // Delete the video file if it exists
                    if (!empty($reel['video_path']) && file_exists('../../' . $reel['video_path'])) {
                        unlink('../../' . $reel['video_path']);
                    }
                    
                    // Delete the thumbnail file if it exists
                    if (!empty($reel['thumbnail_path']) && file_exists('../../' . $reel['thumbnail_path'])) {
                        unlink('../../' . $reel['thumbnail_path']);
                    }
                    
                    // Delete the database record
                    $stmt = $pdo->prepare("DELETE FROM nirali_reels WHERE id = ?");
                    $stmt->execute([$_POST['reel_id']]);
                    $success = 'Reel deleted successfully!';
                }
            } catch (PDOException $e) {
                $error = 'Error deleting reel: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['reel_id'], $_POST['is_active'])) {
            try {
                $stmt = $pdo->prepare("UPDATE nirali_reels SET is_active = ? WHERE id = ?");
                $stmt->execute([(int)$_POST['is_active'], $_POST['reel_id']]);
                echo 'Status updated';
                exit;
            } catch (PDOException $e) {
                http_response_code(500);
                echo 'Error updating status';
                exit;
            }
        } elseif ($_POST['action'] === 'update_reel' && !empty($_POST['reel_id'])) {
            try {
                $title = trim($_POST['title']);
                $caption = trim($_POST['caption']);
                $displayOrder = (int)$_POST['display_order'];
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                $displayLocation = isset($_POST['display_location']) ? $_POST['display_location'] : 'home';
                
                $stmt = $pdo->prepare("UPDATE nirali_reels SET 
                    title = ?, 
                    description = ?, 
                    display_order = ?, 
                    is_active = ?,
                    display_location = ?,
                    updated_at = NOW() 
                    WHERE id = ?");
                
                $stmt->execute([
                    $title,
                    $caption,
                    $displayOrder,
                    $isActive,
                    $displayLocation,
                    $_POST['reel_id']
                ]);
                
                $success = 'Reel updated successfully!';
            } catch (PDOException $e) {
                $error = 'Error updating reel: ' . $e->getMessage();
                error_log($error);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_reel' && !empty($_GET['reel_id'])) {
    // Handle AJAX request to get reel data for editing
    try {
        $stmt = $pdo->prepare("SELECT * FROM nirali_reels WHERE id = ?");
        $stmt->execute([$_GET['reel_id']]);
        $reel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reel) {
            header('Content-Type: application/json');
            echo json_encode($reel);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Reel not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error fetching reel data']);
    }
    exit;
}

// Get all reels with display location
$reels = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT *, 
            CASE 
                WHEN display_location = 'home' THEN 'Home Page' 
                WHEN display_location = 'academy' THEN 'Academy Page' 
                WHEN display_location = 'both' THEN 'Both Pages' 
                ELSE 'Home Page' 
            END as display_location_text 
            FROM nirali_reels ORDER BY display_order, created_at DESC");
            
        if ($stmt) {
            $reels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = 'Error fetching reels: ' . $e->getMessage();
        error_log($error);
    }
} else {
    $error = 'Database connection failed. Please check your configuration.';
    error_log($error);
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <main class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReelModal">
                    <i class="fas fa-plus me-2"></i> Add New Reel
                </button>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Manage Reels</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reels)): ?>
                        <div class="alert alert-info mb-0">No reels found. Add your first reel using the button above.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="100">Thumbnail</th>
                                        <th>Caption</th>
                                        <th>URL</th>
                                        <th>Order</th>
                                        <th>Display On</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reels as $reel): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($reel['thumbnail_url']); ?>" 
                                                     alt="Reel Thumbnail" 
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                            </td>
                                            <td><?php echo htmlspecialchars($reel['caption'] ?? 'No caption'); ?></td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($reel['reel_url']); ?>" 
                                                   target="_blank" 
                                                   class="text-truncate d-inline-block" 
                                                   style="max-width: 200px;">
                                                    <?php echo htmlspecialchars($reel['reel_url']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo (int)$reel['display_order']; ?></td>
                                            <td><?php echo htmlspecialchars($reel['display_location_text'] ?? 'Home Page'); ?></td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" 
                                                           type="checkbox" 
                                                           data-reel-id="<?php echo $reel['id']; ?>"
                                                           <?php echo $reel['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">
                                                        <?php echo $reel['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary edit-reel" data-reel-id="<?php echo $reel['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this reel?');">
                                                    <input type="hidden" name="action" value="delete_reel">
                                                    <input type="hidden" name="reel_id" value="<?php echo $reel['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Reel Modal -->
<div class="modal fade" id="addReelModal" tabindex="-1" aria-labelledby="addReelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReelModalLabel">Add New Instagram Reel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               placeholder="Enter a title for this reel">
                    </div>
                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">Instagram Reel URL</label>
                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                               placeholder="https://www.instagram.com/reel/ABC123/">
                        <div class="form-text">Either upload a video or provide an Instagram URL</div>
                    </div>
                    <div class="mb-3">
                        <label for="video" class="form-label">Or Upload Video</label>
                        <input type="file" class="form-control" id="video" name="video" accept="video/*">
                        <div class="form-text">Max file size: 100MB. Supported formats: MP4, WebM, OGG</div>
                    </div>
                    <div class="mb-3">
                        <label for="caption" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="caption" name="caption" rows="2" 
                               placeholder="Enter a description for this reel"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Location</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="display_home" value="home" checked>
                            <label class="form-check-label" for="display_home">
                                Home Page Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="display_academy" value="academy">
                            <label class="form-check-label" for="display_academy">
                                Academy Page Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="display_both" value="both">
                            <label class="form-check-label" for="display_both">
                                Both Pages
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="add_reel" class="btn btn-primary">Add Reel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Reel Modal -->
<div class="modal fade" id="editReelModal" tabindex="-1" aria-labelledby="editReelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="editReelForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editReelModalLabel">Edit Reel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="reel_id" id="edit_reel_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_caption" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="edit_caption" name="caption" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_display_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Active
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Location</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="edit_display_home" value="home">
                            <label class="form-check-label" for="edit_display_home">
                                Home Page Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="edit_display_academy" value="academy">
                            <label class="form-check-label" for="edit_display_academy">
                                Academy Page Only
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="display_location" id="edit_display_both" value="both">
                            <label class="form-check-label" for="edit_display_both">
                                Both Pages
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="update_reel" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle status toggle
$(document).ready(function() {
    // Status toggle
    $('.status-toggle').change(function() {
        const reelId = $(this).data('reel-id');
        const isActive = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'update_status',
                reel_id: reelId,
                is_active: isActive
            },
            success: function(response) {
                // Update the label text
                const label = $(`input[data-reel-id="${reelId}"]`).siblings('label');
                label.text(isActive ? 'Active' : 'Inactive');
            },
            error: function() {
                alert('Error updating status');
                // Revert the toggle if there was an error
                $(`input[data-reel-id="${reelId}"]`).prop('checked', !isActive);
            }
        });
    });

    // Handle edit button click
    $('.edit-reel').click(function() {
        const reelId = $(this).data('reel-id');
        
        // Fetch reel data via AJAX
        $.ajax({
            url: window.location.href,
            type: 'GET',
            data: {
                action: 'get_reel',
                reel_id: reelId
            },
            dataType: 'json',
            success: function(reel) {
                if (reel) {
                    // Populate the edit form
                    $('#edit_reel_id').val(reel.id);
                    $('#edit_title').val(reel.title);
                    $('#edit_caption').val(reel.description || '');
                    $('#edit_display_order').val(reel.display_order);
                    $('#edit_is_active').prop('checked', reel.is_active == 1);
                    
                    // Set the display location radio button
                    $(`#edit_display_${reel.display_location || 'home'}`).prop('checked', true);
                    
                    // Show the modal
                    const editModal = new bootstrap.Modal(document.getElementById('editReelModal'));
                    editModal.show();
                } else {
                    alert('Error loading reel data');
                }
            },
            error: function() {
                alert('Error loading reel data');
            }
        });
    });

    // Handle edit form submission
    $('#editReelForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update_reel');
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                location.reload(); // Reload to see changes
            },
            error: function() {
                alert('Error updating reel');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
