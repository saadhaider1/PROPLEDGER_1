// PROPLEDGER - Main JavaScript Functions

// Property Purchase Function
function purchaseProperty(propertyId, propertyName, price) {
    // Check if user is logged in (in a real app, this would check authentication)
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to purchase properties.');
        window.location.href = 'login.html';
        return;
    }
    
    // Simulate blockchain transaction
    if (confirm(`Purchase ${propertyName} for $${price.toLocaleString()} (${price.toLocaleString()} PROP tokens)?`)) {
        // Show loading state
        showTransactionProcessing();
        
        // Simulate blockchain processing time
        setTimeout(() => {
            hideTransactionProcessing();
            
            // Simulate successful purchase
            alert(`Successfully purchased ${propertyName}! \n\nTransaction Hash: 0x${Math.random().toString(16).substr(2, 64)}\n\nTokens will be transferred to your wallet within 5-10 minutes.`);
            
            // Store purchase in localStorage (in real app, this would go to blockchain/database)
            const purchases = JSON.parse(localStorage.getItem('propledger_purchases') || '[]');
            purchases.push({
                id: propertyId,
                name: propertyName,
                price: price,
                tokens: price,
                purchaseDate: new Date().toISOString(),
                transactionHash: '0x' + Math.random().toString(16).substr(2, 64)
            });
            localStorage.setItem('propledger_purchases', JSON.stringify(purchases));
            
        }, 3000); // 3 second delay to simulate blockchain processing
    }
}

// Crowdfunding Join Function
function joinCrowdfunding(campaignId, target, raised) {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to join crowdfunding campaigns.');
        window.location.href = 'login.html';
        return;
    }
    
    // Get minimum investment amount based on campaign
    const minInvestments = {
        'downtown-complex': 1000,
        'mountain-retreat': 2500,
        'beachside-resort': 5000,
        'tech-hub': 10000,
        'housing-development': 1500,
        'mall-renovation': 2000
    };
    
    const minInvestment = minInvestments[campaignId] || 1000;
    const remaining = target - raised;
    
    // Prompt for investment amount
    const investmentAmount = prompt(`Enter investment amount (Minimum: $${minInvestment.toLocaleString()}, Remaining: $${remaining.toLocaleString()}):`);
    
    if (!investmentAmount || isNaN(investmentAmount)) {
        alert('Please enter a valid investment amount.');
        return;
    }
    
    const amount = parseInt(investmentAmount);
    
    if (amount < minInvestment) {
        alert(`Minimum investment is $${minInvestment.toLocaleString()}.`);
        return;
    }
    
    if (amount > remaining) {
        alert(`Investment amount cannot exceed remaining target of $${remaining.toLocaleString()}.`);
        return;
    }
    
    // Confirm investment
    if (confirm(`Invest $${amount.toLocaleString()} in this crowdfunding campaign?\n\nYou will receive ${amount.toLocaleString()} PROP tokens representing your share.`)) {
        // Show loading state
        showTransactionProcessing();
        
        // Simulate blockchain processing
        setTimeout(() => {
            hideTransactionProcessing();
            
            // Calculate ownership percentage
            const ownershipPercent = ((amount / target) * 100).toFixed(4);
            
            alert(`Successfully invested $${amount.toLocaleString()}!\n\nTransaction Hash: 0x${Math.random().toString(16).substr(2, 64)}\n\nOwnership: ${ownershipPercent}%\nTokens: ${amount.toLocaleString()} PROP\n\nTokens will be transferred to your wallet within 5-10 minutes.`);
            
            // Store investment in localStorage
            const investments = JSON.parse(localStorage.getItem('propledger_investments') || '[]');
            investments.push({
                campaignId: campaignId,
                amount: amount,
                tokens: amount,
                ownershipPercent: ownershipPercent,
                investmentDate: new Date().toISOString(),
                transactionHash: '0x' + Math.random().toString(16).substr(2, 64)
            });
            localStorage.setItem('propledger_investments', JSON.stringify(investments));
            
        }, 3000);
    }
}

// Transaction Processing UI
function showTransactionProcessing() {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.id = 'transaction-modal';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    // Create modal content
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: var(--card-bg);
        padding: 3rem;
        border-radius: 15px;
        text-align: center;
        border: 1px solid rgba(0, 212, 255, 0.3);
        max-width: 400px;
        width: 90%;
    `;
    
    modal.innerHTML = `
        <div style="color: var(--blockchain-blue); font-size: 3rem; margin-bottom: 1rem;">⛓️</div>
        <h3 style="color: var(--text-light); margin-bottom: 1rem;">Processing Transaction</h3>
        <p style="color: var(--text-secondary); margin-bottom: 2rem;">Your transaction is being processed on the blockchain...</p>
        <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid var(--blockchain-blue); border-top: 3px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

function hideTransactionProcessing() {
    const modal = document.getElementById('transaction-modal');
    if (modal) {
        modal.remove();
    }
}

// Loading Screen Functions
function initializeLoadingScreen() {
    const loadingScreen = document.getElementById('loadingScreen');
    const mainContent = document.getElementById('mainContent');
    
    if (loadingScreen && mainContent) {
        // Show loading screen first
        loadingScreen.style.display = 'flex';
        mainContent.style.opacity = '0';
        
        // Array of loading messages for variety
        const loadingMessages = [
            'Initializing Blockchain Network...',
            'Connecting to Smart Contracts...',
            'Verifying CDA Compliance...',
            'Loading Property Database...',
            'Securing Your Connection...'
        ];
        
        // Change loading message every 800ms
        const messageElement = loadingScreen.querySelector('.loading-message');
        let messageIndex = 0;
        
        const messageInterval = setInterval(() => {
            if (messageElement && messageIndex < loadingMessages.length - 1) {
                messageIndex++;
                messageElement.textContent = loadingMessages[messageIndex];
            }
        }, 800);
        
        // Hide loading screen after 3.5 seconds
        setTimeout(() => {
            clearInterval(messageInterval);
            
            // Fade out loading screen
            loadingScreen.classList.add('fade-out');
            
            // Show main content with fade in
            setTimeout(() => {
                loadingScreen.style.display = 'none';
                mainContent.style.opacity = '1';
                mainContent.style.transition = 'opacity 0.8s ease-in-out';
            }, 1000); // Wait for fade out animation
            
        }, 3500); // Total loading time
    }
}

// Smooth scrolling for navigation links
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loading screen only on index page
    if (window.location.pathname.includes('index.html') || window.location.pathname === '/' || window.location.pathname.endsWith('/PROPLEDGER/') || window.location.pathname.endsWith('/html/')) {
        initializeLoadingScreen();
    }
    
    // Ensure token popup is hidden on page load
    const tokenPopup = document.getElementById('tokenPopup');
    if (tokenPopup) {
        tokenPopup.style.display = 'none';
        tokenPopup.classList.remove('visible');
    }
    
    // Add smooth scrolling to navigation links
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add hover effects to module cards
    const moduleCards = document.querySelectorAll('.module-card');
    moduleCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Wallet Connection Functions (for login/signup pages)
function connectWallet(walletType) {
    switch(walletType) {
        case 'metamask':
            return connectMetaMask();
        case 'walletconnect':
            return connectWalletConnect();
        case 'coinbase':
            return connectCoinbase();
        default:
            alert('Wallet type not supported');
    }
}

function connectMetaMask() {
    if (typeof window.ethereum !== 'undefined') {
        return window.ethereum.request({ method: 'eth_requestAccounts' })
            .then(accounts => {
                localStorage.setItem('propledger_user', JSON.stringify({
                    wallet: 'metamask',
                    address: accounts[0],
                    loginTime: new Date().toISOString()
                }));
                return accounts[0];
            })
            .catch(error => {
                console.error('Error connecting to MetaMask:', error);
                throw error;
            });
    } else {
        throw new Error('MetaMask is not installed');
    }
}

function connectWalletConnect() {
    // WalletConnect integration would go here
    alert('WalletConnect integration would be implemented here with the WalletConnect SDK.');
}

function connectCoinbase() {
    // Coinbase Wallet integration would go here
    alert('Coinbase Wallet integration would be implemented here with the Coinbase Wallet SDK.');
}

// Property filtering (for properties page)
function filterProperties() {
    const propertyType = document.getElementById('propertyType')?.value;
    const priceRange = document.getElementById('priceRange')?.value;
    const location = document.getElementById('location')?.value;
    
    // In a real application, this would filter the properties based on the selected criteria
    console.log('Filtering properties:', { propertyType, priceRange, location });
    
    // For demo purposes, just show an alert
    if (propertyType !== 'all' || priceRange !== 'all' || location !== 'all') {
        alert('Filtering properties based on your criteria...');
    }
}

// Add event listeners for filters when page loads
document.addEventListener('DOMContentLoaded', function() {
    const filters = ['propertyType', 'priceRange', 'location'];
    filters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', filterProperties);
        }
    });
});

// ROI Calculator Function
function calculateROI(principal, rate, years) {
    const monthlyRate = rate / 12 / 100;
    const months = years * 12;
    const futureValue = principal * Math.pow(1 + monthlyRate, months);
    return futureValue - principal;
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Generate random transaction hash
function generateTxHash() {
    return '0x' + Math.random().toString(16).substr(2, 64);
}

// Token Popup Functions
function showTokenPopup() {
    const popup = document.getElementById('tokenPopup');
    if (popup && !popup.classList.contains('visible')) {
        popup.style.display = 'block';
        popup.classList.add('visible');
    }
}

function hideTokenPopup() {
    const popup = document.getElementById('tokenPopup');
    if (popup && popup.classList.contains('visible')) {
        popup.style.display = 'none';
        popup.classList.remove('visible');
    }
}

// Check if user is logged in
function isUserLoggedIn() {
    return localStorage.getItem('propledger_user') !== null;
}

// Get user data
function getUserData() {
    const userData = localStorage.getItem('propledger_user');
    return userData ? JSON.parse(userData) : null;
}

// Logout function
function logout() {
    localStorage.removeItem('propledger_user');
    localStorage.removeItem('propledger_purchases');
    localStorage.removeItem('propledger_investments');
    window.location.href = 'index.html';
}

// Token Buying Functions
function buyTokens(packType, price, totalTokens) {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to purchase tokens.');
        window.location.href = 'login.html';
        return;
    }
    
    const packNames = {
        'starter': 'Starter Pack',
        'growth': 'Growth Pack',
        'premium': 'Premium Pack',
        'elite': 'Elite Pack'
    };
    
    const packName = packNames[packType] || 'Token Pack';
    const bonus = totalTokens - price;
    
    if (confirm(`Purchase ${packName}?\n\nPrice: $${price.toLocaleString()}\nBase Tokens: ${price.toLocaleString()} PROP\nBonus Tokens: ${bonus.toLocaleString()} PROP\nTotal Tokens: ${totalTokens.toLocaleString()} PROP`)) {
        showTransactionProcessing();
        
        setTimeout(() => {
            hideTransactionProcessing();
            alert(`Successfully purchased ${packName}!\n\nTransaction Hash: 0x${Math.random().toString(16).substr(2, 64)}\nTokens: ${totalTokens.toLocaleString()} PROP\n\nTokens will be transferred to your wallet within 5-10 minutes.`);
            
            // Store token purchase in localStorage
            const tokenPurchases = JSON.parse(localStorage.getItem('propledger_token_purchases') || '[]');
            tokenPurchases.push({
                packType: packType,
                packName: packName,
                price: price,
                totalTokens: totalTokens,
                bonusTokens: bonus,
                purchaseDate: new Date().toISOString(),
                transactionHash: '0x' + Math.random().toString(16).substr(2, 64)
            });
            localStorage.setItem('propledger_token_purchases', JSON.stringify(tokenPurchases));
            
        }, 3000);
    }
}

function buyCustomTokens() {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to purchase tokens.');
        window.location.href = 'login.html';
        return;
    }
    
    const amount = prompt('Enter the amount you want to invest (Minimum: $100):');
    
    if (!amount || isNaN(amount)) {
        alert('Please enter a valid amount.');
        return;
    }
    
    const investmentAmount = parseInt(amount);
    
    if (investmentAmount < 100) {
        alert('Minimum investment is $100.');
        return;
    }
    
    const tokens = investmentAmount; // 1:1 ratio
    
    if (confirm(`Purchase ${tokens.toLocaleString()} PROP tokens for $${investmentAmount.toLocaleString()}?\n\nRate: 1 PROP = $1 USD`)) {
        showTransactionProcessing();
        
        setTimeout(() => {
            hideTransactionProcessing();
            alert(`Successfully purchased ${tokens.toLocaleString()} PROP tokens!\n\nTransaction Hash: 0x${Math.random().toString(16).substr(2, 64)}\nAmount: $${investmentAmount.toLocaleString()}\nTokens: ${tokens.toLocaleString()} PROP\n\nTokens will be transferred to your wallet within 5-10 minutes.`);
            
            // Store custom token purchase
            const tokenPurchases = JSON.parse(localStorage.getItem('propledger_token_purchases') || '[]');
            tokenPurchases.push({
                packType: 'custom',
                packName: 'Custom Purchase',
                price: investmentAmount,
                totalTokens: tokens,
                bonusTokens: 0,
                purchaseDate: new Date().toISOString(),
                transactionHash: '0x' + Math.random().toString(16).substr(2, 64)
            });
            localStorage.setItem('propledger_token_purchases', JSON.stringify(tokenPurchases));
            
        }, 3000);
    }
}

function showTokenRewards() {
    const modal = document.createElement('div');
    modal.id = 'token-rewards-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    modal.innerHTML = `
        <div style="background: var(--card-bg); padding: 2rem; border-radius: 15px; border: 1px solid rgba(0, 212, 255, 0.3); max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
            <h3 style="color: var(--blockchain-blue); margin-bottom: 1.5rem; text-align: center;">🎁 Token Rewards Program</h3>
            
            <div style="margin-bottom: 2rem;">
                <h4 style="color: var(--text-light); margin-bottom: 1rem;">💰 Referral Program</h4>
                <ul style="color: var(--text-secondary); margin-left: 1.5rem;">
                    <li>Earn 5% of your friend's token purchase as bonus tokens</li>
                    <li>No limit on referrals - invite unlimited friends</li>
                    <li>Bonus tokens credited instantly upon friend's purchase</li>
                </ul>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4 style="color: var(--text-light); margin-bottom: 1rem;">📈 Staking Rewards</h4>
                <ul style="color: var(--text-secondary); margin-left: 1.5rem;">
                    <li>Stake your PROP tokens and earn 8% APY</li>
                    <li>Minimum staking period: 30 days</li>
                    <li>Compound your returns automatically</li>
                    <li>Unstake anytime after minimum period</li>
                </ul>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4 style="color: var(--text-light); margin-bottom: 1rem;">🏆 Loyalty Program</h4>
                <ul style="color: var(--text-secondary); margin-left: 1.5rem;">
                    <li>Bronze: 5% bonus on purchases over $1,000</li>
                    <li>Silver: 7% bonus on purchases over $10,000</li>
                    <li>Gold: 10% bonus on purchases over $50,000</li>
                    <li>Platinum: 15% bonus on purchases over $100,000</li>
                </ul>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h4 style="color: var(--text-light); margin-bottom: 1rem;">🎯 Special Promotions</h4>
                <ul style="color: var(--text-secondary); margin-left: 1.5rem;">
                    <li>Holiday bonuses: Up to 25% extra tokens</li>
                    <li>Limited-time flash sales</li>
                    <li>Early access to new property listings</li>
                    <li>VIP events and networking opportunities</li>
                </ul>
            </div>
            
            <div style="text-align: center;">
                <button onclick="closeTokenRewardsModal()" class="btn btn-primary">Got It!</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeTokenRewardsModal() {
    const modal = document.getElementById('token-rewards-modal');
    if (modal) {
        modal.remove();
    }
}

// Portfolio Manager Functions
function showPortfolioManagers(event) {
    console.log('showPortfolioManagers called'); // Debug log
    
    // Get portfolio management box position instead of just button
    const portfolioBox = document.getElementById('portfolio-management');
    const boxRect = portfolioBox.getBoundingClientRect();
    
    // Position modal directly over the portfolio management box
    const modalWidth = 320;
    let leftPosition = boxRect.left;
    
    // Remove any existing modal first
    const existingModal = document.getElementById('portfolioManagersModal');
    if (existingModal) {
        existingModal.remove();
        console.log('Removed existing modal');
    }
    
    // Create modal dynamically
    const modal = document.createElement('div');
    modal.id = 'portfolioManagersModal';
    modal.className = 'modal';
    modal.style.cssText = `
        display: block !important;
        position: fixed !important;
        top: ${boxRect.top}px !important;
        left: ${leftPosition}px !important;
        width: ${boxRect.width}px !important;
        height: auto !important;
        max-height: 80vh !important;
        z-index: 10000 !important;
        background-color: transparent !important;
        overflow: visible !important;
        padding: 0 !important;
        box-sizing: border-box !important;
        transform: scale(0.95) !important;
        opacity: 0 !important;
        transition: all 0.3s ease-out !important;
    `;
    
    modal.innerHTML = `
        <div class="modal-content" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border-radius: 12px; width: 100%; overflow-y: auto; position: relative; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6); border: 1px solid rgba(0, 205, 172, 0.4);">
            <div class="modal-header" style="padding: 0.8rem 0.8rem 0.4rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #00CDAC; font-size: 1rem; font-weight: 700; margin: 0;">Portfolio Managers</h2>
                <span class="close" onclick="closePortfolioManagers()" style="font-size: 1.2rem; font-weight: bold; color: #ffffff; cursor: pointer; padding: 0.2rem;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 0.8rem;">
                <p style="text-align: center; margin-bottom: 0.8rem; color: #b0b0b0; font-size: 0.7rem;">
                    Connect with our expert portfolio managers
                </p>
                
                <div class="agents-grid" style="display: grid; grid-template-columns: 1fr; gap: 0.8rem;">
                    
                    <!-- Agent 1 -->
                    <div class="agent-card" style="background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%); border-radius: 8px; padding: 0.8rem; border: 1px solid rgba(0, 205, 172, 0.3); box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);">
                        <div class="agent-header" style="display: flex; align-items: center; margin-bottom: 0.8rem;">
                            <div class="agent-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-right: 0.8rem; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: white; font-weight: bold;">AH</div>
                            <div>
                                <h3 style="margin: 0; color: #ffffff; font-size: 0.9rem; font-weight: 600;">Ahmed Hassan</h3>
                                <p style="color: #00CDAC; font-weight: 600; margin: 0; font-size: 0.7rem;">Senior Portfolio Manager</p>
                                <div class="rating" style="color: #fbbf24; font-size: 0.6rem;">★★★★★ (4.9/5)</div>
                            </div>
                        </div>
                        
                        <div class="agent-actions" style="display: flex; gap: 0.3rem;">
                            <button class="btn btn-primary" onclick="startVideoCall('Ahmed Hassan')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: linear-gradient(135deg, #2563EB, #00CDAC); color: white; border: none; border-radius: 6px; cursor: pointer;">📹 Call</button>
                            <button class="btn btn-secondary" onclick="sendMessage('Ahmed Hassan')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: rgba(255, 255, 255, 0.8); color: #2563EB; border: 1px solid #2563EB; border-radius: 6px; cursor: pointer;">💬 Message</button>
                            <button class="btn btn-success" onclick="scheduleMeeting('Ahmed Hassan')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: linear-gradient(135deg, #10B981, #16a34a); color: white; border: none; border-radius: 6px; cursor: pointer;">📅 Meet</button>
                        </div>
                    </div>

                    <!-- Agent 2 -->
                    <div class="agent-card" style="background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%); border-radius: 8px; padding: 0.8rem; border: 1px solid rgba(0, 205, 172, 0.3); box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);">
                        <div class="agent-header" style="display: flex; align-items: center; margin-bottom: 0.8rem;">
                            <div class="agent-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-right: 0.8rem; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: white; font-weight: bold;">SA</div>
                            <div>
                                <h3 style="margin: 0; color: #ffffff; font-size: 0.9rem; font-weight: 600;">Sarah Ali</h3>
                                <p style="color: #00CDAC; font-weight: 600; margin: 0; font-size: 0.7rem;">Investment Strategist</p>
                                <div class="rating" style="color: #fbbf24; font-size: 0.6rem;">★★★★★ (4.8/5)</div>
                            </div>
                        </div>
                        
                        <div class="agent-actions" style="display: flex; gap: 0.3rem;">
                            <button class="btn btn-primary" onclick="startVideoCall('Sarah Ali')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: linear-gradient(135deg, #2563EB, #00CDAC); color: white; border: none; border-radius: 6px; cursor: pointer;">📹 Call</button>
                            <button class="btn btn-secondary" onclick="sendMessage('Sarah Ali')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: rgba(255, 255, 255, 0.8); color: #2563EB; border: 1px solid #2563EB; border-radius: 6px; cursor: pointer;">💬 Message</button>
                            <button class="btn btn-success" onclick="scheduleMeeting('Sarah Ali')" style="flex: 1; padding: 0.3rem; font-size: 0.6rem; background: linear-gradient(135deg, #10B981, #16a34a); color: white; border: none; border-radius: 6px; cursor: pointer;">📅 Meet</button>
                        </div>
                    </div>

                    <!-- Agent 3 -->
                    <div class="agent-card" style="background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%); border-radius: 15px; padding: 2rem; border: 1px solid rgba(0, 205, 172, 0.3); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);">
                        <div class="agent-header" style="text-align: center; margin-bottom: 1.5rem;">
                            <div class="agent-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">MK</div>
                            <h3 style="margin: 0; color: #ffffff; font-size: 1.3rem; font-weight: 600;">Muhammad Khan</h3>
                            <p style="color: #00CDAC; font-weight: 600; margin: 0.5rem 0; font-size: 1rem;">Blockchain Specialist</p>
                            <div class="rating" style="color: #fbbf24; font-size: 0.9rem;">★★★★☆ (4.7/5)</div>
                        </div>
                        
                        <div class="agent-info" style="margin-bottom: 1.5rem;">
                            <p style="font-size: 0.9rem; color: #b0b0b0; line-height: 1.5;">
                                Blockchain technology expert specializing in smart contracts and DeFi real estate solutions. Helps clients navigate complex tokenized investments.
                            </p>
                            <div class="agent-stats" style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.8rem; color: #888888;">
                                <span><strong>Experience:</strong> 5 years</span>
                                <span><strong>Clients:</strong> 95+</span>
                                <span><strong>ROI Avg:</strong> 13.5%</span>
                            </div>
                        </div>
                        
                        <div class="agent-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button class="btn btn-primary" onclick="startVideoCall('Muhammad Khan')" style="flex: 1; padding: 0.7rem; font-size: 0.9rem; background: linear-gradient(135deg, #2563EB, #00CDAC); color: white; border: none; border-radius: 12px; cursor: pointer;">📹 Video Call</button>
                            <button class="btn btn-secondary" onclick="sendMessage('Muhammad Khan')" style="flex: 1; padding: 0.7rem; font-size: 0.9rem; background: rgba(255, 255, 255, 0.8); color: #2563EB; border: 2px solid #2563EB; border-radius: 12px; cursor: pointer;">💬 Message</button>
                            <button class="btn btn-success" onclick="scheduleMeeting('Muhammad Khan')" style="width: 100%; padding: 0.7rem; font-size: 0.9rem; margin-top: 0.5rem; background: linear-gradient(135deg, #10B981, #16a34a); color: white; border: none; border-radius: 12px; cursor: pointer;">📅 Schedule Meeting</button>
                        </div>
                    </div>

                    <!-- Agent 4 -->
                    <div class="agent-card" style="background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%); border-radius: 15px; padding: 2rem; border: 1px solid rgba(0, 205, 172, 0.3); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);">
                        <div class="agent-header" style="text-align: center; margin-bottom: 1.5rem;">
                            <div class="agent-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">FN</div>
                            <h3 style="margin: 0; color: #ffffff; font-size: 1.3rem; font-weight: 600;">Fatima Noor</h3>
                            <p style="color: #00CDAC; font-weight: 600; margin: 0.5rem 0; font-size: 1rem;">Commercial Property Expert</p>
                            <div class="rating" style="color: #fbbf24; font-size: 0.9rem;">★★★★★ (4.9/5)</div>
                        </div>
                        
                        <div class="agent-info" style="margin-bottom: 1.5rem;">
                            <p style="font-size: 0.9rem; color: #b0b0b0; line-height: 1.5;">
                                Specializes in commercial real estate investments including office buildings, retail spaces, and industrial properties with exceptional market insights.
                            </p>
                            <div class="agent-stats" style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.8rem; color: #888888;">
                                <span><strong>Experience:</strong> 10 years</span>
                                <span><strong>Clients:</strong> 200+</span>
                                <span><strong>ROI Avg:</strong> 15.3%</span>
                            </div>
                        </div>
                        
                        <div class="agent-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button class="btn btn-primary" onclick="startVideoCall('Fatima Noor')" style="flex: 1; padding: 0.7rem; font-size: 0.9rem; background: linear-gradient(135deg, #2563EB, #00CDAC); color: white; border: none; border-radius: 12px; cursor: pointer;">📹 Video Call</button>
                            <button class="btn btn-secondary" onclick="sendMessage('Fatima Noor')" style="flex: 1; padding: 0.7rem; font-size: 0.9rem; background: rgba(255, 255, 255, 0.8); color: #2563EB; border: 2px solid #2563EB; border-radius: 12px; cursor: pointer;">💬 Message</button>
                            <button class="btn btn-success" onclick="scheduleMeeting('Fatima Noor')" style="width: 100%; padding: 0.7rem; font-size: 0.9rem; margin-top: 0.5rem; background: linear-gradient(135deg, #10B981, #16a34a); color: white; border: none; border-radius: 12px; cursor: pointer;">📅 Schedule Meeting</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Trigger slide-in animation
    setTimeout(() => {
        modal.style.transform = 'scale(1)';
        modal.style.opacity = '1';
    }, 10);
    
    console.log('Modal created and should be visible now');
}

function closePortfolioManagers() {
    const modal = document.getElementById('portfolioManagersModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}

function startVideoCall(agentName) {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to start video calls with portfolio managers.');
        window.location.href = 'login.html';
        return;
    }
    
    // Simulate video call initiation
    const callId = 'call_' + Math.random().toString(36).substr(2, 9);
    
    if (confirm(`Start video call with ${agentName}?\n\nThis will open a secure video conference room.`)) {
        // Show video call processing
        showVideoCallModal(agentName, callId);
        
        // Store call in localStorage
        const calls = JSON.parse(localStorage.getItem('propledger_calls') || '[]');
        calls.push({
            agentName: agentName,
            callId: callId,
            type: 'video',
            startTime: new Date().toISOString(),
            status: 'initiated'
        });
        localStorage.setItem('propledger_calls', JSON.stringify(calls));
    }
}

function sendMessage(agentName) {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to send messages to portfolio managers.');
        window.location.href = 'login.html';
        return;
    }
    
    const message = prompt(`Send a message to ${agentName}:`);
    
    if (message && message.trim()) {
        // Simulate message sending
        showTransactionProcessing();
        
        setTimeout(() => {
            hideTransactionProcessing();
            alert(`Message sent to ${agentName}!\n\nThey will respond within 24 hours. You'll receive a notification when they reply.`);
            
            // Store message in localStorage
            const messages = JSON.parse(localStorage.getItem('propledger_messages') || '[]');
            messages.push({
                agentName: agentName,
                message: message.trim(),
                timestamp: new Date().toISOString(),
                status: 'sent',
                messageId: 'msg_' + Math.random().toString(36).substr(2, 9)
            });
            localStorage.setItem('propledger_messages', JSON.stringify(messages));
            
        }, 1500);
    }
}

function scheduleMeeting(agentName) {
    // Check if user is logged in
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to schedule meetings with portfolio managers.');
        window.location.href = 'login.html';
        return;
    }
    
    // Show meeting scheduling modal
    showMeetingScheduler(agentName);
}

function showVideoCallModal(agentName, callId) {
    const modal = document.createElement('div');
    modal.id = 'video-call-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    modal.innerHTML = `
        <div style="background: var(--card-bg); padding: 2rem; border-radius: 15px; border: 1px solid rgba(0, 212, 255, 0.3); max-width: 800px; width: 90%; text-align: center;">
            <h3 style="color: var(--blockchain-blue); margin-bottom: 1.5rem;">📹 Video Call with ${agentName}</h3>
            
            <div style="background: #1a1a1a; height: 400px; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; justify-content: center; position: relative;">
                <div style="color: var(--text-secondary); font-size: 1.2rem;">
                    🎥 Video call would be displayed here<br>
                    <small>Call ID: ${callId}</small>
                </div>
                <div style="position: absolute; top: 10px; right: 10px; background: rgba(255, 0, 0, 0.8); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                    🔴 LIVE
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem;">
                <button class="btn btn-secondary" onclick="toggleMute()" style="padding: 0.8rem 1.5rem;">🎤 Mute</button>
                <button class="btn btn-secondary" onclick="toggleVideo()" style="padding: 0.8rem 1.5rem;">📹 Video</button>
                <button class="btn btn-secondary" onclick="shareScreen()" style="padding: 0.8rem 1.5rem;">🖥️ Share</button>
                <button class="btn btn-danger" onclick="endVideoCall()" style="padding: 0.8rem 1.5rem;">📞 End Call</button>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                This is a demo of the video call interface. In a real implementation, this would connect to a video conferencing service.
            </p>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function showMeetingScheduler(agentName) {
    const modal = document.createElement('div');
    modal.id = 'meeting-scheduler-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    modal.innerHTML = `
        <div style="background: var(--card-bg); padding: 2rem; border-radius: 15px; border: 1px solid rgba(0, 212, 255, 0.3); max-width: 500px; width: 90%;">
            <h3 style="color: var(--blockchain-blue); margin-bottom: 1.5rem; text-align: center;">📅 Schedule Meeting with ${agentName}</h3>
            
            <form id="meetingForm" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="color: var(--text-light); display: block; margin-bottom: 0.5rem;">Meeting Date:</label>
                    <input type="date" id="meetingDate" min="${minDate}" required style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-light);">
                </div>
                
                <div>
                    <label style="color: var(--text-light); display: block; margin-bottom: 0.5rem;">Meeting Time:</label>
                    <select id="meetingTime" required style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-light);">
                        <option value="">Select Time</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="17:00">5:00 PM</option>
                    </select>
                </div>
                
                <div>
                    <label style="color: var(--text-light); display: block; margin-bottom: 0.5rem;">Meeting Type:</label>
                    <select id="meetingType" required style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-light);">
                        <option value="">Select Type</option>
                        <option value="consultation">Investment Consultation</option>
                        <option value="portfolio-review">Portfolio Review</option>
                        <option value="strategy-planning">Strategy Planning</option>
                        <option value="risk-assessment">Risk Assessment</option>
                    </select>
                </div>
                
                <div>
                    <label style="color: var(--text-light); display: block; margin-bottom: 0.5rem;">Additional Notes (Optional):</label>
                    <textarea id="meetingNotes" rows="3" placeholder="Any specific topics you'd like to discuss..." style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-light); resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="button" onclick="closeMeetingScheduler()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Schedule Meeting</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add form submit handler
    document.getElementById('meetingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const date = document.getElementById('meetingDate').value;
        const time = document.getElementById('meetingTime').value;
        const type = document.getElementById('meetingType').value;
        const notes = document.getElementById('meetingNotes').value;
        
        if (date && time && type) {
            scheduleMeetingConfirm(agentName, date, time, type, notes);
        }
    });
}

function scheduleMeetingConfirm(agentName, date, time, type, notes) {
    const meetingId = 'meeting_' + Math.random().toString(36).substr(2, 9);
    
    // Close scheduler modal
    closeMeetingScheduler();
    
    // Show confirmation
    showTransactionProcessing();
    
    setTimeout(() => {
        hideTransactionProcessing();
        
        const formattedDate = new Date(date).toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const timeFormatted = new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        
        alert(`Meeting scheduled successfully!\n\nAgent: ${agentName}\nDate: ${formattedDate}\nTime: ${timeFormatted}\nType: ${type}\nMeeting ID: ${meetingId}\n\nYou will receive a confirmation email and calendar invite shortly.`);
        
        // Store meeting in localStorage
        const meetings = JSON.parse(localStorage.getItem('propledger_meetings') || '[]');
        meetings.push({
            agentName: agentName,
            date: date,
            time: time,
            type: type,
            notes: notes,
            meetingId: meetingId,
            status: 'scheduled',
            scheduledAt: new Date().toISOString()
        });
        localStorage.setItem('propledger_meetings', JSON.stringify(meetings));
        
    }, 2000);
}

function closeMeetingScheduler() {
    const modal = document.getElementById('meeting-scheduler-modal');
    if (modal) {
        modal.remove();
    }
}

function endVideoCall() {
    const modal = document.getElementById('video-call-modal');
    if (modal) {
        modal.remove();
    }
    alert('Video call ended. Thank you for using PROPLEDGER video conferencing!');
}

function toggleMute() {
    alert('Microphone toggled (demo)');
}

function toggleVideo() {
    alert('Video toggled (demo)');
}

function shareScreen() {
    alert('Screen sharing initiated (demo)');
}
