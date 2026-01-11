<?php
require_once('common/header.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';
$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get tournament details
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: tournament.php");
    exit();
}

$tournament = $result->fetch_assoc();
$stmt->close();

// Handle room details update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_room') {
    $room_id = trim($_POST['room_id'] ?? '');
    $room_password = trim($_POST['room_password'] ?? '');
    $new_status = $_POST['status'] ?? $tournament['status'];

    if (empty($room_id) || empty($room_password)) {
        $error_msg = 'Please fill in room details';
    } else {
        $stmt = $conn->prepare("UPDATE tournaments SET room_id = ?, room_password = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $room_id, $room_password, $new_status, $tournament_id);

        if ($stmt->execute()) {
            $tournament['room_id'] = $room_id;
            $tournament['room_password'] = $room_password;
            $tournament['status'] = $new_status;
            $success_msg = 'Room details updated!';
        } else {
            $error_msg = 'Error updating room details';
        }
        $stmt->close();
    }
}

// Handle winner declaration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'declare_winner') {
    $winner_id = intval($_POST['winner_id'] ?? 0);

    if ($winner_id <= 0) {
        $error_msg = 'Please select a winner';
    } else {
        // Get prize pool
        $prize = floatval($tournament['prize_pool']);

        // Update winner's wallet
        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->bind_param("di", $prize, $winner_id);
        $stmt->execute();
        $stmt->close();

        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
        $description = "Prize from " . htmlspecialchars($tournament['title']);
        $stmt->bind_param("ids", $winner_id, $prize, $description);
        $stmt->execute();
        $stmt->close();

        // Update tournament status and winner
        $completed_status = 'Completed';
        $stmt = $conn->prepare("UPDATE tournaments SET status = ?, winner_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $completed_status, $winner_id, $tournament_id);

        if ($stmt->execute()) {
            $tournament['status'] = 'Completed';
            $tournament['winner_id'] = $winner_id;
            $success_msg = 'Winner declared and prize distributed!';
        } else {
            $error_msg = 'Error declaring winner';
        }
        $stmt->close();
    }
}

// Get participants
$participants = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email
    FROM participants p
    INNER JOIN users u ON p.user_id = u.id
    WHERE p.tournament_id = ?
    ORDER BY p.joined_at DESC
");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $participants[] = $row;
}
$stmt->close();
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-cog text-purple-500 mr-2"></i>Manage Tournament
        </h2>
        <h3 class="text-2xl font-bold text-blue-400 mb-2"><?php echo htmlspecialchars($tournament['title']); ?></h3>
        <p class="text-gray-400"><?php echo htmlspecialchars($tournament['game_name']); ?></p>
    </div>

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

    <!-- Tournament Info Card -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-xl font-bold mb-4">Tournament Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-700/50 rounded-lg p-3">
                <p class="text-gray-400 text-xs mb-1">Status</p>
                <p class="font-bold text-<?php echo ($tournament['status'] === 'Completed') ? 'gray' : (($tournament['status'] === 'Live') ? 'red' : 'blue'); ?>-400"><?php echo $tournament['status']; ?></p>
            </div>
            <div class="bg-slate-700/50 rounded-lg p-3">
                <p class="text-gray-400 text-xs mb-1">Entry Fee</p>
                <p class="font-bold text-green-400">₹<?php echo number_format($tournament['entry_fee'], 2); ?></p>
            </div>
            <div class="bg-slate-700/50 rounded-lg p-3">
                <p class="text-gray-400 text-xs mb-1">Prize Pool</p>
                <p class="font-bold text-yellow-400">₹<?php echo number_format($tournament['prize_pool'], 2); ?></p>
            </div>
            <div class="bg-slate-700/50 rounded-lg p-3">
                <p class="text-gray-400 text-xs mb-1">Participants</p>
                <p class="font-bold text-blue-400"><?php echo count($participants); ?></p>
            </div>
        </div>
    </div>

    <!-- Room Details Form (Only if not Completed) -->
    <?php if ($tournament['status'] !== 'Completed'): ?>
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-xl font-bold mb-4">
            <i class="fas fa-door-open text-blue-400 mr-2"></i>Room Details
        </h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="update_room">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Room ID</label>
                    <input type="text" name="room_id" value="<?php echo htmlspecialchars($tournament['room_id'] ?? ''); ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter room ID" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Room Password</label>
                    <input type="text" name="room_password" value="<?php echo htmlspecialchars($tournament['room_password'] ?? ''); ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter room password" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Tournament Status</label>
                <select name="status" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500 transition">
                    <option value="Upcoming" <?php echo ($tournament['status'] === 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="Live" <?php echo ($tournament['status'] === 'Live') ? 'selected' : ''; ?>>Live</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-save mr-2"></i>Update Room Details
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Declare Winner Form (Only if not Completed) -->
    <?php if ($tournament['status'] !== 'Completed'): ?>
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-xl font-bold mb-4">
            <i class="fas fa-crown text-yellow-400 mr-2"></i>Declare Winner
        </h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="declare_winner">

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Winner</label>
                <select name="winner_id" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-purple-500 transition" required>
                    <option value="">-- Choose a winner --</option>
                    <?php foreach ($participants as $participant): ?>
                        <option value="<?php echo $participant['id']; ?>">
                            <?php echo htmlspecialchars($participant['username']); ?> (<?php echo htmlspecialchars($participant['email']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bg-yellow-900/20 border border-yellow-600 rounded-lg p-4 mb-4">
                <p class="text-yellow-200 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>Prize: <strong>₹<?php echo number_format($tournament['prize_pool'], 2); ?></strong> will be credited to the winner's wallet.
                </p>
            </div>

            <button type="submit" onclick="return confirm('Are you sure? This action cannot be undone.')" class="w-full bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-trophy mr-2"></i>Declare Winner & Distribute Prize
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Participants List -->
    <div>
        <h3 class="text-2xl font-bold mb-4">
            <i class="fas fa-users text-purple-500 mr-2"></i>Participants (<?php echo count($participants); ?>)
        </h3>

        <?php if (count($participants) > 0): ?>
            <div class="space-y-2">
                <?php foreach ($participants as $participant): ?>
                    <div class="bg-slate-800 rounded-lg p-4 border border-slate-700 flex items-center justify-between hover:border-purple-500 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold"><?php echo htmlspecialchars($participant['username']); ?></p>
                                <p class="text-gray-400 text-xs"><?php echo htmlspecialchars($participant['email']); ?></p>
                            </div>
                        </div>
                        <?php if ($tournament['winner_id'] == $participant['id']): ?>
                            <span class="bg-yellow-900/30 border border-yellow-500 px-3 py-1 rounded-full text-xs font-semibold text-yellow-400">
                                <i class="fas fa-crown mr-1"></i>Winner
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400">No participants yet</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('common/bottom.php'); ?>
