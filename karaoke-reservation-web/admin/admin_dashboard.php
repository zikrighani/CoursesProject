<?php
include '../dbconfig.php'; // your database connection
session_start();

// Basic session check for admin.
// In a real application, you would also check if $_SESSION['role'] is 'admin'.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') { // Added role check here
    header("Location: login.php"); // Redirect to login page if not logged in or not admin
    exit();
}

// Assuming the admin's full name is stored in the session upon login
$adminName = $_SESSION['fullName'] ?? 'Admin'; // Default to 'Admin' if not set

// Initialize metrics variables
$allTimeSales = 0; // Changed variable name for clarity
$totalCustomers = 0;
$totalRooms = 0;
$totalReservations = 0;

// Fetch business metrics from the database
if ($conn) {
    // All Time Sales
    // Removed date filters to get all-time sales
    $stmt = $conn->prepare("SELECT SUM(amountPaid) AS totalSales FROM payments WHERE paymentStatus = 'paid'");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $allTimeSales = $row['totalSales'] ?? 0; // Updated variable name
        $stmt->close();
    }

    // Total Customers (only count users with role 'user')
    $stmt = $conn->prepare("SELECT COUNT(userID) AS totalCustomers FROM users WHERE role = 'user'");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalCustomers = $row['totalCustomers'] ?? 0;
        $stmt->close();
    }


    // Total Rooms
    $result = $conn->query("SELECT COUNT(roomID) AS totalRooms FROM rooms");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalRooms = $row['totalRooms'] ?? 0;
    }

    // Total Reservations
    $result = $conn->query("SELECT COUNT(reservationID) AS totalReservations FROM reservations");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReservations = $row['totalReservations'] ?? 0;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e5e7eb; /* Changed to a medium grey background */
        }
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #ffffff; /* White background for cards */
            color: #374151; /* Darker text for cards */
            border-radius: 0.75rem; /* Slightly less rounded corners */
            border: 1px solid #000; /* Added black border */
            display: flex; /* Added flexbox */
            flex-direction: column; /* Stack children vertically */
            justify-content: space-between; /* Push button to bottom */
            padding: 1.5rem; /* Adjusted padding for consistency */
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); /* Lighter shadow on hover */
        }
        .dashboard-icon {
            font-size: 3rem; /* Slightly smaller icons */
            margin-bottom: 1rem;
            color: #4f46e5; /* A standard blue accent color */
        }
        .card-title {
            font-weight: 600; /* Medium bold title */
            font-size: 1.125rem; /* Standard title size */
            margin-bottom: 0.5rem;
        }
        .card-text {
            font-size: 0.875rem; /* Standard text size */
            margin-bottom: 1rem; /* Keep some margin */
            flex-grow: 1; /* Allow text to grow and push button down */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .btn-card {
            background-color: #4f46e5; /* Standard blue button color */
            color: #ffffff;
            padding: 0.5rem 1rem; /* Adjusted padding */
            border-radius: 0.375rem; /* Standard rounded corners */
            font-weight: 500; /* Medium font weight */
            transition: background-color 0.3s ease;
            display: inline-block; /* Ensure it's an inline-block for proper spacing */
            margin-top: auto; /* Push button to the bottom of the flex container */
        }
        .btn-card:hover {
            background-color: #4338ca; /* Darker blue on hover */
        }
        .header-bg {
            background-color: #ffffff; /* White header background */
            padding: 1.5rem 2rem; /* Adjusted padding */
            border-radius: 0.75rem; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0,0,0,0.05); /* Lighter shadow */
            border: 1px solid #000; /* Added black border */
        }
        .logout-btn {
            background-color: #ef4444; /* Standard red logout button */
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #dc2626;
        }
        .metric-section { /* New class for the metrics section container */
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #000; /* Added black border */
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem; /* Add margin below the section */
        }
        .metric-card {
            padding: 1rem; /* Adjusted padding for individual metric cards */
            text-align: center;
            background-color: #f9fafb; /* Slightly different background for individual cards */
            border-radius: 0.5rem;
            border: 1px solid #000; /* Added black border */
        }
        .metric-value {
            font-size: 2.25rem; /* Slightly smaller font for values */
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .metric-label {
            font-size: 0.9rem; /* Slightly smaller font for labels */
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-6xl mt-8">
        <div class="flex justify-between items-center mb-8 header-bg">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
            <a href="../logout.php" class="logout-btn"> Logout
            </a>
        </div>

        <div class="metric-section">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Business Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="metric-card">
                    <div class="metric-value">RM <?php echo htmlspecialchars(number_format($allTimeSales, 2)); ?></div>
                    <div class="metric-label">Sales</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalCustomers); ?></div>
                    <div class="metric-label">Total Customers</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalRooms); ?></div>
                    <div class="metric-label">Total Rooms</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalReservations); ?></div>
                    <div class="metric-label">Total Reservations</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-door-open"></i> </div>
                <h2 class="card-title">Manage Karaoke Rooms</h2>
                <p class="card-text">Add, edit, or remove karaoke room types and their pricing.</p>
                <a href="manage_rooms.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-users"></i> </div>
                <h2 class="card-title">Manage Users</h2>
                <p class="card-text">View and manage registered user accounts.</p>
                <a href="manage_users.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-file-invoice-dollar"></i> </div>
                <h2 class="card-title">Manage Payments</h2>
                <p class="card-text">Access payment records and transaction history.</p>
                <a href="manage_payments.php" class="btn-card">
                    Go to Payments
                </a>
            </div>

            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-check"></i> </div>
                <h2 class="card-title">Manage Reservations</h2>
                <p class="card-text">View, confirm, or cancel customer bookings.</p>
                <a href="manage_reservations.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <div class="p-6 rounded-lg shadow-md dashboard-card text-center lg:col-span-2"> <div class="dashboard-icon">
                    <i class="fas fa-chart-bar"></i> </div>
                <h2 class="card-title">View Reports</h2>
                <p class="card-text">Generate and view business performance reports.</p>
                <a href="report.php" class="btn-card">
                    Go to Reports
                </a>
            </div>

        </div>

    </div>
</body>
</html>
