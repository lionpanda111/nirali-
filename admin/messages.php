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

// Handle message status update
if (isset($_GET['update_status']) && is_numeric($_GET['update_status']) && isset($_GET['status'])) {
    $message_id = (int)$_GET['update_status'];
    $status = $_GET['status'];
    
    // Validate status
    $valid_statuses = ['new', 'in_progress', 'completed', 'spam'];
    if (!in_array($status, $valid_statuses)) {
        $status = 'new';
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $message_id]);
        
        $_SESSION['success'] = 'Message status updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating message status: ' . $e->getMessage();
    }
    
    header('Location: messages.php');
    exit();
}

// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        
        $_SESSION['success'] = 'Message deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting message: ' . $e->getMessage();
    }
    
    header('Location: messages.php');
    exit();
}

// Mark message as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = (int)$_GET['mark_read'];
    
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$message_id]);
        
        // If this was an AJAX request, just exit
        if (isset($_GET['ajax'])) {
            echo '1';
            exit();
        }
    } catch (PDOException $e) {
        if (isset($_GET['ajax'])) {
            echo '0';
            exit();
        }
        $_SESSION['error'] = 'Error marking message as read: ' . $e->getMessage();
    }
    
    if (!isset($_GET['ajax'])) {
        header('Location: messages.php');
        exit();
    }
}

// Fetch all contact messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build the base query
$query = "SELECT * FROM contact_messages";
$count_query = "SELECT COUNT(*) as total FROM contact_messages";

// Add search filter if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if (!empty($status_filter) && in_array($status_filter, ['new', 'in_progress', 'completed', 'spam'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

// Add WHERE clause if needed
if (!empty($where)) {
    $where_clause = " WHERE " . implode(" AND ", $where);
    $query .= $where_clause;
    $count_query .= $where_clause;
}

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
// Make a copy of params for the count query
$count_params = $params;
// Add pagination parameters to the main query
$params[] = (int)$per_page;
$params[] = (int)$offset;

try {
    // Get total count for pagination (without pagination parameters)
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($count_params);
    $total_messages = (int)$stmt->fetchColumn();
    $total_pages = ceil($total_messages / $per_page);
    
    // Get messages for current page (with pagination parameters)
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the query and parameters
    error_log("Query: " . $query);
    error_log("Params: " . print_r($params, true));
    error_log("Total messages: " . $total_messages);
    
    // Get message counts by status
    $status_counts = [
        'all' => 0,
        'new' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'spam' => 0,
        'unread' => 0
    ];
    
    $status_query = "SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status";
    $stmt = $pdo->query($status_query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_counts[$row['status']] = (int)$row['count'];
    }
    
    $unread_query = "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0";
    $status_counts['unread'] = (int)$pdo->query($unread_query)->fetchColumn();
    $status_counts['all'] = array_sum($status_counts) - $status_counts['unread'] - $status_counts['all'];
    
} catch (PDOException $e) {
    $error = 'Error fetching messages: ' . $e->getMessage();
    error_log($error);
    $messages = [];
    $total_messages = 0;
    $total_pages = 1;
    $status_counts = [
        'all' => 0,
        'new' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'spam' => 0,
        'unread' => 0
    ];
}

// Include the header
$page_title = 'Contact Messages';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contact Messages</h1>
        <div>
            <span class="me-3"><?php echo $total_messages; ?> messages found</span>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <!-- Status Filters -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?status=" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo empty($status_filter) ? 'active' : ''; ?>">
                        All Messages
                        <span class="badge bg-primary rounded-pill"><?php echo $status_counts['all']; ?></span>
                    </a>
                    <a href="?status=new" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $status_filter === 'new' ? 'active' : ''; ?>">
                        New
                        <span class="badge bg-info rounded-pill"><?php echo $status_counts['new']; ?></span>
                    </a>
                    <a href="?status=in_progress" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>">
                        In Progress
                        <span class="badge bg-warning rounded-pill"><?php echo $status_counts['in_progress']; ?></span>
                    </a>
                    <a href="?status=completed" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                        Completed
                        <span class="badge bg-success rounded-pill"><?php echo $status_counts['completed']; ?></span>
                    </a>
                    <a href="?status=spam" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $status_filter === 'spam' ? 'active' : ''; ?>">
                        Spam
                        <span class="badge bg-danger rounded-pill"><?php echo $status_counts['spam']; ?></span>
                    </a>
                    <a href="?unread=1" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo isset($_GET['unread']) ? 'active' : ''; ?>">
                        Unread
                        <span class="badge bg-secondary rounded-pill"><?php echo $status_counts['unread']; ?></span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Messages List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php 
                        if (!empty($status_filter)) {
                            echo ucfirst(str_replace('_', ' ', $status_filter)) . ' ';
                        }
                        echo 'Messages';
                        ?>
                    </h6>
                    <div>
                        <form method="get" class="d-inline" id="searchForm">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <?php if (!empty($status_filter)): ?>
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                                <?php endif; ?>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($messages)): ?>
                        <div class="alert alert-info">No messages found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="messagesTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="message-row <?php echo $message['is_read'] ? '' : 'table-primary'; ?>" 
                                            data-id="<?php echo $message['id']; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars($message['email']); ?></div>
                                                <?php if (!empty($message['phone'])): ?>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($message['phone']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($message['subject']) ? htmlspecialchars($message['subject']) : '<em>No subject</em>'; ?></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 100) . (strlen($message['message']) > 100 ? '...' : ''))); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $message['status'] === 'new' ? 'info' : 
                                                        ($message['status'] === 'in_progress' ? 'warning' : 
                                                        ($message['status'] === 'completed' ? 'success' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="message-view.php?id=<?php echo $message['id']; ?>" 
                                                       class="btn btn-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" 
                                                                id="statusDropdown<?php echo $message['id']; ?>" 
                                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                                title="Change Status">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdown<?php echo $message['id']; ?>">
                                                            <li><a class="dropdown-item" href="?update_status=<?php echo $message['id']; ?>&status=new">Mark as New</a></li>
                                                            <li><a class="dropdown-item" href="?update_status=<?php echo $message['id']; ?>&status=in_progress">Mark as In Progress</a></li>
                                                            <li><a class="dropdown-item" href="?update_status=<?php echo $message['id']; ?>&status=completed">Mark as Completed</a></li>
                                                            <li><a class="dropdown-item" href="?update_status=<?php echo $message['id']; ?>&status=spam">Mark as Spam</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a href="#" class="dropdown-item text-danger delete-message" 
                                                                   data-id="<?php echo $message['id']; ?>" 
                                                                   data-name="<?php echo htmlspecialchars($message['name']); ?>">
                                                                    Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" 
                                                       class="btn btn-info" title="Reply">
                                                        <i class="fas fa-reply"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo; Previous</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                <span aria-hidden="true">Next &raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the message from <strong id="deleteMessageName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark message as read when clicking on a row
    const messageRows = document.querySelectorAll('.message-row');
    messageRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't mark as read if clicking on a button or link
            if (e.target.tagName === 'A' || e.target.closest('a') || e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            
            const messageId = this.getAttribute('data-id');
            if (messageId) {
                // Mark as read via AJAX
                fetch(`messages.php?mark_read=${messageId}&ajax=1`)
                    .then(response => response.text())
                    .then(data => {
                        if (data === '1') {
                            this.classList.remove('table-primary');
                            // Update unread count in the UI if needed
                            const unreadBadge = document.querySelector('a[href="?unread=1"] .badge');
                            if (unreadBadge) {
                                const newCount = Math.max(0, parseInt(unreadBadge.textContent) - 1);
                                unreadBadge.textContent = newCount;
                            }
                        }
                    });
            }
            
            // Navigate to the message view
            const viewLink = this.querySelector('a[title="View"]');
            if (viewLink) {
                window.location.href = viewLink.href;
            }
        });
    });
    
    // Delete message confirmation
    const deleteButtons = document.querySelectorAll('.delete-message');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const messageId = this.getAttribute('data-id');
            const messageName = this.getAttribute('data-name');
            
            document.getElementById('deleteMessageName').textContent = messageName;
            confirmDeleteBtn.href = `messages.php?delete=${messageId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
    
    // Handle search form submission
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput && searchInput.value.trim() === '') {
                this.action = window.location.pathname;
                if (window.location.search.includes('status=')) {
                    this.action += '?' + window.location.search;
                }
            }
        });
    }
});
</script>
