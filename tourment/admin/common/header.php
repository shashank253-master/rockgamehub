<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once(__DIR__ . '/../../common/config.php');

// Get admin info if logged in
$admin_info = null;
if (isset($_SESSION['admin_id'])) {
    $admin_id = intval($_SESSION['admin_id']);
    $result = $conn->query("SELECT username FROM admin WHERE id = $admin_id");
    if ($result && $result->num_rows > 0) {
        $admin_info = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Play - Admin</title>
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
                    <p class="text-xs text-gray-400">Admin Panel</p>
                </div>
            </div>

            <?php if ($admin_info): ?>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Admin</p>
                        <p class="text-lg font-bold text-blue-400"><?php echo htmlspecialchars($admin_info['username']); ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-shield text-white"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="content-wrapper">
