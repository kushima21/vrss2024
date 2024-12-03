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

// Update email and password
if (isset($_POST['submit'])) {
    $new_email = $_POST['email'];
    $new_password = ($_POST['password']); // Hash the new password

    $update_query = "UPDATE signup SET email = ?, password = ? WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("ssi", $new_email, $new_password, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Account information updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating account information.');</script>";
    }
    $stmt->close();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_dashboard.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_security_account.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="logo">
            <h2><i class="fa-solid fa-key" style="color: #630303;"></i>VRRS</h2>
        </div>
        <div class="nav-links" id="navLinks">
            <ul>
                <li><a href="user_dashboard.php">Home</a></li>
                <li><a href="user_about.php">About</a></li>
                <li><a href="user_reservation.php">Reservation</a></li>
                <li><a href="user_contact.php">Contact</a></li>
                <li><a href="user_services.php">Services</a></li>
                <li><a href="user_search.php"><i class="fa-solid fa-magnifying-glass"></i></a></li>
                <li><a href="#"><i class="fa-solid fa-bell"></i></a></li>
            </ul>
        </div>  
        <div class="mode">
    <div class="moon-sun" id="toggle-switch">
        <i class="fa-solid fa-moon" id="moon"></i>
        <i class="fa-solid fa-sun" id="sun"></i> <!-- Corrected this line -->
</div>   
        <div class="profile">
            <div class="profileBtn">
                <span class="profile_text"><?php echo $fname . ' ' . $lname; ?></span>
                <i class="fa-solid fa-circle-user"></i>
            </div>
            <ul class="profile_options">
                <li class="profile_option">
                    <i class="fa-solid fa-user"></i>
                    <a href="user_profile_modification.php"><span class="option_profile">Profile Modifications</span></a>
                </li>
                <li class="profile_option">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <a href="user_transaction_history.php"><span class="option_profile">Transaction History</span></a>
                </li>
                <li class="profile_option">
                    <i class="fa-solid fa-shield"></i>
                    <a href="#"><span class="option_profile">Terms & Policies</span></a>
                </li>
                <li class="profile_option">
                    <i class="fa-solid fa-circle-info"></i>
                    <a href="#"><span class="option_profile">Help Center</span></a>
                </li>
                <li class="profile_option">
                    <i class="fa-solid fa-inbox"></i>
                    <a href="user_inbox.php"><span class="option_profile">Inbox</span></a>
                </li>
                <li class="profile_option">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <a href="logout.php"><span class="option_profile">Logout</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>

<section>
    <div class="profile--modification--main">
        <div class="profile--modification--container">
            <div class="profile--information--leftside--container">
                <div class="profile--modification--leftside--header">
                    <a href="user_profile_modification.php"><h1>Profile Modification</h1></a>
                </div>
                <div class="profile--modification--leftside--box">
                    <div class="profile--personal--border">
                        <a href="user_personal_information.php"><h3>Personal Account Information</h3></a>
                    </div>
                    <div class="profile--security--border">
                        <a href="user_security_account.php"><h3>Security Account Information</h3></a>
                    </div>
                </div>
            </div>

            <div class="profile--information--rightside--container">
                <div class="profile--information--details--box">
                    <div class="user--security--account--container">
                        <div class="user--security--account--header--border">
                            <h1>Security Account Information</h1>
                        </div>
                        <div class="user--security--account--details--border">
                            <div class="user--account--details--header">
                                <h1>New Email Address</h1>
                            </div>
                            <form id="updateForm" method="POST" enctype="multipart/form-data">
                                <div class="user--account--email--details">
                                    <input type="email" id="email" name="email" placeholder="Email Address" required>
                                </div>
                                <div class="user--account--details--header">
                                    <h1>New Password</h1>
                                </div>
                                <div class="user--account--email--details">
                                    <input type="password"  name="password" placeholder="New Password" required>
                                </div>
                                <div class="user--account--button--border">
                                <div class="profile--account--button"><button type="submit" id="submit" name="submit" onclick="return confirm('Are you sure you want to save these changes?');">Change Information</button></div>
</div>
</form>
<script>
function confirmSubmit(submit) {
    submit.preventDefault(); // Prevent the form from submitting immediately
    swal({
        title: "Are you sure?",
        text: "Once saved, your information will be updated!",
        icon: "warning",
        buttons: {
            cancel: "Cancel",
            confirm: {
                text: "Save",
                value: true,
                visible: true,
                className: "confirm"
            }
        }
    }).then((isConfirm) => {
        if (isConfirm) {
            // Automatically submit the form
            document.getElementById("updateForm").submit();
        } else {
            // User clicked cancel, do nothing
            swal("Cancelled", "Your information was not changed.", "error");
        }
    });
}
</script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8oUuL59K44qFZtLrrmZgw8K2sDR7b1z6KmFsoOWMNfjp10qD9uJibZqaS8Zz3nZKe4tvDQsIbDTVEzUFAg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="user_security_account.js"></script>
<script src="user.js"></script>

</body>
</html>
