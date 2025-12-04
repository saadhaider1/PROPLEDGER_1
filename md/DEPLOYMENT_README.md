# PROPLEDGER Deployment Guide

## ⚠️ Important: Backend Dependency Issue

Your PROPLEDGER application is a **full-stack PHP application** that requires:
- PHP server for backend logic
- MySQL database for data storage
- Server-side session management
- API endpoints for authentication and messaging

**Netlify and similar static hosting platforms CANNOT run PHP code.**

---

## Current Deployment Options

### Option 1: Static Demo (Frontend Only) - CURRENT SETUP ✅

**What works:**
- ✅ Browse all pages (Home, Investments, Properties, etc.)
- ✅ View UI and design
- ✅ See property listings and crowdfunding campaigns
- ✅ Navigate between pages

**What DOESN'T work:**
- ❌ User login/registration (requires PHP backend)
- ❌ Token purchases (requires database)
- ❌ Messaging system (requires PHP + MySQL)
- ❌ Portfolio management (requires authentication)
- ❌ Any database operations

**How to deploy:**
```bash
# The netlify.toml is already configured
# Just deploy the project and it will serve files from /html/ folder
```

---

### Option 2: Separate Frontend + Backend (Recommended) ✅

**Architecture:**
1. **Frontend (Netlify):** Deploy HTML/CSS/JS as static site
2. **Backend (PHP Hosting):** Deploy PHP files to a PHP-compatible host
3. **Update API calls:** Modify JS to call remote backend API

**Steps:**

#### A. Deploy Backend to PHP Host
Choose one:
- **Heroku** (free tier available, supports PHP)
- **Railway.app** (modern, supports PHP + MySQL)
- **Traditional hosting** (Hostinger, Bluehost, etc.)

#### B. Update Frontend API Endpoints
In your JavaScript files, change:
```javascript
// FROM (local):
fetch('../auth/login_handler.php', ...)

// TO (remote backend):
fetch('https://your-backend.herokuapp.com/auth/login_handler.php', ...)
```

#### C. Deploy Frontend to Netlify
```bash
# Already configured with netlify.toml
# Publish directory: html
```

---

### Option 3: Full-Stack PHP Hosting (Easiest) ✅

Deploy the **entire application** (frontend + backend) to a PHP hosting platform:

**Recommended Platforms:**
1. **Heroku** - Free tier, supports PHP + MySQL addon
2. **Railway.app** - Modern, easy deployment
3. **Traditional Hosting** - Hostinger, Bluehost, SiteGround

**Advantages:**
- ✅ No code changes needed
- ✅ Everything works as-is
- ✅ Database included
- ✅ PHP execution supported

**How to deploy to Heroku:**
```bash
# 1. Install Heroku CLI
# 2. Create Procfile in root:
echo "web: vendor/bin/heroku-php-apache2 html/" > Procfile

# 3. Create composer.json:
echo '{}' > composer.json

# 4. Deploy:
git init
heroku create your-propledger-app
git add .
git commit -m "Initial deployment"
git push heroku main

# 5. Add MySQL addon:
heroku addons:create cleardb:ignite
```

---

## Current Configuration Files

### `netlify.toml`
- Configured to serve files from `/html/` directory
- Redirects all routes to index.html
- Security headers enabled
- Cache optimization for static assets

### `.gitignore`
- Excludes PHP backend files (for static deployment)
- Excludes database and config files
- Excludes test and debug files

---

## Recommended Next Steps

**For Quick Demo:**
1. Deploy to Netlify as-is (frontend only)
2. Add banner: "Demo Mode - Backend features disabled"

**For Full Functionality:**
1. Choose Option 3 (Full-Stack PHP Hosting)
2. Deploy to Heroku or Railway.app
3. Configure database connection
4. Test all features

---

## Need Help?

**Current Issue:** "Page not found" on deployment
**Cause:** Deployment platform looking for index.html at root, but it's in `/html/` folder
**Solution:** The `netlify.toml` file fixes this by setting `publish = "html"`

If you still see "Page not found":
1. Verify `netlify.toml` is in the root directory
2. Check deployment logs for errors
3. Ensure `/html/index.html` exists
4. Try manual deployment with drag-and-drop of `/html/` folder

---

## Contact & Support

For deployment assistance, check:
- Netlify Docs: https://docs.netlify.com
- Heroku PHP Guide: https://devcenter.heroku.com/articles/getting-started-with-php
- Railway Docs: https://docs.railway.app
