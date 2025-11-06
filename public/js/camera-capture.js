/**
 * Universal Camera Capture Component
 * Works on desktop (webcam), mobile (camera), and tablet (camera)
 * Progressive enhancement with fallback to file upload
 */

class CameraCapture {
    constructor(options = {}) {
        this.options = {
            targetInput: options.targetInput || null, // Hidden input to store base64 or file path
            previewElement: options.previewElement || null, // Image element for preview
            captureButton: options.captureButton || null, // Button to trigger capture
            uploadEndpoint: options.uploadEndpoint || '/api/upload-photo',
            maxWidth: options.maxWidth || 800,
            maxHeight: options.maxHeight || 800,
            quality: options.quality || 0.85,
            facingMode: options.facingMode || 'user', // 'user' (front) or 'environment' (back)
            onSuccess: options.onSuccess || null,
            onError: options.onError || null
        };

        this.stream = null;
        this.videoElement = null;
        this.canvasElement = null;

        this.init();
    }

    init() {
        // Create necessary DOM elements if they don't exist
        if (!this.videoElement) {
            this.videoElement = document.createElement('video');
            this.videoElement.setAttribute('autoplay', '');
            this.videoElement.setAttribute('playsinline', ''); // Important for iOS
            this.videoElement.style.display = 'none';
        }

        if (!this.canvasElement) {
            this.canvasElement = document.createElement('canvas');
            this.canvasElement.style.display = 'none';
        }

        // Append to body if not already there
        if (!this.videoElement.parentNode) {
            document.body.appendChild(this.videoElement);
        }
        if (!this.canvasElement.parentNode) {
            document.body.appendChild(this.canvasElement);
        }

        // Set up capture button if provided
        if (this.options.captureButton) {
            this.options.captureButton.addEventListener('click', () => {
                this.detectAndCapture();
            });
        }
    }

    /**
     * Detect device type and use appropriate capture method
     */
    detectAndCapture() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile && this.supportsFileInputCapture()) {
            // Mobile device - use native camera input
            this.captureViaNativeInput();
        } else if (this.supportsGetUserMedia()) {
            // Desktop or device with webcam - use getUserMedia
            this.captureViaWebcam();
        } else {
            // Fallback to file upload
            this.captureViaFileUpload();
        }
    }

    /**
     * Check if browser supports file input with capture attribute
     */
    supportsFileInputCapture() {
        const input = document.createElement('input');
        input.setAttribute('capture', 'camera');
        return 'capture' in input;
    }

    /**
     * Check if browser supports getUserMedia
     */
    supportsGetUserMedia() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    /**
     * Mobile: Use native camera input
     */
    captureViaNativeInput() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.capture = 'camera'; // Use camera directly

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.processImageFile(file);
            }
        });

        input.click();
    }

    /**
     * Desktop: Use webcam via getUserMedia
     */
    async captureViaWebcam() {
        try {
            // Request camera access
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: this.options.facingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            // Display video
            this.videoElement.srcObject = this.stream;
            this.videoElement.style.display = 'block';

            // Create a modal or overlay to show video and capture button
            this.showWebcamPreview();

        } catch (error) {
            console.error('Error accessing camera:', error);
            if (this.options.onError) {
                this.options.onError('Camera access denied: ' + error.message);
            }
            // Fallback to file upload
            this.captureViaFileUpload();
        }
    }

    /**
     * Show webcam preview with capture button
     */
    showWebcamPreview() {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'camera-capture-modal';
        modal.innerHTML = `
            <div class="camera-capture-modal-content">
                <div class="camera-capture-video-container">
                    <video id="cameraPreviewVideo" autoplay playsinline></video>
                </div>
                <div class="camera-capture-controls">
                    <button type="button" class="btn btn-primary btn-lg" id="takePictureBtn">
                        <i class="bi bi-camera-fill"></i> Take Picture
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" id="cancelCameraBtn">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                </div>
            </div>
        `;

        // Add styles if not already present
        if (!document.getElementById('camera-capture-styles')) {
            const style = document.createElement('style');
            style.id = 'camera-capture-styles';
            style.textContent = `
                .camera-capture-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.9);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .camera-capture-modal-content {
                    width: 90%;
                    max-width: 800px;
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                }
                .camera-capture-video-container {
                    position: relative;
                    width: 100%;
                    background: black;
                    border-radius: 8px;
                    overflow: hidden;
                    margin-bottom: 20px;
                }
                .camera-capture-video-container video {
                    width: 100%;
                    height: auto;
                    display: block;
                }
                .camera-capture-controls {
                    display: flex;
                    gap: 12px;
                    justify-content: center;
                }
                .camera-capture-controls button {
                    flex: 1;
                    max-width: 200px;
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(modal);

        // Connect video stream to modal video element
        const videoInModal = modal.querySelector('#cameraPreviewVideo');
        videoInModal.srcObject = this.stream;

        // Take picture button
        modal.querySelector('#takePictureBtn').addEventListener('click', () => {
            this.captureFromVideo();
            document.body.removeChild(modal);
        });

        // Cancel button
        modal.querySelector('#cancelCameraBtn').addEventListener('click', () => {
            this.stopCamera();
            document.body.removeChild(modal);
        });
    }

    /**
     * Capture frame from video stream
     */
    captureFromVideo() {
        const video = this.videoElement;
        const canvas = this.canvasElement;

        // Set canvas dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw current video frame to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Stop camera
        this.stopCamera();

        // Convert to blob and process
        canvas.toBlob((blob) => {
            this.processImageBlob(blob);
        }, 'image/jpeg', this.options.quality);
    }

    /**
     * Fallback: Regular file upload
     */
    captureViaFileUpload() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.processImageFile(file);
            }
        });

        input.click();
    }

    /**
     * Process image from File object
     */
    processImageFile(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                this.resizeAndProcess(img);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Process image from Blob
     */
    processImageBlob(blob) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                this.resizeAndProcess(img);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(blob);
    }

    /**
     * Resize image and process
     */
    resizeAndProcess(img) {
        const canvas = this.canvasElement;
        let width = img.width;
        let height = img.height;

        // Calculate new dimensions maintaining aspect ratio
        if (width > this.options.maxWidth || height > this.options.maxHeight) {
            const ratio = Math.min(this.options.maxWidth / width, this.options.maxHeight / height);
            width = width * ratio;
            height = height * ratio;
        }

        canvas.width = width;
        canvas.height = height;

        // Draw resized image
        const context = canvas.getContext('2d');
        context.drawImage(img, 0, 0, width, height);

        // Get base64 data
        const dataUrl = canvas.toDataURL('image/jpeg', this.options.quality);

        // Update preview if element provided
        if (this.options.previewElement) {
            this.options.previewElement.src = dataUrl;
            this.options.previewElement.style.display = 'block';
        }

        // Update target input if provided
        if (this.options.targetInput) {
            this.options.targetInput.value = dataUrl;
        }

        // Upload to server
        this.uploadImage(dataUrl);
    }

    /**
     * Upload image to server
     */
    async uploadImage(dataUrl) {
        try {
            // Convert base64 to blob
            const blob = await this.dataUrlToBlob(dataUrl);

            // Create form data
            const formData = new FormData();
            formData.append('photo', blob, 'capture.jpg');

            // Upload
            const response = await fetch(this.options.uploadEndpoint, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Upload failed: ' + response.statusText);
            }

            const result = await response.json();

            if (this.options.onSuccess) {
                this.options.onSuccess(result);
            }

            return result;

        } catch (error) {
            console.error('Upload error:', error);
            if (this.options.onError) {
                this.options.onError('Upload failed: ' + error.message);
            }
        }
    }

    /**
     * Convert data URL to Blob
     */
    async dataUrlToBlob(dataUrl) {
        const response = await fetch(dataUrl);
        return await response.blob();
    }

    /**
     * Stop camera stream
     */
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.videoElement) {
            this.videoElement.srcObject = null;
            this.videoElement.style.display = 'none';
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        this.stopCamera();
        if (this.videoElement && this.videoElement.parentNode) {
            this.videoElement.parentNode.removeChild(this.videoElement);
        }
        if (this.canvasElement && this.canvasElement.parentNode) {
            this.canvasElement.parentNode.removeChild(this.canvasElement);
        }
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CameraCapture;
}
