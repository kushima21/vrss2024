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

        // Display welcome message and proceed with other queries

        // Fetch count of available vehicles
        $availableVehiclesQuery = "SELECT COUNT(*) as available_count FROM vehicles_tbl WHERE vehicle_status = 'Available'";
        $availableVehiclesResult = $con->query($availableVehiclesQuery);
        $available_vehicles = ($availableVehiclesResult->num_rows > 0) ? $availableVehiclesResult->fetch_assoc()['available_count'] : 0;

        // Fetch count of completed reservations
        $completedReservationsQuery = "SELECT COUNT(*) as completed_count FROM reservation_request WHERE status = 'Completed'";
        $completedReservationsResult = $con->query($completedReservationsQuery);
        $payment_complete = ($completedReservationsResult->num_rows > 0) ? $completedReservationsResult->fetch_assoc()['completed_count'] : 0;

        // Fetch data for line chart (count of reservations per vehicle)
        $lineChartQuery = "SELECT vehicle_name, COUNT(*) AS count FROM reservation_request GROUP BY vehicle_name";
        $lineChartResult = mysqli_query($con, $lineChartQuery);

        $labels = [];
        $values = [];

        while ($chartRow = mysqli_fetch_assoc($lineChartResult)) {
            $labels[] = $chartRow['vehicle_name'];
            $values[] = (int) $chartRow['count'];
        }

        // Check for new pending reservations
        $notificationQuery = "SELECT COUNT(*) AS new_pending_reservations FROM reservation_request WHERE status = 'pending' AND is_notified = 0";
        $notificationResult = mysqli_query($con, $notificationQuery);
        $newPendingReservations = ($notificationResult && mysqli_num_rows($notificationResult) > 0) ? mysqli_fetch_assoc($notificationResult)['new_pending_reservations'] : 0;

        if ($newPendingReservations > 0) {
            // Notify the user about pending reservations
            echo "<script>alert('You have $newPendingReservations new pending reservations.');</script>";

            // Mark reservations as notified
            $updateNotificationQuery = "UPDATE reservation_request SET is_notified = 1 WHERE status = 'pending' AND is_notified = 0";
            mysqli_query($con, $updateNotificationQuery);
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

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin_main_settings.css?= <?php echo time(); ?>">
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
                <div class="about">
                    <div class="setting-charts">
                        <div class="setting-chart">
                                <li>
                                    <a href="admin_account_settings.php">
                                        <h1><i class="fa-solid fa-user-gear"></i>Account</h1>
                                    </a>
                                        <p>Manage any basic account preferences such as full name, address and etc.</p>
                                </li>
                        </div>
                        <div class="setting-chart">
                            <li>
                                <a href="admin_main_security.php">
                                    <h1><i class="fa-solid fa-shield-halved"></i>Security</h1>
                                </a>
                                    <p>Manage any basic account preferences as change password, username, add new admin and etc.</p> 
                            </li>
                        </div>
                        <div class="setting-chart">
                                    <li>
                                        <a href="admin_main_vehicles.php">
                                            <h1><i class="fa-solid fa-gear"></i>Vehicle Management</h1>
                                        </a>
                                            <p>Manage vehicle details, add new vehicle rental details and etc.</p>
                                    </li>
                        </div>
                        <div class="setting-chart">
                        <li>
                            <a href="admin_main_drivers.php">
                                <h1><i class="fa-solid fa-screwdriver-wrench"></i>Driver</h1>
                            </a>
                                    <p>Manage driver details, add new driver details and etc.</p>
                            </li>
                        </div>

                    </div>
                
                    <div class="setting-image">
                        <img src="img/setting2.svg" alt="" class="setting-image">
                    </div>
                </div>
            
        </div>      
    </div>
</div>
    
</body>
</html>