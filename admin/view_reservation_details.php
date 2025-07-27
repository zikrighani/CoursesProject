<?php
include '../dbconfig.php'; // Include your database configuration
session_start();

// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect if not logged in or not admin
    exit();
}

$reservationID = $_GET['reservation_id'] ?? null;
$reservationDetails = null;
$errorMessage = '';

if (!$reservationID) {
    $errorMessage = 'Reservation ID is missing.';
} else {
    // Fetch reservation details with associated room, package, and user details
    $query = "
        SELECT
            r.reservationID,
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
            reservations r
        LEFT JOIN
            rooms rm ON r.roomID = rm.roomID
        LEFT JOIN
            packages pkg ON rm.packageID = pkg.packageID
        LEFT JOIN
            users u ON r.userID = u.userID
        WHERE
            r.reservationID = ?
    ";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $errorMessage = 'Failed to prepare statement: ' . $conn->error;
    } else {
        $stmt->bind_param("i", $reservationID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reservationDetails = $result->fetch_assoc();
            // Calculate duration in hours if startTime and endTime are valid
            if (isset($reservationDetails['startTime']) && isset($reservationDetails['endTime'])) {
                $start = strtotime($reservationDetails['startTime']);
                $end = strtotime($reservationDetails['endTime']);
                $duration_seconds = $end - $start;
                $reservationDetails['durationHours'] = round($duration_seconds / 3600, 1);
            } else {
                $reservationDetails['durationHours'] = 'N/A';
            }
        } else {
            $errorMessage = 'Reservation not found.';
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
    <title>Reservation Details - <?php echo htmlspecialchars($reservationID ?? 'N/A'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Reservation Details</h1>

        <?php if ($errorMessage): ?>
            <div class="p-4 bg-red-100 text-red-700 rounded-lg mb-6">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php elseif ($reservationDetails): ?>
            <div class="space-y-6">
                <div class="p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Reservation Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Reservation ID:</span> <?php echo htmlspecialchars($reservationDetails['reservationID']); ?></div>
                        <div><span class="font-medium">Reservation Date:</span> <?php echo htmlspecialchars(date('Y-m-d', strtotime($reservationDetails['reservationDate']))); ?></div>
                        <div><span class="font-medium">Time:</span> <?php echo htmlspecialchars(date('H:i', strtotime($reservationDetails['startTime']))); ?> - <?php echo htmlspecialchars(date('H:i', strtotime($reservationDetails['endTime']))); ?></div>
                        <div><span class="font-medium">Duration:</span> <?php echo htmlspecialchars($reservationDetails['durationHours']); ?> hour(s)</div>
                        <div><span class="font-medium">Status:</span> <?php echo htmlspecialchars(ucfirst($reservationDetails['reservationStatus'])); ?></div>
                    </div>
                </div>

                <div class="p-6 bg-green-50 rounded-lg border border-green-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Room Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Room Name:</span> <?php echo htmlspecialchars($reservationDetails['roomName'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Package Name:</span> <?php echo htmlspecialchars($reservationDetails['packageName'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Package Description:</span> <?php echo htmlspecialchars($reservationDetails['packageDescription'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Price Per Hour:</span> RM <?php echo htmlspecialchars(number_format($reservationDetails['pricePerHour'] ?? 0, 2)); ?></div>
                    </div>
                </div>

                <div class="p-6 bg-purple-50 rounded-lg border border-purple-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Customer Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div><span class="font-medium">Name:</span> <?php echo htmlspecialchars($reservationDetails['customerName'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Email:</span> <?php echo htmlspecialchars($reservationDetails['customerEmail'] ?? 'N/A'); ?></div>
                        <div><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($reservationDetails['customerPhone'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="manage_reservations.php" class="inline-block bg-gray-300 text-gray-800 py-2 px-6 rounded-lg text-lg font-semibold hover:bg-gray-400 transition duration-300 shadow-md">
                Back to Manage Reservations
            </a>
        </div>
    </div>
</body>
</html>
