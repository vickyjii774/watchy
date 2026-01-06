<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['send'])){
   $name = $_POST['name'];
   $email = $_POST['email'];
   $number = $_POST['number'];
   $msg = $_POST['msg'];

   // Validate inputs
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

   $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $message[] = 'Message already sent!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);
      if($insert_message){
         $message[] = 'Message sent successfully!';
      }else{
         $message[] = 'Failed to send message!';
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
   <title>Contact Us | JerseyMandu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
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

<?php include 'components/user_header.php'; ?>

<section class="contact">
   <div class="container">
      <!-- Page Header -->
      <div class="page-header">
         <h1>Contact Us</h1>
         <p>Get in touch with us for any inquiries or support</p>
      </div>

      <!-- Contact Info Cards -->
      <div class="contact-info">
         <div class="info-card">
            <i class="fas fa-map-marker-alt"></i>
            <h3>Our Location</h3>
            <p>Kathmandu, Nepal</p>
         </div>

         <div class="info-card">
            <i class="fas fa-phone"></i>
            <h3>Call Us</h3>
            <p>+977 9845919784</p>
            <p>+977 9823672479</p>
         </div>

         <div class="info-card">
            <i class="fas fa-envelope"></i>
            <h3>Email Us</h3>
            <p>info@JerseyMandu.com</p>
            <p>support@JerseyMandu.com</p>
         </div>

         <div class="info-card">
            <i class="fas fa-clock"></i>
            <h3>Opening Hours</h3>
            <p>10:00 AM - 8:00 PM</p>
            <p>Sunday - Friday</p>
         </div>
      </div>

      <!-- Contact Form Section -->
      <div class="contact-section">
         <div class="contact-img">
            <img src="./images/barcelona.png">
         </div>

         <div class="contact-form">
            <h2>Send Us a Message</h2>
            <form action="" method="post">
               <div class="form-group">
                  <label for="name">Full Name</label>
                  <input type="text" name="name" id="name" required maxlength="50" 
                         placeholder="Enter your name" class="form-control">
               </div>

               <div class="form-group">
                  <label for="email">Email Address</label>
                  <input type="email" name="email" id="email" required maxlength="50" 
                         placeholder="Enter your email" class="form-control">
               </div>

               <div class="form-group">
                  <label for="number">Phone Number</label>
                  <input type="number" name="number" id="number" required maxlength="10" 
                         placeholder="Enter your number" class="form-control" 
                         min="0" max="9999999999" 
                         onkeypress="if(this.value.length == 10) return false;">
               </div>

               

               <div class="form-group">
                  <label for="msg">Your Message</label>
                  <textarea name="msg" id="msg" class="form-control" required maxlength="500" 
                            placeholder="Type your message here..." rows="5"></textarea>
               </div>

               <button type="submit" name="send" class="submit-btn">
                  <i class="fas fa-paper-plane"></i>
                  Send Message
               </button>
            </form>
         </div>
      </div>

      <!-- Social Media Section -->
      <div class="social-section">
         <h2>Connect With Us</h2>
         <div class="social-links">
            <a href="https://www.facebook.com" class="social-link" target="_blank">
               <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com" class="social-link" target="_blank">
               <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.x.com" class="social-link" target="_blank">
               <i class="fab fa-twitter"></i>
            </a>
            <a href="https://www.linkedin.com" class="social-link" target="_blank">
               <i class="fab fa-linkedin-in"></i>
               
            </a>
         </div>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>