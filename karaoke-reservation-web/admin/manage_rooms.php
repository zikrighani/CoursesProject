<?php
include '../dbconfig.php'; // Include your database configuration
session_start();


// Basic session check for admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect if not logged in or not admin
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Light gray background */
        }
        .selection-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #ffffff;
            color: #374151;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }
        .selection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .selection-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #4f46e5;
        }
        .selection-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .selection-description {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .btn-selection {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        .btn-selection:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Choose Room Management Option</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-8 rounded-lg shadow-md selection-card text-center">
                <div class="selection-icon">
                    <i class="fas fa-door-closed"></i> </div>
                <h2 class="selection-title">Manage Individual Rooms</h2>
                <p class="selection-description">Add, edit, or remove specific physical karaoke rooms (e.g., Room 101).</p>
                <a href="manage_individual_rooms.php" class="btn-selection">
                    Go to Management
                </a>
            </div>

            <div class="p-8 rounded-lg shadow-md selection-card text-center">
                <div class="selection-icon">
                    <i class="fas fa-box-open"></i> </div>
                <h2 class="selection-title">Manage Room Packages</h2>
                <p class="selection-description">Define and manage room types, pricing, and descriptions (e.g., Standard, VIP).</p>
                <a href="manage_packages.php" class="btn-selection">
                    Go to Management
                </a>
            </div>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="admin_dashboard.php" class="font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition-colors duration-200 inline-block">
            Back to Admin Dashboard
            </a>
        </p>
    </div>
</body>
</html>
