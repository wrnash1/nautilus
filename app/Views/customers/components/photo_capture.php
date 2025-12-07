<?php
/**
 * Customer Photo Capture Component
 * Universal camera capture that works on desktop, tablet, and mobile
 * Usage: Include this file and call initPhotoCapture(customerId)
 */
?>

<div class="photo-capture-container">
    <!-- Current Photo Display -->
    <div class="current-photo text-center">
        <div class="photo-frame">
            <?php if (!empty($customer['photo_path'])): ?>
                <img id="customerPhoto" src="<?= htmlspecialchars($customer['photo_path']) ?>" alt="Customer Photo" class="customer-photo-display">
            <?php else: ?>
                <div class="no-photo-placeholder">
                    <i class="bi bi-person-circle"></i>
                    <p>No photo</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Capture Buttons -->
    <div class="photo-actions text-center mt-3">
        <button type="button" id="capturePhotoBtn" class="btn btn-primary btn-lg">
            <i class="bi bi-camera-fill"></i> Take Photo
        </button>
        <button type="button" id="uploadPhotoBtn" class="btn btn-secondary btn-lg">
            <i class="bi bi-upload"></i> Upload Photo
        </button>
        <?php if (!empty($customer['photo_path'])): ?>
        <button type="button" id="removePhotoBtn" class="btn btn-outline-danger btn-lg">
            <i class="bi bi-trash"></i> Remove
        </button>
        <?php endif; ?>
    </div>

    <!-- Hidden input for file upload -->
    <input type="file" id="photoFileInput" accept="image/*" style="display: none;">

    <!-- Hidden input to store photo path/data -->
    <input type="hidden" id="photoData" name="photo_data">
</div>

<style>
.photo-capture-container {
    padding: 20px;
}

.photo-frame {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    border: 3px solid #dee2e6;
    border-radius: 50%;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.customer-photo-display {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-photo-placeholder {
    text-align: center;
    color: #6c757d;
}

.no-photo-placeholder i {
    font-size: 5rem;
}

.no-photo-placeholder p {
    margin: 0;
    font-size: 0.9rem;
}

.photo-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.photo-actions .btn {
    min-width: 140px;
}

@media (max-width: 576px) {
    .photo-frame {
        width: 150px;
        height: 150px;
    }

    .photo-actions .btn {
        flex: 1;
        min-width: auto;
    }
}

/* Loading indicator */
.photo-uploading {
    position: relative;
}

.photo-uploading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script src="/js/camera-capture.js"></script>
<script>
(function() {
    const customerId = <?= $customer['id'] ?? 0 ?>;
    let cameraCapture = null;

    function initPhotoCapture() {
        // Initialize camera capture
        cameraCapture = new CameraCapture({
            previewElement: document.getElementById('customerPhoto'),
            targetInput: document.getElementById('photoData'),
            uploadEndpoint: '/api/customers/' + customerId + '/photo',
            maxWidth: 800,
            maxHeight: 800,
            quality: 0.85,
            facingMode: 'user', // Front camera for customer photos
            onSuccess: function(result) {
                if (result.success && result.photo_path) {
                    // Update photo display
                    updatePhotoDisplay(result.photo_path);
                    showNotification('Photo uploaded successfully!', 'success');
                }
            },
            onError: function(error) {
                showNotification(error, 'error');
            }
        });

        // Capture photo button
        document.getElementById('capturePhotoBtn').addEventListener('click', function() {
            if (cameraCapture) {
                cameraCapture.detectAndCapture();
            }
        });

        // Upload photo button
        document.getElementById('uploadPhotoBtn').addEventListener('click', function() {
            document.getElementById('photoFileInput').click();
        });

        // File input change
        document.getElementById('photoFileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadPhotoFile(file);
            }
        });

        // Remove photo button
        const removeBtn = document.getElementById('removePhotoBtn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (confirm('Remove customer photo?')) {
                    removePhoto();
                }
            });
        }
    }

    function uploadPhotoFile(file) {
        const formData = new FormData();
        formData.append('photo', file);

        showLoading();

        fetch('/api/customers/' + customerId + '/photo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            hideLoading();
            if (result.success && result.photo_path) {
                updatePhotoDisplay(result.photo_path);
                showNotification('Photo uploaded successfully!', 'success');
            } else {
                showNotification('Upload failed', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Upload error: ' + error.message, 'error');
        });
    }

    function removePhoto() {
        showLoading();

        fetch('/api/customers/' + customerId + '/photo', {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(result => {
            hideLoading();
            if (result.success) {
                // Reset photo display
                const photoFrame = document.querySelector('.photo-frame');
                photoFrame.innerHTML = `
                    <div class="no-photo-placeholder">
                        <i class="bi bi-person-circle"></i>
                        <p>No photo</p>
                    </div>
                `;
                showNotification('Photo removed', 'success');

                // Hide remove button
                const removeBtn = document.getElementById('removePhotoBtn');
                if (removeBtn) {
                    removeBtn.style.display = 'none';
                }
            } else {
                showNotification('Failed to remove photo', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Error: ' + error.message, 'error');
        });
    }

    function updatePhotoDisplay(photoPath) {
        const photoFrame = document.querySelector('.photo-frame');
        photoFrame.innerHTML = `
            <img id="customerPhoto" src="${photoPath}" alt="Customer Photo" class="customer-photo-display">
        `;

        // Show remove button if it doesn't exist
        const removeBtn = document.getElementById('removePhotoBtn');
        if (removeBtn) {
            removeBtn.style.display = 'inline-block';
        } else {
            const actions = document.querySelector('.photo-actions');
            const newRemoveBtn = document.createElement('button');
            newRemoveBtn.type = 'button';
            newRemoveBtn.id = 'removePhotoBtn';
            newRemoveBtn.className = 'btn btn-outline-danger btn-lg';
            newRemoveBtn.innerHTML = '<i class="bi bi-trash"></i> Remove';
            newRemoveBtn.addEventListener('click', function() {
                if (confirm('Remove customer photo?')) {
                    removePhoto();
                }
            });
            actions.appendChild(newRemoveBtn);
        }
    }

    function showLoading() {
        const container = document.querySelector('.photo-capture-container');
        container.classList.add('photo-uploading');
    }

    function hideLoading() {
        const container = document.querySelector('.photo-capture-container');
        container.classList.remove('photo-uploading');
    }

    function showNotification(message, type) {
        // Simple notification - can be replaced with toast library
        const className = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${className} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 10000;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPhotoCapture);
    } else {
        initPhotoCapture();
    }
})();
</script>
