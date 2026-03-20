<?php
$db_name = 'mysql:host=sql206.infinityfree.com;dbname=if0_41438495_watchy';
$user_name = 'if0_41438495';
$user_password = 'r6ju4wF10r';
$conn = new PDO($db_name, $user_name, $user_password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
$conn->exec("SET CHARACTER SET utf8mb4");
?>
