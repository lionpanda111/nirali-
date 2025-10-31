<?php
/**
 * Admin Functions
 * 
 * Contains all the common functions used across the admin panel
 */

// Only define functions if they don't already exist
if (!function_exists('checkAdminAccess')) {
    /**
     * Check if admin is logged in and has admin access
     * Redirects to login page if not authenticated
     */
    function checkAdminAccess() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $_SESSION['error'] = 'You must be logged in to access this page.';
            header('Location: login.php');
            exit();
        }
        
        // Check if user has admin role
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            header('Location: index.php');
            exit();
        }
    }
}

if (!function_exists('displayAlerts')) {
    /**
     * Display success/error alerts
     */
    function displayAlerts() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
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
}

if (!function_exists('createSlug')) {
    /**
     * Create a URL-friendly slug from a string
     * 
     * @param string $text The text to convert to a slug
     * @return string The generated slug
     */
    function createSlug($text) {
        // Replace non-letter or non-number characters with -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate to ASCII
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim and convert to lowercase
        $text = trim($text, '-');
        $text = strtolower($text);
        
        // Return the slug or 'n-a' if empty
        return $text ?: 'n-a';
    }
}

if (!function_exists('formatDuration')) {
    /**
     * Format duration in minutes to human-readable format
     * 
     * @param int $minutes Duration in minutes
     * @return string Formatted duration (e.g., "1 hour 30 mins")
     */
    function formatDuration($minutes) {
        if ($minutes < 60) {
            return $minutes . ' mins';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
        
        if ($remainingMinutes > 0) {
            $result .= ' ' . $remainingMinutes . ' mins';
        }
        
        return $result;
    }
}

if (!function_exists('getDBConnection')) {
    /**
     * Get database connection
     * 
     * @return PDO Database connection
     */
    function getDBConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                // Include the main config file if not already included
                if (!defined('DB_HOST')) {
                    require_once __DIR__ . '/../../includes/config.php';
                }
                
                // Create PDO instance
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                die('Could not connect to the database. Please try again later.');
            }
        }
        
        return $pdo;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL with an optional status code
     * 
     * @param string $url The URL to redirect to
     * @param int $statusCode HTTP status code (default: 302)
     */
    function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize user input
     * 
     * @param mixed $input The input to sanitize
     * @return mixed Sanitized input
     */
    function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = sanitize($value);
            }
            return $input;
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('startsWith')) {
    /**
     * Check if a string starts with a given substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool True if the string starts with the substring, false otherwise
     */
    function startsWith($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('endsWith')) {
    /**
     * Check if a string ends with a given substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool True if the string ends with the substring, false otherwise
     */
    function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * Generate a random string
     * 
     * @param int $length Length of the random string
     * @return string Random string
     */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date to a human-readable format
     * 
     * @param string $date The date to format
     * @param string $format The format to use (default: 'F j, Y, g:i a')
     * @return string Formatted date
     */
    function formatDate($date, $format = 'F j, Y, g:i a') {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    }
}

if (!function_exists('isValidEmail')) {
    /**
     * Check if a string is a valid email address
     * 
     * @param string $email The email address to validate
     * @return bool True if the email is valid, false otherwise
     */
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('isValidUrl')) {
    /**
     * Check if a string is a valid URL
     * 
     * @param string $url The URL to validate
     * @return bool True if the URL is valid, false otherwise
     */
    function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (!function_exists('getCurrentUrl')) {
    /**
     * Get the current page URL
     * 
     * @return string The current page URL
     */
    function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

if (!function_exists('getClientIp')) {
    /**
     * Get the client's IP address
     * 
     * @return string The client's IP address
     */
    function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
}

if (!function_exists('toTitleCase')) {
    /**
     * Convert a string to title case
     * 
     * @param string $str The string to convert
     * @return string The converted string
     */
    function toTitleCase($str) {
        return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate a string to a specified length
     * 
     * @param string $str The string to truncate
     * @param int $length The maximum length
     * @param string $suffix The suffix to append if truncated (default: '...')
     * @return string The truncated string
     */
    function truncate($str, $length, $suffix = '...') {
        if (strlen($str) <= $length) {
            return $str;
        }
        
        $str = substr($str, 0, $length - strlen($suffix));
        $str = substr($str, 0, strrpos($str, ' '));
        
        return $str . $suffix;
    }
}

if (!function_exists('generateVideoThumbnail')) {
    /**
     * Generate a thumbnail from a video file
     * 
     * @param string $videoPath Path to the video file
     * @param string $thumbnailPath Path to save the thumbnail
     * @param int $time Frame time in seconds (default: 1)
     * @return bool True on success, false on failure
     */
    function generateVideoThumbnail($videoPath, $thumbnailPath, $time = 1) {
        // Check if FFmpeg is available
        if (!function_exists('shell_exec') || !`which ffmpeg`) {
            return false;
        }
        
        // Generate thumbnail using FFmpeg
        $command = sprintf(
            'ffmpeg -ss %s -i %s -vframes 1 -q:v 2 -f image2 %s 2>&1',
            escapeshellarg($time),
            escapeshellarg($videoPath),
            escapeshellarg($thumbnailPath)
        );
        
        @exec($command, $output, $returnCode);
        
        // Check if thumbnail was created successfully
        return $returnCode === 0 && file_exists($thumbnailPath);
    }
}

if (!function_exists('isVideoFile')) {
    /**
     * Check if a file is a valid video file
     * 
     * @param string $filePath Path to the file
     * @return bool True if valid video, false otherwise
     */
    function isVideoFile($filePath) {
        $allowedTypes = [
            'video/mp4',
            'video/webm',
            'video/ogg',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            'video/x-matroska',
            'video/3gpp',
            'video/3gpp2',
            'video/x-flv'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
}
?>
