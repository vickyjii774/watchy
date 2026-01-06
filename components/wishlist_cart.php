<?php

if(isset($_POST['add_to_wishlist'])){
   $redirect_url = isset($_GET['search']) ? 'search_page.php?search=' . urlencode($_GET['search']) : $_SERVER['PHP_SELF'];

   if($user_id == ''){
       header('location:user_login.php');
       exit();
   }else{
       $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
       $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
       $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
       $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);

       $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
       $check_wishlist->execute([$name, $user_id]);

       $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
       $check_cart->execute([$name, $user_id]);

       if($check_wishlist->rowCount() > 0){
           $_SESSION['message'] = 'Product already exists in wishlist!';
       } elseif($check_cart->rowCount() > 0){
           $_SESSION['message'] = 'Product is already in the cart!';
       } else {
           $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
           $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
           $_SESSION['message'] = 'Added to wishlist!';
       }
       header('location:'.$redirect_url);
       exit();
   }
}

if(isset($_POST['add_to_cart'])){
   $redirect_url = isset($_GET['search']) ? 'search_page.php?search=' . urlencode($_GET['search']) : $_SERVER['PHP_SELF'];

   if($user_id == ''){
       header('location:user_login.php');
       exit();
   }else{
       $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
       $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
       $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
       $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
       $qty = filter_var($_POST['qty'] ?? 1, FILTER_SANITIZE_STRING);

       $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
       $check_cart_numbers->execute([$name, $user_id]);

       if($check_cart_numbers->rowCount() > 0){
           $_SESSION['message'] = 'Already added to cart!';
       }else{
           $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
           $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
           $_SESSION['message'] = 'Product added to cart successfully!';
       }
       header('location:'.$redirect_url);
       exit();
   }
}

?>