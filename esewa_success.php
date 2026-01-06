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

// Verify eSewa payment and create order
if(isset($_GET['oid']) && isset($_GET['amt']) && isset($_GET['refId']) && isset($_SESSION['order_details'])){
   $transaction_id = $_GET['oid'];
   $amount = $_GET['amt'];
   $ref_id = $_GET['refId'];
   
   // Get order details from session
   $order_details = $_SESSION['order_details'];
   
   // Create order in database after successful payment
   $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, payment_status, payment_reference) VALUES(?,?,?,?,?,?,?,?,?,?)");
   $insert_order->execute([
      $user_id, 
      $order_details['name'], 
      $order_details['number'], 
      $order_details['email'], 
      $order_details['method'], 
      $order_details['address'], 
      $order_details['total_products'], 
      $order_details['total_price'], 
      'pending',
      $ref_id
   ]);
   $order_id = $conn->lastInsertId();
   
   // Decrease stock and clear cart
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);
   
   while($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)){
      $pid = $fetch_cart['pid'];
      $quantity = $fetch_cart['quantity'];
      
      $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
      $update_stock->execute([$quantity, $pid]);
   }
   
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$user_id]);
   
   // Clear order details from session
   unset($_SESSION['order_details']);
   
   $message[] = 'Payment successful!';
} else {
   $message[] = 'Invalid payment response!';
   header('location:checkout.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Payment Success | JerseyMandu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .success-container {
         max-width: 600px;
         margin: 50px auto;
         padding: 30px;
         background: #fff;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0,0,0,0.1);
         text-align: center;
      }
      .success-icon {
         font-size: 60px;
         color: #28a745;
         margin-bottom: 20px;
      }
      .success-message {
         margin-bottom: 30px;
      }
      .btn-continue {
         background: #007bff;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 4px;
         text-decoration: none;
         font-size: 16px;
         transition: background 0.3s;
      }
      .btn-continue:hover {
         background: #0069d9;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="success">
   <div class="container">
      <div class="success-container">
         <div class="success-icon">
            <i class="fas fa-check-circle"></i>
         </div>
         <h2>Payment Successful!</h2>
         <div class="success-message">
            <p>Your payment has been processed successfully.</p>
            <p>Order ID: <?= isset($order_id) ? $order_id : 'N/A'; ?></p>
            <p>Transaction Reference: <?= isset($_GET['refId']) ? $_GET['refId'] : 'N/A'; ?></p>
         </div>
         <a href="orders.php" class="btn-continue">View My Orders</a>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>