<?php
$superadmin_password = "Helloworld";  // Change this!
$hashed_password = password_hash($superadmin_password, PASSWORD_BCRYPT);
echo "Hashed Password: " . $hashed_password;
?>
