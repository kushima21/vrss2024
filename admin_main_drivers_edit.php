<?php
session_start(); // Make sure to start the session
include('dbconnect.php'); // Ensure this file includes your database connection setup

$isSuccess = false;
$message = "";

// Check if user is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to login.php if not logged in
    header("Location: admin_login.php");
    exit();
}

// Initialize $edit_data as an empty array
$edit_data = [];

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM drivers_tbl WHERE driver_id = ?";
    
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($con, $edit_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $edit_data = mysqli_fetch_assoc($result);
        } else {
            $message = "No data found for the given ID.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Error preparing the edit query: " . mysqli_error($con);
    }
}


if (isset($_POST['update'])) {
    $driver_id = $_POST['id'];
    $driver_name = mysqli_real_escape_string($con, $_POST['driver_name']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $commission = mysqli_real_escape_string($con, $_POST['commission']);
    $contact_num = mysqli_real_escape_string($con, $_POST['contact_num']);
    $drivers_license = mysqli_real_escape_string($con, $_POST['drivers_license']);

    // Check if image is uploaded
    $filename = $_FILES['image']['name'];
    $filetempname = $_FILES['image']['tmp_name'];
    $filesize = $_FILES['image']['size'];
    $fileerror = $_FILES['image']['error'];

    // Default image path (if no new image is uploaded)
    $filedestination = $edit_data['images'];

    if (!empty($filename)) {
        $fileext = explode('.', $filename);
        $filetrueext = strtolower(end($fileext));
        $allowed_ext = ['jpg', 'png', 'jpeg'];

        if (in_array($filetrueext, $allowed_ext)) {
            if ($fileerror === 0) {
                if ($filesize < 10000000) { // 10 MB limit
                    $filenewname = uniqid('', true) . "." . $filetrueext;
                    $filedestination = 'images/' . $filenewname;

                    if (!move_uploaded_file($filetempname, $filedestination)) {
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

    // Start transaction to ensure all updates succeed
    mysqli_begin_transaction($con);

    try {
        // Update drivers_tbl record
        $update_query = "UPDATE drivers_tbl SET driver_name=?, address=?, commission=?, contact_num=?, drivers_license=?, images=? WHERE driver_id=?";
        $stmt = mysqli_prepare($con, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $driver_name, $address, $commission, $contact_num, $drivers_license, $filedestination, $driver_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating drivers_tbl: " . mysqli_error($con));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Error preparing drivers_tbl update query: " . mysqli_error($con));
        }

        // Update reservation_request table
        $reservation_update_query = "UPDATE reservation_request SET driver_name=? WHERE driver_id=?";
        $stmt2 = mysqli_prepare($con, $reservation_update_query);
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, "si", $driver_name, $driver_id);
            if (!mysqli_stmt_execute($stmt2)) {
                throw new Exception("Error updating reservation_request: " . mysqli_error($con));
            }
            mysqli_stmt_close($stmt2);
        } else {
            throw new Exception("Error preparing reservation_request update query: " . mysqli_error($con));
        }

        // Update vehicles_tbl table
        $vehicles_update_query = "UPDATE vehicles_tbl SET driver_name=? WHERE driver_id=?";
        $stmt3 = mysqli_prepare($con, $vehicles_update_query);
        if ($stmt3) {
            mysqli_stmt_bind_param($stmt3, "si", $driver_name, $driver_id);
            if (!mysqli_stmt_execute($stmt3)) {
                throw new Exception("Error updating vehicles_tbl: " . mysqli_error($con));
            }
            mysqli_stmt_close($stmt3);
        } else {
            throw new Exception("Error preparing vehicles_tbl update query: " . mysqli_error($con));
        }

        // Commit the transaction if all updates succeed
        mysqli_commit($con);
        $isSuccess = true;
        $message = "Record updated successfully!";
        header("Location: admin_main_drivers.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        mysqli_rollback($con);
        $message = $e->getMessage();
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
    <link rel="stylesheet" href="admin_main_drivers_edit.css?= <?php echo time();?>">
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
            <a href="admin_main_drivers.php"><span><i class="fa-solid fa-arrow-left"></i> Go Back</span></a>
            <h4>Driver Management <i class="fa-solid fa-person-circle-plus"></i></h4>
        </div>

        <div class="pussy--container">
            <div class="edit--box">
                <div class="driver--form">
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="details--drivers">
                            <input type="hidden" name="id" value="<?php echo $edit_data['driver_id']; ?>">

                            <label for="driver_name">Full Name:</label><br>
                            <input type="text" name="driver_name" value="<?php echo $edit_data['driver_name']; ?>"><br>

                            <div class="form-group">
                                <div class="form-left">
                                    <label for="address">Address:</label><br>
                                    <input type="text" name="address" value="<?php echo $edit_data['address']; ?>"><br>
                                </div>
                                <div class="form-right previous-image"><br>
                                    <img src="<?php echo $edit_data['images']; ?>" alt="Previous Image">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-left">
                                    <label for="commission">Commission:</label><br>
                                    <input type="text" name="commission" value="<?php echo $edit_data['commission']; ?>"><br>
                                </div>
                                <div class="form-right new-image">
                                    <label for="image"></label><br>
                                    <input type="file" id="image_input" name="image" accept="image/*"><br>
                                </div>
                            </div>

                            <label for="contact_num">Contact Number:</label><br>
                            <input type="text" name="contact_num" value="<?php echo $edit_data['contact_num']; ?>" required><br>

                            <label for="drivers_license">Driver's License:</label><br>
                            <input type="text" name="drivers_license" value="<?php echo $edit_data['drivers_license']; ?>" required><br>

                            <div class="drivers--btn">
                                <?php if ($edit_data['driver_id'] == ''): ?>
                                    <br><input type="submit" name="submit" value="Add Drivers">
                                <?php else: ?>
                                    <br><input  type="submit" name="update" value="Update Data">
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>