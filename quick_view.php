<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

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
            echo 'already added to wishlist!';
        }else{
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
            $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
            echo 'added to wishlist!';
        }
    }
    exit;
}

if(isset($_GET['pid'])){
   $pid = $_GET['pid'];
   $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $select_product->execute([$pid]);
   if($select_product->rowCount() > 0){
      $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
   }else{
      header('location:shop.php');
      exit();
   }
}else{
   header('location:shop.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $fetch_product['name']; ?> | WatchShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="quick-view-container">
    <div class="quick-view-wrapper">
        <div class="quick-view-content">
            <!-- Product Gallery -->
            <div class="quick-view-gallery">
                <div class="quick-view-main-image">
                    <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="" id="main-image">
                </div>
                <div class="quick-view-thumbnails">
                    <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="" 
                         onclick="changeImage(this)" class="active">
                    <?php if($fetch_product['image_02'] != ''): ?>
                    <img src="uploaded_img/<?= $fetch_product['image_02']; ?>" alt="" 
                         onclick="changeImage(this)">
                    <?php endif; ?>
                    <?php if($fetch_product['image_03'] != ''): ?>
                    <img src="uploaded_img/<?= $fetch_product['image_03']; ?>" alt="" 
                         onclick="changeImage(this)">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="quick-view-info">
                <div class="quick-view-header">
                    <h1 class="quick-view-title"><?= htmlspecialchars($fetch_product['name']); ?></h1>
                    <div class="quick-view-price">
                        <span class="quick-view-current-price">Nrs. <?= number_format($fetch_product['price']); ?>/-</span>
                    </div>
                </div>

                <div class="quick-view-description">
                    <h3>Description</h3>
                    <p><?= htmlspecialchars($fetch_product['details']); ?></p>
                </div>

                <form action="" method="post" class="product-form">
                    <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
                    <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                    <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">

                    <div class="quick-view-quantity">
                        <label>Quantity:</label>
                        <div class="quick-view-quantity-controls">
                            <button type="button" class="quick-view-qty-btn minus">-</button>
                            <input type="number" name="qty" class="quick-view-qty-input" min="1" max="<?= $fetch_product['stock']; ?>" value="1" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>
                            <button type="button" class="quick-view-qty-btn plus">+</button>
                        </div>
                    </div>

                    <div class="product-stock">
                        <?php if ($fetch_product['stock'] == 0): ?>
                            <span class="stock out-of-stock" style="color: red;">Out of Stock</span>
                        <?php elseif ($fetch_product['stock'] < 5): ?>
                            <span class="stock low-stock" style="color: #f67800;">Low Stock: <?= $fetch_product['stock']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="quick-view-actions">
                        <button type="button" name="add_to_cart" class="quick-view-btn quick-view-btn-primary" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        <button type="button" name="add_to_wishlist" class="quick-view-btn quick-view-btn-secondary">
                            <i class="fas fa-heart"></i>
                            Add to Wishlist
                        </button>
                    </div>
                </form>

                <div class="quick-view-additional">
                    <div class="quick-view-info-item">
                        <i class="fas fa-truck"></i>
                        <span>Free Delivery</span>
                    </div>
                    <div class="quick-view-info-item">
                        <i class="fas fa-undo"></i>
                        <span>30 Days Return</span>
                    </div>
                    <div class="quick-view-info-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure Payment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the form
    const form = document.querySelector('.product-form');
    
    // Add click handlers for both buttons
    const cartBtn = form.querySelector('[name="add_to_cart"]');
    const wishlistBtn = form.querySelector('[name="add_to_wishlist"]');

    if(cartBtn) {
        cartBtn.onclick = function(e) {
            e.preventDefault();
            handleAction(form, 'add_to_cart');
        };
    }

    if(wishlistBtn) {
        wishlistBtn.onclick = function(e) {
            e.preventDefault();
            handleAction(form, 'add_to_wishlist');
        };
    }

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

    // Image gallery functionality
    function changeImage(element) {
        document.getElementById('main-image').src = element.src;
        document.querySelectorAll('.quick-view-thumbnails img').forEach(img => {
            img.classList.remove('active');
        });
        element.classList.add('active');
    }

    // Quantity controls
    const minusBtn = document.querySelector('.quick-view-qty-btn.minus');
    const plusBtn = document.querySelector('.quick-view-qty-btn.plus');
    const qtyInput = document.querySelector('.quick-view-qty-input');

    if(plusBtn && minusBtn && qtyInput) {
        plusBtn.addEventListener('click', function() {
            let currentValue = parseInt(qtyInput.value);
            if (currentValue < parseInt(qtyInput.max)) {
                qtyInput.value = currentValue + 1;
            }
        });

        minusBtn.addEventListener('click', function() {
            let currentValue = parseInt(qtyInput.value);
            if (currentValue > 1) {
                qtyInput.value = currentValue - 1;
            }
        });

        qtyInput.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > parseInt(this.max)) {
                this.value = this.max;
            }
        });
    }
});
</script>

</body>
</html>
