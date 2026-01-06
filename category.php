<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Category Products | WatchShop</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="category-products">
   <div class="container">
      <?php
         $category = isset($_GET['category']) ? $_GET['category'] : '';
         // Sanitize category name for display
         $display_category = ucwords(str_replace('-', ' ', $category));
      ?>
      
      <div class="page-header">
         <div class="header-content">
            <h1><?= $display_category ?> Watches</h1>
            <div class="breadcrumb">
               <a href="home.php">Home</a>
               <i class="fas fa-angle-right"></i>
               <a href="shop.php">Shop</a>
               <i class="fas fa-angle-right"></i>
               <span><?= $display_category ?>
            </div>
         </div>
      </div>

      <div class="products-container">
         <div class="filters-section">
            <div class="filter-group">
               <h3>Sort By</h3>
               <select class="sort-select" onchange="window.location.href=this.value">
                  <option value="?category=<?= $category ?>&sort=default">Default</option>
                  <option value="?category=<?= $category ?>&sort=price-low">Price: Low to High</option>
                  <option value="?category=<?= $category ?>&sort=price-high">Price: High to Low</option>
                  <option value="?category=<?= $category ?>&sort=name">Name: A to Z</option>
               </select>
            </div>
         </div>

         <div class="products-grid">
            <?php
               $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
               
               $query = "SELECT * FROM `products` WHERE name LIKE ?";
               $params = ["%{$category}%"];
               
               // Add sorting
               switch($sort) {
                  case 'price-low':
                     $query .= " ORDER BY price ASC";
                     break;
                  case 'price-high':
                     $query .= " ORDER BY price DESC";
                     break;
                  case 'name':
                     $query .= " ORDER BY name ASC";
                     break;
                  default:
                     $query .= " ORDER BY id DESC";
               }
               
               $select_products = $conn->prepare($query);
               $select_products->execute($params);

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
                     <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= $fetch_product['name']; ?>">
                     <div class="product-actions">
                        <button type="submit" name="add_to_wishlist" class="action-btn wishlist-btn">
                           <i class="fas fa-heart"></i>
                        </button>
                        <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="action-btn view-btn">
                           <i class="fas fa-eye"></i>
                        </a>
                     </div>
                  </div>

                  <div class="product-info">
                     <h3 class="product-name"><?= $fetch_product['name']; ?></h3>
                     <div class="product-price">
                        <span class="price">Nrs. <?= number_format($fetch_product['price']); ?>/-
                     </div>
                     <div class="product-details">
                        <div class="quantity">
                           <input type="number" name="qty" class="qty-input" min="1" max="99" value="1" 
                                  onkeypress="if(this.value.length == 2) return false;">
                        </div>
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                           <i class="fas fa-shopping-cart"></i>
                           <span>Add to Cart
                        </button>
                     </div>
                  </div>
               </form>
            </div>
            <?php
                  }
               }else{
                  echo '<div class="empty-category">
                           <img src="images/no-products.svg" alt="No Products Found">
                           <h3>No Products Found!</h3>
                           <p>We couldn\'t find any products in this category.</p>
                           <a href="shop.php" class="browse-btn">Browse Other Categories</a>
                        </div>';
               }
            ?>
         </div>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
