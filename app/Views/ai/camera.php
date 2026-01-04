<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-camera"></i> AI Camera Scanner</h2>
    <div>
        <button class="btn btn-outline-secondary" onclick="switchMode('product')">
            <i class="bi bi-upc-scan"></i> Product
        </button>
        <button class="btn btn-outline-secondary" onclick="switchMode('cert')">
            <i class="bi bi-card-heading"></i> Cert Card
        </button>
        <button class="btn btn-outline-secondary" onclick="switchMode('serial')">
            <i class="bi bi-hash"></i> Serial #
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="modeTitle">Product Scanner</h5>
                <span id="cameraStatus" class="badge bg-secondary">Initializing...</span>
            </div>
            <div class="card-body p-0 position-relative">
                <video id="cameraFeed" autoplay playsinline
                    style="width:100%; max-height:60vh; background:#000;"></video>
                <canvas id="captureCanvas" style="display:none;"></canvas>

                <!-- Overlay for targeting -->
                <div id="scanOverlay" style="position:absolute; top:0; left:0; right:0; bottom:0; pointer-events:none;">
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); 
                                width:60%; height:40%; border:3px dashed rgba(0,255,0,0.7); border-radius:8px;"></div>
                    <div style="position:absolute; bottom:20px; left:50%; transform:translateX(-50%); 
                                background:rgba(0,0,0,0.7); color:#fff; padding:8px 16px; border-radius:4px;">
                        <span id="scanInstruction">Point camera at product barcode or item</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 justify-content-center">
                    <button id="captureBtn" class="btn btn-primary btn-lg" onclick="captureImage()">
                        <i class="bi bi-camera-fill"></i> Capture
                    </button>
                    <button id="flashBtn" class="btn btn-outline-secondary" onclick="toggleFlash()">
                        <i class="bi bi-lightning"></i>
                    </button>
                    <button id="switchCameraBtn" class="btn btn-outline-secondary" onclick="switchCamera()">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Recognition Result</h5>
            </div>
            <div class="card-body" id="resultContainer">
                <p class="text-muted text-center">Capture an image to see results</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Scans</h5>
            </div>
            <ul class="list-group list-group-flush" id="recentScans">
                <li class="list-group-item text-muted">No recent scans</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .product-match {
        background: #d4edda;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .product-match h6 {
        margin: 0 0 5px;
    }

    .product-match .price {
        font-size: 1.25rem;
        font-weight: bold;
        color: #28a745;
    }

    .scan-loading {
        text-align: center;
        padding: 40px;
    }

    .scan-loading .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>

<script>
    let currentMode = 'product';
    let stream = null;
    let useFrontCamera = false;

    const modeConfig = {
        product: {
            title: 'Product Scanner',
            instruction: 'Point camera at product barcode or item',
            endpoint: '/store/ai/camera/recognize-product'
        },
        cert: {
            title: 'Certification Card Scanner',
            instruction: 'Position cert card within the frame',
            endpoint: '/store/ai/camera/scan-cert'
        },
        serial: {
            title: 'Serial Number Scanner',
            instruction: 'Focus on equipment serial number',
            endpoint: '/store/ai/camera/scan-serial'
        }
    };

    async function initCamera() {
        try {
            const constraints = {
                video: {
                    facingMode: useFrontCamera ? 'user' : 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            };

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            document.getElementById('cameraFeed').srcObject = stream;
            document.getElementById('cameraStatus').textContent = 'Ready';
            document.getElementById('cameraStatus').className = 'badge bg-success';
        } catch (err) {
            console.error('Camera error:', err);
            document.getElementById('cameraStatus').textContent = 'Camera Error';
            document.getElementById('cameraStatus').className = 'badge bg-danger';
        }
    }

    function switchMode(mode) {
        currentMode = mode;
        const config = modeConfig[mode];
        document.getElementById('modeTitle').textContent = config.title;
        document.getElementById('scanInstruction').textContent = config.instruction;
        document.getElementById('resultContainer').innerHTML = '<p class="text-muted text-center">Capture an image to see results</p>';

        // Update button states
        document.querySelectorAll('[onclick^="switchMode"]').forEach(btn => {
            btn.className = btn.onclick.toString().includes(mode)
                ? 'btn btn-primary'
                : 'btn btn-outline-secondary';
        });
    }

    function switchCamera() {
        useFrontCamera = !useFrontCamera;
        initCamera();
    }

    function toggleFlash() {
        if (stream) {
            const track = stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();
            if (capabilities.torch) {
                const settings = track.getSettings();
                track.applyConstraints({ advanced: [{ torch: !settings.torch }] });
            }
        }
    }

    async function captureImage() {
        const video = document.getElementById('cameraFeed');
        const canvas = document.getElementById('captureCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);

        const imageData = canvas.toDataURL('image/jpeg', 0.8);

        // Show loading
        document.getElementById('resultContainer').innerHTML = `
        <div class="scan-loading">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Analyzing image...</p>
        </div>
    `;

        try {
            const config = modeConfig[currentMode];
            const response = await fetch(config.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'image=' + encodeURIComponent(imageData)
            });

            const result = await response.json();
            displayResult(result);
            addToRecentScans(result);
        } catch (err) {
            document.getElementById('resultContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> Recognition failed. Please try again.
            </div>
        `;
        }
    }

    function displayResult(result) {
        let html = '';

        if (result.success) {
            switch (currentMode) {
                case 'product':
                    if (result.product) {
                        html = displayProductResult(result);
                    } else if (result.barcode) {
                        html = `<div class="alert alert-warning">
                        <i class="bi bi-upc"></i> Barcode found: <strong>${result.barcode}</strong><br>
                        <small>No matching product in inventory.</small>
                    </div>`;
                    } else {
                        html = `<div class="alert alert-info">
                        <h6>AI Recognition</h6>
                        <p><strong>Labels:</strong> ${result.labels?.map(l => l.description).join(', ') || 'None'}</p>
                        <p><strong>Text:</strong> ${result.text || 'None'}</p>
                    </div>`;
                    }
                    break;

                case 'cert':
                    html = displayCertResult(result);
                    break;

                case 'serial':
                    html = displaySerialResult(result);
                    break;
            }
        } else {
            html = `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> ${result.error || 'Recognition failed'}
        </div>`;
        }

        document.getElementById('resultContainer').innerHTML = html;
    }

    function displayProductResult(result) {
        const product = result.product;
        if (Array.isArray(product?.matches)) {
            let html = `<p class="text-muted">Found ${product.matches.length} possible matches:</p>`;
            product.matches.forEach(p => {
                html += `
                <div class="product-match">
                    <h6>${p.name}</h6>
                    <p class="mb-1"><small class="text-muted">SKU: ${p.sku}</small></p>
                    <p class="price mb-2">$${parseFloat(p.price).toFixed(2)}</p>
                    <button class="btn btn-success btn-sm" onclick="addToCart(${p.id})">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                </div>
            `;
            });
            return html;
        } else {
            return `
            <div class="product-match">
                <h6>${product.name}</h6>
                <p class="mb-1"><small class="text-muted">SKU: ${product.sku}</small></p>
                <p class="price mb-2">$${parseFloat(product.price).toFixed(2)}</p>
                <button class="btn btn-success btn-sm" onclick="addToCart(${product.id})">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        `;
        }
    }

    function displayCertResult(result) {
        const parsed = result.parsed || {};
        return `
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-card-heading"></i> Certification Details</h6>
                <table class="table table-sm">
                    <tr><td>Agency</td><td><strong>${parsed.agency || 'Unknown'}</strong></td></tr>
                    <tr><td>Level</td><td><strong>${parsed.cert_level || 'Unknown'}</strong></td></tr>
                    <tr><td>Cert #</td><td><strong>${parsed.cert_number || 'Not found'}</strong></td></tr>
                    <tr><td>Date</td><td><strong>${parsed.cert_date || 'Not found'}</strong></td></tr>
                </table>
                <button class="btn btn-primary btn-sm" onclick="addCertToCustomer()">
                    <i class="bi bi-person-plus"></i> Add to Customer
                </button>
            </div>
        </div>
    `;
    }

    function displaySerialResult(result) {
        let html = `
        <div class="alert alert-success">
            <h6><i class="bi bi-hash"></i> Serial Number Found</h6>
            <h4 class="mb-0">${result.serial_number}</h4>
        </div>
    `;

        if (result.equipment) {
            html += `
            <div class="card">
                <div class="card-body">
                    <h6>Equipment Found</h6>
                    <p><strong>${result.equipment.name || result.equipment.equipment_code}</strong></p>
                    <p class="mb-1">Type: ${result.equipment.type}</p>
                    <p class="mb-0">Status: <span class="badge bg-info">${result.equipment.status}</span></p>
                </div>
            </div>
        `;
        } else {
            html += `<p class="text-muted">Equipment not found in database.</p>`;
        }

        return html;
    }

    function addToRecentScans(result) {
        const list = document.getElementById('recentScans');
        const time = new Date().toLocaleTimeString();
        const item = document.createElement('li');
        item.className = 'list-group-item d-flex justify-content-between';
        item.innerHTML = `
        <span>${result.success ? (result.product?.name || result.serial_number || 'Scanned') : 'Failed'}</span>
        <small class="text-muted">${time}</small>
    `;

        if (list.querySelector('.text-muted')) {
            list.innerHTML = '';
        }
        list.insertBefore(item, list.firstChild);

        // Keep only last 5
        while (list.children.length > 5) {
            list.removeChild(list.lastChild);
        }
    }

    function addToCart(productId) {
        window.location.href = '/store/pos?add=' + productId;
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initCamera);
</script>