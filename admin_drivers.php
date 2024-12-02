<?php
include('dbconnect.php'); // Include the database connection file

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="admin_drivers.css?= <?php echo time();?>">
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

            <!-- driver tables section -->
             <div class="admin--drivers--section--main--container">
                <div class="admin--drivers--section--main--container--box">
                    <div class="admin--drivers--header--main--container">
                        <h2>Drivers Section</h2>
                    </div>
                    <div class="admin--drivers--details--box--container">
                        <div class="admin--drivers--details--main--container">
                            <?php
                                    $fetchadata = "SELECT * FROM drivers_tbl";
                                    $result = mysqli_query($con, $fetchadata);
                                    while ($row = mysqli_fetch_assoc($result)){
                                        $driver_id = $row['driver_id'];
                                        $driver_name= $row['driver_name'];
                                        $address= $row['address'];
                                        $commission = $row['commission'];
                                        $contact_num =$row['contact_num'];
                                        $drivers_license = $row ['drivers_license'];
                                        $vehicle_name = $row['vehicle_name'];
                                        $images = $row['images'];

                                ?>
                            <div class="admin--drivers--form--details--container">
                                <div class="drivers--information--form--container">
                                    <label>Drivers Name :</label>
                                    <p><?php echo $driver_name; ?></p>
                                    <label>Address :</label>
                                    <p><?php echo $address;?></p>
                                    <label>Contact Number :</label>
                                    <p><?php echo $contact_num; ?></p>
                                </div>
                                <div class="drivers--other--details">
                                    <label>Commission :</label>
                                    <p>Php: <?php echo $commission; ?>.00</p>
                                    <label>Driver License :</label>
                                    <p><?php echo $drivers_license;?></p>
                                    <label>Vehicle Assigned :</label>
                                    <p><?php echo $vehicle_name; ?></p>
                                </div>
                                <div class="drivers--image">
                                    <img src="<?php echo $images; ?>" alt="">
                                </div>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
             </div>

            </body>
            </html>