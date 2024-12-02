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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
    <link rel="stylesheet" href="setting_vehicles_setting.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_main_vehicles.css?= <?php echo time();?>">

    <title>Admin Dashboard</title>
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
    <link rel="stylesheet" href="setting_vehicles_setting.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_main_vehicles.css?= <?php echo time();?>">
    <link rel="stylesheet" href="admin_main_vehicles_view.css?= <?php echo time();?>">

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
                            <h1>Vehicles Management</h1>
                        </div>
                        <div class="admin--vehicles--header--url">
                            <div class="admin--url--button--container">
                                <ul>
                                    <li><a href="admin_main_vehicles_add.php"><button>Add New Vehicles</button></a></li>
                                    <li><a href="admin_main_vehicles_maintenance.php"><button>Maintenace</button></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="view--vehicles--main--container--box">
                        <div class="view--vehicles--image--container">
                            <div  class="main--img"><img id="main-image" src="<?php echo htmlspecialchars($vehicle_data['main_image']); ?>"></div>
                            <div class="other--img">
                                <img src="<?php echo htmlspecialchars($vehicle_data['main_image']); ?>" onclick="changeImage(this)">
                                <img src="<?php echo htmlspecialchars($vehicle_data['additionalImages']); ?>" onclick="changeImage(this)">
                                <img src="<?php echo htmlspecialchars($vehicle_data['passengerImages']); ?>" onclick="changeImage(this)">
                            </div>
                        </div>
                        <div class="view--vehicles--details--container">
                            <div class="V-D-H"><h2>Vehicles Details</h2></div>
                            <div class="V-C-A">
                            <div class="V-D-D">
                                <h2><?php echo htmlspecialchars($vehicle_data['vehicle_name']); ?></h2>
                                <span>Color :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['color']); ?></p>
                                <span>Registration Plate :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['registration_plate']); ?></p>
                                <span>Seating Capacity :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['seating_capacity']); ?></p>
                                <span>Availability :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['availability']); ?></p>
                                <span>Status :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['vehicle_status']); ?></p>
                            </div>
                            <div class="V-D-C">
                                <h2>Driver Details</h2>
                                <span>Driver Assigned :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['driver_name']); ?></p>
                                <span>Address :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['address']); ?></p>
                                <span>Contact Number :</span>
                                <p><?php echo htmlspecialchars($vehicle_data['contact_num']); ?></p>
                            </div>
                            </div>
                            <div class="button">
                                <div class="rent"><h2>Php : <?php echo htmlspecialchars($vehicle_data['rent_price']); ?>.00</h2></div>
                                <a href="admin_main_vehicles.php"><button type="button">Get Back</button></a>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
                 
            <script>
            function changeImage(element) {
                const mainImage = document.getElementById('main-image');
                mainImage.src = element.src;
            }
            </script>
<script src="setting_vehicle.js"></script>
<script src="edit.js"></script>
 </body>
</head>
