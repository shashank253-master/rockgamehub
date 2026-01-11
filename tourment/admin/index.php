<?php
require_once('common/header.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get dashboard statistics
$stats = [
    'total_users' => 0,
    'total_tournaments' => 0,
    'total_prize_distributed' => 0,
    'total_revenue' => 0
];

// Total Users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
$stats['total_users'] = intval($row['count']);

// Total Tournaments
$result = $conn->query("SELECT COUNT(*) as count FROM tournaments");
$row = $result->fetch_assoc();
$stats['total_tournaments'] = intval($row['count']);

// Total Prize Distributed and Revenue
$result = $conn->query("
    SELECT 
        COALESCE(SUM(t.prize_pool), 0) as prize_distributed,
        COALESCE(SUM(t.entry_fee * (SELECT COUNT(*) FROM participants WHERE tournament_id = t.id) * t.commission_percentage / 100), 0) as revenue
    FROM tournaments t
    WHERE t.status = 'Completed'
");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_prize_distributed'] = floatval($row['prize_distributed']);
    $stats['total_revenue'] = floatval($row['revenue']);
}
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Welcome Section -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-chart-line text-purple-500 mr-2"></i>Dashboard
        </h2>
        <p class="text-gray-400">Welcome to the admin panel</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
        <!-- Total Users -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 border border-blue-500/50 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-2">Total Users</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['total_users']); ?></p>
                </div>
                <div class="bg-blue-500/20 p-4 rounded-lg">
                    <i class="fas fa-users text-3xl text-blue-300"></i>
                </div>
            </div>
        </div>

        <!-- Total Tournaments -->
        <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6 border border-purple-500/50 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-2">Total Tournaments</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['total_tournaments']); ?></p>
                </div>
                <div class="bg-purple-500/20 p-4 rounded-lg">
                    <i class="fas fa-trophy text-3xl text-purple-300"></i>
                </div>
            </div>
        </div>

        <!-- Prize Distributed -->
        <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl p-6 border border-yellow-500/50 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm mb-2">Prize Distributed</p>
                    <p class="text-3xl font-bold text-white">₹<?php echo number_format($stats['total_prize_distributed'], 0); ?></p>
                </div>
                <div class="bg-yellow-500/20 p-4 rounded-lg">
                    <i class="fas fa-money-bill-wave text-3xl text-yellow-300"></i>
                </div>
            </div>
        </div>

        <!-- Revenue (Commission) -->
        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-6 border border-green-500/50 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-2">Total Revenue</p>
                    <p class="text-3xl font-bold text-white">₹<?php echo number_format($stats['total_revenue'], 0); ?></p>
                </div>
                <div class="bg-green-500/20 p-4 rounded-lg">
                    <i class="fas fa-coins text-3xl text-green-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-xl font-bold mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
        </h3>
        <div class="flex flex-col gap-3">
            <a href="tournament.php" class="block bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-3 rounded-lg transition text-center">
                <i class="fas fa-plus-circle mr-2"></i>Create New Tournament
            </a>
            <a href="user.php" class="block bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-bold py-3 rounded-lg transition text-center">
                <i class="fas fa-users mr-2"></i>Manage Users
            </a>
        </div>
    </div>

    <!-- Recent Activity Placeholder -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-xl font-bold mb-4">
            <i class="fas fa-history text-purple-500 mr-2"></i>System Overview
        </h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-slate-700/50 rounded-lg p-4">
                <p class="text-gray-400 text-xs mb-2">Upcoming</p>
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'Upcoming'");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold text-blue-400"><?php echo $row['count']; ?></p>
            </div>
            <div class="bg-slate-700/50 rounded-lg p-4">
                <p class="text-gray-400 text-xs mb-2">Live</p>
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'Live'");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold text-red-400"><?php echo $row['count']; ?></p>
            </div>
            <div class="bg-slate-700/50 rounded-lg p-4">
                <p class="text-gray-400 text-xs mb-2">Completed</p>
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'Completed'");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold text-green-400"><?php echo $row['count']; ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once('common/bottom.php'); ?>
