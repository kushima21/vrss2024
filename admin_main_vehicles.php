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

// Initialize edit data
$edit_data = [
    'vehicle_id' => '',
    'model' => '',
    'vehicle_name' => '',
    'seating_capacity' => '',
    'color' => '',
    'registration_plate' => '',
    'rent_price' => '',
    'driver_assign' => '',
    'transmission' => '',
    'image' => '',
    'vehicle_status' => 'Available'
];

// Handle vehicle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $con->prepare("SELECT driver_id, vehicle_name FROM vehicles_tbl WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $driver_id = $row['driver_id'];

        $stmt = $con->prepare("UPDATE drivers_tbl SET vehicle_name = NULL, vehicle_id = NULL WHERE driver_id = ?");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();

        $stmt = $con->prepare("DELETE FROM vehicles_tbl WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        $message = "Vehicle deleted successfully!";
    } else {
        $message = "Vehicle not found.";
    }
}

// Handle vehicle editing
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $con->prepare("SELECT * FROM vehicles_tbl WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_data = $edit_result->fetch_assoc();
}

// Handle file upload
function handleFileUpload($file, &$message, &$isSuccess) {
    $allowed_ext = ['jpg', 'png', 'jpeg'];
    $fileext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($fileext, $allowed_ext) && $file['error'] === 0 && $file['size'] < 10000000) {
        $filenewname = uniqid('', true) . "." . $fileext;
        $filedestination = 'images/' . $filenewname;

        if (!is_dir('images/')) {
            mkdir('images/', 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $filedestination)) {
            return $filedestination;
        } else {
            $message = "Failed to move uploaded file!";
            $isSuccess = false;
        }
    } else {
        $message = "Invalid file format or file size too large!";
        $isSuccess = false;
    }
    return false;
}

// Handle form submission for adding/updating vehicle
if (isset($_POST['submit']) || isset($_POST['update'])) {
    $vehicle_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $model = $_POST['model'];
    $vehicle_name = $_POST['vehicle_name'];
    $seating_capacity = $_POST['seating_capacity'];
    $color = $_POST['color'];
    $registration_plate = $_POST['registration_plate'];
    $rent_price = $_POST['rent_price'];
    $transmission = $_POST['transmission'];
    $driver_assign = $_POST['driver_assign'];
    $vehicle_status = $_POST['vehicle_status'] ?? 'Available';

    // Handle file upload for image
    $filedestination = isset($_FILES['image']) ? handleFileUpload($_FILES['image'], $message, $isSuccess) : '';

    if ($filedestination || $vehicle_id) {
        if ($vehicle_id) {
            // Update vehicle
            $stmt = $con->prepare("UPDATE vehicles_tbl SET model = ?, vehicle_name = ?, seating_capacity = ?, color = ?, registration_plate = ?, rent_price = ?, transmission = ?, image = ?, driver_id = ? WHERE id = ?");
            $stmt->bind_param("sssdsdssi", $model, $vehicle_name, $seating_capacity, $color, $registration_plate, $rent_price, $transmission, $filedestination, $driver_assign, $vehicle_id);
        } else {
            // Insert vehicle
            $stmt = $con->prepare("INSERT INTO vehicles_tbl (model, vehicle_name, seating_capacity, color, registration_plate, rent_price, driver_id, vehicle_status, transmission, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsdsss", $model, $vehicle_name, $seating_capacity, $color, $registration_plate, $rent_price, $driver_assign, $vehicle_status, $transmission, $filedestination);
        }

        if ($stmt->execute()) {
            $message = "Vehicle " . ($vehicle_id ? "updated" : "added") . " successfully!";
            $isSuccess = true;
            if (!$vehicle_id) $vehicle_id = $stmt->insert_id;

            // Update driver's vehicle assignment
            $stmt = $con->prepare("UPDATE drivers_tbl SET vehicle_name = ?, vehicle_id = ? WHERE driver_id = ?");
            $stmt->bind_param("sii", $vehicle_name, $vehicle_id, $driver_assign);
            $stmt->execute();
        } else {
            $message = "Error saving vehicle: " . $stmt->error;
            $isSuccess = false;
        }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin.css?= <?php echo time();?>">
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
                    <div class="admin--vehcicles--details--form--box">
                    <?php
                $fetchadata = "SELECT * FROM vehicles_tbl WHERE vehicle_status != 'Maintenance'";
                $result = mysqli_query($con, $fetchadata);
                while ($row = mysqli_fetch_assoc($result)) {
                    $vehicle_id = $row['id'];
                    $model = $row['model'];
                    $vehicle_name = $row['vehicle_name'];
                    $driver_name = $row['driver_name'];
                    $color = $row['color'];
                    $registration_plate = $row['registration_plate'];
                    $rent_price = $row['rent_price'];
                    $transmission = $row['transmission'];
                    $main_image = $row['main_image'];
                    $rent_price = $row['rent_price'];
                    $seating_capacity = $row['seating_capacity'];
                    $vehicle_status = $row['vehicle_status'];
                ?>
                        <div class="vehicles--box--container">
                            <div class="vehicle--image"><img src="<?php echo $main_image; ?>"></div>
                            <div class="vehicles--details--form">
                                <h1>Model</h1>
                                <p><?php echo $model; ?></p>
                                <h1>Vehicles Name</h1>
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
                                <div class="view--button"><a href="admin_main_vehicles_view.php?vehicle_id=<?php echo $vehicle_id; ?>"><button>View Vehicles Details</button></a></div>
                               
                                <input type="hidden" name="id" value="<?php echo $edit_data['vehicle_id']; ?>">
                                <div class="edit--or--delete--container">
                                    <a href="admin_main_vehicles_edit.php?edit_id=<?php echo $vehicle_id; ?>"><button><i class="fa-solid fa-pen"></i>Edit</button></a>
                                    <a href="?delete_id=<?php echo $vehicle_id; ?>" onclick="return confirmDelete(event)"><button><i class="fa-solid fa-trash"></i>Delete</button></a>
                                </div>
                            
                            </div>
                        </div>
                    <?php } ?>  

                    </div>
                </div>
            </div>


<script src="setting_vehicle.js"></script>
<script src="edit.js"></script>
 </body>
</head>
