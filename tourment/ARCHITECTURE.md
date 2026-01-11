# 🗺️ ROCK PLAY - VISUAL ARCHITECTURE & FLOW DIAGRAMS

## 📊 APPLICATION ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────┐
│                     ROCK PLAY APPLICATION                   │
│                    (Mobile-First Web App)                   │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│                      FRONTEND LAYER                          │
├──────────────────────────────────────────────────────────────┤
│  HTML5 + Tailwind CSS + Vanilla JavaScript                   │
│  ├─ User Panel (Login, Home, My Tournaments, Wallet)        │
│  ├─ Admin Panel (Login, Dashboard, Tournaments, Users)      │
│  └─ Shared Components (Header, Navigation, Footer)          │
└──────────────────────────────────────────────────────────────┘
                              ↕
┌──────────────────────────────────────────────────────────────┐
│                      BACKEND LAYER                           │
├──────────────────────────────────────────────────────────────┤
│  PHP 7.4+ (Pure, No Frameworks)                              │
│  ├─ Authentication (Session Management, Bcrypt)             │
│  ├─ Business Logic (Join, Declare Winner, Transactions)     │
│  ├─ Validation (Server-side Form Validation)                │
│  └─ Security (Prepared Statements, XSS Prevention)          │
└──────────────────────────────────────────────────────────────┘
                              ↕
┌──────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                          │
├──────────────────────────────────────────────────────────────┤
│  MySQL 5.7+ (rock_play)                                      │
│  ├─ users (Accounts & Wallet)                                │
│  ├─ admin (Admin Accounts)                                   │
│  ├─ tournaments (Tournament Details)                         │
│  ├─ participants (User-Tournament Links)                     │
│  └─ transactions (Wallet History)                            │
└──────────────────────────────────────────────────────────────┘
```

---

## 🔄 USER AUTHENTICATION FLOW

```
USER PANEL LOGIN/SIGNUP
═══════════════════════

START
  ↓
┌─────────────────────┐
│ login.php           │
│ (Dual Tab Tabs)     │
└─────────────────────┘
  ├─ LOGIN TAB
  │   ├─ Username
  │   ├─ Password
  │   └─ Submit
  │       ↓
  │   Check users table
  │       ↓
  │   Password_verify()
  │       ↓
  │   ✓ Valid?
  │   ├─ YES → Session create → Redirect to index.php
  │   └─ NO  → Error message → Stay on login.php
  │
  └─ SIGNUP TAB
      ├─ Username
      ├─ Email
      ├─ Password
      └─ Confirm Password
          ↓
      Validation:
      ├─ Non-empty?
      ├─ Passwords match?
      ├─ Min 6 chars?
      └─ Username/Email unique?
          ↓
      ✓ Valid?
      ├─ YES → Hash password → Insert user → Success message
      └─ NO  → Error message → Stay on signup.php
          ↓
        END


ADMIN PANEL LOGIN
═════════════════

START
  ↓
┌─────────────────────┐
│ admin/login.php     │
│ (Simple Form)       │
└─────────────────────┘
  ├─ Username
  ├─ Password
  └─ Submit
      ↓
  Check admin table
      ↓
  Password_verify()
      ↓
  ✓ Valid?
  ├─ YES → Session create → Redirect to admin/index.php
  └─ NO  → Error message → Stay on admin/login.php
      ↓
    END
```

---

## 🎮 TOURNAMENT JOIN FLOW

```
USER JOINS TOURNAMENT
════════════════════

Home Page (index.php)
  ├─ Display Tournament Cards
  ├─ Show: Title, Game, Fee, Prize, Time
  └─ Join Button (Form)
      ↓
User Clicks "Join Now"
      ↓
┌──────────────────────────┐
│ POST /index.php          │
│ Data: tournament_id      │
└──────────────────────────┘
      ↓
┌──────────────────────────────────────────────┐
│ PHP Processing                               │
├──────────────────────────────────────────────┤
│ 1. Get tournament entry_fee                  │
│ 2. Get user wallet_balance                   │
│ 3. Check if already joined                   │
│ 4. Check if balance >= entry_fee             │
└──────────────────────────────────────────────┘
      ↓
┌──────────────────────────┐
│ Validation Results       │
└──────────────────────────┘
      │
      ├─ Insufficient Balance
      │   ↓
      │  Show Error: "Need ₹X more"
      │   ↓
      │  Reload page
      │
      ├─ Already Joined
      │   ↓
      │  Show Error: "Already joined"
      │   ↓
      │  Reload page
      │
      └─ ✓ VALID
          ↓
        ┌────────────────────────────────────┐
        │ Database Updates                   │
        ├────────────────────────────────────┤
        │ 1. UPDATE users wallet_balance -=  │
        │ 2. INSERT participants record      │
        │ 3. INSERT transactions record      │
        └────────────────────────────────────┘
          ↓
        Show Success Message
          ↓
        Reload page
          ↓
        Tournament now in "My Tournaments"
          ↓
        END
```

---

## 👑 WINNER DECLARATION FLOW

```
ADMIN DECLARES WINNER
═════════════════════

Admin Panel (manage_tournament.php)
  ├─ View Tournament Details
  ├─ View Participants List
  └─ "Declare Winner" Form
      ↓
┌──────────────────────────────┐
│ Admin Selects Winner         │
│ ├─ Dropdown: All Participants│
│ └─ Submit Button             │
└──────────────────────────────┘
      ↓
┌────────────────────────────────────────┐
│ Confirmation Dialog                    │
│ "Are you sure? This cannot be undone"  │
└────────────────────────────────────────┘
      ├─ Cancel → Go Back
      └─ Confirm → Proceed
          ↓
        ┌──────────────────────────────────────┐
        │ POST admin/manage_tournament.php      │
        │ Data: winner_id                      │
        └──────────────────────────────────────┘
          ↓
        ┌──────────────────────────────────────────────┐
        │ PHP Processing (Atomic Transaction)         │
        ├──────────────────────────────────────────────┤
        │ 1. Get tournament prize_pool amount          │
        │ 2. Get winner_id from form                   │
        │ 3. BEGIN TRANSACTION                         │
        │    ├─ UPDATE users: wallet_balance += prize  │
        │    ├─ INSERT transactions: credit record     │
        │    └─ UPDATE tournaments:                    │
        │        ├─ status = 'Completed'              │
        │        └─ winner_id = selected_id           │
        │ 4. COMMIT TRANSACTION                        │
        └──────────────────────────────────────────────┘
          ↓
        Update tournament status to "Completed"
          ↓
        Show Success: "Prize distributed!"
          ↓
        Display winner badge on participant list
          ↓
        Winner receives wallet credit notification
          ↓
        END
```

---

## 💰 WALLET TRANSACTION FLOW

```
TRANSACTION LIFECYCLE
════════════════════

DEBIT TRANSACTION (Join Tournament)
───────────────────────────────────
  Entry Fee: ₹100
      ↓
  INSERT transactions
  ├─ user_id
  ├─ amount: 100
  ├─ type: 'debit'
  ├─ description: "Tournament entry fee"
  └─ created_at: NOW()
      ↓
  UPDATE users
  └─ wallet_balance -= 100
      ↓
  Record in History: -₹100


CREDIT TRANSACTION (Win Prize)
──────────────────────────────
  Prize: ₹5,000
      ↓
  INSERT transactions
  ├─ user_id
  ├─ amount: 5000
  ├─ type: 'credit'
  ├─ description: "Prize from Tournament Name"
  └─ created_at: NOW()
      ↓
  UPDATE users
  └─ wallet_balance += 5000
      ↓
  Record in History: +₹5,000


WALLET HISTORY DISPLAY
──────────────────────
  SELECT * FROM transactions
  WHERE user_id = {user_id}
  ORDER BY created_at DESC
  LIMIT 50
      ↓
  Display as List:
  ├─ [Green Icon] +₹5,000 | Prize from X | Jan 20, 8:30 PM
  ├─ [Red Icon]   -₹100   | Tournament entry fee | Jan 15, 2:45 PM
  ├─ ...
  └─ (Load 50 most recent)
```

---

## 📊 DATABASE RELATIONSHIPS

```
ENTITY RELATIONSHIP DIAGRAM
═══════════════════════════

┌─────────────────┐         ┌──────────────────────┐
│     USERS       │         │   TOURNAMENTS        │
├─────────────────┤         ├──────────────────────┤
│ id (PK)         │         │ id (PK)              │
│ username        │         │ title                │
│ email           │         │ game_name            │
│ password        │         │ entry_fee            │
│ wallet_balance  │         │ prize_pool           │
│ created_at      │         │ match_time           │
└─────────────────┘         │ room_id              │
        │                   │ room_password        │
        │                   │ status               │
        │                   │ commission_percentage│
        │                   │ winner_id (FK→users) │
        │                   │ created_at           │
        │                   └──────────────────────┘
        │                           │
        │                           │
        ├─────────────────────┬─────┘
        │                     │
        │    ┌────────────────┴────────────────┐
        │    │                                 │
        │    ▼                                 ▼
    ┌──────────────────────┐      ┌──────────────────────┐
    │   PARTICIPANTS       │      │   TRANSACTIONS       │
    ├──────────────────────┤      ├──────────────────────┤
    │ id (PK)              │      │ id (PK)              │
    │ user_id (FK→users)   │      │ user_id (FK→users)   │
    │ tournament_id (FK)   │      │ amount               │
    │ joined_at            │      │ type (credit/debit)  │
    └──────────────────────┘      │ description          │
                                  │ created_at           │
    ┌──────────────────────┐      └──────────────────────┘
    │      ADMIN           │
    ├──────────────────────┤
    │ id (PK)              │
    │ username             │
    │ password             │
    └──────────────────────┘


KEY RELATIONSHIPS
═════════════════
users 1 ──► M participants ◄─── 1 tournaments
users 1 ──► M transactions
tournaments 1 ──► M participants
tournaments 1 ──► 1 users (winner_id, nullable)
```

---

## 🔐 SECURITY LAYERS

```
SECURITY ARCHITECTURE
════════════════════

┌───────────────────────────────────┐
│    CLIENT-SIDE SECURITY (JS)      │
├───────────────────────────────────┤
│ • Disable right-click             │
│ • Disable text selection          │
│ • Disable zoom controls           │
│ • Prevent copy/paste              │
└───────────────────────────────────┘
            ↓
┌───────────────────────────────────────────┐
│    FORM LEVEL SECURITY (HTML/JS)          │
├───────────────────────────────────────────┤
│ • POST method (not GET)                   │
│ • No sensitive data in URLs               │
│ • Confirmation dialogs for critical ops   │
└───────────────────────────────────────────┘
            ↓
┌──────────────────────────────────────────────┐
│    SERVER-SIDE VALIDATION (PHP)              │
├──────────────────────────────────────────────┤
│ • Input validation (non-empty, type)         │
│ • Range validation (positive numbers)        │
│ • Uniqueness checks (username, email)        │
│ • Logical validation (balance, permissions)  │
└──────────────────────────────────────────────┘
            ↓
┌──────────────────────────────────────────────────┐
│    DATABASE SECURITY (PHP/MySQL)                 │
├──────────────────────────────────────────────────┤
│ • Prepared statements (parameterized queries)    │
│ • Bind parameters (type-safe)                    │
│ • Escape output (htmlspecialchars)               │
│ • SQL injection prevention                       │
└──────────────────────────────────────────────────┘
            ↓
┌──────────────────────────────────────────────────┐
│    PASSWORD SECURITY (Bcrypt)                    │
├──────────────────────────────────────────────────┤
│ • Hashing algorithm: PASSWORD_BCRYPT             │
│ • One-way encryption                             │
│ • Salted hashes                                  │
│ • Comparison: password_verify()                  │
└──────────────────────────────────────────────────┘
            ↓
┌──────────────────────────────────────────────────┐
│    SESSION SECURITY (PHP)                        │
├──────────────────────────────────────────────────┤
│ • Server-side session storage                    │
│ • Session ID in cookies                          │
│ • Automatic session start                        │
│ • Session destroy on logout                      │
└──────────────────────────────────────────────────┘
```

---

## 🎯 USER JOURNEY MAP

```
COMPLETE USER JOURNEY
════════════════════

NEW USER
════════

  1. Visit login.php
      ↓
  2. Click "Sign Up" tab
      ↓
  3. Enter Username, Email, Password
      ↓
  4. Account Created ✓
      ↓
  5. Login with credentials
      ↓
  6. Redirected to index.php (HOME)


HOME PAGE EXPERIENCE
════════════════════

  index.php
    ├─ View all tournaments in grid
    ├─ Each card shows:
    │  ├─ Title & Game Name
    │  ├─ Entry Fee (₹)
    │  ├─ Prize Pool (₹)
    │  └─ "Join Now" button
    └─ Browse & select tournament
        ↓
    Click "Join Now"
        ↓
    Form POST submission
        ↓
    Wallet balance checked
        ↓
    Entry fee deducted ✓
        ↓
    Success message
        ↓
    Reload page


MY TOURNAMENTS EXPERIENCE
═════════════════════════

  my_tournaments.php
    │
    ├─ TAB: UPCOMING/LIVE
    │   ├─ Shows all joined tournaments
    │   │   (status = Upcoming or Live)
    │   ├─ When LIVE:
    │   │   ├─ Room ID visible
    │   │   └─ Room Password visible
    │   └─ Tournament details
    │
    └─ TAB: COMPLETED
        ├─ Shows past tournaments
        ├─ Result:
        │  ├─ "Winner" badge (if won)
        │  └─ "Participated" (if lost)
        └─ Prize amount display


WALLET EXPERIENCE
═════════════════

  wallet.php
    ├─ Large balance card
    │  └─ Current: ₹X,XXX.XX
    ├─ Stats:
    │  ├─ Total Credited: ₹X
    │  └─ Total Debited: ₹X
    ├─ Transaction list
    │  ├─ +₹5,000 | Prize | Jan 20
    │  ├─ -₹100 | Entry Fee | Jan 15
    │  └─ ... (up to 50 transactions)
    └─ Buttons (placeholder):
       ├─ Add Money (disabled)
       └─ Withdraw (disabled)


PROFILE & LOGOUT
════════════════

  profile.php
    │
    ├─ TAB: PROFILE
    │   ├─ Edit username
    │   ├─ Edit email
    │   ├─ View wallet balance
    │   └─ Save changes
    │
    └─ TAB: SECURITY
        ├─ Change password
        │  ├─ Current password
        │  ├─ New password
        │  └─ Confirm password
        └─ Logout button
            ↓
        Session destroyed
            ↓
        Redirect to login.php
```

---

## ⚙️ ADMIN DASHBOARD FLOW

```
ADMIN EXPERIENCE
════════════════

LOGIN → admin/login.php
  ↓
  username: admin
  password: admin123 (default, CHANGE!)
  ↓
  Redirect: admin/index.php (DASHBOARD)


DASHBOARD (admin/index.php)
══════════════════════════

  Statistics Cards:
  ├─ Total Users: 150
  ├─ Total Tournaments: 25
  ├─ Prize Distributed: ₹1,25,000
  └─ Total Revenue: ₹25,000
  
  Quick Actions:
  ├─ Create New Tournament →
  └─ Manage Users →


CREATE TOURNAMENT
═════════════════

  admin/tournament.php
    │
    ├─ CREATE FORM
    │   ├─ Title: "PUBG Grand X"
    │   ├─ Game: "PUBG Mobile"
    │   ├─ Entry Fee: ₹100
    │   ├─ Prize Pool: ₹5,000
    │   ├─ Match Time: 2025-01-25 08:00 PM
    │   ├─ Commission: 20%
    │   └─ [Create Tournament]
    │       ↓
    │       INSERT tournaments
    │       ↓
    │       Success: "Tournament created"
    │
    └─ TOURNAMENT LIST
        ├─ All tournaments (DESC by time)
        ├─ Each shows:
        │  ├─ Title, Game Name
        │  ├─ Entry: ₹100
        │  ├─ Prize: ₹5,000
        │  ├─ Participants: 45
        │  ├─ Commission: 20%
        │  └─ Buttons:
        │     ├─ [Manage] (if not completed)
        │     └─ [Delete]
        └─ Actions
            ├─ Manage: manage_tournament.php
            └─ Delete: Confirmation + DELETE


MANAGE TOURNAMENT
═════════════════

  admin/manage_tournament.php?id=X
    │
    ├─ TOURNAMENT HEADER
    │  └─ Title, Game, Entry, Prize, Status
    │
    ├─ ROOM DETAILS (if not Completed)
    │   ├─ Room ID: [text input]
    │   ├─ Room Password: [text input]
    │   ├─ Status: [Upcoming/Live dropdown]
    │   └─ [Update Room Details]
    │
    ├─ DECLARE WINNER (if not Completed)
    │   ├─ Select Winner: [dropdown of participants]
    │   ├─ Prize: ₹5,000
    │   └─ [Declare Winner & Distribute Prize]
    │       ↓
    │       Confirmation dialog
    │       ↓
    │       ├─ Award ₹5,000 to winner
    │       ├─ Record transaction
    │       ├─ Mark tournament completed
    │       └─ Show success
    │
    └─ PARTICIPANTS LIST
        ├─ All joined users
        ├─ Winner badge (if declared)
        └─ Editable throughout tournament


USER MANAGEMENT
════════════════

  admin/user.php
    │
    ├─ STATISTICS
    │  ├─ Total Users: 150
    │  ├─ Active Players: 98
    │  └─ Total Wallet: ₹2,50,000
    │
    └─ USER LIST
        ├─ Each user card shows:
        │  ├─ Username
        │  ├─ Email
        │  ├─ Joined date
        │  ├─ Wallet: ₹X,XXX
        │  ├─ Tournaments: 5
        │  └─ [Block] button (placeholder)
        └─ Sortable by any field


ADMIN SETTINGS
═══════════════

  admin/setting.php
    │
    ├─ TAB: ADMIN INFO
    │   ├─ Edit username
    │   ├─ Admin ID (read-only)
    │   └─ [Save Changes]
    │
    └─ TAB: SECURITY
        ├─ Change Password
        │  ├─ Current password
        │  ├─ New password
        │  └─ [Change Password]
        └─ [Logout]
            ↓
        Session destroyed
            ↓
        Redirect to admin/login.php
```

---

## 📱 RESPONSIVE LAYOUT STRUCTURE

```
MOBILE (< 640px)
════════════════
┌──────────────┐
│   HEADER     │ ← Sticky
├──────────────┤
│              │
│   CONTENT    │ ← Full width
│  (Scrollable)│
│              │
├──────────────┤
│ NAV (Bottom) │ ← Sticky (h-20)
│ Home│ Tours │
│ Wallet│Prof │
└──────────────┘


TABLET (640-1024px)
═══════════════════
┌────────────────────────┐
│      HEADER            │ ← Sticky
├────────────────────────┤
│                        │
│  CONTENT (2 columns)   │
│  Grid layout           │
│  (Scrollable)          │
│                        │
├────────────────────────┤
│ NAV (Bottom) 5 icons   │ ← Sticky
└────────────────────────┘


DESKTOP (> 1024px)
═══════════════════
┌──────────────────────────────────┐
│           HEADER                 │ ← Sticky
├──────────────────────────────────┤
│                                  │
│  CONTENT (2-4 columns grid)      │
│  Optimized spacing               │
│  (Scrollable)                    │
│                                  │
├──────────────────────────────────┤
│  NAV (Bottom) 5 icons            │ ← Sticky
└──────────────────────────────────┘
```

---

**Rock Play v1.0** - Complete Architecture & Workflow Diagrams

All systems are fully documented and ready for implementation! 🚀
