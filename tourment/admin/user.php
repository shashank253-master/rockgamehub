<?php
require_once('common/header.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Get all users with their info
$users = [];
$result = $conn->query("
    SELECT u.id, u.username, u.email, u.wallet_balance, u.created_at,
           COUNT(p.id) as tournament_count
    FROM users u
    LEFT JOIN participants p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-users text-purple-500 mr-2"></i>Users
        </h2>
        <p class="text-gray-400">Manage registered users</p>
    </div>

    <!-- Messages -->
    <?php if ($success_msg): ?>
        <div class="bg-green-900/30 border border-green-500 rounded-lg p-4 flex items-center">
            <i class="fas fa-check-circle text-green-400 mr-3 text-xl"></i>
            <span class="text-green-200"><?php echo htmlspecialchars($success_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- User Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-4 border border-blue-500/50 shadow-lg">
            <p class="text-blue-100 text-xs mb-2">Total Users</p>
            <p class="text-3xl font-bold text-white"><?php echo count($users); ?></p>
        </div>

        <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-4 border border-purple-500/50 shadow-lg">
            <p class="text-purple-100 text-xs mb-2">Active Players</p>
            <?php 
            $active = 0;
            foreach ($users as $u) {
                if ($u['tournament_count'] > 0) $active++;
            }
            ?>
            <p class="text-3xl font-bold text-white"><?php echo $active; ?></p>
        </div>

        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-4 border border-green-500/50 shadow-lg">
            <p class="text-green-100 text-xs mb-2">Total Wallet</p>
            <?php 
            $total_wallet = 0;
            foreach ($users as $u) {
                $total_wallet += floatval($u['wallet_balance']);
            }
            ?>
            <p class="text-2xl font-bold text-white">₹<?php echo number_format($total_wallet, 0); ?></p>
        </div>
    </div>

    <!-- Users Table -->
    <div>
        <h3 class="text-2xl font-bold mb-4">
            <i class="fas fa-list text-purple-500 mr-2"></i>User List
        </h3>

        <?php if (count($users) > 0): ?>
            <div class="space-y-3">
                <?php foreach ($users as $user): ?>
                    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 hover:border-purple-500 transition shadow-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-white"><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <p class="text-gray-400 text-sm">
                                        <i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                    <p class="text-gray-500 text-xs mt-1">
                                        <i class="fas fa-calendar mr-1"></i>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 ml-4">
                                <div class="text-center">
                                    <p class="text-gray-400 text-xs mb-1">Wallet</p>
                                    <p class="text-lg font-bold text-green-400">₹<?php echo number_format($user['wallet_balance'], 2); ?></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-400 text-xs mb-1">Tournaments</p>
                                    <p class="text-lg font-bold text-blue-400"><?php echo $user['tournament_count']; ?></p>
                                </div>
                                <div>
                                    <button class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded-lg transition text-sm disabled opacity-50 cursor-not-allowed" disabled>
                                        <i class="fas fa-ban mr-1"></i>Block
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No users yet</h3>
                <p class="text-gray-500">Users will appear here once they sign up</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('common/bottom.php'); ?>
