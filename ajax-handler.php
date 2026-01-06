<?php
include 'components/connect.php';
session_start();

$response = ['status' => 'error', 'message' => 'Something went wrong!'];

if(!isset($_SESSION['user_id'])){
    $response['message'] = 'Please login first!';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['action'])){
    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);

    if($_POST['action'] === 'add_to_cart'){
        $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
        $check_cart->execute([$name, $user_id]);

        if($check_cart->rowCount() > 0){
            $response = ['status' => 'error', 'message' => 'Already in cart!'];
        } else {
            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $name, $price, 1, $image]);
            $response = ['status' => 'success', 'message' => 'Added to cart!'];
        }
    }

    if($_POST['action'] === 'add_to_wishlist'){
        $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist->execute([$name, $user_id]);

        if($check_wishlist->rowCount() > 0){
            $response = ['status' => 'error', 'message' => 'Already in wishlist!'];
        } else {
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
            $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
            $response = ['status' => 'success', 'message' => 'Added to wishlist!'];
        }
    }
}

echo json_encode($response);
?>