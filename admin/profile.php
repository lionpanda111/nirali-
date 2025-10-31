<?php
// Set the page title
$page_title = 'My Profile';

// Include header
include 'includes/header.php';

// Get admin details
$admin_id = $_SESSION['admin_id'];
$error = '';
$success = '';

// Fetch admin data
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($full_name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists. Please use a different email address.';
        } else {
            // Check if changing password
            $password_changed = false;
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error = 'Please fill in all password fields to change your password.';
                } elseif (!password_verify($current_password, $admin['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New password and confirm password do not match.';
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $password_changed = true;
                }
            }
            
            if (empty($error)) {
                try {
                    // Update admin details
                    if ($password_changed) {
                        $stmt = $pdo->prepare("UPDATE admin_users SET full_name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$full_name, $email, $password_hash, $admin_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE admin_users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$full_name, $email, $admin_id]);
                    }
                    
                    // Update session variables
                    $_SESSION['admin_name'] = $full_name;
                    
                    $success = 'Profile updated successfully!';
                    
                    // Refresh admin data
                    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="mb-3">Change Password</h5>
                    <p class="text-muted">Leave these fields blank if you don't want to change your password.</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
