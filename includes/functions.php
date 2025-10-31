<?php
/**
 * Upload a file to the server
 * 
 * @param array $file The $_FILES array element
 * @param string $subdir The subdirectory to upload to (e.g., 'videos', 'thumbnails')
 * @param array $allowed_types Array of allowed MIME types
 * @return string The path to the uploaded file
 * @throws Exception If upload fails
 */
function uploadFile($file, $subdir = 'uploads', $allowed_types = []) {
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file type if allowed types are specified
    if (!empty($allowed_types)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
        }
    }
    
    // Generate a unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_ext;
    $destination = $upload_dir . $filename;
    
    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Return the relative path
    return '/uploads/' . $subdir . '/' . $filename;
}

/**
 * Check if current page is active
 * 
 * @param string $page The page to check against
 * @return string Returns 'active' if current page matches, empty string otherwise
 */
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page) ? 'active' : '';
}

/**
 * Format duration in minutes to a human-readable format
 * 
 * @param int $minutes Duration in minutes
 * @return string Formatted duration (e.g., "1 hour 30 mins")
 */
function format_duration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' mins';
    }
    
    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;
    
    $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
    
    if ($remaining_minutes > 0) {
        $result .= ' ' . $remaining_minutes . ' min' . ($remaining_minutes > 1 ? 's' : '');
    }
    
    return $result;
}

/**
 * Sanitize user input
 * 
 * @param string $data The input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate a slug from a string
 * 
 * @param string $text The text to convert to a slug
 * @return string The generated slug
 */
function generate_slug($text) {
    // Replace non-letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

/**
 * Get the first paragraph from a string of HTML
 * 
 * @param string $html The HTML content
 * @return string The first paragraph
 */
function get_first_paragraph($html) {
    if (preg_match('/<p[^>]*>(.*?)<\/p>/s', $html, $matches)) {
        return strip_tags($matches[1]);
    }
    return '';
}

/**
 * Truncate text to a specified length
 * 
 * @param string $text The text to truncate
 * @param int $length The maximum length
 * @param string $suffix The suffix to append if truncated
 * @return string The truncated text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . $suffix;
    }
    return $text;
}

/**
 * Check if a string starts with a specific substring
 * 
 * @param string $haystack The string to search in
 * @param string $needle The substring to search for
 * @return bool True if $haystack starts with $needle
 */
function starts_with($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

/**
 * Check if a string ends with a specific substring
 * 
 * @param string $haystack The string to search in
 * @param string $needle The substring to search for
 * @return bool True if $haystack ends with $needle
 */
function ends_with($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

/**
 * Format a date in a readable format
 * 
 * @param string $date The date string
 * @param string $format The format to use (default: 'F j, Y')
 * @return string The formatted date
 */
function format_date($date, $format = 'F j, Y') {
    $date = new DateTime($date);
    return $date->format($format);
}

/**
 * Get the current URL
 * 
 * @return string The current URL
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}


/**
 * Check if user is logged in and has admin access
 * Redirects to login page if not authenticated
 * 
 * @return void
 */
function checkAdminAccess() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        // Store the current URL to redirect back after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit();
    }
}

/**
 * Display success/error messages
 * 
 * @return void
 */
function displayAlert() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['success']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['error']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}

/**
 * Check if the request is an AJAX request
 * 
 * @return bool True if the request is an AJAX request
 */
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Get the client's IP address
 * 
 * @return string The IP address
 */
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the random string
 * @return string The generated random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>
