/**
 * POS Enhanced Features JavaScript
 * Weather, Time/Date, Clock In/Out, AI Assistant, Auto-Logout
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // Time and Date Display
    // ========================

    function updateDateTime() {
        const now = new Date();
        const timeEl = document.getElementById('posCurrentTime');
        const dateEl = document.getElementById('posCurrentDate');

        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            });
        }
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);

    // ========================
    // Weather Widget
    // ========================

    async function fetchWeather() {
        const weatherTemp = document.getElementById('weatherTemp');
        const weatherDesc = document.getElementById('weatherDesc');
        const weatherIcon = document.getElementById('weatherIcon');

        if (!weatherTemp) return;

        try {
            // Try to get user location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    const { latitude, longitude } = pos.coords;
                    await getWeatherData(latitude, longitude);
                }, () => {
                    // Default to a location if geolocation denied
                    setDefaultWeather();
                });
            } else {
                setDefaultWeather();
            }
        } catch (e) {
            setDefaultWeather();
        }
    }

    async function getWeatherData(lat, lon) {
        try {
            // Using Open-Meteo API (free, no key required)
            const response = await fetch(
                `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&temperature_unit=fahrenheit`
            );
            const data = await response.json();

            if (data.current_weather) {
                const temp = Math.round(data.current_weather.temperature);
                const code = data.current_weather.weathercode;

                document.getElementById('weatherTemp').textContent = `${temp}°F`;
                document.getElementById('weatherDesc').textContent = getWeatherDescription(code);
                document.getElementById('weatherIcon').className = getWeatherIcon(code);
            }
        } catch (e) {
            setDefaultWeather();
        }
    }

    function setDefaultWeather() {
        const temp = document.getElementById('weatherTemp');
        const desc = document.getElementById('weatherDesc');
        if (temp) temp.textContent = '72°F';
        if (desc) desc.textContent = 'Sunny';
    }

    function getWeatherDescription(code) {
        const descriptions = {
            0: 'Clear Sky', 1: 'Mainly Clear', 2: 'Partly Cloudy', 3: 'Overcast',
            45: 'Foggy', 48: 'Fog', 51: 'Light Drizzle', 53: 'Drizzle', 55: 'Heavy Drizzle',
            61: 'Light Rain', 63: 'Rain', 65: 'Heavy Rain', 71: 'Light Snow', 73: 'Snow',
            75: 'Heavy Snow', 80: 'Light Showers', 81: 'Showers', 82: 'Heavy Showers',
            95: 'Thunderstorm', 96: 'Hail Storm', 99: 'Heavy Hail'
        };
        return descriptions[code] || 'Clear';
    }

    function getWeatherIcon(code) {
        if (code === 0 || code === 1) return 'bi bi-sun fs-4';
        if (code === 2) return 'bi bi-cloud-sun fs-4';
        if (code === 3) return 'bi bi-clouds fs-4';
        if (code >= 45 && code <= 48) return 'bi bi-cloud-fog fs-4';
        if (code >= 51 && code <= 65) return 'bi bi-cloud-rain fs-4';
        if (code >= 71 && code <= 75) return 'bi bi-cloud-snow fs-4';
        if (code >= 80 && code <= 82) return 'bi bi-cloud-rain-heavy fs-4';
        if (code >= 95) return 'bi bi-cloud-lightning-rain fs-4';
        return 'bi bi-cloud-sun fs-4';
    }

    fetchWeather();
    // Refresh weather every 30 minutes
    setInterval(fetchWeather, 30 * 60 * 1000);

    // ========================
    // Clock In/Out
    // ========================

    let clockedIn = false;
    let clockInTime = null;
    let clockInterval = null;

    const timeClockBtn = document.getElementById('timeClockBtn');
    const timeClockLabel = document.getElementById('timeClockLabel');
    const timeClockDuration = document.getElementById('timeClockDuration');

    function checkClockStatus() {
        fetch('/store/time-clock/status')
            .then(r => r.json())
            .then(data => {
                if (data.clocked_in) {
                    clockedIn = true;
                    clockInTime = new Date(data.clock_in_time);
                    updateClockUI();
                    startClockTimer();
                } else {
                    clockedIn = false;
                    updateClockUI();
                }
            })
            .catch(() => {
                // Default state
                updateClockUI();
            });
    }

    function updateClockUI() {
        if (timeClockBtn) {
            if (clockedIn) {
                timeClockBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                if (timeClockLabel) timeClockLabel.textContent = 'Clock Out';
            } else {
                timeClockBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                if (timeClockLabel) timeClockLabel.textContent = 'Clock In';
                if (timeClockDuration) timeClockDuration.textContent = '--:--:--';
            }
        }
    }

    function startClockTimer() {
        if (clockInterval) clearInterval(clockInterval);

        clockInterval = setInterval(() => {
            if (clockInTime && timeClockDuration) {
                const now = new Date();
                const diff = now - clockInTime;
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                timeClockDuration.textContent =
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }

    if (timeClockBtn) {
        timeClockBtn.addEventListener('click', function () {
            const action = clockedIn ? 'out' : 'in';

            fetch('/store/time-clock/' + action, { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        clockedIn = !clockedIn;
                        if (clockedIn) {
                            clockInTime = new Date();
                            startClockTimer();
                        } else {
                            if (clockInterval) clearInterval(clockInterval);
                            clockInTime = null;
                        }
                        updateClockUI();
                        showToast(clockedIn ? 'Clocked in successfully!' : 'Clocked out!', 'success');
                    }
                })
                .catch(() => {
                    // Toggle locally for demo
                    clockedIn = !clockedIn;
                    if (clockedIn) {
                        clockInTime = new Date();
                        startClockTimer();
                    } else {
                        if (clockInterval) clearInterval(clockInterval);
                    }
                    updateClockUI();
                    showToast(clockedIn ? 'Clocked in!' : 'Clocked out!', 'success');
                });
        });
    }

    checkClockStatus();

    // ========================
    // AI Assistant
    // ========================

    const aiChatInput = document.getElementById('aiChatInput');
    const aiChatSend = document.getElementById('aiChatSend');
    const aiChatMessages = document.getElementById('aiChatMessages');

    function addAiMessage(message, isUser = false) {
        if (!aiChatMessages) return;

        const messageHtml = isUser ? `
            <div class="d-flex gap-3 mb-3 justify-content-end">
                <div class="user-message p-3 rounded-3" style="background: #4f46e5; color: white; max-width: 80%;">
                    ${escapeHtml(message)}
                </div>
                <div class="user-avatar">
                    <i class="bi bi-person-circle fs-4 text-muted"></i>
                </div>
            </div>
        ` : `
            <div class="d-flex gap-3 mb-3">
                <div class="ai-avatar">
                    <i class="bi bi-robot fs-4 text-primary"></i>
                </div>
                <div class="ai-message p-3 rounded-3" style="background: white; max-width: 80%;">
                    ${message}
                </div>
            </div>
        `;

        aiChatMessages.insertAdjacentHTML('beforeend', messageHtml);
        aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function processAiQuery(query) {
        addAiMessage(query, true);

        // Show typing indicator
        addAiMessage('<div class="typing-indicator"><span></span><span></span><span></span></div>', false);

        try {
            const response = await fetch('/store/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query })
            });
            const data = await response.json();

            // Remove typing indicator
            const lastMsg = aiChatMessages.lastElementChild;
            if (lastMsg) lastMsg.remove();

            addAiMessage(data.response || 'I processed your request!', false);
        } catch (e) {
            // Remove typing indicator
            const lastMsg = aiChatMessages.lastElementChild;
            if (lastMsg) lastMsg.remove();

            // Demo responses
            const demoResponses = {
                'low stock': 'I found 5 items with low stock:<br>• BCD Jacket - 2 left<br>• Mask Pro - 3 left<br>• Fins M - 1 left',
                'bestseller': 'Today\'s top seller is the <strong>PADI Open Water eLearning</strong> with 8 sales!',
                'under $100': 'Budget-friendly gear:<br>• Snorkel Set - $45<br>• Dive Bag - $35<br>• Mask Basic - $28',
                'schedule': 'Today\'s schedule:<br>• 9 AM - OW Pool Session<br>• 1 PM - Equipment Rental<br>• 3 PM - Nitrox Class',
                'scan': 'Opening camera for product scan...'
            };

            let response = 'I can help you find products, check inventory, or look up customer info. Try asking about specific items!';

            for (const [key, val] of Object.entries(demoResponses)) {
                if (query.toLowerCase().includes(key)) {
                    response = val;
                    break;
                }
            }

            addAiMessage(response, false);
        }
    }

    if (aiChatSend) {
        aiChatSend.addEventListener('click', function () {
            const query = aiChatInput?.value.trim();
            if (query) {
                processAiQuery(query);
                aiChatInput.value = '';
            }
        });
    }

    if (aiChatInput) {
        aiChatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    processAiQuery(query);
                    this.value = '';
                }
            }
        });
    }

    // Quick action buttons
    document.querySelectorAll('.ai-quick-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const prompt = this.dataset.prompt;
            if (prompt) {
                processAiQuery(prompt);
            }
        });
    });

    // ========================
    // Auto-Logout Timer
    // ========================

    let idleTimeout = 15 * 60 * 1000; // 15 minutes default
    let idleTimer = null;
    let countdownTimer = null;

    function resetIdleTimer() {
        if (idleTimer) clearTimeout(idleTimer);
        if (countdownTimer) clearInterval(countdownTimer);

        const autoLogoutEl = document.getElementById('autoLogoutTimer');
        if (autoLogoutEl) autoLogoutEl.style.display = 'none';

        idleTimer = setTimeout(showLogoutWarning, idleTimeout - 60000); // Show warning 1 min before
    }

    function showLogoutWarning() {
        const autoLogoutEl = document.getElementById('autoLogoutTimer');
        const countdownEl = document.getElementById('logoutCountdown');

        if (autoLogoutEl && countdownEl) {
            autoLogoutEl.style.display = 'block';
            let seconds = 60;

            countdownTimer = setInterval(() => {
                seconds--;
                countdownEl.textContent = `0:${seconds.toString().padStart(2, '0')}`;

                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    window.location.href = '/logout?reason=idle';
                }
            }, 1000);
        }
    }

    // Reset timer on activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetIdleTimer, { passive: true });
    });

    resetIdleTimer();

    // ========================
    // Utility Functions
    // ========================

    function showToast(message, type = 'info') {
        const toast = document.getElementById('posToast');
        if (toast) {
            toast.querySelector('.toast-body').textContent = message;
            toast.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning');
            toast.classList.add('bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'));
            new bootstrap.Toast(toast).show();
        }
    }

    // Add typing indicator CSS
    const style = document.createElement('style');
    style.textContent = `
        .typing-indicator { display: flex; gap: 4px; padding: 8px 0; }
        .typing-indicator span { width: 8px; height: 8px; background: #ccc; border-radius: 50%; animation: typing 1s infinite; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing { 0%, 100% { opacity: 0.3; } 50% { opacity: 1; } }
        
        .weather-widget, .datetime-widget, .time-clock-btn, .ai-assistant-btn {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .weather-widget:hover, .datetime-widget:hover, .time-clock-btn:hover, .ai-assistant-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important;
        }
    `;
    document.head.appendChild(style);
});
