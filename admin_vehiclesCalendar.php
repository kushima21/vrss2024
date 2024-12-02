<?php
include('dbconnect.php');
session_start();

// Check if the user is logged in
if (isset($_SESSION['admin_email'])) {
    $loggedInEmail = $_SESSION['admin_email'];

    // Fetch user details
    $fetchUserQuery = "SELECT * FROM admin_signup WHERE admin_email = ?";
    $stmt = mysqli_prepare($con, $fetchUserQuery);
    mysqli_stmt_bind_param($stmt, "s", $loggedInEmail);
    mysqli_stmt_execute($stmt);
    $userResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($userResult) > 0) {
        $userRow = mysqli_fetch_assoc($userResult);
        $status_id = $userRow['status_id'];

        // Check if status_id is 1
        if ($status_id != 1) {
            echo "Access denied: Your account status is not valid.";
            exit;
        }
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "Please log in.";
    exit;
}

// Define the query to fetch data
$query = "SELECT 
            reservation_id, 
            customer_name, 
            cnum, 
            customer_address, 
            pickup_date, 
            return_date, 
            vehicle_name, 
            photo, 
            rent_price, 
            vehicle_status,
            status,
            driver_name, 
            counting_days 
          FROM reservation_request";

// Execute the query
$result = mysqli_query($con, $query);

// Prepare reservation data for FullCalendar
$reservations = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reservations[] = [
            'title' => $row['vehicle_name'],
            'start' => $row['pickup_date'],
            'end' => $row['return_date'],
            'description' => "Customer Name: " . $row['customer_name'] . "\n" .
                             "Pickup Date: " . $row['pickup_date'] . "\n" .
                             "Return Date: " . $row['return_date'] . "\n" .
                             "Contact: " . $row['cnum'] . "\n" .
                             "Rent Price: " . $row['rent_price'] . ".00\n" .
                             "Rented Day: " . $row['counting_days'] . " Days\n" .
                             "Driver Assigned: " . $row['driver_name'],
            'photo' => $row['photo']
        ];
    }
}

// Close the database connection
mysqli_close($con);

// Convert the PHP array to JSON for FullCalendar
$reservationData = json_encode($reservations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_vehiclesCalendar.css?= <?php echo time();?>">

    <title>Admin Dashboard</title>
</head>
<body>

<?php if (!empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Notification',
                    text: '<?php echo $message; ?>',
                    icon: '<?php echo ($isSuccess) ? "success" : "error"; ?>',
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Perform additional actions on save
                        window.location.href = 'setting_vehicle.php';
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Handle cancel action if needed
                        window.location.href = 'setting_vehicle.php';
                    }
                });
            });
        </script>
    <?php endif; ?>



    <div class="container">
        <div class="topbar">
            <div class="logo">
                <h2>VRRS</h2>
            </div>
            <div class="search">
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
            <div class="admin--vehicles--main--container">
                <div class="admin--vehicle--main--container--box">

                    <div class="admin--vehicles--header--container">
                        <h2>Vehicles Section</h2>
                        <div class="admin--search--vehicle--container">
                            <form id="searchForm" method="GET" action="">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="search" id="searchInput" placeholder="Search Vehicles" value="" required>
                                <button type="submit">Search</button>
                            </form>
                        </div>
                        <div class="admin--icon--container">
                            <a href="admin_vehiclesCalendar.php"><i class="fas fa-calendar"></i></a>
                            <a href="admin_vehicles.php"><i class="fas fa-car"></i></a>
                            <a href="#"><i class="fas fa-wrench"></i></a>
                        </div>
                    </div>

                    <div class="vehicles--main--container">
                        <div id="calendar"></div>
                        <div class="reserved--hover" id="reservedHover" style="display: none;"></div>
                    </div>
                </div>
            </div>

<script src="setting_vehicle.js"></script>
<script src="edit.js"></script>

<script>
// Initialize the calendar
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var hoverDiv = document.getElementById('reservedHover');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?php echo $reservationData; ?>,
        eventMouseEnter: function (info) {
            // Populate the hover div with event details
            hoverDiv.innerHTML = `
                <strong>${info.event.title}</strong><br>
                <img src="${info.event.extendedProps.photo}" alt="Vehicle Image" style="width: 100px; height: auto;"><br>
                ${info.event.extendedProps.description.replace(/\n/g, '<br>')}
            `;

            // Get the bounding box of the event element
            var eventRect = info.el.getBoundingClientRect();
            var calendarRect = calendarEl.getBoundingClientRect();

            // Position the hover div above the event
            hoverDiv.style.display = 'block';
            hoverDiv.style.position = 'absolute';
            hoverDiv.style.top = `${eventRect.top - calendarRect.top - hoverDiv.offsetHeight - 10}px`; // 10px above the event
            hoverDiv.style.left = `${eventRect.left - calendarRect.left + (eventRect.width / 2) - (hoverDiv.offsetWidth / 2)}px`; // Centered horizontally
            hoverDiv.style.zIndex = '1000';
        },
        eventMouseLeave: function () {
            // Hide the hover div when the mouse leaves the event
            hoverDiv.style.display = 'none';
        }
    });

    calendar.render();
});
</script>





 </body>
</head>
