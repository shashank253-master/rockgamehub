<?php
require_once('common/header.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = intval($_SESSION['admin_id']);
$success_msg = '';
$error_msg = '';
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

// Get admin info
$stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_admin') {
    $new_username = trim($_POST['username'] ?? '');

    if (empty($new_username)) {
        $error_msg = 'Please fill in username';
    } else {
        // Check if username already exists (excluding current admin)
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $new_username, $admin_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_msg = 'Username already taken';
        } else {
            $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $new_username, $admin_id);

            if ($stmt->execute()) {
                $_SESSION['admin_username'] = $new_username;
                $admin['username'] = $new_username;
                $success_msg = 'Admin info updated successfully!';
            } else {
                $error_msg = 'Error updating admin info';
            }
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = 'Please fill in all fields';
    } elseif ($new_password !== $confirm_password) {
        $error_msg = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error_msg = 'Password must be at least 6 characters';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $pwd_result = $stmt->get_result();
        $pwd_admin = $pwd_result->fetch_assoc();
        $stmt->close();

        if (!password_verify($current_password, $pwd_admin['password'])) {
            $error_msg = 'Current password is incorrect';
        } else {
            $new_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $admin_id);

            if ($stmt->execute()) {
                $success_msg = 'Password changed successfully!';
            } else {
                $error_msg = 'Error changing password';
            }
            $stmt->close();
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<div class="max-w-full px-4 py-6 space-y-6">
    <!-- Title -->
    <div>
        <h2 class="text-3xl font-bold mb-2">
            <i class="fas fa-cog text-purple-500 mr-2"></i>Settings
        </h2>
        <p class="text-gray-400">Manage admin account</p>
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

    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-slate-700">
        <a href="?tab=settings" class="px-6 py-3 font-semibold transition-all <?php echo ($current_tab === 'settings') ? 'text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
            <i class="fas fa-user mr-2"></i>Admin Info
        </a>
        <a href="?tab=password" class="px-6 py-3 font-semibold transition-all <?php echo ($current_tab === 'password') ? 'text-white border-b-2 border-purple-500' : 'text-gray-400 hover:text-white'; ?>">
            <i class="fas fa-lock mr-2"></i>Security
        </a>
    </div>

    <?php if ($current_tab === 'settings'): ?>
        <!-- Admin Info Tab -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-lg">
            <h3 class="text-2xl font-bold mb-6">Admin Information</h3>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="update_admin">

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div class="bg-slate-700/50 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Admin ID
                    </p>
                    <p class="text-gray-300 font-mono"><?php echo $admin_id; ?></p>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- Security Tab -->
        <div class="space-y-6">
            <!-- Change Password -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-lg">
                <h3 class="text-2xl font-bold mb-6">Change Password</h3>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="change_password">

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-lock mr-2"></i>Current Password
                        </label>
                        <input type="password" name="current_password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter your current password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-key mr-2"></i>New Password
                        </label>
                        <input type="password" name="new_password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Enter new password (min 6 characters)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-check-circle mr-2"></i>Confirm Password
                        </label>
                        <input type="password" name="confirm_password" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition" placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-2 rounded-lg transition duration-200 transform hover:scale-105 mt-6">
                        <i class="fas fa-sync mr-2"></i>Change Password
                    </button>
                </form>
            </div>

            <!-- Logout Section -->
            <div class="bg-red-900/20 border border-red-500 rounded-2xl p-8">
                <h3 class="text-2xl font-bold mb-4 text-red-400">
                    <i class="fas fa-sign-out-alt mr-2"></i>Session
                </h3>
                <p class="text-gray-300 mb-4">End your current admin session.</p>
                <a href="?logout=1" class="block text-center w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once('common/bottom.php'); ?>
