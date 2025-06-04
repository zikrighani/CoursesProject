<?php

include '../dbconfig.php'; // your database connection
session_start();

// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect if not logged in or not admin
    exit();
}

$message = '';
$messageType = ''; // 'success' or 'error'

// Handle Edit User Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $userID = $_POST['edit_user_id'];
    $fullName = $_POST['edit_full_name'];
    $email = $_POST['edit_email'];
    $phone = $_POST['edit_phone'];
    $role = $_POST['edit_role'];

    if (!empty($fullName) && !empty($email) && !empty($role)) {
        $stmt = $conn->prepare("UPDATE users SET fullName = ?, email = ?, phone = ?, role = ? WHERE userID = ?");
        $stmt->bind_param("ssssi", $fullName, $email, $phone, $role, $userID);
        if ($stmt->execute()) {
            $message = 'User updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating user: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Please fill in all required fields for editing.';
        $messageType = 'error';
    }
}

// Handle Delete User Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userID = $_POST['delete_user_id'];

    // In a real system, you might want to handle associated reservations/payments first
    // or set userID to NULL in reservations if it's a foreign key with ON DELETE SET NULL.
    // For this example, we'll directly delete the user.
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    if ($stmt->execute()) {
        $message = 'User deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting user: ' . $stmt->error;
        $messageType = 'error';
    }
    $stmt->close();
}

// Fetch all users
$users = [];
$result = $conn->query("SELECT userID, fullName, email, phone, role FROM users ORDER BY fullName ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $message = 'Error loading users: ' . $conn->error;
    $messageType = 'error';
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Users</h1>

        <div class="mb-6">
            <input type="text" placeholder="Search users by name or email..." class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">User ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Phone</th>
                        <th class="py-3 px-6 text-center">Role</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="py-3 px-6 text-center text-gray-500">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($user['userID']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['fullName']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td class="py-3 px-6 text-center <?php echo ($user['role'] === 'admin' ? 'font-bold text-blue-700' : ''); ?>"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <button onclick="showEditUserForm(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="bg-yellow-500 text-white py-1 px-3 rounded-md text-xs hover:bg-yellow-600 transition duration-300">Edit</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="delete_user" value="1">
                                            <input type="hidden" name="delete_user_id" value="<?php echo htmlspecialchars($user['userID']); ?>">
                                            <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-md text-xs hover:bg-red-600 transition duration-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="editUserFormContainer" class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200 hidden">
            <h2 class="text-2xl font-semibold text-blue-800 mb-4">Edit User</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" id="edit_user_id" name="edit_user_id">
                <div>
                    <label for="edit_full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="edit_full_name" name="edit_full_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_email" name="edit_email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="edit_phone" name="edit_phone" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="edit_role" name="edit_role" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditUserForm()" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg text-md font-semibold hover:bg-gray-400 transition duration-300">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg text-md font-semibold hover:bg-blue-700 transition duration-300">Save Changes</button>
                </div>
            </form>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="admin_dashboard.php" class="font-medium text-gray-600 hover:text-gray-800">Back to Admin Dashboard</a>
        </p>
    </div>

    <script>
        // Function to show the edit form and populate it with data
        function showEditUserForm(userData) {
            document.getElementById('edit_user_id').value = userData.userID;
            document.getElementById('edit_full_name').value = userData.fullName;
            document.getElementById('edit_email').value = userData.email;
            document.getElementById('edit_phone').value = userData.phone;
            document.getElementById('edit_role').value = userData.role;
            document.getElementById('editUserFormContainer').classList.remove('hidden');
            document.getElementById('editUserFormContainer').scrollIntoView({ behavior: 'smooth' });
        }

        // Function to hide the edit form
        function hideEditUserForm() {
            document.getElementById('editUserFormContainer').classList.add('hidden');
        }

        // Auto-hide messages after a few seconds
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
