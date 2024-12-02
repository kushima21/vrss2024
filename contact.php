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
    <title>Contact us</title>
    <link rel="stylesheet" href="contact.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

<div class="header">
            <h1>Contact Us</h1>
        </div>
        <div class="container--contact--box">
        <div class="contact-container">
            <div class="contact">
                <h3 class="heading">Get in Touch</h3>
                <p class="text">We are here for you! How can we help?</p>
                <div class="contact-image">
                <img src="img/contact.svg" alt="">
            </div>
            </div>
            <form action="">
                <div class="input-box">
                    <input type="text" class="input-field" Placeholder="Enter your Name">
                    <input type="text" class="input-field" Placeholder="Your Email">
              
                <div class="textarea">
                    <textarea placeholder="Message"></textarea>
                </div>
                
                <div class="form-button">
                    <button class="btn">Send <i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </form> 
        </div>
</div>
</div>
<section class="map-container">
    <div class="map-content">
                <div class="content">
                    <div class="left-side">
                        <div class="details">
                            <i class="fa-solid fa-location-dot"></i>
                            <div class="topic">Address</div>
                            <div class="text1"> Purok 1, Sto. Niño Village, Baroy</div>
                            <div class="text2">Lanao del Norte</div>
                        </div>
                        <div class="details">
                        <i class="fa-solid fa-phone-volume"></i>
                            <div class="topic">Phone</div>
                            <div class="text1"> +0916 195 9227</div>
                            <div class="text2">+0917 710 2873</div>
                        </div>
                        <div class="details">
                        <i class="fa-brands fa-facebook"></i>
                            <div class="topic">Facebook</div>
                            <div class="text1"> Jeremie Sumaylo</div>
                            <div class="text2">Jaromar Travel and Tours</div>
                        </div>
                    </div>
                    <div class="right-side">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d31606.76001488376!2d123.78427399999998!3d8.014915!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2f1e8a86935e459d%3A0xff2b665b6a22fc6b!2sJaromar%20Travel%20%26%20Tours!5e0!3m2!1sen!2sph!4v1714314780723!5m2!1sen!2sph" width="580" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
    </div>
</section>
<footer>
        <div class="footer_copyrigth">
            &copy; Van_Reservation. All Rights Reserved.
        </div>
    </footer>

    <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <script src="darkmode.js"></script>
</body>
</html>