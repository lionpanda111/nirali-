<?php
$page_title = 'Manage Courses';
require_once __DIR__ . '/../includes/config.php';
require_once 'includes/header.php';

// Get PDO connection
try {
    $pdo = getDBConnection();

    // Handle course deletion
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        
        // First, get the course image path if exists
        $stmt = $pdo->prepare("SELECT image FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && !empty($row['image']) && file_exists('../../' . $row['image'])) {
            unlink('../../' . $row['image']);
        }
        
        // Delete the course
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Course deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting course";
        }
        header('Location: courses.php');
        exit();
    }

    // Toggle course status
    if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
        $id = (int)$_GET['toggle_status'];
        $pdo->query("UPDATE courses SET status = 1 - status WHERE id = $id");
        header('Location: courses.php');
        exit();
    }

    // Fetch all courses
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY display_order, title");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manage Courses</h1>
        <a href="course-edit.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="coursesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($courses)): ?>
                            <?php $count = 1; foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td>
                                        <?php if (!empty($course['image'])): ?>
                                            <img src="../../<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 4px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($course['title']) ?></td>
                                    <td><?= htmlspecialchars($course['duration']) ?></td>
                                    <td>$<?= number_format($course['price'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $course['status'] ? 'success' : 'secondary' ?>" style="cursor: pointer;" 
                                              onclick="window.location.href='courses.php?toggle_status=<?= $course['id'] ?>'">
                                            <?= $course['status'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="course-edit.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $course['id'] ?>, '<?= htmlspecialchars(addslashes($course['title'])) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No courses found. <a href="course-edit.php">Add your first course</a>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete the course "${title}"? This action cannot be undone.`)) {
        window.location.href = `courses.php?delete=${id}`;
    }
}

// Initialize DataTable
$(document).ready(function() {
    $('#coursesTable').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 6] } // Disable sorting on image and actions columns
        ]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
