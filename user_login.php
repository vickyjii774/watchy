<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
   $select_user->execute([$email, $pass]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   if($select_user->rowCount() > 0){
      $_SESSION['user_id'] = $row['id'];
      $message = ['type' => 'success', 'text' => 'Login successful!'];
      header('refresh:2;url=home.php');
   }else{
      $message = ['type' => 'error', 'text' => 'Incorrect email or password!'];
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Add this div for notifications -->
<div id="notification" class="notification"></div>
   
<?php include 'components/user_header.php'; ?>

<section class="auth-section">
   <div class="auth-container">
      <div class="auth-card">
         <div class="auth-header">
            <div class="auth-icon">
               <i class="fas fa-user-circle"></i>
            </div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
         </div>
         
         <form action="" method="post" class="auth-form">
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="email" name="email" required placeholder="Email Address" maxlength="50" class="form-input">
               </div>
            </div>
            
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="password" name="pass" required placeholder="Password" maxlength="20" class="form-input">
               </div>
            </div>
            
            <button type="submit" name="submit" class="auth-btn">
               <span>Sign In</span>
               <i class="fas fa-arrow-right"></i>
            </button>
         </form>
         
         <div class="auth-footer">
            <p>Don't have an account? <a href="user_register.php">Create one</a></p>
         </div>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.style.display = 'block';

    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Show notifications from PHP
<?php if(isset($message)){ ?>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('<?= $message['text'] ?>', '<?= $message['type'] ?>');
    });
<?php } ?>
</script>

</body>
</html>
