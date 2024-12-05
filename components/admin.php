<?php
session_start();
include("../api/connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '2') {
    header("Location: login.php");
    exit();
}

// Fetch timesheets
$timesheet_query = "SELECT t.*, e.first_name, e.last_name 
                    FROM daily_timesheet t
                    JOIN employees e ON t.emp_id = e.employee_id
                    ORDER BY t.date DESC";
$timesheet_result = mysqli_query($connect, $timesheet_query);

// Fetch check-in/check-out times
$checkin_query = "SELECT c.*, e.first_name, e.last_name 
                  FROM daily_checkin c
                  JOIN employees e ON c.empid = e.employee_id
                  ORDER BY c.checkin_time DESC";
$checkin_result = mysqli_query($connect, $checkin_query);

// Handle filter submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_date'])) {
    $filter_date = mysqli_real_escape_string($connect, $_POST['filter_date']);
    $timesheet_query .= " WHERE t.date = '$filter_date'";
    $timesheet_result = mysqli_query($connect, $timesheet_query);
}

// Get total employees count
$total_query = "SELECT COUNT(*) as total FROM employees";
$total_result = mysqli_query($connect, $total_query);
$total_employees = mysqli_fetch_assoc($total_result)['total'];

// Get working today count
$working_query = "SELECT COUNT(DISTINCT empid) as working 
                 FROM daily_checkin 
                 WHERE DATE(checkin_time) = CURDATE() 
                 AND checkout_time IS NULL";
$working_result = mysqli_query($connect, $working_query);
$working_today = mysqli_fetch_assoc($working_result)['working'];

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-600 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold mb-6">Menu</h2>
                <nav class="space-y-2">
                    <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700 transition-colors" onclick="showSection('dashboard')">Dashboard</a>
                    <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700 transition-colors" onclick="showSection('timesheet')">Timesheet</a>
                    <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700 transition-colors" onclick="showSection('checkin-checkout')">Check-in/Check-out</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-8 py-4">
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                        Logout
                    </a>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="p-8">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="section">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-gray-500 text-sm font-medium">Total Employees</h3>
                            <p class="text-4xl font-bold mt-2"><?php echo $total_employees; ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-gray-500 text-sm font-medium">Working Today</h3>
                            <p class="text-4xl font-bold mt-2"><?php echo $working_today; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Timesheet Section -->
                <div id="timesheet-section" class="section hidden">
                    <div class="bg-white rounded-lg shadow-sm mb-8">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-4">Timesheets</h2>
                            <form method="POST" class="mb-4">
                                <div class="flex items-center">
                                    <label for="filter_date" class="mr-2">Filter by date:</label>
                                    <input type="date" id="filter_date" name="filter_date" 
                                           class="border rounded px-2 py-1 mr-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                                        Filter
                                    </button>
                                </div>
                            </form>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($timesheet_result)): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['date']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['task']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['no_of_hours']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <?php
                                                    $status_text = '';
                                                    $status_color = '';
                                                    switch ($row['status']) {
                                                        case 1:
                                                            $status_text = 'Done';
                                                            $status_color = 'text-green-600';
                                                            break;
                                                        case 2:
                                                            $status_text = 'In Process';
                                                            $status_color = 'text-yellow-600';
                                                            break;
                                                        case 3:
                                                            $status_text = 'Not Done';
                                                            $status_color = 'text-red-600';
                                                            break;
                                                        default:
                                                            $status_text = 'Unknown';
                                                            $status_color = 'text-gray-600';
                                                    }
                                                    echo "<span class='$status_color font-semibold'>$status_text</span>";
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-in/Check-out Section -->
                <div id="checkin-checkout-section" class="section hidden">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-4">Check-in/Check-out Times</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($checkin_result)): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['checkin_time']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $row['checkout_time'] ? htmlspecialchars($row['checkout_time']) : 'Not checked out'; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });

            // Show the selected section
            document.getElementById(sectionId + '-section').classList.remove('hidden');
        }

        // Show dashboard by default
        showSection('dashboard');
    </script>
</body>
</html>