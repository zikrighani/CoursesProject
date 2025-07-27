<?php 

include '../dbconfig.php'; // your database connection 
session_start(); 

// Basic session check for admin 
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); // Redirect if not logged in or not admin 
    exit(); 
} 

$message = ''; 
$messageType = ''; // 'success' or 'error' 
$searchTerm = ''; 

// Handle Edit User Form Submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) { 
    $userID = $_POST['edit_user_id']; 
    $fullName = $_POST['edit_full_name']; 
    $email = $_POST['edit_email']; 
    $phone = $_POST['edit_phone']; 
    $role = $_POST['edit_role']; 

    if (!empty($fullName) && !empty($email) && !empty($role)) { 
        $stmt = $conn->prepare("UPDATE users SET fullName = ?, email = ?, phone = ?, role = ? WHERE userID = ?"); 
        if ($stmt) { // Check if prepare was successful 
            $stmt->bind_param("ssssi", $fullName, $email, $phone, $role, $userID); 
            if ($stmt->execute()) { 
                $message = 'User updated successfully!'; 
                $messageType = 'success'; 
            } else { 
                $message = 'Error updating user: ' . $stmt->error; 
                $messageType = 'error'; 
            } 
            $stmt->close(); // Close the statement immediately after use 
        } else { 
            $message = 'Error preparing update statement: ' . $conn->error; 
            $messageType = 'error'; 
        } 
    } else { 
        $message = 'Please fill in all required fields for editing.'; 
        $messageType = 'error'; 
    } 
} 

// Handle Delete User Action 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) { 
    $userID = $_POST['delete_user_id']; 

    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?"); 
    if ($stmt) { // Check if prepare was successful 
        $stmt->bind_param("i", $userID); 
        if ($stmt->execute()) { 
            $message = 'User deleted successfully!'; 
            $messageType = 'success'; 
        } else { 
            $message = 'Error deleting user: ' . $stmt->error; 
            $messageType = 'error'; 
        } 
        $stmt->close(); // Close the statement immediately after use 
    } else { 
        $message = 'Error preparing delete statement: ' . $conn->error; 
        $messageType = 'error'; 
    } 
} 

// Handle Change Request Approval 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) { 
    $requestID = $_POST['request_id']; // This now refers to reqID 

    // Get request details 
    $stmt = $conn->prepare("SELECT * FROM requestChanges WHERE reqID = ?"); // Changed 'id' to 'reqID' 
    if ($stmt) { 
        $stmt->bind_param("i", $requestID); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 
        $request = $result->fetch_assoc(); 
        $stmt->close(); 

        if ($request) { 
            $updateFields = []; 
            $updateValues = []; 
            $updateTypes = ''; 

            // Build update query based on what needs to be changed 
            if ($request['emailChange'] === 'yes' && !empty($request['newEmail'])) { 
                $updateFields[] = "email = ?"; 
                $updateValues[] = $request['newEmail']; 
                $updateTypes .= 's'; 
            } 

            if ($request['phnoChange'] === 'yes' && !empty($request['newPNo'])) { 
                $updateFields[] = "phone = ?"; 
                $updateValues[] = $request['newPNo']; 
                $updateTypes .= 's'; 
            } 

            if (!empty($updateFields)) { 
                // Update user information 
                $updateValues[] = $request['userID']; 
                $updateTypes .= 'i'; 

                $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE userID = ?"; 
                $updateStmt = $conn->prepare($updateQuery); 

                if ($updateStmt) { 
                    // Use call_user_func_array for binding parameters with dynamic types and values 
                    $updateStmt->bind_param($updateTypes, ...$updateValues); 
                    if ($updateStmt->execute()) { 
                        // Delete the approved request 
                        $deleteStmt = $conn->prepare("DELETE FROM requestChanges WHERE reqID = ?"); // Changed 'id' to 'reqID' 
                        if ($deleteStmt) { 
                            $deleteStmt->bind_param("i", $requestID); 
                            $deleteStmt->execute(); 
                            $deleteStmt->close(); 
                        } 

                        $message = 'Change request approved and user information updated successfully!'; 
                        $messageType = 'success'; 
                    } else { 
                        $message = 'Error updating user information: ' . $updateStmt->error; 
                        $messageType = 'error'; 
                    } 
                    $updateStmt->close(); 
                } else { 
                    $message = 'Error preparing update statement: ' . $conn->error; 
                    $messageType = 'error'; 
                } 
            } else { 
                $message = 'No valid changes found in the request.'; 
                $messageType = 'error'; 
            } 
        } else { 
            $message = 'Request not found.'; 
            $messageType = 'error'; 
        } 
    } else { 
        $message = 'Error preparing request query: ' . $conn->error; 
        $messageType = 'error'; 
    } 
} 

// Handle Change Request Rejection 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) { 
    $requestID = $_POST['request_id']; // This now refers to reqID 

    $stmt = $conn->prepare("DELETE FROM requestChanges WHERE reqID = ?"); // Changed 'id' to 'reqID' 
    if ($stmt) { 
        $stmt->bind_param("i", $requestID); 
        if ($stmt->execute()) { 
            $message = 'Change request rejected and removed successfully!'; 
            $messageType = 'success'; 
        } else { 
            $message = 'Error rejecting request: ' . $stmt->error; 
            $messageType = 'error'; 
        } 
        $stmt->close(); 
    } else { 
        $message = 'Error preparing reject statement: ' . $conn->error; 
        $messageType = 'error'; 
    } 
} 

// Handle Search Term 
$users = []; // Initialize users array 
$result = null; // Initialize result 

if (isset($_GET['search']) && !empty($_GET['search'])) { 
    $searchTerm = $_GET['search']; 
    $searchWildcard = '%' . $searchTerm . '%'; 
    $stmt = $conn->prepare("SELECT userID, fullName, email, phone, role FROM users WHERE userID LIKE ? OR fullName LIKE ? OR email LIKE ? OR phone LIKE ? OR role LIKE ? ORDER BY fullName ASC"); 
    if ($stmt) { // Check if prepare was successful 
        $stmt->bind_param("sssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 
        $stmt->close(); // Close after getting result 
    } else { 
        $message = 'Error preparing search statement: ' . $conn->error; 
        $messageType = 'error'; 
    } 
} else { 
    // Fetch all users if no search term 
    $result = $conn->query("SELECT userID, fullName, email, phone, role FROM users ORDER BY fullName ASC"); 
} 

if ($result) { 
    while ($row = $result->fetch_assoc()) { 
        $users[] = $row; 
    } 
} 

// Fetch pending change requests 
$changeRequests = []; 
$requestResult = $conn->query(" 
    SELECT r.*, u.fullName, u.email as currentEmail, u.phone as currentPhone 
    FROM requestChanges r 
    JOIN users u ON r.userID = u.userID 
    ORDER BY r.createdAt DESC 
"); 

if ($requestResult) { 
    while ($row = $requestResult->fetch_assoc()) { 
        $changeRequests[] = $row; 
    } 
} 

// Function to format phone number for display 
function formatPhoneNumber($phone) { 
    if (strlen($phone) >= 10) { 
        // Check if it starts with '60' for Malaysia country code 
        if (substr($phone, 0, 2) === '60') { 
            // Format as +60 XX-XXX XXXX or +60 XXX-XXXXXXX 
            // This assumes a common Malaysian format, adjust as needed 
            if (strlen($phone) === 10) { // e.g., 6012345678 
                return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7); 
            } elseif (strlen($phone) === 11) { // e.g., 60123456789 
                return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 3) . '-' . substr($phone, 5, 3) . ' ' . substr($phone, 8); 
            } 
        } else { 
            // If it doesn't start with '60', assume it's a local number that needs +60 prefix 
            // This is a common pattern, but you might need to refine based on actual data 
            if (strlen($phone) === 9) { // e.g., 012345678 
                return '+60 ' . substr($phone, 1, 2) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6); 
            } elseif (strlen($phone) === 10) { // e.g., 0123456789 
                return '+60 ' . substr($phone, 1, 3) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7); 
            } 
        } 
    } 
    return $phone; // Return as is if not long enough or doesn't match expected patterns 
} 

$conn->close(); // Close the main database connection at the very end 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Manage Users - Crony Karaoke Admin</title> 
    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> 
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
        .tab-button { 
            transition: all 0.3s ease; 
        } 
        .tab-button.active { 
            background-color: #3b82f6; 
            color: white; 
        } 
        .tab-content { 
            display: none; 
        } 
        .tab-content.active { 
            display: block; 
        } 
        .request-badge { 
            animation: pulse 2s infinite; 
        } 
        @keyframes pulse { 
            0% { 
                transform: scale(1); 
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); 
            } 
            70% { 
                transform: scale(1.05); 
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); 
            } 
            100% { 
                transform: scale(1); 
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); 
            } 
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
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">User Management</h1> 

        <div class="flex mb-6 bg-gray-200 rounded-lg p-1"> 
            <button id="usersTab" class="tab-button flex-1 py-2 px-4 rounded-md text-sm font-medium active"> 
                <i class="fas fa-users mr-2"></i> 
                Users (<?php echo count($users); ?>) 
            </button> 
            <button id="requestsTab" class="tab-button flex-1 py-2 px-4 rounded-md text-sm font-medium"> 
                <i class="fas fa-clock mr-2"></i> 
                Change Requests 
                <?php if (count($changeRequests) > 0): ?> 
                    <span class="request-badge bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-2"><?php echo count($changeRequests); ?></span> 
                <?php endif; ?> 
            </button> 
        </div> 

        <div id="usersContent" class="tab-content active"> 
            <div class="mb-6"> 
                <form method="GET" action="" class="flex space-x-2"> 
                    <input type="text" name="search" placeholder="Search users by ID, name, email, phone, or role..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            value="<?php echo htmlspecialchars($searchTerm); ?>"> 
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-300">Search</button> 
                    <?php if (!empty($searchTerm)): ?> 
                        <button type="button" onclick="window.location.href='manage_users.php'" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400 transition duration-300">Clear</button> 
                    <?php endif; ?> 
                </form> 
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
                                    <td class="py-3 px-6 text-left"><?php echo formatPhoneNumber($user['phone']); ?></td> 
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
        </div> 

        <div id="requestsContent" class="tab-content"> 
            <?php if (empty($changeRequests)): ?> 
                <div class="text-center py-8"> 
                    <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i> 
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Pending Requests</h3> 
                    <p class="text-gray-500">All change requests have been processed.</p> 
                </div> 
            <?php else: ?> 
                <div class="space-y-4"> 
                    <?php foreach ($changeRequests as $request): ?> 
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6"> 
                            <div class="flex justify-between items-start mb-4"> 
                                <div> 
                                    <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($request['fullName']); ?></h3> 
                                    <p class="text-sm text-gray-600">User ID: <?php echo htmlspecialchars($request['userID']); ?> | Submitted: <?php echo date('M d, Y H:i', strtotime($request['createdAt'])); ?></p> 
                                </div> 
                                <div class="flex space-x-2"> 
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this change request?');"> 
                                        <input type="hidden" name="approve_request" value="1"> 
                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['reqID']); ?>"> <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded-md text-sm hover:bg-green-600 transition duration-300"> 
                                            <i class="fas fa-check mr-1"></i>Approve 
                                        </button> 
                                    </form> 
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reject this change request?');"> 
                                        <input type="hidden" name="reject_request" value="1"> 
                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['reqID']); ?>"> <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-md text-sm hover:bg-red-600 transition duration-300"> 
                                            <i class="fas fa-times mr-1"></i>Reject 
                                        </button> 
                                    </form> 
                                </div> 
                            </div> 
                            
                            <div class="grid md:grid-cols-2 gap-4"> 
                                <?php if ($request['emailChange'] === 'yes'): ?> 
                                    <div class="bg-white p-4 rounded-md border"> 
                                        <h4 class="font-medium text-gray-800 mb-2">Email Change Request</h4> 
                                        <div class="text-sm"> 
                                            <p><span class="font-medium">Current:</span> <?php echo htmlspecialchars($request['currentEmail']); ?></p> 
                                            <p><span class="font-medium text-blue-600">New:</span> <?php echo htmlspecialchars($request['newEmail']); ?></p> 
                                        </div> 
                                    </div> 
                                <?php endif; ?> 
                                
                                <?php if ($request['phnoChange'] === 'yes'): ?> 
                                    <div class="bg-white p-4 rounded-md border"> 
                                        <h4 class="font-medium text-gray-800 mb-2">Phone Change Request</h4> 
                                        <div class="text-sm"> 
                                            <p><span class="font-medium">Current:</span> <?php echo formatPhoneNumber($request['currentPhone']); ?></p> 
                                            <p><span class="font-medium text-blue-600">New:</span> <?php echo formatPhoneNumber($request['newPNo']); ?></p> 
                                        </div> 
                                    </div> 
                                <?php endif; ?> 
                            </div> 
                        </div> 
                    <?php endforeach; ?> 
                </div> 
            <?php endif; ?> 
        </div> 

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="admin_dashboard.php" class="font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition-colors duration-200 inline-block">
            Back to Admin Dashboard
            </a>
        </p> 
    </div> 

    <script> 
        // Tab functionality 
        document.getElementById('usersTab').addEventListener('click', function() { 
            switchTab('users'); 
        }); 

        document.getElementById('requestsTab').addEventListener('click', function() { 
            switchTab('requests'); 
        }); 

        function switchTab(tab) { 
            // Remove active class from all tabs and content 
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active')); 
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active')); 
            
            // Add active class to selected tab and content 
            if (tab === 'users') { 
                document.getElementById('usersTab').classList.add('active'); 
                document.getElementById('usersContent').classList.add('active'); 
            } else { 
                document.getElementById('requestsTab').classList.add('active'); 
                document.getElementById('requestsContent').classList.add('active'); 
            } 
        } 

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
            // Optional: Auto-switch to requests tab if there are pending requests on page load 
            <?php if (count($changeRequests) > 0 && empty($_POST)): ?> 
                switchTab('requests'); 
            <?php endif; ?> 
        }); 

    </script> 
</body> 
</html>