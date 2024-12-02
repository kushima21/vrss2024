<?php
  include('dbconnect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="stylesheet" href="about.css?= <?php echo time();?>">
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
      <div class="about--header--vrss">
        <h1>About VRRS</h1>
    </div>

    <div class="about">
        <div class="col-1">
            <div class="about-card">
                <p>The name Van Reservation is known locally as a safe, and reliable transport service. 
                    The name Van Reservation is known locally as a safe, and reliable transport service.
                    Its Local network which enables client to make a reservation in one location.
                    To be the local PREMIER transport service provider in the car rental industry in the Lanao del Norte.
                    </p>
            </div>
            <div class="about-card">
            
                <p>
                    We offer a wide range  of vehicle to suit your needs, whether you're moving, transporting goods, or simply need a driver for simple vacation.
                    Our company well-maintained locally safe. With flexible rental option and affordable rates, we make it easy for you to get behind the wheel and get on your day.</p>
            </div>

        </div>
        <div class="col-2">
            <img src="img/Toyota-Hiace-1.png" class="van">
        </div>
</div>
    <div class="row2">
        <h1>Our Team</h1>
    </div>
    <div class="about--container--details">

        <div class="column2">
            <div class="card2">
                <div class="img-container">
                    <img src="img/hondrada.png" />
                </div>
                <h3>John Mark Hondrada</h3>
                <p>Programmer</p>
                <div class="icons">
                    <a href="">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-facebook-messenger"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="column2">
            <div class="card2">
                <div class="img-container">
                    <img src="img/queenie.jpg" />
                </div>
                <h3>Queenie Love Puebla</h3>
                <p>Programmer</p>
                <div class="icons">
                    <a href="">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-facebook-messenger"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-github"></i> 
                    </a>
                </div>
            </div>
        </div>
        <div class="column2">
            <div class="card2">
                <div class="img-container">
                    <img src="img/julius.png" />
                </div>
                <h3>Julius Caesar Padrones</h3>
                <p>Programmer</p>
                <div class="icons">
                    <a href="">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-facebook-messenger"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="">
                        <i class="fa-brands fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer_copyrigth">
            &copy; Van_Reservation. All Rights Reserved.
        </div>
    </footer>
    <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="darkmode.js"></script>
</body>
</html>