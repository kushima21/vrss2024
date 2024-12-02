<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection

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
        $status_id = $row['status_id'];
        $pnum = $row['pnum'];   
        $address = $row['address'];
        $image = $row['image'];
        $password = $row['password'];
    }
}

// Update account details
if (isset($_POST['update'])) {
    $fieldsToUpdate = [];
    $params = [];
    $types = "";

    // Check each field to see if it was updated
    if (!empty($_POST['admin_email']) && $_POST['admin_email'] !== $admin_email) {
        $fieldsToUpdate[] = "admin_email = ?";
        $params[] = $_POST['admin_email'];
        $types .= "s";
    }
    if (!empty($_POST['password']) && $_POST['password'] !== $password) {
        $fieldsToUpdate[] = "password = ?";
        $params[] = $_POST['password'];
        $types .= "s";
    }
    // Add more fields as needed for fname, lname, pnum, address, etc.

    // If there are fields to update, proceed with the query
    if (!empty($fieldsToUpdate)) {
        $update_query = "UPDATE admin_signup SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
        $stmt = $con->prepare($update_query);

        // Bind the parameters and the ID
        $params[] = $id;
        $types .= "i";
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo "<script>alert('Account information updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating account information.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('No changes detected.');</script>";
    }
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
                <link rel="stylesheet" href="admin_main_security_view.css?= <?php echo time(); ?>">
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

                    <div class="admin--main--security--container">
                        <div class="admin--security--container--box">
                            <div class="admin--security--header--box">
                                <a href="admin_main_security.php"><i class="fas fa-arrow-left"></i> Get Back</a>
                                <h1>Security</h1>
                            </div>

                            <div class="admin--security--container--box--details">
                                <div class="admin--security--box--form--details">
                                <form id="" method="POST" enctype="multipart/form-data">
                                    <div class="admin--form--email">
                                        <div class="admin--form--header"><p>Change New Email</p></div>
                                        <div class="email--update"><input type="email" id="admin_email" name="admin_email" placeholder="Email Address" ></div>
                                    </div>
                                    <div class="admin--form--email">
                                        <div class="admin--form--header"><p>Change New Password</p></div>
                                        <div class="password--update"><input type="password" id="password" name="password" placeholder="Password" ></div>
                                    </div>

                                    <div class="#">
                                    <div class="button--update">
                                        <button type="submit" name="update">Update</button>
                                    </div>
                                </div>
                                </form>
                                </div>
                            </div>

                        </div>
                    </div>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            </body>
            </html>