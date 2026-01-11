    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-800 border-t border-slate-700 z-50">
        <div class="flex justify-around items-center h-20">
            <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '/tourment/admin/index.php' : '/tourment/index.php'; ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '/tourment/admin/tournament.php' : '/tourment/my_tournaments.php'; ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'my_tournaments.php' || basename($_SERVER['PHP_SELF']) === 'tournament.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-trophy text-xl mb-1"></i>
                <span class="text-xs"><?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'Tournaments' : 'My Tournaments'; ?></span>
            </a>
            <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') === false): ?>
            <a href="/tourment/wallet.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'wallet.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-wallet text-xl mb-1"></i>
                <span class="text-xs">Wallet</span>
            </a>
            <?php endif; ?>
            <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
            <a href="/tourment/admin/user.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'user.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-users text-xl mb-1"></i>
                <span class="text-xs">Users</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '/tourment/admin/setting.php' : '/tourment/profile.php'; ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'profile.php' || basename($_SERVER['PHP_SELF']) === 'setting.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-user-circle text-xl mb-1"></i>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Disable right-click
        document.addEventListener('contextmenu', (e) => e.preventDefault());
        
        // Disable zoom with Ctrl+Scroll
        document.addEventListener('wheel', (e) => {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });
        
        // Disable Ctrl+Plus/Minus
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '0')) {
                e.preventDefault();
            }
        });
        
        // Disable text selection
        document.addEventListener('selectstart', (e) => e.preventDefault());
    </script>
</body>
</html>
