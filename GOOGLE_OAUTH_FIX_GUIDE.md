# 🔧 Google OAuth Fix Guide

## ❌ Error: "redirect_uri_mismatch"

This error occurs because the redirect URI in your code doesn't match what's configured in Google Cloud Console.

---

## ✅ Quick Solution: Use Demo OAuth (Works Immediately!)

**No Google setup required!**

1. Go to: `http://localhost:8000/login.html`
2. Click **"👤 User Login"**
3. Click the **GREEN button**: **"🧪 Demo OAuth Login (No Setup Required)"**
4. ✅ You'll be instantly logged in with a demo account!

This creates a test OAuth user without needing Google configuration.

---

## 🔐 Full Solution: Fix Google OAuth

If you want real Google OAuth to work, follow these steps:

### Step 1: Go to Google Cloud Console

1. Visit: https://console.cloud.google.com/apis/credentials
2. Sign in with your Google account
3. Find your OAuth 2.0 Client ID: `1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2`

### Step 2: Add Redirect URIs

1. Click the **Edit** button (pencil icon) on your OAuth client
2. Scroll to **"Authorized redirect URIs"**
3. Click **"+ ADD URI"** and add these URIs **one by one**:

```
http://localhost:8000/auth/oauth_callback_simple.php
```

```
http://localhost:8000/auth/oauth_callback.php
```

```
http://localhost/PROPLEDGER/auth/oauth_callback_simple.php
```

```
http://localhost/PROPLEDGER/auth/oauth_callback.php
```

4. Click **"SAVE"** at the bottom

### Step 3: Wait 5 Minutes

Google takes a few minutes to propagate the changes. Wait 5 minutes, then try again.

### Step 4: Test Google OAuth

1. Go to: `http://localhost:8000/login.html`
2. Click **"👤 User Login"**
3. Click the **BLUE button**: **"Continue with Google"**
4. Sign in with your Google account
5. ✅ You should be redirected back and logged in!

---

## 📋 Current Configuration

**Your OAuth Client ID:** `1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2`

**Current Redirect URI in Code:**
```
http://localhost:8000/auth/oauth_callback_simple.php
```

**This MUST match exactly** in your Google Cloud Console.

---

## 🐛 Troubleshooting

### Still getting redirect_uri_mismatch?

1. **Check the exact error message** - it will show which URI Google received
2. **Make sure you saved** in Google Cloud Console
3. **Wait 5-10 minutes** for changes to propagate
4. **Clear browser cache** and try again
5. **Use Demo OAuth** as a temporary solution

### Can't access Google Cloud Console?

Use the **Demo OAuth button** instead - it works perfectly for testing!

---

## 🎯 Recommended Approach

**For Development/Testing:**
- ✅ Use **Demo OAuth** (green button)
- Fast, no setup required
- Creates realistic test accounts

**For Production:**
- ✅ Configure real Google OAuth
- Better user experience
- Professional authentication

---

## 📞 Need Help?

- Demo OAuth works immediately: `http://localhost:8000/login.html`
- Test authentication: `http://localhost:8000/test-auth.html`
- Demo accounts: `investor@propledger.com` / `password`

---

**Last Updated:** November 4, 2025
