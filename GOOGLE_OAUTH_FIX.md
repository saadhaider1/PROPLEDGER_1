# 🔧 Fix Google OAuth Redirect URI Error

## ❌ Current Error
```
Error 400: redirect_uri_mismatch
```

This happens because the redirect URI in your code doesn't match what's registered in Google Cloud Console.

---

## ✅ Solution: Add Redirect URI to Google Cloud Console

### Step 1: Go to Google Cloud Console
Visit: https://console.cloud.google.com/apis/credentials

### Step 2: Find Your OAuth Client
- Look for: **Web client 1** or your OAuth 2.0 Client ID
- Client ID: `1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2.apps.googleusercontent.com`

### Step 3: Edit the OAuth Client
Click on the client name to edit it

### Step 4: Add Authorized Redirect URIs
In the "Authorized redirect URIs" section, add:

```
http://localhost:8000/auth/oauth_callback_simple.php
```

**Important Notes:**
- ✅ Use `http://localhost:8000` (NOT `http://127.0.0.1:8000`)
- ✅ Do NOT include `?provider=google` in the URI
- ✅ Make sure there's no trailing slash

### Step 5: Save Changes
Click "SAVE" at the bottom of the page

### Step 6: Wait (Optional)
Sometimes it takes a few seconds for changes to propagate. Wait 10-30 seconds.

---

## 🧪 Test Again

After adding the redirect URI:

1. Go to: http://localhost:8000/login.html
2. Click "User Login"
3. Click "Continue with Google"
4. ✅ Should work now!

Or test at: http://localhost:8000/test-auth.html

---

## 📋 Current Configuration

### Redirect URI (Fixed)
```
http://localhost:8000/auth/oauth_callback_simple.php
```

### Client ID
```
1059043152331-f24i9e3rpgv1c10h1fpel2aqgn5tfev2.apps.googleusercontent.com
```

### Client Secret
```
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
```

---

## 🔍 Verify Your Settings

In Google Cloud Console, your OAuth client should have:

**Authorized JavaScript origins:**
```
http://localhost:8000
```

**Authorized redirect URIs:**
```
http://localhost:8000/auth/oauth_callback_simple.php
```

---

## 🐛 Still Not Working?

### Option 1: Check the Exact Error
Look at the Google error page - it will show:
- The redirect URI you're using
- The redirect URIs that are registered

Make sure they match EXACTLY (including http vs https, port number, etc.)

### Option 2: Use a Different Port
If you need to use a different port, update these files:

1. `auth/oauth_login_simple.php` - Line with `redirect_uri`
2. `auth/oauth_callback_simple.php` - Line with `redirect_uri`
3. Google Cloud Console - Add the new redirect URI

### Option 3: Create New OAuth Credentials
If the current credentials don't work:

1. Go to Google Cloud Console
2. Create new OAuth 2.0 Client ID
3. Application type: Web application
4. Add redirect URI: `http://localhost:8000/auth/oauth_callback_simple.php`
5. Copy the new Client ID and Client Secret
6. Update `auth/oauth_login_simple.php` and `auth/oauth_callback_simple.php`

---

## ✅ After Fixing

Once you add the redirect URI to Google Cloud Console, OAuth login will work:

1. Click "Continue with Google"
2. Sign in with your Google account
3. Grant permissions
4. ✅ Redirected back to PROPLEDGER
5. ✅ Automatically logged in!

---

## 📝 Summary

**What was wrong:**
- Redirect URI had `?provider=google` query parameter
- This didn't match Google Cloud Console configuration

**What was fixed:**
- Removed query parameter from redirect URI
- Now using: `http://localhost:8000/auth/oauth_callback_simple.php`

**What you need to do:**
- Add this URI to Google Cloud Console
- Save changes
- Test again

---

**Quick Link:** https://console.cloud.google.com/apis/credentials
