<?php
include('dbconnect.php'); // Include the database connection file

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reservation_id = $_POST['reservation_id'];

    if ($action == 'accept') {
        // Fetch the vehicle_id from reservation_request
        $fetchVehicleQuery = "SELECT vehicle_id FROM reservation_request WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $fetchVehicleQuery);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $reservation_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $vehicle_id = $row['vehicle_id'];

            // Update reservation status to 'Traveling', payment status to 'Pending', and set traveling_date
            $updateReservationQuery = "UPDATE reservation_request SET status = 'Traveling', payment_status = 'Pending', traveling_date = NOW(), vehicle_status = 'Traveling' WHERE reservation_id = ?";
            $stmt = mysqli_prepare($con, $updateReservationQuery);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $reservation_id);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    // Update vehicle status to 'Traveling'
                    $updateVehicleQuery = "UPDATE vehicles_tbl SET vehicle_status = 'Traveling' WHERE id = ?";
                    $stmt_vehicle = mysqli_prepare($con, $updateVehicleQuery);

                    if ($stmt_vehicle) {
                        mysqli_stmt_bind_param($stmt_vehicle, "i", $vehicle_id);
                        $vehicleUpdateResult = mysqli_stmt_execute($stmt_vehicle);

                        if ($vehicleUpdateResult) {
                            echo '<script>alert("Vehicle is now Traveling and traveling_date set!");</script>';
                        } else {
                            echo "Error updating vehicle status: " . mysqli_error($con);
                        }
                    } else {
                        echo "Failed to prepare update vehicle statement: " . mysqli_error($con);
                    }
                } else {
                    echo "Error updating reservation status: " . mysqli_error($con);
                }
            } else {
                echo "Failed to prepare update reservation statement: " . mysqli_error($con);
            }
        } else {
            echo "Failed to prepare fetch vehicle statement: " . mysqli_error($con);
        }
    } elseif ($action == 'completed') {
        // Update reservation to 'Completed' and payment status to 'Completed'
        $updateQuery = "UPDATE reservation_request SET status = 'Completed', payment_status = 'Completed', vehicle_status = 'Maintenance' WHERE reservation_id = ?";
        $stmt = mysqli_prepare($con, $updateQuery);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $reservation_id);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                // Update vehicle status to 'Maintenance'
                $updateVehicleQuery = "UPDATE vehicles_tbl SET vehicle_status = 'Maintenance' WHERE id = (SELECT vehicle_id FROM reservation_request WHERE reservation_id = ?)";
                $stmt_vehicle = mysqli_prepare($con, $updateVehicleQuery);

                if ($stmt_vehicle) {
                    mysqli_stmt_bind_param($stmt_vehicle, "i", $reservation_id);
                    $vehicleUpdateResult = mysqli_stmt_execute($stmt_vehicle);

                    if ($vehicleUpdateResult) {
                        echo '<script>alert("Reservation completed and vehicle set to Maintenance!");</script>';
                    } else {
                        echo "Error updating vehicle status: " . mysqli_error($con);
                    }
                } else {
                    echo "Failed to prepare update vehicle statement: " . mysqli_error($con);
                }
            } else {
                echo "Error updating reservation status: " . mysqli_error($con);
            }
        } else {
            echo "Failed to prepare update reservation statement: " . mysqli_error($con);
        }
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
        <link rel="stylesheet" href="admin_topickup.css?= <?php echo time();?>">
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
        <div class="admin--monitoring--container">
                <div class="admin--monitoring--container--box">
                    <div class="admin--monitoring--header">
                        <h1>Monitoring</h1>
                        <div class="admin--monitoring--chooses">
                            <ul>
                                <li><a href="admin_monitoring.php">All Reservation</a></li>
                                <li><a href="admin_topickup.php" style="color: red;">To PickUp</a></li>
                                <li><a href="admin_traveling.php"> Travelling</a></li>
                                <li><a href="admin_completed.php"> Completed</a></li>
                                <li><a href="admin_cancelled.php">Cancelled</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="admin--customers--monitoring--container">
        <?php 
        // Corrected SQL query to filter out specific statuses
        $fetchadata = "SELECT * FROM reservation_request WHERE status = 'Approved'";
        $result = mysqli_query($con, $fetchadata);

        // Loop through the results
        while ($row = mysqli_fetch_assoc($result)) { 
            $reservation_id = $row['reservation_id'];
            $pickup_date = $row['pickup_date'];
            $customer_name = $row['customer_name'];
            $customer_address = $row['customer_address'];
            $vehicle_name = $row['vehicle_name'];
            $return_date = $row['return_date'];
            $rent_price = $row['rent_price'];
            $payment_status = $row['payment_status'];
            $status = $row['status'];
            $model = $row['model'];
            $driver_name = $row['driver_name'];
            $driver_contact = $row['driver_contact'];
            $rent_per_day = $row['rent_per_day'];
            $photo = $row['photo'];
            $cnum = $row['cnum'];
            $counting_days = $row['counting_days'];
            $request_time = $row['request_time'];
        ?>
        <form method="POST" action="">
            <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
            <input type="hidden" name="action" value="">
            <div class="admin--customers--monitoring--box">
                <div class="admin--customers--image--box"><img src="<?php echo $photo; ?>"></div>
                <div class="admin--vehicle--form--container">
                    <h1>Model</h1>
                    <p>2022</p>
                    <h1>Vehicle Name</h1>
                    <p><?php echo $vehicle_name; ?></p>
                    <h1>Driver Assigned</h1>
                    <p><?php echo $driver_name; ?></p>
                    <h1>Rent Price</h1>
                    <p>Php: <?php echo $rent_per_day; ?>.00</p>
                </div>
                <div class="admin--customers--form">
                    <h1>Customers Name</h1>
                    <p><?php echo $customer_name; ?></p>
                    <h1>Customers Address</h1>
                    <p><?php echo $customer_address; ?></p>
                    <h1>Contact Number</h1>
                    <p><?php echo $cnum; ?></p>
                    <h1>Request Time</h1>
                    <p style="color: red;"><?php echo $request_time; ?></p>
                </div>
                <div class="admin--reservation--details">
                    <h1>Pickup Date</h1>
                    <p><?php echo $pickup_date; ?></p>
                    <h1>Return Date</h1>
                    <p><?php echo $return_date; ?></p>
                    <h1>Rent Days</h1>
                    <p><?php echo $counting_days; ?> Days</p>
                </div>
                <div class="admin--additional--details">
                    <h1>Invoice ID: <?php echo $reservation_id; ?></h1>
                    <div class="status"><h1>Status</h1><p><?php echo $status; ?></p></div>
                    <div class="payment"><h1>Total Payment</h1><p>Php: <?php echo $rent_price; ?>.00</p></div>
                    <div class="setting--rental--reservation--form--button">
                        <button type="button" onclick="submitForm(this)">To Pickup</button>
                    </div> 
                </div>
            </div>
        </form>
        <?php } ?>
    </div>

    <script>
        function submitForm(button) {
            // Find the closest form to the clicked button
            const form = button.closest('form');
            form.querySelector('input[name="action"]').value = 'accept';
            form.submit();
        }
    </script>

                </div>
        </div>
    </body>
    </html>