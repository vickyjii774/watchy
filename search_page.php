<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Handle AJAX requests
if(isset($_POST['action']) && !empty($user_id)) {
    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $qty = $_POST['qty'];
    
    if($_POST['action'] == 'add_to_cart') {
        $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
        $check_cart_numbers->execute([$name, $user_id]);

        if($check_cart_numbers->rowCount() > 0){
            echo 'already added to cart!';
        }else{
            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
            echo 'added to cart!';
        }
    }
    
    if($_POST['action'] == 'add_to_wishlist') {
        $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist_numbers->execute([$name, $user_id]);

        if($check_wishlist_numbers->rowCount() > 0){
            echo 'already added to wishlist!';
        }else{
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
            $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
            echo 'added to wishlist!';
        }
    }
    exit;
}

include 'components/wishlist_cart.php';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search_query)) {
    $search_pattern = '%' . $search_query . '%';
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ? OR details LIKE ?");
    $select_products->execute([$search_pattern, $search_pattern]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Results | WatchShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="search-page">
   <div class="heading">
      <h1>Search Results for "<?= htmlspecialchars($search_query) ?>"</h1>
   </div>
   <div class="box-container">
      <?php
      if (!empty($search_query) && $select_products->rowCount() > 0) {
         while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="product-card">
         <form action="" method="post" class="product-form">
            <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
            <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
            <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
            <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
            
            <div class="product-image">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?>">
               <div class="product-actions">
                  <button type="button" class="action-btn wishlist-btn">
                     <i class="fas fa-heart"></i>
                  </button>
                  <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="action-btn">
                     <i class="fas fa-eye"></i>
                  </a>
               </div>
            </div>

            <div class="product-info">
               <h3 class="product-name"><?= htmlspecialchars($fetch_product['name']); ?></h3>
               <div class="product-price">
                  <span class="price">Nrs. <?= number_format($fetch_product['price']); ?>/-</span>
               </div>
               <div class="product-footer">
                  <div class="quantity">
                     <input type="number" name="qty" class="qty-input" min="1" max="99" value="1">
                  </div>
                  <button type="button" class="add-to-cart">
                     Add to Cart
                  </button>
               </div>
            </div>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<div class="empty">No products found!</div>';
      }
      ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.product-form');
    
    forms.forEach(form => {
        // Wishlist button handler
        const wishlistBtn = form.querySelector('.wishlist-btn');
        if(wishlistBtn) {
            wishlistBtn.onclick = function(e) {
                e.preventDefault();
                handleAction(form, 'add_to_wishlist');
            };
        }

        // Cart button handler
        const cartBtn = form.querySelector('.add-to-cart');
        if(cartBtn) {
            cartBtn.onclick = function(e) {
                e.preventDefault();
                handleAction(form, 'add_to_cart');
            };
        }
    });

    function updateHeaderCounts(type) {
        if(type === 'cart') {
            const cartCounts = document.querySelectorAll('.cart-count, .count[data-type="cart"]');
            cartCounts.forEach(count => {
                let currentCount = parseInt(count.textContent) || 0;
                count.textContent = currentCount + 1;
            });
        } else if(type === 'wishlist') {
            const wishlistCounts = document.querySelectorAll('.wishlist-count, .count[data-type="wishlist"]');
            wishlistCounts.forEach(count => {
                let currentCount = parseInt(count.textContent) || 0;
                count.textContent = currentCount + 1;
            });
        }
    }

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
            // Update counts only if item was successfully added (not "already added")
            if (!data.includes('already')) {
                if (action === 'add_to_cart') {
                    updateHeaderCounts('cart');
                } else if (action === 'add_to_wishlist') {
                    updateHeaderCounts('wishlist');
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
});
</script>

</body>
</html>
