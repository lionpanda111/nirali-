<?php
// Start session and include config
session_start();
require_once __DIR__ . '/../includes/config.php';

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

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: login.php');
    exit();
}

// Check if message ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid message ID.';
    header('Location: messages.php');
    exit();
}

$message_id = (int)$_GET['id'];

// Fetch message details
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        $_SESSION['error'] = 'Message not found.';
        header('Location: messages.php');
        exit();
    }
    
    // Mark message as read
    if (!$message['is_read']) {
        $updateStmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$message_id]);
        $message['is_read'] = 1;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching message: ' . $e->getMessage();
    header('Location: messages.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $valid_statuses = ['new', 'in_progress', 'completed', 'spam'];
    
    if (in_array($new_status, $valid_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $message_id]);
            
            $_SESSION['success'] = 'Message status updated successfully!';
            header('Location: message-view.php?id=' . $message_id);
            exit();
        } catch (PDOException $e) {
            $error = 'Error updating message status: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid status selected.';
    }
}


// Include the header
$page_title = 'View Message';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">View Message</h1>
        <div>
            <a href="messages.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Messages
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Message Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo !empty($message['subject']) ? htmlspecialchars($message['subject']) : 'No Subject'; ?>
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?php 
                                echo $message['status'] === 'new' ? 'info' : 
                                    ($message['status'] === 'in_progress' ? 'warning' : 
                                    ($message['status'] === 'completed' ? 'success' : 'danger')); 
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusDropdown">
                            <li><h6 class="dropdown-header">Change Status</h6></li>
                            <form method="post" action="">
                                <li>
                                    <button type="submit" name="update_status" value="new" 
                                            class="dropdown-item <?php echo $message['status'] === 'new' ? 'active' : ''; ?>">
                                        <span class="badge bg-info me-2">New</span> Mark as New
                                    </button>
                                </li>
                                <li>
                                    <button type="submit" name="update_status" value="in_progress" 
                                            class="dropdown-item <?php echo $message['status'] === 'in_progress' ? 'active' : ''; ?>">
                                        <span class="badge bg-warning me-2">In Progress</span> Mark as In Progress
                                    </button>
                                </li>
                                <li>
                                    <button type="submit" name="update_status" value="completed" 
                                            class="dropdown-item <?php echo $message['status'] === 'completed' ? 'active' : ''; ?>">
                                        <span class="badge bg-success me-2">Completed</span> Mark as Completed
                                    </button>
                                </li>
                                <li>
                                    <button type="submit" name="update_status" value="spam" 
                                            class="dropdown-item <?php echo $message['status'] === 'spam' ? 'active' : ''; ?>">
                                        <span class="badge bg-danger me-2">Spam</span> Mark as Spam
                                    </button>
                                </li>
                            </form>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($message['name']); ?></h5>
                            <div class="text-muted small">
                                <?php echo htmlspecialchars($message['email']); ?>
                                <?php if (!empty($message['phone'])): ?>
                                    <br>Phone: <?php echo htmlspecialchars($message['phone']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">
                                <?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?>
                            </div>
                            <a href="messages.php?delete=<?php echo $message['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="message-content mt-4">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>
            </div>
        </div>
                    <h6 class="m-0 font-weight-bold text-primary">Sender Information</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Name:</strong>
                            <div class="text-muted"><?php echo htmlspecialchars($message['name']); ?></div>
                        </li>
                        <li class="mb-2">
                            <strong>Email:</strong>
                            <div class="text-muted">
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </a>
                            </div>
                        </li>
                        <?php if (!empty($message['phone'])): ?>
                            <li class="mb-2">
                                <strong>Phone:</strong>
                                <div class="text-muted">
                                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $message['phone']); ?>">
                                        <?php echo htmlspecialchars($message['phone']); ?>
                                    </a>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <strong>Status:</strong>
                            <div>
                                <span class="badge bg-<?php 
                                    echo $message['status'] === 'new' ? 'info' : 
                                        ($message['status'] === 'in_progress' ? 'warning' : 
                                        ($message['status'] === 'completed' ? 'success' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                                </span>
                            </div>
                        </li>
                        <li class="mb-2">
                            <strong>Received:</strong>
                            <div class="text-muted">
                                <?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?>
                            </div>
                        </li>
                        <?php if ($message['updated_at'] !== $message['created_at']): ?>
                            <li class="mb-2">
                                <strong>Last Updated:</strong>
                                <div class="text-muted">
                                    <?php echo date('F j, Y, g:i a', strtotime($message['updated_at'])); ?>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        
                        <a href="#" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#forwardModal">
                            <i class="fas fa-share me-1"></i> Forward
                        </a>
                        <a href="messages.php?delete=<?php echo $message['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this message?')">
                            <i class="fas fa-trash me-1"></i> Delete Message
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forward Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forwardModalLabel">Forward Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="forward_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="forward_email" name="forward_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="forward_message" class="form-label">Additional Message (Optional)</label>
                        <textarea class="form-control" id="forward_message" name="forward_message" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Forward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.message-content {
    line-height: 1.6;
    white-space: pre-wrap;
}

.message-content p {
    margin-bottom: 1rem;
}

.message-content a {
    word-break: break-all;
}

.badge {
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.dropdown-item.active {
    background-color: #f8f9fa;
    color: #000;
}

.dropdown-item.active .badge {
    color: #fff !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle forward form submission
    const forwardForm = document.querySelector('#forwardModal form');
    if (forwardForm) {
        forwardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('forward_email').value;
            const message = document.getElementById('forward_message').value;
            
            // In a real application, you would send this data to the server via AJAX
            alert('This is a demo. In a real application, this would forward the message to: ' + email);
            
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('forwardModal'));
            modal.hide();
        });
    }
    
    // Auto-resize textarea
    const textarea = document.getElementById('reply_message');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});
</script>
