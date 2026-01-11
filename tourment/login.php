<?php
require_once('common/config.php');

$error_msg = '';
$success_msg = '';
$current_tab = 'login';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_msg = 'Please fill in all fields';
        $current_tab = 'login';
    } else {
        // Use prepared statement
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error_msg = 'Invalid credentials';
                $current_tab = 'login';
            }
        } else {
            $error_msg = 'User not found';
            $current_tab = 'login';
        }
        $stmt->close();
    }
}

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $username = trim($_POST['signup_username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = 'Please fill in all fields';
        $current_tab = 'signup';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Passwords do not match';
        $current_tab = 'signup';
    } elseif (strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters';
        $current_tab = 'signup';
    } else {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_msg = 'Username or email already exists';
            $current_tab = 'signup';
        } else {
            // Hash password and insert new user with initial wallet
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $initial_wallet = 0;

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, wallet_balance) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $username, $email, $hashed_password, $initial_wallet);

            if ($stmt->execute()) {
                $success_msg = 'Signup successful! Please log in.';
                $current_tab = 'login';
            } else {
                $error_msg = 'Error during signup. Please try again.';
                $current_tab = 'signup';
            }
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
    <title>Rock Play - Login</title>
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
                <div class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 p-4 rounded-full mb-4">
                    <i class="fas fa-gamepad text-3xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Rock Play</h1>
                <p class="text-gray-400">Join the Tournament</p>
            </div>

            <!-- Auth Card -->
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden">
                <!-- Tab Buttons -->
                <div class="flex border-b border-slate-700">
                    <button onclick="switchTab('login')" id="login-btn" class="flex-1 py-4 font-semibold transition-all <?php echo ($current_tab === 'login') ? 'bg-slate-700 text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                    <button onclick="switchTab('signup')" id="signup-btn" class="flex-1 py-4 font-semibold transition-all <?php echo ($current_tab === 'signup') ? 'bg-slate-700 text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
                        <i class="fas fa-user-plus mr-2"></i>Sign Up
                    </button>
                </div>

                <div class="p-8">
                    <!-- Messages -->
                    <?php if ($error_msg): ?>
                        <div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                                <span class="text-red-200"><?php echo htmlspecialchars($error_msg); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_msg): ?>
                        <div class="bg-green-900/30 border border-green-500 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                <span class="text-green-200"><?php echo htmlspecialchars($success_msg); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="login-form" method="POST" class="space-y-4 <?php echo ($current_tab !== 'login') ? 'hidden' : ''; ?>">
                        <input type="hidden" name="action" value="login">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <input type="text" name="username" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter your username">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input type="password" name="password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter your password">
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                    </form>

                    <!-- Signup Form -->
                    <form id="signup-form" method="POST" class="space-y-4 <?php echo ($current_tab !== 'signup') ? 'hidden' : ''; ?>">
                        <input type="hidden" name="action" value="signup">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <input type="text" name="signup_username" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Choose a username">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" name="email" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter your email">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input type="password" name="signup_password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Create a password">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Password
                            </label>
                            <input type="password" name="confirm_password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Confirm your password">
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                            <i class="fas fa-user-plus mr-2"></i>Create Account
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p>Rock Play v1.0 &copy; 2025</p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            const loginBtn = document.getElementById('login-btn');
            const signupBtn = document.getElementById('signup-btn');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                loginBtn.classList.add('bg-slate-700', 'text-white', 'border-b-2', 'border-purple-500');
                loginBtn.classList.remove('text-gray-400');
                signupBtn.classList.remove('bg-slate-700', 'text-white', 'border-b-2', 'border-purple-500');
                signupBtn.classList.add('text-gray-400');
            } else {
                signupForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
                signupBtn.classList.add('bg-slate-700', 'text-white', 'border-b-2', 'border-purple-500');
                signupBtn.classList.remove('text-gray-400');
                loginBtn.classList.remove('bg-slate-700', 'text-white', 'border-b-2', 'border-purple-500');
                loginBtn.classList.add('text-gray-400');
            }
        }

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
