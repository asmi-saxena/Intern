<?php
session_start();
include("../api/connect.php");

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare a statement to prevent SQL injection
    $stmt = $connect->prepare("SELECT u.user_id, u.username, u.role, e.employee_id, e.first_name, e.last_name, d.department_name 
                               FROM users u 
                               LEFT JOIN employees e ON u.user_id = e.user_id 
                               LEFT JOIN departments d ON e.department_id = d.department_id 
                               WHERE u.username = ? AND u.password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['department'] = $user['department_name'];
        
        // Redirect based on role
        switch ($user['role']) {
            case 1:
                header("Location: superadmin.php");
                break;
            case 2:
                header("Location: admin.php");
                break;
            case 3:
                header("Location: manager.php");
                break;
            case 4:
                header("Location: user.php");
                break;
            default:
                $error = "Invalid role";
                break;
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dreamforce 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Login
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm text-blue-600 hover:underline">Back to Registration</a>
        </div>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">Need help? Contact support:</p>
            <a href="mailto:support@dreamforce2025.com" class="text-sm text-blue-600 hover:underline">support@dreamforce2025.com</a>
        </div>
    </div>
</body>
</html>