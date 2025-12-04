
function openTokenPurchaseModal() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'paymentModal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>💳 Buy PROP Tokens</h2>
                <button onclick="closeModal('paymentModal')" class="close-button">×</button>
            </div>
            <div class="modal-body">
                <!-- Token Purchase Summary -->
                <div class="token-summary">
                    <div class="token-rate">
                        <span class="rate-label">Exchange Rate:</span>
                        <span class="rate-value">1 PROP = ₨ 1,000 PKR</span>
                    </div>
                </div>

                <!-- Token Amount Selection -->
                <div class="form-group">
                    <label for="tokenAmount">🪙 Number of Tokens to Buy</label>
                    <input type="number" id="tokenAmount" min="1" value="10" oninput="updateTotal()" class="token-input">
                    <div class="token-suggestions">
                        <button type="button" onclick="setTokenAmount(10)" class="suggestion-btn">10 Tokens</button>
                        <button type="button" onclick="setTokenAmount(50)" class="suggestion-btn">50 Tokens</button>
                        <button type="button" onclick="setTokenAmount(100)" class="suggestion-btn">100 Tokens</button>
                        <button type="button" onclick="setTokenAmount(500)" class="suggestion-btn">500 Tokens</button>
                    </div>
                </div>

                <!-- Cost Breakdown -->
                <div class="cost-breakdown">
                    <div class="cost-item">
                        <span>Token Cost:</span>
                        <span id="tokenCost">₨ 10,000</span>
                    </div>
                    <div class="cost-item">
                        <span>Platform Fee (2%):</span>
                        <span id="platformFee">₨ 200</span>
                    </div>
                    <div class="cost-item">
                        <span>Transaction Fee:</span>
                        <span id="transactionFee">₨ 50</span>
                    </div>
                    <hr class="cost-divider">
                    <div class="cost-item total">
                        <span><strong>Total Amount:</strong></span>
                        <span id="totalAmount"><strong>₨ 10,250</strong></span>
                    </div>
                </div>

                <hr>
                
                <!-- Payment Method Selection -->
                <h4>💰 Select Payment Method</h4>
                <div class="payment-methods">
                    <div class="payment-method" onclick="selectPaymentMethod('card')">
                        <input type="radio" name="paymentMethod" value="card" id="cardMethod" checked>
                        <label for="cardMethod">
                            <div class="method-icon">💳</div>
                            <div class="method-info">
                                <h5>Credit/Debit Card</h5>
                                <p>Visa, MasterCard, Local Banks</p>
                            </div>
                        </label>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('bank')">
                        <input type="radio" name="paymentMethod" value="bank" id="bankMethod">
                        <label for="bankMethod">
                            <div class="method-icon">🏦</div>
                            <div class="method-info">
                                <h5>Bank Transfer</h5>
                                <p>Direct bank to bank transfer</p>
                            </div>
                        </label>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('mobile')">
                        <input type="radio" name="paymentMethod" value="mobile" id="mobileMethod">
                        <label for="mobileMethod">
                            <div class="method-icon">📱</div>
                            <div class="method-info">
                                <h5>Mobile Wallet</h5>
                                <p>JazzCash, Easypaisa, UBL Omni</p>
                            </div>
                        </label>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('crypto')">
                        <input type="radio" name="paymentMethod" value="crypto" id="cryptoMethod">
                        <label for="cryptoMethod">
                            <div class="method-icon">₿</div>
                            <div class="method-info">
                                <h5>Cryptocurrency</h5>
                                <p>Bitcoin, Ethereum, USDT</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Payment Form -->
                <div id="paymentForm">
                    <!-- Default Card Payment Form -->
                    <div id="cardPaymentForm" class="payment-form active">
                        <div class="form-group">
                            <label for="cardNumber">💳 Card Number</label>
                            <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="cardExpiry">📅 Expiry Date</label>
                                <input type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label for="cardCVC">🔒 CVC</label>
                                <input type="text" id="cardCVC" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cardHolder">👤 Cardholder Name</label>
                            <input type="text" id="cardHolder" placeholder="Enter full name as on card">
                        </div>
                    </div>

                    <!-- Bank Transfer Form -->
                    <div id="bankPaymentForm" class="payment-form">
                        <div class="bank-info">
                            <h5>🏦 Bank Transfer Details</h5>
                            <div class="bank-details">
                                <p><strong>Account Title:</strong> PROPLEDGER (PVT) LIMITED</p>
                                <p><strong>Account Number:</strong> 0123456789012345</p>
                                <p><strong>Bank:</strong> Habib Bank Limited (HBL)</p>
                                <p><strong>Branch Code:</strong> 1234</p>
                                <p><strong>IBAN:</strong> PK36HABB0012345678901234</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bankReference">📋 Transaction Reference</label>
                            <input type="text" id="bankReference" placeholder="Enter bank reference/transaction ID">
                        </div>
                    </div>

                    <!-- Mobile Wallet Form -->
                    <div id="mobilePaymentForm" class="payment-form">
                        <div class="form-group">
                            <label for="mobileProvider">📱 Select Mobile Wallet</label>
                            <select id="mobileProvider">
                                <option value="jazzcash">JazzCash</option>
                                <option value="easypaisa">Easypaisa</option>
                                <option value="ubl">UBL Omni</option>
                                <option value="sadapay">SadaPay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mobileNumber">📞 Mobile Number</label>
                            <input type="tel" id="mobileNumber" placeholder="03xx-xxxxxxx">
                        </div>
                        <div class="form-group">
                            <label for="mobilePin">🔐 Wallet PIN</label>
                            <input type="password" id="mobilePin" placeholder="Enter your wallet PIN" maxlength="5">
                        </div>
                    </div>

                    <!-- Crypto Payment Form -->
                    <div id="cryptoPaymentForm" class="payment-form">
                        <div class="crypto-info">
                            <h5>₿ Cryptocurrency Payment</h5>
                            <div class="form-group">
                                <label for="cryptoType">🪙 Select Cryptocurrency</label>
                                <select id="cryptoType" onchange="updateCryptoAddress()">
                                    <option value="btc">Bitcoin (BTC)</option>
                                    <option value="eth">Ethereum (ETH)</option>
                                    <option value="usdt">Tether USDT</option>
                                    <option value="usdc">USD Coin (USDC)</option>
                                </select>
                            </div>
                            <div class="crypto-address">
                                <p><strong>Send Payment To:</strong></p>
                                <div class="address-container">
                                    <input type="text" id="cryptoAddress" readonly>
                                    <button type="button" onclick="copyCryptoAddress()" class="copy-btn">📋 Copy</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cryptoTxId">🔗 Transaction Hash</label>
                            <input type="text" id="cryptoTxId" placeholder="Enter blockchain transaction hash">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="processPayment()" class="btn btn-success btn-full" id="purchaseBtn">
                    <span class="btn-icon">💳</span>
                    <span class="btn-text">Purchase Tokens</span>
                    <span class="btn-amount" id="btnAmount">₨ 10,250</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    updateTotal();
    selectPaymentMethod('card'); // Default to card payment
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.remove();
    }
}

// Enhanced update total function with cost breakdown
function updateTotal() {
    const tokenAmount = parseInt(document.getElementById('tokenAmount')?.value || 0);
    const tokenCost = tokenAmount * 1000;
    const platformFee = Math.ceil(tokenCost * 0.02); // 2% platform fee
    const transactionFee = 50; // Fixed transaction fee
    const totalAmount = tokenCost + platformFee + transactionFee;

    // Update cost breakdown display
    if (document.getElementById('tokenCost')) {
        document.getElementById('tokenCost').textContent = `₨ ${tokenCost.toLocaleString()}`;
        document.getElementById('platformFee').textContent = `₨ ${platformFee.toLocaleString()}`;
        document.getElementById('transactionFee').textContent = `₨ ${transactionFee.toLocaleString()}`;
        document.getElementById('totalAmount').innerHTML = `<strong>₨ ${totalAmount.toLocaleString()}</strong>`;
        document.getElementById('btnAmount').textContent = `₨ ${totalAmount.toLocaleString()}`;
    }

    // Update legacy field if it exists
    if (document.getElementById('totalPkr')) {
        document.getElementById('totalPkr').value = `₨ ${totalAmount.toLocaleString()}`;
    }
}

// Function to set token amount from suggestion buttons
function setTokenAmount(amount) {
    document.getElementById('tokenAmount').value = amount;
    updateTotal();
}

// Function to select payment method
function selectPaymentMethod(method) {
    // Update radio buttons
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.checked = false;
    });
    document.getElementById(method + 'Method').checked = true;

    // Update visual selection
    document.querySelectorAll('.payment-method').forEach(element => {
        element.classList.remove('selected');
    });
    document.querySelector(`#${method}Method`).closest('.payment-method').classList.add('selected');

    // Show corresponding payment form
    document.querySelectorAll('.payment-form').forEach(form => {
        form.classList.remove('active');
    });
    document.getElementById(method + 'PaymentForm').classList.add('active');

    // Update button text and icon
    const btnIcon = document.querySelector('.btn-icon');
    const btnText = document.querySelector('.btn-text');
    
    switch(method) {
        case 'card':
            btnIcon.textContent = '💳';
            btnText.textContent = 'Purchase with Card';
            break;
        case 'bank':
            btnIcon.textContent = '🏦';
            btnText.textContent = 'Confirm Bank Transfer';
            break;
        case 'mobile':
            btnIcon.textContent = '📱';
            btnText.textContent = 'Pay with Mobile Wallet';
            break;
        case 'crypto':
            btnIcon.textContent = '₿';
            btnText.textContent = 'Confirm Crypto Payment';
            break;
    }
}

// Function to update crypto address based on selected cryptocurrency
function updateCryptoAddress() {
    const cryptoType = document.getElementById('cryptoType').value;
    const addresses = {
        btc: '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
        eth: '0x32Be343B94f860124dC4fEe278FDCBD38C102D88',
        usdt: '0x32Be343B94f860124dC4fEe278FDCBD38C102D88',
        usdc: '0x32Be343B94f860124dC4fEe278FDCBD38C102D88'
    };
    
    document.getElementById('cryptoAddress').value = addresses[cryptoType];
}

// Function to copy crypto address to clipboard
function copyCryptoAddress() {
    const addressInput = document.getElementById('cryptoAddress');
    addressInput.select();
    addressInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show feedback
        const copyBtn = document.querySelector('.copy-btn');
        const originalText = copyBtn.textContent;
        copyBtn.textContent = '✅ Copied!';
        copyBtn.style.backgroundColor = 'var(--success-green)';
        
        setTimeout(() => {
            copyBtn.textContent = originalText;
            copyBtn.style.backgroundColor = '';
        }, 2000);
    } catch (err) {
        alert('Failed to copy address. Please copy manually.');
    }
}

function processPayment() {
    const tokenAmount = parseInt(document.getElementById('tokenAmount').value);
    const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const tokenCost = tokenAmount * 1000;
    const platformFee = Math.ceil(tokenCost * 0.02);
    const transactionFee = 50;
    const totalAmount = tokenCost + platformFee + transactionFee;

    let isValid = false;
    let paymentDetails = '';

    // Validate based on selected payment method
    switch(selectedMethod) {
        case 'card':
            const cardNumber = document.getElementById('cardNumber').value;
            const cardHolder = document.getElementById('cardHolder').value;
            const cardExpiry = document.getElementById('cardExpiry').value;
            const cardCVC = document.getElementById('cardCVC').value;
            
            if (!cardNumber || !cardHolder || !cardExpiry || !cardCVC) {
                alert('💳 Please fill in all card details.');
                return;
            }
            
            isValid = true;
            paymentDetails = `Card ending in ${cardNumber.slice(-4)}`;
            break;

        case 'bank':
            const bankReference = document.getElementById('bankReference').value;
            
            if (!bankReference) {
                alert('🏦 Please enter the bank transaction reference.');
                return;
            }
            
            isValid = true;
            paymentDetails = `Bank Transfer (Ref: ${bankReference})`;
            break;

        case 'mobile':
            const mobileNumber = document.getElementById('mobileNumber').value;
            const mobilePin = document.getElementById('mobilePin').value;
            const mobileProvider = document.getElementById('mobileProvider').value;
            
            if (!mobileNumber || !mobilePin) {
                alert('📱 Please enter your mobile number and wallet PIN.');
                return;
            }
            
            isValid = true;
            paymentDetails = `${mobileProvider.toUpperCase()} - ${mobileNumber}`;
            break;

        case 'crypto':
            const cryptoTxId = document.getElementById('cryptoTxId').value;
            const cryptoType = document.getElementById('cryptoType').value;
            
            if (!cryptoTxId) {
                alert('₿ Please enter the blockchain transaction hash.');
                return;
            }
            
            isValid = true;
            paymentDetails = `${cryptoType.toUpperCase()} Transaction`;
            break;
    }

    if (!isValid) return;

    // Confirm purchase
    const confirmMessage = `💰 Confirm Token Purchase

` +
        `Tokens: ${tokenAmount.toLocaleString()} PROP
` +
        `Token Cost: ₨ ${tokenCost.toLocaleString()}
` +
        `Platform Fee: ₨ ${platformFee.toLocaleString()}
` +
        `Transaction Fee: ₨ ${transactionFee.toLocaleString()}
` +
        `Total Amount: ₨ ${totalAmount.toLocaleString()}

` +
        `Payment Method: ${paymentDetails}

` +
        `Continue with purchase?`;

    if (!confirm(confirmMessage)) return;

    // Show processing with method-specific message
    showTransactionProcessing(selectedMethod);
    
    // Simulate different processing times based on payment method
    let processingTime = 3000;
    switch(selectedMethod) {
        case 'card': processingTime = 3000; break;
        case 'bank': processingTime = 2000; break;
        case 'mobile': processingTime = 2500; break;
        case 'crypto': processingTime = 4000; break;
    }

    setTimeout(() => {
        hideTransactionProcessing();
        closeModal('paymentModal');
        
        // Generate transaction hash
        const txHash = '0x' + Math.random().toString(16).substr(2, 64);
        
        // Success message with detailed breakdown
        const successMessage = `✅ Token Purchase Successful!

` +
            `🪙 Tokens Purchased: ${tokenAmount.toLocaleString()} PROP
` +
            `💰 Total Paid: ₨ ${totalAmount.toLocaleString()}
` +
            `💳 Payment Method: ${paymentDetails}
` +
            `🔗 Transaction Hash: ${txHash}

` +
            `Tokens will be transferred to your wallet within 5-10 minutes.
` +
            `You can track the transaction status in your dashboard.`;
        
        alert(successMessage);
        
        // Update user's token balance in localStorage (simulation)
        updateUserTokenBalance(tokenAmount, totalAmount, paymentDetails, txHash);
        
    }, processingTime);
}

// Function to update user token balance (simulation)
function updateUserTokenBalance(tokens, amount, paymentMethod, txHash) {
    const currentBalance = parseInt(localStorage.getItem('propledger_token_balance') || '395');
    const newBalance = currentBalance + tokens;
    localStorage.setItem('propledger_token_balance', newBalance.toString());
    
    // Store purchase history
    const purchases = JSON.parse(localStorage.getItem('propledger_token_purchases') || '[]');
    purchases.push({
        tokens: tokens,
        amount: amount,
        paymentMethod: paymentMethod,
        transactionHash: txHash,
        purchaseDate: new Date().toISOString(),
        status: 'completed'
    });
    localStorage.setItem('propledger_token_purchases', JSON.stringify(purchases));
    
    // Update token display in navbar if present
    updateTokenDisplays(newBalance);
}

// Function to update token displays across the site
function updateTokenDisplays(newBalance) {
    const tokenCounts = document.querySelectorAll('.token-count');
    const tokenValues = document.querySelectorAll('.token-value');
    
    tokenCounts.forEach(element => {
        element.textContent = `${newBalance} Tokens`;
    });
    
    tokenValues.forEach(element => {
        element.textContent = `₨ ${(newBalance * 1000).toLocaleString()}`;
    });
}

function showTransactionProcessing() {
    const processingOverlay = document.createElement('div');
    processingOverlay.className = 'modal-overlay processing';
    processingOverlay.id = 'processingOverlay';
    processingOverlay.innerHTML = `
        <div class="spinner-container">
            <div class="spinner"></div>
            <p>Processing Transaction...</p>
        </div>
    `;
    document.body.appendChild(processingOverlay);
}

function hideTransactionProcessing() {
    const overlay = document.getElementById('processingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

