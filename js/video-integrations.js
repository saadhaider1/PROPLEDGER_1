// Third-Party Video Call Integrations for PROPLEDGER

// 1. JITSI MEET INTEGRATION (Free & Easy)
function startJitsiVideoCall(agentName) {
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to start video calls.');
        window.location.href = 'login.html';
        return;
    }
    
    // Generate unique room name
    const roomName = `propledger-${agentName.replace(/\s+/g, '-').toLowerCase()}-${Date.now()}`;
    const userName = JSON.parse(localStorage.getItem('propledger_user')).full_name || 'User';
    
    // Create Jitsi Meet URL
    const jitsiUrl = `https://meet.jit.si/${roomName}?userInfo.displayName=${encodeURIComponent(userName)}`;
    
    if (confirm(`Start video call with ${agentName}?\n\nThis will open Jitsi Meet in a new window.`)) {
        // Open Jitsi Meet in new window
        const callWindow = window.open(jitsiUrl, 'jitsi-call', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        
        // Store call info with user identification
        const calls = JSON.parse(localStorage.getItem('propledger_calls') || '[]');
        const currentUser = JSON.parse(localStorage.getItem('propledger_user') || '{}');
        
        calls.push({
            agentName: agentName,
            roomName: roomName,
            type: 'jitsi-meet',
            startTime: new Date().toISOString(),
            status: 'active',
            url: jitsiUrl,
            // Add user identification for filtering
            initiatedBy: currentUser.user_type === 'agent' || currentUser.type === 'agent' ? 'agent' : 'user',
            userId: currentUser.user_type !== 'agent' && currentUser.type !== 'agent' ? currentUser.id : null,
            agentId: currentUser.user_type === 'agent' || currentUser.type === 'agent' ? currentUser.id : null,
            userName: currentUser.user_type !== 'agent' && currentUser.type !== 'agent' ? (currentUser.name || currentUser.full_name) : null,
            clientName: currentUser.user_type !== 'agent' && currentUser.type !== 'agent' ? (currentUser.name || currentUser.full_name) : null
        });
        localStorage.setItem('propledger_calls', JSON.stringify(calls));
        
        // Show notification
        showCallNotification(`Video call started with ${agentName}`, 'Room: ' + roomName);
    }
}

// 2. ZOOM INTEGRATION (Requires Zoom SDK)
function startZoomVideoCall(agentName) {
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to start video calls.');
        return;
    }
    
    // This would require Zoom SDK setup
    alert('Zoom integration requires SDK setup. Please contact administrator.');
    
    // Example of how it would work:
    /*
    const zoomConfig = {
        apiKey: 'YOUR_ZOOM_API_KEY',
        apiSecret: 'YOUR_ZOOM_API_SECRET',
        meetingNumber: generateMeetingNumber(),
        passWord: generatePassword(),
        userName: getUserName(),
        userEmail: getUserEmail()
    };
    
    ZoomMtg.init({
        leaveUrl: window.location.origin + '/html/dashboard.html',
        success: function() {
            ZoomMtg.join({
                meetingNumber: zoomConfig.meetingNumber,
                userName: zoomConfig.userName,
                signature: generateSignature(zoomConfig),
                apiKey: zoomConfig.apiKey,
                passWord: zoomConfig.passWord,
                success: function() {
                    console.log('Zoom call started');
                }
            });
        }
    });
    */
}

// 3. GOOGLE MEET INTEGRATION
function startGoogleMeetCall(agentName) {
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to start video calls.');
        return;
    }
    
    // Generate Google Meet URL (requires Google Workspace)
    const meetUrl = 'https://meet.google.com/new';
    
    if (confirm(`Start Google Meet call with ${agentName}?\n\nThis will open Google Meet in a new window.`)) {
        const callWindow = window.open(meetUrl, 'google-meet', 'width=1200,height=800');
        
        // Store call info
        const calls = JSON.parse(localStorage.getItem('propledger_calls') || '[]');
        calls.push({
            agentName: agentName,
            type: 'google-meet',
            startTime: new Date().toISOString(),
            status: 'active',
            url: meetUrl
        });
        localStorage.setItem('propledger_calls', JSON.stringify(calls));
        
        showCallNotification(`Google Meet call started with ${agentName}`, 'New meeting created');
    }
}

// 4. MICROSOFT TEAMS INTEGRATION
function startTeamsCall(agentName) {
    const isLoggedIn = localStorage.getItem('propledger_user');
    
    if (!isLoggedIn) {
        alert('Please login to start video calls.');
        return;
    }
    
    // Teams meeting URL (requires Teams setup)
    const teamsUrl = 'https://teams.microsoft.com/l/meetup-join/';
    
    alert('Microsoft Teams integration requires organizational setup. Please contact administrator.');
}

// 5. AGORA.IO INTEGRATION (Professional WebRTC)
class AgoraVideoCall {
    constructor() {
        this.client = null;
        this.localTracks = {
            videoTrack: null,
            audioTrack: null
        };
        this.remoteUsers = {};
        this.appId = 'YOUR_AGORA_APP_ID'; // Replace with your Agora App ID
    }
    
    async startAgoraCall(agentName, channelName) {
        try {
            // Initialize Agora client
            this.client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
            
            // Join channel
            await this.client.join(this.appId, channelName, null, null);
            
            // Create local tracks
            this.localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            this.localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
            
            // Publish local tracks
            await this.client.publish(Object.values(this.localTracks));
            
            // Show video call interface
            this.showAgoraVideoInterface(agentName, channelName);
            
            // Handle remote users
            this.client.on("user-published", this.handleUserPublished.bind(this));
            this.client.on("user-unpublished", this.handleUserUnpublished.bind(this));
            
        } catch (error) {
            console.error('Agora call error:', error);
            alert('Failed to start video call. Please try again.');
        }
    }
    
    handleUserPublished(user, mediaType) {
        // Subscribe to remote user
        this.client.subscribe(user, mediaType);
        
        if (mediaType === 'video') {
            // Play remote video
            const remoteVideoTrack = user.videoTrack;
            remoteVideoTrack.play('remote-video-container');
        }
        
        if (mediaType === 'audio') {
            // Play remote audio
            const remoteAudioTrack = user.audioTrack;
            remoteAudioTrack.play();
        }
    }
    
    showAgoraVideoInterface(agentName, channelName) {
        // Similar to WebRTC interface but using Agora components
        // Implementation would be similar to the WebRTC modal above
    }
}

// 6. SIMPLE IFRAME INTEGRATION (Any video service)
function startIframeVideoCall(agentName, serviceUrl) {
    const modal = document.createElement('div');
    modal.id = 'iframe-video-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    modal.innerHTML = `
        <div style="width: 95%; height: 95%; background: white; border-radius: 10px; overflow: hidden; position: relative;">
            <div style="background: var(--card-bg); padding: 1rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0; color: var(--blockchain-blue);">📹 Video Call with ${agentName}</h3>
                <button onclick="document.getElementById('iframe-video-modal').remove()" style="background: #ff4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;">End Call</button>
            </div>
            <iframe src="${serviceUrl}" style="width: 100%; height: calc(100% - 80px); border: none;"></iframe>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Utility function to show call notifications
function showCallNotification(title, message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--card-bg);
        border: 1px solid var(--blockchain-blue);
        border-radius: 10px;
        padding: 1rem;
        z-index: 9999;
        max-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span style="font-size: 1.2rem;">📹</span>
            <strong style="color: var(--blockchain-blue);">${title}</strong>
        </div>
        <div style="color: var(--text-secondary); font-size: 0.9rem;">${message}</div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
