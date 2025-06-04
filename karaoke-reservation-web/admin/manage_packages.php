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

// Handle Add Package Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
    $packageName = $_POST['new_package_name'];
    $pricePerHour = $_POST['new_package_price'];
    $description = $_POST['new_package_description'];

    // Basic validation
    if (!empty($packageName) && is_numeric($pricePerHour) && $pricePerHour >= 0) {
        $stmt = $conn->prepare("INSERT INTO packages (packageName, description, pricePerHour) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $packageName, $description, $pricePerHour);
        if ($stmt->execute()) {
            $message = 'Package added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding package: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all fields correctly.';
        $messageType = 'error';
    }
}

// Handle Edit Package Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_package'])) {
    $packageID = $_POST['edit_package_id'];
    $packageName = $_POST['edit_package_name'];
    $pricePerHour = $_POST['edit_package_price'];
    $description = $_POST['edit_package_description'];

    if (!empty($packageName) && is_numeric($pricePerHour) && $pricePerHour >= 0) {
        $stmt = $conn->prepare("UPDATE packages SET packageName = ?, description = ?, pricePerHour = ? WHERE packageID = ?");
        $stmt->bind_param("ssdi", $packageName, $description, $pricePerHour, $packageID);
        if ($stmt->execute()) {
            $message = 'Package updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating package: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all fields correctly for editing.';
        $messageType = 'error';
    }
}

// Handle Delete Package Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_package'])) {
    $packageID = $_POST['delete_package_id'];

    // IMPORTANT: Before deleting a package, ensure no rooms are linked to it.
    // In a real system, you might want to update rooms to set packageID to NULL or prevent deletion.
    $stmt = $conn->prepare("DELETE FROM packages WHERE packageID = ?");
    $stmt->bind_param("i", $packageID);
    if ($stmt->execute()) {
        $message = 'Package deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting package. Ensure no rooms are currently using this package type: ' . $stmt->error;
        $messageType = 'error';
    }
    $stmt->close();
}

// Fetch existing packages
$packages = [];
$result = $conn->query("SELECT packageID, packageName, description, pricePerHour FROM packages ORDER BY packageName ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
} else {
    $message = 'Error loading packages: ' . $conn->error;
    $messageType = 'error';
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Room Packages</title>
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

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-3xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Room Packages</h1>

        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New Package</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="add_package" value="1">
                <div>
                    <label for="new_package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                    <input type="text" id="new_package_name" name="new_package_name" placeholder="e.g., Standard Package" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="new_package_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="new_package_description" name="new_package_description" rows="2" placeholder="e.g., Cozy room, up to 4 people" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <label for="new_package_price" class="block text-sm font-medium text-gray-700 mb-1">Price per Hour (RM)</label>
                    <input type="number" id="new_package_price" name="new_package_price" step="0.50" min="0" placeholder="e.g., 25.00" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg text-lg font-semibold hover:bg-green-700 transition duration-300 shadow-md">
                    Add Package
                </button>
            </form>
        </div>

        <div class="mb-8 p-6 bg-white rounded-lg border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Existing Room Packages</h2>
            <div class="space-y-4">
                <?php if (empty($packages)): ?>
                    <p class="text-center text-gray-500">No packages found.</p>
                <?php else: ?>
                    <?php foreach ($packages as $package): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg shadow-sm">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($package['packageName']); ?></h3>
                                <p class="text-sm text-gray-600">Price: RM <?php echo htmlspecialchars(number_format($package['pricePerHour'], 2)); ?>/hour</p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($package['description']); ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="showEditPackageForm(<?php echo htmlspecialchars(json_encode($package)); ?>)" class="bg-yellow-500 text-white py-1 px-3 rounded-md text-sm hover:bg-yellow-600 transition duration-300">Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this package? This will affect rooms linked to it!');">
                                    <input type="hidden" name="delete_package" value="1">
                                    <input type="hidden" name="delete_package_id" value="<?php echo htmlspecialchars($package['packageID']); ?>">
                                    <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-sm hover:bg-red-600 transition duration-300">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="editPackageFormContainer" class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200 hidden">
            <h2 class="text-2xl font-semibold text-blue-800 mb-4">Edit Package</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="edit_package" value="1">
                <input type="hidden" id="edit_package_id" name="edit_package_id">
                <div>
                    <label for="edit_package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                    <input type="text" id="edit_package_name" name="edit_package_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_package_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="edit_package_description" name="edit_package_description" rows="2" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <label for="edit_package_price" class="block text-sm font-medium text-gray-700 mb-1">Price per Hour (RM)</label>
                    <input type="number" id="edit_package_price" name="edit_package_price" step="0.50" min="0" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditPackageForm()" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg text-md font-semibold hover:bg-gray-400 transition duration-300">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-blue-700 transition duration-300">Save Changes</button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="manage_rooms.php" class="font-medium text-gray-600 hover:text-gray-800">Back to Room Management Selection</a>
        </p>
    </div>

    <script>
        // Function to show the edit form for Packages and populate it with data
        function showEditPackageForm(packageData) {
            document.getElementById('edit_package_id').value = packageData.packageID;
            document.getElementById('edit_package_name').value = packageData.packageName;
            document.getElementById('edit_package_description').value = packageData.description;
            document.getElementById('edit_package_price').value = parseFloat(packageData.pricePerHour);
            document.getElementById('editPackageFormContainer').classList.remove('hidden');
            document.getElementById('editPackageFormContainer').scrollIntoView({ behavior: 'smooth' });
        }

        // Function to hide the edit form for Packages
        function hideEditPackageForm() {
            document.getElementById('editPackageFormContainer').classList.add('hidden');
        }

        // Auto-hide messages after a few seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer.children.length > 0) {
                setTimeout(() => {
                    messageContainer.classList.add('hidden');
                }, 5000); // Hide after 5 seconds
            }
        });
    </script>
</body>
</html>
