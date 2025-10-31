<?php
session_start();

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Initialize database connection
$pdo = getDBConnection();
if ($pdo === null) {
    die('Could not connect to the database. Please try again later.');
}

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$page_title = 'Admin Login';
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Debug: Log the login attempt
    error_log("Login attempt - Username: $username");
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
        error_log("Login failed: Empty username or password");
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, status FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                error_log("User found in database: " . json_encode($user));
                
                // Debug: Check if password matches
                $passwordMatch = password_verify($password, $user['password']);
                error_log("Password verification result: " . ($passwordMatch ? 'Match' : 'No match'));
                
                if ($passwordMatch && $user['status'] == 1) {
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['full_name'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    // Set remember me cookie if checked
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 days
                        setcookie('remember_token', $token, $expires, '/');
                        
                        // Store token in database
                        $updateToken = $pdo->prepare("UPDATE admin_users SET remember_token = ?, token_expires = ? WHERE id = ?");
                        $updateToken->execute([$token, date('Y-m-d H:i:s', $expires), $user['id']]);
                    }
                    
                    // Redirect to dashboard
                    header('Location: index.php');
                    exit;
                } elseif ($passwordMatch) {
                    $error = 'Your account has been deactivated. Please contact the administrator.';
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo $page_title; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo i {
            font-size: 3rem;
            color: #ff6b6b;
            margin-bottom: 15px;
        }
        .login-logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .form-control:focus {
            border-color: #ff6b6b;
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 107, 0.25);
        }
        .btn-login {
            background-color: #ff6b6b;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        .btn-login:hover {
            background-color: #ff5252;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-paint-brush"></i>
            <h1>Nirali Makeup Studio</h1>
            <p class="text-muted">Admin Panel</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
            
           
        </form>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
