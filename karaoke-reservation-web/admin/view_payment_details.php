<?php
include '../dbconfig.php'; // Include your database configuration
session_start();

// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect if not logged in or not admin
    exit();
}

$paymentID = $_GET['payment_id'] ?? null;
$paymentDetails = null;
$errorMessage = '';

if (!$paymentID) {
    $errorMessage = 'Payment ID is missing.';
} else {
    // Fetch payment details with associated reservation, room, package, and user details
    $query = "
        SELECT
            p.paymentID,
            p.reservationID,
            p.amountPaid,
            p.paymentDate,
            p.paymentStatus,
            r.reservationDate,
            r.startTime,
            r.endTime,
            r.status AS reservationStatus,
            rm.roomName,
            pkg.packageName,
            pkg.description AS packageDescription,
            pkg.pricePerHour,
            u.fullName AS customerName,
            u.email AS customerEmail,
            u.phone AS customerPhone
        FROM
            payments p
        LEFT JOIN
            reservations r ON p.reservationID = r.reservationID
        LEFT JOIN
            rooms rm ON r.roomID = rm.roomID
        LEFT JOIN
            packages pkg ON rm.packageID = pkg.packageID
        LEFT JOIN
            users u ON r.userID = u.userID
        WHERE
            p.paymentID = ?
    ";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $errorMessage = 'Failed to prepare statement: ' . $conn->error;
    } else {
        $stmt->bind_param("i", $paymentID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $paymentDetails = $result->fetch_assoc();
            // Calculate duration in hours if startTime and endTime are valid
            if (isset($paymentDetails['startTime']) && isset($paymentDetails['endTime'])) {
                $start = strtotime($paymentDetails['startTime']);
                $end = strtotime($paymentDetails['endTime']);
                $duration_seconds = $end - $start;
                $paymentDetails['durationHours'] = round($duration_seconds / 3600, 1);
            } else {
                $paymentDetails['durationHours'] = 'N/A';
            }
        } else {
            $errorMessage = 'Payment not found.';
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - <?php echo htmlspecialchars($paymentID ?? 'N/A'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Payment Details</h1>

        <?php if ($errorMessage): ?>
            <div class="p-4 bg-red-100 text-red-700 rounded-lg mb-6">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php elseif ($paymentDetails): ?>
            <div class="space-y-6">
                <div class="p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Payment Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Payment ID:</span> <?php echo htmlspecialchars($paymentDetails['paymentID']); ?></div>
                        <div><span class="font-medium">Amount Paid:</span> RM <?php echo htmlspecialchars(number_format($paymentDetails['amountPaid'], 2)); ?></div>
                        <div><span class="font-medium">Payment Date:</span> <?php echo htmlspecialchars(date('Y-m-d', strtotime($paymentDetails['paymentDate']))); ?></div>
                        <div><span class="font-medium">Payment Status:</span> <?php echo htmlspecialchars(ucfirst($paymentDetails['paymentStatus'])); ?></div>
                    </div>
                </div>

                <div class="p-6 bg-green-50 rounded-lg border border-green-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Reservation Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Reservation ID:</span> <?php echo htmlspecialchars($paymentDetails['reservationID'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Room Type:</span> <?php echo htmlspecialchars($paymentDetails['packageName'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($paymentDetails['roomName'] ?? 'N/A'); ?>)</div>
                        <div><span class="font-medium">Booking Date:</span> <?php echo htmlspecialchars($paymentDetails['reservationDate'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Time:</span> <?php echo htmlspecialchars($paymentDetails['startTime'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($paymentDetails['endTime'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Duration:</span> <?php echo htmlspecialchars($paymentDetails['durationHours'] ?? 'N/A'); ?> hour(s)</div>
                        <div><span class="font-medium">Room Cost:</span> RM <?php echo htmlspecialchars(number_format(($paymentDetails['pricePerHour'] ?? 0) * ($paymentDetails['durationHours'] ?? 0), 2)); ?></div>
                    </div>
                </div>

                <div class="p-6 bg-purple-50 rounded-lg border border-purple-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Customer Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Name:</span> <?php echo htmlspecialchars($paymentDetails['customerName'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Email:</span> <?php echo htmlspecialchars($paymentDetails['customerEmail'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($paymentDetails['customerPhone'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="manage_payments.php" class="inline-block bg-gray-300 text-gray-800 py-2 px-6 rounded-lg text-lg font-semibold hover:bg-gray-400 transition duration-300 shadow-md">
                Back to Manage Payments
            </a>
        </div>
    </div>
</body>
</html>
