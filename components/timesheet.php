<?php
session_start();
include("../api/connect.php");

// Check if the user is logged in and has a valid user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_query = "SELECT employee_id FROM employees WHERE user_id = ?";
$user_stmt = mysqli_prepare($connect, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_result);

$employee_id = $user_data['employee_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = trim($_POST['task']);
    $hours = floatval($_POST['hours']);
    $status = intval($_POST['status']);
    $date = date('Y-m-d');

    if (!empty($task) && $hours > 0 && $hours <= 24 && in_array($status, [1, 2, 3])) {
        $insert_query = "INSERT INTO daily_timesheet (employee_id, date, task, no_of_hours, status) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($connect, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sssdi", $employee_id, $date, $task, $hours, $status);
        mysqli_stmt_execute($insert_stmt);
    }
}

// Fetch timesheet data
$timesheet_query = "SELECT date, task, no_of_hours, status FROM daily_timesheet WHERE emp_id = ? ORDER BY date DESC LIMIT 10";
$timesheet_stmt = mysqli_prepare($connect, $timesheet_query);
mysqli_stmt_bind_param($timesheet_stmt, "s", $employee_id);
mysqli_stmt_execute($timesheet_stmt);
$timesheet_result = mysqli_stmt_get_result($timesheet_stmt);

mysqli_close($connect);

function getStatusText($status) {
    switch ($status) {
        case 1: return 'Done';
        case 2: return 'In Process';
        case 3: return 'Not Done';
        default: return 'Unknown';
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 1: return 'text-green-600';
        case 2: return 'text-yellow-600';
        case 3: return 'text-red-600';
        default: return 'text-gray-600';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Daily Timesheet - Dreamforce 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-center text-gray-800">Your Daily Timesheet</h1>
        </header>

        <main>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8 p-6">
                <h2 class="text-xl font-semibold mb-4">Add Timesheet Entry</h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                    <div>
                        <label for="task" class="block text-sm font-medium text-gray-700">What is your task?</label>
                        <input type="text" id="task" name="task" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="hours" class="block text-sm font-medium text-gray-700">Number of hours worked</label>
                        <input type="number" id="hours" name="hours" step="0.5" min="0.5" max="24" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="1">Done</option>
                            <option value="2">In Process</option>
                            <option value="3">Not Done</option>
                        </select>
                    </div>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Entry
                    </button>
                </form>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = mysqli_fetch_assoc($timesheet_result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['date']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['task']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['no_of_hours']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="<?php echo getStatusColor($row['status']); ?> font-semibold">
                                        <?php echo getStatusText($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="user.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Back to Dashboard
                </a>
            </div>
        </main>

        <footer class="mt-8 text-center">
            <p class="text-sm text-gray-500">&copy; 2025 Dreamforce. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>