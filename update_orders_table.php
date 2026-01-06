<?php
include 'components/connect.php';

// Add payment_status and payment_reference columns to orders table if they don't exist
try {
    $check_columns = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'payment_status'");
    if($check_columns->rowCount() == 0) {
        $conn->exec("ALTER TABLE `orders` ADD COLUMN `payment_status` VARCHAR(20) DEFAULT 'pending'");
        echo "Added payment_status column to orders table.<br>";
    }
    
    $check_columns = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'payment_reference'");
    if($check_columns->rowCount() == 0) {
        $conn->exec("ALTER TABLE `orders` ADD COLUMN `payment_reference` VARCHAR(100) DEFAULT NULL");
        echo "Added payment_reference column to orders table.<br>";
    }
    
    echo "Database updated successfully!";
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>