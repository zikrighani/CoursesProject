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

// Create uploads directory if it doesn't exist
$uploadDir = '../assets/images/packages/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Function to handle feature insertion
function insertFeatures($conn, $packageID, $features) {
    if (empty($features)) {
        return true; // No features to insert, consider it success
    }
    $stmt = $conn->prepare("INSERT INTO package_features (packageID, featureText) VALUES (?, ?)");
    foreach ($features as $feature) {
        if (!empty(trim($feature))) { // Only insert non-empty features
            $stmt->bind_param("is", $packageID, $feature);
            if (!$stmt->execute()) {
                error_log("Error inserting feature: " . $stmt->error);
                $stmt->close();
                return false;
            }
        }
    }
    $stmt->close();
    return true;
}

// Handle Add Package Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
    $packageName = $_POST['new_package_name'];
    $pricePerHour = $_POST['new_package_price'];
    $description = $_POST['new_package_description'];
    $newFeatures = isset($_POST['new_package_features']) ? $_POST['new_package_features'] : [];
    $imagePath = null;

    // Handle image upload
    if (isset($_FILES['new_package_image']) && $_FILES['new_package_image']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['new_package_image'];
        $fileName = basename($uploadedFile['name']); // Use basename to prevent directory traversal
        $fileTmpName = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];

        // Validate file type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        $fileType = mime_content_type($fileTmpName);

        if (in_array($fileType, $allowedTypes)) {
            // Validate file size (max 5MB)
            if ($fileSize <= 5 * 1024 * 1024) {
                // Generate a unique filename to prevent overwriting and improve security
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid('package_', true) . '.' . $fileExtension;
                $targetPath = $uploadDir . $uniqueFileName;

                if (move_uploaded_file($fileTmpName, $targetPath)) {
                    $imagePath = $uniqueFileName;
                } else {
                    $message = 'Error uploading image file.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Image file size must be less than 5MB.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid image format. Please use PNG, JPG, JPEG, or WebP.';
            $messageType = 'error';
        }
    }

    // Basic validation
    if (!empty($packageName) && is_numeric($pricePerHour) && $pricePerHour >= 0 && $messageType !== 'error') {
        $conn->begin_transaction(); // Start transaction

        $stmt = $conn->prepare("INSERT INTO packages (packageName, description, pricePerHour, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $packageName, $description, $pricePerHour, $imagePath);
        if ($stmt->execute()) {
            $newPackageID = $conn->insert_id;
            if (insertFeatures($conn, $newPackageID, $newFeatures)) {
                $conn->commit(); // Commit transaction if all successful
                $message = 'Package added successfully!';
                $messageType = 'success';
            } else {
                $conn->rollback(); // Rollback if feature insertion fails
                $message = 'Error adding package features.';
                $messageType = 'error';
                // Delete uploaded image if database insert failed
                if ($imagePath && file_exists($uploadDir . $imagePath)) {
                    unlink($uploadDir . $imagePath);
                }
            }
        } else {
            $conn->rollback(); // Rollback if package insertion fails
            $message = 'Error adding package: ' . $stmt->error;
            $messageType = 'error';
            // Delete uploaded image if database insert failed
            if ($imagePath && file_exists($uploadDir . $imagePath)) {
                unlink($uploadDir . $imagePath);
            }
        }
        $stmt->close();
    } else if ($messageType !== 'error') {
        $message = 'Please fill in all required fields correctly (Package Name, Price, and valid Image if uploaded).';
        $messageType = 'error';
    }
}

// Handle Edit Package Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_package'])) {
    $packageID = $_POST['edit_package_id'];
    $packageName = $_POST['edit_package_name'];
    $pricePerHour = $_POST['edit_package_price'];
    $description = $_POST['edit_package_description'];
    $newFeatures = isset($_POST['edit_package_features']) ? $_POST['edit_package_features'] : [];
    $imagePath = $_POST['current_image']; // Keep current image by default

    // Get current package data (especially image path)
    $currentPackageQuery = $conn->prepare("SELECT image FROM packages WHERE packageID = ?");
    $currentPackageQuery->bind_param("i", $packageID);
    $currentPackageQuery->execute();
    $currentPackageResult = $currentPackageQuery->get_result();
    $currentPackage = $currentPackageResult->fetch_assoc();
    $currentImage = $currentPackage['image'];
    $currentPackageQuery->close();

    // Handle image upload for edit
    if (isset($_FILES['edit_package_image']) && $_FILES['edit_package_image']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['edit_package_image'];
        $fileName = basename($uploadedFile['name']); // Use basename
        $fileTmpName = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];

        // Validate file type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        $fileType = mime_content_type($fileTmpName);

        if (in_array($fileType, $allowedTypes)) {
            // Validate file size (max 5MB)
            if ($fileSize <= 5 * 1024 * 1024) {
                // Generate a unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid('package_', true) . '.' . $fileExtension;
                $targetPath = $uploadDir . $uniqueFileName;

                if (move_uploaded_file($fileTmpName, $targetPath)) {
                    // Delete old image if it exists and is different from the new one
                    if ($currentImage && $currentImage !== $uniqueFileName && file_exists($uploadDir . $currentImage)) {
                        unlink($uploadDir . $currentImage);
                    }
                    $imagePath = $uniqueFileName;
                } else {
                    $message = 'Error uploading new image file.';
                    $messageType = 'error';
                }
            } else {
                $message = 'New image file size must be less than 5MB.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid new image format. Please use PNG, JPG, JPEG, or WebP.';
            $messageType = 'error';
        }
    }

    if (!empty($packageName) && is_numeric($pricePerHour) && $pricePerHour >= 0 && $messageType !== 'error') {
        $conn->begin_transaction(); // Start transaction

        $stmt = $conn->prepare("UPDATE packages SET packageName = ?, description = ?, pricePerHour = ?, image = ? WHERE packageID = ?");
        $stmt->bind_param("ssdsi", $packageName, $description, $pricePerHour, $imagePath, $packageID);
        if ($stmt->execute()) {
            // Delete existing features for this package
            $deleteFeaturesStmt = $conn->prepare("DELETE FROM package_features WHERE packageID = ?");
            $deleteFeaturesStmt->bind_param("i", $packageID);
            $deleteFeaturesStmt->execute();
            $deleteFeaturesStmt->close();

            // Insert new features
            if (insertFeatures($conn, $packageID, $newFeatures)) {
                $conn->commit(); // Commit transaction
                $message = 'Package updated successfully!';
                $messageType = 'success';
            } else {
                $conn->rollback(); // Rollback if feature insertion fails
                $message = 'Error updating package features.';
                $messageType = 'error';
                // Note: Image deletion for failed updates is trickier if you've already moved it.
                // For simplicity, we assume image upload success leads to database success here.
            }
        } else {
            $conn->rollback(); // Rollback if package update fails
            $message = 'Error updating package: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } else if ($messageType !== 'error') {
        $message = 'Please fill in all required fields correctly for editing.';
        $messageType = 'error';
    }
}

// Handle Delete Package Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_package'])) {
    $packageID = $_POST['delete_package_id'];

    // Get image path before deleting
    $imageQuery = $conn->prepare("SELECT image FROM packages WHERE packageID = ?");
    $imageQuery->bind_param("i", $packageID);
    $imageQuery->execute();
    $imageResult = $imageQuery->get_result();
    $imageData = $imageResult->fetch_assoc();
    $imageFileName = $imageData['image']; // Get the file name
    $imageQuery->close();

    $conn->begin_transaction(); // Start transaction for deletion

    // IMPORTANT: Before deleting a package, ensure no rooms are linked to it.
    // This part of your original code checks for this implicitly by attempting deletion.
    // If you have ON DELETE RESTRICT on rooms.packageID, it will fail here.
    // If you have ON DELETE SET NULL or CASCADE, it will work.
    $stmt = $conn->prepare("DELETE FROM packages WHERE packageID = ?");
    $stmt->bind_param("i", $packageID);
    if ($stmt->execute()) {
        // Since package_features has ON DELETE CASCADE, features will be deleted automatically.

        // Delete associated image file
        if ($imageFileName && file_exists($uploadDir . $imageFileName)) {
            unlink($uploadDir . $imageFileName);
        }
        $conn->commit(); // Commit transaction
        $message = 'Package deleted successfully!';
        $messageType = 'success';
    } else {
        $conn->rollback(); // Rollback if package deletion fails
        $message = 'Error deleting package. Ensure no rooms are currently using this package type: ' . $stmt->error;
        $messageType = 'error';
    }
    $stmt->close();
}

// Fetch existing packages and their features
$packages = [];
// Join packages with package_features to get all features for each package
$query = "SELECT p.packageID, p.packageName, p.description, p.pricePerHour, p.image, pf.featureText
          FROM packages p
          LEFT JOIN package_features pf ON p.packageID = pf.packageID
          ORDER BY p.packageName ASC, pf.featureID ASC"; // Order by featureID to maintain order

$result = $conn->query($query);
if ($result) {
    $tempPackages = [];
    while ($row = $result->fetch_assoc()) {
        $packageID = $row['packageID'];
        if (!isset($tempPackages[$packageID])) {
            $tempPackages[$packageID] = [
                'packageID' => $row['packageID'],
                'packageName' => $row['packageName'],
                'description' => $row['description'],
                'pricePerHour' => $row['pricePerHour'],
                'image' => $row['image'],
                'features' => [] // Initialize features array
            ];
        }
        if ($row['featureText'] !== null) { // Only add if there's a feature
            $tempPackages[$packageID]['features'][] = $row['featureText'];
        }
    }
    $packages = array_values($tempPackages); // Convert associative array back to indexed array
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
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .image-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s;
        }
        .image-upload-area:hover {
            border-color: #6b7280;
        }
        .image-upload-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
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
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Manage Room Packages</h1>

        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New Package</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="add_package" value="1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="new_package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <input type="text" id="new_package_name" name="new_package_name" placeholder="e.g., Standard Package" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="new_package_price" class="block text-sm font-medium text-gray-700 mb-1">Price per Hour (RM)</label>
                        <input type="number" id="new_package_price" name="new_package_price" step="0.50" min="0" placeholder="e.g., 25.00" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                </div>
                <div>
                    <label for="new_package_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (Summary)</label>
                    <textarea id="new_package_description" name="new_package_description" rows="2" placeholder="e.g., Cozy room, up to 4 people with basic amenities." class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Package Features (One per line)</label>
                    <div id="new-features-container" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="text" name="new_package_features[]" placeholder="e.g., Premium sound system" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <button type="button" onclick="removeFeatureInput(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-300 text-sm">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addFeatureInput('new-features-container', 'new_package_features[]')" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300 text-sm">Add Another Feature</button>
                </div>
                <div>
                    <label for="new_package_image" class="block text-sm font-medium text-gray-700 mb-1">Package Image</label>
                    <div class="image-upload-area" onclick="document.getElementById('new_package_image').click()">
                        <input type="file" id="new_package_image" name="new_package_image" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden" onchange="previewImage(this, 'add-preview')">
                        <div id="add-preview" class="mb-2"></div>
                        <p class="text-gray-500">Click to upload image or drag and drop</p>
                        <p class="text-xs text-gray-400">PNG, JPG, JPEG, WebP (Max 5MB)</p>
                    </div>
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
                            <div class="flex items-center space-x-4">
                                <?php if ($package['image']): ?>
                                    <img src="../assets/images/packages/<?php echo htmlspecialchars($package['image']); ?>" alt="<?php echo htmlspecialchars($package['packageName']); ?>" class="image-preview">
                                <?php else: ?>
                                    <div class="w-32 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($package['packageName']); ?></h3>
                                    <p class="text-sm text-gray-600">Price: RM <?php echo htmlspecialchars(number_format($package['pricePerHour'], 2)); ?>/hour</p>
                                    <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($package['description']); ?></p>
                                    <?php if (!empty($package['features'])): ?>
                                        <ul class="list-disc list-inside text-xs text-gray-700 mt-1">
                                            <?php foreach ($package['features'] as $feature): ?>
                                                <li><?php echo htmlspecialchars($feature); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-xs text-gray-400">No specific features listed.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="showEditPackageForm(<?php echo htmlspecialchars(json_encode($package)); ?>)" class="bg-yellow-500 text-white py-1 px-3 rounded-md text-sm hover:bg-yellow-600 transition duration-300">Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this package? This will also delete all associated features and might affect rooms linked to it!');">
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
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="edit_package" value="1">
                <input type="hidden" id="edit_package_id" name="edit_package_id">
                <input type="hidden" id="current_image" name="current_image">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <input type="text" id="edit_package_name" name="edit_package_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="edit_package_price" class="block text-sm font-medium text-gray-700 mb-1">Price per Hour (RM)</label>
                        <input type="number" id="edit_package_price" name="edit_package_price" step="0.50" min="0" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                </div>
                <div>
                    <label for="edit_package_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (Summary)</label>
                    <textarea id="edit_package_description" name="edit_package_description" rows="2" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Package Features (One per line)</label>
                    <div id="edit-features-container" class="space-y-2">
                        </div>
                    <button type="button" onclick="addFeatureInput('edit-features-container', 'edit_package_features[]')" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300 text-sm">Add Another Feature</button>
                </div>
                <div>
                    <label for="edit_package_image" class="block text-sm font-medium text-gray-700 mb-1">Package Image</label>
                    <div id="current-image-preview" class="mb-2"></div>
                    <div class="image-upload-area" onclick="document.getElementById('edit_package_image').click()">
                        <input type="file" id="edit_package_image" name="edit_package_image" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden" onchange="previewImage(this, 'edit-preview')">
                        <div id="edit-preview" class="mb-2"></div>
                        <p class="text-gray-500">Click to upload new image or drag and drop</p>
                        <p class="text-xs text-gray-400">PNG, JPG, JPEG, WebP (Max 5MB) - Leave empty to keep current image</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditPackageForm()" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg text-md font-semibold hover:bg-gray-400 transition duration-300">Cancel</button>
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
        // Function to preview uploaded images
        function previewImage(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            previewContainer.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview mx-auto';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Function to add a new feature input field
        function addFeatureInput(containerId, inputName, value = '') {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2';
            div.innerHTML = `
                <input type="text" name="${inputName}" placeholder="e.g., Premium sound system" value="${value}" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <button type="button" onclick="removeFeatureInput(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-300 text-sm">Remove</button>
            `;
            container.appendChild(div);
        }

        // Function to remove a feature input field
        function removeFeatureInput(button) {
            button.parentNode.remove();
        }

        // Function to show the edit form for Packages and populate it with data
        function showEditPackageForm(packageData) {
            document.getElementById('edit_package_id').value = packageData.packageID;
            document.getElementById('edit_package_name').value = packageData.packageName;
            document.getElementById('edit_package_description').value = packageData.description;
            document.getElementById('edit_package_price').value = parseFloat(packageData.pricePerHour);
            document.getElementById('current_image').value = packageData.image || '';
            
            // Populate features for editing
            const editFeaturesContainer = document.getElementById('edit-features-container');
            editFeaturesContainer.innerHTML = ''; // Clear previous features
            if (packageData.features && packageData.features.length > 0) {
                packageData.features.forEach(feature => {
                    addFeatureInput('edit-features-container', 'edit_package_features[]', feature);
                });
            } else {
                // Add one empty input if no features exist
                addFeatureInput('edit-features-container', 'edit_package_features[]');
            }

            // Show current image
            const currentImagePreview = document.getElementById('current-image-preview');
            currentImagePreview.innerHTML = '';
            if (packageData.image) {
                currentImagePreview.innerHTML = `
                    <div class="mb-2">
                        <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                        <img src="../assets/images/packages/${packageData.image}" alt="${packageData.packageName}" class="image-preview">
                    </div>
                `;
            }
            
            document.getElementById('editPackageFormContainer').classList.remove('hidden');
            document.getElementById('editPackageFormContainer').scrollIntoView({ behavior: 'smooth' });
        }

        // Function to hide the edit form for Packages
        function hideEditPackageForm() {
            document.getElementById('editPackageFormContainer').classList.add('hidden');
            document.getElementById('edit-preview').innerHTML = '';
            document.getElementById('current-image-preview').innerHTML = '';
        }

        // Auto-hide messages after a few seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer.children.length > 0) {
                setTimeout(() => {
                    messageContainer.classList.add('hidden');
                }, 5000); // Hide after 5 seconds
            }

            // Add drag and drop functionality
            const uploadAreas = document.querySelectorAll('.image-upload-area');
            uploadAreas.forEach(area => {
                area.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                area.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                area.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        const fileInput = this.querySelector('input[type="file"]');
                        fileInput.files = files;
                        
                        // Trigger preview
                        const previewId = fileInput.getAttribute('onchange').match(/'([^']+)'/)[1];
                        previewImage(fileInput, previewId);
                    }
                });
            });

            // Add one default empty feature input for the 'Add New Package' form
            addFeatureInput('new-features-container', 'new_package_features[]');
        });
    </script>
</body>
</html>