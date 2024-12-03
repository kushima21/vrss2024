<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection

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
       // Assuming status_id is part of the fetched data

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


if (isset($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];
    $vehicle_query = "SELECT * FROM vehicles_tbl WHERE id = $vehicle_id";
    $vehicle_result = mysqli_query($con, $vehicle_query);
    $vehicle_data = mysqli_fetch_assoc($vehicle_result);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_help_center.css?= <?php echo time();?>">
    <link rel="stylesheet" href="reservation.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            <h2 ><i class="fa-solid fa-key" ></i>VRRS</h2>
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
                                        <a href="user_main_transaction_history.php?viewed=true">
                                            <span class="option_profile">Transaction History</span>
                                            <?php if ($hasApprovedReservation): ?>
                                                <span class="notification-dot"></span> <!-- Red dot for unviewed approved reservations -->
                                            <?php endif; ?>
                                        </a>
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

<section class="home" id="home">
    <h1 class="home-parallax" data-speed="-2"></h1>
</section>
<div class="services_container" id="services-1">
<?php
$fetchadata = "SELECT * FROM vehicles_tbl";
$result = mysqli_query($con, $fetchadata);
while ($row = mysqli_fetch_assoc($result)){
    $id = $row['id'];
    $model = $row['model'];
    $vehicle_name = $row['vehicle_name'];
    $color = $row['color'];
    $registration_plate = $row['registration_plate'];
    $rent_price = $row['rent_price'];
    $driver_name = $row['driver_name'];
    $transmission = $row['transmission'];
    $main_image = $row['main_image'];
    $availability = $row['availability'];
    $vehicle_status = $row['vehicle_status'];
    $maintenance_start_time = $row['maintenance_start_time']; // Get the maintenance end time
    
    // Convert the maintenance end time to a JavaScript-compatible format
    $maintenance_start_time_js = date("Y-m-d\TH:i:s", strtotime($maintenance_start_time));
?>
<div class="reserve-container">
    <div class="front-face">
        <i class="fa-solid fa-download"></i>
        <img width="270px" height="200px" src="<?php echo $main_image; ?>" alt="">
        <div class="cover-name">
            <h3 id="vehicleName_<?php echo $id; ?>"><?php echo $vehicle_name; ?></h3>
            <h1 id="modelName_<?php echo $id; ?>"><?php echo $model; ?><hr></h1>
            <h1>Php: <?php echo $rent_price; ?>.00</h1>
            <h3>Unit Availability: <?php echo ($availability == 0) ? 'None' : $availability; ?> Slots</h3>
        </div>
        <div class="services_details">
            <button class="detailsBtn" id="<?php echo $id; ?>">More Details</button>
        </div>
    </div>
    <div class="back-face">
        <h2>Specifications</h2>
        <h3>Model: <?php echo $model; ?></h3>
        <h3>Vehicle Name: <?php echo $vehicle_name?></h3>
        <h3>Color: <?php echo $color; ?></h3>
        <h3>Registration Plate: <?php echo $registration_plate; ?></h3>
        <h3>Transmission: <?php echo $transmission; ?></h3>
        <h3>Driver Assigned: <?php echo $driver_name ?> </h3>
        <div class="services_details">
            <?php if ($vehicle_status == 'Maintenance'): ?>
                <button class="reserveBtn" id="maintenanceBtn_<?php echo $id; ?>" disabled>Under Maintenance</button>
            <?php elseif ($availability == 0): ?>
                <button class="reserveBtn" disabled>Fully Booked</button>
            <?php else: ?>
                <a href="user_reservationForm_details.php?vehicle_id=<?php echo $id; ?>">
                    <button class="reserveBtn">Reserve Now</button>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
// JavaScript to update the countdown timer if vehicle is under maintenance
<?php if ($vehicle_status == 'Maintenance'): ?>
    const maintenanceEndTime = new Date("<?php echo $maintenance_end_time_js; ?>").getTime();
    const maintenanceBtn = document.getElementById("maintenanceBtn_<?php echo $id; ?>");

    function updateTimer() {
        const now = new Date().getTime();
        const timeLeft = maintenanceEndTime - now;

        if (timeLeft <= 0) {
            // Display "24:00:00" when maintenance is over
            maintenanceBtn.innerHTML = "Under Maintenance";
            maintenanceBtn.disabled = false;
            clearInterval(timerInterval);
        } else {
            const hours = Math.floor((timeLeft / (1000 * 60 * 60))); // Get hours
            const minutes = Math.floor((timeLeft / (1000 * 60)) % 60); // Get minutes
            const seconds = Math.floor((timeLeft / 1000) % 60); // Get seconds

            // Format time as "HH:MM:SS"
            const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            maintenanceBtn.innerHTML = `Under Maintenance (Time Left: ${formattedTime})`;
        }
    }

    // Update the timer every second
    const timerInterval = setInterval(updateTimer, 1000);
<?php endif; ?>
</script>
<?php } ?>
</div>
<div class="description1">
        <p>We offer a wide range  of vehicle to suit your needs, whether you're moving, transporting goods, or simply need a driver for simple vacation.</p>
        <p>Our company well-maintained locally safe. With flexible rental option and    affordable rates, we make it easy for you to get behind the wheel and get on your day.</p>

</div>
   
    <footer>
        <div class="footer_copyrigth">
            &copy; Van_Reservation. All Rights Reserved.
        </div>
    </footer>
<script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <script src="user.js"></script>
      <script src="darkmode.js"></script>
      <script>
        function toggleNav() {
  const navLinks = document.getElementById("navLinks");
  navLinks.classList.toggle("show");
}
      </script>
    
</body>
</html>