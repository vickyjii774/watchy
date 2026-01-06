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

// Delete order
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:orders.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders | JerseyMandu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders">
   <div class="container">
      <div class="page-header">
         <h1>My Orders</h1>
         <p>Track and manage your orders</p>
      </div>

      <div class="orders-container">
         <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND payment_status != 'failed' ORDER BY placed_on DESC");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
         ?>
         
         <div class="orders-grid">
            <?php
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
            ?>
            <div class="order-card">
               <div class="order-header">
                  <div class="order-info">
                     <span class="order-date">
                        <i class="far fa-calendar-alt"></i>
                        <?= date('d M Y', strtotime($fetch_orders['placed_on'])); ?>
                     
                     <span class="order-id">
                        <i class="fas fa-hashtag"></i>
                        <?= $fetch_orders['id']; ?>
                     
                  </div>
                  <div class="order-status <?= $fetch_orders['payment_status']; ?>">
                     <?= $fetch_orders['payment_status']; ?>
                  </div>
               </div>

               <div class="order-body">
                  <div class="order-details">
                     <div class="detail-item">
                        <span class="label">Products:
                        <span class="value"><?= $fetch_orders['total_products']; ?>
                     </div>
                     <div class="detail-item">
                        <span class="label">Total Amount:
                        <span class="value">Nrs. <?= number_format($fetch_orders['total_price']); ?>/-
                     </div>
                     <div class="detail-item">
                        <span class="label">Payment Method:
                        <span class="value"><?= $fetch_orders['method']; ?>
                     </div>
                  </div>

                  <div class="shipping-details">
                     <h3>Shipping Details</h3>
                     <p class="name">
                        <i class="fas fa-user"></i>
                        <?= $fetch_orders['name']; ?>
                     </p>
                     <p class="phone">
                        <i class="fas fa-phone"></i>
                        <?= $fetch_orders['number']; ?>
                     </p>
                     <p class="email">
                        <i class="fas fa-envelope"></i>
                        <?= $fetch_orders['email']; ?>
                     </p>
                     <p class="address">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= $fetch_orders['address']; ?>
                     </p>
                  </div>
               </div>

               <div class="order-footer">
                  <a href="orders.php?delete=<?= $fetch_orders['id']; ?>" class="track-btn" onclick="return confirm('Delete this order?');">
                     <i class="fas fa-trash"></i>
                     Delete Order
                  </a>
               </div>
            </div>
            <?php
            }
            ?>
         </div>

         <?php
         }else{
            echo '<div class="empty-orders">
                     <img src="images/empty-orders.png" alt="No Orders">
                     <h3>No Orders Found!</h3>
                     <p>You haven\'t placed any orders yet.</p>
                     <a href="shop.php" class="shop-btn">
                        <i class="fas fa-shopping-bag"></i>
                        Start Shopping
                     </a>
                  </div>';
         }
         ?>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>