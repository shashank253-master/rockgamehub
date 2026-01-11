<?php
require_once('common/header.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Get user wallet balance
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$wallet_balance = floatval($user['wallet_balance']);
$stmt->close();

// Get transaction history
$transactions = [];
$stmt = $conn->prepare("
    SELECT id, amount, type, description, created_at
    FROM transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Calculate totals
$total_credited = 0;
$total_debited = 0;
foreach ($transactions as $txn) {
    if ($txn['type'] === 'credit') {
        $total_credited += floatval($txn['amount']);
    } else {
        $total_debited += floatval($txn['amount']);
    }
}
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-wallet text-purple-500 mr-2"></i>My Wallet
        </h2>
        <p class="text-gray-400">Manage your account balance</p>
    </div>

    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-blue-600 to-purple-700 rounded-2xl p-8 shadow-2xl border border-blue-500/50">
        <p class="text-blue-100 text-sm mb-2 font-semibold">
            <i class="fas fa-piggy-bank mr-2"></i>Current Balance
        </p>
        <h1 class="text-5xl font-bold text-white mb-4">₹<?php echo number_format($wallet_balance, 2); ?></h1>
        
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-blue-500/20 rounded-lg p-3 border border-blue-400/50">
                <p class="text-blue-100 text-xs mb-1">Total Credited</p>
                <p class="text-lg font-bold text-green-300">₹<?php echo number_format($total_credited, 2); ?></p>
            </div>
            <div class="bg-blue-500/20 rounded-lg p-3 border border-blue-400/50">
                <p class="text-blue-100 text-xs mb-1">Total Debited</p>
                <p class="text-lg font-bold text-red-300">₹<?php echo number_format($total_debited, 2); ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-3">
            <button class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-lg transition transform hover:scale-105 disabled opacity-50 cursor-not-allowed" disabled>
                <i class="fas fa-plus mr-2"></i>Add Money
            </button>
            <button class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 rounded-lg transition transform hover:scale-105 disabled opacity-50 cursor-not-allowed" disabled>
                <i class="fas fa-arrow-down mr-2"></i>Withdraw
            </button>
        </div>
    </div>

    <!-- Transaction History -->
    <div>
        <h3 class="text-2xl font-bold mb-4">
            <i class="fas fa-history text-purple-500 mr-2"></i>Transaction History
        </h3>

        <?php if (count($transactions) > 0): ?>
            <div class="space-y-2">
                <?php foreach ($transactions as $txn): ?>
                    <div class="bg-slate-800 rounded-lg p-4 border border-slate-700 flex items-center justify-between hover:border-purple-500 transition">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo ($txn['type'] === 'credit') ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400'; ?>">
                                <i class="fas fa-<?php echo ($txn['type'] === 'credit') ? 'arrow-down' : 'arrow-up'; ?>"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold"><?php echo htmlspecialchars($txn['description']); ?></p>
                                <p class="text-gray-400 text-xs"><?php echo date('M d, Y h:i A', strtotime($txn['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold <?php echo ($txn['type'] === 'credit') ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo ($txn['type'] === 'credit') ? '+' : '-'; ?>₹<?php echo number_format($txn['amount'], 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No transactions yet</h3>
                <p class="text-gray-500">Your transaction history will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('common/bottom.php'); ?>
