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

// Get order details from session
if(isset($_SESSION['order_details'])){
   $name = $_SESSION['order_details']['name'];
   $number = $_SESSION['order_details']['number'];
   $email = $_SESSION['order_details']['email'];
   $method = 'esewa';
   $address = $_SESSION['order_details']['address'];
   $total_products = $_SESSION['order_details']['total_products'];
   $total_price = $_SESSION['order_details']['total_price'];
   // Generate temporary order ID for eSewa transaction
   $temp_order_id = time();
} else {
   header('location:checkout.php');
   exit();
}

// eSewa configuration
$success_url = "http://".$_SERVER['HTTP_HOST']."/ecommerce/esewa_success.php";
$failure_url = "http://".$_SERVER['HTTP_HOST']."/ecommerce/esewa_failure.php";
$amount = $total_price;
$transaction_uuid = "WS-".time()."-".$temp_order_id; // Making unique transaction ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>eSewa Payment | JerseyMandu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .payment-container {
         max-width: 600px;
         margin: 50px auto;
         padding: 20px;
         background: #fff;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0,0,0,0.1);
         text-align: center;
      }
      .payment-info {
         margin-bottom: 30px;
      }
      .payment-btn {
         background: #10A513;
         color: white;
         border: none;
         padding: 12px 30px;
         border-radius: 4px;
         font-size: 16px;
         cursor: pointer;
         transition: background 0.3s;
      }
      .payment-btn:hover {
         background: #0D8A0F;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="payment">
   <div class="container">
      <div class="payment-container">
         <h2>Complete Your Payment</h2>
         <div class="payment-info">
            <p>Total Amount: <strong>Nrs. <?= number_format($total_price); ?> /-</strong></p>
            <p>Transaction ID: <?= $temp_order_id; ?></p>
         </div>
         
         <form action="https://rc.esewa.com.np/epay/main" method="POST">
            <input value="<?= $total_price; ?>" name="tAmt" type="hidden">
            <input value="<?= $total_price; ?>" name="amt" type="hidden">
            <input value="0" name="txAmt" type="hidden">
            <input value="0" name="psc" type="hidden">
            <input value="0" name="pdc" type="hidden">
            <input value="EPAYTEST" name="scd" type="hidden">
            <input value="<?= $transaction_uuid; ?>" name="pid" type="hidden">
            <input value="<?= $success_url; ?>" type="hidden" name="su">
            <input value="<?= $failure_url; ?>" type="hidden" name="fu">
            <button type="submit" class="payment-btn">Pay with eSewa</button>
         </form>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>