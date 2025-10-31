<?php
/**
 * Database Migration Script
 * Run this script to apply database changes
 */

require_once __DIR__ . '/../includes/config.php';

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/migration-error.log');

echo "🚀 Starting Database Migration\n";

try {
    // Connect to database
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("Failed to connect to database");
    }

    // Enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

    // Start transaction
    $pdo->beginTransaction();

    // Create tables if they don't exist
    $migrations = [
        // Add your migration SQL here
        // Example:
        // "CREATE TABLE IF NOT EXISTS users (...)",
    ];

    $applied = 0;
    foreach ($migrations as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Applied migration\n";
            $applied++;
        } catch (PDOException $e) {
            echo "❌ Error applying migration: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Successfully applied $applied migrations\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    error_log("Migration Error: " . $e->getMessage());
    exit(1);
}

echo "\n🚀 Database migration completed!\n";

// Helper function to run SQL files
function runSqlFile($pdo, $file) {
    if (!file_exists($file)) {
        throw new Exception("SQL file not found: $file");
    }
    
    $sql = file_get_contents($file);
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
}
