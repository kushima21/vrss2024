<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection

// Check if a user is logged in based on session variables
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM signup WHERE email = ?";
    $stmt = $con->prepare($fetchadata);
    $stmt->bind_param("s", $loggedInEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data

        // Always display the user dashboard for users
        if ($status_id == 2) {
            // Code to display the user dashboard goes here
           
            // Further code to render the user dashboard can be placed here
        } else if ($status_id == 1) {
            // If the status_id indicates admin, redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Handle any other roles, if necessary
            echo "Access denied. You are not authorized to view this page.";
        }
    } else {
        // If no user data is found, redirect to login.php
        header("Location: login.php");
        exit();
    }
} else {
    // If no email session is set, redirect to login.php
    header("Location: login.php");
    exit();
}

$reservationData = [
    'reservation_id' => '',
    'customer_name' => '',
    'customer_address' => '',
    'request_time' => '',
    'pickup_date' => '',
    'return_date' => '',
    'photo' => '',
    'vehicle_name' => '',
    'driver_name' => '',
    'payment_status' => '',
    'seating_capacity' => '',
    'rent_per_day' => '',
    'rent_price' => '',
    'driver_address' => '',
    'driver_contact' => '',
    'counting_days' => '',
    'model' => '',
    'registration_plate' => '',
    'cnum' => ''
];

if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];

    // Validate reservation_id to ensure it's a number
    if (is_numeric($reservation_id)) {
        $fetchReservationData = "SELECT * FROM reservation_request WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $fetchReservationData);
        mysqli_stmt_bind_param($stmt, "i", $reservation_id); // Use 'i' for integer parameter
        mysqli_stmt_execute($stmt);
        $reservationResult = mysqli_stmt_get_result($stmt);

        if ($reservationResult) {
            $reservationData = mysqli_fetch_assoc($reservationResult);
        } else {
            die("Error fetching Reservation data: " . mysqli_error($con));
        }
    } else {
        die("Invalid reservation ID."); // Handle case where reservation_id is not a valid number
    }
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
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="reservation.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_invoice.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">

    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <h2><i class="fa-solid fa-key"></i>VRRS</h2>
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
                                          <a href="user_main_transaction_history.php"><span class="option_profile">Transaction History </span></a>
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
            <div class="user--invoice--main--container">
                <div class="user--invoice--main--container--box">
                    <div class="user--invoice--container--box">
                        <div class="user--invoice--container--header">
                            <h2><i class="fa-solid fa-key" ></i>VRRS</h2>
                            <h3>Invoice No. <?php echo htmlspecialchars($reservationData['reservation_id']); ?><h3>
                        </div>
                        <div class="user--invoice--details--main--container">
                            <div class="user--invoice--vehicles--main--container">
                                <div class="user--invoice--vehicles--header">
                                    <h2>Rental Description :</h2>
                                </div>
                                <div class="user--invoice--vehicles--container--box">
                                    <div class="user--invoice--vehicles--image--container">
                                        <img src="<?php echo htmlspecialchars($reservationData['photo']); ?>">
                                    </div>
                                    <div class="user--invoice--vehicles--details--form">
                                        <h2><?php echo htmlspecialchars($reservationData['vehicle_name']); ?></h2>
                                        <p>Model : <?php echo htmlspecialchars($reservationData['model']); ?></p>
                                        <p>Seating Capacity: <?php echo htmlspecialchars($reservationData['seating_capacity']); ?></p>
                                        <p>Rent Per Day Charge : <?php echo htmlspecialchars($reservationData['rent_per_day']); ?>.00 </p>
                                        <p>Registration Plate :<?php echo htmlspecialchars($reservationData['registration_plate']); ?></p>
                                        <p>Color</p>
                                    </div>
                                </div>
                                <div class="user--invoice--drivers--main--container">
                                    <h2>Drivers Details</h2>
                                    <p>Driver Assigned: <?php echo htmlspecialchars($reservationData['driver_name']); ?></p>
                                    <p>Contact Number: <?php echo htmlspecialchars($reservationData['driver_contact']); ?></p>
                                    <p>Address: <?php echo htmlspecialchars($reservationData['driver_address']); ?></p>
                                </div>
                            </div>
                            <div class="user--invoice--customers--main--container">
                                <div class="user--invoice--customers--form--container">
                                    <div class="user--invoice--customer--header">
                                        <span>Customers Details</span>
                                        <span>Request Time : <?php echo htmlspecialchars($reservationData['request_time']); ?></span>
                                    </div>
                                    <p>Customers Name : <?php echo htmlspecialchars($reservationData['customer_name']); ?></p>
                                    <p>Contact Number : <?php echo htmlspecialchars($reservationData['cnum']); ?></p>
                                    <p>Customers Address : <?php echo htmlspecialchars($reservationData['customer_address']); ?></p>
                                    <p>PickUp Date : <?php echo htmlspecialchars($reservationData['pickup_date']); ?></p>
                                    <p>Return Date : <?php echo htmlspecialchars($reservationData['return_date']); ?></p>
                                    <p>Rented Days : <?php echo htmlspecialchars($reservationData['counting_days']); ?> Days</p>
                                    <p>Payment Status : <?php echo htmlspecialchars($reservationData['payment_status']); ?></p>
                                </div>
                                <div class="term--condition">
                                    <span>VRSS Terms & Conditions</span>
                                    <p>* Payments should be made after the travel.</p>
                                    <p>* Payments should be in cash and will be paid though the driver in charge.</p>
                                    <p>* We reserve the right to modify these payment terms at any time. 
                                    <br>Notice of changes will be provided prior to the travel date.</p>
                                </div>
                                <div class="user--invoice--payment--main--container">
                                    <div class="total--payment"><h3>Total Php : <?php echo htmlspecialchars($reservationData['rent_price']); ?>.00</h3></div>
                                    <div class="invoice--button"><a href="user_main_transaction_history.php"><button>View Reservation</button></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
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