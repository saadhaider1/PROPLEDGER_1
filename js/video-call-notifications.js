// Real-time Video Call Notification System for PROPLEDGER

class VideoCallNotificationSystem {
    constructor() {
        this.checkInterval = null;
        this.lastCheckedTime = Date.now();
        this.currentUser = null;
        this.init();
    }

    init() {
        // Get current user info
        const userStr = localStorage.getItem('propledger_user');
        if (userStr) {
            this.currentUser = JSON.parse(userStr);
            this.startListening();
        }
    }

    startListening() {
        // Check for new video call invitations every 3 seconds
        this.checkInterval = setInterval(() => {
            this.checkForIncomingCalls();
        }, 3000);

        console.log('Video call notification system started');
    }

    stopListening() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }

    checkForIncomingCalls() {
        const allCalls = JSON.parse(localStorage.getItem('propledger_calls') || '[]');
        const currentTime = Date.now();
        
        // Look for recent calls (within last 30 seconds) that this user should be notified about
        const recentCalls = allCalls.filter(call => {
            const callTime = new Date(call.startTime).getTime();
            const timeDiff = currentTime - callTime;
            
            // Call is recent (within 30 seconds) and not initiated by current user
            return timeDiff < 30000 && 
                   timeDiff > 0 && 
                   !call.notificationShown &&
                   this.shouldNotifyUser(call);
        });

        recentCalls.forEach(call => {
            this.showIncomingCallNotification(call);
            // Mark as notification shown
            call.notificationShown = true;
        });

        if (recentCalls.length > 0) {
            localStorage.setItem('propledger_calls', JSON.stringify(allCalls));
        }
    }

    shouldNotifyUser(call) {
        if (!this.currentUser) return false;

        const isAgent = this.currentUser.user_type === 'agent' || this.currentUser.type === 'agent';
        const currentUserName = this.currentUser.name || this.currentUser.full_name || '';

        if (isAgent) {
            // Agent should be notified if:
            // 1. User initiated call and mentioned this agent's name
            // 2. Call is directed to agents in general
            return call.initiatedBy === 'user' && 
                   (call.agentName.toLowerCase().includes(currentUserName.toLowerCase()) ||
                    call.agentName.toLowerCase().includes('portfolio') ||
                    call.agentName.toLowerCase().includes('manager') ||
                    call.agentName.toLowerCase().includes('agent'));
        } else {
            // User should be notified if:
            // 1. Agent initiated call and mentioned this user's name
            // 2. Call is for this specific user
            return call.initiatedBy === 'agent' && 
                   (call.clientName.toLowerCase().includes(currentUserName.toLowerCase()) ||
                    call.userId === this.currentUser.id);
        }
    }

    showIncomingCallNotification(call) {
        const isAgent = this.currentUser.user_type === 'agent' || this.currentUser.type === 'agent';
        const callerName = isAgent ? call.clientName || call.userName || 'User' : call.agentName || 'Agent';
        
        // Create notification popup
        const notification = document.createElement('div');
        notification.id = 'video-call-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.4);
            z-index: 10001;
            max-width: 350px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            animation: slideInBounce 0.5s ease-out;
            border: 2px solid rgba(255, 255, 255, 0.3);
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 2rem; margin-right: 1rem; animation: pulse 1s infinite;">📹</div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Incoming Video Call</h3>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">From: ${callerName}</p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button onclick="joinVideoCall('${call.roomName}', '${call.url}')" 
                        style="flex: 1; background: #00ff88; color: #000; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                    📞 Join Call
                </button>
                <button onclick="declineVideoCall('${call.roomName}')" 
                        style="flex: 1; background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                    ❌ Decline
                </button>
            </div>
            <div style="margin-top: 0.75rem; font-size: 0.8rem; opacity: 0.8; text-align: center;">
                Room: ${call.roomName}
            </div>
        `;

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInBounce {
                0% { transform: translateX(100%) scale(0.8); opacity: 0; }
                60% { transform: translateX(-10px) scale(1.05); opacity: 1; }
                100% { transform: translateX(0) scale(1); opacity: 1; }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
        `;
        document.head.appendChild(style);

        // Remove existing notification if any
        const existing = document.getElementById('video-call-notification');
        if (existing) {
            existing.remove();
        }

        document.body.appendChild(notification);

        // Auto-remove after 30 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 30000);

        // Play notification sound (if available)
        this.playNotificationSound();
    }

    playNotificationSound() {
        try {
            // Create a simple notification beep
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime + 0.2);
            
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (error) {
            console.log('Audio notification not available');
        }
    }
}

// Global functions for notification buttons
function joinVideoCall(roomName, url) {
    // Close notification
    const notification = document.getElementById('video-call-notification');
    if (notification) {
        notification.remove();
    }

    // Join the video call
    const callWindow = window.open(url, 'jitsi-call', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    
    // Show success message
    showCallNotification('✅ Joined video call successfully!', `Room: ${roomName}`);
    
    // Update call history to show as joined
    updateCallHistoryStatus(roomName, 'joined');
}

function declineVideoCall(roomName) {
    // Close notification
    const notification = document.getElementById('video-call-notification');
    if (notification) {
        notification.remove();
    }

    // Show decline message
    showCallNotification('❌ Video call declined', `Room: ${roomName}`);
    
    // Update call history to show as declined
    updateCallHistoryStatus(roomName, 'declined');
}

// Update call status in localStorage
function updateCallHistoryStatus(roomName, status) {
    const allCalls = JSON.parse(localStorage.getItem('propledger_calls') || '[]');
    const callIndex = allCalls.findIndex(call => call.roomName === roomName);
    
    if (callIndex !== -1) {
        allCalls[callIndex].status = status;
        allCalls[callIndex].responseTime = new Date().toISOString();
        localStorage.setItem('propledger_calls', JSON.stringify(allCalls));
        
        // Refresh call history displays
        if (typeof refreshVideoCallHistory === 'function') {
            refreshVideoCallHistory();
        }
        if (typeof refreshUserVideoCallHistory === 'function') {
            refreshUserVideoCallHistory();
        }
    }
}

// Initialize the notification system when page loads
let videoCallNotificationSystem = null;

document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure user data is loaded
    setTimeout(() => {
        videoCallNotificationSystem = new VideoCallNotificationSystem();
    }, 1000);
});

// Clean up when page unloads
window.addEventListener('beforeunload', function() {
    if (videoCallNotificationSystem) {
        videoCallNotificationSystem.stopListening();
    }
});
