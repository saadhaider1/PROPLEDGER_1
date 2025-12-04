# 🚀 PROPLEDGER Quick Deployment Guide

## ⚠️ IMPORTANT: Read This First!

Your "Page not found" error happens because:
1. **Your app is PHP-based** - Netlify only hosts static files (HTML/CSS/JS)
2. **Backend won't work** - Login, database, messaging require PHP server
3. **You're deploying a DEMO** - Only the frontend UI will work

---

## 🎯 Method 1: Netlify Drag & Drop (EASIEST - 2 Minutes)

### Step 1: Prepare the HTML folder
The `html` folder contains all your frontend files. This is what you'll deploy.

### Step 2: Deploy to Netlify
1. Go to: **https://app.netlify.com/drop**
2. **Drag and drop** the `html` folder (the entire folder, not individual files)
3. Wait for deployment (30-60 seconds)
4. Get your live URL: `https://random-name-123.netlify.app`

### Step 3: Fix the paths issue
After deployment, if you see "Page not found":
- The `netlify.toml` file should fix this automatically
- If not, go to Netlify dashboard → Site settings → Build & deploy → Edit settings
- Set **Publish directory** to: `html`

**That's it! Your demo will be live.**

---

## 🎯 Method 2: Netlify CLI (More Control)

### Step 1: Install Netlify CLI
```powershell
npm install -g netlify-cli
```

### Step 2: Login to Netlify
```powershell
netlify login
```
This opens a browser window - click "Authorize"

### Step 3: Deploy from your project folder
```powershell
cd c:\xampp\htdocs\PROPLEDGER
netlify deploy
```

### Step 4: Answer the prompts
- **Create & configure a new site?** → Yes
- **Team:** → Choose your team
- **Site name:** → `propledger-demo` (or any unique name)
- **Publish directory:** → `html`

### Step 5: Deploy to production
```powershell
netlify deploy --prod
```

**Your site will be live at:** `https://propledger-demo.netlify.app`

---

## 🎯 Method 3: GitHub + Netlify (Automatic Deployments)

### Step 1: Create GitHub repository
```powershell
cd c:\xampp\htdocs\PROPLEDGER
git init
git add .
git commit -m "Initial commit"
```

### Step 2: Push to GitHub
```powershell
# Create a new repo on GitHub first, then:
git remote add origin https://github.com/YOUR_USERNAME/propledger.git
git branch -M main
git push -u origin main
```

### Step 3: Connect to Netlify
1. Go to: **https://app.netlify.com**
2. Click **"Add new site"** → **"Import an existing project"**
3. Choose **GitHub**
4. Select your **propledger** repository
5. Configure build settings:
   - **Build command:** (leave empty)
   - **Publish directory:** `html`
6. Click **Deploy site**

**Benefit:** Every time you push to GitHub, Netlify auto-deploys!

---

## 🎯 Method 4: Full-Stack Deployment (For Working Backend)

If you want **login, database, and messaging to work**, you need PHP hosting:

### Option A: Heroku (Free Tier Available)

#### Step 1: Install Heroku CLI
Download from: https://devcenter.heroku.com/articles/heroku-cli

#### Step 2: Create required files

**Create `Procfile` in root:**
```
web: vendor/bin/heroku-php-apache2 html/
```

**Create `composer.json` in root:**
```json
{
  "require": {
    "php": "^7.4 || ^8.0"
  }
}
```

#### Step 3: Deploy to Heroku
```powershell
cd c:\xampp\htdocs\PROPLEDGER
git init
heroku login
heroku create propledger-app
git add .
git commit -m "Deploy to Heroku"
git push heroku main
```

#### Step 4: Add MySQL Database
```powershell
heroku addons:create cleardb:ignite
heroku config:get CLEARDB_DATABASE_URL
```

#### Step 5: Update database config
Copy the database URL and update `config/database.php` with the credentials.

**Your app will be live at:** `https://propledger-app.herokuapp.com`

---

### Option B: Railway.app (Modern & Easy)

1. Go to: **https://railway.app**
2. Sign up with GitHub
3. Click **"New Project"** → **"Deploy from GitHub repo"**
4. Select your repository
5. Railway auto-detects PHP
6. Add MySQL database from Railway dashboard
7. Update `config/database.php` with Railway database credentials

**Your app will be live at:** `https://propledger.up.railway.app`

---

## 🔧 Troubleshooting

### "Page not found" after deployment
**Solution:**
1. Check that `netlify.toml` is in the root directory
2. Verify publish directory is set to `html`
3. Make sure `html/index.html` exists

### CSS/JS not loading
**Solution:**
1. Check browser console for 404 errors
2. Verify paths in HTML use relative paths (`../css/style.css`)
3. Make sure `css` and `js` folders are inside `html` folder

### Login/Database not working on Netlify
**Expected behavior!** Netlify doesn't support PHP. Use Heroku or Railway for full functionality.

---

## 📊 Deployment Comparison

| Method | Time | Backend Works? | Auto-Deploy? | Cost |
|--------|------|----------------|--------------|------|
| Netlify Drag & Drop | 2 min | ❌ No | ❌ No | Free |
| Netlify CLI | 5 min | ❌ No | ❌ No | Free |
| GitHub + Netlify | 10 min | ❌ No | ✅ Yes | Free |
| Heroku | 15 min | ✅ Yes | ✅ Yes | Free tier |
| Railway | 10 min | ✅ Yes | ✅ Yes | Free tier |

---

## 🎯 Recommended Path

**For Quick Demo (UI Only):**
→ Use **Method 1: Netlify Drag & Drop**

**For Full Working App:**
→ Use **Method 4: Heroku or Railway**

---

## 📝 Next Steps After Deployment

1. **Test the deployed site**
2. **Add a custom domain** (optional)
3. **Enable HTTPS** (automatic on Netlify/Heroku)
4. **Monitor performance** in dashboard

---

## ❓ Need Help?

If you're still stuck:
1. Check deployment logs in Netlify/Heroku dashboard
2. Verify all files are uploaded correctly
3. Test locally first: `http://localhost/PROPLEDGER/html/index.html`

**Common issue:** If you see 404 errors, the publish directory is wrong. Set it to `html`.
