<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != '1') {
    header("Location: login.php");
    exit();
}

include("../api/connect.php");

// Function to encrypt password using bcrypt
function encryptPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to generate employee ID based on auto-incrementing id
function generateEmployeeID($connect) {
    $query = "SELECT MAX(id) as max_id FROM employees";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    $next_id = ($row['max_id'] ?? 0) + 1;
    
    return 'emp' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
}

// Updated function to log activity
function logActivity($connect, $superadmin_emp_id, $logged_in_user_id) {
    $query = "INSERT INTO activity_records (superadmin_emp_id, logged_in_user_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $superadmin_emp_id, $logged_in_user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
    $first_name = mysqli_real_escape_string($connect, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($connect, $_POST['last_name']);
    $department_id = mysqli_real_escape_string($connect, $_POST['department_id']);
    $position = mysqli_real_escape_string($connect, $_POST['position']);
    $hire_date = mysqli_real_escape_string($connect, $_POST['hire_date']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $role = mysqli_real_escape_string($connect, $_POST['role']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

    $username = strtolower($first_name . '.' . $last_name);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    // Encrypt the password
    $encrypted_password = encryptPassword($password);

    // Generate employee ID
    $employee_id = generateEmployeeID($connect);

    // Insert into users table
    $insert_user_query = "INSERT INTO users (username, email, password, role, created_at, updated_at) VALUES ('$username', '$email', '$encrypted_password', '$role', '$created_at', '$updated_at')";
    if (mysqli_query($connect, $insert_user_query)) {
        $user_id = mysqli_insert_id($connect);

        // Insert into employees table
        $insert_employee_query = "INSERT INTO employees (employee_id, user_id, first_name, last_name, department_id, position, hire_date) VALUES ('$employee_id', '$user_id', '$first_name', '$last_name', '$department_id', '$position', '$hire_date')";
        if (mysqli_query($connect, $insert_employee_query)) {
            $success_message = "Employee added successfully! Employee ID: $employee_id";
        } else {
            $error_message = "Error adding employee: " . mysqli_error($connect);
        }
    } else {
        $error_message = "Error adding user: " . mysqli_error($connect);
    }
}

// Handle employee login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_login'])) {
    $user_id = mysqli_real_escape_string($connect, $_POST['user_id']);
    
    // Fetch user details
    $user_query = "SELECT u.*, e.employee_id, e.first_name, e.last_name, e.department_id, e.position, e.hire_date, d.department_name 
                   FROM users u 
                   JOIN employees e ON u.user_id = e.user_id 
                   JOIN departments d ON e.department_id = d.department_id 
                   WHERE u.user_id = '$user_id'";
    $user_result = mysqli_query($connect, $user_query);
    
    if ($user = mysqli_fetch_assoc($user_result)) {
        // Log the activity
        $superadmin_emp_id = $_SESSION['employee_id']; 
        logActivity($connect, $superadmin_emp_id, $user['employee_id']);

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['department'] = $user['department_name'];
        
        // Redirect to appropriate dashboard
        switch ($user['role']) {
            case 2:
                header("Location: admin.php");
                break;
            case 3:
                header("Location: user.php");
                break;
            case 4:
                header("Location: user.php");
                break;
            default:
                $error_message = "Invalid role";
                break;
        }
        exit();
    } else {
        $error_message = "Error logging in as employee";
    }
}

// Fetch total number of employees
$totalEmployeesQuery = "SELECT COUNT(*) as total FROM employees";
$totalEmployeesResult = mysqli_query($connect, $totalEmployeesQuery);
$totalEmployees = mysqli_fetch_assoc($totalEmployeesResult)['total'];

// Fetch number of employees working today
$workingTodayQuery = "SELECT COUNT(*) as working FROM employees WHERE employee_status = '1'";
$workingTodayResult = mysqli_query($connect, $workingTodayQuery);
$workingToday = mysqli_fetch_assoc($workingTodayResult)['working'];

// Fetch employees on leave
$onLeaveQuery = "SELECT e.employee_id, e.first_name, e.last_name, d.department_name 
                    FROM employees e 
                    LEFT JOIN departments d ON e.department_id = d.department_id 
                    WHERE e.employee_status = '2'";
$onLeaveResult = mysqli_query($connect, $onLeaveQuery);

// Fetch all employees for the list
$allEmployeesQuery = "SELECT e.employee_id, e.first_name, e.last_name, u.email, e.employee_status, d.department_name, u.user_id, u.role 
                        FROM employees e 
                        LEFT JOIN departments d ON e.department_id = d.department_id 
                        LEFT JOIN users u ON e.user_id = u.user_id 
                        ORDER BY e.employee_id";
$allEmployeesResult = mysqli_query($connect, $allEmployeesQuery);

// Fetch departments for the dropdown
$departmentsQuery = "SELECT department_id, department_name FROM departments";
$departmentsResult = mysqli_query($connect, $departmentsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Left Sidebar Navigation -->
        <nav class="w-64 bg-blue-600 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold mb-6">Menu</h2>
                <ul class="space-y-2">
                    <li><a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showPage('home')">Home</a></li>
                    <li><a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showPage('employees')">Employees</a></li>
                    <li><a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showPage('settings')">Settings</a></li>
                    <li><a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showPage('reports')">Reports</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Super Admin Dashboard</h1>
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Logout</a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4">
                <div class="max-w-7xl mx-auto">
                    <!-- Home Page Content -->
                    <div id="home-page">
                        <!-- Employee Statistics -->
                        <div class="mb-8 flex justify-center space-x-6">
                            <div class="bg-white p-6 rounded-lg shadow-md w-48 h-48 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">Total Employees</p>
                                    <p class="text-4xl font-bold"><?php echo $totalEmployees; ?></p>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow-md w-48 h-48 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">Working Today</p>
                                    <p class="text-4xl font-bold"><?php echo $workingToday; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Employees on Leave Table -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Employees on Leave</h3>
                            </div>
                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($employee = mysqli_fetch_assoc($onLeaveResult)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Employees Page Content -->
                    <div id="employees-page" class="hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">All Employees</h2>
                            <button onclick="showAddEmployeeForm()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Employee</button>
                        </div>
                        
                        <?php if (isset($success_message)): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?php echo $success_message; ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?php echo $error_message; ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Add Employee Form -->
                        <div id="add-employee-form" class="hidden bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                            <h3 class="text-xl font-bold mb-4">Add New Employee</h3>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">
                                        First Name
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="first_name" name="first_name" type="text" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">
                                        Last Name
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="last_name" name="last_name" type="text" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold  mb-2" for="department_id">
                                        Department
                                    </label>
                                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700  leading-tight focus:outline-none focus:shadow-outline" id="department_id" name="department_id" required>
                                        <?php 
                                        mysqli_data_seek($departmentsResult, 0);
                                        while ($department = mysqli_fetch_assoc($departmentsResult)): 
                                        ?>
                                            <option value="<?php echo $department['department_id']; ?>"><?php echo htmlspecialchars($department['department_name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="position">
                                        Position
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="position" name="position" type="text" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="hire_date">
                                        Hire Date
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hire_date" name="hire_date" type="date" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                        Email
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                                        Password
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                                        Role
                                    </label>
                                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="role" name="role" required>
                                        <option value="2">Admin</option>
                                        <option value="3">Manager</option>
                                        <option value="4">Employee</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between">
                                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="add_employee">
                                        Add Employee
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php 
                                    mysqli_data_seek($allEmployeesResult, 0);
                                    while ($employee = mysqli_fetch_assoc($allEmployeesResult)): 
                                        $statusText = '';
                                        if ($employee['employee_status'] == 0) {
                                            $statusText = 'Left the Organization';
                                        } elseif ($employee['employee_status'] == 1) {
                                            $statusText = 'Working Today';
                                        } elseif ($employee['employee_status'] == 2) {
                                            $statusText = 'On Leave';
                                        }
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($statusText); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" action="">
                                                <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                                                <button type="submit" name="employee_login" class="text-indigo-600 hover:text-indigo-900">Login as User</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Settings Page Content -->
                    <div id="settings-page" class="hidden">
                        <h2 class="text-2xl font-bold mb-4">Settings</h2>
                        <p>Settings page content goes here.</p>
                    </div>

                    <!-- Reports Page Content -->
                    <div id="reports-page" class="hidden">
                        <h2 class="text-2xl font-bold mb-4">Reports</h2>
                        <p>Reports page content goes here.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function showPage(page) {
            const pages = ['home', 'employees', 'settings', 'reports'];
            pages.forEach(p => {
                document.getElementById(p + '-page').classList.add('hidden');
            });
            document.getElementById(page + '-page').classList.remove('hidden');
        }

        function showAddEmployeeForm() {
            const form = document.getElementById('add-employee-form');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>