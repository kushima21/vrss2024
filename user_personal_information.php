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
        $pnum = $row['pnum']; // Assuming status_id is part of the fetched data
        $address = $row['address']; // Assuming status_id is part of the fetched data
        $image = $row['image']; // Assuming status_id is part of the fetched data

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

if (isset($_GET['id'])) {
    // Sanitize and validate `id` parameter
    $id = intval($_GET['id']); // Convert to integer to prevent SQL injection

    $user_query = "SELECT * FROM signup WHERE id = ?";
    $stmt = $con->prepare($user_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $userdata = $user_result->fetch_assoc();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_dashboard.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_personal_information.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                <h2><i class="fa-solid fa-key" ></i>VRRS</h2>
            </div>
            <div class="nav-links" id="navLinks">
                <ul>
                    <li><a href="user_dashboard.php">Home</a></li>
                    <li><a href="user_about.php">About</a></li>
                    <li><a href="user_reservation.php">Reservation</a></li>
                    <li><a href="user_contact.php">Contact</a></li>
                    <li><a href="user_services.php">Services</a></li>
                    <li><a href="user_search.php"> <i class="fa-solid fa-magnifying-glass"></i></a></li>
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
                                    <span class="profile_text"> <?php echo $fname, $lname; ?></span>
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <ul class="profile_options">
                                    <li class="profile_option">
                                      <i class="fa-solid fa-user"></i>
                                        <a href="user_main_transaction_history.php"><span class="option_profile">Profile Modifications</span></a>
                                    </li>
                                    <li class="profile_option">
                                          <i class="fa-solid fa-clock-rotate-left"></i>
                                          <a href="user_transaction_history.php"><span class="option_profile">Transaction History </span></a>
                                    </li>
                                    <li class="profile_option">
                                        <i class="fa-solid fa-shield"></i>
                                        <a href="#"></a><span class="option_profile">Terms & Policies</span></a>
                                    </li>
                                    <li class="profile_option">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <a href="#"></a><span class="option_profile">Help Center</span></a>
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
           <div  class="profile--modification--leftside--header">
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
                    <div class="profile--information--rightside--header">
                        <h1>Personal Account Information Details</h1>
                    </div>
                    <div class="profile--information--rightside--form">
                        <div class="profile--information--account--details">
                            <div class="profile--account--header"><h1>Account Name Information</h1></div>
                            <div class="profile--account--header--details">
                                <h1>Full Name</h1>
                                <p><?php echo ($fname); ?> <?php echo ($lname); ?></p>
                            </div>
                            <div class="profile--account--header--details">
                                <h1>Contact Number</h1>
                                <p><?php echo ($pnum); ?></p>
                            </div>
                            <div class="profile--account--header--details">
                                <h1>Address Information</h1>
                                <p><?php echo ($address); ?></p>
                            </div>
                            <div class="profile--account--header--details">
                                <h1>Email Address</h1>
                                    <p><?php echo ($email); ?></p>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <div class="profile--information--button">
                                <a href="user_personal_information_edit.php?edit_id=<?php echo $id; ?>"><button type="submit">Change Information</button></a>
                                <a href="user_dashboard.php"><button type="submit">Get back</button></a>
                            </div>
                        </div>
                        
                        <div class="profile--information--image"><img src="<?php echo ($image); ?>"></div>
                    </div>
                </div>
           </div>
        </div>
    </div>
</section>



     
      <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
 
      <script src="darkmode.js"></script>
      <script src="admin.js"></script>
</div>
</body>
</html>