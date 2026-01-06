<?php
   // Start the session only if it hasn't been started already
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }

   // Include the config file with the correct path
   include_once 'connect.php'; // Adjust the path to match your file structure

   // Initialize variables for logged-in and guest users
   $user_id = null;
   $total_wishlist_counts = 0;
   $total_cart_counts = 0;

   if (isset($_SESSION['user_id'])) {
       $user_id = $_SESSION['user_id'];

       // Fetch wishlist count
       $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
       $count_wishlist_items->execute([$user_id]);
       $total_wishlist_counts = $count_wishlist_items->rowCount();

       // Fetch cart count
       $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
       $count_cart_items->execute([$user_id]);
       $total_cart_counts = $count_cart_items->rowCount();
   }
?>


<header class="header">
    <!-- Main Header -->
    <div class="main-header">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo -->
                <div class="logo-section">
                    <a href="home.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="logo-text">
                            <h1>JerseyMandu <span>Nepal</span></h1>
                        </div>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="search-section">
                    <form action="search_page.php" method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" 
                            placeholder="Search products..." 
                            minlength="1" maxlength="100">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search" id="fasfas"></i>
                        </button>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Wishlist -->
                    <a href="<?php echo $user_id ? 'wishlist.php' : 'user_login.php'; ?>" class="action-btn" title="Wishlist">
                        <div class="action-icon">
                            <i class="fas fa-heart"></i>
                            <?php if($total_wishlist_counts > 0): ?>
                                <span class="badge"><?= $total_wishlist_counts; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="action-text">Wishlist</span>
                    </a>

                    <!-- Cart -->
                    <a href="<?php echo $user_id ? 'cart.php' : 'user_login.php'; ?>" class="action-btn" title="Shopping Cart">
                        <div class="action-icon">
                            <i class="fas fa-shopping-bag"></i>
                            <?php if($total_cart_counts > 0): ?>
                                <span class="badge"><?= $total_cart_counts; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="action-text">Cart</span>
                    </a>

                    <!-- User Account -->
                    <div class="user-menu">
                        <button class="user-btn" onclick="toggleUserMenu(event)">
                            <div class="user-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-text">
                                <?php if ($user_id): ?>
                                    <?php
                                        $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                                        $select_profile->execute([$user_id]);
                                        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <?= $fetch_profile["name"]; ?>
                                <?php else: ?>
                                    Account
                                <?php endif; ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown">
                            <?php if ($user_id): ?>
                                <div class="dropdown-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="user-details">
                                        <h4><?= $fetch_profile["name"]; ?></h4>
                                        <p><?= $fetch_profile["email"]; ?></p>
                                    </div>
                                </div>
                                <div class="dropdown-menu">
                                    <a href="orders.php" class="dropdown-item">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>My Orders</span>
                                    </a>
                                    <a href="update_user.php" class="dropdown-item">
                                        <i class="fas fa-user-edit"></i>
                                        <span>Profile Settings</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="components/user_logout.php" class="dropdown-item logout" 
                                       onclick="return confirm('Are you sure you want to logout?');">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="guest-menu">
                                    <a href="user_login.php" class="dropdown-item">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Login</span>
                                    </a>
                                    <a href="user_register.php" class="dropdown-item">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Register</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-wrapper">
                <ul class="nav-menu">
                    <li><a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="shop.php" class="nav-link"><i class="fas fa-store"></i> Shop</a></li>
                    <li><a href="orders.php" class="nav-link"><i class="fas fa-box"></i> Orders</a></li>
                    <li><a href="about.php" class="nav-link"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="contact.php" class="nav-link"><i class="fas fa-phone"></i> Contact</a></li>
                </ul>
                <button class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
</header>

<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    const dropdown = document.querySelector('.user-dropdown');
    
    dropdown.classList.toggle('dropdown-active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.user-dropdown');
    const userMenu = document.querySelector('.user-menu');
    
    if (!userMenu.contains(event.target)) {
        dropdown.classList.remove('dropdown-active');
    }
});

// Close dropdown when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.querySelector('.user-dropdown');
        dropdown.classList.remove('dropdown-active');
    }
});

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
});
</script>
