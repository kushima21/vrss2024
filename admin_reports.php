
<?php
    include('dbconnect.php');
    require('fpdf/fpdf.php');

    $isSuccess = false;
    $message = "";

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch count of completed reservations
    $sql = "SELECT COUNT(*) as completed_count FROM reservation_request WHERE status = 'Completed'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $payment_complete = $row["completed_count"];
    } else {
        $payment_complete = 0;
    }

    // Fetch count of cancelled transactions
    $sql = "SELECT COUNT(*) as cancelled_count FROM reservation_request WHERE status = 'Cancelled'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $payment_cancelled = $row["cancelled_count"];
    } else {
        $payment_cancelled = 0;
    }

    // Fetch count of customers with Approved and Travelling status
    $sql = "SELECT COUNT(*) as approved_count FROM reservation_request WHERE status = 'Approved'";
    $result = $con->query($sql);
    $approved_count = ($result->num_rows > 0) ? $result->fetch_assoc()['approved_count'] : 0;

    $sql = "SELECT COUNT(*) as travelling_count FROM reservation_request WHERE status = 'Traveling'";
    $result = $con->query($sql);
    $travelling_count = ($result->num_rows > 0) ? $result->fetch_assoc()['travelling_count'] : 0;

    $customers_count = $approved_count + $travelling_count; // Total customers

    // Fetch data for bar chart (count of rentals per vehicle)
    $query = "SELECT vehicle_name, COUNT(*) AS count FROM reservation_request GROUP BY vehicle_name";
    $result = mysqli_query($con, $query);

    $data = array();
    $labels = array();
    $values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['vehicle_name'];
        $values[] = (int) $row['count'];
    }

    $chart_data = json_encode([
        'labels' => $labels,
        'values' => $values,
    ]);

    // Fetch data for area chart (monthly completed rentals)
    $query = "SELECT DATE_FORMAT(date_completed, '%Y-%m') AS month_year, COUNT(*) AS count 
            FROM reservation_request 
            WHERE status = 'Completed' 
            GROUP BY month_year 
            ORDER BY month_year ASC";
    $result = mysqli_query($con, $query);

    $monthly_labels = array();
    $monthly_values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $monthly_labels[] = $row['month_year'];
        $monthly_values[] = (int) $row['count'];
    }

    $monthly_chart_data = json_encode([
        'labels' => $monthly_labels,
        'values' => $monthly_values,
    ]);
    // Fetch data for area chart (monthly completed rentals)
    $query = "SELECT DATE(date_completed) AS date, COUNT(*) AS count 
            FROM reservation_request 
            WHERE status = 'Completed' 
            GROUP BY date 
            ORDER BY date ASC";
    $result = mysqli_query($con, $query);

    $daily_labels = array();
    $daily_values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $daily_labels[] = $row['date'];
        $daily_values[] = (int) $row['count'];
    }

    $daily_chart_data = json_encode([
        'labels' => $daily_labels,
        'values' => $daily_values,
    ]);
    $query = "SELECT vehicle_name, COUNT(*) AS count FROM reservation_request GROUP BY vehicle_name";
    $result = mysqli_query($con, $query);

    $data = array();
    $labels = array();
    $values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['vehicle_name'];
        $values[] = (int) $row['count'];
    }

    $chart_data = json_encode([
        'labels' => $labels,
        'values' => $values,
    ]);

    // New query for daily rentals
    $daily_query = "SELECT vehicle_name, DATE(pickup_date) AS rental_date, COUNT(*) AS count 
                    FROM reservation_request 
                    GROUP BY vehicle_name, rental_date";
    $daily_result = mysqli_query($con, $daily_query);

    $daily_labels = array();
    $daily_values = array();

    while ($row = mysqli_fetch_assoc($daily_result)) {
        $daily_labels[] = $row['rental_date']; // Just the date for daily rentals
        $daily_values[] = (int) $row['count']; // Count of rentals for that date
    }

    $daily_chart_data = json_encode([
        'labels' => $daily_labels,
        'values' => $daily_values,
    ]);

    // Today's date
    $today = date('Y-m-d');

    // Fetch daily revenue (today's rentals)
    $dailyQuery = "SELECT SUM(rent_price) as daily_revenue FROM reservation_request WHERE DATE(date_completed) = '$today'";
    $dailyResult = $con->query($dailyQuery);
    $dailyRevenue = $dailyResult->fetch_assoc()['daily_revenue'];

    // Fetch weekly revenue (rentals in the last 7 days)
    $weeklyQuery = "SELECT SUM(rent_price) as weekly_revenue FROM reservation_request WHERE date_completed >= NOW() - INTERVAL 7 DAY";
    $weeklyResult = $con->query($weeklyQuery);
    $weeklyRevenue = $weeklyResult->fetch_assoc()['weekly_revenue'];

    // Fetch monthly revenue (rentals in the last 30 days)
    $monthlyQuery = "SELECT SUM(rent_price) as monthly_revenue FROM reservation_request WHERE date_completed >= NOW() - INTERVAL 30 DAY";
    $monthlyResult = $con->query($monthlyQuery);
    $monthlyRevenue = $monthlyResult->fetch_assoc()['monthly_revenue'];

    // Fetch total revenue
    $totalQuery = "SELECT SUM(rent_price) as total_revenue FROM reservation_request";
    $totalResult = $con->query($totalQuery);
    $totalRevenue = $totalResult->fetch_assoc()['total_revenue'];

    // Fetch data for weekly completed rentals including month name
    $query = "SELECT YEAR(pickup_date) AS year, MONTH(pickup_date) AS month, WEEK(pickup_date, 1) AS week, COUNT(*) AS count 
            FROM reservation_request 
            WHERE status = 'Completed' 
            GROUP BY year, month, week 
            ORDER BY year, month, week ASC";

    $result = mysqli_query($con, $query);

    $weekly_labels = array();
    $weekly_values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        // Extract the data
        $week_number = $row['week'];
        $year = $row['year'];
        $month_num = $row['month'];
        
        // Convert the month number to a month name
        $month_name = DateTime::createFromFormat('!m', $month_num)->format('F'); // Get full month name (e.g., "January")
        
        // Format the label to show the month, week number, and year
        $weekly_labels[] = $month_name . ' ' . $year . ' - Week ' . $week_number; // Example: "January 2024 - Week 1"
        $weekly_values[] = (int) $row['count'];
    }

    $weekly_chart_data = json_encode([
        'labels' => $weekly_labels,
        'values' => $weekly_values,
    ]);

    // Fetch data for monthly completed rentals
    $query = "SELECT YEAR(pickup_date) AS year, MONTH(pickup_date) AS month, COUNT(*) AS count 
            FROM reservation_request 
            WHERE status = 'Completed' 
            GROUP BY year, month 
            ORDER BY year, month ASC";
    $result = mysqli_query($con, $query);

    $monthly_labels = array();
    $monthly_values = array();

    while ($row = mysqli_fetch_assoc($result)) {
        // Format the label to show the month and year
        $month_number = $row['month'];
        $year = $row['year'];

        // Convert month number to month name (e.g., 1 -> January, 2 -> February)
        $month_name = date("F", mktime(0, 0, 0, $month_number, 10));
        $monthly_labels[] = $month_name . ' ' . $year; // Example: "January 2024"
        $monthly_values[] = (int) $row['count'];
    }

    $monthly_chart_data = json_encode([
        'labels' => $monthly_labels,
        'values' => $monthly_values,
    ]);

    $completed_reservations = fetch_completed_reservations($con);

    // Fetch statistics
    function fetch_count($con, $table, $condition) {
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $condition";
        $result = $con->query($sql);
        return ($result->num_rows > 0) ? $result->fetch_assoc()["count"] : 0;
    }

    function fetch_sum($con, $table, $column, $condition) {
        $sql = "SELECT SUM($column) as total FROM $table WHERE $condition";
        $result = $con->query($sql);
        return ($result->num_rows > 0) ? $result->fetch_assoc()["total"] : 0;
    }

    $available_vehicles = fetch_count($con, 'vehicles_tbl', "vehicle_status = 'Available'");
    $payment_complete = fetch_count($con, 'reservation_request', "status = 'Completed'");
    $payment_cancelled = fetch_count($con, 'reservation_request', "status = 'Cancelled'");
    $approved_count = fetch_count($con, 'reservation_request', "status = 'Approved'");
    $travelling_count = fetch_count($con, 'reservation_request', "status = 'Traveling'");
    $customers_count = $approved_count + $travelling_count;

    $total_revenue = fetch_sum($con, 'reservation_request', 'rent_price', "status = 'Completed'");

    function fetch_completed_reservations($con) {
        $sql = "SELECT customer_name, vehicle_name, driver_name, pickup_date, return_date, date_completed, rent_price 
                FROM reservation_request 
                WHERE status = 'Completed'";
        $result = $con->query($sql);
        $reservations = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
        } else {
            echo "Error in SQL query: " . $con->error;
        }
        return $reservations;
    }

    // Correct array naming
    $driver_names = array();  // Renamed to avoid overwriting

    // Fetch data for most rented vehicles including registration_plate and driver_name
    $query = "SELECT rr.vehicle_name, rr.registration_plate, d.driver_name, COUNT(*) AS count
            FROM reservation_request rr
            LEFT JOIN vehicles_tbl d ON rr.driver_id = d.driver_id
            GROUP BY rr.vehicle_name, rr.registration_plate, d.driver_name";  // Correct group by

    $result = mysqli_query($con, $query);

    // Check if the query was successful
    if (!$result) {
        die('Query failed: ' . mysqli_error($con));
    }

    $data = array();
    $labels = array();
    $values = array();
    $driver_names = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['vehicle_name'];
        $values[] = (int) $row['count'];
        $data[] = $row['registration_plate'];
        $driver_names[] = $row['driver_name'];  // Store the driver name
    }


    ob_start();
    if (isset($_POST['download_pdf'])) {
        // Get filter parameters from the GET request
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'month';
        $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all'; // Get status filter from URL (all, completed, cancelled, etc.)
        $selected_month = isset($_GET['month']) ? date('m', strtotime($_GET['month'])) : date('m');
        $selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    
        // Initialize the SQL conditions array
        $sql_conditions = [];
    
        // Filter by month and year for the appropriate date fields based on status
        if (!empty($selected_month)) {
            if ($status_filter == 'Approved') {
                $sql_conditions[] = "(MONTH(approved_date) = '$selected_month' AND YEAR(approved_date) = '$selected_year')";
            } elseif ($status_filter == 'Traveling') {
                $sql_conditions[] = "(MONTH(traveling_date) = '$selected_month' AND YEAR(traveling_date) = '$selected_year')";
            } elseif ($status_filter == 'Completed') {
                $sql_conditions[] = "(MONTH(date_completed) = '$selected_month' AND YEAR(date_completed) = '$selected_year')";
            } elseif ($status_filter == 'Cancelled') {
                $sql_conditions[] = "(MONTH(cancel_time) = '$selected_month' AND YEAR(cancel_time) = '$selected_year')";
            }
        } else {
            // Filter for year only if no month is selected and based on status
            if ($status_filter == 'Approved') {
                $sql_conditions[] = "(YEAR(approved_date) = '$selected_year')";
            } elseif ($status_filter == 'Traveling') {
                $sql_conditions[] = "(YEAR(traveling_date) = '$selected_year')";
            } elseif ($status_filter == 'Completed') {
                $sql_conditions[] = "(YEAR(date_completed) = '$selected_year')";
            } elseif ($status_filter == 'Cancelled') {
                $sql_conditions[] = "(YEAR(cancel_time) = '$selected_year')";
            }
        }
    
        // Add status filter if it's not 'all'
        if ($status_filter != 'all') {
            $sql_conditions[] = "status = '$status_filter'";
        }
    
        // Filter by date range (start_date and end_date)
        if ($start_date && $end_date) {
            if ($status_filter == 'Approved') {
                $sql_conditions[] = "(approved_date >= '$start_date' AND approved_date <= '$end_date')";
            } elseif ($status_filter == 'Traveling') {
                $sql_conditions[] = "(traveling_date >= '$start_date' AND traveling_date <= '$end_date')";
            } elseif ($status_filter == 'Completed') {
                $sql_conditions[] = "(date_completed >= '$start_date' AND date_completed <= '$end_date')";
            } elseif ($status_filter == 'Cancelled') {
                $sql_conditions[] = "(cancel_time >= '$start_date' AND cancel_time <= '$end_date')";
            }
        }
    
        // Build the SQL query
        $sql_query = "SELECT * FROM reservation_request";
        if (count($sql_conditions) > 0) {
            $sql_query .= " WHERE " . implode(' AND ', $sql_conditions);
        }

    // Execute the query
    $result = mysqli_query($con, $sql_query);
    
        // Calculate total revenue
        $totalRevenue = 0;
        $revenueQuery = "SELECT SUM(rent_price) as total_revenue FROM reservation_request";
        if (count($sql_conditions) > 0) {
            $revenueQuery .= " WHERE " . implode(' AND ', $sql_conditions);
        }
        $revenueResult = mysqli_query($con, $revenueQuery);
        if ($revenueRow = mysqli_fetch_assoc($revenueResult)) {
            $totalRevenue = $revenueRow['total_revenue'];
        }
    
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
    
        // Title
        $pdf->Cell(0, 10, 'VRSS Reservation Report', 0, 1, 'C');
        $pdf->Ln(1);
    
        // Add filters information to the PDF
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, 'Filters Used:', 0, 1, 'L');
    
        // Add Year
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Year: ' . $selected_year, 0, 1, 'L');
    
        // Add Month
        $monthName = date('F', mktime(0, 0, 0, $selected_month, 10)); // Get the full month name
        $pdf->Cell(0, 10, 'Month: ' . $monthName, 0, 1, 'L');
    
        // Add Status Filter
        $pdf->Cell(0, 10, 'Status: ' . $status_filter, 0, 1, 'L');
    
        // Add Date Range (if applicable)
        if ($start_date && $end_date) {
            $pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'L');
        } else {
            $pdf->Cell(0, 10, 'Date Range: Not Specified', 0, 1, 'L');
        }
    
        // Add a line break for spacing before the table
        $pdf->Ln(5);

        // Get the page width and adjust for margins
        $pageWidth = $pdf->GetPageWidth() - 20; // Subtract margins (10 on each side)

        // Define relative widths for columns (proportions)
        $relativeWidths = [
            3, // Customer Name
            3, // Vehicle Rented
            3, // Driver Assigned
            2, // Pickup Date
            2, // Return Date
            1, // Days
            2, // Rent Price
            2  // Status
        ];

        // Calculate actual widths based on proportions
        $totalRelativeWidth = array_sum($relativeWidths);
        $headerWidths = array_map(function($width) use ($pageWidth, $totalRelativeWidth) {
            return ($width / $totalRelativeWidth) * $pageWidth;
        }, $relativeWidths);

        // Table headers
        $pdf->SetFont('Arial', 'B', 8); // Set font for headers
        $pdf->Cell($headerWidths[0], 10, 'Customer Name', 1);
        $pdf->Cell($headerWidths[1], 10, 'Vehicle Rented', 1);
        $pdf->Cell($headerWidths[2], 10, 'Driver Assigned', 1);
        $pdf->Cell($headerWidths[3], 10, 'Pickup Date', 1);
        $pdf->Cell($headerWidths[4], 10, 'Return Date', 1);
        $pdf->Cell($headerWidths[5], 10, 'Days', 1);
        $pdf->Cell($headerWidths[6], 10, 'Rent Price', 1);
        $pdf->Cell($headerWidths[7], 10, 'Status', 1);
        $pdf->Ln();

        // Populate table data
        if (mysqli_num_rows($result) > 0) {
            $pdf->SetFont('Arial', '', 8); // Match font size with headers
            while ($row = mysqli_fetch_assoc($result)) {
                $pdf->Cell($headerWidths[0], 10, $row['customer_name'], 1);
                $pdf->Cell($headerWidths[1], 10, $row['vehicle_name'], 1);
                $pdf->Cell($headerWidths[2], 10, $row['driver_name'], 1);
                $pdf->Cell($headerWidths[3], 10, $row['pickup_date'], 1);
                $pdf->Cell($headerWidths[4], 10, $row['return_date'], 1);
                $pdf->Cell($headerWidths[5], 10, $row['counting_days'], 1);
                $pdf->Cell($headerWidths[6], 10, 'Php: ' . number_format($row['rent_price'], 2), 1);
                $pdf->Cell($headerWidths[7], 10, $row['status'], 1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell($pageWidth, 10, 'No data found for the selected filters.', 1, 1, 'C');
        }

        // Add total revenue at the bottom
    $pdf->SetFont('Arial', 'B', 10);

    // Merge all columns for the header to align it to the left
    $pdf->Cell(array_sum($headerWidths), 10, 'Total Revenue:', 1, 0, 'L');

    // Display the total revenue value in a separate cell, right-aligned
    $pdf->Cell(0, 10, 'Php: ' . number_format($totalRevenue, 2), 0, 1, 'R');

        // Output PDF
        $pdf->Output('D', 'Reservation_Report.pdf');
        exit;
    }

    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <link rel="stylesheet" href="admin_reports.css?= <?php echo time();?>">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            <div class="search"></div>
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
            <div class="admin--reports--main--container">
                <div class="admin--reports--container--box">
                    <div class="admin--reports--header--container">
                        <div class="admin--reports--header">
                            <div class="reports--headers"><h2>VRSS REPORTS</h2></div>
                            <div class="reports--header--url">
                                <a href="admin_reports.php"><i class="fas fa-user" style="color: red;"></i></a>
                                <a href="admin_reports_vehicle.php"><i class="fas fa-car"></i></a>
                                <a href="admin_calendar.php"><i class="fas fa-calendar"></i></a>
                            </div>
                        </div>

                        <div class="admin--reports--graph--container">
                            <div class="charts-card">
                            <h2 class="chart-title">Daily Reports</h2>
                            <div id="daily-chart"></div>
                            </div>
                            
                            <div class="charts-card">   
                            <h2 class="chart-title">Weekly Reports</h2>
                                <div id="weekly-chart"></div>
                            </div>

                            <div class="charts-card">   
                            <h2 class="chart-title">Monthly Reports</h2>
                            <div id="monthly-chart"></div>
                            </div>
                        </div>
                        <div class="admin--reports--header--reservation">
                <table border="2" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Vehicle Rented</th>
                    <th>Driver Assigned</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Rented Days</th>
                    <th>Rent Price</th>
                    <th>Date Completed</th>
                    <th>Date Cancelled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
// Set default filters if not selected
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'month';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$selected_month = isset($_GET['month']) && !empty($_GET['month']) ? date('m', strtotime($_GET['month'])) : '';
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Get selected year from GET request

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Start building the SQL query
$sql_conditions = [];

// Filter by year only if no month is selected
if (!empty($selected_month)) {
    $sql_conditions[] = "(
        (MONTH(request_time) = '$selected_month' AND YEAR(request_time) = '$selected_year') OR
        (MONTH(approved_date) = '$selected_month' AND YEAR(approved_date) = '$selected_year') OR
        (MONTH(traveling_date) = '$selected_month' AND YEAR(traveling_date) = '$selected_year') OR
        (MONTH(date_completed) = '$selected_month' AND YEAR(date_completed) = '$selected_year') OR
        (MONTH(cancel_time) = '$selected_month' AND YEAR(cancel_time) = '$selected_year')
    )";
} else {
    // Filter for year only
    $sql_conditions[] = "(
        YEAR(request_time) = '$selected_year' OR
        YEAR(approved_date) = '$selected_year' OR
        YEAR(traveling_date) = '$selected_year' OR
        YEAR(date_completed) = '$selected_year' OR
        YEAR(cancel_time) = '$selected_year'
    )";
}

// Add status-specific conditions
if ($status_filter != 'all') {
    switch ($status_filter) {
        case 'Pending':
            $sql_conditions[] = "request_time IS NOT NULL AND 
                                 approved_date IS NULL AND 
                                 cancel_time IS NULL";
            break;
        case 'Approved':
            $sql_conditions[] = "approved_date IS NOT NULL AND 
                                 traveling_date IS NULL";
            break;
        case 'Traveling':
            $sql_conditions[] = "traveling_date IS NOT NULL AND 
                                 date_completed IS NULL";
            break;
        case 'Completed':
            $sql_conditions[] = "date_completed IS NOT NULL";
            break;
        case 'Cancelled':
            $sql_conditions[] = "cancel_time IS NOT NULL";
            break;
    }
}

// Filter by date range if both are set
if (!empty($start_date) && !empty($end_date)) {
    $sql_conditions[] = "(
        (request_time BETWEEN '$start_date' AND '$end_date') OR
        (approved_date BETWEEN '$start_date' AND '$end_date') OR
        (traveling_date BETWEEN '$start_date' AND '$end_date') OR
        (date_completed BETWEEN '$start_date' AND '$end_date') OR
        (cancel_time BETWEEN '$start_date' AND '$end_date')
    )";
}

// Combine conditions
$sql_query = "SELECT * FROM reservation_request";
if (!empty($sql_conditions)) {
    $sql_query .= " WHERE " . implode(' AND ', $sql_conditions);
}

// Execute the query
$result = mysqli_query($con, $sql_query);
$total_revenue = 0;

// Display results if data exists
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_revenue += $row['rent_price'];
        echo "<tr>
            <td>{$row['customer_name']}</td>
            <td>{$row['vehicle_name']}</td>
            <td>{$row['driver_name']}</td>
            <td>{$row['pickup_date']}</td>
            <td>{$row['return_date']}</td>
            <td>{$row['counting_days']} Days</td>
            <td>Php: {$row['rent_price']}.00</td>
            <td>{$row['date_completed']}</td>
            <td>{$row['cancel_time']}</td>
            <td>{$row['status']}</td>
        </tr>";
    }
} else {
    // Display a single row indicating no data
    echo "<tr><td colspan='8' style='text-align: center;'>No data reports found.</td></tr>";
}

// Display total revenue only if data exists
?>



        </table>
    </div>
    <div class="total--container">
        <h2>Total Revenue:</h2>
        <span>Php:  <?php echo number_format($total_revenue, 2); ?></span>
    </div>

                    </div>
                </div>
                <div class="admin--filtering--container">
                <form method="GET" action="">
                <div class="admin--filtering--box--container">
                <div class="select--option">
                <div class="status--filter">
                    <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Traveling" <?php echo ($status_filter === 'Traveling') ? 'selected' : ''; ?>>Traveling</option>
                            <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
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

    <script>
    const calendarElement = document.querySelector('.calendar');
    const selectElement = document.getElementById('date-filter')

    // Function to create the calendar
    function createCalendar(year, month) {
        const date = new Date(year, month);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDay = date.getDay();

        let calendarHTML = `
            <div class="calendar-header">
                <button onclick="prevMonth()">&#8592;</button>
                <h3>${date.toLocaleString('default', { month: 'long' })} ${year}</h3>
                <button onclick="nextMonth()">&#8594;</button>
            </div>
            <div class="calendar-days">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
        `;

        // Empty slots before the first day
        for (let i = 0; i < firstDay; i++) {
            calendarHTML += `<div></div>`;
        }

        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const today = new Date();
            const isToday = day === today.getDate() && year === today.getFullYear() && month === today.getMonth();
            const selectedDay = day < 10 ? '0' + day : day;
            const selectedDate = `${year}-${month + 1 < 10 ? '0' + (month + 1) : month + 1}-${selectedDay}`;
            
            calendarHTML += `<div class="${isToday ? 'today' : ''}" onclick="selectDate('${selectedDate}')">${day}</div>`;
        }

        calendarHTML += `</div>`;
        calendarElement.innerHTML = calendarHTML;
    }

    // Handle previous month navigation
    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        createCalendar(currentYear, currentMonth);
    }

    // Handle next month navigation
    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        createCalendar(currentYear, currentMonth);
    }

    // Function to select a date and filter reservations
    function selectDate(date) {
        const filter = selectElement.value;
        window.location.href = `?date=${date}&filter=${filter}`; // Redirect with selected date and filter
    }

    // Initialize Calendar
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();
    createCalendar(currentYear, currentMonth);

    // Handle filter change
    selectElement.addEventListener('change', function() {
        const filter = this.value;
        const date = new Date(currentYear, currentMonth).toISOString().split('T')[0];
        window.location.href = `?date=${date}&filter=${filter}`; // Redirect with filter
    });
    </script>

    <script>
    // Set revenue values dynamically with two decimal places
    document.getElementById('daily-revenue').innerText = '<?php echo number_format($dailyRevenue, 2, '.', ''); ?>';
    document.getElementById('weekly-revenue').innerText = '<?php echo number_format($weeklyRevenue, 2, '.', ''); ?>';
    document.getElementById('monthly-revenue').innerText = '<?php echo number_format($monthlyRevenue, 2, '.', ''); ?>';
    document.getElementById('total-revenue').innerText = '<?php echo number_format($totalRevenue, 2, '.', ''); ?>';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
    <script>
        // Fetch the chart data from PHP
        const chartData = <?php echo $chart_data; ?>;
    const dailyChartData = <?php echo $daily_chart_data; ?>; // Make sure this is defined in your PHP
    const weeklyChartData = <?php echo $weekly_chart_data; ?>; // Ensure this is also available

    // Area Chart for Daily Completed Rentals
    const dailyAreaChartOptions = {
        series: [{
            name: 'Completed Rentals',
            data: dailyChartData.values,
        }],
        chart: {
            type: 'area',
            height: 300,
            background: '',
            toolbar: {
                show: false,
            },
        },
        colors: ['#2962ff'],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        grid: {
            borderColor: '#55596e',
            yaxis: {
                lines: { show: true },
            },
            xaxis: {
                lines: { show: true },
            },
        },
        xaxis: {
        categories: dailyChartData.labels.map(date => {
            const formattedDate = new Date(date);
            return formattedDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',  // Display full month name
                day: '2-digit'
            });
        }),
        title: { style: { color: '#f5f7ff' } },
        axisBorder: { show: true, color: '#55596e' },
        axisTicks: { show: true, color: '#55596e' },
        labels: { style: { colors: '#f5f7ff' } },
        },
        yaxis: {
            title: { text: 'Rentals', style: { color: '#f5f7ff' } },
            axisBorder: { color: '#55596e', show: true },
            axisTicks: { color: '#55596e', show: true },
            labels: { style: { colors: '#f5f7ff' } },
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'dark',
        },
        fill: {
            opacity: 0.8,
            gradient: {
                shade: 'dark',
                type: 'vertical',
                shadeIntensity: 0.3,
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 0.1,
                stops: [0, 90, 100],
            },
        },
    };

    // Render Daily Chart
    const dailyAreaChart = new ApexCharts(
        document.querySelector('#daily-chart'),
        dailyAreaChartOptions
    );
    dailyAreaChart.render();

    // Area Chart for Weekly Completed Rentals
    const weeklyAreaChartOptions = {
        series: [{
            name: 'Completed Rentals',
            data: weeklyChartData.values, // Data for the chart
        }],
        chart: {
            type: 'area',
            height: 280,
            background: '',
            toolbar: {
                show: false,
            },
        },
        colors: ['#ff5722'], // You can use a different color for the weekly chart
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 10,
        },
        grid: {
            borderColor: '#55596e',
            yaxis: {
                lines: { show: true },
            },
            xaxis: {
                lines: { show: true },
            },
        },
        xaxis: {
            categories: weeklyChartData.labels.map(week => {
                // Format the label to include "Month Year - Week X"
                return week; // Directly use the formatted labels from PHP
            }),
            title: { style: { color: '#f5f7ff' } },
            axisBorder: { show: true, color: '#55596e' },
            axisTicks: { show: true, color: '#55596e' },
            labels: { style: { colors: '#f5f7ff' } },
        },
        yaxis: {
            title: { text: 'Rentals', style: { color: '#f5f7ff' } },
            axisBorder: { color: '#55596e', show: true },
            axisTicks: { color: '#55596e', show: true },
            labels: { style: { colors: '#f5f7ff' } },
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'dark',
        },
        fill: {
            opacity: 0.8,
            gradient: {
                shade: 'dark',
                type: 'vertical',
                shadeIntensity: 0.3,
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 0.1,
                stops: [0, 90, 100],
            },
        },
    };

    // Render Weekly Chart
    const weeklyAreaChart = new ApexCharts(
        document.querySelector('#weekly-chart'),
        weeklyAreaChartOptions
    );
    weeklyAreaChart.render();

    // Bar Chart for Rentals
    const barChartOptions = {
            series: [{
                data: chartData.values,
                name: 'Rentals',
            }],
            chart: {
                type: 'bar',
                background: 'static',
                height: 350,
                toolbar: {
                    show: false,
                },
            },
            colors: ['#1962ff', '#d50000', '#2e7d32', '#ff6d00', '#583cb3'],
            plotOptions: {
                bar: {
                    distributed: true,
                    borderRadius: 4,
                    horizontal: false,
                    columnWidth: '40%',
                },
            },
            dataLabels: {
                enabled: false,
            },
            fill: {
                opacity: 1,
            },
            grid: {
                borderColor: '#15296e',
                yaxis: {
                    lines: {
                        show: true,
                    },
                },
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
            },
            legend: {
                labels: {
                    colors: '#f5f7ff',
                },
                show: true,
                position: 'top',
            },
            stroke: {
                colors: ['transparent'],
                show: true,
                width: 2,
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
            },
            xaxis: {
                categories: chartData.labels,
                title: {
                    style: {
                        color: '#f5f7ff',
                    },
                },
                axisBorder: {
                    show: true,
                    color: '#55596e',
                },
                axisTicks: {
                    show: true,
                    color: '#55596e',
                },
                labels: {
                    style: {
                        colors: '#f1f7ff',
                    },
                },
            },
            yaxis: {
                title: {
                    text: 'Count',
                    style: {
                        color: '#f5f7ff',
                    },
                },
                axisBorder: {
                    color: '#55596e',
                    show: true,
                },
                axisTicks: {
                    color: '#55596e',
                    show: true,
                },
                labels: {
                    style: {
                        colors: '#f5f7ff',
                    },
                },
            },
        };

        const barChart = new ApexCharts(
            document.querySelector('#bar-chart'),
            barChartOptions
        );
        barChart.render();
    </script>
    <script>
    // Fetch the monthly chart data from PHP
    const monthlyChartData = <?php echo $monthly_chart_data; ?>;

    // Monthly Area Chart for Completed Rentals
    const monthlyAreaChartOptions = {
        series: [{
            name: 'Completed Rentals',
            data: monthlyChartData.values,
        }],
        chart: {
            type: 'area',
            height: 300,
            width: '100%', // Set the width to 100% of the container, or you can set a fixed width like '800px'
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        colors: ['#ff5722'], // You can choose a different color for monthly chart
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        grid: {
            borderColor: '#55596e',
            yaxis: {
                lines: { show: true },
            },
            xaxis: {
                lines: { show: true },
            },
        },
        xaxis: {
            categories: monthlyChartData.labels, // Use the month labels
            title: { style: { color: '#f5f7ff' } },
            axisBorder: { show: true, color: '#55596e' },
            axisTicks: { show: true, color: '#55596e' },
            labels: { style: { colors: '#f5f7ff' } },
        },
        yaxis: {
            title: { text: 'Rentals', style: { color: '#f5f7ff' } },
            axisBorder: { color: '#55596e', show: true },
            axisTicks: { color: '#55596e', show: true },
            labels: { style: { colors: '#f5f7ff' } },
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'dark',
        },
        fill: {
            opacity: 0.8,
            gradient: {
                shade: 'dark',
                type: 'vertical',
                shadeIntensity: 0.3,
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 0.1,
                stops: [0, 90, 100],
            },
        },
    };

    // Render Monthly Chart
    const monthlyAreaChart = new ApexCharts(
        document.querySelector('#monthly-chart'),
        monthlyAreaChartOptions
    );
    monthlyAreaChart.render();
    </script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
        // Fetch data from the PHP script
        fetch("getMostRentedVehiclesData.php")
            .then(response => response.json())
            .then(data => {
                // Check if there's an error in the data response
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // Prepare the labels and data for the chart
                const vehicleNames = data.map(item => item.vehicle_name);
                const rentCounts = data.map(item => item.rent_count);

                // Get the canvas element where the chart will be drawn
                const ctx = document.getElementById('mostRentedVehiclesChart').getContext('2d');

                // Create the bar chart
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: vehicleNames,
                        datasets: [{
                            label: 'Number of Rentals',
                            data: rentCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)', // Custom color for bars
                            borderColor: 'rgba(54, 162, 235, 1)', // Border color for bars
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true, // Ensure the y-axis starts at zero
                                title: {
                                    display: true,
                                    text: 'Number of Rentals'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Vehicles'
                                }
                            }
                        },
                        responsive: true, // Make the chart responsive to screen size
                        plugins: {
                            legend: {
                                display: false // Hide legend as it's not needed in this case
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Error fetching data:", error));
    });
        </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="setting_report.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="setting_report.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </body>
        </html>