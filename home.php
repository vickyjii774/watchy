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
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>JerseyMandu Nepal</title>
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>JerseyMandu Nepal</h1>
        <p>Class in Every Tick, Style in Every Click.</p>
        <a href="shop.php" class="cta-button">Shop Now</a>
    </div>
    <div class="hero-features">
        <div class="feature">
            <i class="fas fa-shipping-fast"></i>
            <span><font color="black">Free Shipping</font>
        </div>
        <div class="feature">
            <i class="fas fa-shield-alt"></i>
            <span><font color="black">Secure Payment</font>
        </div>
        <div class="feature">
            <i class="fas fa-undo"></i>
            <span><font color="black">Easy Returns</font>
        </div>
    </div>
</section><br><br>


<section class="featured-products">
    <div class="section-header">
        <h2>Featured Products</h2>
        <p>Our most popular selections</p>
    </div>
    <div class="products-grid">
        <?php
        $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 8"); 
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
                    <div class="product-stock">
                        <?php if ($fetch_product['stock'] == 0): ?>
                            <span class="stock out-of-stock" style="color: red;">Out of Stock</span>
                        <?php elseif ($fetch_product['stock'] < 5): ?>
                            <span class="stock low-stock" style="color: #f67800;">Low Stock: <?= $fetch_product['stock']; ?></span>
                        
                        <?php endif; ?>
                    </div>
                    <div class="product-footer">
                        <div class="quantity">
                            <input type="number" name="qty" class="qty-input" min="1" max="<?= $fetch_product['stock']; ?>" value="1" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>
                        </div>
                        <button type="button" class="add-to-cart" <?= $fetch_product['stock'] == 0 ? 'disabled' : ''; ?>>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No products added yet!</p>';
        }
        ?>
    </div>
</section>

<!-- Special Offers Section -->
<section class="special-offers">
    <div class="offer-grid">
        <div class="offer-card">
            <div class="offer-content">
                <h3>Premium Jerseys</h3>
                <p>Up to 50% off on selected items</p>
                <a href="shop.php?sort=price-high" class="offer-btn">Shop Now</a>
            </div>
            <!-- <img src="images/home-img-3.jpeg" alt="Premium Watches"> -->
        </div>
        <div class="offer-card">
            <div class="offer-content">
                <h3>Latest Jerseys</h3>
                <p>New arrivals with special launch prices</p>
                <a href="shop.php" class="offer-btn">Shop Now</a>
            </div>
            <!-- <img src="images/home-img-2.png" alt="Smartphones"> -->
        </div>
    </div>
</section>

<!-- Newsletter Section
<section class="newsletter">
    <div class="newsletter-content">
        <h3>Subscribe to Our Newsletter</h3>
        <p>Get updates about new products and special offers</p>
        <form class="newsletter-form">
            <input type="email" placeholder="Enter your email address">
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section> -->

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

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

        // Quantity input handler
        const qtyInput = form.querySelector('.qty-input');
        if(qtyInput) {
            qtyInput.oninput = function() {
                const maxQty = parseInt(qtyInput.max);
                const currentQty = parseInt(qtyInput.value);
                const stockSpan = form.querySelector('.product-stock span');

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

    function updateHeaderCounts(type) {
        // Get all count elements (there might be multiple in the header)
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
