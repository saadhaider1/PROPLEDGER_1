// Universal authentication system for PROPLEDGER
class AuthManager {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    async init() {
        await this.checkAuth();
        this.updateNavigation();
        this.checkPageAccess();
    }

    async checkAuth() {
        try {
            // Check localStorage first for immediate response
            const localUser = localStorage.getItem('propledger_user');
            if (localUser) {
                this.currentUser = JSON.parse(localUser);
                this.updateUserInfo(this.currentUser);
                return true;
            }

            // Fallback to server check
            const authPath = window.location.pathname.includes('/html/') ? '../auth/check_session.php' : 'auth/check_session.php';
            const response = await fetch(authPath);
            const data = await response.json();
            
            if (data.success) {
                this.currentUser = data.user;
                this.updateUserInfo(data.user);
                return true;
            } else {
                this.currentUser = null;
                this.clearUserInfo();
                return false;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            this.currentUser = null;
            this.clearUserInfo();
            return false;
        }
    }

    updateUserInfo(userInfo) {
        // Store user info in multiple formats for compatibility
        localStorage.setItem('propledger_user', JSON.stringify(userInfo));
        localStorage.setItem('userInfo', JSON.stringify(userInfo));
        sessionStorage.setItem('userInfo', JSON.stringify(userInfo));
    }

    clearUserInfo() {
        // Clear all stored user information
        localStorage.removeItem('propledger_user');
        localStorage.removeItem('userInfo');
        sessionStorage.removeItem('userInfo');
    }

    getUserInfo() {
        // Try to get user info from multiple sources
        if (this.currentUser) return this.currentUser;
        
        const localUser = localStorage.getItem('userInfo') || localStorage.getItem('propledger_user');
        if (localUser) return JSON.parse(localUser);
        
        const sessionUser = sessionStorage.getItem('userInfo');
        if (sessionUser) return JSON.parse(sessionUser);
        
        return null;
    }

    updateNavigation() {
        const authButtons = document.querySelector('.auth-buttons');
        const userWelcome = document.getElementById('userWelcome');
        const tokenBar = document.getElementById('tokenBar');
        
        if (!authButtons) return;

        if (this.currentUser) {
            // User is logged in - show user info and logout
            const userName = this.currentUser.full_name || this.currentUser.name || 'User';
            const userType = this.currentUser.type || this.currentUser.user_type || 'investor';
            
            // Different navigation based on user type
            if (userType === 'agent') {
                authButtons.innerHTML = `
                    <span class="user-welcome" style="color: var(--blockchain-blue); font-weight: 600; margin: 0 1rem;">Agent: ${userName}</span>
                    <button onclick="authManager.logout()" class="btn btn-secondary">Logout</button>
                `;
                
                // Hide token bar for agents
                if (tokenBar) {
                    tokenBar.style.display = 'none';
                }
            } else {
                authButtons.innerHTML = `
                    <button onclick="openTokenPurchaseModal()" class="btn btn-success">💳 Buy Tokens</button>
                    <span class="user-welcome" style="color: var(--blockchain-blue); font-weight: 600; margin: 0 1rem;">Welcome, ${userName}!</span>
                    <button onclick="authManager.logout()" class="btn btn-secondary">Logout</button>
                `;
                
                // Show token bar when logged in as investor
                if (tokenBar) {
                    tokenBar.style.display = 'flex';
                    // Update token display with saved balance or default to 0
                    const savedBalance = localStorage.getItem('propledger_token_balance');
                    const balance = savedBalance ? parseInt(savedBalance) : 0;
                    const tokenCountEl = document.getElementById('tokenCount');
                    const tokenValueEl = document.getElementById('tokenValue');
                    if (tokenCountEl) tokenCountEl.textContent = balance + ' Tokens';
                    if (tokenValueEl) tokenValueEl.textContent = '₨ ' + (balance * 1000).toLocaleString();
                }
            }
            
            // Update welcome message if exists
            if (userWelcome) {
                userWelcome.textContent = `Welcome, ${userName}!`;
            }
        } else {
            // User is not logged in - show login/signup buttons
            authButtons.innerHTML = `
                <button onclick="openTokenPurchaseModal()" class="btn btn-success">💳 Buy Tokens</button>
                <a href="login.html" class="btn btn-secondary">Login</a>
                <a href="signup.html" class="btn btn-primary">Sign Up</a>
            `;
            
            // Hide token bar when not logged in
            if (tokenBar) {
                tokenBar.style.display = 'none';
            }
        }
    }

    async logout() {
        try {
            const logoutPath = window.location.pathname.includes('/html/') ? '../auth/logout_handler.php' : 'auth/logout_handler.php';
            const response = await fetch(logoutPath, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.currentUser = null;
                this.clearUserInfo();
                localStorage.removeItem('propledger_token_balance');
                alert('Logged out successfully!');
                window.location.href = window.location.pathname.includes('/html/') ? 'index.html' : 'html/index.html';
            }
        } catch (error) {
            console.error('Logout failed:', error);
            // Force redirect anyway
            this.currentUser = null;
            this.clearUserInfo();
            localStorage.removeItem('propledger_token_balance');
            window.location.href = window.location.pathname.includes('/html/') ? 'index.html' : 'html/index.html';
        }
    }

    isLoggedIn() {
        return this.currentUser !== null;
    }

    getUser() {
        return this.currentUser;
    }

    requireAuth() {
        if (!this.isLoggedIn()) {
            alert('Please log in to access this feature.');
            window.location.href = 'login.html';
            return false;
        }
        return true;
    }

    // Check if current user has access to specific features
    hasAccess(feature) {
        if (!this.isLoggedIn()) return false;
        
        const userType = this.currentUser.type || this.currentUser.user_type || 'investor';
        
        // Define agent access restrictions
        const agentAllowedFeatures = ['properties', 'investments', 'crowdfunding', 'support', 'about'];
        const agentRestrictedFeatures = ['dashboard', 'token-purchase', 'managers'];
        
        if (userType === 'agent') {
            return agentAllowedFeatures.includes(feature);
        }
        
        // Investors have full access
        return true;
    }

    // Redirect based on user type and access
    checkPageAccess(currentPage) {
        if (!this.isLoggedIn()) return;
        
        const userType = this.currentUser.type || this.currentUser.user_type || 'investor';
        
        // Redirect agents away from restricted pages (but not if already on agent-dashboard)
        if (userType === 'agent' && !window.location.pathname.includes('agent-dashboard.html')) {
            const restrictedPages = ['dashboard.html', 'token-purchase-demo.html'];
            if (restrictedPages.some(page => window.location.pathname.includes(page))) {
                window.location.href = 'agent-dashboard.html';
            }
        }
    }
}

// Global auth manager instance
let authManager;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    authManager = new AuthManager();
});

// Helper function for pages that require authentication
function requireAuth() {
    if (authManager && !authManager.requireAuth()) {
        return false;
    }
    return true;
}
