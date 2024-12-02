<?php
include('dbconnect.php'); // Include the database connection file

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
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
// Fetch reservation data
$query = "SELECT 
            status AS title, 
            pickup_date AS start, 
            return_date AS end, 
            CONCAT(
                'Customer Name: ', customer_name, '\n',
                'Pickup Date: ', pickup_date, '\n',
                'Return Date: ', return_date, '\n',
                'Contact: ', cnum, '\n',
                'Rent Price: ', rent_price,'.00' '\n',
                'Rented Day: ', counting_days, 'Days' '\n',
                'Vehicle Name: ', vehicle_name, '\n',
                'Driver Assigned: ', driver_name
            ) AS description,
            photo
          FROM reservation_request";

$result = mysqli_query($con, $query);

$reservations = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reservations[] = $row;
    }
}
mysqli_close($con); // Close the database connection
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <link rel="stylesheet" href="admin_reports_calendar.css?= <?php echo time();?>">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <title>Admin Dashboard</title>
    </head>

    <body>

    <?php if (!empty($message)): ?>
        <script>
            swal.fire({
                title: '<?php echo $isSuccess ? "Success" : "Error"; ?>',
                text: '<?php echo $message; ?>',
                icon: '<?php echo $isSuccess ? "success" : "error"; ?>'
            });
        </script>
    <?php endif; ?>

    <div class="container">
        <div class="topbar">
            <div class="logo">
                <h2>VRRS</h2>
            </div>
            <div class="search"></div>
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
            <div class="admin--reports--main--container">
            <div class="admin--reports--container--box">
            <div class="admin--reports--header--container">
                <div class="admin--reports--header">
                    <div class="reports--headers">
                    <h2>VRSS REPORTS</h2>
                </div>
                <div class="reports--header--url">
                    <a href="admin_reports.php"><i class="fas fa-user"></i></a>
                    <a href="admin_reports_vehicle.php"><i class="fas fa-car"></i></a>
                    <a href="admin_reports_calendar.php"><i class="fas fa-calendar" style="color: red;"></i></a>
                </div>
            </div>
            <div class="admin--reports--graph--container">
                <div class="calendar"></div>
                <div class="reserved--hover"></div>
            </div>
            </div>
            </div>
        
            <script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.querySelector('.calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        validRange: {
            start: new Date().toISOString().split('T')[0], // Lock past dates
        },
        events: reservations, // Use the dynamically fetched events

        eventMouseEnter: function (info) {
            // Create the hover detail box
            var hoverBox = document.createElement('div');
            hoverBox.className = 'fc-hover-box';

            // Add event details and image, position photo on the right
            hoverBox.innerHTML = `
                <div class="hover-content">
                    <strong>${info.event.title}</strong><br>
                    ${info.event.extendedProps.description.replace(/\n/g, '<br>')}
                </div>
                <img src="${info.event.extendedProps.photo}" alt="Vehicle Photo" class="hover-photo">
            `;

            hoverBox.style.position = 'absolute';
            hoverBox.style.zIndex = '1000';
            hoverBox.style.background = '#fff';
            hoverBox.style.border = '1px solid #ccc';
            hoverBox.style.padding = '10px';
            hoverBox.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            hoverBox.style.borderRadius = '5px';
            hoverBox.style.pointerEvents = 'none'; // Ensure it doesn't block other events

            document.body.appendChild(hoverBox);

            // Position the hover box above the date (cursor)
            info.el.addEventListener('mousemove', function (e) {
                hoverBox.style.top = e.pageY - hoverBox.offsetHeight - 10 + 'px'; // 10px offset to appear above
                hoverBox.style.left = e.pageX + 15 + 'px'; // Slight offset to the right
            });

            // Remove the hover box on mouse leave
            info.el.addEventListener('mouseleave', function () {
                hoverBox.remove();
            });
        }
    });

    // Render the calendar
    calendar.render();
});
</script>
<script>
    // Pass PHP data to JavaScript
    const reservations = <?php echo json_encode($reservations); ?>;
</script>
        <script src="setting_report.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        </body>
        </html>