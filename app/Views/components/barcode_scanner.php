<?php
/**
 * Barcode Scanner Component
 * Uses QuaggaJS for webcam-based barcode scanning
 */
?>

<div class="barcode-scanner-modal" id="barcodeScannerModal">
    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upc-scan"></i> Scan Barcode
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Instructions:</strong> Hold the barcode steady in front of your camera.
                        The scanner will automatically detect and read the barcode.
                    </div>

                    <!-- Camera Selection -->
                    <div class="mb-3">
                        <label for="cameraSelect" class="form-label">
                            <i class="bi bi-camera"></i> Camera
                        </label>
                        <select id="cameraSelect" class="form-select">
                            <option value="">Detecting cameras...</option>
                        </select>
                    </div>

                    <!-- Scanner Container -->
                    <div id="scanner-container" class="position-relative">
                        <div id="interactive" class="viewport"></div>
                        <div id="scanner-overlay" class="scanner-overlay">
                            <div class="scanner-line"></div>
                        </div>
                    </div>

                    <!-- Scan Result -->
                    <div id="scan-result" class="mt-3" style="display: none;">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>Barcode Detected:</strong>
                            <span id="barcode-value" class="badge bg-dark fs-6 ms-2"></span>
                        </div>
                    </div>

                    <!-- Manual Entry Alternative -->
                    <div class="mt-3">
                        <label for="manual-barcode" class="form-label">
                            <i class="bi bi-keyboard"></i> Or enter barcode manually:
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="manual-barcode"
                                   placeholder="Enter barcode number">
                            <button class="btn btn-primary" type="button" id="manual-submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QuaggaJS from CDN -->
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

<style>
#scanner-container {
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    max-height: 400px;
}

#interactive.viewport {
    width: 100%;
    height: 400px;
}

#interactive.viewport canvas,
#interactive.viewport video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scanner-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 60%;
    border: 3px solid #00ff00;
    border-radius: 8px;
    pointer-events: none;
}

.scanner-line {
    position: absolute;
    width: 100%;
    height: 2px;
    background: #00ff00;
    box-shadow: 0 0 10px #00ff00;
    animation: scan 2s linear infinite;
}

@keyframes scan {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
}

.drawingBuffer {
    display: none !important;
}
</style>

<script>
class BarcodeScanner {
    constructor(options = {}) {
        this.onScan = options.onScan || this.defaultOnScan;
        this.modal = null;
        this.quaggaStarted = false;
        this.cameras = [];
        this.currentCamera = null;
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('scannerModal'));

        // Set up event listeners
        document.getElementById('scannerModal').addEventListener('shown.bs.modal', () => {
            this.detectCameras();
        });

        document.getElementById('scannerModal').addEventListener('hidden.bs.modal', () => {
            this.stop();
        });

        document.getElementById('cameraSelect').addEventListener('change', (e) => {
            if (e.target.value) {
                this.currentCamera = e.target.value;
                this.restart();
            }
        });

        document.getElementById('manual-submit').addEventListener('click', () => {
            const barcode = document.getElementById('manual-barcode').value.trim();
            if (barcode) {
                this.onScan(barcode, 'manual');
            }
        });

        // Allow Enter key for manual entry
        document.getElementById('manual-barcode').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('manual-submit').click();
            }
        });
    }

    async detectCameras() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            this.cameras = devices.filter(device => device.kind === 'videoinput');

            const select = document.getElementById('cameraSelect');
            select.innerHTML = '';

            if (this.cameras.length === 0) {
                select.innerHTML = '<option value="">No cameras detected</option>';
                return;
            }

            this.cameras.forEach((camera, index) => {
                const option = document.createElement('option');
                option.value = camera.deviceId;
                option.text = camera.label || `Camera ${index + 1}`;
                select.appendChild(option);
            });

            // Auto-select first camera and start
            this.currentCamera = this.cameras[0].deviceId;
            select.value = this.currentCamera;
            this.start();

        } catch (error) {
            console.error('Error detecting cameras:', error);
            document.getElementById('cameraSelect').innerHTML =
                '<option value="">Camera access denied or not available</option>';
        }
    }

    start() {
        if (this.quaggaStarted) {
            return;
        }

        const config = {
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#interactive'),
                constraints: {
                    deviceId: this.currentCamera,
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
            },
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "code_39_vin_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ]
            },
            locate: true,
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: 4,
            frequency: 10,
        };

        Quagga.init(config, (err) => {
            if (err) {
                console.error('Quagga initialization failed:', err);
                alert('Failed to initialize barcode scanner. Please check camera permissions.');
                return;
            }

            Quagga.start();
            this.quaggaStarted = true;

            // Listen for detected barcodes
            Quagga.onDetected((result) => {
                const code = result.codeResult.code;

                // Display detected barcode
                document.getElementById('barcode-value').textContent = code;
                document.getElementById('scan-result').style.display = 'block';

                // Play success sound (optional)
                this.playBeep();

                // Call callback
                this.onScan(code, 'camera');

                // Auto-close after 2 seconds
                setTimeout(() => {
                    this.modal.hide();
                }, 2000);
            });
        });
    }

    stop() {
        if (this.quaggaStarted) {
            Quagga.stop();
            this.quaggaStarted = false;
        }

        // Hide scan result
        document.getElementById('scan-result').style.display = 'none';
        document.getElementById('barcode-value').textContent = '';
        document.getElementById('manual-barcode').value = '';
    }

    restart() {
        this.stop();
        setTimeout(() => this.start(), 100);
    }

    show() {
        this.modal.show();
    }

    hide() {
        this.modal.hide();
    }

    defaultOnScan(barcode, method) {
        console.log('Barcode scanned:', barcode, 'Method:', method);
        // Default behavior - can be overridden
        alert(`Barcode detected: ${barcode}`);
    }

    playBeep() {
        // Create a simple beep sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    }
}

// Global instance
window.barcodeScanner = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.barcodeScanner = new BarcodeScanner({
        onScan: function(barcode, method) {
            // Custom callback - can be overridden by page
            console.log('Barcode scanned:', barcode, 'via', method);

            // Trigger custom event for other scripts to listen to
            const event = new CustomEvent('barcodeScanned', {
                detail: { barcode, method }
            });
            document.dispatchEvent(event);
        }
    });

    window.barcodeScanner.init();
});
</script>
