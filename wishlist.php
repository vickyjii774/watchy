<?php
include 'components/connect.php';
session_start();
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

include 'components/wishlist_cart.php';

// Delete item from wishlist
if(isset($_POST['delete'])){
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
   $message[] = 'Wishlist item deleted successfully';
}

// Add to cart from wishlist
if(isset($_POST['add_to_cart'])){
   $pid = $_POST['pid'];
   $name = $_POST['name'];
   $price = $_POST['price'];
   $image = $_POST['image'];
   $qty = $_POST['qty'] ?? 1; // Default quantity is 1

   // Check if product already exists in cart
   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ?");
   $check_cart_numbers->execute([$pid, $user_id]);

   $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
   $check_stock->execute([$pid]);
   $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

   if($product_stock < $qty) {
       $message[] = 'Not enough stock!';
   } elseif($check_cart_numbers->rowCount() > 0) {
       $message[] = 'Already added to cart!';
   } else {
       try {
           // Insert into cart
           $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
           $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
           $message[] = 'Added to cart successfully!';
       } catch(PDOException $e) {
           $message[] = 'Error adding to cart: ' . $e->getMessage();
       }
   }
}

// Delete all items from wishlist
if(isset($_GET['delete_all'])){
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$user_id]);
   header('location:wishlist.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="wishlist">
   <div class="container">
      <div class="page-title">
         <h1>My Wishlist</h1>
         <p>Products you have saved for later</p>
      </div>

      <div class="wishlist-content">
         <?php
         $grand_total = 0;
         $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
         $select_wishlist->execute([$user_id]);
         if($select_wishlist->rowCount() > 0){
         ?>
         <div class="wishlist-grid">
            <?php
            while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
               $grand_total += $fetch_wishlist['price'];
            ?>
            <div class="wishlist-card">
               <!-- Separate forms for delete and add to cart actions -->
               <div class="wishlist-image">
                  <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="<?= $fetch_wishlist['name']; ?>">
                  <form action="" method="post" class="delete-form">
                     <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
                     <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Delete this item?');">
                        <i class="fas fa-times"></i>
                     </button>
                  </form>
               </div>

               <div class="wishlist-info">
                  <h3 class="product-name"><?= htmlspecialchars($fetch_wishlist['name']); ?></h3>
                  <div class="price">
                     <span class="currency">Nrs.</span>
                     <span class="amount"><?= number_format($fetch_wishlist['price']); ?></span>
                  </div>
                  
                  <div class="product-stock">
                     <?php
                     $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
                     $check_stock->execute([$fetch_wishlist['pid']]);
                     $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];
                     ?>
                     <?php if ($product_stock == 0): ?>
                           <span class="stock out-of-stock" style="color: red;">Out of Stock</span>
                     <?php elseif ($product_stock < 5): ?>
                           <span class="stock low-stock" style="color: #f67800;">Low Stock: <?= $product_stock; ?></span>
                     <?php endif; ?>
                  </div>
                  
                  <div class="wishlist-actions">
                     <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="view-btn">
                           <i class="fas fa-eye"></i>
                           <span>View Details</span>
                     </a>
                     
                     <form action="" method="post" class="cart-form">
                           <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
                           <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
                           <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
                           <input type="hidden" name="image" value="<?= $fetch_wishlist['image']; ?>">
                           
                           <button type="submit" name="add_to_cart" class="cart-btn" <?= $product_stock == 0 ? 'disabled' : ''; ?>>
                              <i class="fas fa-shopping-cart"></i>
                              <span>Add to Cart</span>
                           </button>
                     </form>
                  </div>
               </div>
            </div>
            <?php
            }
            ?>
         </div>

         <div class="wishlist-summary">
            <p class="grand-total">
               Total Amount: <span>Nrs. <?= number_format($grand_total); ?>/-</span>
            </p>
            <div class="wishlist-buttons">
               <a href="shop.php" class="continue-btn">Continue Shopping</a>
               <a href="wishlist.php?delete_all" class="delete-all-btn" onclick="return confirm('Delete all from wishlist?');">
                  Delete All Items
               </a>
            </div>
         </div>
         <?php
         }else{
            echo '<div class="empty-wishlist">
                     <img src="images/empty-wishlist.png" alt="Empty Wishlist">
                     <h3>Your wishlist is empty!</h3>
                     <p>Add items that you want to buy later</p>
                     <a href="shop.php" class="shop-btn">Start Shopping</a>
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

<script>

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.cart-form');
    
    forms.forEach(form => {
        const cartBtn = form.querySelector('.cart-btn');
        if(cartBtn) {
            cartBtn.onclick = function(e) {
                e.preventDefault();
                handleAction(form, 'add_to_cart');
            };
        }

        const qtyInput = form.querySelector('.qty-input');
        if(qtyInput) {
            qtyInput.oninput = function() {
                const maxQty = parseInt(qtyInput.max);
                const currentQty = parseInt(qtyInput.value);
                const stockSpan = form.closest('.wishlist-info').querySelector('.product-stock span');

                if (currentQty >= maxQty) {
                    stockSpan.style.color = 'red';
                    stockSpan.textContent = `In Stock: ${maxQty}`;
                } else if (maxQty < 5) {
                    stockSpan.style.color = '#f67800';
                    stockSpan.textContent = `Low Stock: ${maxQty}`;
                } else {
                    stockSpan.style.color = 'green';
                    stockSpan.textContent = `In Stock: ${maxQty}`;
                }
            };
        }
    });

    function handleAction(form, action) {
        if('<?= $user_id ?>' === '') {
            showMessage('please login first!', true);
            return;
        }

        const formData = new FormData(form);
        formData.append('action', action);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            showMessage(data);
            if (!data.includes('already') && !data.includes('not enough stock')) {
                if (action === 'add_to_cart') {
                    updateHeaderCounts('cart');
                }
            }
        })
        .catch(error => {
            showMessage('something went wrong!', true);
            console.error('Error:', error);
        });
    }

    function showMessage(msg) {
        const message = document.createElement('div');
        message.className = 'message';
        message.innerHTML = `
            <span>${msg}</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        `;
        document.body.appendChild(message);
        setTimeout(() => message.remove(), 3000);
    }

    function updateHeaderCounts(type) {
        if(type === 'cart') {
            const cartCounts = document.querySelectorAll('.cart-count, .count[data-type="cart"]');
            cartCounts.forEach(count => {
                let currentCount = parseInt(count.textContent) || 0;
                count.textContent = currentCount + 1;
            });
        }
    }
});
</script>