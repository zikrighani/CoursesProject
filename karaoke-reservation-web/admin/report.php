<?php
include '../dbconfig.php';
session_start();


// Check admin authentication
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['fullName'] ?? 'Admin';

// Get summary statistics
function getSummaryStats($conn) {
    $stats = [];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(amountPaid) as totalRevenue FROM payments WHERE paymentStatus = 'paid'");
    $stats['totalRevenue'] = $result->fetch_assoc()['totalRevenue'] ?? 0;
    
    // This month revenue
    $result = $conn->query("SELECT SUM(amountPaid) as monthRevenue FROM payments WHERE paymentStatus = 'paid' AND MONTH(paymentDate) = MONTH(CURDATE()) AND YEAR(paymentDate) = YEAR(CURDATE())");
    $stats['monthRevenue'] = $result->fetch_assoc()['monthRevenue'] ?? 0;
    
    // Total bookings
    $result = $conn->query("SELECT COUNT(*) as totalBookings FROM reservations");
    $stats['totalBookings'] = $result->fetch_assoc()['totalBookings'] ?? 0;
    
    // Confirmed bookings
    $result = $conn->query("SELECT COUNT(*) as confirmedBookings FROM reservations WHERE status = 'confirmed'");
    $stats['confirmedBookings'] = $result->fetch_assoc()['confirmedBookings'] ?? 0;
    
    return $stats;
}

$summaryStats = getSummaryStats($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Reports - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e5e7eb;
        }
        .report-card {
            background: #ffffff;
            color: #374151;
            border-radius: 0.75rem;
            border: 1px solid #000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .header-bg {
            background-color: #ffffff;
            padding: 1.5rem 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            border: 1px solid #000;
        }
        .report-btn {
            background-color: #059669;
            color: #ffffff;
            padding: 1.5rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
            border: 2px solid #059669;
        }
        .report-btn:hover {
            background-color: #047857;
            border-color: #047857;
            transform: translateY(-2px);
        }
        .back-btn {
            background-color: #6b7280;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #4b5563;
        }
        .stats-card {
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #000;
            padding: 1.5rem;
            text-align: center;
        }
        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .stats-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-7xl mt-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 header-bg">
            <h1 class="text-3xl font-bold text-gray-800">Business Reports</h1>
            <a href="admin_dashboard.php" class="back-btn" style="background-color: #dc2626; color: #fff;">
                <i class="fas fa-arrow-left mr-2"></i>Back to Admin Dashboard
            </a>
        </div>

        <!-- Summary Statistics -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Business Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stats-card">
                    <div class="stats-value">RM <?php echo number_format($summaryStats['totalRevenue'], 2); ?></div>
                    <div class="stats-label">Total Revenue</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value">RM <?php echo number_format($summaryStats['monthRevenue'], 2); ?></div>
                    <div class="stats-label">This Month Revenue</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value"><?php echo $summaryStats['totalBookings']; ?></div>
                    <div class="stats-label">Total Bookings</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value"><?php echo $summaryStats['confirmedBookings']; ?></div>
                    <div class="stats-label">Confirmed Bookings</div>
                </div>
            </div>
        </div>

        <!-- Report Navigation -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Select Report Type</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="sales_report.php" class="report-btn">
                    <i class="fas fa-chart-line text-2xl mb-3 block"></i>
                    Sales Report
                    <div class="text-sm font-normal mt-2 opacity-90">View payment transactions and revenue data</div>
                </a>
                <a href="reservation_report.php" class="report-btn">
                    <i class="fas fa-calendar-alt text-2xl mb-3 block"></i>
                    Reservations Report
                    <div class="text-sm font-normal mt-2 opacity-90">View booking details and customer information</div>
                </a>
                <a href="payment_methods_report.php" class="report-btn">
                    <i class="fas fa-credit-card text-2xl mb-3 block"></i>
                    Payment Methods Report
                    <div class="text-sm font-normal mt-2 opacity-90">Analyze payment method usage and statistics</div>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-600 text-sm mt-8">
            <p>Report dashboard accessed on <?php echo date('Y-m-d H:i:s'); ?> by <?php echo htmlspecialchars($adminName); ?></p>
            <p>© <?php echo date('Y'); ?> Karaoke Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>