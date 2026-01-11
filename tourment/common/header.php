<?php
if (!isset($_SESSION)) {
    session_start();
}
// Include config from same directory
require_once(__DIR__ . '/config.php');

// Get user info if logged in
$user_info = null;
$wallet_balance = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $result = $conn->query("SELECT username, wallet_balance FROM users WHERE id = $user_id");
    if ($result && $result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
        $wallet_balance = $user_info['wallet_balance'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        * {
            -webkit-touch-callout: none;
        }
        html {
            overflow: hidden;
        }
        body {
            overflow-y: auto;
            padding-bottom: 80px;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .content-wrapper {
            padding-bottom: 80px;
        }
    </style>
</head>
<body class="bg-slate-900 text-white" oncontextmenu="return false">
    <!-- Header -->
    <header class="sticky top-0 bg-slate-800 border-b border-slate-700 shadow-lg z-40">
        <div class="max-w-full px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-2 rounded-lg">
                    <i class="fas fa-gamepad text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Rock Play</h1>
                    <p class="text-xs text-gray-400">Tournament Hub</p>
                </div>
            </div>

            <?php if ($user_info): ?>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Wallet Balance</p>
                        <p class="text-lg font-bold text-green-400">₹<?php echo number_format($wallet_balance, 2); ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="content-wrapper">
