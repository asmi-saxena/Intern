<?php
session_start();
if ($_SESSION['role'] != '3') {
    header("Location: login.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colocation Dashboard - Dreamforce 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <header class="bg-purple-600 text-white p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">Colocation Dashboard</h1>
                <a href="login.php" class="bg-white text-purple-600 px-4 py-2 rounded hover:bg-purple-100">Logout</a>
            </div>
        </header>
        <main class="flex-grow container mx-auto mt-8 p-4">
            <main class="flex-grow container mx-auto mt-8 p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Event Management</h2>
                    <p>Manage Dreamforce events and schedules.</p>
                    <a href="#" class="mt-4 inline-block bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-100">Manage Events</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Attendee Management</h2>
                    <p>Manage attendee registrations and information.</p>
                    <a href="#" class="mt-4 inline-block bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-100">Manage Attendees</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Reports</h2>
                    <p>Generate and view event reports.</p>
                    <a href="#" class="mt-4 inline-block bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-100">View Reports</a>
                </div>
            </div>
        </main>
        <footer class="bg-gray-200 text-center p-4 mt-8">
            
        </footer>
    </div>
</body>
</html>