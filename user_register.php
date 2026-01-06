<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select_user->execute([$email]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   if($select_user->rowCount() > 0){
      $message = ['type' => 'error', 'text' => 'Email already exists!'];
   }else{
      if($pass != $cpass){
         $message = ['type' => 'error', 'text' => 'Confirm password not matched!'];
      }else{
         $insert_user = $conn->prepare("INSERT INTO `users`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message = ['type' => 'success', 'text' => 'Registered successfully!'];
         header('refresh:2;url=user_login.php');
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   
</head>
<body>

<div id="notification" class="notification"></div>
   
<?php include 'components/user_header.php'; ?>

<section class="auth-section">
   <div class="auth-container">
      <div class="auth-card">
         <div class="auth-header">
            <div class="auth-icon">
               <i class="fas fa-user-plus"></i>
            </div>
            <h2>Create Account</h2>
            <p>Join our watch community</p>
         </div>
         
         <form action="" method="post" class="auth-form">
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="text" name="name" required placeholder="Full Name" maxlength="20" class="form-input">
               </div>
            </div>
            
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="email" name="email" required placeholder="Email Address" maxlength="50" class="form-input">
               </div>
            </div>
            
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="password" name="pass" id="password" required placeholder="Password" maxlength="20" class="form-input" oninput="checkPassword(this.value)">
               </div>
            </div>
            
            <div class="password-requirements" id="passwordRequirements">
               <div class="requirement" id="length">• Minimum 8 characters</div>
               <div class="requirement" id="uppercase">• At least one uppercase letter</div>
               <div class="requirement" id="lowercase">• At least one lowercase letter</div>
               <div class="requirement" id="number">• At least one number</div>
               <div class="requirement" id="special">• At least one special character (!@#$%^&*)</div>
            </div>
            
            <div class="form-group">
               <div class="input-wrapper">
                  <input type="password" name="cpass" required placeholder="Confirm Password" maxlength="20" class="form-input">
               </div>
            </div>
            
            <button type="submit" name="submit" class="auth-btn">
               <span>Create Account</span>
               <i class="fas fa-arrow-right"></i>
            </button>
         </form>
         
         <div class="auth-footer">
            <p>Already have an account? <a href="user_login.php">Sign in</a></p>
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

function checkPassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*]/.test(password)
    };

    // Update requirement items
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(req);
        if (requirements[req]) {
            element.classList.add('valid');
            element.classList.remove('invalid');
        } else {
            element.classList.remove('valid');
            element.classList.add('invalid');
        }
    });

    return Object.values(requirements).every(Boolean);
}

document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    if (!checkPassword(password)) {
        e.preventDefault();
        showNotification('Password must meet all requirements!', 'error');
    }
});

// Show notifications from PHP
<?php if(isset($message)){ ?>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('<?= $message['text'] ?>', '<?= $message['type'] ?>');
    });
<?php } ?>
</script>

</body>
</html>
