<!-- VOIP Caller ID Popup Component -->
<!-- Include this in your main layout for store pages -->

<div id="callerIdPopup" class="caller-id-popup" style="display: none;">
    <div class="caller-id-content">
        <div class="caller-id-header">
            <i class="bi bi-telephone-inbound-fill text-success"></i>
            <span>Incoming Call</span>
            <button type="button" class="btn-close" onclick="closeCallerPopup()"></button>
        </div>
        <div class="caller-id-body">
            <div id="callerKnown" style="display: none;">
                <h4 id="callerName"></h4>
                <p class="text-muted mb-2" id="callerPhone"></p>
                <div class="caller-stats">
                    <div class="stat">
                        <span class="label">Total Spent</span>
                        <span class="value" id="callerSpent">$0.00</span>
                    </div>
                    <div class="stat">
                        <span class="label">Visits</span>
                        <span class="value" id="callerVisits">0</span>
                    </div>
                    <div class="stat">
                        <span class="label">Last Visit</span>
                        <span class="value" id="callerLastVisit">-</span>
                    </div>
                </div>
                <div class="caller-actions mt-3">
                    <a id="callerProfileLink" href="#" class="btn btn-primary btn-sm">
                        <i class="bi bi-person"></i> View Profile
                    </a>
                    <a id="callerPosLink" href="#" class="btn btn-success btn-sm">
                        <i class="bi bi-cart"></i> New Sale
                    </a>
                </div>
            </div>
            <div id="callerUnknown" style="display: none;">
                <h4>Unknown Caller</h4>
                <p class="text-muted mb-2" id="unknownPhone"></p>
                <a href="/store/customers/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus"></i> Create Customer
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .caller-id-popup {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .caller-id-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        min-width: 320px;
        overflow: hidden;
    }

    .caller-id-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .caller-id-header i {
        font-size: 1.5rem;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .caller-id-header .btn-close {
        margin-left: auto;
        filter: brightness(0) invert(1);
    }

    .caller-id-body {
        padding: 16px;
    }

    .caller-id-body h4 {
        margin: 0 0 4px 0;
        font-weight: 600;
    }

    .caller-stats {
        display: flex;
        gap: 16px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #eee;
    }

    .caller-stats .stat {
        text-align: center;
    }

    .caller-stats .label {
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
    }

    .caller-stats .value {
        display: block;
        font-weight: 600;
        font-size: 1rem;
    }

    .caller-actions {
        display: flex;
        gap: 8px;
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .caller-id-content {
            background: #1a1a2e;
            color: #fff;
        }

        .caller-stats {
            border-color: #333;
        }
    }
</style>

<script>
    // VOIP Caller ID System
    const CallerID = {
        popup: null,
        audio: null,

        init() {
            this.popup = document.getElementById('callerIdPopup');
            // Optional: ringtone
            // this.audio = new Audio('/assets/sounds/ringtone.mp3');

            // Listen for WebSocket incoming call events (if using real-time)
            if (typeof WebSocket !== 'undefined' && window.voipWebSocket) {
                window.voipWebSocket.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    if (data.type === 'incoming_call') {
                        this.showIncoming(data.phone);
                    }
                };
            }
        },

        async showIncoming(phone) {
            // Lookup customer
            try {
                const response = await fetch(`/store/voip/lookup?phone=${encodeURIComponent(phone)}`);
                const data = await response.json();

                if (data.found) {
                    document.getElementById('callerKnown').style.display = 'block';
                    document.getElementById('callerUnknown').style.display = 'none';
                    document.getElementById('callerName').textContent = data.customer.name;
                    document.getElementById('callerPhone').textContent = phone;
                    document.getElementById('callerSpent').textContent = '$' + data.customer.totalSpent;
                    document.getElementById('callerVisits').textContent = data.customer.visitCount;
                    document.getElementById('callerLastVisit').textContent = data.customer.lastVisit;
                    document.getElementById('callerProfileLink').href = '/store/customers/' + data.customer.id;
                    document.getElementById('callerPosLink').href = '/store/pos?customer=' + data.customer.id;
                } else {
                    document.getElementById('callerKnown').style.display = 'none';
                    document.getElementById('callerUnknown').style.display = 'block';
                    document.getElementById('unknownPhone').textContent = phone;
                }

                this.popup.style.display = 'block';

                // Play ringtone
                if (this.audio) {
                    this.audio.loop = true;
                    this.audio.play();
                }

                // Auto-hide after 30 seconds
                setTimeout(() => this.hide(), 30000);

            } catch (error) {
                console.error('Caller lookup failed:', error);
            }
        },

        hide() {
            this.popup.style.display = 'none';
            if (this.audio) {
                this.audio.pause();
                this.audio.currentTime = 0;
            }
        }
    };

    function closeCallerPopup() {
        CallerID.hide();
    }

    // Initialize when DOM ready
    document.addEventListener('DOMContentLoaded', () => CallerID.init());

    // Demo function to test popup (remove in production)
    function demoIncomingCall(phone = '555-123-4567') {
        CallerID.showIncoming(phone);
    }
</script>