<?php
include '../dbconfig.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['fullName'] ?? 'Admin';

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="sales_report_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo "<table border='1'>";
    echo "<tr><th>Payment ID</th><th>Reservation ID</th><th>Customer Name</th><th>Room</th><th>Payment Method</th><th>Amount</th><th>Payment Date</th><th>Status</th></tr>";
    
    $query = "SELECT p.paymentID, p.reservationID, u.fullName, r.roomName, p.paymentMethod, p.amountPaid, p.paymentDate, p.paymentStatus 
              FROM payments p 
              JOIN reservations res ON p.reservationID = res.reservationID 
              JOIN users u ON res.userID = u.userID 
              JOIN rooms r ON res.roomID = r.roomID 
              ORDER BY p.paymentDate DESC";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['paymentID'] . "</td>";
        echo "<td>" . $row['reservationID'] . "</td>";
        echo "<td>" . $row['fullName'] . "</td>";
        echo "<td>" . $row['roomName'] . "</td>";
        echo "<td>" . $row['paymentMethod'] . "</td>";
        echo "<td>RM " . number_format($row['amountPaid'], 2) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', strtotime($row['paymentDate'])) . "</td>";
        echo "<td>" . ucfirst($row['paymentStatus']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit();
}

// Get sales data
$query = "SELECT p.paymentID, p.reservationID, u.fullName, r.roomName, p.paymentMethod, p.amountPaid, p.paymentDate, p.paymentStatus 
          FROM payments p 
          JOIN reservations res ON p.reservationID = res.reservationID 
          JOIN users u ON res.userID = u.userID 
          JOIN rooms r ON res.roomID = r.roomID 
          ORDER BY p.paymentDate DESC";

$result = $conn->query($query);

// Calculate totals
$totalPaid = 0;
$totalPending = 0;
$totalRefunded = 0;

$totalsQuery = "SELECT paymentStatus, SUM(amountPaid) as total FROM payments GROUP BY paymentStatus";
$totalsResult = $conn->query($totalsQuery);
while ($row = $totalsResult->fetch_assoc()) {
    if ($row['paymentStatus'] === 'paid') $totalPaid = $row['total'];
    if ($row['paymentStatus'] === 'pending') $totalPending = $row['total'];
    if ($row['paymentStatus'] === 'refunded') $totalRefunded = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Admin Dashboard</title>
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
        .export-btn {
            background-color: #059669;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .export-btn:hover {
            background-color: #047857;
        }
        .stats-card {
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #000;
            padding: 1.5rem;
            text-align: center;
        }
        .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .stats-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .status-paid { color: #059669; font-weight: 600; }
        .status-pending { color: #d97706; font-weight: 600; }
        .status-refunded { color: #dc2626; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-7xl mt-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 header-bg">
            <h1 class="text-3xl font-bold text-gray-800">Sales Report</h1>
            <div class="flex gap-4">
                <a href="?export=excel" class="export-btn">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </a>
                <a href="report.php" class="back-btn" style="background-color: #dc2626; color: #fff;">
                <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Sales Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stats-card">
                    <div class="stats-value text-green-600">RM <?php echo number_format($totalPaid, 2); ?></div>
                    <div class="stats-label">Total Paid</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value text-yellow-600">RM <?php echo number_format($totalPending, 2); ?></div>
                    <div class="stats-label">Total Pending</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value text-red-600">RM <?php echo number_format($totalRefunded, 2); ?></div>
                    <div class="stats-label">Total Refunded</div>
                </div>
            </div>
        </div>

        <!-- Sales Data Table -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment Transactions</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-4 py-2 text-left">Payment ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Reservation ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Customer</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Room</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Payment Method</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Amount</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['paymentID']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['reservationID']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['fullName']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['roomName']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['paymentMethod']; ?></td>
                            <td class="border border-gray-300 px-4 py-2">RM <?php echo number_format($row['amountPaid'], 2); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo date('Y-m-d H:i:s', strtotime($row['paymentDate'])); ?></td>
                            <td class="border border-gray-300 px-4 py-2">
                                <span class="status-<?php echo $row['paymentStatus']; ?>">
                                    <?php echo ucfirst($row['paymentStatus']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-600 text-sm mt-8">
            <p>Sales report generated on <?php echo date('Y-m-d H:i:s'); ?> by <?php echo htmlspecialchars($adminName); ?></p>
            <p>© <?php echo date('Y'); ?> Karaoke Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>