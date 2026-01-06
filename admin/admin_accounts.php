<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

// Check if superadmin
$select_admin = $conn->prepare("SELECT role FROM `admins` WHERE id = ?");
$select_admin->execute([$admin_id]);
$admin = $select_admin->fetch(PDO::FETCH_ASSOC);

if ($admin['role'] !== 'superadmin') {
    $message[] = 'Access denied! Only superadmin can manage admins.';
    header('location:dashboard.php');
    exit();
}

// Add new admin
if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $cpass = sha1($_POST['cpass']);

    $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
    $select_admin->execute([$name]);

    if ($select_admin->rowCount() > 0) {
        $message[] = 'Username already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'Confirm password not matched!';
        } else {
            $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password, role) VALUES(?, ?, 'admin')");
            $insert_admin->execute([$name, $pass]);
            $message[] = 'New admin registered successfully!';
        }
    }
}

// Delete admin
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    $check_role = $conn->prepare("SELECT role FROM `admins` WHERE id = ?");
    $check_role->execute([$delete_id]);
    $admin_role = $check_role->fetch(PDO::FETCH_ASSOC);

    if ($admin_role && $admin_role['role'] !== 'superadmin') {
        $delete_admin = $conn->prepare("DELETE FROM `admins` WHERE id = ?");
        $delete_admin->execute([$delete_id]);
        $message[] = 'Admin deleted successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="message-container"></div>

<section class="admin-section">
    <h1 class="heading">Admin Accounts</h1>

    <div class="admin-container">
        <!-- Left Side - Add New Admin -->
        <div class="add-admin">
            <h3>Add New Admin</h3>
            <form action="" method="post">
                <input type="text" name="name" required placeholder="Enter username" class="input-box">
                <input type="password" name="pass" required placeholder="Enter password" class="input-box">
                <input type="password" name="cpass" required placeholder="Confirm password" class="input-box">
                <input type="submit" value="Add Admin" name="submit" class="btn">
            </form>
        </div>

        <!-- Right Side - List of Admins -->
        <div class="admin-list">
            <h3>Existing Admins</h3>
            <div class="admin-table">
                <?php
                $select_accounts = $conn->prepare("SELECT * FROM `admins` WHERE role = 'admin'");
                $select_accounts->execute();
                if ($select_accounts->rowCount() > 0) {
                    while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <div class="admin-card">
                    <p><strong>ID:</strong> <?= $fetch_accounts['id']; ?></p>
                    <p><strong>Username:</strong> <?= $fetch_accounts['name']; ?></p>
                    <a href="admin_accounts.php?delete=<?= $fetch_accounts['id']; ?>" 
                       onclick="return confirm('Delete this admin?')" class="delete-btn">Delete</a>
                </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">No admin accounts available!</p>';
                }
                ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Admin Management Layout */
.admin-container {
    display: flex;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.add-admin {
    flex: 1;
    max-width: 30%;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.admin-list {
    flex: 2;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Admin List Table */
.admin-table {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-card {
    background: #f4f4f4;
    padding: 15px;
    border-radius: 5px;
    width: 48%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.admin-card p {
    margin: 5px 0;
}

.delete-btn {
    background: #e74c3c;
    color: white;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
    margin-top: 10px;
}

.delete-btn:hover {
    background: #c0392b;
}

/* Form Styling */
.input-box {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.btn {
    width: 100%;
    padding: 10px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn:hover {
    background: #2980b9;
}

/* Messages */
.message-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
}

.message {
    background: #fff;
    padding: 1rem;
    border-left: 4px solid;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    animation: fadeIn 0.5s ease-in-out;
}

.message.success {
    border-left-color: #2ecc71;
}

.message.error {
    border-left-color: #e74c3c;
}

.message i {
    cursor: pointer;
    margin-left: auto;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>

<script>
function showMessage(text, type = 'success') {
    const container = document.querySelector('.message-container');

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `<span>${text}</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i>`;

    container.appendChild(messageDiv);

    setTimeout(() => messageDiv.remove(), 3000);
}

// Display messages from PHP
<?php 
if(isset($message)) {
    foreach($message as $msg) {
        echo "showMessage('$msg', 'success');";
    }
}
?>
</script>

</body>
</html>