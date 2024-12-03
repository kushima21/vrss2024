<?php
include ('dbconnect.php');

if (!$con){
    die ("Connection failed:" . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
    <link rel="stylesheet" href="services.css?= <?php echo time();?>">
    <link rel="stylesheet" href="index.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<div class="container">
<div class="wrapper">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="topbar">
        <div class="logo">
            <h2><i class="fa-solid fa-key" style="color: #630303;"></i> VRRS</h2>
        </div>
        <!-- Toggle Button for Mobile -->
        <button id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="reservation.php">Reservation</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
        <div>
            <a href="signup.php" class="button header__cta hide-for-mobile">
                <i class="fa-solid fa-user-plus"></i> Signup
            </a>
        </div>
    </div>
</div>

<style>
    /* Hide nav-links by default on smaller screens */
    .nav-links {
        display: none;
    }

    /* When nav-active class is added, show nav-links */
    .nav-links.nav-active {
        display: block;
    }

    /* Additional styling for responsive design */
    @media (min-width: 768px) {
        .nav-links {
            display: block; /* Show by default on larger screens */
        }
        #menuToggle {
            display: none; /* Hide the toggle button on larger screens */
        }
    }
</style>

<script>
    // JavaScript to toggle navigation menu
    document.getElementById('menuToggle').onclick = function() {
        var nav = document.getElementById('navLinks');
        nav.classList.toggle('nav-active');
    };
</script>

<section class="section">
        <div class="section-header">
            <h1>OUR SERVICES</h1>
        </div>
    <div class="row">
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-car" style="color: #000000;"></i>
                </div>
                <h3>Pick-up Drop-off Service</h3>
                <p>Arrive to any destination in Lanao del Norte with VRS services. Let our professional chauffeur bring you
                to your destination with ease! Simply let us know your pick-up and drop-off points and enjoy a smooth ride.
                </p>
            </div>
        </div>
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-id-card" style="color: #000000;"></i>
                </div>
                <h3>Car Rental With Driver</h3>
                <p>If you want to eliminate the hassle of driving in unfamiliar places, then opt for a car rental service with a driver. Whether you’re traveling <br>
                on a business trip or going on a vacation with your family, a driver will drive you there safely</p>
            </div>
        </div>
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-envelope-open" style="color: #000000;"></i>
                </div>
                <h3>Customer Support</h3>
                <p>Provide customer support via phone, email, or live chat to assist with reservations, inquiries, or issues during the rental process.</p>
            </div>
        </div>
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-globe" style="color: #000000;"></i>
                </div>
                <h3>Online Reservation System</h3>
                <p>Offer an easy-to-use online platform where customers can browse available vans, check rental rates, and make reservations conveniently.</p>
            </div>
        </div>
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-calendar-days" style="color: #000000;"></i>
                </div>
                <h3>Event Transportation Services</h3>
                <p>Provide transportation solutions for events such as weddings, corporate gatherings, concerts, and sports events, including shuttle services and group transportation.</p>
            </div>
        </div>
        <div class="column">
            <div class="card">
                <div class="icon-wrapper">
                <i class="fa-solid fa-van-shuttle" style="color: #000000;"></i>
                </div>
                <h3>Vehicle Delivery</h3>
                <p>Offer a vehicle delivery service where the rented van is delivered directly to the customer's doorstep or desired location, saving time and effort.</p>
            </div>
        </div>
    </div>
</section>
<section>
        <footer>
            <div class="footer_copyrigth">
                &copy; Van_Reservation. All Rights Reserved.
            </div>
        </footer>
</section>
   
    <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
 

</body>
</html>