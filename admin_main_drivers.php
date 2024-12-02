<?php
session_start(); // Make sure to start the session
include('dbconnect.php'); // Ensure this file includes your database connection setup

$isSuccess = false;
$message = "";

// Check if user is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to login.php if not logged in
    header("Location: login.php");
    exit();
}

$edit_data = [
    'driver_id' => '',
    'driver_name' => '',
    'address' => '',
    'commission' => '',
    'contact_num' => '',
    'drivers_license' => '',
    'vehicle_name' => '',
    'images' => ''
];

if (isset($_POST['submit'])) {
    $driver_name = mysqli_real_escape_string($con, $_POST['driver_name']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $commission = mysqli_real_escape_string($con, $_POST['commission']);
    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $drivers_license = mysqli_real_escape_string($con, $_POST['drivers_license']);
    $vehicle_name = isset($_POST['vehicle_name']) ? mysqli_real_escape_string($con, $_POST['vehicle_name']) : null; // Assign NULL if vehicle_name is not set
    
    $filename = $_FILES['image']['name'];
    $filetempname = $_FILES['image']['tmp_name'];
    $filesize = $_FILES['image']['size'];
    $fileerror = $_FILES['image']['error'];
    $filetype = $_FILES['image']['type'];

    $fileext = explode('.', $filename);
    $filetrueext = strtolower(end($fileext));
    $allowed_ext = ['jpg', 'png', 'jpeg'];

    if (in_array($filetrueext, $allowed_ext)) {
        if ($fileerror === 0) {
            if ($filesize < 10000000) { // 10 MB limit
                $filenewname = uniqid('', true) . "." . $filetrueext;
                $filedestination = 'images/' . $filenewname;
                
                if (!is_dir('images/')) {
                    mkdir('images/', 0755, true); // Create directory recursively if not exists
                }
                
                // Move the uploaded file to the destination directory
                if (move_uploaded_file($filetempname, $filedestination)) {
                    // Insert into database using prepared statement (recommended for security)
                    $savedata = "INSERT INTO drivers_tbl (driver_name, address, commission, contact_num, drivers_license, vehicle_name, images) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($con, $savedata);
                    mysqli_stmt_bind_param($stmt, "sssssss", $driver_name, $address, $commission, $contact_num, $drivers_license, $vehicle_name, $filedestination);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Saved Successfully!";
                        $isSuccess = true;
                    } else {
                        $message = "Failed to save!";
                        $isSuccess = false;
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Failed to move uploaded file!";
                    $isSuccess = false;
                }
            } else {
                $message = "File size exceeds limit!";
                $isSuccess = false;
            }
        } else {
            $message = "Error uploading file!";
            $isSuccess = false;
        }
    } else {
        $message = "Invalid file format!";
        $isSuccess = false;
    }
}


if (isset($_POST['update'])) {
    $driver_id = $_POST['driver_id'];
    $driver_name = mysqli_real_escape_string($con, $_POST['driver_name']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $commission = mysqli_real_escape_string($con, $_POST['commission']);
    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $drivers_license = mysqli_real_escape_string($con, $_POST['drivers_license']);
    $vehicle_name = mysqli_real_escape_string($con, $_POST['vehicle_name']);
    
    $filename = $_FILES['image']['name'];
    $filetempname = $_FILES['image']['tmp_name'];
    $filesize = $_FILES['image']['size'];
    $fileerror = $_FILES['image']['error'];
    $filetype = $_FILES['image']['type'];

    $fileext = explode('.', $filename);
    $filetrueext = strtolower(end($fileext));
    $allowed_ext = ['jpg', 'png', 'jpeg'];

    if (in_array($filetrueext, $allowed_ext)) {
        if ($fileerror === 0) {
            if ($filesize < 10000000) { // 10 MB limit
                $filenewname = uniqid('', true) . "." . $filetrueext;
                $filedestination = 'images/' . $filenewname;
                
                // Move the uploaded file to the destination directory
                if (move_uploaded_file($filetempname, $filedestination)) {
                    // Update database record
                    $update_query = "UPDATE drivers_tbl SET driver_name=?, address=?, commission=?, contact_num=?, drivers_license=?, vehicle_name=?, images=? WHERE driver_id=?";
                    $stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($stmt, "sssssssi", $driver_name, $address, $commission, $contact_num, $drivers_license, $vehicle_name, $filedestination, $driver_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Record updated successfully!";
                        header("Location: settings_driver.php");
                        exit();
                    } else {
                        $message = "Error updating record: " . mysqli_error($con);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Error moving uploaded file!";
                }
            } else {
                $message = "File size exceeds limit!";
            }
        } else {
            $message = "Error uploading file!";
        }
    } else {
        $message = "Invalid file format!";
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Start a transaction
    mysqli_begin_transaction($con);
    
    try {
        // First, update the vehicle_tbl to set driver_name to NULL or an empty value
        $update_query = "UPDATE vehicles_tbl SET driver_name = NULL WHERE driver_id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating vehicles_tbl: " . mysqli_error($con));
        }

        // Then, delete the driver record
        $delete_query = "DELETE FROM drivers_tbl WHERE driver_id = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting record: " . mysqli_error($con));
        }

        // Commit the transaction
        mysqli_commit($con);

        $message = "Record deleted successfully!";
        header("Location: admin_main_drivers.php");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($con);
        $message = $e->getMessage();
    }

    mysqli_stmt_close($stmt);
}

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM drivers_tbl WHERE driver_id = ?";
    $stmt = mysqli_prepare($con, $edit_query);
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    
    $edit_result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($edit_result);
    
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin_main_drivers.css?= <?php echo time();?>">
    <title>Admin Dashboard</title>
</head>

<body>



<!---sweetalert -->

<?php if (!empty($message)): ?>

    <script>
        swal.fire({
            title:'<?php echo $isSuccess ? "Success" : "Error"; ?>',
            text: '<?php echo $message; ?>',
            icon: '<?php echo $isSuccess ? "sucess" : "error"; ?>'
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
        <div class="main">
                
                    <div class="vehi_head">
                    <a href="admin_main_settings.php"><span><i class="fa-solid fa-arrow-left"></i>Go Back</span></a>
                        <h4>Driver Management <i class="fa-solid fa-person-circle-plus"></i></h4>
                        <div class="add_security">
                            <a href="admin_main_drivers_add.php"><button id="myBtn"><h2><i class="fa-solid fa-plus"></i>Add New Driver</h2></button></a>
                        </div>
                    </div>
                        <table class="table" id="form1">
                            <thead>
                                <tr>
                                   
                                    <th>Full Name</th>
                                    <th>Address</th>
                                    <th>Commission</th>
                                    <th>Contact Number</th>
                                    <th>Driver's License</th>
                                    <th>Vehicle Assigned</th>
                                    <th>Image</th>
                                    <th>Option</th>
                                </tr>
                            </thead>
                       
                        <div class="vehicle-container" id="vehicle1">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="vehicle-box">
                                <div class="vehicle--details" id="form2">
                                    <div class="close">&times;</div>
                                            <div class="vehicle_head"><h2>Add New Driver <h2></div>
                                      
                                    <div class="driver-form">
                                    <input type="hidden" name="id" value="<?php echo $edit_data['driver_id']; ?>">
                        
                                            <label for="driver_name">Full Name:</label><br>
                                            <input type ="text" name="driver_name" value="" required><br>
                                            
                                            <label for="address">Address:</label><br>
                                            <input type ="text" name="address" value="" required><br>

                                                <label for="commission">Commission:</label><br>
                                            <input type ="text" name="commission" value="" required><br>
                                            
                                            <label for="contact_num">Contact Number:</label>
                                            <input type ="text" name="contact_num" value="" required><br>


                                            <label for="drivers_license">Driver's License:</label><br>
                                            <input type ="text" name="drivers_license" value="" required><br>
                                           

                                            

                                            <label for="image">Image:</label><br>
                                            <input type="file" id="image_input" name="image" accept="images/*" value="" ><br>
                                            
                                            
                                            <div class="vehicle-btn">                   
                                           <br>
                                                 <input type="submit" name="submit" value="Add Drivers">
                                            
                                            </div>
                                           
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                       

                        <tbody>
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
                                        <tr>
                                        <td><?php echo $driver_name; ?></td>
                                        <td><?php echo $address; ?></td>
                                        <td><?php echo  $commission; ?></td>
                                        <td><?php echo $contact_num; ?></td>           
                                        <td><?php echo $drivers_license; ?></td>
                                        <td><?php echo  $vehicle_name; ?></td>
    
                                        <td><img width="auto" height="100px" src="<?php echo $images; ?>" alt=""> </td>
                                        
                                        <td>   
                                        <a href="admin_main_drivers_edit.php?edit_id=<?php echo $driver_id; ?>"><button id="">Edit<i class="fa-solid fa-pen"></i></button></a>  |
                                        <a href = "?delete_id=<?php echo $driver_id;?>"><button><i class="fa-solid fa-trash"></i>Delete</button></a>
                                        </td>
                                    </tr>
                                    <?php }?>   
                            
                        </tbody>    
                </table>    
            </div>

        </div>            
    <script src="settings_vehicle.js"></script>
    <script src="setting_driver.js"></script>
 </body>
</head>
