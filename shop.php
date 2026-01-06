<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

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
  
      $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
      $check_stock->execute([$pid]);
      $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC)['stock'];
  
      if($product_stock < $qty) {
          echo 'not enough stock!';
      } elseif($check_cart_numbers->rowCount() > 0) {
          echo 'already added to cart!';
      } else {
          $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
          $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
          echo 'added to cart!';
      }
   }
    
   if($_POST['action'] == 'add_to_wishlist') {
      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         echo 'already added to wishlist!';}
      else{
         $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
         echo 'added to wishlist!';
        }
      }
    exit;
}

include 'components/wishlist_cart.php';

// Get price range filter
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;

// Get sorting option
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop | JerseyMandu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="shop-section">
   <div class="container">
      <!-- Hero Banner -->
      <div class="shop-hero">
         <div class="hero-content">
            <h1>Premium Jersey Collection</h1>
            <p>Discover the best quality jersey.</p>
         </div>
      </div>
      
      <!-- Filters Bar -->
      <div class="filters-bar">
         <div class="filters-left">
            <form class="price-filter" method="GET">
               <div class="filter-group">
                  <label>Price Range:</label>
                  <div class="price-inputs">
                     <input type="number" name="min_price" placeholder="Min" value="<?= $min_price ?>" min="0">
                     <span>-</span>
                     <input type="number" name="max_price" placeholder="Max" value="<?= $max_price ?>" min="0">
                     <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i>
                     </button>
                  </div>
               </div>
            </form>
         </div>
         
         <div class="filters-right">
            <div class="sort-group">
               <label>Sort by:</label>
               <select name="sort" onchange="location = this.value;" class="sort-select">
                  <option value="?sort=latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                  <option value="?sort=price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                  <option value="?sort=price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                  <option value="?sort=name-asc" <?= $sort === 'name-asc' ? 'selected' : '' ?>>Name: A to Z</option>
               </select>
            </div>
         </div>
      </div>

      <!-- Products Section -->
      <div class="products-section">
         <div class="section-header">
            <div class="results-info">
               <?php
               $count_query = $conn->prepare("SELECT COUNT(*) as total FROM `products`");
               $count_query->execute();
               $total = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
               echo "<span class='results-count'>{$total} Products</span>";
               ?>
            </div>
         </div>

         <div class="products-grid">
               <?php
                  $query = "SELECT * FROM `products` WHERE 1";
                  
                  // Apply price filter
                  $query .= " AND price BETWEEN :min_price AND :max_price";
                  
                  // Apply sorting
                  switch($sort) {
                     case 'price-low':
                        $query .= " ORDER BY price ASC";
                        break;
                     case 'price-high':
                        $query .= " ORDER BY price DESC";
                        break;
                     case 'name-asc':
                        $query .= " ORDER BY name ASC";
                        break;
                     default:
                        $query .= " ORDER BY id DESC";
                  }

                  $select_products = $conn->prepare($query);
                  
                  // Bind parameters
                  $select_products->bindParam(':min_price', $min_price);
                  $select_products->bindParam(':max_price', $max_price);
                  
                  $select_products->execute();

                  if($select_products->rowCount() > 0){
                     while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
               ?>
               <div class="product-card">
                  <form action="" method="post" class="product-form">
                     <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                     <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
                     <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                     <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
                     
                     <div class="product-image">
                        <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?>">
                        <div class="product-overlay">
                           <div class="product-actions">
                              <button type="button" class="action-btn wishlist-btn" title="Add to Wishlist">
                                 <i class="fas fa-heart"></i>
                              </button>
                              <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="action-btn" title="Quick View">
                                 <i class="fas fa-eye"></i>
                              </a>
                           </div>
                        </div>
                        <?php if ($fetch_product['stock'] == 0): ?>
                           <div class="stock-badge out-of-stock">Out of Stock</div>
                        <?php elseif ($fetch_product['stock'] < 5): ?>
                           <div class="stock-badge low-stock">Low Stock</div>
                        <?php endif; ?>
                     </div>

                     <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($fetch_product['name']); ?></h3>
                        <div class="product-price">
                           <span class="current-price">Nrs. <?= number_format($fetch_product['price']); ?></span>
                        </div>
                        
                        <div class="product-actions-bottom">
                           <div class="quantity-selector">
                              <button type="button" class="qty-btn minus" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>-</button>
                              <input type="number" name="qty" class="qty-input" min="1" max="<?= $fetch_product['stock']; ?>" value="1" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?> readonly>
                              <button type="button" class="qty-btn plus" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>+</button>
                           </div>
                           <button type="button" class="cart-btn" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>
                              <i class="fas fa-shopping-bag"></i>
                           </button>
                        </div>
                     </div>
                  </form>
               </div>
               <?php
                     }
                  }else{
                     echo '<p class="empty">No products found!</p>';
                  }
               ?>
            </div>
         </div>
      </div>
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
            wishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleAction(form, 'add_to_wishlist');
            });
        }

        // Cart button handler
        const cartBtn = form.querySelector('.cart-btn');
        if(cartBtn) {
            cartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleAction(form, 'add_to_cart');
            });
        }
        


        // Get quantity elements
        const qtyInput = form.querySelector('.qty-input');
        const minusBtn = form.querySelector('.qty-btn.minus');
        const plusBtn = form.querySelector('.qty-btn.plus');
        
        // Quantity buttons handlers
        if(minusBtn) {
            minusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const currentValue = parseInt(qtyInput.value);
                if(currentValue > 1) {
                    qtyInput.value = currentValue - 1;
                }
            });
        }
        
        if(plusBtn) {
            plusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const currentValue = parseInt(qtyInput.value);
                const maxValue = parseInt(qtyInput.max);
                if(currentValue < maxValue) {
                    qtyInput.value = currentValue + 1;
                }
            });
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
            // Update counts only if item was successfully added (not "already added" or "not enough stock")
            if (!data.includes('already') && !data.includes('not enough stock')) {
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
