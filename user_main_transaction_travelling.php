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
if (isset($_GET['reservation_id'])) {
    $reservation_id_id = $_GET['reservation_id'];
    $reservation_query = "SELECT * FROM reservation_request WHERE reservation_id = $reservation_id";
    $reservation_result = mysqli_query($con, $reservation_query_query);
    $reservation_data = mysqli_fetch_assoc($reservation_result);
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
    <link rel="stylesheet" href="user_main_transaction_history.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                        <li><a href="user_main_transaction_travelling.php" style="color: darkred;">Traveling</a></li>
                        <li><a href="user_main_transaction_completed.php">Completed</a></li>
                        <li><a href="user_main_transaction_cancelled.php">Cancelled</a></li>
                      </ul>
                    </div>
                    <div class="user--reservation--main--container--box">
                    <?php
                    // Prepared statement for reservation request
                    $stmt = $con->prepare("SELECT * FROM reservation_request WHERE email = ? AND status = 'Traveling'");
                    $stmt->bind_param("s", $loggedInEmail);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $reservation_id = $row['reservation_id'];
                            $vehicle_name = $row['vehicle_name'];
                            $photo = $row['photo'];
                            $status = $row['status'];
                            $rent_price = $row['rent_price'];
                            $rent_per_day = $row['rent_per_day'];
                            $counting_days = $row['counting_days'];
                            $model = $row['model'];
                            $seating_capacity= $row['seating_capacity'];
                            $driver_name = $row['driver_name'];
                    ?> 
                        <div class="reservation--container--box">
                          <div class="image--container">
                            <img src="<?php echo $photo; ?>">
                            <div class="vehicles--details--container">
                              <p><?php echo $vehicle_name; ?></p>
                              <p>Model: <?php echo $model; ?> </p>
                              <p>Seating Capacity: <?php echo $seating_capacity; ?></p>
                              <p>Day per charge : <?php echo $rent_per_day; ?>.00</p>
                              <p>Driver Assigned : <?php echo $driver_name; ?></p>
                              <p>Reservation Status : <?php echo $status; ?></p>
                            </div>
                          </div>
                          <div class="button--container">
                          <a href="user_main_transaction_travelling_view.php?reservation_id=<?php echo $reservation_id; ?>"><button>View Reservation</button></a>
                            <a href="user_main_transaction_print.php?reservation_id=<?php echo $reservation_id; ?>"><button>Print Invoice</button></a>
                          </div>
                        </div>
                        <?php
                        }
                    } else {
                        echo "<p style='text-align: center;'>No Reservations Request.</p>";
                    }
                    $stmt->close();
                    ?>
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