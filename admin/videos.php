<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration and functions
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // If not admin, show login form
    if (!empty($_POST)) {
        // If this is a POST request, we need to handle it properly
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Please login first']);
        exit();
    }
    // For GET requests, show the login page
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login if not admin
    $_SESSION['error'] = 'You do not have permission to access this page';
    header('Location: login.php');
    exit();
}

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $display_order = (int)$_POST['display_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Handle file uploads
                $video_url = '';
                $thumbnail_url = '';
                
                // Upload video
                if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                    $video_url = uploadFile($_FILES['video_file'], 'videos', ['video/mp4', 'video/webm', 'video/ogg']);
                } elseif (isset($_POST['video_url']) && !empty($_POST['video_url'])) {
                    $video_url = $_POST['video_url'];
                }
                
                // Upload thumbnail
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails', ['image/jpeg', 'image/png', 'image/gif']);
                } elseif (isset($_POST['thumbnail_url']) && !empty($_POST['thumbnail_url'])) {
                    $thumbnail_url = $_POST['thumbnail_url'];
                }
                
                if ($_POST['action'] === 'add') {
                    // Add new video
                    $stmt = $pdo->prepare("INSERT INTO videos (title, description, video_url, thumbnail_url, display_order, is_active) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $video_url, $thumbnail_url, $display_order, $is_active]);
                    
                    $_SESSION['success'] = 'Video added successfully!';
                } else {
                    // Update existing video
                    $id = (int)$_POST['id'];
                    $update_fields = [
                        'title' => $title,
                        'description' => $description,
                        'display_order' => $display_order,
                        'is_active' => $is_active,
                        'id' => $id
                    ];
                    
                    $sql = "UPDATE videos SET title = :title, description = :description, display_order = :display_order, is_active = :is_active";
                    
                    if (!empty($video_url)) {
                        $sql .= ", video_url = :video_url";
                        $update_fields['video_url'] = $video_url;
                    }
                    
                    if (!empty($thumbnail_url)) {
                        $sql .= ", thumbnail_url = :thumbnail_url";
                        $update_fields['thumbnail_url'] = $thumbnail_url;
                    }
                    
                    $sql .= " WHERE id = :id";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($update_fields);
                    
                    $_SESSION['success'] = 'Video updated successfully!';
                }
            } elseif ($_POST['action'] === 'delete') {
                // Delete video
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success'] = 'Video deleted successfully!';
            }
            
            header('Location: videos.php');
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    // Fetch all videos
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY display_order, title");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database connection error: ' . $e->getMessage();
    $videos = [];
}

$page_title = 'Manage Videos';
include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Videos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Videos</li>
    </ol>
    
    <?php displayAlert(); ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-video me-1"></i>
                Videos List
            </h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                <i class="fas fa-plus me-1"></i> Add New Video
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($videos)): ?>
                <div class="alert alert-info">No videos found. Add your first video using the button above.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="videosTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>URL</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $index => $video): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if (!empty($video['thumbnail_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="Thumbnail" style="width: 80px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 45px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($video['video_url']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo (int)$video['display_order']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $video['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $video['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-video" 
                                                data-id="<?php echo $video['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($video['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($video['description']); ?>"
                                                data-video-url="<?php echo htmlspecialchars($video['video_url']); ?>"
                                                data-thumbnail-url="<?php echo htmlspecialchars($video['thumbnail_url']); ?>"
                                                data-display-order="<?php echo (int)$video['display_order']; ?>"
                                                data-is-active="<?php echo (int)$video['is_active']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="videos.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
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
</div>

<!-- Add/Edit Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="videoForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Add New Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="videoId" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="video_file" class="form-label">Video File</label>
                            <input type="file" class="form-control" id="video_file" name="video_file" accept="video/*">
                            <small class="form-text text-muted">Upload a video file or provide a URL below.</small>
                            
                            <div class="mt-2">
                                <label for="video_url" class="form-label">- OR - Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://example.com/video.mp4">
                            </div>
                            
                            <div id="videoPreview" class="mt-2 d-none">
                                <video id="videoPlayer" controls class="img-fluid" style="max-height: 200px;"></video>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="thumbnail" class="form-label">Thumbnail Image</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            <small class="form-text text-muted">Upload a thumbnail image or provide a URL below.</small>
                            
                            <div class="mt-2">
                                <label for="thumbnail_url" class="form-label">- OR - Thumbnail URL</label>
                                <input type="url" class="form-control" id="thumbnail_url" name="thumbnail_url" placeholder="https://example.com/thumbnail.jpg">
                            </div>
                            
                            <div id="thumbnailPreview" class="mt-2 text-center">
                                <img id="thumbnailImage" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="img-thumbnail" style="max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#videosTable').DataTable({
        responsive: true,
        order: [[4, 'asc']], // Sort by display_order by default
        columnDefs: [
            { orderable: false, targets: [1, 6] } // Disable sorting on thumbnail and actions columns
        ]
    });
    
    // Show add video modal
    $('.btn-add-video').click(function() {
        $('#videoForm')[0].reset();
        $('#videoModalLabel').text('Add New Video');
        $('#formAction').val('add');
        $('#videoId').val('');
        $('#videoPreview').addClass('d-none');
        $('#thumbnailImage').hide();
        $('#videoModal').modal('show');
    });
    
    // Show edit video modal
    $(document).on('click', '.edit-video', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const description = $(this).data('description');
        const videoUrl = $(this).data('video-url');
        const thumbnailUrl = $(this).data('thumbnail-url');
        const displayOrder = $(this).data('display-order');
        const isActive = $(this).data('is-active');
        
        $('#videoModalLabel').text('Edit Video');
        $('#formAction').val('edit');
        $('#videoId').val(id);
        $('#title').val(title);
        $('#description').val(description);
        $('#video_url').val(videoUrl);
        $('#thumbnail_url').val(thumbnailUrl);
        $('#display_order').val(displayOrder);
        $('#is_active').prop('checked', isActive === 1);
        
        // Show video preview if URL exists
        if (videoUrl) {
            const videoPlayer = $('#videoPlayer')[0];
            videoPlayer.src = videoUrl;
            $('#videoPreview').removeClass('d-none');
        } else {
            $('#videoPreview').addClass('d-none');
        }
        
        // Show thumbnail preview if URL exists
        if (thumbnailUrl) {
            $('#thumbnailImage').attr('src', thumbnailUrl).show();
        } else {
            $('#thumbnailImage').hide();
        }
        
        $('#videoModal').modal('show');
    });
    
    // Preview video when URL changes
    $('#video_url').on('change keyup', function() {
        const url = $(this).val();
        if (url) {
            const videoPlayer = $('#videoPlayer')[0];
            videoPlayer.src = url;
            $('#videoPreview').removeClass('d-none');
        } else {
            $('#videoPreview').addClass('d-none');
        }
    });
    
    // Preview thumbnail when URL changes
    $('#thumbnail_url').on('change keyup', function() {
        const url = $(this).val();
        if (url) {
            $('#thumbnailImage').attr('src', url).show();
        } else {
            $('#thumbnailImage').hide();
        }
    });
    
    // Preview selected thumbnail file
    $('#thumbnail').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#thumbnailImage').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Clear file input when modal is hidden
    $('#videoModal').on('hidden.bs.modal', function() {
        $('#videoForm')[0].reset();
        $('#videoPreview').addClass('d-none');
        $('#thumbnailImage').hide().attr('src', '');
    });
});
</script>

<?php
// Close the database connection if it was opened
if (isset($pdo)) {
    $pdo = null;
}
?>
