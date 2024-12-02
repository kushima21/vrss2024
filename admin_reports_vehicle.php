<?php
include('dbconnect.php');
require('fpdf/fpdf.php');

$isSuccess = false;
$message = "";

// Fetch data for bar chart (count of rentals per vehicle based on 'Completed' status)
$query = "SELECT vehicle_name, driver_name, rent_per_day, registration_plate, vehicle_status, COUNT(*) AS count
          FROM reservation_request 
          WHERE status = 'Completed' 
          GROUP BY vehicle_name, driver_name, rent_per_day, registration_plate, vehicle_status";
$result = mysqli_query($con, $query);

$labels = array();
$values = array();
$driver_names = array();
$rent_per_day = array();
$registration_plates = array();
$vehicle_statuses = array();

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['vehicle_name'];
    $values[] = (int) $row['count'];
    $driver_names[] = $row['driver_name'];
    $rent_per_day[] = $row['rent_per_day'];
    $registration_plates[] = $row['registration_plate'];
    $vehicle_statuses[] = $row['vehicle_status'];
}

$chart_data = json_encode([
    'labels' => $labels,
    'values' => $values,
]);

// Handle PDF download request
if (isset($_POST['download_pdf'])) {
    // Create instance of FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Get current date (Year-Month-Day format)
    $current_date = date('Y-m-d');

    // Title with current date
    $pdf->Cell(200, 10, 'VRSS Report - ' . $current_date, 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Vehicle Reports', 0, 1, 'C');
    // Table Header
    $pdf->SetFont('Arial', 'B', 10);

    // Define relative widths for each column (relative proportions, not actual pixel widths)
    $relativeWidths = [3, 3, 2, 2, 2, 2]; // Adjust the relative proportions as needed
    
    // Calculate actual widths based on proportions
    $pageWidth = 200; // A4 page width in mm
    $totalRelativeWidth = array_sum($relativeWidths);
    $cell_widths = array_map(function($width) use ($pageWidth, $totalRelativeWidth) {
        return ($width / $totalRelativeWidth) * $pageWidth;
    }, $relativeWidths);

    // Table Header with adjusted column widths
    $pdf->Cell($cell_widths[0], 10, 'Vehicle Name', 1, 0, 'C');
    $pdf->Cell($cell_widths[1], 10, 'Driver Assigned', 1, 0, 'C');
    $pdf->Cell($cell_widths[2], 10, 'Rent Per Day', 1, 0, 'C');
    $pdf->Cell($cell_widths[3], 10, 'Registration Plate', 1, 0, 'C');
    $pdf->Cell($cell_widths[4], 10, 'Vehicle Status', 1, 0, 'C'); // New column for vehicle status
    $pdf->Cell($cell_widths[5], 10, 'Rental Count', 1, 1, 'C');

    // Table Data
    $pdf->SetFont('Arial', '', 10);
    foreach ($labels as $index => $label) {
        $pdf->Cell($cell_widths[0], 10, $label, 1, 0, 'L');
        $pdf->Cell($cell_widths[1], 10, $driver_names[$index], 1, 0, 'L');
        $pdf->Cell($cell_widths[2], 10, number_format($rent_per_day[$index], 2), 1, 0, 'C');
        $pdf->Cell($cell_widths[3], 10, $registration_plates[$index], 1, 0, 'C');
        $pdf->Cell($cell_widths[4], 10, $vehicle_statuses[$index], 1, 0, 'C'); // Display vehicle status
        $pdf->Cell($cell_widths[5], 10, $values[$index], 1, 1, 'C');
    }

    // Output the PDF to the browser
    $pdf->Output('D', 'vehicle_rental_report.pdf');
    exit;
}
?>





   <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" 
          crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="admin_reports_vehicle.css?=<?php echo time(); ?>">
    
    <title>Admin Dashboard</title>
</head>
<body>
    <!-- Alert Message -->
    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                title: '<?php echo $isSuccess ? "Success" : "Error"; ?>',
                text: '<?php echo $message; ?>',
                icon: '<?php echo $isSuccess ? "success" : "error"; ?>'
            });
        </script>
    <?php endif; ?>

    <!-- Topbar -->
    <div class="container">
        <div class="topbar">
            <div class="logo">
                <h2>VRRS</h2>
            </div>
            <div class="search"></div>
            <i class="fa-solid fa-bell"></i>
            <div class="user">
                <img src="img/admin.png" alt="">
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <ul>
                <li class="key">
                    <a href="#">
                        <img src="img/car-key.png" alt="Key">
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php">
                        <i class="fa-solid fa-house"></i>
                        <span class="nav-item">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="admin_reservation.php">
                        <i class="fa-solid fa-key"></i>
                        <span class="nav-item">Reservation</span>
                    </a>
                </li>

                <li>
                    <a href="admin_vehicles.php">
                        <i class="fa-solid fa-car"></i>
                        <span class="nav-item">Vehicles Section</span>
                    </a>
                </li>
                <li>
                    <a href="admin_monitoring.php">
                        <i class="fa-solid fa-desktop"></i>
                        <span class="nav-item">Monitoring</span>
                    </a>
                </li>
                <li>
                    <a href="admin_customers.php">
                        <i class="fa-solid fa-users"></i>
                        <span class="nav-item">Customer Section</span>
                    </a>
                </li>
                <li>
                    <a href="admin_drivers.php">
                        <i class="fa-solid fa-id-card"></i>
                        <span class="nav-item">Driver Section</span>
                    </a>
                </li>
                <li>
                    <a href="admin_reports.php">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span class="nav-item">Reports</span>
                    </a>
                </li>
                <li>
                    <a href="admin_main_settings.php">
                        <i class="fa-solid fa-wrench"></i>
                        <span class="nav-item">Settings</span>
                    </a>
                </li>
            </ul>
            <div class="bottom-content">
                <ul>
                    <li>
                        <a href="admin_logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span class="nav-item">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

      <!-- Reports Section -->
<div class="admin--reports--main--container">
    <div class="admin--reports--container--box">
        <div class="admin--reports--header--container">
            <div class="admin--reports--header">
                <div class="reports--headers">
                    <h2>VRSS REPORTS</h2>
                </div>
                <div class="reports--header--url">
                    <a href="admin_reports.php"><i class="fas fa-user"></i></a>
                    <a href="admin_reports_vehicle.php"><i class="fas fa-car" style="color: red;"></i></a>
                    <a href="admin_calendar.php"><i class="fas fa-calendar"></i></a>
                </div>
            </div>

            <div class="admin--reports--graph--container">
                <div class="charts-card">
                <h2 class="chart-title">Top Vehicles Rented</h2>
                 <!-- Create a canvas element for Chart.js -->
                <canvas id="bar-chart" width="1200" height="280"></canvas>
                </div>
            </div>

            <!-- Vehicle Reservation Report Table -->
            <div class="admin--vehicle--reports--reservation">
    <table class="reservation-table">
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Vehicle Name</th>
                <th>Registration Plate</th>
                <th>Driver Assigned</th>
                <th>Rent per Day</th>
                <th>Usage</th>
                <th>Diuse</th> <!-- Added column for canceled reservation count -->
            </tr>
        </thead>
        <tbody>
        <?php
        // Initialize an array to track the count of completed and canceled reservations for each vehicle by vehicle_id
        $fetchReservations = "SELECT vehicle_id, 
                                     COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS completed_count,
                                     COUNT(CASE WHEN vehicle_status = 'Cancelled' THEN 1 END) AS cancelled_count
                              FROM reservation_request
                              GROUP BY vehicle_id";
        $reservationResult = mysqli_query($con, $fetchReservations);

        // Set default filters if not selected
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'month';
        $status_filter = isset($_GET['vehicle_status']) ? $_GET['vehicle_status'] : 'all';
        $selected_month = isset($_GET['month']) ? date('m', strtotime($_GET['month'])) : date('m');
        $selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        // Get the start and end date filters
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        // Start building the SQL query for filtering reservations
        $sql_conditions = [];
        $sql_conditions[] = "((MONTH(date_completed) = '$selected_month' AND YEAR(date_completed) = '$selected_year') 
                            OR (MONTH(cancel_time) = '$selected_month' AND YEAR(cancel_time) = '$selected_year'))";

        // Filter for the selected month and year
        if ($status_filter != 'all') {
            $sql_conditions[] = "vehicle_status = '$status_filter'";
        }

        // Filter by start and end date (if provided)
        if ($start_date && $end_date) {
            $sql_conditions[] = "(date_completed BETWEEN '$start_date' AND '$end_date')";
        }

        // Combine conditions
        $sql_query = "SELECT * FROM reservation_request";
        if (count($sql_conditions) > 0) {
            $sql_query .= " WHERE " . implode(' AND ', $sql_conditions);
        }

        // Execute the query
        $vehicleResult = mysqli_query($con, $sql_query);

        // Populate reservation counts (completed and canceled reservations)
        $vehicle_reservation_count = [];
        $vehicle_cancelled_count = [];
        while ($row = mysqli_fetch_assoc($reservationResult)) {
            $vehicle_reservation_count[$row['vehicle_id']] = $row['completed_count'];
            $vehicle_cancelled_count[$row['vehicle_id']] = $row['cancelled_count'];
        }

        // Initialize an array to track processed vehicle_ids
        $processedVehicles = [];

        // Loop through each vehicle
        while ($row = mysqli_fetch_assoc($vehicleResult)) {
            $vehicle_id = $row['vehicle_id'];

            // Skip this vehicle if it has already been processed
            if (in_array($vehicle_id, $processedVehicles)) {
                continue;
            }

            // Mark the vehicle as processed
            $processedVehicles[] = $vehicle_id;

            // Retrieve vehicle details
            $vehicle_name = $row['vehicle_name'];
            $driver_name = $row['driver_name'];
            $rent_per_day = $row['rent_per_day'];
            $registration_plate = $row['registration_plate'];
            $vehicle_status = $row['vehicle_status'];
            $status = $row['status'];

            // Get the completed and canceled reservation counts for this vehicle
            $reservation_count = isset($vehicle_reservation_count[$vehicle_id]) ? $vehicle_reservation_count[$vehicle_id] : 0;
            $cancelled_count = isset($vehicle_cancelled_count[$vehicle_id]) ? $vehicle_cancelled_count[$vehicle_id] : 0;

            // Output vehicle details and reservation counts
        ?>
            <tr>
                <td><?php echo $vehicle_id; ?></td>
                <td><?php echo $vehicle_name; ?></td>
                <td><?php echo $registration_plate; ?></td>
                <td><?php echo $driver_name; ?></td>
                <td><?php echo $rent_per_day; ?>.00</td>
                <td><?php echo $reservation_count; ?></td> <!-- Display the reservation count -->
                <td><?php echo $cancelled_count; ?></td> <!-- Display the canceled reservation count -->
            </tr>
        <?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- Filtering Section -->
<div class="admin--filtering--container">
    <form method="GET" action="">
        <div class="admin--filtering--box--container">
            <label for="vehicle_status">Status:</label>
            <select id="vehicle-status" name="vehicle_status">
                <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All</option>
                <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <br>
            <br>
            <label for="year">Select Year:</label><br>
            <select id="year" name="year">
                <?php
                // Generate a list of years from 2020 to the current year
                $current_year = date('Y');
                for ($year = 2020; $year <= $current_year; $year++) {
                    echo "<option value='$year' " . ($selected_year == $year ? 'selected' : '') . ">$year</option>";
                }
                ?>
            </select>
            <br>
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo isset($_GET['month']) ? $_GET['month'] : ''; ?>">
            <br>

            <label for="start_date">Start Date:</label><br>
            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>"><br>

            <label for="end_date">End Date:</label><br>
            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>"><br>
            <br>
            <button type="submit">Apply Filters</button>
        </div>
    </form>
    <br>
    <form method="post">
        <button type="submit" name="download_pdf">Download Report as PDF</button>  
    </form>
</div>

</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare the data for the chart from PHP
var chartData = <?php echo $chart_data; ?>;

// Create the chart using Chart.js
var ctx = document.getElementById('bar-chart').getContext('2d');
var barChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Rental Count',
            data: chartData.values,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
<script src="setting_report.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
