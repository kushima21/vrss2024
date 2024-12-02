<?php
session_start(); // Start session to access session variables
include('dbconnect.php'); // Include database connection

// Check if the user is logged in
if (isset($_SESSION['admin_email'])) {
    $loggedInEmail = $_SESSION['admin_email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchDataQuery = "SELECT * FROM admin_signup WHERE admin_email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchDataQuery);

    // Check if user details were found
    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $admin_email = $row['admin_email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data

        // Check if the user has the required status_id
        if ($status_id == 1) {
            // Display welcome message
            // Example of using the user details

            // Fetch count of available vehicles
            $availableVehiclesQuery = "SELECT COUNT(*) as available_count FROM vehicles_tbl WHERE vehicle_status = 'Available'";
            $availableVehiclesResult = $con->query($availableVehiclesQuery);
            $available_vehicles = ($availableVehiclesResult->num_rows > 0) ? $availableVehiclesResult->fetch_assoc()['available_count'] : 0;

            // Fetch count of completed reservations
            $completedReservationsQuery = "SELECT COUNT(*) as completed_count FROM reservation_request WHERE status = 'Completed'";
            $completedReservationsResult = $con->query($completedReservationsQuery);
            $payment_complete = ($completedReservationsResult->num_rows > 0) ? $completedReservationsResult->fetch_assoc()['completed_count'] : 0;

            // Fetch count of pending reservations
            $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Pending'";
            $reservationStatusResult = $con->query($reservationStatusQuery);
            $Pending = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0; 

            // Fetch count of approved reservations
            $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Approved'";
            $reservationStatusResult = $con->query($reservationStatusQuery);
            $Approved = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0; 
            
            // Fetch count of traveling reservations
            $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Traveling'";
            $reservationStatusResult = $con->query($reservationStatusQuery);
            $Traveling = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0;

            // Fetch count of cancelled reservations
            $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Cancelled'";
            $reservationStatusResult = $con->query($reservationStatusQuery);
            $Cancelled = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0;

            // Check for new cancelled reservations
            $notificationQuery = "SELECT COUNT(*) AS new_cancelled_reservations FROM reservation_request WHERE status = 'Cancelled' AND is_notified = 0";
            $notificationResult = mysqli_query($con, $notificationQuery);
            $newCancelledReservations = ($notificationResult && mysqli_num_rows($notificationResult) > 0) ? mysqli_fetch_assoc($notificationResult)['new_cancelled_reservations'] : 0;

            if ($newCancelledReservations > 0) {
                // Notify the user about cancelled reservations
                echo "<script>alert('You have $newCancelledReservations new cancelled reservations.');</script>"; 

                // Mark reservations as notified
                $updateNotificationQuery = "UPDATE reservation_request SET is_notified = 1 WHERE status = 'Cancelled' AND is_notified = 0";
                mysqli_query($con, $updateNotificationQuery);
            }

        } else {
            // Redirect to login.php if status_id is not 1
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

     // Fetch count of available vehicles
     $availableVehiclesQuery = "SELECT COUNT(*) as available_count FROM vehicles_tbl WHERE vehicle_status = 'Available'";
     $availableVehiclesResult = $con->query($availableVehiclesQuery);
     $available_vehicles = ($availableVehiclesResult->num_rows > 0) ? $availableVehiclesResult->fetch_assoc()['available_count'] : 0;

     // Fetch count of completed reservations
     $completedReservationsQuery = "SELECT COUNT(*) as completed_count FROM reservation_request WHERE status = 'Completed'";
     $completedReservationsResult = $con->query($completedReservationsQuery);
     $payment_complete = ($completedReservationsResult->num_rows > 0) ? $completedReservationsResult->fetch_assoc()['completed_count'] : 0;

     // Fetch count of pending reservations
     $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Pending'";
     $reservationStatusResult = $con->query($reservationStatusQuery);
     $Pending = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0; // Fixed typo here: changed $reservationStatustResult to $reservationStatusResult

     $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Approved'";
     $reservationStatusResult = $con->query($reservationStatusQuery);
     $Approved = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0; // Fixed typo here: changed $reservationStatustResult to $reservationStatusResult
     
     $reservationStatusQuery = "SELECT COUNT(*) as request_count FROM reservation_request WHERE status = 'Traveling'";
     $reservationStatusResult = $con->query($reservationStatusQuery);
     $Traveling = ($reservationStatusResult->num_rows > 0) ? $reservationStatusResult->fetch_assoc()['request_count'] : 0; // Fixed typo here: changed $reservationStatustResult to $reservationStatusResult
     // Fetch data for line chart (count of reservations per vehicle)
     // Fetch data for line chart (count of reservations per vehicle)
     
     $lineChartQuery = "SELECT vehicle_name, COUNT(*) AS count FROM reservation_request GROUP BY vehicle_name";
     $lineChartResult = mysqli_query($con, $lineChartQuery);

     $labels = [];
     $values = [];
     while ($chartRow = mysqli_fetch_assoc($lineChartResult)) {
         $labels[] = $chartRow['vehicle_name'];
         $values[] = (int) $chartRow['count'];
     }
mysqli_close($con);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?=<?php echo time(); ?>">
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
        <div class="main">
            <div class="cards">
                <div class="card">
                    <a href="admin_reservation.php">
                    <div class="card-content">
                    <div class="number">
                        <?php echo $Pending; ?>
                        <?php if ($Pending > 0): ?>
                            <span class="notification-dot"></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-name">Reservation Request</div>
                </div>
                    </a>
                    <div class="icon-box"></div>
                </div>
                <div class="card">
                    <a href="admin_cancelled.php">
                        <div class="card-content">
                            <div class="number">
                                <?php echo $Cancelled; ?>
                                <?php if ($Cancelled > 0): ?>
                                    <span class="notification-dot"></span>
                                <?php endif; ?>
                                <div class="card-name">Cancelled</div>
                            </div>
                        </div>
                    </a>
                    <div class="icon-box"><i class="fa-solid fa-users"></i></div>
                </div>

                <div class="card">
                <a href="admin_traveling.php">
                    <div class="card-content">
                        <div class="number"><?php echo  $Traveling; ?></div>
                        <div class="card-name">Travelling</div>
                    </div>
                </a>
                    <div class="icon-box">
                    <i class="fa-solid fa-car icon"></i>
                    </div>
                </div>
                <div class="card">
                    <div class="card-content">
                        <div class="number"><?php echo $payment_complete; ?></div>
                        <div class="card-name">Completed Transactions</div>
                    </div>
                    <div class="icon-box">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
            <div class="charts">
                <div class="chart">
                    <h2>Monthly Report</h2>
                    <canvas id="lineChart"></canvas>
                </div>
                <div class="chart" id="doughnut-chart">
                    <h2>Reports</h2>
                    <canvas id="doughnut"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="path/to/jquery.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var ctx = document.getElementById('lineChart').getContext('2d');
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Reservations per Vehicle',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });

        var ctxDoughnut = document.getElementById('doughnut').getContext('2d');
        var myDoughnutChart = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Most Frequent Rented Van',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: [
                        'rgba(41, 155, 99, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(120, 46, 139, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderColor: [
                        'rgba(41, 155, 99, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(120, 46, 139, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        // Function to fetch data via AJAX
        function fetchDataForDoughnutChart() {
            $.ajax({
                url: 'fetch_most_rented_vans.php', // PHP script to fetch data
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Update chart data
                    myDoughnutChart.data.labels = data.labels;
                    myDoughnutChart.data.datasets[0].data = data.values;
                    myDoughnutChart.update(); // Update chart with new data
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        // Call fetchDataForDoughnutChart function to initially populate chart
        fetchDataForDoughnutChart();
    });
    </script>
   <script>
    // Fetch pending notifications count
    function fetchNotifications() {
        fetch("admin_dashboard.php?fetch_notification=true")
            .then(response => response.json())
            .then(data => {
                const notificationCount = document.getElementById("notification-count");
                if (data.pending_count > 0) {
                    notificationCount.textContent = data.pending_count;
                    notificationCount.style.display = "inline";
                } else {
                    notificationCount.style.display = "none";
                }
            })
            .catch(error => console.error("Error fetching notifications:", error));
    }

    // Mark notifications as read when bell is clicked
    document.getElementById('notification-bell').addEventListener('click', function () {
        fetch('admin_dashboard.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mark_notified: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('notification-count').textContent = '';
            }
        })
        .catch(error => console.error("Error marking notifications:", error));
    });

    // Periodically check for new notifications
    setInterval(fetchNotifications, 30000); // Fetch every 30 seconds
    fetchNotifications(); // Initial fetch
</script>

</body>
</html>
