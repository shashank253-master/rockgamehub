# 🚀 ROCK PLAY - QUICK START GUIDE

## ⚡ 30-Second Setup

### 1. Run Installation
```
Visit: http://localhost/tourment/install.php
Click: "Start Installation"
```

### 2. Login as User
```
URL: http://localhost/tourment/login.php
Action: Sign up with new account
```

### 3. Login as Admin
```
URL: http://localhost/tourment/admin/login.php
Username: admin
Password: admin123
```

---

## 📋 FILE CHECKLIST

✅ **Root Level**
- install.php - Database setup
- login.php - User auth
- index.php - Browse tournaments
- my_tournaments.php - User tournaments
- wallet.php - Wallet & history
- profile.php - User settings
- README.md - Full documentation
- .htaccess - Security config

✅ **common/ Folder**
- config.php - Database connection
- header.php - Page header + nav
- bottom.php - Page footer + nav

✅ **admin/ Folder**
- login.php - Admin login
- index.php - Dashboard
- tournament.php - Create tournaments
- manage_tournament.php - Manage tournament
- user.php - User management
- setting.php - Admin settings

✅ **admin/common/ Folder**
- header.php - Admin header
- bottom.php - Admin footer

---

## 🎮 USER PANEL FEATURES

### Homepage (index.php)
- View all upcoming tournaments
- Card grid layout with details
- Join button with payment

### My Tournaments (my_tournaments.php)
- **Upcoming/Live**: Tournaments joined + room details when live
- **Completed**: Past tournaments with results

### Wallet (wallet.php)
- Current balance display
- Transaction history (credits/debits)
- Add Money & Withdraw buttons (placeholder)

### Profile (profile.php)
- Edit username & email
- Change password
- View wallet balance
- Logout

---

## ⚙️ ADMIN PANEL FEATURES

### Dashboard (index.php)
- Total Users, Tournaments, Prize Distributed, Revenue
- Quick action buttons
- System overview

### Tournaments (tournament.php)
- Create new tournament form
- List all tournaments
- Edit/Delete buttons

### Manage Tournament (manage_tournament.php)
- Update room ID & password
- Select winner from dropdown
- Declare Winner & Distribute Prize
- View all participants

### Users (user.php)
- List all registered users
- Show wallet balance
- Tournament count per user
- Block user button (placeholder)

### Settings (setting.php)
- Update admin username
- Change admin password
- Logout

---

## 💾 DATABASE INFO

**Host**: 127.0.0.1
**User**: root
**Password**: root
**Database Name**: rock_play (auto-created)

**Tables Created**:
1. users - User accounts & wallet
2. admin - Admin accounts
3. tournaments - Tournament details
4. participants - User tournament participation
5. transactions - Wallet transaction history

---

## 🔐 SECURITY IMPLEMENTED

✅ Bcrypt password hashing
✅ Prepared statements (SQL injection prevention)
✅ Session-based authentication
✅ Input validation & sanitization
✅ XSS prevention (htmlspecialchars)
✅ Disabled right-click, text selection, zoom
✅ Form validation on server-side

---

## 📱 RESPONSIVE DESIGN

- Mobile-first approach
- Tailwind CSS (no Bootstrap)
- Bottom navigation bar
- Grid layouts for all screen sizes
- Touch-friendly UI elements

---

## 🎨 DESIGN ELEMENTS

**Colors**:
- Dark Theme (slate #0f172a)
- Primary: Blue & Purple gradients
- Success: Green
- Error: Red
- Warning: Yellow

**Icons**: Font Awesome 6.4.0

**Fonts**: System fonts (-apple-system, BlinkMacSystemFont, Segoe UI, Roboto)

---

## 🔄 TYPICAL USER FLOW

### User Journey:
1. Sign up → login.php
2. Browse tournaments → index.php
3. Join tournament (pay entry fee)
4. View joined tournaments → my_tournaments.php
5. When tournament goes live, see room details
6. Check wallet → wallet.php
7. Update profile → profile.php
8. Logout

### Admin Journey:
1. Login → admin/login.php
2. View dashboard → admin/index.php
3. Create tournament → admin/tournament.php
4. Manage tournament → admin/manage_tournament.php
5. Declare winner → triggers wallet credit
6. View users → admin/user.php
7. Update settings → admin/setting.php
8. Logout

---

## 💡 KEY MECHANICS

### Tournament Join Process
1. User clicks "Join Now"
2. System checks wallet balance
3. Deducts entry fee
4. Adds user to participants
5. Records transaction
6. Shows success message

### Winner Declaration Process
1. Admin selects winner from dropdown
2. Prize amount added to winner's wallet
3. Transaction recorded
4. Tournament marked as "Completed"
5. Success message shown

### Wallet Transaction
- Type: Credit (prize) or Debit (entry fee)
- Description: Auto-generated
- All transactions tracked with timestamp

---

## 📲 PRICING FORMAT

All prices in **Indian Rupees (₹)**

Examples:
- Entry Fee: ₹100
- Prize Pool: ₹5,000
- Wallet: ₹2,500.50

---

## ⚠️ IMPORTANT REMINDERS

1. **Installation Required**: Visit install.php first
2. **Default Admin**: Username `admin`, Password `admin123`
3. **Change Admin Password**: Do this after first login
4. **MySQL Running**: Ensure MySQL service is active
5. **PHP 7.4+**: Required for password_hash/verify
6. **No AJAX**: All forms reload the page
7. **Session-based**: Uses PHP sessions for auth

---

## 🧪 TESTING CHECKLIST

- [ ] Installation completes successfully
- [ ] Admin login works with default credentials
- [ ] User signup creates account
- [ ] User login works
- [ ] Can create tournament as admin
- [ ] Can join tournament as user
- [ ] Wallet balance updates after joining
- [ ] Can view participants as admin
- [ ] Can declare winner and distribute prize
- [ ] Winner receives prize in wallet
- [ ] Can view transaction history
- [ ] All navigation links work
- [ ] Responsive on mobile browser
- [ ] Right-click disabled
- [ ] Text selection disabled

---

## 🆘 TROUBLESHOOTING

### Database Connection Error
- Check MySQL is running
- Verify credentials in common/config.php
- Ensure database doesn't already exist (delete for fresh install)

### Session Not Working
- Check if PHP session save path is writable
- Verify session_start() called in config

### Redirect Loop
- Clear browser cookies
- Check if user/admin ID exists in database

### Styling Not Showing
- Verify Tailwind CDN is accessible
- Check Font Awesome CDN is accessible
- Clear browser cache

---

## 📞 SUPPORT

For issues:
1. Check README.md for detailed documentation
2. Verify all files are in correct directories
3. Check PHP error logs
4. Test database connection separately

---

**Rock Play v1.0** - Ready to Deploy! 🎮

Visit: http://localhost/tourment/install.php to begin
