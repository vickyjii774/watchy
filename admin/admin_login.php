<?php

include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:dashboard.php');
   }else{
      $message[] = 'incorrect username or password!';
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

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="admin-auth-section">
   <div class="auth-container">
      <div class="auth-card">
         <div class="auth-header">
            <div class="auth-icon">
               <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Admin Portal</h2>
            <p>Secure access to administration panel</p>
         </div>
         
         <form action="" method="post" class="auth-form">
            <div class="form-group">
               <div class="input-wrapper">
                  <i class="fas fa-user-shield"></i>
                  <input type="text" name="name" required placeholder="Admin Username" maxlength="20" class="form-input">
               </div>
            </div>
            
            <div class="form-group">
               <div class="input-wrapper">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="pass" required placeholder="Admin Password" maxlength="20" class="form-input">
               </div>
            </div>
            
            <button type="submit" name="submit" class="auth-btn">
               <span>Access Dashboard</span>
               <i class="fas fa-arrow-right"></i>
            </button>
         </form>
         
         <div class="auth-footer">
            <p><i class="fas fa-info-circle"></i> Authorized personnel only</p>
         </div>
      </div>
   </div>
</section>
   
</body>
</html>