<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection

// Check if user is logged in
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Fetch user details based on logged-in email
    $fetchQuery = "SELECT * FROM signup WHERE email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchQuery);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data
        $address = $row['address']; // Assuming address is part of the fetched data

        // Check user status
        if ($status_id != 2) {
            header("Location: login.php");
            exit(); // Ensure no further code is executed
        }
    } else {
        header("Location: login.php");
        exit(); // Ensure no further code is executed
    }
} else {
    header("Location: login.php");
    exit(); // Ensure no further code is executed
}

// Initialize form data
$edit_data = ['fname' => '', 'lname' => '', 'address' => '', 'pnum' => '', 'image' => '', 'id' => ''];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $pnum = mysqli_real_escape_string($con, $_POST['pnum']);

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $filename = $_FILES['image']['name'];
        $filetempname = $_FILES['image']['tmp_name'];
        $filesize = $_FILES['image']['size'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array($fileext, $allowed_ext) && $filesize < 10000000) {
            $filenewname = uniqid() . '.' . $fileext;
            $filedestination = 'images/' . $filenewname;

            if (move_uploaded_file($filetempname, $filedestination)) {
                // Update record with the new image
                $update_query = "UPDATE signup SET fname=?, lname=?, address=?, pnum=?, image=? WHERE id=?";
                $stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $address, $pnum, $filedestination, $id);
            }
        } else {
            echo "Invalid file or size too large!";
        }
    } else {
        // Update record without a new image
        $update_query = "UPDATE signup SET fname=?, lname=?, address=?, pnum=? WHERE id=?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $fname, $lname, $address, $pnum, $id);
    }

    // Execute the query
    if ($stmt && mysqli_stmt_execute($stmt)) {
        header("Location: user_main_transaction_account.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
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
    <link rel="stylesheet" href="user_main_transaction_accountEdit.css?= <?php echo time();?>">
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
            <h2><i class="fa-solid fa-key" ></i>VRRS</h2>
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
<div class="profile">
                                <div class="profileBtn">
                                    <span class="profile_text"> <?php echo $fname, $lname; ?></span>
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <ul class="profile_options">
                                    <li class="profile_option">
                                      <i class="fa-solid fa-user"></i>
                                        <a href="user_profile_modification.php"><span class="option_profile">Profile Modifications</span></a>
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
                              <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="user--main--right--side--container">
                  <div class="user--right--side--header"><h2>My Personal Account</h2></div>
                    <div class="user--url--main--container">
                        <div class="personal--information--container">
                            <a href="user_main_transaction_account.php" style="color: darkred;">Personal Account Information</a>
                        </div>
                        <div class="security--information--container">
                            <a href="user_main_transaction_security.php">Security Account Information</a>
                        </div>
                    </div>
                    <div class="user--reservation--main--container--box">
                        <div class="reservation--container--box">
                            <form method="POST" id="updateForm" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                                <div class="user--information--container">
                                    <label for="fname">First Name:</label>
                                    <br>
                                    <input type="text" name="fname" value="<?php echo $edit_data['fname']; ?>">
                                    <br>
                                    <label for="lname">Last Name:</label>
                                    <br>
                                    <input type="text" name="lname" value="<?php echo $edit_data['lname']; ?>">
                                    <br>
                                    <label for="address">Address:</label>
                                    <br>
                                    <input type="text" name="address" value="<?php echo $edit_data['address']; ?>">
                                    <br>
                                    <label for="pnum">Contact Number:</label>
                                    <br>
                                    <input type="text" name="pnum" value="<?php echo $edit_data['pnum']; ?>"><br><br>
                                </div>
                                <div class="button--edit--container">
                                    <button type="button" id="updateBtn">Update Information</button>
                                    <a href="user_main_transaction_account.php"><button type="button">Get back</button></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    </div>
                </div>
            </div>
            <script>
    document.getElementById('updateBtn').addEventListener('click', function (e) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to save these changes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Saved!',
                    'Your information has been updated.',
                    'success'
                ).then(() => {
                    // Submit the form programmatically
                    document.getElementById('updateForm').submit();
                });
            }
        });
    });
</script>
<script src="user.js"></script>
<script src="darkmode.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleNav() {
const navLinks = document.getElementById("navLinks");
navLinks.classList.toggle("show");
}
</script>
</body>
</html>