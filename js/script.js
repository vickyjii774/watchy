document.addEventListener("DOMContentLoaded", function() {
   // Navbar and Profile Toggle
   let navbar = document.querySelector('.header .flex .navbar');
   let profile = document.querySelector('.header .flex .profile');
   let menuBtn = document.querySelector('#menu-btn');
   let userBtn = document.querySelector('#user-btn');

   if (menuBtn) {
       menuBtn.onclick = () => {
           navbar.classList.toggle('active');
           if (profile) profile.classList.remove('active');
       };
   }

   if (userBtn) {
       userBtn.onclick = () => {
           profile.classList.toggle('active');
           if (navbar) navbar.classList.remove('active');
       };
   }

   window.onscroll = () => {
       if (navbar) navbar.classList.remove('active');
       if (profile) profile.classList.remove('active');
   };

   // Product Image Swap
   let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');
   let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');

   if (mainImage && subImages.length > 0) {
       subImages.forEach(image => {
           image.onclick = () => {
               let src = image.getAttribute('src');
               mainImage.src = src;
           };
       });
   }

   // Mobile Menu Toggle
   let mobileMenuBtn = document.querySelector('.mobile-menu-btn');
   let navList = document.querySelector('.nav-list');

   if (mobileMenuBtn && navList) {
       mobileMenuBtn.addEventListener('click', function() {
           navList.classList.toggle('active');
       });

       document.addEventListener('click', function(e) {
           if (!navList.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
               navList.classList.remove('active');
           }
       });
   }

   // AJAX Request Function
   function sendRequest(action, button) {
       let pid = button.getAttribute("data-pid");
       let name = button.getAttribute("data-name");
       let price = button.getAttribute("data-price");
       let image = button.getAttribute("data-image");

       let formData = new FormData();
       formData.append("action", action);
       formData.append("pid", pid);
       formData.append("name", name);
       formData.append("price", price);
       formData.append("image", image);

       fetch("ajax-handler.php", {
           method: "POST",
           body: formData
       })
       .then(response => response.json())
       .then(data => {
           showMessage(data.message, data.status);
       })
       .catch(error => console.error("Error:", error));
   }

   // Add to Cart & Wishlist
   document.querySelectorAll(".add-to-cart").forEach(button => {
       button.addEventListener("click", function() {
           sendRequest("add_to_cart", this);
       });
   });

   document.querySelectorAll(".add-to-wishlist").forEach(button => {
       button.addEventListener("click", function() {
           sendRequest("add_to_wishlist", this);
       });
   });

   // Show Message Function
   function showMessage(message, status) {
       let messageBox = document.createElement("div");
       messageBox.className = `message ${status}`;
       messageBox.innerHTML = `<span>${message}</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i>`;

       document.body.appendChild(messageBox);

       setTimeout(() => {
           messageBox.remove();
       }, 3000);
   }
});