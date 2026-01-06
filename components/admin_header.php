<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <section class="flex">
      <a href="dashboard.php" class="logo">Admin<span>Panel</span></a>
      
      <nav class="navbar">
         <a href="dashboard.php">Home</a>
         <a href="products.php">Products</a>
         <a href="placed_orders.php">Orders</a>
         <a href="users_accounts.php">Users</a>
         <?php 
            // Check if user is superadmin
            $select_admin = $conn->prepare("SELECT role FROM `admins` WHERE id = ?");
            $select_admin->execute([$_SESSION['admin_id']]);
            $admin = $select_admin->fetch(PDO::FETCH_ASSOC);
            
            if($admin && $admin['role'] === 'superadmin'): 
         ?>
            <a href="admin_accounts.php">Admins</a>
         <?php endif; ?>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
            $select_profile->execute([$_SESSION['admin_id']]);
            if($select_profile->rowCount() > 0){
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p class="name"><?= $fetch_profile['name']; ?></p>
         <div class="flex-btn">
            <a href="update_profile.php" class="option-btn">update profile</a>
            <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
         </div>
         <?php
            }
         ?>
      </div>
   </section>
</header>

<style>
.header {
   position: sticky;
   top: 0;
   left: 0;
   right: 0;
   background-color: var(--white);
   box-shadow: var(--box-shadow);
   z-index: 1000;
}

.header .flex {
   display: flex;
   align-items: center;
   justify-content: space-between;
   position: relative;
   padding: 1.5rem 2rem;
}

.header .flex .logo {
   font-size: 2.5rem;
   color: var(--black);
}

.header .flex .logo span {
   color: var(--main-color);
}

.header .flex .navbar a {
   margin: 0 1rem;
   font-size: 2rem;
   color: var(--black);
}

.header .flex .navbar a:hover {
   color: var(--main-color);
   text-decoration: underline;
}

.header .flex .icons div {
   margin-left: 1.5rem;
   font-size: 2.5rem;
   cursor: pointer;
   color: var(--black);
}

.header .flex .icons div:hover {
   color: var(--main-color);
}

.header .flex .profile {
   position: absolute;
   top: 120%;
   right: 2rem;
   background-color: var(--white);
   border-radius: .5rem;
   box-shadow: var(--box-shadow);
   border: var(--border);
   padding: 2rem;
   width: 30rem;
   padding-top: 1.2rem;
   display: none;
   animation: fadeIn .2s linear;
}

.header .flex .profile.active {
   display: inline-block;
}

.header .flex .profile p {
   text-align: center;
   color: var(--black);
   font-size: 2rem;
   margin-bottom: 1rem;
}

#menu-btn {
   display: none;
}

/* Media Queries */
@media (max-width:991px) {
   .header .flex .navbar {
      position: absolute;
      top: 99%;
      left: 0;
      right: 0;
      border-top: var(--border);
      border-bottom: var(--border);
      background-color: var(--white);
      transition: .2s linear;
      clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
   }

   .header .flex .navbar.active {
      clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
   }

   .header .flex .navbar a {
      display: block;
      margin: 2rem;
   }

   #menu-btn {
      display: inline-block;
   }
}
</style>

<script>
let profile = document.querySelector('.header .flex .profile');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   navbar.classList.remove('active');
}

let navbar = document.querySelector('.header .flex .navbar');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   profile.classList.remove('active');
}

window.onscroll = () =>{
   profile.classList.remove('active');
   navbar.classList.remove('active');
}
</script>
