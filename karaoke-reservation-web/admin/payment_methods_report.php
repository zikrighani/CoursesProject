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
    header('Content-Disposition: attachment;filename="payment_methods_report_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo "<table border='1'>";
    echo "<tr><th>Payment Method</th><th>Total Transactions</th><th>Total Amount</th><th>Percentage of Total</th></tr>";
    
    $query = "SELECT paymentMethod, COUNT(*) as transactionCount, SUM(amountPaid) as totalAmount
              FROM payments 
              WHERE paymentStatus = 'paid'
              GROUP BY paymentMethod 
              ORDER BY totalAmount DESC";
    
    $result = $conn->query($query);
    $grandTotal = 0;
    
    // Calculate grand total first
    $totalQuery = "SELECT SUM(amountPaid) as grandTotal FROM payments WHERE paymentStatus = 'paid'";
    $totalResult = $conn->query($totalQuery);
    $grandTotal = $totalResult->fetch_assoc()['grandTotal'] ?? 0;
    
    $result = $conn->query($query); // Re-execute query
    while ($row = $result->fetch_assoc()) {
        $percentage = $grandTotal > 0 ? ($row['totalAmount'] / $grandTotal) * 100 : 0;
        echo "<tr>";
        echo "<td>" . $row['paymentMethod'] . "</td>";
        echo "<td>" . $row['transactionCount'] . "</td>";
        echo "<td>RM " . number_format($row['totalAmount'], 2) . "</td>";
        echo "<td>" . number_format($percentage, 1) . "%</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit();
}

// Get payment methods data
$query = "SELECT paymentMethod, COUNT(*) as transactionCount, SUM(amountPaid) as totalAmount
          FROM payments 
          WHERE paymentStatus = 'paid'
          GROUP BY paymentMethod 
          ORDER BY totalAmount DESC";

$result = $conn->query($query);

// Calculate grand total
$totalQuery = "SELECT SUM(amountPaid) as grandTotal FROM payments WHERE paymentStatus = 'paid'";
$totalResult = $conn->query($totalQuery);
$grandTotal = $totalResult->fetch_assoc()['grandTotal'] ?? 0;

// Get most popular payment method
$popularQuery = "SELECT paymentMethod, COUNT(*) as count FROM payments WHERE paymentStatus = 'paid' GROUP BY paymentMethod ORDER BY count DESC LIMIT 1";
$popularResult = $conn->query($popularQuery);
$mostPopular = $popularResult->fetch_assoc();

// Get total transactions count
$transactionQuery = "SELECT COUNT(*) as totalTransactions FROM payments WHERE paymentStatus = 'paid'";
$transactionResult = $conn->query($transactionQuery);
$totalTransactions = $transactionResult->fetch_assoc()['totalTransactions'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods Report - Admin Dashboard</title>
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
        .progress-bar {
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            height: 1rem;
            overflow: hidden;
        }
        .progress-fill {
            background-color: #059669;
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-7xl mt-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 header-bg">
            <h1 class="text-3xl font-bold text-gray-800">Payment Methods Report</h1>
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment Methods Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stats-card">
                    <div class="stats-value text-blue-600">RM <?php echo number_format($grandTotal, 2); ?></div>
                    <div class="stats-label">Total Revenue</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value text-green-600"><?php echo $totalTransactions; ?></div>
                    <div class="stats-label">Total Transactions</div>
                </div>
                <div class="stats-card">
                    <div class="stats-value text-purple-600"><?php echo $mostPopular['paymentMethod'] ?? 'N/A'; ?></div>
                    <div class="stats-label">Most Popular Method</div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Data Table -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment Method Analysis</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-4 py-2 text-left">Payment Method</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Total Transactions</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Total Amount</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Percentage</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Usage Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $result = $conn->query($query); // Re-execute query for display
                        while ($row = $result->fetch_assoc()): 
                            $percentage = $grandTotal > 0 ? ($row['totalAmount'] / $grandTotal) * 100 : 0;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2 font-semibold">
                                <i class="fas fa-<?php 
                                    echo $row['paymentMethod'] === 'Credit Card' ? 'credit-card' : 
                                        ($row['paymentMethod'] === 'Debit Card' ? 'credit-card' : 
                                        ($row['paymentMethod'] === 'E-Wallet' ? 'mobile-alt' : 'university')); 
                                ?> mr-2"></i>
                                <?php echo $row['paymentMethod']; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['transactionCount']; ?></td>
                            <td class="border border-gray-300 px-4 py-2 font-semibold">RM <?php echo number_format($row['totalAmount'], 2); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo number_format($percentage, 1); ?>%</td>
                            <td class="border border-gray-300 px-4 py-2">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="report-card p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment Methods Distribution</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php 
                $result = $conn->query($query); // Re-execute query for chart
                $colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500'];
                $index = 0;
                while ($row = $result->fetch_assoc()): 
                    $percentage = $grandTotal > 0 ? ($row['totalAmount'] / $grandTotal) * 100 : 0;
                    $color = $colors[$index % count($colors)];
                ?>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-4 h-4 <?php echo $color; ?> rounded mr-3"></div>
                        <div>
                            <div class="font-semibold"><?php echo $row['paymentMethod']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $row['transactionCount']; ?> transactions</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold">RM <?php echo number_format($row['totalAmount'], 2); ?></div>
                        <div class="text-sm text-gray-500"><?php echo number_format($percentage, 1); ?>%</div>
                    </div>
                </div>
                <?php 
                $index++;
                endwhile; 
                ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-600 text-sm mt-8">
            <p>Payment methods report generated on <?php echo date('Y-m-d H:i:s'); ?> by <?php echo htmlspecialchars($adminName); ?></p>
            <p>© <?php echo date('Y'); ?> Karaoke Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>