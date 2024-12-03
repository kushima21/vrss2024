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
        } else {
            // Redirect to login.php if status_id is not 2
            header("Location: login.php");
            exit();
        }
        
    } else {
        // Redirect to login.php if user is not found
        header("Location: login.php");
        exit();
    }
} else {
    // Redirect to login.php if user is not logged in
    header("Location: login.php");
    exit();
}

$reservationData = [ 'customer_name' => '', 'rent_price' => '', 'pickup_date' => '', 'return_date' => '', 'photo' => '', 'payment_status' => '', 'status' => '', 'seating_capacity' => '', 'driver_name' => '', 'driver_contact' => '', 'model' => '', 'vehicle_name' => '', 'cnum' => '', 'driver_contact','counting_days','request_time','reservation_id'];

if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $fetchReservationData = "SELECT * FROM reservation_request WHERE reservation_id = ?";
    $stmt = mysqli_prepare($con, $fetchReservationData);
    mysqli_stmt_bind_param($stmt, "i", $reservation_id);
    mysqli_stmt_execute($stmt);
    $reservation_Result = mysqli_stmt_get_result($stmt);

    if ($reservation_Result) {
        $reservation_Data = mysqli_fetch_assoc($reservation_Result);
    } else {
        die("Error fetching Reservation data: " . mysqli_error($con));
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
    <link rel="stylesheet" href="user_main_transaction_view.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
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
            <div class="user--main--transaction----box">
                <div class="user--main--side--bar--container">
                <div class="account--container">
                    <a href="user_main_transaction_account.php">
                    <div class="side--container">
                      <i class="fa-solid fa-circle-user"></i><h2><?php echo $fname; ?> <?php echo $lname; ?></h2>
                    </div>
                    </a>
                  </div>
                    <div class="account--main--side--bar">
                        <div class="account--main--url--container">
                            <ul>
                              <li><a href="user_main_transaction_account.php"><i class="fas fa-user"></i> My Account</a></li>
                              <li><a href="user_main_transaction_history.php" style="color: darkred;"><i class="fas fa-calendar-alt"></i> My Reservation</a></li>
                              <li><a href="user_transaction_term_condition.php"><i class="fas fa-file-alt"></i> Terms and Conditions</a></li>
                              <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="user--main--right--side--container">
                  <div class="user--right--side--header"><h2>My Reservation</h2></div>
                    <div class="user--url--main--container">
                      <ul>
                        <li><a href="user_main_transaction_all.php">All</a></li>
                        <li><a href="user_main_transaction_history.php">Request </a></li>
                        <li><a href="user_main_transaction_topickup.php">ToPickup</a></li>
                        <li><a href="user_main_transaction_travelling.php">Traveling</a></li>
                        <li><a href="user_main_transaction_completed.php" style="color: darkred;">Completed</a></li>
                        <li><a href="user_main_transaction_cancelled.php">Cancelled</a></li>
                      </ul>
                    </div>
                    <div class="user--reservation--main--container--box">
                        <div class="reservation--container--box">
                            <div class="invoice--head">
                                <p>Invoice ID :<?php echo htmlspecialchars($reservation_Data['reservation_id']); ?></p>
                                <p>Request Time : <?php echo htmlspecialchars($reservation_Data['request_time']); ?></p>
                            </div>
                          <div class="image--container">
                            <img src="<?php echo htmlspecialchars($reservation_Data['photo']); ?>">
                            <div class="vehicles--details--container">
                              <p><?php echo htmlspecialchars($reservation_Data['vehicle_name']); ?></p>
                              <p>Model: <?php echo htmlspecialchars($reservation_Data['model']); ?> </p>
                              <p>Seating Capacity: <?php echo htmlspecialchars($reservation_Data['seating_capacity']); ?></p>
                              <p>Driver Assigned : <?php echo htmlspecialchars($reservation_Data['driver_name']); ?></p>
                              <p>Contact Numbers : <?php echo htmlspecialchars($reservation_Data['driver_contact']); ?></p>
                            </div>
                          </div>
                          <div class="customers--details">
                            <p>Customers Name : <?php echo htmlspecialchars($reservation_Data['customer_name']); ?></p>
                            <p>Address : <?php echo htmlspecialchars($reservation_Data['customer_address']); ?></p>
                            <p>Contact Number: <?php echo htmlspecialchars($reservation_Data['cnum']); ?></p>
                            <p>Pickup Date: <?php echo htmlspecialchars($reservation_Data['pickup_date']); ?></p>
                            <p>Return Date : <?php echo htmlspecialchars($reservation_Data['return_date']); ?></p>
                            <p>Day Rented : <?php echo htmlspecialchars($reservation_Data['counting_days']); ?> Days</p>
                            <p>Reservation Status : <?php echo htmlspecialchars($reservation_Data['status']); ?></p>
                          </div>
                          <div class="user--main--total--payment">
                            <span>Total Payment</span>
                            <p>Php : <?php echo htmlspecialchars($reservation_Data['rent_price']); ?>.00</p>
                            <div class="button--container">
                            <a href="user_main_transaction_completed.php"><button>Get Back</button></a>
                            <a href="user_reservation.php"><button>Reserved Again</button></a>
                          </div>
                          </div>
    
                        </div>
                    </div>
                </div>
            </div>

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