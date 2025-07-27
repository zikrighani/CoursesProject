<?php
include '../dbconfig.php'; // Include your database configuration
session_start();


// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect if not logged in or not admin
    exit();
}

$message = '';
$messageType = ''; // 'success' or 'error'

// Handle Payment Status Update (Mark Paid/Refunded)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentID = $_POST['payment_id'] ?? null;
    $newStatus = null;

    if (isset($_POST['refund_payment'])) {
        $newStatus = 'refunded';
    } elseif (isset($_POST['mark_paid'])) {
        $newStatus = 'paid'; // Keep this logic for completeness if needed elsewhere, though button is removed for 'paid' status
    }

    if ($paymentID && $newStatus) {
        $stmt = $conn->prepare("UPDATE payments SET paymentStatus = ? WHERE paymentID = ?");
        $stmt->bind_param("si", $newStatus, $paymentID);
        if ($stmt->execute()) {
            $message = "Payment ID {$paymentID} marked as '{$newStatus}' successfully!";
            $messageType = 'success';
        } else {
            $message = 'Error updating payment status: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Invalid action or missing payment ID.';
        $messageType = 'error';
    }
}

// Fetch all payments with associated reservation and user details
$payments = [];
$query = "
    SELECT
        p.paymentID,
        p.reservationID,
        p.amountPaid,
        p.paymentDate,
        p.paymentStatus,
        r.userID,
        u.fullName AS customerName
    FROM
        payments p
    LEFT JOIN
        reservations r ON p.reservationID = r.reservationID
    LEFT JOIN
        users u ON r.userID = u.userID
    ORDER BY
        p.paymentDate DESC
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
} else {
    $message = 'Error loading payments: ' . $conn->error;
    $messageType = 'error';
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
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

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-5xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Payments</h1>
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <input type="text" placeholder="Search by Reservation ID or Customer Name..." class="flex-grow px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <button class="bg-blue-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-blue-700 transition duration-300 shadow-md">Filter</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Payment ID</th>
                        <th class="py-3 px-6 text-left">Reservation ID</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-right">Amount (RM)</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody" class="text-gray-600 text-sm font-light">
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" class="py-3 px-6 text-center text-gray-500">No payments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($payment['paymentID']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($payment['reservationID'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($payment['customerName'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(date('Y-m-d', strtotime($payment['paymentDate']))); ?></td>
                                <td class="py-3 px-6 text-right font-medium">RM <?php echo htmlspecialchars(number_format($payment['amountPaid'], 2)); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <?php
                                        $statusClass = '';
                                        if ($payment['paymentStatus'] === 'paid') {
                                            $statusClass = 'bg-green-200 text-green-800';
                                        } elseif ($payment['paymentStatus'] === 'pending') {
                                            $statusClass = 'bg-yellow-200 text-yellow-800';
                                        } elseif ($payment['paymentStatus'] === 'refunded') {
                                            $statusClass = 'bg-red-200 text-red-800';
                                        }
                                    ?>
                                    <span class="<?php echo $statusClass; ?> py-1 px-3 rounded-full text-xs"><?php echo htmlspecialchars(ucfirst($payment['paymentStatus'])); ?></span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <a href="view_payment_details.php?payment_id=<?php echo htmlspecialchars($payment['paymentID']); ?>" class="bg-blue-500 text-white py-1 px-3 rounded-md text-xs hover:bg-blue-600 transition duration-300">View</a>

                                        <?php if ($payment['paymentStatus'] === 'pending'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to mark this payment as refunded?');">
                                                <input type="hidden" name="refund_payment" value="1">
                                                <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['paymentID']); ?>">
                                                <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-xs hover:bg-red-600 transition duration-300">Mark Refunded</button>
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
            <a href="admin_dashboard.php" class="font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition-colors duration-200 inline-block">
            Back to Admin Dashboard
            </a>
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
