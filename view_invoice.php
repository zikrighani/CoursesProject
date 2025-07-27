<?php
session_start();

include '../dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get reservation ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: booking.php");
    exit();
}

$reservationID = $_GET['id'];

// Get invoice data
$invoiceQuery = "
    SELECT 
        r.reservationID,
        r.reservationDate,
        r.startTime,
        r.endTime,
        r.totalPrice,
        r.status as reservationStatus,
        r.addInfo,
        r.createdAt,
        ro.roomName,
        pk.packageName,
        pk.pricePerHour,
        pk.description,
        p.paymentStatus,
        p.paymentMethod,
        p.amountPaid,
        p.paymentDate,
        u.fullName,
        u.email,
        u.phone,
        TIMESTAMPDIFF(HOUR, r.startTime, r.endTime) as duration
    FROM reservations r
    JOIN rooms ro ON r.roomID = ro.roomID
    JOIN packages pk ON ro.packageID = pk.packageID
    JOIN users u ON r.userID = u.userID
    LEFT JOIN payments p ON r.reservationID = p.reservationID
    WHERE r.reservationID = ? AND r.userID = ?
";

$stmt = $conn->prepare($invoiceQuery);
$stmt->bind_param("ii", $reservationID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: booking.php");
    exit();
}

$invoice = $result->fetch_assoc();

// Generate booking reference
$bookingReference = '#CK' . str_pad($invoice['reservationID'], 5, '0', STR_PAD_LEFT);

// Determine if this is a refund receipt
$isRefund = $invoice['reservationStatus'] == 'cancelled' && $invoice['paymentStatus'] == 'refunded';
$documentTitle = $isRefund ? 'REFUND RECEIPT' : 'RECEIPT';

// Format dates
$formattedDate = date('d/m/Y', strtotime($invoice['reservationDate']));
$formattedStartTime = date('g:i A', strtotime($invoice['startTime']));
$formattedEndTime = date('g:i A', strtotime($invoice['endTime']));
$receiptDate = date('d/m/Y g:i A', strtotime($invoice['paymentDate'] ?: $invoice['createdAt']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $documentTitle; ?> - Crony Karaoke</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: white;
            color: #000;
            padding: 20px;
            line-height: 1.4;
        }

        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border: 2px dashed #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .receipt-info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .receipt-info div {
            margin-bottom: 5px;
        }

        .customer-info {
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
        }

        .customer-info h4 {
            font-size: 14px;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .booking-details {
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
        }

        .booking-details h4 {
            font-size: 14px;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .items-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            text-align: left;
            padding: 5px 0;
            font-size: 12px;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .items-table .amount {
            text-align: right;
        }

        .total-section {
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-bottom: 20px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .total-final {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 10px;
        }

        .payment-info {
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
        }

        .payment-info h4 {
            font-size: 14px;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 15px;
            font-size: 12px;
        }

        .print-button {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            background: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }

        .print-btn:hover {
            background: #333;
        }

        .back-link {
            text-align: center;
            margin-top: 10px;
        }

        .back-link a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .status-badge {
            padding: 2px 8px;
            border: 1px solid #000;
            font-size: 12px;
            font-weight: bold;
        }

        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
            
            .print-button,
            .back-link {
                display: none;
            }
            
            .receipt {
                border: none;
                max-width: none;
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">CRONY KARAOKE</div>
            <div class="company-info">
                Premium Karaoke Experience<br>
                Level 2, Lot 18, Plaza Sentral<br>
                Kuala Lumpur, Malaysia<br>
                Tel: +60 16-501 4332<br>
                Email: helper@cronykaraoke.com
            </div>
            <div class="receipt-title"><?php echo $documentTitle; ?></div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div><strong>Receipt #:</strong> <?php echo $bookingReference; ?></div>
            <div><strong>Date:</strong> <?php echo $receiptDate; ?></div>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <h4>CUSTOMER DETAILS</h4>
            <div><strong>Name:</strong> <?php echo htmlspecialchars($invoice['fullName']); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($invoice['email']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['phone']); ?></div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <h4>BOOKING DETAILS</h4>
            <div><strong>Date:</strong> <?php echo $formattedDate; ?></div>
            <div><strong>Time:</strong> <?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?></div>
            <div><strong>Duration:</strong> <?php echo $invoice['duration']; ?> hour<?php echo $invoice['duration'] > 1 ? 's' : ''; ?></div>
            <div><strong>Room:</strong> <?php echo htmlspecialchars($invoice['roomName']); ?></div>
            <div><strong>Status:</strong> 
                <span class="status-badge">
                    <?php echo $isRefund ? 'REFUNDED' : 'PAID'; ?>
                </span>
            </div>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>QTY</th>
                    <th>RATE</th>
                    <th class="amount">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($invoice['packageName']); ?> Package<br>
                        <small><?php echo htmlspecialchars($invoice['roomName']); ?></small>
                    </td>
                    <td><?php echo $invoice['duration']; ?>h</td>
                    <td>RM <?php echo number_format($invoice['pricePerHour'], 2); ?></td>
                    <td class="amount">RM <?php echo number_format($invoice['totalPrice'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>RM <?php echo number_format($invoice['totalPrice'], 2); ?></span>
            </div>
            <div class="total-line">
                <span>Tax (0%):</span>
                <span>RM 0.00</span>
            </div>
            <div class="total-line total-final">
                <span><?php echo $isRefund ? 'TOTAL REFUNDED:' : 'TOTAL PAID:'; ?></span>
                <span>RM <?php echo number_format($invoice['amountPaid'], 2); ?></span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <h4>PAYMENT DETAILS</h4>
            <div><strong>Method:</strong> <?php echo htmlspecialchars($invoice['paymentMethod']); ?></div>
            <div><strong>Date:</strong> <?php echo date('d/m/Y g:i A', strtotime($invoice['paymentDate'])); ?></div>
            <?php if ($isRefund): ?>
            <div><strong>Refund Status:</strong> COMPLETED</div>
            <?php endif; ?>
        </div>

        <!-- Special Requests -->
        <?php if (!empty($invoice['addInfo'])): ?>
        <div class="payment-info">
            <h4><?php echo $invoice['reservationStatus'] == 'cancelled' ? 'CANCELLATION DETAILS' : 'SPECIAL REQUESTS'; ?></h4>
            <div><?php echo nl2br(htmlspecialchars($invoice['addInfo'])); ?></div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div>*** THANK YOU FOR CHOOSING CRONY KARAOKE ***</div>
            <div>Sing. Laugh. Repeat.</div>
            <div style="margin-top: 10px;">
                This receipt was generated on<br>
                <?php echo date('d/m/Y g:i A'); ?>
            </div>
        </div>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="print-button">
        <button onclick="window.print()" class="print-btn">🖨️ PRINT RECEIPT</button>
    </div>

    <!-- Back Link -->
    <div class="back-link">
        <a href="booking.php">← Back to Bookings</a>
    </div>

    <script>
        // Auto-focus for better print experience
        window.addEventListener('load', function() {
            // Optional: Auto-print when page loads (uncomment if needed)
            // window.print();
        });
    </script>
</body>
</html>