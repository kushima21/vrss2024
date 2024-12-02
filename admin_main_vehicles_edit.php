<?php
include('dbconnect.php');

$isSuccess = false;
$message = "";

// Step 1: Retrieve vehicle details for editing
if (isset($_GET['edit_id']) && $con) {
    $edit_id = $_GET['edit_id'];
    $stmt = $con->prepare("SELECT * FROM vehicles_tbl WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $edit_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $message = "Error preparing the edit query: " . $con->error;
    }
}

// Step 2: Handle form submission for updating vehicle details
if (isset($_POST['update'])) {
    $model = $_POST['model'] ?? $edit_data['model'];
    $vehicle_name = $_POST['vehicle_name'] ?? $edit_data['vehicle_name'];
    $seating_capacity = $_POST['seating_capacity'] ?? $edit_data['seating_capacity'];
    $color = $_POST['color'] ?? $edit_data['color'];
    $registration_plate = $_POST['registration_plate'] ?? $edit_data['registration_plate'];
    $rent_price = $_POST['rent_price'] ?? $edit_data['rent_price'];
    $transmission = $_POST['transmission'] ?? $edit_data['transmission'];
    $driver_assign = $_POST['driver_assign'] ?? $edit_data['driver_id'];
    $vehicle_status = $_POST['vehicle_status'] ?? 'Available';
    $availability = $_POST['availability'] ?? $edit_data['availability'];

    // Fetch driver information
    $driver_stmt = $con->prepare("SELECT driver_name FROM drivers_tbl WHERE driver_id = ?");
    $driver_stmt->bind_param("i", $driver_assign);
    $driver_stmt->execute();
    $driverResult = $driver_stmt->get_result();
    $driver_name = $driverResult->fetch_assoc()['driver_name'] ?? '';
    $driver_stmt->close();

    // Initialize image paths with current values
    $mainImageDestination = $edit_data['main_image'];
    $passengerImageDestination = $edit_data['passengerImages'];
    $additionalImagePaths = $edit_data['additionalImages'] ;
    $allowed_ext = ['jpg', 'png', 'jpeg'];

    // Handle Main Image Upload
    if ($_FILES['main_image']['error'] === 0) {
        $mainImageDestination = handleImageUpload($_FILES['main_image'], 'more_images/', $allowed_ext, $mainImageDestination);
    }

     // Handle additional Image Image Upload
     if ($_FILES['additionalImages']['error'] === 0) {
        $additionalImagePaths = handleImageUpload($_FILES['additionalImages'], 'more_images/', $allowed_ext,  $additionalImagePaths);
    }
    // Handle Passenger Image Upload
    if ($_FILES['passengerImages']['error'] === 0) {
        $passengerImageDestination = handleImageUpload($_FILES['passengerImages'], 'more_images/', $allowed_ext, $passengerImageDestination);
    }


    

    // Update vehicle details in the database
    $stmt = $con->prepare("UPDATE vehicles_tbl SET model=?, vehicle_name=?, seating_capacity=?, color=?, registration_plate=?, rent_price=?, driver_id=?, vehicle_status=?, transmission=?, main_image=?, additionalImages=?, passengerImages=?, availability=?, driver_name=? WHERE id=?");

    if ($stmt) {
        $stmt->bind_param("sssssdisssssssi", $model, $vehicle_name, $seating_capacity, $color, $registration_plate, $rent_price, $driver_assign, $vehicle_status, $transmission, $mainImageDestination, $additionalImagePaths, $passengerImageDestination, $availability, $driver_name, $edit_id);

        if ($stmt->execute()) {
            // Update driver information if changed
            if ($driver_assign != $edit_data['driver_id']) {
                // Set the previous driver's vehicle assignment to NULL
                $nullifyOldDriver = $con->prepare("UPDATE drivers_tbl SET vehicle_name = NULL, vehicle_id = NULL WHERE driver_id = ?");
                $nullifyOldDriver->bind_param("i", $edit_data['driver_id']);
                $nullifyOldDriver->execute();
                $nullifyOldDriver->close();

                // Update the new driver with the vehicle information
                $updateDriver = $con->prepare("UPDATE drivers_tbl SET vehicle_name = ?, vehicle_id = ? WHERE driver_id = ?");
                $updateDriver->bind_param("sii", $vehicle_name, $edit_id, $driver_assign);
                $updateDriver->execute();
                $updateDriver->close();
            }

            // Insert reservation request status
            $insertReservationStatus = $con->prepare("INSERT INTO reservation_request (vehicle_id, vehicle_status) VALUES (?, ?)");
            $insertReservationStatus->bind_param("is", $edit_id, $vehicle_status);
            $insertReservationStatus->execute();
            $insertReservationStatus->close();

            $isSuccess = true;
            header("Location: admin_main_vehicles.php");
            exit;  // Stop further execution
        } else {
            $message = "Failed to update vehicle.";
        }
        $stmt->close();
    } else {
        $message = "Error preparing the update query: " . $con->error;
    }
}

// Image upload handling function
function handleImageUpload($file, $destinationDir, $allowed_ext, $oldImagePath = null) {
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($fileExt, $allowed_ext) && $file['size'] < 10000000) {
        $newFileName = uniqid('', true) . "." . $fileExt;
        $newFilePath = $destinationDir . $newFileName;
        if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
            // Remove the old image if it exists
            if ($oldImagePath && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            return $newFilePath;
        }
    }
    return $oldImagePath;  // Return the old path if no new file is uploaded
}
?>




    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="admin_main_vehicles_add.css?= <?php echo time();?>">
        <title>Admin Dashboard</title>
    </head>
    <body>
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $isSuccess ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
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

            <div class="add--vehicles--container">
                <div class="add--head">
                    <h1>Change Vehicle Information</h1>
                </div>
                        <div class="add--vehicles--description">
                            <div class="add--vehicles--box">
                                <div class="vehicle--head--description"><h1>Vehicle Descriptions</h1></div>
                                <div class="vehicles--form--description">
                                <form id="updateForm" method="POST" enctype="multipart/form-data">
    <!-- Hidden input to store the action value -->
                                    <input type="hidden" name="action" id="actionField" value="<?php echo $edit_data['id']; ?>">
                                    <label for="model">Model:</label>
                                    <input type="text" name="model" value="<?php echo $edit_data['model']; ?>" >
                                    <br>
                                    <label for="vehicle_name">Vehicle Name:</label>
                                    <input type="text" name="vehicle_name" value="<?php echo $edit_data['vehicle_name']; ?>" >
                                    <br>
                                    <label for="seating_capacity">Seating Capacity:</label>
                                    <input type="text" name="seating_capacity" value="<?php echo $edit_data['seating_capacity']; ?>" >
                                    <br>
                                    <label for="availability">Availability:</label>
                                    <input type="text" name="availability" value="<?php echo $edit_data['availability']; ?>">
                                    <br>
                                    <label for="color">Color:</label>
                                    <input type="text" name="color" value="<?php echo $edit_data['color']; ?>" >
                                    <br>
                                    <label for="registration_plate">Registration Plate:</label>
                                    <input type="text" name="registration_plate" value="<?php echo $edit_data['registration_plate']; ?>" >
                                    <br>
                                    <label for="rent_price">Rent Price:</label>
                                    <input type="number" name="rent_price" value="<?php echo $edit_data['rent_price']; ?>"  >
                                    <br>
                                    <label for="driver_assign">Driver Assign:</label>
                                    <select name="driver_assign" id="driver_assign" value="<?php echo $edit_data['driver_assign']; ?>" >
                                <?php
                                // Fetch drivers who are not assigned to any vehicle
                                $fetchDrivers = "SELECT driver_id, driver_name FROM drivers_tbl WHERE vehicle_name IS NULL";
                                $driversResult = mysqli_query($con, $fetchDrivers);

                                // Check if there are drivers available
                                if (mysqli_num_rows($driversResult) > 0) {
                                    // Loop through the result set and generate options
                                    while ($driver = mysqli_fetch_assoc($driversResult)) {
                                        echo '<option value="' . $driver['driver_id'] . '">' . $driver['driver_name'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No Drivers Available</option>';
                                }
                                ?>
                            </select>
                            <br>

                                    <label for="transmission">Transmission:</label>
                                    <input type="text" name="transmission" value="<?php echo $edit_data['transmission']; ?>">
                                </div>
                            </div>
                            <!--add image css -->
                                    <div class="add--image--box">
                                        <div class="image--head--description"><h1>Vehicle Images</h1></div>
                                        <div class="image--form--container">
    <div class="image-input-group">
        <label for="main_image">Main Image:</label>
        <input type="file" name="main_image" id="mainImage"  >
        <img id="mainImagePreview" class="image-preview" src="" alt="Main Image Preview" style="display:none;">
    </div>
    <div class="image-input-group">
        <label for="additionalImages">Front Image:</label>
        <input type="file" id="frontImage" name="additionalImages" accept="image/*" >

        <img id="frontImagePreview" class="image-preview" src="" alt="Front Image Preview" style="display:none;">
    </div>
    <div class="image-input-group">
        <label for="passengerImages">Passenger Seat Image:</label>
        <input type="file" name="passengerImages" id="passengerImage" accept="image/*">
        <img id="passengerImagePreview" class="image-preview" src="" alt="Passenger Seat Image Preview" style="display:none;">
    </div>
</div>

                            <div class="add--vehicles--button">
                                <ul>
                                    <?php if ($edit_data['id'] == ''): ?>
                                    <li><button type="submit" class="submit" name="submit">Add vehicles</button></li>
                                    <?php else: ?>
                                    <li><button type="submit" name="update">Update</button></li>
                                    <li><a href="admin_main_vehicles.php"><button type="button">Cancel</button></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                                    </div>
                            
                            <!-- end add image css -->
                            </form>
                        </div>
                        
     
            
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="setting_vehicle.js"></script>
            <script src="edit.js"></script>
            <script src="admin_main_vehicles_add.js"></script>
        </div>
    </body>
    </html>


   