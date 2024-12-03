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

// Handle form submission for updates
if (isset($_POST['update'])) {
    if (isset($_POST['id'])) {  // Check if 'id' is set in the form
        $id = $_POST['id'];
        $fname = mysqli_real_escape_string($con, $_POST['fname']);
        $lname = mysqli_real_escape_string($con, $_POST['lname']);
        $address = mysqli_real_escape_string($con, $_POST['address']);
        $pnum = mysqli_real_escape_string($con, $_POST['pnum']);

        // Check if an image was uploaded
        if ($_FILES['image']['name'] != '') {
            $filename = $_FILES['image']['name'];
            $filetempname = $_FILES['image']['tmp_name'];
            $filesize = $_FILES['image']['size'];
            $fileerror = $_FILES['image']['error'];

            // Image upload validation
            $fileext = explode('.', $filename);
            $filetrueext = strtolower(end($fileext));
            $allowed_ext = ['jpg', 'png', 'jpeg'];

            if (in_array($filetrueext, $allowed_ext)) {
                if ($fileerror === 0) {
                    if ($filesize < 10000000) { // 10 MB limit
                        $filenewname = uniqid('', true) . "." . $filetrueext;
                        $filedestination = 'images/' . $filenewname;

                        // Move the uploaded file and update the database
                        if (move_uploaded_file($filetempname, $filedestination)) {
                            // Update database record with new image
                            $update_query = "UPDATE signup SET fname=?, lname=?, address=?, pnum=?, image=? WHERE id=?";
                            $stmt = mysqli_prepare($con, $update_query);
                            mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $address, $pnum, $filedestination, $id);

                            if (mysqli_stmt_execute($stmt)) {
                                $message = "Record updated successfully!";
                                header("Location: user_personal_information.php");
                                exit();
                            } else {
                                $message = "Error updating record: " . mysqli_error($con);
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            $message = "Error moving uploaded file!";
                        }
                    } else {
                        $message = "File size exceeds limit!";
                    }
                } else {
                    $message = "Error uploading file!";
                }
            } else {
                $message = "Invalid file format!";
            }
        } else {
            // If no image uploaded, update without the image field
            $update_query = "UPDATE signup SET fname=?, lname=?, address=?, pnum=? WHERE id=?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, "ssssi", $fname, $lname, $address, $pnum, $id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Record updated successfully!";
                header("Location: user_personal_information.php");
                exit();
            } else {
                $message = "Error updating record: " . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $message = "No user ID provided!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_dashboard.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information_edit.css?= <?php echo time();?>">
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
<section>
    <div class="profile--modification--main">
        <div class="profile--modification--container">
           <div class="profile--information--leftside--container">
           <div  class="profile--modification--leftside--header">
                    <a href="#"><h1>Profile Modification</h1></a>
                </div>
                <div class="profile--modification--leftside--box">
                    <div class="profile--personal--border">
                        <a href="user_personal_information.php"><h3>Personal Account Information</h3></a>
                    </div>
                    <div class="profile--security--border">
                        <a href="#"><h3>Security Account Information</h3></a>
                    </div>
                </div>
           </div>

           <div class="profile--information--rightside--container">
                <div class="profile--information--details--box">
                    <div class="profile--information--rightside--header">
                        <h1>Personal Account Information Details</h1>
                    </div>
                    <div class="profile--information--rightside--form">
                        <div class="profile--information--account--details">
                        <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                                    <div class="profile--account--header">
                                        <div class="header--form">
                                            <h1>Account Name Information</h1>
                                            
                                            <div class="edit--form--details">
                                                <label for="fname">First Name:</label>
                                                <input type="text" name="fname" value="<?php echo $edit_data['fname']; ?>"><br><br>
                                                
                                                <label for="lname">Last Name:</label>
                                                <input type="text" name="lname" value="<?php echo $edit_data['lname']; ?>"><br><br>
                                                
                                                <label for="address">Address:</label>
                                                <input type="text" name="address" value="<?php echo $edit_data['address']; ?>"><br><br>
                                                
                                                <label for="pnum">Contact Number:</label>
                                                <input type="text" name="pnum" value="<?php echo $edit_data['pnum']; ?>"><br><br>
                                            </div>
                                        </div>
                                        <div class="profile--account--image">
                                            <input type="file" name="image" id="image" accept="image/*">
                                            <img src="<?php echo $edit_data['image']; ?>" alt="User Image">
                                        </div>
                                    </div>

                                    <div class="profile--account--bottom--border">
                                        <div class="profile--account--button">
                                            <button type="submit" name="update" onclick="return confirm('Are you sure you want to save these changes?');">Update Information</button>
                                        </div>
                                    </div>
                                </form>
                        </div>
                </div>
           </div>
        </div>
    </div>
</section>



     
      <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
 
     
      <script src="user.js"></script>
</div>
</body>
</html>