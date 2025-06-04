<?php
include '../dbconfig.php'; // Include your database configuration
session_start();

// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect if not logged in or not admin
    exit();
}

$message = '';
$messageType = ''; // 'success' or 'error'

// Handle Reservation Status Update (Confirm/Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['confirm_reservation']) || isset($_POST['cancel_reservation']))) {
    $reservationID = $_POST['reservation_id'];
    $newStatus = '';

    if (isset($_POST['confirm_reservation'])) {
        $newStatus = 'confirmed';
    } elseif (isset($_POST['cancel_reservation'])) {
        $newStatus = 'cancelled';
    }

    if (!empty($newStatus)) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservationID = ?");
        $stmt->bind_param("si", $newStatus, $reservationID);
        if ($stmt->execute()) {
            $message = "Reservation ID {$reservationID} marked as '{$newStatus}' successfully!";
            $messageType = 'success';
        } else {
            $message = 'Error updating reservation status: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Invalid action requested.';
        $messageType = 'error';
    }
}

// Fetch all reservations with associated room and user details
$reservations = [];
$query = "
    SELECT
        r.reservationID,
        r.reservationDate,
        r.startTime,
        r.endTime,
        r.status,
        rm.roomName,
        u.fullName AS customerName,
        u.email AS customerEmail
    FROM
        reservations r
    LEFT JOIN
        rooms rm ON r.roomID = rm.roomID
    LEFT JOIN
        users u ON r.userID = u.userID
    ORDER BY
        r.reservationDate DESC, r.startTime DESC
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Calculate duration in hours
        $start = strtotime($row['startTime']);
        $end = strtotime($row['endTime']);
        $duration_seconds = $end - $start;
        $row['durationHours'] = round($duration_seconds / 3600, 1);
        $reservations[] = $row;
    }
} else {
    $message = 'Error loading reservations: ' . $conn->error;
    $messageType = 'error';
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .message-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div id="messageContainer" class="message-container">
        <?php if ($message): ?>
            <div class="p-3 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> shadow-md">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-6xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Reservations</h1>
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <input type="text" placeholder="Search by Reservation ID or Customer Name..." class="flex-grow px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <button class="bg-blue-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-blue-700 transition duration-300 shadow-md">Filter</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Res ID</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-left">Room</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-left">Time</th>
                        <th class="py-3 px-6 text-left">Duration</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (empty($reservations)): ?>
                        <tr><td colspan="8" class="py-3 px-6 text-center text-gray-500">No reservations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($reservation['reservationID']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($reservation['customerName'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($reservation['roomName'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(date('Y-m-d', strtotime($reservation['reservationDate']))); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(date('H:i', strtotime($reservation['startTime']))); ?> - <?php echo htmlspecialchars(date('H:i', strtotime($reservation['endTime']))); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($reservation['durationHours']); ?> hour(s)</td>
                                <td class="py-3 px-6 text-center">
                                    <?php
                                        $statusClass = '';
                                        if ($reservation['status'] === 'confirmed') {
                                            $statusClass = 'bg-green-200 text-green-800';
                                        } elseif ($reservation['status'] === 'pending') {
                                            $statusClass = 'bg-yellow-200 text-yellow-800';
                                        } elseif ($reservation['status'] === 'cancelled') {
                                            $statusClass = 'bg-red-200 text-red-800';
                                        }
                                    ?>
                                    <span class="<?php echo $statusClass; ?> py-1 px-3 rounded-full text-xs"><?php echo htmlspecialchars(ucfirst($reservation['status'])); ?></span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <a href="view_reservation_details.php?reservation_id=<?php echo htmlspecialchars($reservation['reservationID']); ?>" class="bg-blue-500 text-white py-1 px-3 rounded-md text-xs hover:bg-blue-600 transition duration-300">View</a>

                                        <?php if ($reservation['status'] === 'pending'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to confirm this reservation?');">
                                                <input type="hidden" name="confirm_reservation" value="1">
                                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['reservationID']); ?>">
                                                <button type="submit" class="bg-green-500 text-white py-1 px-3 rounded-md text-xs hover:bg-green-600 transition duration-300">Confirm</button>
                                            </form>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                                <input type="hidden" name="cancel_reservation" value="1">
                                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['reservationID']); ?>">
                                                <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-xs hover:bg-red-600 transition duration-300">Cancel</button>
                                            </form>
                                        <?php elseif ($reservation['status'] === 'confirmed'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this confirmed reservation?');">
                                                <input type="hidden" name="cancel_reservation" value="1">
                                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['reservationID']); ?>">
                                                <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-xs hover:bg-red-600 transition duration-300">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="admin_dashboard.php" class="font-medium text-gray-600 hover:text-gray-800">Back to Admin Dashboard</a>
        </p>
    </div>

    <script>
        // Function to display messages (success/error/info)
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            let bgColorClass = '';
            let textColorClass = '';

            if (type === 'success') {
                bgColorClass = 'bg-green-100';
                textColorClass = 'text-green-700';
            } else if (type === 'error') {
                bgColorClass = 'bg-red-100';
                textColorClass = 'text-red-700';
            } else if (type === 'info') {
                bgColorClass = 'bg-blue-100';
                textColorClass = 'text-blue-700';
            }

            messageContainer.innerHTML = `
                <div class="p-3 rounded-lg ${bgColorClass} ${textColorClass} shadow-md">
                    ${message}
                </div>
            `;
            messageContainer.classList.remove('hidden');
            setTimeout(() => {
                messageContainer.classList.add('hidden');
            }, 5000); // Hide after 5 seconds
        }

        // Auto-hide messages on page load after a few seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer.children.length > 0) {
                setTimeout(() => {
                    messageContainer.classList.add('hidden');
                }, 5000);
            }
        });
    </script>
</body>
</html>
