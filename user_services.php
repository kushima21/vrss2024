<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM signup WHERE email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchadata);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data

        // Check if the user has the required status_id
        if ($status_id == 2) {
            // Display or use fetched details as needed
            // Example of using the user details
        } else {
            // Redirect to login.php if status_id is not 2
            header("Location: login.php");
            exit(); // Ensure no further code is executed
        }
        
    } else {
        // Redirect to login.php if user is not found
        header("Location: login.php");
        exit(); // Ensure no further code is executed
    }
} else {
    // Redirect to login.php if user is not logged in
    header("Location: login.php");
    exit(); // Ensure no further code is executed
}
// 1. Check if the user has any unviewed approved reservations
$checkReservationQuery = "SELECT * FROM reservation_request WHERE user_id = ? AND status IN ('Approved', 'Traveling') AND viewed_reservation = 0";
$stmt2 = $con->prepare($checkReservationQuery);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$reservationResult = $stmt2->get_result();
$hasApprovedReservation = mysqli_num_rows($reservationResult) > 0;

// 2. If the user clicks the link, update reservation to mark it as viewed
if (isset($_GET['viewed'])) {
    // Update reservation to mark it as viewed
    $updateReservationQuery = "UPDATE reservation_request SET viewed_reservation = 1 WHERE user_id = ? AND status = 'Approved' AND viewed_reservation = 0";
    $stmt3 = $con->prepare($updateReservationQuery);
    $stmt3->bind_param("i", $id);
    $stmt3->execute();

    // Redirect to the transaction history page to ensure the red dot disappears
    header("Location: user_main_transaction_history.php");
    exit();
}
       
  ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_services.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
<body>

<div class="user--main--container">
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
            <h2 ><i class="fa-solid fa-key" ></i>VRRS</h2>
        </div>
        <div class="nav-container">
            <button class="nav-toggle" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
  </button>
  <div class="nav-links" id="navLinks">
    <ul>
      <li><a href="user_dashboard.php"><i class="fas fa-home"></i> <span class="link-text">Home</span></a></li>
      <li><a href="user_about.php"><i class="fas fa-info-circle"></i> <span class="link-text">About</span></a></li>
      <li><a href="user_reservation.php"><i class="fas fa-calendar-check"></i> <span class="link-text">Reservation</span></a></li>
      <li><a href="user_contact.php"><i class="fas fa-envelope"></i> <span class="link-text">Contact</span></a></li>
      <li><a href="user_services.php"><i class="fas fa-concierge-bell"></i> <span class="link-text">Services</span></a></li>
      <li><a href="user_search.php"><i class="fas fa-magnifying-glass"></i><span class="link-text">Search</span></a></li>    
    </ul>
  </div>
</div>
<div class="mode">
    <div class="moon-sun" id="toggle-switch">
        <i class="fa-solid fa-moon" id="moon"></i>
        <i class="fa-solid fa-sun" id="sun"></i> <!-- Corrected this line -->
    </div>
</div>
                            <div class="profile">
                                <div class="profileBtn">
                                    <span class="profile_text"> <?php echo $fname, $lname; ?></span>
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <ul class="profile_options">
                                    <li class="profile_option">
                                      <i class="fa-solid fa-user"></i>
                                        <a href="user_main_transaction_account.php"><span class="option_profile">Profile Modifications</span></a>
                                    </li>
                                    <li class="profile_option">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <a href="user_main_transaction_history.php?viewed=true">
                                            <span class="option_profile">Transaction History</span>
                                            <?php if ($hasApprovedReservation): ?>
                                                <span class="notification-dot"></span> <!-- Red dot for unviewed approved reservations -->
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li class="profile_option">
                                          <i class="fa-solid fa-shield"></i>
                                          <a href="user_transaction_term_condition.php"><span class="option_profile">Terms and Conditions </span></a>
                                    </li>
                                    <li class="profile_option">
                                      <i class="fa-solid fa-right-from-bracket"></i>
                                        <a href="logout.php"><span class="option_profile">Logout</span></a>
                                    </li>
                                </ul>
                         </div>  
                    </div>     
                </div>              
            
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
    <script src="user.js"></script>
    <script src="darkmode.js"></script>
    <script>
        function toggleNav() {
  const navLinks = document.getElementById("navLinks");
  navLinks.classList.toggle("show");
}
      </script>
</body>
</html>