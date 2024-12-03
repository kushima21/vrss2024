<?php
session_start(); // Start session to access session variables

include('dbconnect.php'); // Include database connection
require('fpdf/fpdf.php');

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
        } else {
            // Redirect to login.php if status_id is not 2
            header("Location: login.php");
            exit();
        }
        
    } else {
        // Redirect to login.php if user is not found
        header("Location: login.php");
        exit();
    }
} else {
    // Redirect to login.php if user is not logged in
    header("Location: login.php");
    exit();
}

$reservationData = [ 'customer_name' => '', 'rent_price' => '', 'pickup_date' => '', 'return_date' => '', 'photo' => '', 'payment_status' => '', 'status' => '', 'seating_capacity' => '', 'driver_name' => '', 'driver_contact' => '', 'model' => '', 'vehicle_name' => '', 'cnum' => '', 'driver_contact', 'counting_days', 'request_time', 'reservation_id', 'status', 'customer_address','rent_per_day', 'request_time'];

if (isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $fetchReservationData = "SELECT * FROM reservation_request WHERE reservation_id = ?"; // Secure query with prepared statements
    $stmt = mysqli_prepare($con, $fetchReservationData);
    mysqli_stmt_bind_param($stmt, "i", $reservation_id);
    mysqli_stmt_execute($stmt);
    $reservation_Result = mysqli_stmt_get_result($stmt);

    if ($reservation_Result) {
        $reservation_Data = mysqli_fetch_assoc($reservation_Result);

        // Check if reservation data is found
        if ($reservation_Data) {
            // Create a new PDF document using FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            
// Set title font and style
$pdf->SetFont('Arial', '', 16);

// Align VRSS to the right
$pdf->Cell(90, 10, 'VRSS', 0, 0, 'R');

// Move to the next line
$pdf->Ln();

// Set regular font for location
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(190, 10, 'Baroy, Lanao del norte. PH', 0, 1, 'L'); 
$pdf->Cell(190, 10, 'Zip Code : 9210', 0, 1, 'L'); 

// Set smaller italic font for email and contact number
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(190, 10, 'vrssbaroy@gmail.com', 0, 1, 'L'); 
$pdf->Cell(190, 10, 'Contact Number : 093123131313', 0, 1, 'L');

// Add margin for the email and contact number (optional)
$pdf->Ln(5);  // Adds a small margin below the text for spacing

// Set font for the Invoice # and align it to the right
$pdf->SetFont('Arial', '', 12); 
$pdf->Cell(190, 10, 'INVOICE #', 0, 0, 'R'); // Align to the right, no line break after this cell

// Set font for reservation ID in italics
$pdf->SetFont('Arial', 'I', 12); 
$pdf->Cell(190, 10, $reservation_Data['reservation_id'], 0, 1); // Reservation ID in italics, with line break after


// Set font for the Invoice Date, italics, and align it to the right
$pdf->SetFont('Arial', 'I', 12); 
$pdf->Cell(190, 10, 'Invoice Date : ' . date('F j, Y'), 0, 1, 'R'); 

// Set font for customer details
$pdf->SetFont('Arial', '', 12);
// Customer Name
$pdf->Cell(100, 10, 'Customer Name: ' . $reservation_Data['customer_name'], 0, 0);
// Email
$pdf->Cell(100, 10, 'Email: ' . $reservation_Data['email'], 0, 1);
// Phone Number
$pdf->Cell(100, 10, 'Phone Number: ' . $reservation_Data['cnum'], 0, 1);
// Customer Address with italic font
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(100, 10, 'Customer Address:', 0, 0);
// Reset font to normal for the address
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, $reservation_Data['customer_address'], 0, 1);
// Optional: Add line break at the end if needed
$pdf->Ln(2);

// Page width and margins
$pageWidth = $pdf->GetPageWidth() - 10; // Subtract margins (10 on each side)

// Define the column widths
$leftColumnWidth = $pageWidth * 0.5;  // Left column width (for headers)
$rightColumnWidth = $pageWidth * 0.5; // Right column width (for results)

// Set title font and style
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Rental Summary', 0, 1, 'L'); // Set width to 0 for full page width
$pdf->Ln();
// Set font for the headers (left side of the table)
$pdf->SetFont('Arial', 'B', 10);

// Left Column: Header (left side)
$pdf->Cell($leftColumnWidth, 10, 'Vehicle Rented', 1, 0, 'L');

// Right Column: Vehicle Name (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['vehicle_name'], 1, 0, 'L');

// Move to the next line for data (for the model)
$pdf->Ln();

// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Model', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['model'], 1, 0, 'L');
$pdf->Ln(10);
// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Driver Assigned', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['driver_name'], 1, 0, 'L');
// Add some space after this section
$pdf->Ln(10);

// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Contact Number', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['driver_contact'], 1, 0, 'L');
// Add some space after this section
$pdf->Ln(10);
// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Rent Price', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, number_format($reservation_Data['rent_per_day'], 2), 1, 0, 'L');

// Add some space after this section
$pdf->Ln(10);
 
// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'PickUp Date', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['pickup_date'], 1, 0, 'L');
// Add some space after this section
$pdf->Ln(10);


// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Return Date', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['return_date'], 1, 0, 'L');
// Add some space after this section
$pdf->Ln(10);

// Set font for model (left column header)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($leftColumnWidth, 10, 'Day Rented', 1, 0, 'L');

// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($rightColumnWidth, 10, $reservation_Data['counting_days'] . ' Days', 1, 0, 'L');
// Add some space after this section
$pdf->Ln(10);


// Right Column: Model (right side)
$pdf->SetFont('Arial', '', 10);
// Format rent price to two decimal places
$formatted_price = number_format($reservation_Data['rent_price'], 2);
// Display the formatted rent price
$pdf->Cell(260, 10, 'Total Payment: Php ' . $formatted_price, 0, 1, 'C');
// Add some space after this section
$pdf->Ln(10);


$pdf->SetFont('Arial', '', 12);

// First Row: Signature Lines
$pdf->Cell(95, 10, '__________________________________', 0, 0, 'C'); // Driver signature line
$pdf->Cell(95, 10, '__________________________________', 0, 1, 'C'); // Customer signature line

// Second Row: Signature Labels
$pdf->Cell(95, 10, 'Driver Assigned Printed Signature', 0, 0, 'C'); // Driver signature label
$pdf->Cell(95, 10, 'Customer Printed Signature', 0, 1, 'C'); // Customer signature label


// Output the PDF as a download
$pdf->Output('D', 'reservation_details_' . $reservation_Data['reservation_id'] . '.pdf');
exit();

        } else {
            die("Reservation not found");
        }
    } else {
        die("Error fetching reservation data: " . mysqli_error($con));
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VRS RESERVATION</title>
    <link rel="stylesheet" href="user_dashboard.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_main_transaction_print.css?= <?php echo time();?>">
    <link rel="stylesheet" href="user_dashboardMood.css?= <?php echo time();?>">
    <link rel="stylesheet" href="float.css?= <?php echo time();?>">
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
<section>
        <div class="user--main--transaction">
            <div class="user--main--transaction--container">
                <div class="user--transaction--box--container">
                    <div class="user--transaction--box--header"><h1>My Reservation History</h1><span>Invoice ID:<?php echo htmlspecialchars($reservation_Data['reservation_id']); ?></span></div>
                    <div class="user--transaction--history--box">
                        <ul>
                            <li><i class="fa-solid fa-user"></i><a href="user_main_transaction_all.php">All</a></li>
                            <li><i class="fa-solid fa-user"></i><a href="user_main_transaction_history.php">Request Reservation</a></li>
                            <li><i class="fa-solid fa-user"></i><a href="user_main_transaction_topickup.php">To Pick</a></li>
                            <li><i class="fa-solid fa-user"></i><a href="user_main_transaction_travelling.php">Travelling</a></li>
                            <li><i class="fa-solid fa-user"></i><a href="user_main_transaction_completed.php">Completed</a></li>
                            <li><i class="fa-solid fa-clock-rotate-left"></i><a href="#">Cancelled</a></li>
                        </ul>
                    </div>
                    

                    <div class="user--transaction--link--container">
                    <?php
                    // Prepared statement for reservation request
                    $stmt = $con->prepare("SELECT * FROM reservation_request WHERE email = ? AND status = 'Approved'");
                    $stmt->bind_param("s", $loggedInEmail);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $reservation_id = $row['reservation_id'];
                            $vehicle_name = $row['vehicle_name'];
                            $photo = $row['photo'];
                            $status = $row['status'];
                            $rent_price = $row['rent_price'];
                            $rent_per_day = $row['rent_per_day'];
                            $counting_days = $row['counting_days'];
                            $model = $row['model'];
                            $seating_capacity= $row['seating_capacity'];
                    ?>
                        <div class="user--main--transaction--border--container">
                            <div class="user--print--details"><h1>Print Reservation Invoice</h1></div>
                        </div>
                        <div class="user--print--container">
    <div class="user--main--view--vehicles--info">
        <div class="user--main--view--vehicle--details">
            <div class="user--main--view--header--vehicle">
                <h1>Vehicles Details</h1>
                <p>Model : <?php echo htmlspecialchars($reservation_Data['model']); ?></p>
                <p>Vehicle Name : <?php echo htmlspecialchars($reservation_Data['vehicle_name']); ?></p>
                <p>Seating Capacity : <?php echo htmlspecialchars($reservation_Data['seating_capacity']); ?></p>
                <p>Drivers Assigned : <?php echo htmlspecialchars($reservation_Data['driver_name']); ?></p>
                <p>Contact Numbers : <?php echo htmlspecialchars($reservation_Data['driver_contact']); ?></p>
            </div>
        </div>
    </div>

    <div class="user--main--customers--info--container">
        <h1>Customers Form Details</h1>
        <div class="user--main--customers--form">
            <span><?php echo htmlspecialchars($reservation_Data['request_time']); ?></span>
            <p>Customers Name : <?php echo htmlspecialchars($reservation_Data['customer_name']); ?></p>
            <p>Address : <?php echo htmlspecialchars($reservation_Data['customer_address']); ?></p>
            <p>Contact Number: <?php echo htmlspecialchars($reservation_Data['cnum']); ?></p>
            <p>Pickup Date: <?php echo htmlspecialchars($reservation_Data['pickup_date']); ?></p>
            <p>Return Date : <?php echo htmlspecialchars($reservation_Data['return_date']); ?></p>
            <p>Day Rented : <?php echo htmlspecialchars($reservation_Data['counting_days']); ?> Days</p>
            <p>Reservation Status : <?php echo htmlspecialchars($reservation_Data['status']); ?></p>
        </div>
    </div>
</div>
        <div class="bottom--details">
            <div class="user--main--total--payment">
            <p>Total Payment: <span>Php: <?php echo htmlspecialchars($reservation_Data['rent_price']); ?>.00</span></p>
            </div>
            <div class="download--pdf">
                <form method="post">
                    <button type="submit" name="download_pdf">Download Report as PDF</button>  
                </form>
            </div>
            <div class="back--button">
                <a href="#"><button>Get Back</button></a>
            </div>
        </div>
        </div>
                    <?php
                        }
                    } else {
                        echo "<p style='text-align: center;'>No Reservations Request.</p>";
                    }
                    $stmt->close();
                    ?>
                </div>
            </div>
        </div>
</section>



     
        <script src=<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js" integrity="sha512-+2pW8xXU/rNr7VS+H62aqapfRpqFwnSQh9ap6THjsm41AxgA0MhFRtfrABS+Lx2KHJn82UOrnBKhjZOXpom2LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
      <script src="user.js"></script>
      <script src="darkmode.js"></script>
</div>
</body>
</html>