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
$documentTitle = $isRefund ? 'Refund Receipt' : 'Invoice';

// Format dates
$formattedDate = date('F j, Y', strtotime($invoice['reservationDate']));
$formattedStartTime = date('g:i A', strtotime($invoice['startTime']));
$formattedEndTime = date('g:i A', strtotime($invoice['endTime']));
$invoiceDate = date('F j, Y', strtotime($invoice['paymentDate'] ?: $invoice['createdAt']));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">
    <meta name="description" content="Crony Karaoke - <?php echo $documentTitle; ?>">
    <title><?php echo $documentTitle; ?> - Crony Karaoke</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/theme/css/style.css">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .invoice-container { box-shadow: none !important; }
            body { background: white !important; }
        }
        
        .invoice-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 800px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(45deg, #493d9e, #8571ff);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .invoice-body {
            padding: 2rem;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .invoice-details {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin: 1.5rem 0;
        }
        
        .invoice-details th {
            background: #f8f9fa;
            padding: 12px;
            font-weight: 600;
        }
        
        .invoice-details td {
            padding: 12px;
            border-top: 1px solid #dee2e6;
        }
        
        .total-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        
        .status-refunded {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-print {
            background-color: #493d9e;
            border-color: #493d9e;
        }
        
        .btn-print:hover {
            background-color: #3d3486;
            border-color: #3d3486;
        }
    </style>
</head>
<body style="background: #edefeb;">

<div class="container">
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <h1 class="mb-3"><?php echo $documentTitle; ?></h1>
            <h2 class="mb-0"><?php echo $bookingReference; ?></h2>
        </div>
        
        <!-- Invoice Body -->
        <div class="invoice-body">
            <!-- Company Info -->
            <div class="company-info">
                <h3>Crony Karaoke</h3>
                <p class="mb-1">Premium Karaoke Experience</p>
                <p class="mb-1">📞 +60 16-501 4332 | 📧 helper@cronykaraoke.com</p>
                <p class="text-muted">Date: <?php echo $invoiceDate; ?></p>
            </div>
            
            <!-- Customer & Booking Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Bill To:</h5>
                    <p class="mb-1"><strong><?php echo htmlspecialchars($invoice['fullName']); ?></strong></p>
                    <p class="mb-1"><?php echo htmlspecialchars($invoice['email']); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($invoice['phone']); ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Booking Info:</h5>
                    <p class="mb-1"><strong>Date:</strong> <?php echo $formattedDate; ?></p>
                    <p class="mb-1"><strong>Time:</strong> <?php echo $formattedStartTime . ' - ' . $formattedEndTime; ?></p>
                    <p class="mb-1"><strong>Duration:</strong> <?php echo $invoice['duration']; ?> hour<?php echo $invoice['duration'] > 1 ? 's' : ''; ?></p>
                    <p class="mb-0">
                        <strong>Status:</strong> 
                        <span class="status-badge <?php echo $isRefund ? 'status-refunded' : 'status-paid'; ?>">
                            <?php echo $isRefund ? 'Refunded' : 'Paid'; ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Service Details -->
            <table class="table invoice-details">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Room</th>
                        <th>Rate/Hour</th>
                        <th>Hours</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($invoice['packageName']); ?> Package</strong>
                            <br>
                            <small class="text-muted"><?php echo nl2br(htmlspecialchars($invoice['description'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($invoice['roomName']); ?></td>
                        <td>RM <?php echo number_format($invoice['pricePerHour'], 2); ?></td>
                        <td><?php echo $invoice['duration']; ?></td>
                        <td class="text-end">RM <?php echo number_format($invoice['totalPrice'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Total Section -->
            <div class="total-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Details:</h6>
                        <p class="mb-1"><strong>Method:</strong> <?php echo htmlspecialchars($invoice['paymentMethod']); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($invoice['paymentDate'])); ?></p>
                        <?php if ($isRefund): ?>
                        <p class="mb-0"><strong>Refund Status:</strong> <span class="text-success">Completed</span></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            <p class="mb-1">Subtotal: <strong>RM <?php echo number_format($invoice['totalPrice'], 2); ?></strong></p>
                            <p class="mb-1">Tax (0%): <strong>RM 0.00</strong></p>
                            <hr>
                            <h5 class="mb-0">
                                <?php echo $isRefund ? 'Total Refunded:' : 'Total Paid:'; ?> 
                                <strong>RM <?php echo number_format($invoice['amountPaid'], 2); ?></strong>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Special Requests -->
            <?php if (!empty($invoice['addInfo'])): ?>
            <div class="mt-4">
                <h6>
                    <?php echo $invoice['reservationStatus'] == 'cancelled' ? 'Cancellation Details:' : 'Special Requests:'; ?>
                </h6>
                <div class="border rounded p-3 bg-light">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($invoice['addInfo'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="text-center mt-4 pt-4 border-top">
                <p class="text-muted mb-2">Thank you for choosing Crony Karaoke!</p>
                <p class="small text-muted mb-0">
                    This <?php echo strtolower($documentTitle); ?> was generated on <?php echo date('F j, Y g:i A'); ?>
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary btn-print me-2">
                    🖨️ Print <?php echo $documentTitle; ?>
                </button>
                <a href="booking.php" class="btn btn-outline-secondary">
                    ← Back to Bookings
                </a>
            </div>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>