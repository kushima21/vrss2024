<?php
include ('dbconnect.php');

$isSuccess = false;
$message = "";

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit'])) {
    $model = $_POST['model'];
    $vehicle_name = $_POST['vehicle_name'];
    $seating_capacity = $_POST['seating_capacity'];
    $color = $_POST['color'];
    $registration_plate = $_POST['registration_plate'];
    $rent_price = $_POST['rent_price'];
    $transmission = $_POST['transmission'];
    $image = $_FILES['image'];
    $vehicle_status = $_POST['vehicle_status'];
   
    
    $filename = $_FILES['image']['name'];
    $filetempname = $_FILES['image']['tmp_name'];
    $filsize = $_FILES['image']['size'];
    $fileerror = $_FILES['image']['error'];
    $filetype = $_FILES['image']['type'];

    $fileext = explode('.', $filename);
    $filetrueext = strtolower(end($fileext));
    $array = ['jpg', 'png', 'jpeg'];

    if (in_array($filetrueext, $array)) {
        if ($fileerror === 0) {
            if ($filsize < 10000000) {
                $filenewname = $filename;
                $filedestination = '../images/' . $filenewname;
                
                if (!is_dir('../images/')) {
                    mkdir('../images/', 0755, true); // Create directory recursively
                }
                
                // Check if destination directory is writable
                if (!is_writable('../images/')) {
                    $message = "Destination directory is not writable!";
                    $isSuccess = false;
                } else {
                    // Move the uploaded file to the destination directory
                    if (move_uploaded_file($filetempname, $filedestination)) {
                        // File uploaded successfully
                        
                        // Your database insertion code here
                        $savedata = "INSERT INTO vehicles_tbl VALUES ('', '$model', '$vehicle_name', '$seating_capacity', '$color', '$registration_plate', '$rent_price','$transmission', '../images/$filenewname', '$vehicle_status')";
                        $result = mysqli_query($con, $savedata);
                        if ($result) {
                            $message = "Saved Successfully!";
                            $isSuccess = true;
                        } else {
                            $message = "Failed!";
                            $isSuccess = false;
                        }
                    } else {
                        // File move failed
                        $message = "Failed to move uploaded file!";
                        $isSuccess = false;
                
                        // Check for specific error
                        $lastError = error_get_last();
                        if ($lastError) {
                            $message .= " Error: " . $lastError['message'];
                        }
                    }
                }
            } else {
                $message = "File size exceeds limit!";
                $isSuccess = false;
            }
        } else {
            $message = "File upload error!";
            $isSuccess = false;
        }
    } else {
        $message = "Invalid file format!";
        $isSuccess = false;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $vehicle_id= $_POST['id'];

    if ($action == 'available') {
        // Update vehicle status to 'Available'
        $updateQuery = "UPDATE vehicles_tbl SET vehicle_status = 'Available' WHERE id = $vehicle_id";
        $result = mysqli_query($con, $updateQuery);
        
        if ($result) {
            // Increment availability by 1 when status is updated to 'Available'
            $updateAvailabilityQuery = "UPDATE vehicles_tbl SET availability = availability + 1 WHERE id = $vehicle_id";
            $availabilityResult = mysqli_query($con, $updateAvailabilityQuery);
            
            if ($availabilityResult) {
                echo '<script>alert("Vehicle is Available and availability updated!");</script>';
            } else {
                echo "Error updating availability: " . mysqli_error($con);
            }
        } else {
            echo "Error updating vehicle status: " . mysqli_error($con);
        }
    } elseif ($action == 'traveling') {
        $updateQuery = "UPDATE vehicles_tbl SET vehicle_status = 'Traveling' WHERE id = $vehicle_id";
        $result = mysqli_query($con, $updateQuery);
        if ($result) {
            echo '<script>alert("Vehicle is Traveling!");</script>';
        } else {
            echo "Error declining reservation: " . mysqli_error($con);
        }
    } elseif ($action == 'maintenance') {
        // Check if the current status is not already 'maintenance'
        $checkStatusQuery = "SELECT vehicle_status FROM vehicles_tbl WHERE id = $vehicle_id";
        $statusResult = mysqli_query($con, $checkStatusQuery);
        $row = mysqli_fetch_assoc($statusResult);
        $currentStatus = $row['vehicle_status'];
        
        if ($currentStatus != 'maintenance') {
            $updateQuery = "UPDATE vehicles_tbl SET vehicle_status = 'Maintenance' WHERE id = $vehicle_id";
            $result = mysqli_query($con, $updateQuery);
            if ($result) {
                echo '<script>alert("Vehicle is under maintenance!");</script>';
            } else {
                echo "Error updating vehicle status: " . mysqli_error($con);
            }
        } else {
            echo '<script>alert("Vehicle is already under Maintenance");</script>';
        }
    }
}

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
    
    <link rel="stylesheet" href="admin_main_vehicles_maintenance.css?= <?php echo time();?>">

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
                            <a href="admin_main_vehicles.php"><i class="fas fa-arrow-left"><span>get back</span></i></a>
                            <h1>Vehicles Maintenance</h1>
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
<div class="A">
                    <div class="admin--vehicles--details--form--box">
    <?php
    // Fetch vehicles with status 'Maintenance'
    $fetchadata = "SELECT * FROM vehicles_tbl WHERE vehicle_status = 'Maintenance'";
    $result = mysqli_query($con, $fetchadata);
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $model = $row['model'];
        $vehicle_name = $row['vehicle_name'];
        $driver_name = $row['driver_name'];
        $color = $row['color'];
        $registration_plate = $row['registration_plate'];
        $rent_price = $row['rent_price'];
        $transmission = $row['transmission'];
        $main_image = $row['main_image'];
        $seating_capacity = $row['seating_capacity'];
        $vehicle_status = $row['vehicle_status'];
    ?>
        <form method="POST" action="" id="form-<?php echo $id; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="action" id="actionField-<?php echo $id; ?>" value="">
            <div class="vehicles--box--container">
                <div class="vehicle--image">
                    <img src="<?php echo $main_image; ?>" alt="Vehicle Image">
                </div>
                <div class="vehicles--details--form">
                    <h1>Model</h1>
                    <p><?php echo $model; ?></p>
                    <h1>Vehicle Name</h1>
                    <p><?php echo $vehicle_name; ?></p>
                    <h1>Driver Assigned</h1>
                    <p><?php echo $driver_name; ?></p>
                    <h1>Seating Capacity</h1>
                    <p><?php echo $seating_capacity; ?></p>
                </div>
                <div class="drivers--details">
                    <h1>Color</h1>
                    <p><?php echo $color; ?></p>
                    <h1>Transmission</h1>
                    <p><?php echo $transmission; ?></p>
                    <h1>Rent Price</h1>
                    <p>Php: <?php echo $rent_price; ?>.00</p>
                    <h1>Status</h1>
                    <p><?php echo $vehicle_status; ?></p>
                </div>
                <div class="container--for--button">
                
                    <div class="edit--or--delete--container">
                        <!-- "Available" Button -->
                        <button type="button" class="available-button" data-id="<?php echo $id; ?>">
                            <i class="fa-solid fa-pen"></i> Available
                        </button>
                        <button type="button"><i class="fa-solid fa-trash"></i>Delete</button>
                    </div>
                </div>
            </div>
        </form>
    <?php } ?>
</div>
</div>
    </div>
<script>
    // Attach event listeners to all "Available" buttons
    document.querySelectorAll('.available-button').forEach(button => {
        button.addEventListener('click', function () {
            const vehicleId = button.getAttribute('data-id');
            const actionField = document.getElementById('actionField-' + vehicleId);
            const form = document.getElementById('form-' + vehicleId);

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to set this vehicle as available?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, set as available!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Set the action field and submit the form
                    actionField.value = 'available';
                    form.submit();
                }
            });
        });
    });
</script>

<script src="setting_vehicle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="edit.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 </body>
</head>
