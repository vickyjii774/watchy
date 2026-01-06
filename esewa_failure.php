<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

// Clear order details from session on payment failure
if(isset($_SESSION['order_details'])){
   unset($_SESSION['order_details']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Payment Failed | JerseyManduShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .failure-container {
         max-width: 600px;
         margin: 50px auto;
         padding: 30px;
         background: #fff;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0,0,0,0.1);
         text-align: center;
      }
      .failure-icon {
         font-size: 60px;
         color: #dc3545;
         margin-bottom: 20px;
      }
      .failure-message {
         margin-bottom: 30px;
      }
      .btn-retry {
         background: #007bff;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 4px;
         text-decoration: none;
         font-size: 16px;
         margin-right: 10px;
         transition: background 0.3s;
      }
      .btn-retry:hover {
         background: #0069d9;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="failure">
   <div class="container">
      <div class="failure-container">
         <div class="failure-icon">
            <i class="fas fa-times-circle"></i>
         </div>
         <h2>Payment Failed</h2>
         <div class="failure-message">
            <p>Your payment could not be processed.</p>
            <p>Please try again or choose a different payment method.</p>
         </div>
         <a href="checkout.php" class="btn-retry">Return to Checkout</a>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>