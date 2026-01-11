<?php
require_once('common/header.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Handle tournament creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title'] ?? '');
    $game_name = trim($_POST['game_name'] ?? '');
    $entry_fee = floatval($_POST['entry_fee'] ?? 0);
    $prize_pool = floatval($_POST['prize_pool'] ?? 0);
    $match_time = $_POST['match_time'] ?? '';
    $commission = intval($_POST['commission'] ?? 20);

    if (empty($title) || empty($game_name) || $entry_fee <= 0 || $prize_pool <= 0 || empty($match_time)) {
        $error_msg = 'Please fill in all fields correctly';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tournaments (title, game_name, entry_fee, prize_pool, match_time, commission_percentage)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssddsi", $title, $game_name, $entry_fee, $prize_pool, $match_time, $commission);

        if ($stmt->execute()) {
            $success_msg = 'Tournament created successfully!';
        } else {
            $error_msg = 'Error creating tournament';
        }
        $stmt->close();
    }
}

// Handle tournament deletion
if (isset($_GET['delete'])) {
    $tournament_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?");
    $stmt->bind_param("i", $tournament_id);
    if ($stmt->execute()) {
        $success_msg = 'Tournament deleted successfully!';
    } else {
        $error_msg = 'Error deleting tournament';
    }
    $stmt->close();
    header("Location: tournament.php");
    exit();
}

// Get all tournaments
$tournaments = [];
$result = $conn->query("SELECT * FROM tournaments ORDER BY match_time DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get participant count
        $p_result = $conn->query("SELECT COUNT(*) as count FROM participants WHERE tournament_id = " . intval($row['id']));
        $p_row = $p_result->fetch_assoc();
        $row['participant_count'] = intval($p_row['count']);
        $tournaments[] = $row;
    }
}
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-trophy text-purple-500 mr-2"></i>Tournaments
        </h2>
        <p class="text-gray-400">Create and manage tournaments</p>
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

    <!-- Create Tournament Form -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 shadow-lg">
        <h3 class="text-2xl font-bold mb-6">
            <i class="fas fa-plus-circle text-blue-400 mr-2"></i>Create New Tournament
        </h3>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Tournament Title</label>
                    <input type="text" name="title" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="e.g., PUBG Grand Championship">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Game Name</label>
                    <input type="text" name="game_name" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="e.g., PUBG Mobile">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Entry Fee (₹)</label>
                    <input type="number" name="entry_fee" step="0.01" min="0" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Prize Pool (₹)</label>
                    <input type="number" name="prize_pool" step="0.01" min="0" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="5000">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Match Date & Time</label>
                    <input type="datetime-local" name="match_time" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Commission % (default 20)</label>
                    <input type="number" name="commission" min="0" max="100" value="20" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                <i class="fas fa-plus mr-2"></i>Create Tournament
            </button>
        </form>
    </div>

    <!-- Tournaments List -->
    <div>
        <h3 class="text-2xl font-bold mb-4">
            <i class="fas fa-list text-purple-500 mr-2"></i>All Tournaments
        </h3>

        <?php if (count($tournaments) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-5 hover:border-purple-500 transition shadow-lg">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-white"><?php echo htmlspecialchars($tournament['title']); ?></h4>
                                <p class="text-gray-400 text-sm">
                                    <i class="fas fa-gamepad mr-1"></i><?php echo htmlspecialchars($tournament['game_name']); ?>
                                </p>
                            </div>
                            <span class="bg-<?php echo ($tournament['status'] === 'Completed') ? 'gray' : (($tournament['status'] === 'Live') ? 'red' : 'blue'); ?>-900/30 border border-<?php echo ($tournament['status'] === 'Completed') ? 'gray' : (($tournament['status'] === 'Live') ? 'red' : 'blue'); ?>-500 px-3 py-1 rounded-full text-xs font-semibold text-<?php echo ($tournament['status'] === 'Completed') ? 'gray' : (($tournament['status'] === 'Live') ? 'red' : 'blue'); ?>-400">
                                <?php echo $tournament['status']; ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
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
                                <p class="font-bold text-blue-400"><?php echo $tournament['participant_count']; ?></p>
                            </div>
                            <div class="bg-slate-700/50 rounded-lg p-3">
                                <p class="text-gray-400 text-xs mb-1">Commission</p>
                                <p class="font-bold text-purple-400"><?php echo $tournament['commission_percentage']; ?>%</p>
                            </div>
                        </div>

                        <p class="text-gray-400 text-sm mb-4">
                            <i class="fas fa-clock mr-2"></i>Match: <?php echo date('M d, Y h:i A', strtotime($tournament['match_time'])); ?>
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <?php if ($tournament['status'] !== 'Completed'): ?>
                                <a href="manage_tournament.php?id=<?php echo $tournament['id']; ?>" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition text-center text-sm">
                                    <i class="fas fa-edit mr-1"></i>Manage
                                </a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $tournament['id']; ?>" onclick="return confirm('Are you sure?')" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition text-center text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No tournaments yet</h3>
                <p class="text-gray-500">Create your first tournament using the form above</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('common/bottom.php'); ?>
