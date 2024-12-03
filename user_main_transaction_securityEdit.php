<?php
session_start();
include('dbconnect.php');

if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email'];

    // Fetch logged-in user details
    $fetchadata = "SELECT * FROM signup WHERE email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchadata);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id'];
        $pnum = $row['pnum'];
        $address = $row['address'];
        $hashed_password = $row['password']; // Retrieve hashed password
}
}

// Initialize form data
$edit_data = ['email' => '', 'password' => ''];

// Fetch data for editing
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM signup WHERE id = ?";

    $stmt = mysqli_prepare($con, $edit_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $edit_data = mysqli_fetch_assoc($result);
        } else {
            $message = "No data found for the given ID.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Error preparing the edit query: " . mysqli_error($con);
    }
}

if (isset($_POST['update'])) {
    if (isset($_POST['id'])) {  // Check if 'id' is set in the form
        $id = $_POST['id'];
        $fieldsToUpdate = [];
        $values = [];

        // Check which fields are provided and add them to the update query
        if (!empty($_POST['email'])) {
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $fieldsToUpdate[] = "email = ?";
            $values[] = $email;
        }

        if (!empty($_POST['password'])) {
            $password = mysqli_real_escape_string($con, $_POST['password']);
            $fieldsToUpdate[] = "password = ?";
            $values[] = $password;
        }

        if (!empty($fieldsToUpdate)) {
            // Begin transaction for atomic updates
            mysqli_begin_transaction($con);
            try {
                // Update the signup table dynamically
                $update_query = "UPDATE signup SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
                $stmt = mysqli_prepare($con, $update_query);
                
                // Add the id at the end of the values array
                $values[] = $id;

                // Bind the parameters dynamically
                $types = str_repeat("s", count($fieldsToUpdate)) . "i";
                mysqli_stmt_bind_param($stmt, $types, ...$values);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error updating signup table: " . mysqli_error($con));
                }
                mysqli_stmt_close($stmt);

                // If the email is being updated, update the reservation_request table
                if (in_array("email = ?", $fieldsToUpdate)) {
                    $update_reservation_query = "UPDATE reservation_request SET email = ? WHERE user_id = ?";
                    $stmt = mysqli_prepare($con, $update_reservation_query);
                    mysqli_stmt_bind_param($stmt, "si", $email, $id);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error updating reservation_request table: " . mysqli_error($con));
                    }
                    mysqli_stmt_close($stmt);
                }

                // Commit transaction
                mysqli_commit($con);
                $message = "Record updated successfully!";
                header("Location: user_main_transaction_security.php");
                exit();
            } catch (Exception $e) {
                // Rollback transaction in case of any error
                mysqli_rollback($con);
                $message = $e->getMessage();
            }
        } else {
            $message = "No fields to update!";
        }
    } else {
        $message = "No user ID provided!";
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
    <link rel="stylesheet" href="user_main_transaction_security.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
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
            <h2 ><i class="fa-solid fa-key"></i>VRRS</h2>
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
                              <li><a href="user_main_transaction_account.php" style="color: darkred;"><i class="fas fa-user"></i> My Account</a></li>
                              <li><a href="user_main_transaction_history.php"><i class="fas fa-calendar-alt"></i> My Reservation</a></li>
                              <li><a href="user_transaction_term_condition.php"><i class="fas fa-file-alt"></i> Terms and Conditions</a></li>
                             
                              <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="user--main--right--side--container">
                  <div class="user--right--side--header"><h2>Security Account Information</h2></div>
                    <div class="user--url--main--container">
                        <div class="personal--information--container">
                            <a href="user_main_transaction_account.php">Personal Account Information</a>
                        </div>
                        <div class="security--information--container">
                            <a href="user_main_transaction_security.php" style="color: darkred;">Security Account Information</a>
                        </div>
                    </div>
                    <div class="user--reservation--main--container--box">
                        <div class="reservation--container--box">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                                <div class="user--information--container">
                                <label for="email">New Email Address:</label><br>
                                <input type="email" id="email" name="email" value="<?php echo $edit_data['email']; ?>"  placeholder="Email Address" required>
                                <br>
                                <label for="password">New Password:</label><br>
                                <input type="password"  name="password" value="" placeholder="New Password" required>
                                </div>
                                <div class="button--edit--container">
                                    <button type="submit" name="update" onclick="return confirm('Are you sure you want to save these changes?');">Update Information</button>
                                    <a href="user_main_transaction_security.php"><button type="button">Get back</button></a>
                                </div>
                            </form>
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