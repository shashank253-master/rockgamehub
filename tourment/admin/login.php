<?php
require_once('../common/config.php');

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_msg = 'Please fill in all fields';
    } else {
        // Use prepared statement
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error_msg = 'Invalid credentials';
            }
        } else {
            $error_msg = 'Admin not found';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Play - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        * {
            -webkit-touch-callout: none;
        }
    </style>
</head>
<body class="bg-slate-900 text-white overflow-hidden" oncontextmenu="return false">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-r from-red-500 to-pink-600 p-4 rounded-full mb-4">
                    <i class="fas fa-shield-alt text-3xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Rock Play</h1>
                <p class="text-gray-400">Admin Panel</p>
            </div>

            <!-- Login Card -->
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden p-8">
                <h2 class="text-2xl font-bold mb-6">Admin Login</h2>

                <!-- Messages -->
                <?php if ($error_msg): ?>
                    <div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                            <span class="text-red-200"><?php echo htmlspecialchars($error_msg); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <input type="text" name="username" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 transition" placeholder="Enter admin username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input type="password" name="password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 transition" placeholder="Enter your password">
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </form>

                <!-- Default Credentials Info -->
                <div class="bg-slate-700/50 rounded-lg p-4 mt-6 border-l-4 border-yellow-500">
                    <p class="text-yellow-200 text-xs font-semibold mb-2">
                        <i class="fas fa-info-circle mr-1"></i>Default Credentials
                    </p>
                    <p class="text-gray-400 text-xs">Username: <span class="text-gray-300 font-mono">admin</span></p>
                    <p class="text-gray-400 text-xs">Password: <span class="text-gray-300 font-mono">admin123</span></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p>Rock Play v1.0 &copy; 2025</p>
            </div>
        </div>
    </div>

    <script>
        // Disable right-click
        document.addEventListener('contextmenu', (e) => e.preventDefault());
        
        // Disable zoom
        document.addEventListener('wheel', (e) => {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });
        
        // Disable text selection
        document.addEventListener('selectstart', (e) => e.preventDefault());
    </script>
</body>
</html>
