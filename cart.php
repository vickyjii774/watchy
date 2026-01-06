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

// Delete item from cart
if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
   $message[] = 'Item removed from cart!';
}

// Delete all items from cart
if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
   exit();
}

// Update quantity
if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);

   // Fetch the product ID from the cart item
   $select_cart_item = $conn->prepare("SELECT pid FROM `cart` WHERE id = ?");
   $select_cart_item->execute([$cart_id]);
   $fetch_cart_item = $select_cart_item->fetch(PDO::FETCH_ASSOC);
   $pid = $fetch_cart_item['pid'];

   $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
   $check_stock->execute([$pid]);
   $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

   if($product_stock < $qty) {
       $message[] = 'Not enough stock!';
   } else {
       $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
       $update_qty->execute([$qty, $cart_id]);
       $message[] = 'Cart quantity updated!';
   }
}

// Check stock and calculate grand total
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);
$out_of_stock = false;
$grand_total = 0;

if($select_cart->rowCount() > 0){
    while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
        $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
        $grand_total += $sub_total;

        $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
        $check_stock->execute([$fetch_cart['pid']]);
        $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

        if ($product_stock == 0) {
            $out_of_stock = true;
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
   <title>Shopping Cart | WatchShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="shopping-cart">
   <div class="container">
      <div class="page-title">
         <h1>Shopping Cart</h1>
         <p>Review and modify your selected items</p>
      </div>

      <div class="cart-content">
         <?php
         if($select_cart->rowCount() > 0){
         ?>
         
         <div class="cart-items">
            <?php
            $select_cart->execute([$user_id]); // Re-execute to fetch data again
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
            ?>
            <div class="cart-item">
               <form action="" method="post" class="cart-form">
                  <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                  
                  <div class="item-image">
                     <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="<?= $fetch_cart['name']; ?>">
                     <button type="submit" name="delete" class="delete-btn" 
                             onclick="return confirm('Delete this item?');">
                        <i class="fas fa-trash"></i>
                     </button>
                  </div>

                  <div class="item-details">
    <div class="item-name">
        <h3><?= htmlspecialchars($fetch_cart['name']); ?></h3>
        <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="view-product">
            <i class="fas fa-eye"></i> View Details
        </a>
    </div>

    <div class="item-price">
        <span class="price">Nrs. <?= number_format($fetch_cart['price']); ?>/-</span>
    </div>

    <div class="product-stock">
        <?php
        $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
        $check_stock->execute([$fetch_cart['pid']]);
        $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];
        ?>
        <?php if ($product_stock == 0): ?>
            <span class="stock out-of-stock" style="color: red;">Out of Stock</span>
        <?php elseif ($product_stock < 5): ?>
            <span class="stock low-stock" style="color: #f67800;">Low Stock: <?= $product_stock; ?></span>
        <?php endif; ?>
    </div>

    <div class="item-quantity">
        <div class="quantity-control">
            <button type="button" class="qty-btn minus">-</button>
            <input type="number" name="qty" class="qty-input" min="1" max="<?= $product_stock; ?>" value="<?= $fetch_cart['quantity']; ?>" <?= $product_stock == 0 ? 'disabled' : ''; ?>>
            <button type="button" class="qty-btn plus">+</button>
        </div>
        <button type="submit" name="update_qty" class="update-btn" <?= $product_stock == 0 ? 'disabled' : ''; ?>>
            <i class="fas fa-sync-alt"></i> Update
        </button>
    </div>

    <div class="item-subtotal">
        <span>Subtotal:</span>
        <span class="amount">Nrs. <?= number_format($sub_total); ?>/-</span>
    </div>
</div>
               </form>
            </div>
            <?php
            }
            ?>
         </div>

         <div class="cart-summary">
            <div class="summary-details">
               <h3>Order Summary</h3>
               
               <div class="summary-item">
                  <span>Subtotal
                  <span>Nrs. <?= number_format($grand_total); ?>/-</span>
               </div>
               
               <div class="summary-item">
                  <span>Delivery
                  <span>Free</span>
               </div>
               
               <div class="summary-total">
                  <span>Total
                  <span>Nrs. <?= number_format($grand_total); ?>/-</span>
               </div>
            </div>

            <div class="cart-actions">
    <a href="shop.php" class="continue-btn">
        <i class="fas fa-arrow-left"></i> Continue Shopping
    </a>
    <a href="cart.php?delete_all" class="clear-btn <?= ($grand_total > 1)?'':'disabled'; ?>"
       onclick="return confirm('Delete all items from cart?');">
        <i class="fas fa-trash"></i> Clear Cart
    </a>
    <a href="checkout.php" class="checkout-btn <?= ($grand_total > 1 && !$out_of_stock)?'':'disabled'; ?>"
       onclick="return <?= $out_of_stock ? 'alert(\'Some items are out of stock. Please remove them before proceeding to checkout.\');' : 'true'; ?>">
        Proceed to Checkout <i class="fas fa-arrow-right"></i>
    </a>
</div>
         </div>

         <?php
         }else{
            echo '<div class="empty-cart">
                     <img src="images/empty-cart.png" alt="Empty Cart">
                     <h3>Your cart is empty!</h3>
                     <p>Add items to your cart to proceed with checkout</p>
                     <a href="shop.php" class="shop-btn">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                     </a>
                  </div>';
         }
         ?>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
document.querySelectorAll('.qty-btn').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.qty-input');
        let value = parseInt(input.value);
        const maxQty = parseInt(input.max);
        const stockSpan = this.closest('.item-details').querySelector('.product-stock span');

        if(this.classList.contains('plus') && value < maxQty) {
            input.value = value + 1;
        }
        else if(this.classList.contains('minus') && value > 1) {
            input.value = value - 1;
        }

        value = parseInt(input.value); // Update value after changing input

        if (value >= maxQty) {
            stockSpan.style.color = 'red';
            stockSpan.textContent = `In Stock: ${maxQty}`;
        } else if (maxQty < 5) {
            stockSpan.style.color = '#f67800';
            stockSpan.textContent = `Low Stock: ${maxQty}`;
        } 
    });
});
</script>

</body>
</html>