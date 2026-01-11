    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-800 border-t border-slate-700 z-50">
        <div class="flex justify-around items-center h-20">
            <a href="/tourment/admin/index.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-chart-line text-xl mb-1"></i>
                <span class="text-xs">Dashboard</span>
            </a>
            <a href="/tourment/admin/tournament.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'tournament.php' || basename($_SERVER['PHP_SELF']) === 'manage_tournament.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-trophy text-xl mb-1"></i>
                <span class="text-xs">Tournaments</span>
            </a>
            <a href="/tourment/admin/user.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'user.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-users text-xl mb-1"></i>
                <span class="text-xs">Users</span>
            </a>
            <a href="/tourment/admin/setting.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white transition <?php echo (basename($_SERVER['PHP_SELF']) === 'setting.php') ? 'text-white border-t-2 border-purple-500' : ''; ?>">
                <i class="fas fa-cog text-xl mb-1"></i>
                <span class="text-xs">Settings</span>
            </a>
        </div>
    </nav>

    <script>
        // Disable right-click
        document.addEventListener('contextmenu', (e) => e.preventDefault());
        
        // Disable zoom
        document.addEventListener('wheel', (e) => {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });
        
        // Disable text selection
        document.addEventListener('selectstart', (e) => e.preventDefault());
    </script>
</body>
</html>
