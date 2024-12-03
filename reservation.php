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
    <title>Document</title>
    <link rel="stylesheet" href="index.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="reservation.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="reservation.js"></script>
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


<section class="home" id="home">
    <h1 class="home-parallax" data-speed="-2">Find Your Van</h1>
    <a href="login.php" class="btn home-parallax" data-speed="-7">Find Your Van</a>
</section>
<div class="services_container" id="services-1">
            <?php
            $fetchadata = "SELECT * FROM vehicles_tbl";
            $result = mysqli_query($con, $fetchadata);
            while ($row = mysqli_fetch_assoc($result)){
                $id = $row['id'];
                $model = $row['model'];
                $vehicle_name = $row['vehicle_name'];
                $color = $row ['color'];
                $registration_plate = $row['registration_plate'];
                $rent_price = $row['rent_price'];
                $transmission = $row['transmission'];
                $main_image = $row['main_image'];
                $vehicle_name = $row['vehicle_name'];
                $driver_name = $row['driver_name'];
                ?>
               <div class="reserve-container">
                    <div class="front-face">
                            <img  src="<?php echo $main_image; ?>" alt="">
                        <div class="cover-name">
                            <h3 id="vehicleName_<?php echo $id; ?>"><?php echo $vehicle_name; ?></h3>
                            <h1 id="modelName_<?php echo $id; ?>"> <?php echo $model; ?><hr></h1>
                            <h1 id="modelName_<?php echo $id; ?>">PHP:<?php echo $rent_price; ?></h1>
                            <h3>Status:<?php echo $vehicle_name; ?></h3>
                        </div>
                        <div class="services_details">
                            <a href="login.php" class="detailsBtn"> More Details</a>
                        </div>
                    </div>
                    <div class="back-face">
                        <h2>Specifications</h2>
                        <h3>Model:<?php echo $model; ?></h3>
                        <h3>Vehicle Name:<?php echo $vehicle_name?></h3>
                        <h3>Color:<?php echo $color; ?></h3>
                        <h3>Registration Plate:<?php echo $registration_plate; ?></h3>
                        <h3>Transmission:<?php echo $transmission; ?></h3>
                        <h3>Driver Assigned: <?php echo $driver_name; ?></h3>
                    <div class="services_details">
                            <a href="login.php" class="reserveBtn"> Reserve Now </a>
                        </div>
                    </div>
               </div>
            <?php }?>
        </div>


    <section>
        <div class="description1">
            <p>We offer a wide range  of vehicle to suit your needs, whether you're moving, transporting <br> goods, or simply need a driver for simple vacation.</p>
            <p>Our company well-maintained locally safe. With flexible rental option and    affordable<br>rates, we make it easy for you to get behind the wheel and get on your day.</p>
        </div>

    </section>


    <footer>
        <div class="footer_copyrigth">
            &copy; VRRSRentalReservation. All Rights Reserved.
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html> 