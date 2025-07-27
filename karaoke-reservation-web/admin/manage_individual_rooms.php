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

// --- Handle Individual Room Actions (from 'rooms' table) ---

// Handle Add Individual Room Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_individual_room'])) {
    $roomName = $_POST['new_individual_room_name'];
    $capacity = $_POST['new_individual_room_capacity'];
    $packageID = $_POST['new_individual_room_package_id'];
    $status = $_POST['new_individual_room_status'];

    if (!empty($roomName) && is_numeric($capacity) && $capacity > 0 && is_numeric($packageID) && $packageID > 0 && !empty($status)) {
        $stmt = $conn->prepare("INSERT INTO rooms (roomName, capacity, status, packageID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $roomName, $capacity, $status, $packageID);
        if ($stmt->execute()) {
            $message = 'Individual Room added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding individual room: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all fields correctly for adding an individual room.';
        $messageType = 'error';
    }
}

// Handle Edit Individual Room Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_individual_room'])) {
    $roomID = $_POST['edit_individual_room_id'];
    $roomName = $_POST['edit_individual_room_name'];
    $capacity = $_POST['edit_individual_room_capacity'];
    $packageID = $_POST['edit_individual_room_package_id'];
    $status = $_POST['edit_individual_room_status'];

    if (!empty($roomName) && is_numeric($capacity) && $capacity > 0 && is_numeric($packageID) && $packageID > 0 && !empty($status)) {
        $stmt = $conn->prepare("UPDATE rooms SET roomName = ?, capacity = ?, status = ?, packageID = ? WHERE roomID = ?");
        $stmt->bind_param("sisii", $roomName, $capacity, $status, $packageID, $roomID);
        if ($stmt->execute()) {
            $message = 'Individual Room updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating individual room: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all fields correctly for editing an individual room.';
        $messageType = 'error';
    }
}

// Handle Delete Individual Room Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_individual_room'])) {
    $roomID = $_POST['delete_individual_room_id'];

    // IMPORTANT: Before deleting a room, consider checking for existing reservations linked to it.
    // A real system might set room status to 'unavailable' instead of deleting, or handle related reservations.
    $stmt = $conn->prepare("DELETE FROM rooms WHERE roomID = ?");
    $stmt->bind_param("i", $roomID);
    if ($stmt->execute()) {
        $message = 'Individual Room deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting individual room. Ensure no reservations are currently linked to this room: ' . $stmt->error;
        $messageType = 'error';
    }
    $stmt->close();
}


// --- Fetch Data for Display ---

// Fetch existing individual rooms with their associated package names
$individualRooms = [];
$queryIndividualRooms = "
    SELECT
        rm.roomID,
        rm.roomName,
        rm.capacity,
        rm.status,
        rm.packageID,
        pkg.packageName
    FROM
        rooms rm
    LEFT JOIN
        packages pkg ON rm.packageID = pkg.packageID
    ORDER BY
        rm.roomName ASC
";
$resultIndividualRooms = $conn->query($queryIndividualRooms);
if ($resultIndividualRooms) {
    while ($row = $resultIndividualRooms->fetch_assoc()) {
        $individualRooms[] = $row;
    }
} else {
    $message = 'Error loading individual rooms: ' . $conn->error;
    $messageType = 'error';
}

// Fetch available packages for dropdowns
$packages = [];
$queryPackages = "SELECT packageID, packageName FROM packages ORDER BY packageName ASC";
$resultPackages = $conn->query($queryPackages);
if ($resultPackages) {
    while ($row = $resultPackages->fetch_assoc()) {
        $packages[] = $row;
    }
} else {
    error_log('Error loading packages for dropdown: ' . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Individual Rooms</title>
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

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-4xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Individual Karaoke Rooms</h1>

        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New Individual Room</h2>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="add_individual_room" value="1">
                <div>
                    <label for="new_individual_room_name" class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                    <input type="text" id="new_individual_room_name" name="new_individual_room_name" placeholder="e.g., Room 101, VIP Room A" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="new_individual_room_capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity (Persons)</label>
                    <input type="number" id="new_individual_room_capacity" name="new_individual_room_capacity" min="1" placeholder="e.g., 8" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="new_individual_room_package_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Package (Room Type)</label>
                    <select id="new_individual_room_package_id" name="new_individual_room_package_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="">Select a Package</option>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?php echo htmlspecialchars($pkg['packageID']); ?>"><?php echo htmlspecialchars($pkg['packageName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="new_individual_room_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="new_individual_room_status" name="new_individual_room_status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-green-700 transition duration-300 shadow-md">
                    Add Room
                </button>
            </form>
        </div>

        <div class="mb-8 p-6 bg-white rounded-lg border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Existing Individual Rooms</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Room ID</th>
                            <th class="py-3 px-6 text-left">Room Name</th>
                            <th class="py-3 px-6 text-left">Capacity</th>
                            <th class="py-3 px-6 text-left">Package Type</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php if (empty($individualRooms)): ?>
                            <tr><td colspan="6" class="py-3 px-6 text-center text-gray-500">No individual rooms found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($individualRooms as $room): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($room['roomID']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($room['roomName']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($room['capacity']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($room['packageName'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-6 text-center">
                                        <span class="py-1 px-3 rounded-full text-xs <?php echo ($room['status'] == 'available' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($room['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center space-x-2">
                                            <button onclick="showEditIndividualRoomForm(<?php echo htmlspecialchars(json_encode($room)); ?>)" class="bg-yellow-500 text-white py-1 px-3 rounded-md text-sm hover:bg-yellow-600 transition duration-300">Edit</button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this room? This will affect reservations linked to it!');">
                                                <input type="hidden" name="delete_individual_room" value="1">
                                                <input type="hidden" name="delete_individual_room_id" value="<?php echo htmlspecialchars($room['roomID']); ?>">
                                                <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-sm hover:bg-red-600 transition duration-300">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="editIndividualRoomFormContainer" class="mt-6 p-6 bg-blue-50 rounded-lg border border-blue-200 hidden">
            <h3 class="text-xl font-semibold text-blue-800 mb-3">Edit Individual Room</h3>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="edit_individual_room" value="1">
                <input type="hidden" id="edit_individual_room_id" name="edit_individual_room_id">
                <div>
                    <label for="edit_individual_room_name" class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                    <input type="text" id="edit_individual_room_name" name="edit_individual_room_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_individual_room_capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity (Persons)</label>
                    <input type="number" id="edit_individual_room_capacity" name="edit_individual_room_capacity" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_individual_room_package_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Package (Room Type)</label>
                    <select id="edit_individual_room_package_id" name="edit_individual_room_package_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="">Select a Package</option>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?php echo htmlspecialchars($pkg['packageID']); ?>"><?php echo htmlspecialchars($pkg['packageName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="edit_individual_room_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="edit_individual_room_status" name="edit_individual_room_status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditIndividualRoomForm()" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg text-md font-semibold hover:bg-gray-400 transition duration-300">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-blue-700 transition duration-300">Save Changes</button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="manage_rooms.php" class="font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition duration-300 inline-block">Back to Room Management Selection</a>
        </p>
        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="admin_dashboard.php" class="font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition-colors duration-200 inline-block">
            Back to Admin Dashboard
            </a>
        </p> 
    </div>

    <script>
        // Function to show the edit form for Individual Rooms and populate it with data
        function showEditIndividualRoomForm(roomData) {
            document.getElementById('edit_individual_room_id').value = roomData.roomID;
            document.getElementById('edit_individual_room_name').value = roomData.roomName;
            document.getElementById('edit_individual_room_capacity').value = roomData.capacity;
            document.getElementById('edit_individual_room_package_id').value = roomData.packageID;
            document.getElementById('edit_individual_room_status').value = roomData.status;
            document.getElementById('editIndividualRoomFormContainer').classList.remove('hidden');
            document.getElementById('editIndividualRoomFormContainer').scrollIntoView({ behavior: 'smooth' });
        }

        // Function to hide the edit form for Individual Rooms
        function hideEditIndividualRoomForm() {
            document.getElementById('editIndividualRoomFormContainer').classList.add('hidden');
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
