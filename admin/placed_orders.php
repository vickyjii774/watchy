<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'Payment status updated!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

// Get filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query based on filters
$query = "SELECT orders.*, users.name as user_name FROM `orders` 
          LEFT JOIN `users` ON orders.user_id = users.id WHERE payment_status != 'failed'";

if($status_filter != 'all') {
    $query .= " AND orders.payment_status = '$status_filter'";
}

if($date_filter == 'today') {
    $query .= " AND DATE(placed_on) = CURDATE()";
} elseif($date_filter == 'week') {
    $query .= " AND placed_on >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
} elseif($date_filter == 'month') {
    $query .= " AND placed_on >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
}

// Add sorting
if($sort_by == 'newest') {
    $query .= " ORDER BY placed_on DESC";
} elseif($sort_by == 'oldest') {
    $query .= " ORDER BY placed_on ASC";
} elseif($sort_by == 'highest') {
    $query .= " ORDER BY total_price DESC";
} elseif($sort_by == 'lowest') {
    $query .= " ORDER BY total_price ASC";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">
   <h1 class="heading">Placed Orders</h1>

   <div class="filter-container">
      <form action="" method="GET" class="filter-form">
         <div class="filter-group">
            <label>Status:</label>
            <select name="status" onchange="this.form.submit()">
               <option value="all" <?= ($status_filter == 'all') ? 'selected' : ''; ?>>All Status</option>
               <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
               <option value="completed" <?= ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
               <option value="cancelled" <?= ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
               <option value="failed" <?= ($status_filter == 'failed') ? 'selected' : ''; ?>>Failed</option>
            </select>
         </div>

         <div class="filter-group">
            <label>Date:</label>
            <select name="date" onchange="this.form.submit()">
               <option value="all" <?= ($date_filter == 'all') ? 'selected' : ''; ?>>All Time</option>
               <option value="today" <?= ($date_filter == 'today') ? 'selected' : ''; ?>>Today</option>
               <option value="week" <?= ($date_filter == 'week') ? 'selected' : ''; ?>>This Week</option>
               <option value="month" <?= ($date_filter == 'month') ? 'selected' : ''; ?>>This Month</option>
            </select>
         </div>

         <div class="filter-group">
            <label>Sort By:</label>
            <select name="sort" onchange="this.form.submit()">
               <option value="newest" <?= ($sort_by == 'newest') ? 'selected' : ''; ?>>Newest First</option>
               <option value="oldest" <?= ($sort_by == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
               <option value="highest" <?= ($sort_by == 'highest') ? 'selected' : ''; ?>>Highest Amount</option>
               <option value="lowest" <?= ($sort_by == 'lowest') ? 'selected' : ''; ?>>Lowest Amount</option>
            </select>
         </div>
      </form>
   </div>

   <div class="table-container">
      <?php
         $select_orders = $conn->prepare($query);
         $select_orders->execute();
         if($select_orders->rowCount() > 0){
      ?>
      <table>
         <thead>
            <tr>
               <th>Order ID</th>
               <th>Date</th>
               <th>Customer Details</th>
               <th>Order Details</th>
               <th>Amount</th>
               <th>Payment Method</th>
               <th>Status</th>
               <th>Actions</th>
            </tr>
         </thead>
         <tbody>
         <?php while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){ 
            $products = explode(',', $fetch_orders['total_products']);
         ?>
            <tr>
               <td>#<?= $fetch_orders['id']; ?></td>
               <td><?= date('d-M-Y', strtotime($fetch_orders['placed_on'])); ?></td>
               <td>
                  <p><strong><?= $fetch_orders['name']; ?></strong></p>
                  <p>Phone: <?= $fetch_orders['number']; ?></p>
                  <p>Email: <?= $fetch_orders['email']; ?></p>
                  <p class="small">Address: <?= $fetch_orders['address']; ?></p>
               </td>
               <td>
                  <div class="products-list">
                     <?php foreach($products as $product): ?>
                        <p class="product-item"><?= $product; ?></p>
                     <?php endforeach; ?>
                  </div>
               </td>
               <td>Nrs.<?= number_format($fetch_orders['total_price']); ?>/-</td>
               <td><?= $fetch_orders['method']; ?></td>
               <td>
               <form action="" method="post" class="status-form">
                  <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                  <input type="hidden" name="update_payment" value="1">
                  <select name="payment_status" class="select status-<?= $fetch_orders['payment_status']; ?>" onchange="this.form.submit()">
                     <option value="pending" <?= ($fetch_orders['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                     <option value="completed" <?= ($fetch_orders['payment_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                     <option value="cancelled" <?= ($fetch_orders['payment_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                     <option value="failed" <?= ($fetch_orders['payment_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                  </select>
               </form>

               </td>
               <td>
                  
               <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" 
                  class="delete-btn" 
                  onclick="return confirm('Delete this order?');"
                  title="Delete Order"
                  style=" min-width: auto;">
                  
                  <i class="fas fa-trash-alt"></i>
               </a>

               </td>
            </tr>
         <?php } ?>
         </tbody>
      </table>
      <?php } else { ?>
         <p class="empty">No orders found!</p>
      <?php } ?>
   </div>
</section>

<style>
.filter-container {
    margin: 20px auto;
    max-width: 1200px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    font-size: 1.6rem;
    color: #333;
    white-space: nowrap;
}

.filter-group select {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1.4rem;
    color: #333;
    min-width: 150px;
}
.table-container {
    margin: 20px auto;
    max-width: 1200px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow-x: auto; /* Ensures horizontal scrolling if needed */
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* This helps control column widths */
}

th, td {
    padding: 8px 10px; /* Reduced padding */
    text-align: left;
    border-bottom: 1px solid #ddd;
    font-size: 1.4rem;
    vertical-align: top;
}

/* Specific column widths */
table th:nth-child(1), /* Order ID */
table td:nth-child(1) {
    width: 8%;
}

table th:nth-child(2), /* Date */
table td:nth-child(2) {
    width: 10%;
}

table th:nth-child(3), /* Customer Details */
table td:nth-child(3) {
    width: 20%;
}

table th:nth-child(4), /* Order Details */
table td:nth-child(4) {
    width: 20%;
}

table th:nth-child(5), /* Amount */
table td:nth-child(5) {
    width: 10%;
}

table th:nth-child(6), /* Payment Method */
table td:nth-child(6) {
    width: 12%;
}

table th:nth-child(7), /* Status */
table td:nth-child(7) {
    width: 12%;
}

table th:nth-child(8), /* Actions */
table td:nth-child(8) {
    width: 8%;
}

/* Status select styling */
.status-form select {
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
    font-size: 1.3rem;
    width: 100%;
    min-width: 100px;
}

/* Product list improvements */
.products-list {
    max-height: 150px;
    overflow-y: auto;
    padding-right: 5px;
}

.product-item {
    margin-bottom: 3px;
    padding-bottom: 3px;
    border-bottom: 1px dashed #eee;
    word-break: break-word; /* Prevents text from overflowing */
}

/* Customer details spacing */
td p {
    margin: 2px 0;
    line-height: 1.4;
}



/* Responsive adjustments */
@media (max-width: 1200px) {
    .table-container {
        margin: 20px;
    }
    
    table {
        min-width: 1000px;
    }
    
    th, td {
        padding: 6px 8px; /* Further reduce padding on smaller screens */
    }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide messages after 3 seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
    });
});
</script>

</body>
</html>
