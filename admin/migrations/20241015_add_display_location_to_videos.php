<?php
// Add display_location column to videos table
$sql = [
    "ALTER TABLE `videos`
     ADD COLUMN `display_location` ENUM('home', 'academy', 'both') NOT NULL DEFAULT 'home' AFTER `is_active`"
];

// Execute the SQL
foreach ($sql as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Successfully added display_location column to videos table\n";
    } else {
        echo "Error adding display_location column: " . $conn->error . "\n";
    }
}
?>
