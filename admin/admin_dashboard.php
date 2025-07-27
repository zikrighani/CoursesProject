<?php
// Include database configuration
include '../dbconfig.php'; 

// Include session security file. This file is responsible for starting the session
// and performing initial security checks. It should contain the ONLY session_start() call.
require_once '../session_security.php';

// Enhanced session validation:
// Checks if the user is logged in, has a role, and if the role is 'admin'.
// Also ensures userID is not empty.
if (!isset($_SESSION['userID']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin' ||
    empty($_SESSION['userID'])) {

    // If validation fails, clear any existing session data for security.
    session_unset();
    session_destroy();

    // Redirect to the login page with a session expired message.
    header("Location: ../login.php?msg=session_expired");
    exit(); // Terminate script execution after redirection.
}

// Optional: Check session timeout (uncomment if you want session timeout)
/*
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: ../login.php?timeout=1");
        exit();
    }
}
$_SESSION['last_activity'] = time();
*/

// Regenerate session ID for security (prevents session fixation)
// This ensures that the session ID changes periodically, making it harder
// for attackers to hijack sessions.
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true); // Regenerate ID and delete old session file.
    $_SESSION['regenerated'] = true; // Mark as regenerated to avoid regenerating on every page load.
}

// Assuming the admin's full name is stored in the session upon login.
// Use null coalescing operator (??) to provide a default value if 'fullName' is not set.
$adminName = $_SESSION['fullName'] ?? 'Admin';

// Initialize metrics variables to 0.
$allTimeSales = 0;
$totalCustomers = 0;
$totalRooms = 0;
$totalReservations = 0;

// Fetch business metrics from the database.
// This block connects to the database and retrieves key performance indicators.
if ($conn) {
    // Fetch All Time Sales: Sum of 'amountPaid' from 'payments' table where 'paymentStatus' is 'paid'.
    $stmt = $conn->prepare("SELECT SUM(amountPaid) AS totalSales FROM payments WHERE paymentStatus = 'paid'");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $allTimeSales = $row['totalSales'] ?? 0; // Set to 0 if no sales found.
        $stmt->close();
    } else {
        // Log error if statement preparation fails.
        error_log("Failed to prepare statement for all time sales: " . $conn->error);
    }

    // Fetch Total Customers: Count of 'userID' from 'users' table where 'role' is 'user'.
    $stmt = $conn->prepare("SELECT COUNT(userID) AS totalCustomers FROM users WHERE role = 'user'");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalCustomers = $row['totalCustomers'] ?? 0; // Set to 0 if no customers found.
        $stmt->close();
    } else {
        // Log error if statement preparation fails.
        error_log("Failed to prepare statement for total customers: " . $conn->error);
    }

    // Fetch Total Rooms: Count of 'roomID' from 'rooms' table.
    $result = $conn->query("SELECT COUNT(roomID) AS totalRooms FROM rooms");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalRooms = $row['totalRooms'] ?? 0; // Set to 0 if no rooms found.
    } else {
        // Log error if query fails.
        error_log("Failed to query total rooms: " . $conn->error);
    }

    // Fetch Total Reservations: Count of 'reservationID' from 'reservations' table.
    $result = $conn->query("SELECT COUNT(reservationID) AS totalReservations FROM reservations");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReservations = $row['totalReservations'] ?? 0; // Set to 0 if no reservations found.
    } else {
        // Log error if query fails.
        error_log("Failed to query total reservations: " . $conn->error);
    }

    // Close the database connection.
    $conn->close();
} else {
    // Log error if database connection failed.
    error_log("Database connection failed.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Prevent caching to ensure the latest version of the page is always loaded -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Tailwind CSS CDN for utility-first styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom CSS styles for the dashboard */
        body {
            font-family: 'Inter', sans-serif; /* Using Inter font */
            background-color: #e5e7eb; /* Medium grey background */
        }
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for hover effects */
            background: #ffffff; /* White background for cards */
            color: #374151; /* Darker text for cards */
            border-radius: 0.75rem; /* Slightly less rounded corners */
            border: 1px solid #000; /* Added black border for definition */
            display: flex; /* Use flexbox for layout */
            flex-direction: column; /* Stack children vertically */
            justify-content: space-between; /* Distribute space to push button to bottom */
            padding: 1.5rem; /* Adjusted padding for consistency */
        }
        .dashboard-card:hover {
            transform: translateY(-5px); /* Lift card slightly on hover */
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
            display: flex; /* Use flexbox for centering text */
            align-items: center; /* Vertically center text */
            justify-content: center; /* Horizontally center text */
            text-align: center;
        }
        .btn-card {
            background-color: #4f46e5; /* Standard blue button color */
            color: #ffffff;
            padding: 0.5rem 1rem; /* Adjusted padding */
            border-radius: 0.375rem; /* Standard rounded corners */
            font-weight: 500; /* Medium font weight */
            transition: background-color 0.3s ease; /* Smooth transition for background color */
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
            background-color: #dc2626; /* Darker red on hover */
        }
        .metric-section { /* Container for the metrics section */
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
        <!-- Header section with welcome message and logout button -->
        <div class="flex justify-between items-center mb-8 header-bg">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
            <a href="../logout.php" class="logout-btn" onclick="return confirmLogout();">
                Logout
            </a>
        </div>

        <!-- Business Overview Metrics Section -->
        <div class="metric-section">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Business Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Sales Metric Card -->
                <div class="metric-card">
                    <div class="metric-value">RM <?php echo htmlspecialchars(number_format($allTimeSales, 2)); ?></div>
                    <div class="metric-label">Sales</div>
                </div>
                <!-- Total Customers Metric Card -->
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalCustomers); ?></div>
                    <div class="metric-label">Total Customers</div>
                </div>
                <!-- Total Rooms Metric Card -->
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalRooms); ?></div>
                    <div class="metric-label">Total Rooms</div>
                </div>
                <!-- Total Reservations Metric Card -->
                <div class="metric-card">
                    <div class="metric-value"><?php echo htmlspecialchars($totalReservations); ?></div>
                    <div class="metric-label">Total Reservations</div>
                </div>
            </div>
        </div>

        <!-- Management Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <!-- Manage Karaoke Rooms Card -->
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <h2 class="card-title">Manage Karaoke Rooms</h2>
                <p class="card-text">Add, edit, or remove karaoke room types and their pricing.</p>
                <a href="manage_rooms.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <!-- Manage Users Card -->
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h2 class="card-title">Manage Users</h2>
                <p class="card-text">View and manage registered user accounts.</p>
                <a href="manage_users.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <!-- Manage Payments Card -->
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h2 class="card-title">Manage Payments</h2>
                <p class="card-text">Access payment records and transaction history.</p>
                <a href="manage_payments.php" class="btn-card">
                    Go to Payments
                </a>
            </div>

            <!-- Manage Reservations Card -->
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h2 class="card-title">Manage Reservations</h2>
                <p class="card-text">View, confirm, or cancel customer bookings.</p>
                <a href="manage_reservations.php" class="btn-card">
                    Go to Management
                </a>
            </div>

            <!-- View Reports Card (spans two columns on large screens) -->
            <div class="p-6 rounded-lg shadow-md dashboard-card text-center lg:col-span-2">
                <div class="dashboard-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h2 class="card-title">View Reports</h2>
                <p class="card-text">Generate and view business performance reports.</p>
                <a href="report.php" class="btn-card">
                    Go to Reports
                </a>
            </div>
        </div>
    </div>

    <script>
        // Prevent back button access after logout
        // This script pushes a dummy state to the browser history on load,
        // and on popstate (when the user tries to go back), it checks the session status.
        // If the session is no longer active, it redirects to the login page.
        (function() {
            if (window.history && window.history.pushState) {
                window.addEventListener('load', function() {
                    // Push a dummy state to prevent going back to a potentially logged-out state.
                    window.history.pushState('preventBack', null, '');

                    // Listen for popstate (back button) events.
                    window.addEventListener('popstate', function(event) {
                        // Check if user is still logged in by making a quick session check via AJAX.
                        fetch('check_session.php')
                            .then(response => response.json())
                            .then(data => {
                                if (!data.loggedIn) {
                                    // If not logged in, redirect to login with session expired message.
                                    window.location.href = '../login.php?msg=session_expired';
                                } else {
                                    // If still logged in, push state again to prevent going back further.
                                    window.history.pushState('preventBack', null, '');
                                }
                            })
                            .catch(() => {
                                // On error during fetch, assume not logged in and redirect.
                                window.location.href = '../login.php?msg=session_expired';
                            });
                    });
                });
            }
        })();

        // Disable right-click and common keyboard shortcuts (optional security measure)
        // This can deter casual attempts to inspect source code or use developer tools.
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault(); // Prevent default right-click context menu.
        });

        document.addEventListener('keydown', function(e) {
            // Disable F12 (Developer Tools), Ctrl+Shift+I (Inspect Element),
            // Ctrl+U (View Source), Ctrl+Shift+C (Inspect Element).
            if (e.keyCode === 123 ||
                (e.ctrlKey && e.shiftKey && e.keyCode === 73) ||
                (e.ctrlKey && e.keyCode === 85) ||
                (e.ctrlKey && e.shiftKey && e.keyCode === 67)) {
                e.preventDefault(); // Prevent default action for these key combinations.
                return false; // Stop further event propagation.
            }
        });

        // Confirm logout function: Prompts the user before logging out.
        function confirmLogout() {
            return confirm('Are you sure you want to logout?'); // Using native confirm dialog.
        }

        // Check session periodically (optional)
        // This interval checks the session status every 5 minutes.
        // If the session has expired, it alerts the user and redirects to the login page.
        setInterval(function() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        // Using a simple alert for demonstration. In a real app,
                        // consider a custom modal for better UX.
                        alert('Your session has expired. You will be redirected to login.');
                        window.location.href = '../login.php?msg=session_expired';
                    }
                })
                .catch(() => {
                    // Session check failed (e.g., network error), redirect to login.
                    window.location.href = '../login.php?msg=session_expired';
                });
        }, 300000); // Check every 5 minutes (300000 ms).
    </script>
</body>
</html>
