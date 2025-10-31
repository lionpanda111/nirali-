<?php
// Migration to remove price column from services table

class RemovePriceFromServices {
    public function up($pdo) {
        // Check if the price column exists before trying to remove it
        $check = $pdo->query("SHOW COLUMNS FROM services LIKE 'price'");
        if ($check->rowCount() > 0) {
            $pdo->exec("ALTER TABLE services DROP COLUMN price");
        }
    }
    
    public function down($pdo) {
        // This is the reverse migration in case we need to roll back
        $pdo->exec("ALTER TABLE services ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 AFTER duration");
    }
}

// Run the migration if this file is executed directly
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../includes/config.php';
    $pdo = getDBConnection();
    $migration = new RemovePriceFromServices();
    $migration->up($pdo);
    echo "Migration completed: Removed price column from services table\n";
}
