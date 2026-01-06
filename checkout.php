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

if(isset($_POST['order'])){
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
   $total_products = filter_var($_POST['total_products'], FILTER_SANITIZE_STRING);
   $total_price = filter_var($_POST['total_price'], FILTER_SANITIZE_STRING);

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){
      if($method == 'esewa'){
         // For eSewa, store order details in session without creating order
         $_SESSION['order_details'] = [
            'name' => $name,
            'number' => $number,
            'email' => $email,
            'address' => $address,
            'total_products' => $total_products,
            'total_price' => $total_price,
            'method' => $method
         ];
         
         // Redirect to eSewa payment page
         header('location:esewa_payment.php');
         exit();
      } else {
         // For cash on delivery, create order immediately
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, payment_status) VALUES(?,?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, 'pending']);

         // Decrease stock
         while($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)){
            $pid = $fetch_cart['pid'];
            $quantity = $fetch_cart['quantity'];

            $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
            $update_stock->execute([$quantity, $pid]);
         }

         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
         header('location:orders.php');
         exit();
      }
   }else{
      $message[] = 'Your cart is empty!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout | WatchShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-section">
   <div class="container">
      <!-- Checkout Header -->
      <div class="checkout-header">
         <div class="header-content">
            <h1><i class="fas fa-shopping-bag"></i> Secure Checkout</h1>
            <div class="checkout-steps">
               <div class="step active">
                  <span class="step-number">1</span>
                  <span class="step-text">Review</span>
               </div>
               <div class="step-line"></div>
               <div class="step">
                  <span class="step-number">2</span>
                  <span class="step-text">Payment</span>
               </div>
               <div class="step-line"></div>
               <div class="step">
                  <span class="step-number">3</span>
                  <span class="step-text">Complete</span>
               </div>
            </div>
         </div>
      </div>

      <div class="checkout-wrapper">
         <!-- Order Summary Card -->
         <div class="summary-card">
            <div class="card-header">
               <h2><i class="fas fa-receipt"></i> Order Summary</h2>
            </div>
            <div class="card-body">
            <?php
               $grand_total = 0;
               $cart_items = [];
               $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
               $select_cart->execute([$user_id]);
               if($select_cart->rowCount() > 0){
                  while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                     $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') ';
                     $total_products = implode($cart_items);
                     $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
            ?>
            <div class="order-item">
               <div class="item-image">
                  <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="<?= $fetch_cart['name']; ?>">
               </div>
               <div class="item-info">
                  <h4><?= $fetch_cart['name']; ?></h4>
                  <div class="item-price">
                     <span class="price">Nrs. <?= number_format($fetch_cart['price']); ?></span>
                     <span class="quantity">x <?= $fetch_cart['quantity']; ?></span>
                  </div>
               </div>
               <div class="item-total">
                  <span>Nrs. <?= number_format($fetch_cart['price'] * $fetch_cart['quantity']); ?></span>
               </div>
            </div>
            <?php
                  }
               }else{
                  echo '<p class="empty">Your cart is empty!</p>';
               }
            ?>
            </div>
            <div class="card-footer">
               <div class="total-breakdown">
                  <div class="total-item">
                     <span>Subtotal</span>
                     <span>Nrs. <?= number_format($grand_total); ?></span>
                  </div>
                  <div class="total-item">
                     <span>Shipping</span>
                     <span class="free">Free</span>
                  </div>
                  <div class="total-item grand-total">
                     <span>Total</span>
                     <span>Nrs. <?= number_format($grand_total); ?></span>
                  </div>
               </div>
            </div>
         </div>

         <!-- Checkout Form Card -->
         <div class="form-card">
            <div class="card-header">
               <h2><i class="fas fa-truck"></i> Shipping & Payment</h2>
            </div>
            <div class="card-body">
               <form action="" method="POST" class="checkout-form">
                  <input type="hidden" name="total_products" value="<?= $total_products; ?>">
                  <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
                  
                  <div class="form-section">
                     <h3>Contact Information</h3>
                     <div class="form-row">
                        <div class="form-group">
                           <label for="name"><i class="fas fa-user"></i> Full Name</label>
                           <input type="text" name="name" id="name" required placeholder="Enter your full name" class="form-input">
                        </div>
                        <div class="form-group">
                           <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                           <input type="email" name="email" id="email" required placeholder="Enter your email" class="form-input">
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="number"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" name="number" id="number" required placeholder="Enter your phone number" class="form-input" maxlength="10">
                     </div>
                  </div>

                  <div class="form-section">
                     <h3>Delivery Address</h3>
                     <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Complete Address</label>
                        <textarea name="address" id="address" class="form-input" required placeholder="Enter your complete delivery address" rows="3"></textarea>
                     </div>
                  </div>

                  <div class="form-section">
                     <h3>Payment Method</h3>
                     <div class="payment-options">
                        <div class="payment-option">
                           <input type="radio" name="method" id="cod" value="cash on delivery" required>
                           <label for="cod" class="payment-label">
                              <div class="payment-icon">
                                 <i class="fas fa-money-bill-wave"></i>
                              </div>
                              <div class="payment-info">
                                 <h4>Cash on Delivery</h4>
                                 <p>Pay when you receive your order</p>
                              </div>
                           </label>
                        </div>
                        <div class="payment-option">
                           <input type="radio" name="method" id="esewa" value="esewa" required>
                           <label for="esewa" class="payment-label">
                              <div class="payment-icon">
                                 <i class="fas fa-credit-card"></i>
                              </div>
                              <div class="payment-info">
                                 <h4>eSewa Wallet</h4>
                                 <p>Secure online payment via eSewa</p>
                              </div>
                           </label>
                        </div>
                     </div>
                  </div>

                  <button type="submit" name="order" class="checkout-btn" <?= ($grand_total > 1)?'':'disabled' ?>>
                     <span>Complete Order</span>
                     <i class="fas fa-lock"></i>
                  </button>
               </form>
            </div>
         </div>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>