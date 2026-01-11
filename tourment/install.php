<?php
session_start();

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'rock_play';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_msg = '';
$success_msg = '';
$installation_complete = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
    if ($conn->query($sql) === TRUE) {
        // Select the database
        $conn->select_db($db_name);

        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `username` VARCHAR(100) UNIQUE NOT NULL,
            `email` VARCHAR(100) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `wallet_balance` DECIMAL(10, 2) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);

        // Create admin table
        $sql = "CREATE TABLE IF NOT EXISTS `admin` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `username` VARCHAR(100) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL
        )";
        $conn->query($sql);

        // Insert default admin
        $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
        $sql = "INSERT IGNORE INTO `admin` (username, password) VALUES ('admin', '$admin_pass')";
        $conn->query($sql);

        // Create tournaments table
        $sql = "CREATE TABLE IF NOT EXISTS `tournaments` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `game_name` VARCHAR(100) NOT NULL,
            `entry_fee` DECIMAL(10, 2) NOT NULL,
            `prize_pool` DECIMAL(10, 2) NOT NULL,
            `match_time` DATETIME NOT NULL,
            `room_id` VARCHAR(100),
            `room_password` VARCHAR(100),
            `status` ENUM('Upcoming', 'Live', 'Completed') DEFAULT 'Upcoming',
            `commission_percentage` INT DEFAULT 20,
            `winner_id` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);

        // Create participants table
        $sql = "CREATE TABLE IF NOT EXISTS `participants` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `tournament_id` INT NOT NULL,
            `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Create transactions table
        $sql = "CREATE TABLE IF NOT EXISTS `transactions` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `amount` DECIMAL(10, 2) NOT NULL,
            `type` ENUM('credit', 'debit') NOT NULL,
            `description` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        $success_msg = "Installation successful! Database and tables created. Redirecting to login in 3 seconds...";
        $installation_complete = true;

        // Auto-redirect after 3 seconds
        echo "<meta http-equiv='refresh' content='3;url=login.php'>";
    } else {
        $error_msg = "Error creating database: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Play - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; }
        * { -webkit-touch-callout: none; }
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
                <p class="text-gray-400">Tournament Management System</p>
            </div>

            <!-- Installation Card -->
            <div class="bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-700">
                <h2 class="text-2xl font-bold mb-6">Installation Wizard</h2>

                <?php if ($success_msg): ?>
                    <div class="bg-green-900/30 border border-green-500 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3 text-xl"></i>
                            <div>
                                <p class="text-green-200 font-semibold">Success!</p>
                                <p class="text-green-100 text-sm"><?php echo $success_msg; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg && !$installation_complete): ?>
                    <div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-400 mr-3 text-xl"></i>
                            <p class="text-red-200"><?php echo $error_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$installation_complete): ?>
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                <i class="fas fa-database mr-2"></i>Database Configuration
                            </label>
                            <div class="bg-slate-700/50 rounded-lg p-4 space-y-3">
                                <div>
                                    <p class="text-gray-400 text-xs">Host: <span class="text-gray-300 font-mono">127.0.0.1</span></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">User: <span class="text-gray-300 font-mono">root</span></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">Database: <span class="text-gray-300 font-mono">rock_play</span></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">Default Admin: <span class="text-gray-300 font-mono">admin / admin123</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-700/30 rounded-lg p-4">
                            <p class="text-gray-300 text-sm mb-3">
                                <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                                Click the button below to create the database and all required tables.
                            </p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-3 rounded-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-wrench mr-2"></i>Start Installation
                        </button>
                    </form>
                <?php endif; ?>
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
