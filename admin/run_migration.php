<?php
// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Get database connection
$conn = getDBConnection();

// Define migrations directory
$migrationsDir = __DIR__ . '/migrations';

// Get all migration files
$migrationFiles = glob($migrationsDir . '/*.php');

// Sort files by name (which includes the date)
sort($migrationFiles);

// Check if a specific migration file is requested
$specificMigration = $_GET['migration'] ?? null;

if ($specificMigration) {
    $migrationFile = $migrationsDir . '/' . $specificMigration . '.php';
    if (in_array($migrationFile, $migrationFiles)) {
        runMigration($conn, $migrationFile);
    } else {
        die("Specified migration file not found!");
    }
} else {
    // Run all migrations
    foreach ($migrationFiles as $migrationFile) {
        runMigration($conn, $migrationFile);
    }
    echo "All migrations completed successfully!\n";
}

/**
 * Run a single migration file
 *
 * @param PDO $conn Database connection
 * @param string $migrationFile Path to migration file
 */
function runMigration($conn, $migrationFile) {
    echo "Running migration: " . basename($migrationFile) . "\n";
    try {
        // Include the migration file which will execute the SQL
        require $migrationFile;
        echo "Migration " . basename($migrationFile) . " completed successfully!\n";
    } catch (Exception $e) {
        die("Error running migration " . basename($migrationFile) . ": " . $e->getMessage() . "\n");
    }
}
?>
