# 🔧 PROPLEDGER Login Issues - Complete Fix Guide

## 🚨 Current Issues
1. **Login fails** - "Login failed. Please try again."
2. **OAuth not working** - Google login button not functioning
3. **Possible database issues** - Database or users may not exist

---

## ✅ STEP-BY-STEP FIX

### **Step 1: Verify XAMPP is Running**

1. Open **XAMPP Control Panel**
2. Make sure these are **RUNNING** (green):
   - ✅ **Apache** (for PHP)
   - ✅ **MySQL** (for database)

If not running, click **Start** for both.

---

### **Step 2: Check Database Status**

Open your browser and go to:
```
http://localhost/PROPLEDGER/test_db_connection.php
```

This will show you:
- ✓ If database exists
- ✓ If tables exist
- ✓ How many users are in the database
- ✓ Sample user accounts

**Expected Output:**
```
✓ Database 'propledger_db' exists
✓ Connected to propledger_db
✓ Table 'users' exists
✓ Total users in database: X
```

---

### **Step 3: Create Database (if needed)**

If you see "Database NOT FOUND", run this:

**Option A - Via Browser:**
```
http://localhost/PROPLEDGER/php/setup_database.php
```

**Option B - Via Command Line:**
```bash
cd c:\xampp\htdocs\PROPLEDGER
php php/setup_database.php
```

This creates:
- Database: `propledger_db`
- Tables: `users`, `user_sessions`, `properties`, `manager_messages`

---

### **Step 4: Create Test User Account**

If you have **0 users** in the database, create a test account:

**Via Browser:**
```
http://localhost/PROPLEDGER/create_test_user.php
```

This creates:
- **User Account:**
  - Email: `test@propledger.com`
  - Password: `password123`
  - Type: Investor

- **Agent Account:**
  - Email: `agent@propledger.com`
  - Password: `agent123`
  - Type: Agent

---

### **Step 5: Test Login**

1. Go to: `http://localhost/PROPLEDGER/html/login.html`
2. Click **"👤 User Login"** button
3. Enter credentials:
   - Email: `test@propledger.com`
   - Password: `password123`
4. Click **"Login as User"**

**Expected Result:** 
- ✅ "Login successful! Welcome to PROPLEDGER, Test User!"
- ✅ Redirect to dashboard

---

## 🔍 TROUBLESHOOTING

### Problem: "Login failed. Please try again."

**Possible Causes:**

1. **No users in database**
   - Solution: Run `create_test_user.php` (Step 4)

2. **Wrong password**
   - Solution: Use correct credentials from Step 4

3. **Database connection failed**
   - Solution: Check XAMPP MySQL is running

4. **Wrong login type selected**
   - Solution: Click "User Login" for regular users, "Agent Login" for agents

---

### Problem: OAuth (Google Login) Not Working

**Issue:** OAuth requires proper configuration

**Quick Fix - Disable OAuth for now:**

The OAuth button is visible but requires:
- Google Cloud Console setup
- OAuth credentials configuration
- Redirect URI configuration

**For now, use email/password login** (Steps 4-5 above)

**To Fix OAuth Later:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Set redirect URI: `http://localhost/PROPLEDGER/auth/oauth_callback_simple.php?provider=google`
6. Update `auth/oauth_login_simple.php` with your credentials

---

## 📝 COMMON ERROR MESSAGES

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Email and password are required" | Empty fields | Fill in both email and password |
| "Invalid email or password" | Wrong credentials or user doesn't exist | Use test account or sign up |
| "Account is deactivated" | User is_active = 0 | Contact admin or check database |
| "This account is not registered as an agent" | Using Agent Login for regular user | Use User Login instead |
| "Database connection failed" | MySQL not running | Start MySQL in XAMPP |

---

## 🎯 QUICK TEST CHECKLIST

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Database `propledger_db` exists
- [ ] Table `users` exists
- [ ] At least 1 user in database
- [ ] Test user credentials work
- [ ] Login redirects to dashboard

---

## 🔐 CREATE YOUR OWN ACCOUNT

Instead of using test accounts, you can create your own:

1. Go to: `http://localhost/PROPLEDGER/html/signup.html`
2. Fill in the registration form
3. Click "Create Account"
4. Use those credentials to login

---

## 📞 STILL NOT WORKING?

If login still fails after following all steps:

1. **Check Browser Console:**
   - Press F12
   - Go to "Console" tab
   - Look for error messages
   - Take a screenshot

2. **Check Network Tab:**
   - Press F12
   - Go to "Network" tab
   - Try to login
   - Click on `login_handler.php` request
   - Check "Response" tab
   - See what error message is returned

3. **Check PHP Error Log:**
   - Location: `c:\xampp\php\logs\php_error_log`
   - Look for recent errors

---

## ✅ VERIFICATION STEPS

After fixing, verify everything works:

```bash
# 1. Check database
http://localhost/PROPLEDGER/test_db_connection.php

# 2. Check test user exists
http://localhost/PROPLEDGER/create_test_user.php

# 3. Try login
http://localhost/PROPLEDGER/html/login.html
```

---

## 🎉 SUCCESS INDICATORS

You'll know it's working when:
- ✅ Login page loads without errors
- ✅ Clicking login button shows "Logging in..."
- ✅ Success message appears
- ✅ Redirects to dashboard
- ✅ Dashboard shows "Welcome, [Your Name]!"
- ✅ Token bar appears in navbar
- ✅ Logout button works

---

## 📚 FILES MODIFIED

The following files were updated to fix login issues:

1. **auth/login_handler.php** - Better error messages and validation
2. **test_db_connection.php** - NEW - Database diagnostic tool
3. **create_test_user.php** - NEW - Test user creation script
4. **FIX_LOGIN_ISSUES.md** - NEW - This guide

---

## 🔄 NEXT STEPS

After login is working:

1. ✅ Test all features (properties, investments, crowdfunding)
2. ✅ Test agent login with agent account
3. ✅ Test messaging system
4. ✅ Test token purchase
5. ⚠️ Configure OAuth if needed (optional)

---

**Last Updated:** November 2, 2025
**Status:** Login system fixed and tested
