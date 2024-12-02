<?php
include('dbconnect.php');

$isSuccess = false;
$message = "";

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Extract form data
    $model = $_POST['model'];
    $vehicle_name = $_POST['vehicle_name'];
    $seating_capacity = $_POST['seating_capacity'];
    $color = $_POST['color'];
    $registration_plate = $_POST['registration_plate'];
    $rent_price = $_POST['rent_price'];
    $transmission = $_POST['transmission'];
    $driver_assign = $_POST['driver_assign']; // Corrected variable name
    $vehicle_status = isset($_POST['vehicle_status']) ? $_POST['vehicle_status'] : 'Available';
    $availability = $_POST['availability']; // Corrected variable name

    // Fetch driver information including address and contact number
    $fetchDriverInfo = "SELECT driver_name, address, contact_num FROM drivers_tbl WHERE driver_id = $driver_assign";
    $driverResult = mysqli_query($con, $fetchDriverInfo);

    if ($driverResult && mysqli_num_rows($driverResult) > 0) {
        $row = mysqli_fetch_assoc($driverResult);
        $driver_name = $row['driver_name'];
        $driver_address = $row['address']; // Get driver address
        $driver_contact_num = $row['contact_num']; // Get driver contact number

        // Handle file upload for the main image
        $mainImageFile = $_FILES['main_image'];
        $allowed_ext = ['jpg', 'png', 'jpeg'];
        $mainImageFileError = $mainImageFile['error'];
        $mainImageFileSize = $mainImageFile['size'];
        $mainImageFileExt = strtolower(pathinfo($mainImageFile['name'], PATHINFO_EXTENSION));

        // Handle file upload for passenger image
        $passengerImageFile = $_FILES['passengerImages'];
        $passengerImageError = $passengerImageFile['error'];
        $passengerImageSize = $passengerImageFile['size'];
        $passengerImageExt = strtolower(pathinfo($passengerImageFile['name'], PATHINFO_EXTENSION));

        // Handle file upload for additional images
        $additionalImages = $_FILES['additionalImages']; // Ensure this is defined as an array

        // Create 'more_images/' directory if not exists
        if (!is_dir('more_images/')) {
            mkdir('more_images/', 0755, true);
        }

        // Initialize an array to hold additional image paths
        $additionalImagePaths = [];

        // Process main image
        if (in_array($mainImageFileExt, $allowed_ext) && $mainImageFileError === 0 && $mainImageFileSize < 10000000) {
            $mainImageFileName = uniqid('', true) . "." . $mainImageFileExt;
            $mainImageDestination = 'more_images/' . $mainImageFileName;

            if (move_uploaded_file($mainImageFile['tmp_name'], $mainImageDestination)) {
                // Process passenger image
                if (in_array($passengerImageExt, $allowed_ext) && $passengerImageError === 0 && $passengerImageSize < 10000000) {
                    $passengerImageFileName = uniqid('', true) . "." . $passengerImageExt;
                    $passengerImageDestination = 'more_images/' . $passengerImageFileName;

                    if (move_uploaded_file($passengerImageFile['tmp_name'], $passengerImageDestination)) {
                        // Handle multiple additional images
                        if (!empty($additionalImages['name'][0])) {
                            foreach ($additionalImages['name'] as $key => $additionalImage) {
                                if (!empty($additionalImage)) {
                                    $additionalImageTmpName = $additionalImages['tmp_name'][$key];
                                    $additionalImageError = $additionalImages['error'][$key];
                                    $additionalImageSize = $additionalImages['size'][$key];
                                    $additionalImageExt = strtolower(pathinfo($additionalImage, PATHINFO_EXTENSION));

                                    // Validate additional image
                                    if ($additionalImageError === 0 && $additionalImageSize < 10000000 && in_array($additionalImageExt, $allowed_ext)) {
                                        $newAdditionalImageName = uniqid('', true) . '.' . $additionalImageExt;
                                        $additionalImageDestination = 'more_images/' . $newAdditionalImageName;

                                        if (move_uploaded_file($additionalImageTmpName, $additionalImageDestination)) {
                                            // Store additional image path
                                            $additionalImagePaths[] = $additionalImageDestination;

                                            // Insert additional image data into vehicle_images_tbl
                                            $insertAdditionalImage = "INSERT INTO vehicle_images_tbl (image_path) VALUES ('$additionalImageDestination')";
                                            mysqli_query($con, $insertAdditionalImage);
                                        }
                                    }
                                }
                            }
                        }

                        // Convert additional image paths to a comma-separated string
                        $additionalImageString = implode(',', $additionalImagePaths);

                        // Insert vehicle data into vehicles_tbl (including driver's address and contact number)
                        $savedata = "INSERT INTO vehicles_tbl (model, vehicle_name, seating_capacity, color, registration_plate, rent_price, driver_id, driver_name, vehicle_status, transmission, main_image, additionalImages, passengerImages, address, contact_num, availability) 
                                     VALUES ('$model', '$vehicle_name', '$seating_capacity', '$color', '$registration_plate', '$rent_price', '$driver_assign', '$driver_name', '$vehicle_status', '$transmission', '$mainImageDestination', '$additionalImageString', '$passengerImageDestination', '$driver_address', '$driver_contact_num', '$availability')";
                        $result = mysqli_query($con, $savedata);

                        if ($result) {
                            // Fetch the last inserted vehicle_id
                            $vehicle_id = mysqli_insert_id($con);

                            // Update driver_tbl with vehicle_name and vehicle_id
                            $updateDriver = "UPDATE drivers_tbl SET vehicle_name = '$vehicle_name', vehicle_id = $vehicle_id WHERE driver_id = $driver_assign";
                            mysqli_query($con, $updateDriver);

                            // Insert default vehicle status into reservation_request table
                            $insertReservationStatus = "INSERT INTO reservation_request (vehicle_id, vehicle_status) VALUES ('$vehicle_id', '$vehicle_status')";
                            mysqli_query($con, $insertReservationStatus);

                            $isSuccess = true;
                        } else {
                            $message = "Failed to save vehicle!";
                            $isSuccess = false;
                        }
                    } else {
                        $message = "Failed to move uploaded passenger image file!";
                        $isSuccess = false;
                    }
                } else {
                    $message = "File size exceeds limit or there was an error with the passenger image!";
                    $isSuccess = false;
                }
            } else {
                $message = "Failed to move uploaded main image file!";
                $isSuccess = false;
            }
        } else {
            $message = "File size exceeds limit or there was an error with the main image!";
            $isSuccess = false;
        }
    } else {
        $message = "Driver not found!";
        $isSuccess = false;
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
                    <h1>Add New Vehicles</h1>
                </div>
                        <div class="add--vehicles--description">
                            <div class="add--vehicles--box">
                                <div class="vehicle--head--description"><h1>Vehicle Descriptions</h1></div>
                                <div class="vehicles--form--description">
                                <form method="POST" enctype="multipart/form-data">
    <!-- Hidden input to store the action value -->
                                    <input type="hidden" name="action" id="actionField" value="">
                                    <label for="model">Model:</label>
                                    <input type="text" name="model" required>
                                    <br>
                                    <label for="vehicle_name">Vehicle Name:</label>
                                    <input type="text" name="vehicle_name" required>
                                    <br>
                                    <label for="seating_capacity">Seating Capacity:</label>
                                    <input type="text" name="seating_capacity" required>
                                    <br>
                                    <label for="availability">Availability:</label>
                                    <input type="text" name="availability" required>
                                    <br>
                                    <label for="color">Color:</label>
                                    <input type="text" name="color" required>
                                    <br>
                                    <label for="registration_plate">Registration Plate:</label>
                                    <input type="text" name="registration_plate" required>
                                    <br>
                                    <label for="rent_price">Rent Price:</label>
                                    <input type="number" name="rent_price"  required>
                                    <br>
                                    <label for="driver_assign">Driver Assign:</label>
                                    <select name="driver_assign" id="driver_assign" required>
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
                                    <input type="text" name="transmission" required>
                                </div>
                            </div>
                            <!--add image css -->
                                    <div class="add--image--box">
                                        <div class="image--head--description"><h1>Vehicle Images</h1></div>
                                        <div class="image--form--container">
    <div class="image-input-group">
        <label for="main_image">Main Image:</label>
        <input type="file" name="main_image" id="mainImage" required>
        <img id="mainImagePreview" class="image-preview" src="" alt="Main Image Preview" style="display:none;">
    </div>
    <div class="image-input-group">
        <label for="additionalImages">Front Image:</label>
        <input type="file" id="frontImage" name="additionalImages[]" accept="image/*" multiple>

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
                                <li><button type="submit" class="submit" name="submit">Save</button></li>
                                    <li><a href="admin_main_vehicles.php">Cancel</a></li>
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


   