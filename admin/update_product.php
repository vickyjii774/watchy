<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update'])){

    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);
    $details = $_POST['details'];
    $details = filter_var($details, FILTER_SANITIZE_STRING);
    $stock = $_POST['stock'];
    $stock = filter_var($stock, FILTER_SANITIZE_STRING);
 
    $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, details = ?, stock = ? WHERE id = ?");
    $update_product->execute([$name, $price, $details, $stock, $pid]);
 
    $message[] = 'product updated successfully!';
 
    $old_image_01 = $_POST['old_image_01'];
    $image_01 = $_FILES['image_01']['name'];
    $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
    $image_size_01 = $_FILES['image_01']['size'];
    $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
    $image_folder_01 = '../uploaded_img/'.$image_01;
 
    if(!empty($image_01)){
       if($image_size_01 > 2000000){
          $message[] = 'image size is too large!';
       }else{
          $update_image_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ?");
          $update_image_01->execute([$image_01, $pid]);
          move_uploaded_file($image_tmp_name_01, $image_folder_01);
          unlink('../uploaded_img/'.$old_image_01);
          $message[] = 'image 01 updated successfully!';
       }
    }
 
    $old_image_02 = $_POST['old_image_02'];
    $image_02 = $_FILES['image_02']['name'];
    $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
    $image_size_02 = $_FILES['image_02']['size'];
    $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
    $image_folder_02 = '../uploaded_img/'.$image_02;
 
    if(!empty($image_02)){
       if($image_size_02 > 2000000){
          $message[] = 'image size is too large!';
       }else{
          $update_image_02 = $conn->prepare("UPDATE `products` SET image_02 = ? WHERE id = ?");
          $update_image_02->execute([$image_02, $pid]);
          move_uploaded_file($image_tmp_name_02, $image_folder_02);
          unlink('../uploaded_img/'.$old_image_02);
          $message[] = 'image 02 updated successfully!';
       }
    }
 
    $old_image_03 = $_POST['old_image_03'];
    $image_03 = $_FILES['image_03']['name'];
    $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
    $image_size_03 = $_FILES['image_03']['size'];
    $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
    $image_folder_03 = '../uploaded_img/'.$image_03;
 
    if(!empty($image_03)){
       if($image_size_03 > 2000000){
          $message[] = 'image size is too large!';
       }else{
          $update_image_03 = $conn->prepare("UPDATE `products` SET image_03 = ? WHERE id = ?");
          $update_image_03->execute([$image_03, $pid]);
          move_uploaded_file($image_tmp_name_03, $image_folder_03);
          unlink('../uploaded_img/'.$old_image_03);
          $message[] = 'image 03 updated successfully!';
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
   <title>Update Product</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
    .update-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Row Styles */
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

/* Input Box Styles */
.inputBox {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.inputBox.full-width {
    width: 100%;
}

.inputBox span {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}

/* Input Styles */
.box {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.box:focus {
    border-color: #4CAF50;
    outline: none;
}

/* Textarea Styles */
textarea.box {
    min-height: 200px;
    resize: vertical;
    width: 100%;
}

/* Images Grid */
.images-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.image-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Image Preview Styles */
.image-preview {
    width: 100%;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9f9f9;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* File Input Styles */
.file-input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

/* Button Styles */
.flex-btn {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.btn, .option-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn {
    background: #4CAF50;
    color: white;
}

.btn:hover {
    background: #45a049;
}

.option-btn {
    background: #f44336;
    color: white;
}

.option-btn:hover {
    background: #da190b;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .images-grid {
        grid-template-columns: 1fr;
    }
}

   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Update Product</h1>

   <?php
      $update_id = $_GET['update'];
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$update_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="post" enctype="multipart/form-data" class="update-form">
    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
    <input type="hidden" name="old_image_01" value="<?= $fetch_products['image_01']; ?>">
    <input type="hidden" name="old_image_02" value="<?= $fetch_products['image_02']; ?>">
    <input type="hidden" name="old_image_03" value="<?= $fetch_products['image_03']; ?>">
    
    <div class="form-row">
        <div class="inputBox">
            <span>Product Name (required)</span>
            <input type="text" class="box" required maxlength="100" 
                   placeholder="enter product name" name="name" 
                   value="<?= $fetch_products['name']; ?>">
        </div>

        <div class="inputBox">
            <span>Product Price (required)</span>
            <input type="number" min="0" class="box" required 
                   max="9999999999" placeholder="enter product price" 
                   onkeypress="if(this.value.length == 10) return false;" 
                   name="price" value="<?= $fetch_products['price']; ?>">
        </div>

        <div class="inputBox">
            <span>Stock Quantity (required)</span>
            <input type="number" min="0" class="box" required 
                   max="9999999999" placeholder="enter stock quantity" 
                   name="stock" value="<?= $fetch_products['stock']; ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="inputBox full-width">
            <span>Product Details (required)</span>
            <textarea name="details" class="box" required 
                      placeholder="enter product details"><?= $fetch_products['details']; ?></textarea>
        </div>
    </div>

    <div class="images-grid">
        <div class="image-box">
            <span>Image 01</span>
            <div class="image-preview">
                <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
            </div>
            <input type="file" name="image_01" 
                   accept="image/jpg, image/jpeg, image/png, image/webp" 
                   class="file-input">
        </div>

        <div class="image-box">
            <span>Image 02</span>
            <div class="image-preview">
                <img src="../uploaded_img/<?= $fetch_products['image_02']; ?>" alt="">
            </div>
            <input type="file" name="image_02" 
                   accept="image/jpg, image/jpeg, image/png, image/webp" 
                   class="file-input">
        </div>

        <div class="image-box">
            <span>Image 03</span>
            <div class="image-preview">
                <img src="../uploaded_img/<?= $fetch_products['image_03']; ?>" alt="">
            </div>
            <input type="file" name="image_03" 
                   accept="image/jpg, image/jpeg, image/png, image/webp" 
                   class="file-input">
        </div>
    </div>

    <div class="flex-btn">
        <input type="submit" name="update" class="btn" value="Update Product">
        <a href="products.php" class="option-btn">Go Back</a>
    </div>
</form>


   
   <?php
         }
      }else{
         echo '<p class="empty">no product found!</p>';
      }
   ?>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>