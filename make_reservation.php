<?php
include '../dbconfig.php';
session_start();



// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];
$username = $_SESSION['fullName'];

// Get available packages
// We will fetch features separately for each package
$packageQuery = "SELECT packageID, packageName, pricePerHour, description, image FROM packages ORDER BY pricePerHour";
$packageResult = mysqli_query($conn, $packageQuery);
$packages = [];
while ($row = mysqli_fetch_assoc($packageResult)) {
    $packages[] = $row;
}

// Prepare to fetch features for each package
$packageFeatures = [];
if (!empty($packages)) {
    foreach ($packages as $package) {
        $packageId = $package['packageID'];
        // Corrected: Using 'featureText' as per your SQL schema
        $featuresQuery = "SELECT featureText FROM package_features WHERE packageID = ?";
        $stmtFeatures = mysqli_prepare($conn, $featuresQuery);
        mysqli_stmt_bind_param($stmtFeatures, "i", $packageId);
        mysqli_stmt_execute($stmtFeatures);
        $featuresResult = mysqli_stmt_get_result($stmtFeatures);
        
        $featuresList = [];
        while ($featureRow = mysqli_fetch_assoc($featuresResult)) {
            // Corrected: Using 'featureText' as per your SQL schema
            $featuresList[] = $featureRow['featureText'];
        }
        $packageFeatures[$packageId] = $featuresList;
        mysqli_stmt_close($stmtFeatures);
    }
}


// Handle form submission
$error_message = '';
$success_message = '';

if (isset($_POST['submit'])) {
    $roomType = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = $_POST['duration'];
    $specialRequests = isset($_POST['special_requests']) ? $_POST['special_requests'] : '';

    // Get available room of selected package
    // You'll need to join with packages to get the packageID for the roomType
    $packageIDQuery = "SELECT packageID FROM packages WHERE packageName = ?";
    $stmtPackageID = mysqli_prepare($conn, $packageIDQuery);
    mysqli_stmt_bind_param($stmtPackageID, "s", $roomType);
    mysqli_stmt_execute($stmtPackageID);
    $packageIDResult = mysqli_stmt_get_result($stmtPackageID);
    $packageData = mysqli_fetch_assoc($packageIDResult);
    $selectedPackageID = $packageData['packageID'];
    mysqli_stmt_close($stmtPackageID);


    $roomQuery = "SELECT r.roomID, p.pricePerHour
                  FROM rooms r
                  JOIN packages p ON r.packageID = p.packageID
                  WHERE p.packageID = ? AND r.status = 'available'"; // Use packageID here
    $stmt = mysqli_prepare($conn, $roomQuery);
    mysqli_stmt_bind_param($stmt, "i", $selectedPackageID); // Bind packageID
    mysqli_stmt_execute($stmt);
    $roomResult = mysqli_stmt_get_result($stmt);

    $roomFound = false;

    while ($roomData = mysqli_fetch_assoc($roomResult)) {
        $roomID = $roomData['roomID'];
        $pricePerHour = $roomData['pricePerHour'];
        $totalPrice = $pricePerHour * $duration;

        // Calculate time
        $startTimeObj = DateTime::createFromFormat('H:i', $time);
        $endTimeObj = clone $startTimeObj;
        $endTimeObj->add(new DateInterval('PT' . $duration . 'H'));
        $startTime = $startTimeObj->format('H:i:s');
        $endTime = $endTimeObj->format('H:i:s');

        // Check for early bird discount (3+ days in advance)
        $reservationDateTime = new DateTime($date . ' ' . $startTime);
        $currentDateTime = new DateTime();
        $daysDifference = $currentDateTime->diff($reservationDateTime)->days;
        $earlyBirdDiscount = 0;

        if ($daysDifference >= 3) {
            $earlyBirdDiscount = $totalPrice * 0.10;
            $totalPrice = $totalPrice - $earlyBirdDiscount;
        }

        // Check availability
        $conflictQuery = "SELECT COUNT(*) as count FROM reservations
                          WHERE roomID = ? AND reservationDate = ?
                          AND status != 'cancelled'
                          AND ((startTime < ? AND endTime > ?)
                          OR (startTime < ? AND endTime > ?))";

        $conflictStmt = mysqli_prepare($conn, $conflictQuery);
        mysqli_stmt_bind_param($conflictStmt, "isssss", $roomID, $date, $endTime, $startTime, $startTime, $endTime);
        mysqli_stmt_execute($conflictStmt);
        $conflictResult = mysqli_stmt_get_result($conflictStmt);
        $conflictData = mysqli_fetch_assoc($conflictResult);

        if ($conflictData['count'] == 0) {
            $roomFound = true;

            // Store reservation data in session for payment
            $_SESSION['pending_reservation'] = [
                'userID' => $userID,
                'roomID' => $roomID,
                'roomType' => $roomType,
                'reservationDate' => $date,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'duration' => $duration,
                'totalPrice' => $totalPrice,
                'earlyBirdDiscount' => $earlyBirdDiscount,
                'specialRequests' => $specialRequests
            ];

            header("Location: simulate_payment.php");
            exit();
        }
    }

    if (!$roomFound) {
        $error_message = "Sorry, all rooms of this type are fully booked at the selected time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="Crony Karaoke - Make a Reservation">
    <title>Book a Room - Crony Karaoke</title>

    <link rel="shortcut icon" href="../assets/images/cronykaraoke.webp" type="image/x-icon">

    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/animatecss/animate.css">
    <link rel="stylesheet" href="../assets/theme/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="preload" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter+Tight:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"></noscript>

    <link rel="stylesheet" href="../assets/make_reservation.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="user_home.php">
                <img src="../assets/images/cronykaraoke-1.webp" alt="Crony Karaoke Logo">
                <span>Crony Karaoke</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user_home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_home.php#promotions">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mailto:helper@cronykaraoke.com">Support</a>
                    </li>
                </ul>
                <a class="btn-logout" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Book Your Karaoke Room</h1>
                <p class="page-subtitle">Select your preferred room, date, and time to reserve your spot</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-section">
                            <h2 class="section-header">
                                <span class="step-number">1</span>
                                Choose Your Room
                            </h2>

                            <div class="room-grid">
                                <?php
                                // Define a default image if none is uploaded for a package
                                $defaultImage = '../assets/images/placeholder-room.png'; // Make sure you have a placeholder image

                                foreach ($packages as $package):
                                    $packageID = $package['packageID']; // Get packageID
                                    $packageName = $package['packageName'];
                                    // Construct image path, falling back to default if image is empty
                                    $packageImage = !empty($package['image']) ? '../assets/images/packages/' . htmlspecialchars($package['image']) : $defaultImage;
                                ?>
                                    <div class="room-card" onclick="selectRoom('<?php echo $packageName; ?>')">
                                        <div class="room-image" style="background-image: url('<?php echo $packageImage; ?>');"></div>
                                        <div class="room-details">
                                            <h4 class="room-title"><?php echo htmlspecialchars($packageName); ?> Room</h4>
                                            <p class="room-description"><?php echo htmlspecialchars($package['description']); ?></p>
                                            <ul class="room-features">
                                                <?php
                                                // Display features fetched into $packageFeatures array
                                                if (isset($packageFeatures[$packageID]) && !empty($packageFeatures[$packageID])) {
                                                    foreach ($packageFeatures[$packageID] as $feature): ?>
                                                        <li><?php echo htmlspecialchars($feature); ?></li>
                                                    <?php endforeach;
                                                } else {
                                                    echo '<li>No specific features listed.</li>';
                                                }
                                                ?>
                                            </ul>
                                            <div class="room-price">RM <?php echo number_format($package['pricePerHour'], 2); ?>/hour</div>
                                            <div class="room-select-container">
                                                <input class="form-check-input room-select" type="radio" name="room" id="<?php echo strtolower(str_replace(' ', '-', $packageName)); ?>-room" value="<?php echo htmlspecialchars($packageName); ?>" required>
                                                <label class="form-check-label" for="<?php echo strtolower(str_replace(' ', '-', $packageName)); ?>-room">
                                                    Select Room
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="section-header">
                                <span class="step-number">2</span>
                                Select Date & Time
                            </h2>

                            <div class="row">
                                <div class="col-md-4 mb-3 position-relative">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>" style="color-scheme: dark;">
                                    <i class="fa fa-calendar-alt" style="position:absolute; right:18px; top:44px; color:#fff; pointer-events:none;"></i>
                                    <div class="invalid-feedback">Please select a valid date.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="time" class="form-label">Time</label>
                                    <select class="form-select" id="time" name="time" required>
                                        <option value="" selected disabled>Select time</option>
                                        <?php for ($hour = 10; $hour <= 22; $hour++): ?>
                                            <option value="<?php echo sprintf('%02d:00', $hour); ?>">
                                                <?php echo date('g:00 A', strtotime($hour . ':00')); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a time.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="duration" class="form-label">Duration (hours)</label>
                                    <select class="form-select" id="duration" name="duration" required>
                                        <option value="" selected disabled>Select duration</option>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> hour<?php echo $i > 1 ? 's' : ''; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select duration.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="section-header">
                                <span class="step-number">3</span>
                                Additional Information
                            </h2>

                            <div class="mb-3">
                                <label for="special-requests" class="form-label">Special Requests (Optional)</label>
                                <textarea class="form-control" id="special-requests" name="special_requests" rows="4" placeholder="Any special requests or arrangements..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h3 class="summary-title">Booking Summary</h3>

                            <div class="summary-row">
                                <span class="summary-label">Room:</span>
                                <span class="summary-value" id="summary-room">Not selected</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Date:</span>
                                <span class="summary-value" id="summary-date">Not selected</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Time:</span>
                                <span class="summary-value" id="summary-time">Not selected</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Duration:</span>
                                <span class="summary-value" id="summary-duration">Not selected</span>
                            </div>

                            <div class="summary-total">
                                <div>Total: <span id="summary-total">RM 0.00</span></div>
                            </div>

                            <div class="discount-notice">
                                <i class="fas fa-lightbulb me-1"></i>
                                Book 3+ days in advance for 10% Early Bird discount!
                            </div>

                            <button type="submit" name="submit" class="btn-book">
                                <i class="fas fa-calendar-check me-2"></i>
                                Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-buttons">
                <a href="user_home.php" class="btn btn-secondary">
                    <i class="fas fa-home me-1"></i>
                    Back to Home
                </a>
                <a href="../logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
            <p class="mb-0">© 2025 Crony Karaoke — Sing. Laugh. Repeat.</p>
            <p class="mb-0">Powered by CronyTech</p>
        </div>
    </footer>

    <script src="../assets/web/assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/smoothscroll/smooth-scroll.js"></script>
    <script src="../assets/theme/js/script.js"></script>

    <script>
        // Package prices from PHP
        const packagePrices = <?php echo json_encode(array_column($packages, 'pricePerHour', 'packageName')); ?>;

        // Room selection function
        function selectRoom(roomType) {
            document.querySelectorAll('.room-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Adjust ID to match the new dynamic ID format
            const radioId = roomType.toLowerCase().replace(/ /g, '-') + '-room';
            const radioElement = document.getElementById(radioId);
            if (radioElement) {
                const selectedCard = radioElement.closest('.room-card');
                selectedCard.classList.add('selected');
                radioElement.checked = true;
            }

            updateSummary();
        }

        // Event listeners
        document.getElementById('date').addEventListener('change', updateSummary);
        document.getElementById('time').addEventListener('change', function() {
            updateDurationOptions();
            updateSummary();
        });
        document.getElementById('duration').addEventListener('change', updateSummary);

        // Update duration options based on selected time
        function updateDurationOptions() {
            const timeSelect = document.getElementById('time');
            const durationSelect = document.getElementById('duration');
            const selectedTime = timeSelect.value;

            if (!selectedTime) {
                resetDurationOptions();
                return;
            }

            const selectedHour = parseInt(selectedTime.split(':')[0]);
            const maxHours = 23 - selectedHour; // Karaoke closes at 23:00 (11 PM)

            durationSelect.innerHTML = '<option value="" selected disabled>Select duration</option>';

            for (let i = 1; i <= Math.min(5, maxHours); i++) { // Max 5 hours booking
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i + (i === 1 ? ' hour' : ' hours');
                durationSelect.appendChild(option);
            }

            if (maxHours <= 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No available duration';
                option.disabled = true;
                durationSelect.appendChild(option);
            }

            durationSelect.value = '';
        }

        function resetDurationOptions() {
            const durationSelect = document.getElementById('duration');
            durationSelect.innerHTML = `
                <option value="" selected disabled>Select duration</option>
                <option value="1">1 hour</option>
                <option value="2">2 hours</option>
                <option value="3">3 hours</option>
                <option value="4">4 hours</option>
                <option value="5">5 hours</option>
            `;
        }

        // Update booking summary
        function updateSummary() {
            let roomElement = document.querySelector('input[name="room"]:checked');
            let roomName = "Not selected";
            let roomPrice = 0;

            if (roomElement) {
                roomName = roomElement.value + " Room";
                roomPrice = parseFloat(packagePrices[roomElement.value]);
            }

            document.getElementById('summary-room').textContent = roomName;

            let date = document.getElementById('date').value;
            document.getElementById('summary-date').textContent = date ? new Date(date).toLocaleDateString() : "Not selected";

            let time = document.getElementById('time').value;
            let timeDisplay = "Not selected";
            if (time) {
                const hour = parseInt(time.split(':')[0]);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
                timeDisplay = displayHour + ':00 ' + ampm;
            }
            document.getElementById('summary-time').textContent = timeDisplay;

            let duration = document.getElementById('duration').value;
            let durationDisplay = "Not selected";
            if (duration) {
                durationDisplay = duration + " hour(s)";

                if (time) {
                    const startHour = parseInt(time.split(':')[0]);
                    const endHour = startHour + parseInt(duration);
                    const endAmpm = endHour >= 12 ? 'PM' : 'AM';
                    const endDisplayHour = endHour > 12 ? endHour - 12 : (endHour === 0 ? 12 : endHour);
                    durationDisplay += ` (until ${endDisplayHour}:00 ${endAmpm})`;
                }
            }
            document.getElementById('summary-duration').textContent = durationDisplay;

            let total = 0;
            let discountInfo = '';

            if (duration && roomPrice && date) {
                total = roomPrice * duration;

                // Check for early bird discount
                const selectedDate = new Date(date);
                const currentDate = new Date();
                currentDate.setHours(0,0,0,0); // Reset current date to start of day for accurate comparison

                const diffTime = selectedDate.getTime() - currentDate.getTime();
                const daysDifference = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (daysDifference >= 3) {
                    const discount = total * 0.10;
                    total = total - discount;
                    discountInfo = `<small class="text-warning d-block">🎉 Early Bird: -RM ${discount.toFixed(2)}</small>`;
                }
            }

            document.getElementById('summary-total').innerHTML = "RM " + total.toFixed(2) + discountInfo;
        }

        // Form validation
        (function() {
            'use strict'

            var forms = document.querySelectorAll('.needs-validation')

            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })();
    </script>

    <input name="animation" type="hidden">
</body>
</html>