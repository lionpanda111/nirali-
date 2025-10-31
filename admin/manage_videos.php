<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration
require_once __DIR__ . '/../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = 'Please log in to access the admin panel.';
    header('Location: login.php');
    exit();
}

// Get database connection
try {
    $pdo = getDBConnection();
    if ($pdo === null) {
        throw new Exception("Failed to connect to database");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Error: Could not connect to the database. Please try again later.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $display_order = (int)$_POST['display_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Handle file uploads
                $video_url = '';
                $thumbnail_url = '';
                
                // Upload video file
                if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                    $target_dir = __DIR__ . "/../uploads/videos/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $video_file = $target_dir . basename($_FILES['video_file']['name']);
                    $video_file_type = strtolower(pathinfo($video_file, PATHINFO_EXTENSION));
                    $video_file_name = 'video_' . time() . '.' . $video_file_type;
                    $target_file = $target_dir . $video_file_name;
                    
                    // Check file size (max 50MB)
                    if ($_FILES['video_file']['size'] > 50000000) {
                        throw new Exception("Sorry, your video file is too large. Maximum size is 50MB.");
                    }
                    
                    // Allow certain file formats
                    $allowed_video_types = ['mp4', 'webm', 'ogg'];
                    if (!in_array($video_file_type, $allowed_video_types)) {
                        throw new Exception("Sorry, only MP4, WebM & OGG files are allowed.");
                    }
                    
                    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target_file)) {
                        $video_url = 'uploads/videos/' . $video_file_name;
                    } else {
                        throw new Exception("Sorry, there was an error uploading your video file.");
                    }
                } elseif (isset($_POST['video_url']) && !empty($_POST['video_url'])) {
                    $video_url = $_POST['video_url'];
                }
                
                // Upload thumbnail
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $target_dir = __DIR__ . "/../uploads/thumbnails/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $thumbnail_file = $target_dir . basename($_FILES['thumbnail']['name']);
                    $thumbnail_file_type = strtolower(pathinfo($thumbnail_file, PATHINFO_EXTENSION));
                    $thumbnail_file_name = 'thumb_' . time() . '.' . $thumbnail_file_type;
                    $target_thumb = $target_dir . $thumbnail_file_name;
                    
                    // Check if image file is an actual image
                    $check = getimagesize($_FILES['thumbnail']['tmp_name']);
                    if ($check === false) {
                        throw new Exception("File is not an image.");
                    }
                    
                    // Check file size (max 5MB)
                    if ($_FILES['thumbnail']['size'] > 5000000) {
                        throw new Exception("Sorry, your thumbnail file is too large. Maximum size is 5MB.");
                    }
                    
                    // Allow certain file formats
                    $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($thumbnail_file_type, $allowed_image_types)) {
                        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed for thumbnails.");
                    }
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_thumb)) {
                        $thumbnail_url = 'uploads/thumbnails/' . $thumbnail_file_name;
                    } else {
                        throw new Exception("Sorry, there was an error uploading your thumbnail file.");
                    }
                } elseif (isset($_POST['thumbnail_url']) && !empty($_POST['thumbnail_url'])) {
                    $thumbnail_url = $_POST['thumbnail_url'];
                }
                
                if ($_POST['action'] === 'add') {
                    // Add new video
                    if (empty($video_url)) {
                        throw new Exception("Video URL or file is required");
                    }
                    
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
                    
                    $sql = "UPDATE videos SET title = :title, description = :description, 
                            display_order = :display_order, is_active = :is_active";
                    
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
                
                // Get video info before deleting
                $stmt = $pdo->prepare("SELECT video_url, thumbnail_url FROM videos WHERE id = ?");
                $stmt->execute([$id]);
                $video = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete the record
                $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete associated files
                if ($video) {
                    if (!empty($video['video_url']) && file_exists(__DIR__ . '/../' . $video['video_url'])) {
                        unlink(__DIR__ . '/../' . $video['video_url']);
                    }
                    if (!empty($video['thumbnail_url']) && file_exists(__DIR__ . '/../' . $video['thumbnail_url'])) {
                        unlink(__DIR__ . '/../' . $video['thumbnail_url']);
                    }
                }
                
                $_SESSION['success'] = 'Video deleted successfully!';
            }
            
            header('Location: manage_videos.php');
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

// Fetch all videos
$videos = [];
try {
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY display_order, created_at DESC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching videos: ' . $e->getMessage();
}

// Include header
$page_title = 'Manage Videos';
include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Videos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Videos</li>
    </ol>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-video me-1"></i>
                Videos List
            </h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal" onclick="resetForm()">
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
                                <th>Status</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $index => $video): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if (!empty($video['thumbnail_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($video['thumbnail_url']); ?>" 
                                                 alt="Thumbnail" 
                                                 style="width: 80px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 45px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $video['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $video['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int)$video['display_order']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-video" 
                                                data-id="<?php echo $video['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($video['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($video['description']); ?>"
                                                data-video-url="<?php echo htmlspecialchars($video['video_url']); ?>"
                                                data-thumbnail-url="<?php echo htmlspecialchars($video['thumbnail_url'] ?? ''); ?>"
                                                data-display-order="<?php echo (int)$video['display_order']; ?>"
                                                data-is-active="<?php echo (int)$video['is_active']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-video" 
                                                data-id="<?php echo $video['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($video['title']); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
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
            <form id="videoForm" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Add New Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="videoId" value="">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title">
                        <div class="form-text">Optional: Add a title for the video</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="video_file" class="form-label">Video File</label>
                        <input class="form-control" type="file" id="video_file" name="video_file" accept="video/*">
                        <div class="form-text">Max size: 50MB. Supported formats: MP4, WebM, OGG</div>
                        <div id="videoPreview" class="mt-2 d-none">
                            <video id="videoPlayer" controls class="img-fluid rounded" style="max-height: 200px;"></video>
                        </div>
                        <input type="hidden" name="video_url" id="video_url">
                    </div>
                    
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Thumbnail</label>
                        <input class="form-control" type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        <div class="form-text">Recommended size: 16:9 aspect ratio. Max size: 5MB</div>
                        <div id="thumbnailPreview" class="mt-2">
                            <img id="thumbnailImage" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" 
                                 alt="Thumbnail Preview" 
                                 class="img-thumbnail d-none" 
                                 style="max-height: 150px;">
                        </div>
                        <input type="hidden" name="thumbnail_url" id="thumbnail_url">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the video "<span id="videoTitle"></span>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
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
            { orderable: false, targets: [1, 5] } // Disable sorting on thumbnail and actions columns
        ]
    });
    
    // Handle edit button click
    $('.edit-video').click(function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const description = $(this).data('description');
        const videoUrl = $(this).data('video-url');
        const thumbnailUrl = $(this).data('thumbnail-url');
        const displayOrder = $(this).data('display-order');
        const isActive = $(this).data('is-active');
        
        $('#videoModalLabel').text('Edit Video');
        $('#videoId').val(id);
        $('#title').val(title);
        $('#description').val(description);
        $('#display_order').val(displayOrder);
        $('#is_active').prop('checked', isActive === 1);
        $('#video_url').val(videoUrl);
        $('#thumbnail_url').val(thumbnailUrl);
        $('#formAction').val('edit');
        
        // Show video preview if URL exists
        if (videoUrl) {
            const videoPlayer = $('#videoPlayer')[0];
            videoPlayer.src = '../' + videoUrl;
            $('#videoPreview').removeClass('d-none');
        } else {
            $('#videoPreview').addClass('d-none');
        }
        
        // Show thumbnail preview if URL exists
        const thumbnailImg = $('#thumbnailImage');
        if (thumbnailUrl) {
            thumbnailImg.attr('src', '../' + thumbnailUrl);
            thumbnailImg.removeClass('d-none');
        } else {
            thumbnailImg.addClass('d-none');
        }
        
        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById('videoModal'));
        modal.show();
    });
    
    // Handle delete button click
    $('.delete-video').click(function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        
        $('#deleteId').val(id);
        $('#videoTitle').text(title);
        
        var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });
    
    // Preview video when file is selected
    $('#video_file').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const videoPlayer = $('#videoPlayer')[0];
            const videoUrl = URL.createObjectURL(file);
            videoPlayer.src = videoUrl;
            $('#videoPreview').removeClass('d-none');
            $('#video_url').val(''); // Clear URL if file is uploaded
        }
    });
    
    // Preview thumbnail when file is selected
    $('#thumbnail').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const thumbnailImg = $('#thumbnailImage');
                thumbnailImg.attr('src', e.target.result);
                thumbnailImg.removeClass('d-none');
                $('#thumbnail_url').val(''); // Clear URL if file is uploaded
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Handle form submission
    $('#videoForm').on('submit', function(e) {
        const videoFile = $('#video_file').val();
        const videoUrl = $('#video_url').val();
        
        if (!videoFile && !videoUrl) {
            e.preventDefault();
            alert('Please select a video file or enter a video URL');
            return false;
        }
        
        // If title is empty, set a default title
        if ($('#title').val().trim() === '') {
            const defaultTitle = 'Video - ' + new Date().toLocaleDateString();
            $('<input>').attr({
                type: 'hidden',
                name: 'title',
                value: defaultTitle
            }).appendTo('#videoForm');
        }
        
        return true;
    });
});

// Reset form when adding new video
function resetForm() {
    $('#videoForm')[0].reset();
    $('#videoModalLabel').text('Add New Video');
    $('#videoId').val('');
    $('#formAction').val('add');
    $('#videoPreview').addClass('d-none');
    $('#thumbnailImage').addClass('d-none');
    $('#videoPlayer').attr('src', '');
    $('#thumbnailImage').attr('src', 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
    $('#video_url').val('');
    $('#thumbnail_url').val('');
}
</script>
