<?php
require_once('common/header.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$success_msg = '';
$error_msg = '';

// Handle tournament join
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_tournament'])) {
    $tournament_id = intval($_POST['tournament_id']);

    // Get tournament details and user wallet
    $stmt = $conn->prepare("SELECT entry_fee FROM tournaments WHERE id = ?");
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $tournament = $result->fetch_assoc();
        $entry_fee = floatval($tournament['entry_fee']);

        // Get user wallet
        $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $wallet_result = $stmt->get_result();
        $user_wallet = $wallet_result->fetch_assoc();
        $wallet_balance = floatval($user_wallet['wallet_balance']);

        // Check if already joined
        $stmt = $conn->prepare("SELECT id FROM participants WHERE user_id = ? AND tournament_id = ?");
        $stmt->bind_param("ii", $user_id, $tournament_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_msg = "You have already joined this tournament!";
        } elseif ($wallet_balance < $entry_fee) {
            $error_msg = "Insufficient wallet balance. You need ₹" . number_format($entry_fee, 2);
        } else {
            // Deduct fee and add participant
            $new_balance = $wallet_balance - $entry_fee;
            $stmt = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $user_id);
            $stmt->execute();

            // Add to participants
            $stmt = $conn->prepare("INSERT INTO participants (user_id, tournament_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $tournament_id);
            if ($stmt->execute()) {
                // Record transaction
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
                $description = "Tournament entry fee";
                $stmt->bind_param("ids", $user_id, $entry_fee, $description);
                $stmt->execute();
                
                $success_msg = "Successfully joined the tournament!";
            } else {
                $error_msg = "Error joining tournament. Please try again.";
            }
        }
    } else {
        $error_msg = "Tournament not found.";
    }
    $stmt->close();
}

// Get all upcoming tournaments
$tournaments = [];
$result = $conn->query("SELECT * FROM tournaments WHERE status = 'Upcoming' ORDER BY match_time ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
}

// Get user's joined tournaments
$joined_tournaments = [];
$stmt = $conn->prepare("SELECT DISTINCT tournament_id FROM participants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$joined_result = $stmt->get_result();
while ($row = $joined_result->fetch_assoc()) {
    $joined_tournaments[] = $row['tournament_id'];
}
$stmt->close();
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Messages -->
    <?php if ($success_msg): ?>
        <div class="bg-green-900/30 border border-green-500 rounded-lg p-4 flex items-center">
            <i class="fas fa-check-circle text-green-400 mr-3 text-xl"></i>
            <span class="text-green-200"><?php echo htmlspecialchars($success_msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="bg-red-900/30 border border-red-500 rounded-lg p-4 flex items-center">
            <i class="fas fa-exclamation-circle text-red-400 mr-3 text-xl"></i>
            <span class="text-red-200"><?php echo htmlspecialchars($error_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-trophy text-purple-500 mr-2"></i>Upcoming Tournaments
        </h2>
        <p class="text-gray-400">Browse and join exciting tournaments</p>
    </div>

    <!-- Tournament Cards Grid -->
    <?php if (count($tournaments) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($tournaments as $tournament): ?>
                <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden hover:border-purple-500 transition shadow-lg">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                        <h3 class="text-xl font-bold text-white mb-1"><?php echo htmlspecialchars($tournament['title']); ?></h3>
                        <p class="text-blue-100 text-sm">
                            <i class="fas fa-gamepad mr-1"></i><?php echo htmlspecialchars($tournament['game_name']); ?>
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-gray-400 text-sm">
                                <i class="fas fa-clock text-blue-400 mr-2"></i>
                                <?php echo date('M d, h:i A', strtotime($tournament['match_time'])); ?>
                            </div>
                            <div class="bg-slate-700 px-3 py-1 rounded-full text-xs font-semibold text-yellow-400">
                                <i class="fas fa-star mr-1"></i><?php echo $tournament['status']; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-700/50 rounded-lg p-3">
                                <p class="text-gray-400 text-xs mb-1">Entry Fee</p>
                                <p class="text-lg font-bold text-green-400">₹<?php echo number_format($tournament['entry_fee'], 2); ?></p>
                            </div>
                            <div class="bg-slate-700/50 rounded-lg p-3">
                                <p class="text-gray-400 text-xs mb-1">Prize Pool</p>
                                <p class="text-lg font-bold text-yellow-400">₹<?php echo number_format($tournament['prize_pool'], 2); ?></p>
                            </div>
                        </div>

                        <!-- Join Button -->
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="tournament_id" value="<?php echo $tournament['id']; ?>">
                            <button type="submit" name="join_tournament" value="1" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 <?php echo in_array($tournament['id'], $joined_tournaments) ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo in_array($tournament['id'], $joined_tournaments) ? 'disabled' : ''; ?>>
                                <?php echo in_array($tournament['id'], $joined_tournaments) ? 'Already Joined' : 'Join Now'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
            <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-400 mb-2">No tournaments available</h3>
            <p class="text-gray-500">Check back soon for exciting tournaments!</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once('common/bottom.php'); ?>
