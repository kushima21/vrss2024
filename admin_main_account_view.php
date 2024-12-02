<?php
session_start(); // Start the session
include('dbconnect.php'); // Include your database connection setup

$isSuccess = false;
$message = "";

// Check if user is logged in
if (!isset($_SESSION['admin_email'])) {
    // Redirect to login.php if not logged in
    header("Location: admin_login.php");
    exit();
}

// Fetch user data if edit_id is set
$edit_data = ['fname' => '', 'lname' => '', 'address' => '', 'pnum' => '', 'image' => '', 'id' => ''];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_query = "SELECT * FROM admin_signup WHERE id = $edit_id";
    $edit_result = mysqli_query($con, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_result);
    } else {
        $message = "No data found for the given ID.";
    }
}

// Update user data if the form is submitted
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $update_fields = [];
    $params = [];

    if (!empty($_POST['fname'])) {
        $update_fields[] = "fname = ?";
        $params[] = mysqli_real_escape_string($con, $_POST['fname']);
    }
    if (!empty($_POST['lname'])) {
        $update_fields[] = "lname = ?";
        $params[] = mysqli_real_escape_string($con, $_POST['lname']);
    }
    if (!empty($_POST['address'])) {
        $update_fields[] = "address = ?";
        $params[] = mysqli_real_escape_string($con, $_POST['address']);
    }
    if (!empty($_POST['pnum'])) {
        $update_fields[] = "pnum = ?";
        $params[] = mysqli_real_escape_string($con, $_POST['pnum']);
    }

    // Handle file upload only if a file was selected
    if (!empty($_FILES['image']['name'])) {
        $filename = $_FILES['image']['name'];
        $filetempname = $_FILES['image']['tmp_name'];
        $filesize = $_FILES['image']['size'];
        $fileerror = $_FILES['image']['error'];

        $fileext = explode('.', $filename);
        $filetrueext = strtolower(end($fileext));
        $allowed_ext = ['jpg', 'png', 'jpeg'];

        if (in_array($filetrueext, $allowed_ext)) {
            if ($fileerror === 0 && $filesize < 10000000) { // 10 MB limit
                $filenewname = uniqid('', true) . "." . $filetrueext;
                $filedestination = 'images/' . $filenewname;

                if (move_uploaded_file($filetempname, $filedestination)) {
                    $update_fields[] = "image = ?";
                    $params[] = $filedestination;
                } else {
                    $message = "Error moving uploaded file!";
                }
            } else {
                $message = $filesize >= 10000000 ? "File size exceeds limit!" : "Error uploading file!";
            }
        } else {
            $message = "Invalid file format!";
        }
    }

    if (!empty($update_fields)) {
        $update_query = "UPDATE admin_signup SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $params[] = $id;

        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, str_repeat("s", count($params) - 1) . "i", ...$params);

        if (mysqli_stmt_execute($stmt)) {
            $isSuccess = true;
            $message = "Record updated successfully!";
            header("Location:admin_account_settings.php");
            exit();
        } else {
            $message = "Error updating record: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}

// Optional: Display any messages after form processing
if ($message) {
    echo "<script>alert('$message');</script>";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin_account_settings.css?= <?php echo time(); ?>">
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

        <div class="account_container">
            <div class="account_head">
                <h1>ACCOUNT DETAILS</h1>
            </div>
            <div class="account_details_container">
                <div class="account_details">
                    <div class="account_form">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form_head">
                                <h1>Form Details</h1>
                            </div>
                            <div class="form_details" style="position: relative;">
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">

                                <label for="fname">First Name:</label>
                                <input type="text" name="fname" value="<?php echo $edit_data['fname']; ?>"><br><br>

                                <label for="lname">Last Name:</label>
                                <input type="text" name="lname" value="<?php echo $edit_data['lname']; ?>"><br><br>

                                <label for="address">Address:</label>
                                <input type="text" name="address" value="<?php echo $edit_data['address']; ?>"><br><br>

                                <label for="image">Image:</label>
                                <input type="file" name="image"><br><br>

                                <label for="pnum">Phone Number:</label>
                                <input type="text" name="pnum" value="<?php echo $edit_data['pnum']; ?>" maxlength="11"  title="Please enter a valid 11-digit Philippine mobile number starting with 09." required><br>
                                <!-- Image aligned to the right -->
    
                                <img src="<?php echo $edit_data['image']; ?>" alt="Current Image" style="position: absolute; left: 850px; top: 0; width: 250px;">

                                <div class="account_button">
                                <?php if ($edit_data['id'] == ''): ?>

                                <?php else: ?>
                                    <br><button type="submit" name="update" value="Update Details">Update</button>
                                <?php endif; ?>

                                <a href="admin_account_settings.php">
                                    <button type="button">Go Back</button>
                                </a>
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