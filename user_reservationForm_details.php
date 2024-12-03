<?php
session_start(); // Start session to access session variables
include('dbconnect.php'); // Include database connection

// Check if the user is logged in
if (isset($_SESSION['email'])) {
    $loggedInEmail = $_SESSION['email']; // Retrieve logged-in user's email from session variable

    // Prepare and execute query to fetch user details based on logged-in email
    $fetchadata = "SELECT * FROM signup WHERE email = '$loggedInEmail'";
    $result = mysqli_query($con, $fetchadata);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user details
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $status_id = $row['status_id']; // Assuming status_id is part of the fetched data

        // Check if the user has the required status_id
        if ($status_id == 2) {
            // Display or use fetched details as needed
           // Example of using the user details
        } else {
            // Redirect to login.php if status_id is not 2
            header("Location: login.php");
            exit(); // Ensure no further code is executed
        }
        
    } else {
        // Redirect to login.php if user is not found
        header("Location: login.php");
        exit(); // Ensure no further code is executed
    }
} else {
    // Redirect to login.php if user is not logged in
    header("Location: login.php");
    exit(); // Ensure no further code is executed
}

// Fetch vehicle data placeholder
$vehicleData = [
    'vehicle_name' => '',
    'model' => '',
    'rent_price' => '',
    'main_image' => '',
    'additionalImages' => '',
    'address' => '',
    'contact_num' => '',
    'passengerImages' => '',
    'vehicle_status' => '',
    'registration_plate' => ''

];

if (isset($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];
    $fetchVehicleData = "SELECT * FROM vehicles_tbl WHERE id = ?";
    $stmt = mysqli_prepare($con, $fetchVehicleData);
    mysqli_stmt_bind_param($stmt, "i", $vehicle_id);
    mysqli_stmt_execute($stmt);
    $vehicleResult = mysqli_stmt_get_result($stmt);

    if ($vehicleResult) {
        $vehicleData = mysqli_fetch_assoc($vehicleResult);
    } else {
        die("Error fetching vehicle data: " . mysqli_error($con));
    }
}

// Fetch conflicting reservations for the selected vehicle
$conflictingReservations = [];
$query = "SELECT pickup_date, return_date FROM reservation_request WHERE vehicle_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $vehicle_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $conflictingReservations[] = $row;
}

// Process reservation form submission
if (isset($_POST['submit'])) {
    // Retrieve form data
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $vehicle_name = $vehicleData['vehicle_name'];
    $photo = $vehicleData['main_image'];
    $cnum = $_POST['cnum'];
    $id_card = $_POST['id_card'];
    $valid_id = $_FILES['valid_id']['name'];
    $payment_status = $_POST['payment_status'];
    $status = 'Pending';
    $driver_name = $vehicleData['driver_name'];
    $seating_capacity = $vehicleData['seating_capacity'];
    $rent_per_day = $vehicleData['rent_price'];
    $driver_address = $vehicleData['address'];
    $driver_contact = $vehicleData['contact_num'];
    $model = $vehicleData['model'];
    $vehicle_status = $vehicleData['vehicle_status'];
    $registration_plate = $vehicleData['registration_plate'];
    $driver_id = $vehicleData['driver_id'];
    $current_date = date("Y-m-d");

    // Check if pickup date is valid
    if ($pickup_date < $current_date) {
        echo '<script>alert("You cannot select a pickup date from yesterday or earlier.");</script>';
    } else {
        // Check for date conflicts
        $isConflict = false;
        foreach ($conflictingReservations as $reservation) {
            if (($pickup_date >= $reservation['pickup_date'] && $pickup_date <= $reservation['return_date']) ||
                ($return_date >= $reservation['pickup_date'] && $return_date <= $reservation['return_date']) ||
                ($pickup_date <= $reservation['pickup_date'] && $return_date >= $reservation['return_date'])) {
                $isConflict = true;
                break;
            }
        }

        if ($isConflict) {
            echo '<script>alert("Selected dates are not available. Please choose different dates.");</script>';
        } else {
            // Calculate rent and rental days
            $pickup_date_obj = new DateTime($pickup_date);
            $return_date_obj = new DateTime($return_date);
            $interval = $pickup_date_obj->diff($return_date_obj);
            $counting_days = $interval->days + 1;
            $total_rent_price = $counting_days * $rent_per_day;
            $request_time = date('Y-m-d h:i A');

            // Insert reservation data
            $saveData = "INSERT INTO reservation_request 
                (customer_name, customer_address, pickup_date, vehicle_name, photo, return_date, rent_price, cnum, id_card, valid_id, payment_status, email, user_id, request_time, vehicle_id, status, driver_name, seating_capacity, rent_per_day, driver_address, driver_contact, model, counting_days,vehicle_status,registration_plate,driver_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
            
            $stmt = mysqli_prepare($con, $saveData);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssss",
                    $customer_name, $customer_address, $pickup_date, $vehicle_name, $photo,
                    $return_date, $total_rent_price, $cnum, $id_card, $valid_id, $payment_status,
                    $loggedInEmail, $id, $request_time, $vehicle_id, $status, $driver_name,
                    $seating_capacity, $rent_per_day, $driver_address, $driver_contact, $model, $counting_days, $vehicle_status,$registration_plate, $driver_id
                );

                if (mysqli_stmt_execute($stmt)) {
                    $reservation_id = mysqli_insert_id($con);

                    // Update vehicle availability and dates
                    $updateAvailability = "UPDATE vehicles_tbl SET availability = availability - 1, pickup_date = ?, return_date = ? WHERE id = ?";
                    $stmtUpdate = mysqli_prepare($con, $updateAvailability);

                    if ($stmtUpdate) {
                        mysqli_stmt_bind_param($stmtUpdate, "ssi", $pickup_date, $return_date, $vehicle_id);

                        if (mysqli_stmt_execute($stmtUpdate)) {
                            echo '<script>alert("Reservation successful!");</script>';
                            header("Location: user_invoice.php?reservation_id=" . $reservation_id);
                            exit();
                        } else {
                            echo "Error updating vehicle dates: " . mysqli_stmt_error($stmtUpdate);
                        }
                    } else {
                        echo "Error preparing vehicle update statement: " . mysqli_error($con);
                    }
                } else {
                    echo "Error: " . mysqli_stmt_error($stmt);
                }
            } else {
                echo "Error preparing statement: " . mysqli_error($con);
            }
        }
    }
}
// 1. Check if the user has any unviewed approved reservations
$checkReservationQuery = "SELECT * FROM reservation_request WHERE user_id = ? AND status IN ('Approved', 'Traveling') AND viewed_reservation = 0";
$stmt2 = $con->prepare($checkReservationQuery);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$reservationResult = $stmt2->get_result();
$hasApprovedReservation = mysqli_num_rows($reservationResult) > 0;

// 2. If the user clicks the link, update reservation to mark it as viewed
if (isset($_GET['viewed'])) {
    // Update reservation to mark it as viewed
    $updateReservationQuery = "UPDATE reservation_request SET viewed_reservation = 1 WHERE user_id = ? AND status = 'Approved' AND viewed_reservation = 0";
    $stmt3 = $con->prepare($updateReservationQuery);
    $stmt3->bind_param("i", $id);
    $stmt3->execute();

    // Redirect to the transaction history page to ensure the red dot disappears
    header("Location: user_main_transaction_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata and Title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS Reservation</title>
    <link rel="stylesheet" href="user_reservationForm_details.css?=<?php echo time(); ?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time(); ?>">
    <link rel="stylesheet" href="user_search.css?= <?php echo time(); ?>">
    <link rel="stylesheet" href="float.css?= <?php echo time(); ?>">
    <link rel="stylesheet" href="reservation.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_reservationForm_details.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div class="user--main--container">
<div class="wrapper">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
<div class="topbar">
        <div class="logo">
            <h2><i class="fa-solid fa-key"></i>VRRS</h2>
        </div>
        <div class="nav-container">
            <button class="nav-toggle" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
  </button>
  <div class="nav-links" id="navLinks">
    <ul>
      <li><a href="user_dashboard.php"><i class="fas fa-home"></i> <span class="link-text">Home</span></a></li>
      <li><a href="user_about.php"><i class="fas fa-info-circle"></i> <span class="link-text">About</span></a></li>
      <li><a href="user_reservation.php"><i class="fas fa-calendar-check"></i> <span class="link-text">Reservation</span></a></li>
      <li><a href="user_contact.php"><i class="fas fa-envelope"></i> <span class="link-text">Contact</span></a></li>
      <li><a href="user_services.php"><i class="fas fa-concierge-bell"></i> <span class="link-text">Services</span></a></li>
      <li><a href="user_search.php"><i class="fas fa-magnifying-glass"></i><span class="link-text">Search</span></a></li>    
    </ul>
  </div>
</div>
<div class="mode">
    <div class="moon-sun" id="toggle-switch">
        <i class="fa-solid fa-moon" id="moon"></i>
        <i class="fa-solid fa-sun" id="sun"></i> <!-- Corrected this line -->
    </div>
</div>
                            <div class="profile">
                                <div class="profileBtn">
                                    <span class="profile_text"> <?php echo $fname, $lname; ?></span>
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <ul class="profile_options">
                                    <li class="profile_option">
                                      <i class="fa-solid fa-user"></i>
                                        <a href="user_main_transaction_account.php"><span class="option_profile">Profile Modifications</span></a>
                                    </li>
                                    <li class="profile_option">
                                          <i class="fa-solid fa-clock-rotate-left"></i>
                                          <a href="user_main_transaction_history.php"><span class="option_profile">Transaction History </span></a>
                                    </li>
                                    <li class="profile_option">
                                          <i class="fa-solid fa-shield"></i>
                                          <a href="user_transaction_term_condition.php"><span class="option_profile">Terms and Conditions </span></a>
                                    </li>
                                    <li class="profile_option">
                                      <i class="fa-solid fa-right-from-bracket"></i>
                                        <a href="logout.php"><span class="option_profile">Logout</span></a>
                                    </li>
                                </ul>
                         </div>  
                    </div>     
                </div>    
    <section></section>
    <div class="user--reservation--main--container">
        <div class="user--reservation--main--box">
            <div class="user--vehicle--main--container">

                <div class="user--vehicle--image--main--container">
                    <div class="main--image">
                        <img id="main-image" src="<?php echo htmlspecialchars($vehicleData['main_image']); ?>" >
                    </div>
                    <div class="vehicle--other">
                        <div class="reservation-vehicle-other">
                            <img src="<?php echo htmlspecialchars($vehicleData['main_image']); ?>"  onclick="changeImage(this)">
                        </div>
                        <div class="reservation-vehicle-other">
                            <img src="<?php echo htmlspecialchars($vehicleData['additionalImages']); ?>" onclick="changeImage(this)">
                        </div>
                        <div class="reservation-vehicle-other">
                            <img src="<?php echo htmlspecialchars($vehicleData['passengerImages']); ?>"  onclick="changeImage(this)">
                        </div>
                    </div>
                </div>
                <div class="user--vehicle--details--main--container">
                    <div class="vehicles--description--head"><h1><?php echo htmlspecialchars($vehicleData['vehicle_name']); ?></h1></div>
                    <div class="vehicles--container--description">
                            <p>Model: <?php echo htmlspecialchars($vehicleData['model']); ?></p>
                            <p>Seating Capacity: <?php echo htmlspecialchars($vehicleData['seating_capacity']); ?></p>
                            <p>Color: <?php echo htmlspecialchars($vehicleData['color']); ?></p>
                            <p>Registration Plate: <?php echo htmlspecialchars($vehicleData['registration_plate']); ?></p>
                            <p>Rent Per Day: <?php echo htmlspecialchars($vehicleData['rent_price']); ?>.00</p>
                            <p>Drivers Assigned: <?php echo htmlspecialchars($vehicleData['driver_name']); ?></p>
                            <p>Contact Number: <?php echo htmlspecialchars($vehicleData['contact_num']); ?></p>
                            <p>Address: <?php echo htmlspecialchars($vehicleData['address']); ?></p>
                    </div>
                </div>
            </div>

            <div class="user--reservation--form--container">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="reservation--header"><h2>Reservation Form</h2></div>
                    <div class="reservation--form--container">
                        <label for="customer_name">Customer Name:</label><br>
                        <input type="text" name="customer_name" id="customer_name" placeholder="Customer Name" required>
                        <br>
                        <label for="customer_address">Address:</label><br>
                        <input type="text" name="customer_address" id="customer_address" placeholder="Address" required>
                        <div class="conflict--date">
                        <label for="pickup_date">Pickup Date:</label><br>
                        <input type="date" name="pickup_date" id="pickup_date" onchange="checkDateAvailability(); calculateTotalRent()" placeholder="Pickup Date" required>
                        <br>
                        <label for="return_date">Return Date:</label><br>
                        <input type="date" name="return_date" id="return_date" onchange="checkDateAvailability(); calculateTotalRent()" placeholder="Return Date" required>
                        </div>
                        <label for="cnum">Contact Number:</label><br>
                        <input type="number" name="cnum" id="cnum" placeholder="Contact Number" required>
                        <br>
                        <label for="id_card">ID Card:</label><br>
                        <select id="id_card" name="id_card" required>
                            <option value="none"></option>
                            <option value="Driver License">Driver License</option>
                            <option value="Passport ID">Passport ID</option>
                            <option value="TIN ID">TIN ID</option>
                            <option value="National ID">National ID</option>
                            <option value="Voter's ID">Voter's ID</option>
                        </select>
                        <br>
                        <label for="valid_id">Upload Valid ID</label><br>
                        <input type="file" name="valid_id" id="valid_id" required><br>

                        <label for="payment_status">Payment:</label><br>
                        <select id="payment_status" name="payment_status" required>
                            <option value="pending">On Hold</option>
                        </select>
                    </div>
                    <div class="submit--container">
                        <h2>Php: <?php echo htmlspecialchars($vehicleData['rent_price']); ?>.00</h2>
                        <div class="submit-button">
                            <button type="submit" name="submit" value="submit" onclick="openPopup()">Reserve Now</button>
                            <a href="user_reservation.php" class="detailsBtn">Cancel</a>
                        </div>
                    </div>
                    <div class="term--condition">
                    <p>By reserving the vehicle, you agree to the <a href="user_transaction_term_condition.php">Memorandum of Agreement and acknowledge</a> that you have read the policy and agreement.</p>
                    </div>
            </div>
            </form>
        </div>
    </div>
    <script>
            function changeImage(element) {
                const mainImage = document.getElementById('main-image');
                mainImage.src = element.src;
            }
            </script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="js/sweetalert.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="js/sweetalert.js"></script>
<script src="dropdown-filter.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="user.js"></script>
<script src="dropdown-filter.js"></script>
<script src="user.js"></script>
<script>
        function toggleNav() {
  const navLinks = document.getElementById("navLinks");
  navLinks.classList.toggle("show");
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Flatpickr for pickup date
        const conflictingReservations = <?php echo json_encode($conflictingReservations); ?>;

        // Function to generate an array of unavailable dates
        function getUnavailableDates() {
            const unavailableDates = [];

            conflictingReservations.forEach(reservation => {
                const reservedPickupDate = new Date(reservation.pickup_date);
                const reservedReturnDate = new Date(reservation.return_date);

                // Loop through all dates within the reserved range
                for (let date = new Date(reservedPickupDate); date <= reservedReturnDate; date.setDate(date.getDate() + 1)) {
                    unavailableDates.push(date.toISOString().split('T')[0]); // Add each date to the array
                }
            });

            return unavailableDates;
        }

        // Unavailable dates for the date picker
        const unavailableDates = getUnavailableDates();

        // Initialize Flatpickr for pickup date
        const pickupFlatpickr = flatpickr("#pickup_date", {
            minDate: "today", // Prevent selection of past dates
            disable: unavailableDates, // Disable unavailable dates
            dateFormat: "Y-m-d",
            onChange: updateReturnDateOptions // Check availability on pickup date change
        });

        // Initialize Flatpickr for return date
        const returnFlatpickr = flatpickr("#return_date", {
            minDate: "today", // Prevent selection of past dates
            disable: unavailableDates, // Disable unavailable dates
            dateFormat: "Y-m-d",
            onClose: checkDateAvailability // Check availability on close
        });

        // Function to update return date options based on selected pickup date
        function updateReturnDateOptions(selectedDates) {
            const pickupDate = selectedDates[0];

            if (pickupDate) {
                // Calculate the min return date (one day after the pickup date)
                const minReturnDate = new Date(pickupDate);
                minReturnDate.setDate(minReturnDate.getDate() + 1);

                // Set min date for return date
                returnFlatpickr.set('minDate', minReturnDate);

                // Disable dates before the min return date
                returnFlatpickr.set('disable', [...unavailableDates, ...getDatesBefore(minReturnDate)]);
            }
        }

        // Function to get all dates before a specified date
        function getDatesBefore(date) {
            const datesBefore = [];
            const today = new Date();

            for (let d = new Date(today); d < date; d.setDate(d.getDate() + 1)) {
                datesBefore.push(d.toISOString().split('T')[0]);
            }

            return datesBefore;
        }

        // Function to check date availability and calculate rent
        function checkDateAvailability() {
            const pickupDate = document.getElementById('pickup_date')._flatpickr.selectedDateStr;
            const returnDate = document.getElementById('return_date')._flatpickr.selectedDateStr;

            if (pickupDate && returnDate) {
                const pickup = new Date(pickupDate);
                const returnD = new Date(returnDate);

                // Check if return date is before pickup date
                if (returnD < pickup) {
                    document.getElementById('total_rent').innerText = 'Invalid dates';
                    return alert("Return date cannot be before pickup date.");
                }

                // Calculate total rent
                calculateTotalRent(pickupDate, returnDate);
            }
        }

        // Function to calculate total rent based on selected dates
        function calculateTotalRent(pickupDate, returnDate) {
            const rentPrice = parseFloat(document.getElementById('rent_price').value);
            if (pickupDate && returnDate) {
                const pickup = new Date(pickupDate);
                const returnD = new Date(returnDate);
                const timeDiff = returnD - pickup;
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);
                if (daysDiff >= 0) {
                    const totalRent = (daysDiff + 1) * rentPrice; // +1 to include the pickup date
                    document.getElementById('total_rent').innerText = 'Total Rent: PHP ' + totalRent.toFixed(2);
                } else {
                    document.getElementById('total_rent').innerText = 'Invalid dates';
                }
            }
        }
    });
</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="darkmode.js"></script>
    
</body>
</html>
