<?php
session_start(); // Start session to access session variables
include('dbconnect.php'); // Include database connection

// Check if the user is logged in
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM signup WHERE email = ?";
    $stmt = mysqli_prepare($con, $fetchadata);
    mysqli_stmt_bind_param($stmt, 's', $loggedInEmail);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id'];

        // Ensure the user has the required status
        if ($status_id != 2) {
            header("Location: login.php");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

// Handle the reservation cancellation if POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reason']) && isset($_GET['reservation_id'])) {
    $cancel_reason = $_POST['cancel_reason'];
    $reservation_id = $_GET['reservation_id'];
    $cancel_time = date("Y-m-d H:i:s");

    mysqli_begin_transaction($con);

    try {
        // Update the reservation status to 'Cancelled' in the reservation_request table
        $updateReservationQuery = "UPDATE reservation_request 
                                   SET cancel_reason = ?, status = 'Cancelled', payment_status = 'Cancelled',
                                       vehicle_status = 'Cancelled', pickup_date = NULL, return_date = NULL,
                                       cancel_time = ? 
                                   WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $updateReservationQuery);
        mysqli_stmt_bind_param($stmt, 'sss', $cancel_reason, $cancel_time, $reservation_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating reservation status: " . mysqli_error($con));
        }

        // Get the vehicle ID associated with the reservation
        $fetchVehicleIdQuery = "SELECT vehicle_id FROM reservation_request WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $fetchVehicleIdQuery);
        mysqli_stmt_bind_param($stmt, 'i', $reservation_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $vehicle = mysqli_fetch_assoc($result);
        $vehicle_id = $vehicle['vehicle_id'];

        if ($vehicle_id) {
            // Increment the vehicle availability without modifying vehicle status
            $updateAvailabilityQuery = "UPDATE vehicles_tbl SET availability = availability + 1 WHERE id = ?";
            $stmt = mysqli_prepare($con, $updateAvailabilityQuery);
            mysqli_stmt_bind_param($stmt, 'i', $vehicle_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating vehicle availability: " . mysqli_error($con));
            }
        } else {
            throw new Exception("No vehicle found for the reservation.");
        }

        // Commit transaction if all queries succeed
        mysqli_commit($con);
        header("Location: user_main_transaction_history.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction if any query fails
        mysqli_roll_back($con);
        echo "Error: " . $e->getMessage();
    }
}

// 1. Check if the user has any unviewed approved reservations
$checkReservationQuery = "SELECT * FROM reservation_request WHERE user_id = ? AND status = 'Approved' AND viewed_reservation = 0";
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
    <link rel="stylesheet" href="user_main_cancel_transaction_form.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
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
                    <a href="#">
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
                        <li><a href="user_main_transaction_completed.php">Completed</a></li>
                        <li><a href="#">Cancelled</a></li>
                      </ul>
                    </div>
                    <div class="user--reservation--main--container--box">
                        <div class="reservation--container--box">
                            <div class="cancel--header">
                                <a href="user_main_transaction_history.php">Get Back</a>
                                <span>Cancel Reservation</span>
                            </div>
                            <div class="customers--details">
                                <form id="cancelForm" action="" method="post" enctype="multipart/form-data">
                                <label>
                                    <input type="radio" class="cancel-radio" name="cancel_reason"value="Wrong Address"> Wrong Address
                                </label>
                                <br>
                                <label>
                                    <input type="radio" class="cancel-radio" name="cancel_reason"value="Need to Change Pickup Date, Return Date etc."> Need to Change Pickup Date,Return Date, etc.
                                </label>
                                <br>
                                <label>
                                    <input type="radio" class="cancel-radio" name="cancel_reason"value="Wrong Information"> Wrong Information
                                </label>
                                <br>
                                <label>
                                    <input type="radio" class="cancel-radio" name="cancel_reason"value="Not interested anymore"> Not interested anymore
                                </label>
                                <div class="user--cancel--button">
                                    <button type="button" id="submit_cancel" >Confirm Cancellation</button>
                                </div>
                                </form>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('submit_cancel').addEventListener('click', function (e) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to Cancel this Reservation.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Saved!',
                    'Your reservation is successfully Cancelled.',
                    'success'
                ).then(() => {
                    // Submit the form programmatically
                    document.getElementById('cancelForm').submit();
                });
            }
        });
    });
</script>
</body>
</html>