<?php
    include('dbconnect.php'); // Include the database connection file

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if (isset($_GET['reservation_id'])) {
        $reservation_id = $_GET['reservation_id']; // Corrected variable name
        $reservation_query = "SELECT * FROM reservation_request WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $reservation_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $reservation_id); // Bind the corrected variable
            mysqli_stmt_execute($stmt);
            $reservation_result = mysqli_stmt_get_result($stmt);
            $reservation_data = mysqli_fetch_assoc($reservation_result);
        } else {
            echo "Failed to prepare reservation query: " . mysqli_error($con);
        }
    }

    // Additional code to handle fetching vehicle details if vehicle_id is set
    if (isset($_GET['vehicle_id'])) {
        $vehicle_id = $_GET['vehicle_id'];
        $vehicle_query = "SELECT * FROM vehicles_tbl WHERE id = ?";
        $stmt = mysqli_prepare($con, $vehicle_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $vehicle_id);
            mysqli_stmt_execute($stmt);
            $vehicle_result = mysqli_stmt_get_result($stmt);
            $vehicle_data = mysqli_fetch_assoc($vehicle_result);
        } else {
            echo "Failed to prepare vehicle query: " . mysqli_error($con);
        }
    }
    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
        <link rel="stylesheet" href="admin_customers.css?= <?php echo time();?>">
        <link rel="stylesheet" href="admin_cancelled.css?= <?php echo time();?>">
        <link rel="stylesheet" href="admin_customersview.css?= <?php echo time();?>">

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
        
            <div class="admin--customers--main--container">
                <div class="admin--monitoring--header">
                    <h1>Monitoring</h1>
                    <div class="admin--monitoring--chooses">
                        <ul>
                            <li><a href="admin_monitoring.php">All Reservation</a></li>
                            <li><a href="admin_topickup.php">To PickUp</a></li>
                            <li><a href="admin_traveling.php"> Travelling</a></li>
                            <li><a href="admin_completed.php"> Completed</a></li>
                            <li><a href="admin_cancelled.php" style="color: red;">Cancelled</a></li>
                        </ul>
                    </div>
                </div>

                    <div class="admin--main--customers--container--box">
                        <div class="admin--customers--form--containers">
                            <div class="admin--customers--image--box">
                                <img src="<?php echo htmlspecialchars($reservation_data['photo']); ?>">
                            </div>
                            <div class="admin--vehicle--form--container">
                                <h1>Model</h1>
                                <p>2022</p>
                                <h1>Vehicle Name</h1>
                                <p><?php echo htmlspecialchars($reservation_data['vehicle_name']); ?></p>
                                <h1>Driver Assigned</h1>
                                <p><?php echo htmlspecialchars($reservation_data['driver_name']); ?></p>
                                <h1>Rent Price</h1>
                                <p>Php: <?php echo htmlspecialchars($reservation_data['rent_price']); ?>.00</p>
                            </div>
                            <div class="admin--customers--form">
                                <h1>Customers Name</h1>
                                <p><?php echo htmlspecialchars($reservation_data['customer_name']); ?></p>
                                <h1>Customers Address</h1>
                                <p><?php echo htmlspecialchars($reservation_data['customer_address']); ?></p>
                                <h1>Contact Number</h1>
                                <p><?php echo htmlspecialchars($reservation_data['cnum']); ?></p>
                                <h1>Rent Days</h1>
                                <p><?php echo htmlspecialchars($reservation_data['counting_days']); ?> Days</p>
                            </div>

                            <div class="admin--additional--details">
                                <div class="invoice"><h1>Invoice ID: <?php echo htmlspecialchars($reservation_data['reservation_id']); ?></h1></div>
                                <h1>Cancelled Time</h1>
                                <p style="color: red;"><?php echo htmlspecialchars($reservation_data['cancel_time']); ?></p>
                                <div class="reason"><h1>Cancelled Reason</h1>
                                <p  style="color: red;"><?php echo htmlspecialchars($reservation_data['cancel_reason']); ?></p></div>
                                <div class="status"><h1>Status</h1><p><?php echo htmlspecialchars($reservation_data['status']); ?></p></div>
                                <div class="payment"><h1>Total Payment</h1><p>Php:<?php echo htmlspecialchars($reservation_data['rent_price']); ?>.00</p></div>
                                <div class="button--customers"><a href="admin_cancelled.php"><button>Get Back</button></a></div>
                            </div>

                            
                        </div> 
                    </div>
                </div>
            </div>


    </body>
    </html>