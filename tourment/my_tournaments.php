<?php
require_once('common/header.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-list text-purple-500 mr-2"></i>My Tournaments
        </h2>
        <p class="text-gray-400">View your tournaments and results</p>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-slate-700">
        <a href="?tab=upcoming" class="px-6 py-3 font-semibold transition-all <?php echo ($current_tab === 'upcoming') ? 'text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
            <i class="fas fa-clock mr-2"></i>Upcoming/Live
        </a>
        <a href="?tab=completed" class="px-6 py-3 font-semibold transition-all <?php echo ($current_tab === 'completed') ? 'text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
            <i class="fas fa-history mr-2"></i>Completed
        </a>
    </div>

    <?php if ($current_tab === 'upcoming'): ?>
        <!-- Upcoming/Live Tournaments -->
        <?php
        $stmt = $conn->prepare("
            SELECT t.id, t.title, t.game_name, t.entry_fee, t.prize_pool, t.match_time, t.status, t.room_id, t.room_password
            FROM tournaments t
            INNER JOIN participants p ON t.id = p.tournament_id
            WHERE p.user_id = ? AND t.status IN ('Upcoming', 'Live')
            ORDER BY t.match_time ASC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tournaments = [];
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
        $stmt->close();
        ?>

        <?php if (count($tournaments) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden hover:border-purple-500 transition shadow-lg">
                        <div class="p-5">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($tournament['title']); ?></h3>
                                    <p class="text-gray-400 text-sm">
                                        <i class="fas fa-gamepad mr-1"></i><?php echo htmlspecialchars($tournament['game_name']); ?>
                                    </p>
                                </div>
                                <span class="bg-<?php echo ($tournament['status'] === 'Live') ? 'red' : 'blue'; ?>-900/30 border border-<?php echo ($tournament['status'] === 'Live') ? 'red' : 'blue'; ?>-500 px-3 py-1 rounded-full text-xs font-semibold text-<?php echo ($tournament['status'] === 'Live') ? 'red' : 'blue'; ?>-400">
                                    <?php echo $tournament['status']; ?>
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div>
                                    <p class="text-gray-400 text-xs mb-1">Match Time</p>
                                    <p class="text-sm font-semibold text-white">
                                        <i class="fas fa-clock text-blue-400 mr-1"></i><?php echo date('M d, h:i A', strtotime($tournament['match_time'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs mb-1">Prize Pool</p>
                                    <p class="text-sm font-semibold text-yellow-400">₹<?php echo number_format($tournament['prize_pool'], 2); ?></p>
                                </div>
                            </div>

                            <!-- Room Details (if Live) -->
                            <?php if ($tournament['status'] === 'Live' && $tournament['room_id']): ?>
                                <div class="bg-slate-700/50 rounded-lg p-4 mb-4 border-l-4 border-red-500">
                                    <p class="text-gray-400 text-xs mb-3 font-semibold">
                                        <i class="fas fa-door-open text-red-400 mr-2"></i>Room Details
                                    </p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-gray-500 text-xs mb-1">Room ID</p>
                                            <p class="text-white font-mono font-bold"><?php echo htmlspecialchars($tournament['room_id']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500 text-xs mb-1">Password</p>
                                            <p class="text-white font-mono font-bold"><?php echo htmlspecialchars($tournament['room_password']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No upcoming tournaments</h3>
                <p class="text-gray-500 mb-4">You haven't joined any upcoming tournaments yet</p>
                <a href="index.php" class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold px-6 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i>Browse Tournaments
                </a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Completed Tournaments -->
        <?php
        $stmt = $conn->prepare("
            SELECT t.id, t.title, t.game_name, t.entry_fee, t.prize_pool, t.match_time, t.status, t.winner_id
            FROM tournaments t
            INNER JOIN participants p ON t.id = p.tournament_id
            WHERE p.user_id = ? AND t.status = 'Completed'
            ORDER BY t.match_time DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tournaments = [];
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
        $stmt->close();
        ?>

        <?php if (count($tournaments) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden hover:border-purple-500 transition shadow-lg">
                        <div class="p-5">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($tournament['title']); ?></h3>
                                    <p class="text-gray-400 text-sm">
                                        <i class="fas fa-gamepad mr-1"></i><?php echo htmlspecialchars($tournament['game_name']); ?>
                                    </p>
                                </div>
                                <span class="bg-gray-900/30 border border-gray-600 px-3 py-1 rounded-full text-xs font-semibold text-gray-400">
                                    Completed
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div>
                                    <p class="text-gray-400 text-xs mb-1">Match Date</p>
                                    <p class="text-sm font-semibold text-white">
                                        <i class="fas fa-calendar text-blue-400 mr-1"></i><?php echo date('M d, Y', strtotime($tournament['match_time'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs mb-1">Result</p>
                                    <p class="text-sm font-semibold <?php echo ($tournament['winner_id'] == $user_id) ? 'text-yellow-400' : 'text-gray-400'; ?>">
                                        <?php echo ($tournament['winner_id'] == $user_id) ? '<i class="fas fa-crown mr-1"></i>Winner' : '<i class="fas fa-handshake mr-1"></i>Participated'; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Prize Info -->
                            <div class="bg-slate-700/50 rounded-lg p-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm">Prize Pool</span>
                                    <span class="text-lg font-bold <?php echo ($tournament['winner_id'] == $user_id) ? 'text-yellow-400' : 'text-gray-400'; ?>">₹<?php echo number_format($tournament['prize_pool'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
                <i class="fas fa-history text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No completed tournaments</h3>
                <p class="text-gray-500">Your tournament history will appear here once tournaments are completed</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once('common/bottom.php'); ?>
