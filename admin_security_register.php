<?php
    session_start(); // Start session to access session variables

    include('dbconnect.php'); 

    // Check if the user is logged in
    if (isset($_SESSION['admin_email'])) {
        $loggedInEmail = $_SESSION['admin_email']; // Retrieve logged-in user's email from session variable

        // Prepare and execute query to fetch user details based on logged-in email
        $fetchDataQuery = "SELECT * FROM admin_signup WHERE admin_email = '$loggedInEmail'";
        $result = mysqli_query($con, $fetchDataQuery);

        // Check if user details were found
        if (mysqli_num_rows($result) == 1) {
            // Fetch user details
            $row = mysqli_fetch_assoc($result);
            $id = $row['id'];
            $fname = $row['fname'];
            $lname = $row['lname'];
            $admin_email = $row['admin_email'];
            $status_id = $row['status_id']; // Assuming status_id is part of the fetched data
        }
    }

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // Include database connection
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
    ?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="admin_topickup.css?= <?php echo time();?>">
        <link rel="stylesheet" href="admin_security_register.css?= <?php echo time();?>">
        <link rel="stylesheet" href="admin_main_vehicles.css?= <?php echo time();?>">
        <title>Document</title>
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
                
                
                    <section>
                        <img src="img/setting2.svg" alt="" class="image">   
                                <div class="form-value">
                                    <form action="" method="post">
                                        <h2>Hello, Admin!</h2>
                                            <form action="" method="post"  >
                                            
                                            <div class="single-input">
                                                <input type="text" name="fname" id="fname" placeholder="First Name" value="" required>
                                            </div>

                                            <div class="single-input">
                                                <input type="text" name="lname" id="lname" placeholder="Last Name"  value="" >
                                            </div>
                                            <div class="single-input">
                                                <input type="text" name="address" id="address" placeholder="Address"  value="" required>
                                            </div>
                                            <div class="single-input">
                                                <input type="file" name="image" required>
                                            </div>
                                            <div class="single-input">
                                                <input type="text" name="admin_email" id="admin_email" placeholder="Email"  value="" required>
                                            </div>
                                            <div class="single-input">
                                                <input type="number" name="pnum" id="pnum" placeholder="Contact Number"  value="" required>
                                            </div>
                            
                                            <div class="single-input">
                                                <input type="password" name="password" id="password" placeholder="Password"  value="" required>
                                            </div>
                                            
                                            <div class="button-register">
                                                    <button type="submit" name="submit">Submit</button> 
                                                    <a href="admin_main_security.php"><button type="button">Get Back</button></a>
                                            </div>
                                            </form>
                                        </div>
                                    </form>
                    </div>
                    
                    </section>
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