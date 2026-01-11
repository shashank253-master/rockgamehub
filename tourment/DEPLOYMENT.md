# 🔧 DEPLOYMENT & TROUBLESHOOTING GUIDE

## 📦 DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] All files created in correct directories
- [ ] Folder permissions set to 755 (directories) and 644 (files)
- [ ] MySQL server configured and running
- [ ] PHP version 7.4 or higher
- [ ] mod_rewrite enabled (for .htaccess)
- [ ] Tailwind CSS CDN accessible
- [ ] Font Awesome CDN accessible

### Installation
- [ ] Visit install.php
- [ ] Database created successfully
- [ ] All tables created
- [ ] Default admin inserted

### Post-Installation
- [ ] Change default admin password
- [ ] Test user signup
- [ ] Test user login
- [ ] Test admin login
- [ ] Create test tournament
- [ ] Test joining tournament
- [ ] Test winner declaration
- [ ] Verify transaction history

---

## 🚀 PRODUCTION DEPLOYMENT

### 1. Server Requirements
```
PHP: 7.4+ (recommended 8.0+)
MySQL: 5.7+ (recommended 8.0+)
Apache: 2.4+ with mod_rewrite
Memory: 128MB+ available
Disk: 1GB+ free space
```

### 2. File Structure
```
/var/www/html/tourment/
├── .htaccess
├── common/
├── admin/
├── login.php
├── index.php
├── my_tournaments.php
├── wallet.php
├── profile.php
├── install.php
└── README.md
```

### 3. Permissions
```bash
# Set directory permissions
chmod 755 /var/www/html/tourment/
chmod 755 /var/www/html/tourment/*

# Set file permissions
chmod 644 /var/www/html/tourment/*.php
chmod 644 /var/www/html/tourment/.htaccess
```

### 4. Database Setup
```bash
# Login to MySQL
mysql -u root -p

# Create user (optional)
CREATE USER 'rock_play'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON rock_play.* TO 'rock_play'@'localhost';
FLUSH PRIVILEGES;

# Then visit install.php
```

### 5. Configuration
Edit `common/config.php` with production values:
```php
define('DB_HOST', 'your_db_host');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'rock_play');
```

### 6. SSL Certificate
Add to web server configuration:
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
</VirtualHost>
```

### 7. Backup Strategy
```bash
# Backup database weekly
mysqldump -u root -p rock_play > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf tourment_backup_$(date +%Y%m%d).tar.gz /var/www/html/tourment/
```

---

## 🐛 TROUBLESHOOTING GUIDE

### Installation Issues

#### Error: "Connection failed"
**Cause**: MySQL not running or wrong credentials
**Solution**:
1. Start MySQL service: `sudo service mysql start`
2. Verify credentials in install.php
3. Check MySQL port (default 3306)

**Test Connection**:
```bash
mysql -h 127.0.0.1 -u root -p
```

#### Error: "Database already exists"
**Cause**: Database 'rock_play' already created
**Solution**:
1. Delete existing database: `DROP DATABASE rock_play;`
2. Or rename in config.php: `define('DB_NAME', 'rock_play_v2');`

#### Error: "Cannot write to database"
**Cause**: MySQL user lacks permissions
**Solution**:
```sql
GRANT ALL PRIVILEGES ON rock_play.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

---

### Authentication Issues

#### Problem: "Invalid credentials" on correct login
**Cause**: Password hash corruption or user not created
**Solution**:
1. Manually check users table:
   ```sql
   SELECT id, username, email FROM users;
   ```
2. If user missing, re-signup
3. Check password_hash() function available

#### Problem: Session not persisting after login
**Cause**: Session save path not writable
**Solution**:
```bash
# Check PHP session path
php -i | grep session.save_path

# Create and set permissions
mkdir -p /tmp/php_sessions
chmod 777 /tmp/php_sessions

# Update php.ini
session.save_path = "/tmp/php_sessions"
```

#### Problem: Redirect loop on login
**Cause**: Session not starting properly
**Solution**:
1. Check if session_start() called in config.php
2. Verify no output before session_start()
3. Clear browser cookies: Ctrl+Shift+Del

---

### Database Issues

#### Problem: "Column not found" error
**Cause**: Table not created properly
**Solution**:
1. Check installation completed
2. Verify table exists:
   ```sql
   DESCRIBE users;
   ```
3. If missing, run install.php again

#### Problem: "Deadlock found when trying to get lock"
**Cause**: Query timeout or concurrent access
**Solution**:
1. Check for running queries: `SHOW PROCESSLIST;`
2. Increase timeout in config.php
3. Restart MySQL: `sudo service mysql restart`

#### Problem: "Disk space exceeded"
**Cause**: Database too large
**Solution**:
1. Archive old transactions:
   ```sql
   DELETE FROM transactions WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
   ```
2. Optimize tables:
   ```sql
   OPTIMIZE TABLE users, tournaments, participants, transactions;
   ```

---

### Web Server Issues

#### Problem: ".htaccess not working"
**Cause**: mod_rewrite not enabled or .htaccess not allowed
**Solution**:
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Check Apache config
sudo nano /etc/apache2/apache2.conf

# Ensure AllowOverride is set
<Directory /var/www/html/tourment>
    AllowOverride All
</Directory>

# Restart Apache
sudo systemctl restart apache2
```

#### Problem: "403 Forbidden" error
**Cause**: File permissions incorrect
**Solution**:
```bash
chmod 755 /var/www/html/tourment/
chmod 644 /var/www/html/tourment/*.php
```

#### Problem: "404 Not Found" on certain pages
**Cause**: File not in correct directory
**Solution**:
1. Check file exists: `ls -la /var/www/html/tourment/`
2. Verify path in links (relative vs absolute)
3. Check typos in filenames

---

### PHP/Code Issues

#### Problem: "Call to undefined function password_hash()"
**Cause**: PHP version < 5.5.0
**Solution**:
1. Upgrade PHP to 7.4+
2. Or install password_compat library

#### Problem: "mysql driver not found"
**Cause**: MySQLi extension not installed
**Solution**:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql

# RHEL/CentOS
sudo yum install php-mysql

# Restart Apache
sudo systemctl restart apache2
```

#### Problem: "Undefined variable" warnings
**Cause**: Variable used before declaration
**Solution**:
1. Check initialization at top of file
2. Use isset() before accessing variables
3. Enable error reporting (dev only):
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

---

### Performance Issues

#### Problem: Pages loading slowly
**Cause**: Unoptimized queries or large dataset
**Solution**:
1. Add database indexes:
   ```sql
   ALTER TABLE participants ADD INDEX (user_id);
   ALTER TABLE participants ADD INDEX (tournament_id);
   ALTER TABLE transactions ADD INDEX (user_id);
   ```
2. Limit transaction history queries (already done - 50 limit)
3. Use CDN for Tailwind/Font Awesome

#### Problem: High memory usage
**Cause**: Large result sets loaded to memory
**Solution**:
1. Use LIMIT in queries
2. Use pagination for lists
3. Increase PHP memory_limit (if needed):
   ```php
   ini_set('memory_limit', '256M');
   ```

---

### Security Issues

#### Problem: SQL Injection vulnerability
**Cause**: Not using prepared statements
**Solution**:
- Already implemented: All queries use prepared statements
- Review code: Check all $stmt->bind_param() calls

#### Problem: XSS vulnerability
**Cause**: Not escaping output
**Solution**:
- Already implemented: All output uses htmlspecialchars()
- Review code: Check echo statements

#### Problem: CSRF attacks
**Cause**: No CSRF token
**Solution**:
- Current implementation: Form submissions via POST
- Consider adding: Token validation for critical actions
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate in form
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

---

### Browser/Client Issues

#### Problem: Right-click not disabled
**Cause**: JavaScript not loading
**Solution**:
1. Check browser console for errors (F12)
2. Verify JavaScript in common/bottom.php
3. Check Content Security Policy headers

#### Problem: Text selection still possible
**Cause**: CSS not applied
**Solution**:
1. Check Tailwind CSS loaded
2. Verify browser support
3. Add fallback:
   ```css
   * {
       -webkit-user-select: none;
       -moz-user-select: none;
       -ms-user-select: none;
       user-select: none;
   }
   ```

#### Problem: Mobile layout broken
**Cause**: Viewport meta tag missing or CSS not responsive
**Solution**:
- Already included: `<meta name="viewport" content="width=device-width, initial-scale=1.0">`
- Check Tailwind grid classes responsive

---

## 📊 MONITORING & MAINTENANCE

### Daily Checks
- [ ] Check error logs: `tail -f /var/log/apache2/error.log`
- [ ] Monitor disk space: `df -h`
- [ ] Check MySQL status: `sudo service mysql status`

### Weekly Checks
- [ ] Backup database
- [ ] Review transaction history
- [ ] Check user activity
- [ ] Monitor system resources

### Monthly Checks
- [ ] Archive old transactions
- [ ] Optimize database tables
- [ ] Review access logs
- [ ] Update security patches

### Commands
```bash
# Check error log
tail -50 /var/log/apache2/error.log

# Monitor MySQL
mysqladmin -u root -p status

# Backup database
mysqldump -u root -p rock_play > backup.sql

# Optimize tables
mysql -u root -p rock_play < optimize.sql

# Check disk usage
du -sh /var/www/html/tourment/
```

---

## 🔐 SECURITY HARDENING

### 1. Change Default Credentials
```sql
UPDATE admin SET password = PASSWORD('new_secure_password') WHERE username = 'admin';
```

### 2. Restrict File Access
```apache
# Prevent direct access to config
<Files "config.php">
    Deny from all
</Files>
```

### 3. Enable HTTPS
- Install SSL certificate
- Redirect HTTP to HTTPS:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 4. Disable Directory Listing
```apache
Options -Indexes
```

### 5. Rate Limiting
```apache
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 1000
</IfModule>
```

---

## 📞 SUPPORT CONTACTS

For issues with:
- **Database**: MySQL documentation: https://dev.mysql.com/
- **PHP**: PHP manual: https://www.php.net/manual/
- **Web Server**: Apache docs: https://httpd.apache.org/
- **Security**: OWASP: https://owasp.org/

---

## ✅ FINAL CHECKLIST BEFORE GOING LIVE

- [ ] SSL certificate installed
- [ ] Default credentials changed
- [ ] Database backed up
- [ ] All features tested
- [ ] Error reporting disabled (production)
- [ ] Logs reviewed and cleared
- [ ] Performance optimized
- [ ] Security hardened
- [ ] Monitoring configured
- [ ] Backup strategy documented

---

**Rock Play v1.0** - Production Ready! 🚀

For deployment support, refer to README.md and FEATURES.md
