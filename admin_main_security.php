<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection
if (isset($_POST['submit'])) {

    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $image = mysqli_real_escape_string($con, $_POST['image']);
    $admin_email = mysqli_real_escape_string($con, $_POST['admin_email']);
    $pnum = mysqli_real_escape_string($con, $_POST['pnum']);
    $password =  $_POST['password'];

    // By default, set the role to 'user'
   
    $defaultStatus = 1;

    $savedata = "INSERT INTO admin_signup (id, fname, lname, address, image, admin_email, pnum, password, status_id)
                VALUES ('','$fname', '$lname', '$address', '$image', '$admin_email','$pnum', '$password', '$defaultStatus')"; 

    if (mysqli_query($con, $savedata)) {
        echo '<script>alert("Please Login Credentials!");</script>';
    } else {
        // Log the error or provide a user-friendly message
        echo "Error creating account: " . mysqli_error($con);
    }
}

if (isset($_SESSION['admin_email'])) {
    $loggedInEmail = $_SESSION['admin_email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM admin_signup WHERE admin_email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchadata);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $admin_email = $row['admin_email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data
        $pnum = $row['pnum']; // Assuming status_id is part of the fetched data
        $address = $row['address']; // Assuming status_id is part of the fetched data
        $image = $row['image']; // Assuming status_id is part of the fetched data
        $password = $row['password'];

        // Check if the user has the required status_id
        if ($status_id == 1) {
            // Display or use fetched details as needed
            // Example of using the user details
        } else {
            // Redirect to login.php if status_id is not 2
            header("Location: admin_login.php");
            exit(); // Ensure no further code is executed
        }
        
    } else {
        // Redirect to login.php if user is not found
        header("Location: admin_login.php");
        exit(); // Ensure no further code is executed
    }
} else {
    // Redirect to login.php if user is not logged in
    header("Location: admin_login.php");
    exit(); // Ensure no further code is executed
}

if (isset($_GET['id'])) {
    // Sanitize and validate `id` parameter
    $id = intval($_GET['id']); // Convert to integer to prevent SQL injection

    $user_query = "SELECT * FROM admin_signup WHERE id = ?";
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="admin_main_settings.css?= <?php echo time(); ?>">
        <link rel="stylesheet" href="admin_main_security.css?= <?php echo time(); ?>">
        <link rel="stylesheet" href="admin_topickup.css?= <?php echo time(); ?>">
        <link rel="stylesheet" href="admin_main_vehicles.css?= <?php echo time();?>">
        <title>Admin Dashboard</title>
    </head>
    <body>
    <div class="container">
        <div class="topbar">
            <div class="logo">
                <h2>VRRS</h2>
            </div>
            <div class="search">
                <input type="text" id="search" placeholder="search here">
                <label for="search"><i class="fa-solid fa-magnifying-glass"></i></label>
            </div>
            <i class="fa-solid fa-bell"></i>
            <div class="user">
                <img src="img/admin.png" alt="">
            </div>
        </div>

        <div class="sidebar">
                <ul> 
                    <li class="key">
                        <a href="">
                            <img src="img/car-key.png" alt="">
                        </a>
                    </li>
                    <li>
                        <a href="admin_dashboard.php">
                            <h1><i class="fa-solid fa-house"></i></h1>
                            <span class="nav-item">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_reservation.php">
                            <h1><i class="fa-solid fa-key"></i></h1>
                            <span class="nav-item">Reservation</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="admin_vehicles.php">
                            <h1><i class="fa-solid fa-car"></i></h1>
                            <span class="nav-item">Vehicles Section</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_monitoring.php">
                            <h1><i class="fa-solid fa-desktop"></i></h1>
                            <span class="nav-item">Monitoring</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_customers.php">
                            <h1><i class="fa-solid fa-users"></i></h1>
                            <span class="nav-item">Customer Section</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_drivers.php">
                            <h1><i class="fa-solid fa-id-card"></i></h1>
                            <span class="nav-item">Driver Section</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_reports.php">
                            <h1><i class="fa-solid fa-chart-simple"></i></h1>
                            <span class="nav-item">Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_main_settings.php">    
                            <h1><i class="fa-solid fa-wrench"></i></h1>
                            <span class="nav-item">Settings</span>
                        </a>
                    </li>
                </ul>
                <div class="bottom-content">
                    <ul>
                        <li>
                            <a href="admin_logout.php">    
                                <h1><i class="fa-solid fa-right-from-bracket"></i></h1>
                                <span class="nav-item">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
                <div class="admin--main--vehicle--container">
                <div class="admin--main--vehicles--box">
                    <div class="admin--vehicles--header--back--box">
                        <div class="admin--vehicles--header--back">
                            <h1>Security</h1>
                        </div>
                        <div class="admin--vehicles--header--url">
                            <div class="admin--url--button--container">
                                <ul>
                                    <li><a href="admin_security_register.php"><button>Add New Admin</button></a></li>
                                    <li><a href="admin_account_settings.php"><button>My Profile</button></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                        <div class="user--security--main--container--box">
                            <div class="security--container--box">
                                <div class="user--security--container">
                                    <span>Current Email</span>
                                    <div class="box"><p><?php echo ($admin_email); ?></p></div>

                                    <span>Current Password</span>
                                    <div class="box"> <p><?php echo str_repeat('*', strlen($password)); ?></p></div>
                                </div>
                                <div class="button--edit--container">
                                    <a href="admin_main_security_view.php?edit_id=<?php echo $id; ?>"><button>Change Information</button></a>
                                    <a href="admin_main_settings.php"><button>Get Back</button></a>
                                </div>
                            </div>
                        </div>
        <style>
            .bell i {
                position: relative;
                font-size: 20px;
                cursor: pointer;
                color: black;
            }
        </style>
        <script src="admin_dashboard.js"></script>
    </body>
    </html>