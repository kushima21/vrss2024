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

if (isset($_GET['search'])) {
    $vehicle_name = $_GET['search']; // Get the vehicle name from the query parameter

    // Perform a query to search for the vehicle
    $searchQuery = "SELECT * FROM vehicles_tbl WHERE vehicle_name LIKE ?";
    $stmt = $con->prepare($searchQuery);
    
    if (!$stmt) {
        die("Failed to prepare statement: " . $con->error); // Handle prepare errors
    }

    $searchTerm = "%" . $vehicle_name . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $searchResult = $stmt->get_result();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_vehicles.css?= <?php echo time();?>">
    <link rel="stylesheet" href="setting_vehicles_setting.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_main_vehicles.css?= <?php echo time();?>">

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
            <?php
$searchTerm = ""; // Default value for search term

// Check if a search term is provided in the URL
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search']; // Get the search term from the query parameter
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    
    // Query to fetch vehicle names matching the search term
    $searchQuery = "SELECT vehicle_name FROM vehicles_tbl WHERE vehicle_name LIKE ?";
    $stmt = $con->prepare($searchQuery);
    $searchTermWithWildcard = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $searchTermWithWildcard);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if any vehicles are found
    $vehicles = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row['vehicle_name'];
        }
    }
    
    // Return vehicle names as JSON
    echo json_encode($vehicles);
}
?>
            <div class="admin--vehicles--main--container">
                <div class="admin--vehicle--main--container--box">

                    <div class="admin--vehicles--header--container">
                        <h2>Vehicles Section</h2>
                        <div class="admin--search--vehicle--container">
                            <form id="searchForm" method="GET" action="">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="search" id="searchInput" placeholder="Search Vehicles" value="<?php echo $searchTerm; ?>" required>
                                <button type="submit">Search</button>
                            </form>
                        </div>
                        <div class="admin--icon--container">
                            <a href="admin_vehiclesCalendar.php"><i class="fas fa-calendar"></i></a>
                            <a href="admin_vehicles.php"><i class="fas fa-car"></i></a>
                            <a href="admin_main_vehicles.php"><i class="fas fa-wrench"></i></a>
                        </div>
                    </div>

                    <div class="vehicles--main--container">
                    <?php
        // SQL query to fetch vehicles that match the search term
        $fetchQuery = "SELECT * FROM vehicles_tbl WHERE vehicle_name LIKE ?";
        $stmt = $con->prepare($fetchQuery);
        if (!$stmt) {
            die("Failed to prepare statement: " . $con->error); // Handle SQL preparation errors
        }

        // Add '%' around the search term for pattern matching
        $searchParam = "%" . $searchTerm . "%";
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();

        $count = 0; // Initialize a counter to limit the number of results
        while ($row = $result->fetch_assoc()) {
            if ($count >= 3) break; // Stop after displaying 3 vehicles

            // Fetch vehicle data
            $id = $row['id']; 
            $model = $row['model'];
            $vehicle_name = $row['vehicle_name'];
            $color = $row['color'];
            $registration_plate = $row['registration_plate'];
            $rent_price = $row['rent_price'];
            $driver_name = $row['driver_name'];
            $transmission = $row['transmission'];
            $main_image = $row['main_image'];
            $availability = $row['availability'];
            $vehicle_status = $row['vehicle_status'];
            $seating_capacity = $row['seating_capacity'];
            $address = $row['address'];
            $contact_num = $row['contact_num'];
        ?>
                        <div class="vehicles--main--box--container">
                            <div class="main--image--container"><img src="<?php echo $main_image; ?>"></div>
                            <div class="vehicles--details--container">
                                <h2><?php echo $vehicle_name; ?></h2>
                                <span>Model: <?php echo $model; ?></span>
                                <br>
                                <span>Seating Capacity: <?php echo $seating_capacity; ?></span>
                                <br>
                                <span>Color : <?php echo $color; ?></span>
                                <br>
                                <span>Registration Plate : <?php echo $registration_plate; ?></span>
                                <br>
                                <span>Availability : <?php echo $availability; ?></span>
                                <h3>Status: <?php echo $vehicle_status; ?></h3>
                            </div>
                            <div class="driver--details--container">
                                <h2>Drivers Details</h2>
                                <span>Drivers Assigned : <?php echo $driver_name; ?></span>
                                <br>
                                <span>Address : <?php echo $address; ?></span>
                                <br>
                                <span>Contact Number : <?php echo $contact_num; ?></span>
                                <div class="rent--price">
                                    <span>Rent Price :</span>
                                    <p>Php :</p>
                                    <h2><?php echo $rent_price; ?>.00</h2>
                                </div>
                            </div>
                        </div>
                        <?php 
            $count++; // Increment the counter for each vehicle displayed
        } 

        // Display a message if no vehicles match the search term
        if ($count == 0) {
            echo "<div class='center-message'><p>No vehicles found</p></div>";
        }
        ?>
                    
                    </div>
                </div>
            </div>

<script src="setting_vehicle.js"></script>
<script src="edit.js"></script>
 </body>
</head>
