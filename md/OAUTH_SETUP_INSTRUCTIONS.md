# 🔐 OAuth Setup Instructions for PROPLEDGER

## Overview
PROPLEDGER now supports OAuth authentication with Google, LinkedIn, and Facebook alongside traditional email/password authentication.

## 📋 Setup Steps

### 1. Update Database Schema
First, run the database update script:
```
http://localhost/PROPLEDGER/php/update_oauth_schema.php
```

### 2. Configure OAuth Providers

#### 🔵 Google OAuth Setup
1. Go to [Google Cloud Console](https://console.developers.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Set Application Type: "Web application"
6. Add Authorized Redirect URI: `http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=google`
7. Copy Client ID and Client Secret
8. Update `config/oauth_config.php`:
   ```php
   'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
   'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
   ```

#### 🔵 LinkedIn OAuth Setup
1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/)
2. Create a new app
3. Add product: "Sign In with LinkedIn"
4. Add Authorized Redirect URL: `http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=linkedin`
5. Copy Client ID and Client Secret
6. Update `config/oauth_config.php`:
   ```php
   'client_id' => 'YOUR_LINKEDIN_CLIENT_ID',
   'client_secret' => 'YOUR_LINKEDIN_CLIENT_SECRET',
   ```

#### 🔵 Facebook OAuth Setup
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app
3. Add "Facebook Login" product
4. Add Valid OAuth Redirect URI: `http://localhost/PROPLEDGER/auth/oauth_callback.php?provider=facebook`
5. Copy App ID and App Secret
6. Update `config/oauth_config.php`:
   ```php
   'client_id' => 'YOUR_FACEBOOK_APP_ID',
   'client_secret' => 'YOUR_FACEBOOK_APP_SECRET',
   ```

### 3. Test OAuth Configuration
Visit: `http://localhost/PROPLEDGER/config/oauth_config.php` to check configuration status.

## 🚀 Features Added

### Login Page (`html/login.html`)
- ✅ OAuth login buttons for Google, LinkedIn, Facebook
- ✅ Traditional email/password login (maintained)
- ✅ Seamless integration with existing authentication

### Signup Page (`html/signup.html`)
- ✅ OAuth signup buttons for quick registration
- ✅ Traditional signup form (maintained)
- ✅ Automatic account creation from OAuth data

### Authentication System
- ✅ Hybrid authentication (OAuth + traditional)
- ✅ Secure state management for OAuth flows
- ✅ User data normalization across providers
- ✅ Session management for all auth types

## 🔧 Technical Implementation

### Database Changes
```sql
-- New columns added to users table
ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) NULL;
ALTER TABLE users ADD COLUMN oauth_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN profile_picture_url VARCHAR(500) NULL;
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL;

-- New table for OAuth security
CREATE TABLE oauth_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_token VARCHAR(255) NOT NULL UNIQUE,
    provider VARCHAR(50) NOT NULL,
    redirect_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);
```

### New Files Created
- `config/oauth_config.php` - OAuth provider configurations
- `auth/oauth_login.php` - OAuth login initiation
- `auth/oauth_callback.php` - OAuth callback handler
- `php/update_oauth_schema.php` - Database schema updater

### Updated Files
- `html/login.html` - Added OAuth login buttons
- `html/signup.html` - Added OAuth signup buttons
- `js/auth.js` - Enhanced for OAuth compatibility

## 🎯 User Experience

### OAuth Flow
1. User clicks "Continue with Google/LinkedIn/Facebook"
2. Redirected to OAuth provider
3. User authorizes PROPLEDGER access
4. Redirected back with user data
5. Account created/updated automatically
6. User logged in immediately

### Benefits
- ✅ **No password required** for OAuth users
- ✅ **Instant registration** with verified email
- ✅ **Professional profiles** from LinkedIn
- ✅ **Trusted authentication** via established providers
- ✅ **Reduced friction** for user onboarding

## 🔒 Security Features

- **State validation** prevents CSRF attacks
- **Secure token exchange** with OAuth providers
- **Session management** consistent with existing system
- **Email verification** automatic for OAuth users
- **Profile data validation** and normalization

## 🧪 Testing

### Test OAuth Integration
1. Update database schema
2. Configure at least one OAuth provider
3. Test login flow: `http://localhost/PROPLEDGER/html/login.html`
4. Test signup flow: `http://localhost/PROPLEDGER/html/signup.html`
5. Verify user data in database
6. Test logout functionality

### Debug Tools
- Check configuration: `config/oauth_config.php`
- Test authentication: `test_auth_flow.html`
- Monitor database: Check `users` and `oauth_states` tables

## 🚀 Production Deployment

### Before Going Live
1. ✅ Update OAuth redirect URIs to production domain
2. ✅ Secure OAuth client secrets (environment variables)
3. ✅ Enable HTTPS for OAuth callbacks
4. ✅ Test all OAuth providers thoroughly
5. ✅ Monitor OAuth state table cleanup

### Environment Variables (Recommended)
```php
// Use environment variables in production
'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
```

## 📞 Support

If you encounter issues:
1. Check browser console for JavaScript errors
2. Verify OAuth provider configuration
3. Check database connection and schema
4. Test with debug tools provided
5. Review OAuth provider documentation

---

**PROPLEDGER OAuth Integration Complete! 🎉**

Your users can now enjoy seamless authentication with their preferred social accounts while maintaining the security and functionality of traditional authentication.
