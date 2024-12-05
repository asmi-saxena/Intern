<?php
session_start();
include("../api/connect.php");

// Check if the user is logged in and has a valid user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information from employees and departments tables
$query = "SELECT e.employee_id, e.first_name, e.last_name, e.position, e.hire_date, d.department_name
          FROM employees e
          JOIN departments d ON e.department_id = d.department_id
          WHERE e.user_id = ?";

$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user_data = mysqli_fetch_assoc($result)) {
    // User data fetched successfully
} else {
    // Handle the case where no user data is found
    $error_message = "No user data found.";
}

// Check if the user is currently checked in
$check_status_query = "SELECT * FROM daily_checkin WHERE empid = ? AND checkout_time IS NULL ORDER BY checkin_time DESC LIMIT 1";
$check_status_stmt = mysqli_prepare($connect, $check_status_query);
mysqli_stmt_bind_param($check_status_stmt, "s", $user_data['employee_id']);
mysqli_stmt_execute($check_status_stmt);
$check_status_result = mysqli_stmt_get_result($check_status_stmt);
$is_checked_in = mysqli_num_rows($check_status_result) > 0;

// Handle check-in/check-out actions
if (isset($_POST['action'])) {
    $current_time = date('Y-m-d H:i:s');
    if ($_POST['action'] == 'check_in' && !$is_checked_in) {
        $check_in_query = "INSERT INTO daily_checkin (empid, checkin_time, system_checkin_time) VALUES (?, ?, ?)";
        $check_in_stmt = mysqli_prepare($connect, $check_in_query);
        mysqli_stmt_bind_param($check_in_stmt, "sss", $user_data['employee_id'], $current_time, $current_time);
        mysqli_stmt_execute($check_in_stmt);
        $is_checked_in = true;
    } elseif ($_POST['action'] == 'check_out' && $is_checked_in) {
        $check_out_query = "UPDATE daily_checkin SET checkout_time = ?, system_checkout_time = ? WHERE empid = ? AND checkout_time IS NULL";
        $check_out_stmt = mysqli_prepare($connect, $check_out_query);
        mysqli_stmt_bind_param($check_out_stmt, "sss", $current_time, $current_time, $user_data['employee_id']);
        mysqli_stmt_execute($check_out_stmt);
        $is_checked_in = false;
    }
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">User Dashboard</h1>
            <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Logout</a>
        </header>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php else: ?>
            <main>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:px-6 flex items-center">
                        <div class="flex-shrink-0 h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center text-2xl font-bold text-white">
                            <?php echo strtoupper(substr($user_data['first_name'], 0, 1) . substr($user_data['last_name'], 0, 1)); ?>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold leading-6 text-gray-900">
                                <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>
                            </h2>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                <?php echo htmlspecialchars($user_data['position']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo htmlspecialchars($user_data['employee_id']); ?>
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo htmlspecialchars($user_data['department_name']); ?>
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo date('F j, Y', strtotime($user_data['hire_date'])); ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Attendance</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-center">
                            <form method="POST" class="flex space-x-4">
                                <button type="submit" name="action" value="check_in" class="<?php echo $is_checked_in ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600'; ?> text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" <?php echo $is_checked_in ? 'disabled' : ''; ?>>
                                    Check In
                                </button>
                                <button type="submit" name="action" value="check_out" class="<?php echo !$is_checked_in ? 'bg-gray-300 cursor-not-allowed' : 'bg-red-500 hover:bg-red-600'; ?> text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" <?php echo !$is_checked_in ? 'disabled' : ''; ?>>
                                    Check Out
                                </button>
                            </form>
                            <a href="timesheet.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                View Timesheet
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-500">No recent activity to display.</p>
                        </div>
                    </div>
                </div>
            </main>
        <?php endif; ?>

        <footer class="mt-8 text-center">
            <p class="text-sm text-gray-500"></p>
        </footer>
    </div>

    <script>
        // You can add any client-side JavaScript here if needed
    </script>
</body>
</html>