/**
 * Nautilus Screensaver and Auto-Logout
 * 
 * Features:
 * - Idle detection
 * - Animated screensaver with logo
 * - Auto-logout countdown
 * - PIN unlock option
 */

(function () {
    'use strict';

    // Configuration (can be overridden from server settings)
    const config = {
        screensaverMinutes: 10,
        autoLogoutMinutes: 15,
        pinUnlockEnabled: false,
        enabled: true
    };

    let idleTime = 0;
    let screensaverActive = false;
    let logoutCountdownActive = false;
    let logoutCountdownSeconds = 60;
    let countdownInterval = null;

    // Create screensaver overlay
    function createScreensaver() {
        if (document.getElementById('nautilusScreensaver')) return;

        const screensaverHtml = `
            <div id="nautilusScreensaver" class="screensaver-overlay">
                <div class="screensaver-content">
                    <!-- Animated bubbles background -->
                    <div class="bubbles-container" id="bubblesContainer"></div>
                    
                    <!-- Logo and time -->
                    <div class="screensaver-main">
                        <img src="/assets/img/logo.png" alt="Nautilus Dive Shop" class="screensaver-logo" onerror="this.style.display='none'">
                        <h1 class="screensaver-title">Nautilus Dive Shop</h1>
                        <div class="screensaver-time" id="screensaverTime">--:--</div>
                        <div class="screensaver-date" id="screensaverDate">Loading...</div>
                        
                        <!-- Logout countdown (hidden until triggered) -->
                        <div class="logout-countdown" id="logoutCountdown" style="display: none;">
                            <div class="countdown-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Auto-logout in <span id="countdownTimer">60</span> seconds
                            </div>
                        </div>
                        
                        <!-- PIN unlock panel (if enabled) -->
                        <div class="pin-unlock-panel" id="pinUnlockPanel" style="display: none;">
                            <input type="password" class="pin-input" id="pinInput" maxlength="4" placeholder="Enter PIN">
                            <div class="pin-dots">
                                <span class="pin-dot"></span>
                                <span class="pin-dot"></span>
                                <span class="pin-dot"></span>
                                <span class="pin-dot"></span>
                            </div>
                        </div>
                        
                        <p class="screensaver-hint">Touch or press any key to continue</p>
                    </div>
                </div>
            </div>
            
            <style>
                .screensaver-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
                    z-index: 100000;
                    display: none;
                    overflow: hidden;
                }
                
                .screensaver-overlay.active {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .screensaver-content {
                    text-align: center;
                    color: white;
                    position: relative;
                    z-index: 10;
                }
                
                .screensaver-logo {
                    width: 150px;
                    height: 150px;
                    object-fit: contain;
                    margin-bottom: 20px;
                    animation: float 3s ease-in-out infinite;
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                }
                
                .screensaver-title {
                    font-size: 3rem;
                    font-weight: 700;
                    margin-bottom: 10px;
                    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
                }
                
                .screensaver-time {
                    font-size: 6rem;
                    font-weight: 300;
                    font-family: 'Courier New', monospace;
                    text-shadow: 0 0 20px rgba(255,255,255,0.5);
                }
                
                .screensaver-date {
                    font-size: 1.5rem;
                    opacity: 0.8;
                    margin-bottom: 30px;
                }
                
                .screensaver-hint {
                    font-size: 1rem;
                    opacity: 0.6;
                    margin-top: 40px;
                }
                
                .logout-countdown {
                    margin-top: 30px;
                }
                
                .countdown-warning {
                    background: rgba(239, 68, 68, 0.9);
                    padding: 15px 30px;
                    border-radius: 50px;
                    font-size: 1.2rem;
                    display: inline-block;
                    animation: pulse 1s infinite;
                }
                
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                }
                
                .bubbles-container {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    overflow: hidden;
                    z-index: 1;
                }
                
                .bubble {
                    position: absolute;
                    bottom: -100px;
                    background: rgba(255,255,255,0.1);
                    border-radius: 50%;
                    animation: rise 8s infinite ease-in;
                }
                
                @keyframes rise {
                    0% { 
                        bottom: -100px; 
                        transform: translateX(0) scale(1);
                        opacity: 0.6;
                    }
                    100% { 
                        bottom: 120vh; 
                        transform: translateX(-100px) scale(0.5);
                        opacity: 0;
                    }
                }
                
                .pin-unlock-panel {
                    margin-top: 30px;
                }
                
                .pin-input {
                    background: rgba(255,255,255,0.2);
                    border: 2px solid rgba(255,255,255,0.5);
                    border-radius: 10px;
                    padding: 15px 30px;
                    font-size: 2rem;
                    color: white;
                    text-align: center;
                    letter-spacing: 20px;
                    width: 200px;
                }
                
                .pin-input::placeholder {
                    color: rgba(255,255,255,0.5);
                    letter-spacing: 2px;
                }
                
                .pin-dots {
                    display: flex;
                    justify-content: center;
                    gap: 15px;
                    margin-top: 15px;
                }
                
                .pin-dot {
                    width: 15px;
                    height: 15px;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.3);
                    border: 2px solid rgba(255,255,255,0.5);
                }
                
                .pin-dot.filled {
                    background: white;
                }
            </style>
        `;

        document.body.insertAdjacentHTML('beforeend', screensaverHtml);

        // Create bubbles
        createBubbles();

        // Update time
        updateScreensaverTime();
        setInterval(updateScreensaverTime, 1000);
    }

    function createBubbles() {
        const container = document.getElementById('bubblesContainer');
        if (!container) return;

        for (let i = 0; i < 20; i++) {
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            bubble.style.left = Math.random() * 100 + '%';
            bubble.style.width = (Math.random() * 60 + 20) + 'px';
            bubble.style.height = bubble.style.width;
            bubble.style.animationDuration = (Math.random() * 4 + 6) + 's';
            bubble.style.animationDelay = (Math.random() * 5) + 's';
            container.appendChild(bubble);
        }
    }

    function updateScreensaverTime() {
        const now = new Date();
        const timeEl = document.getElementById('screensaverTime');
        const dateEl = document.getElementById('screensaverDate');

        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }
    }

    function showScreensaver() {
        if (screensaverActive) return;

        createScreensaver();
        const overlay = document.getElementById('nautilusScreensaver');
        if (overlay) {
            overlay.classList.add('active');
            screensaverActive = true;
        }
    }

    function hideScreensaver() {
        const overlay = document.getElementById('nautilusScreensaver');
        if (overlay) {
            overlay.classList.remove('active');
        }
        screensaverActive = false;
        logoutCountdownActive = false;

        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        // Reset idle time
        idleTime = 0;
    }

    function startLogoutCountdown() {
        if (logoutCountdownActive) return;

        logoutCountdownActive = true;
        logoutCountdownSeconds = 60;

        const countdownEl = document.getElementById('logoutCountdown');
        const timerEl = document.getElementById('countdownTimer');

        if (countdownEl) countdownEl.style.display = 'block';

        countdownInterval = setInterval(() => {
            logoutCountdownSeconds--;
            if (timerEl) timerEl.textContent = logoutCountdownSeconds;

            if (logoutCountdownSeconds <= 0) {
                clearInterval(countdownInterval);
                // Perform logout
                window.location.href = '/logout?reason=idle';
            }
        }, 1000);
    }

    // Idle detection
    function resetIdleTime() {
        if (screensaverActive) {
            hideScreensaver();
        }
        idleTime = 0;
    }

    function checkIdleTime() {
        if (!config.enabled) return;

        idleTime++;

        const screensaverThreshold = config.screensaverMinutes * 60;
        const logoutThreshold = config.autoLogoutMinutes * 60;

        if (idleTime >= logoutThreshold - 60 && !logoutCountdownActive) {
            showScreensaver();
            startLogoutCountdown();
        } else if (idleTime >= screensaverThreshold && !screensaverActive) {
            showScreensaver();
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function () {
        // Activity detection
        ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetIdleTime, { passive: true });
        });

        // Check idle every second
        setInterval(checkIdleTime, 1000);

        // Screensaver click to dismiss
        document.addEventListener('click', function (e) {
            if (e.target.closest('#nautilusScreensaver')) {
                if (!logoutCountdownActive) {
                    hideScreensaver();
                }
            }
        });

        // Load settings from server if available
        fetch('/store/api/settings/screensaver')
            .then(r => r.json())
            .then(data => {
                if (data.screensaver_minutes) config.screensaverMinutes = parseInt(data.screensaver_minutes);
                if (data.auto_logout_minutes) config.autoLogoutMinutes = parseInt(data.auto_logout_minutes);
                if (data.pin_unlock_enabled) config.pinUnlockEnabled = data.pin_unlock_enabled === '1';
                if (data.enabled !== undefined) config.enabled = data.enabled;
            })
            .catch(() => {
                // Use defaults
            });
    });

    // Expose for testing
    window.NautilusScreensaver = {
        show: showScreensaver,
        hide: hideScreensaver,
        config: config
    };
})();
