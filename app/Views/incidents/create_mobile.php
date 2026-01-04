<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Incident Report - Nautilus</title>
    <link href="/assets/css/professional-theme.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            padding: 0;
            margin: 0;
            background: var(--bg-page);
        }

        .mobile-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--deep-blue) 100%);
            color: white;
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }

        .mobile-header h1 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }

        .mobile-header-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .incident-form {
            padding: 16px;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .camera-button {
            width: 100%;
            padding: 60px 20px;
            background: var(--gray-100);
            border: 2px dashed var(--gray-400);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .camera-button:hover {
            border-color: var(--primary-blue);
            background: var(--bg-active);
        }

        .camera-button i {
            font-size: 48px;
            color: var(--gray-500);
            margin-bottom: 12px;
        }

        .photo-preview {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
        }

        .photo-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
        }

        .photo-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-preview-item .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: var(--error-red);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        .gps-info {
            background: var(--bg-active);
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gps-info i {
            color: var(--primary-blue);
            font-size: 20px;
        }

        .severity-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .severity-option {
            padding: 16px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            background: white;
        }

        .severity-option input[type="radio"] {
            display: none;
        }

        .severity-option.minor { border-color: var(--info-blue); }
        .severity-option.moderate { border-color: var(--warning-yellow); }
        .severity-option.serious { border-color: var(--coral-orange); }
        .severity-option.critical { border-color: var(--error-red); }
        .severity-option.fatal { border-color: var(--gray-900); }

        .severity-option input[type="radio"]:checked + .severity-label {
            font-weight: 600;
        }

        .severity-option.minor input[type="radio"]:checked ~ * {
            background: var(--info-blue);
            color: white;
        }

        .severity-option.moderate input[type="radio"]:checked ~ * {
            background: var(--warning-yellow);
            color: var(--text-primary);
        }

        .severity-option.serious input[type="radio"]:checked ~ * {
            background: var(--coral-orange);
            color: white;
        }

        .severity-option.critical input[type="radio"]:checked ~ * {
            background: var(--error-red);
            color: white;
        }

        .severity-option.fatal input[type="radio"]:checked ~ * {
            background: var(--gray-900);
            color: white;
        }

        .severity-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .severity-label {
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 8px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .checkbox-item:hover {
            background: var(--bg-hover);
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-item label {
            flex: 1;
            cursor: pointer;
            margin: 0;
        }

        .submit-section {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 16px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        .progress-bar {
            height: 4px;
            background: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-blue), var(--ocean-teal));
            transition: width var(--transition-base);
        }

        .voice-note-button {
            background: var(--bg-hover);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .voice-note-button:hover {
            border-color: var(--primary-blue);
            background: var(--bg-active);
        }

        .voice-note-button.recording {
            background: var(--error-red);
            color: white;
            border-color: var(--error-red);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <h1>
        <i class="bi bi-exclamation-triangle-fill"></i>
        Incident Report
    </h1>
    <div class="mobile-header-subtitle">PADI Form 10120 - Mobile Edition</div>
</div>

<form id="incident-form" class="incident-form">

    <!-- Basic Information -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-info-circle-fill text-primary"></i>
            Basic Information
        </div>

        <div class="form-group">
            <label class="form-label">Incident Date & Time</label>
            <input type="datetime-local" class="form-control" name="incident_datetime" required>
        </div>

        <div class="form-group">
            <label class="form-label">Location</label>
            <input type="text" class="form-control" name="location" placeholder="Dive site or address" required>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="getGPSLocation()">
                <i class="bi bi-geo-alt-fill"></i>
                Use Current Location
            </button>
            <div id="gps-info" class="gps-info mt-2" style="display: none;">
                <i class="bi bi-check-circle-fill"></i>
                <span id="gps-coords"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Severity</label>
            <div class="severity-selector">
                <div class="severity-option minor">
                    <input type="radio" name="severity" value="minor" id="severity-minor">
                    <label for="severity-minor" class="severity-label">
                        <div class="severity-icon">😌</div>
                        <div>Minor</div>
                    </label>
                </div>
                <div class="severity-option moderate">
                    <input type="radio" name="severity" value="moderate" id="severity-moderate">
                    <label for="severity-moderate" class="severity-label">
                        <div class="severity-icon">😐</div>
                        <div>Moderate</div>
                    </label>
                </div>
                <div class="severity-option serious">
                    <input type="radio" name="severity" value="serious" id="severity-serious">
                    <label for="severity-serious" class="severity-label">
                        <div class="severity-icon">😟</div>
                        <div>Serious</div>
                    </label>
                </div>
                <div class="severity-option critical">
                    <input type="radio" name="severity" value="critical" id="severity-critical">
                    <label for="severity-critical" class="severity-label">
                        <div class="severity-icon">😨</div>
                        <div>Critical</div>
                    </label>
                </div>
                <div class="severity-option fatal">
                    <input type="radio" name="severity" value="fatal" id="severity-fatal">
                    <label for="severity-fatal" class="severity-label">
                        <div class="severity-icon">💀</div>
                        <div>Fatal</div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Diver Information -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-person-fill text-primary"></i>
            Diver Information
        </div>

        <div class="form-group">
            <label class="form-label">Diver Name</label>
            <input type="text" class="form-control" name="diver_name" required>
        </div>

        <div class="form-group">
            <label class="form-label">Certification Level</label>
            <select class="form-control" name="cert_level">
                <option value="">Select level...</option>
                <option value="Open Water">Open Water Diver</option>
                <option value="Advanced">Advanced Open Water</option>
                <option value="Rescue">Rescue Diver</option>
                <option value="Divemaster">Divemaster</option>
                <option value="Instructor">Instructor</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Total Dives</label>
            <input type="number" class="form-control" name="total_dives" min="0">
        </div>
    </div>

    <!-- Incident Description -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-file-text-fill text-primary"></i>
            Incident Description
        </div>

        <div class="form-group">
            <label class="form-label">What Happened?</label>
            <textarea class="form-control" name="description" rows="6" required
                      placeholder="Describe the incident in detail..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Voice Note (Optional)</label>
            <button type="button" class="voice-note-button" id="voice-button">
                <i class="bi bi-mic-fill" style="font-size: 24px;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 600;">Tap to Record</div>
                    <div style="font-size: 12px; opacity: 0.7;">Voice description</div>
                </div>
                <span id="recording-time" style="display: none;">00:00</span>
            </button>
        </div>
    </div>

    <!-- Medical Response -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-heart-pulse-fill text-danger"></i>
            Medical Response
        </div>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <input type="checkbox" name="first_aid" id="first_aid">
                <label for="first_aid">First Aid Provided</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" name="oxygen" id="oxygen">
                <label for="oxygen">Oxygen Administered</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" name="cpr" id="cpr">
                <label for="cpr">CPR Performed</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" name="aed" id="aed">
                <label for="aed">AED Used</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" name="hospital" id="hospital">
                <label for="hospital">Transported to Hospital</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" name="chamber" id="chamber">
                <label for="chamber">Hyperbaric Chamber Treatment</label>
            </div>
        </div>
    </div>

    <!-- Photos/Evidence -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="bi bi-camera-fill text-primary"></i>
            Photos & Evidence
        </div>

        <div class="form-group">
            <input type="file" id="photo-input" accept="image/*" capture="environment" multiple style="display: none;">
            <div class="camera-button" onclick="document.getElementById('photo-input').click()">
                <i class="bi bi-camera-fill"></i>
                <div style="font-weight: 600; margin-bottom: 4px;">Take Photos</div>
                <div style="font-size: 14px; color: var(--text-secondary);">Tap to capture evidence</div>
            </div>
            <div id="photo-preview" class="photo-preview"></div>
        </div>
    </div>

</form>

<!-- Submit Section -->
<div class="submit-section">
    <div class="progress-bar">
        <div class="progress-bar-fill" id="progress-fill" style="width: 0%"></div>
    </div>
    <div style="display: flex; gap: 12px;">
        <button type="button" class="btn btn-outline-primary" style="flex: 1;" onclick="saveDraft()">
            <i class="bi bi-save"></i>
            Save Draft
        </button>
        <button type="submit" form="incident-form" class="btn btn-danger" style="flex: 2;">
            <i class="bi bi-send-fill"></i>
            Submit Report
        </button>
    </div>
</div>

<script>
let photos = [];
let gpsCoords = null;
let recording = false;
let recordingStart = null;
let recordingInterval = null;

// GPS Location
function getGPSLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    const button = event.target.closest('button');
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Getting location...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            gpsCoords = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };

            document.getElementById('gps-info').style.display = 'flex';
            document.getElementById('gps-coords').textContent =
                `${gpsCoords.latitude.toFixed(6)}, ${gpsCoords.longitude.toFixed(6)}`;

            button.innerHTML = '<i class="bi bi-check-circle-fill"></i> Location Captured';
            button.classList.add('btn-success');
        },
        (error) => {
            alert('Error getting location: ' + error.message);
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Use Current Location';
        }
    );
}

// Photo Handling
document.getElementById('photo-input').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            photos.push({
                file: file,
                preview: e.target.result
            });
            updatePhotoPreview();
        };
        reader.readAsDataURL(file);
    });
});

function updatePhotoPreview() {
    const container = document.getElementById('photo-preview');
    container.innerHTML = photos.map((photo, index) => `
        <div class="photo-preview-item">
            <img src="${photo.preview}" alt="Photo ${index + 1}">
            <button type="button" class="remove-photo" onclick="removePhoto(${index})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
    updateProgress();
}

function removePhoto(index) {
    photos.splice(index, 1);
    updatePhotoPreview();
}

// Voice Recording
document.getElementById('voice-button').addEventListener('click', function() {
    if (!recording) {
        startRecording();
    } else {
        stopRecording();
    }
});

function startRecording() {
    recording = true;
    recordingStart = Date.now();
    document.getElementById('voice-button').classList.add('recording');
    document.getElementById('recording-time').style.display = 'block';

    recordingInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - recordingStart) / 1000);
        const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        document.getElementById('recording-time').textContent = `${minutes}:${seconds}`;
    }, 1000);

    // Implement actual voice recording here
}

function stopRecording() {
    recording = false;
    document.getElementById('voice-button').classList.remove('recording');
    clearInterval(recordingInterval);

    const button = document.getElementById('voice-button');
    button.querySelector('div div').textContent = 'Voice Note Recorded';
    button.querySelector('i').className = 'bi bi-check-circle-fill';
}

// Progress Calculation
function updateProgress() {
    const formData = new FormData(document.getElementById('incident-form'));
    let filled = 0;
    const total = 10; // Total required fields

    if (formData.get('incident_datetime')) filled++;
    if (formData.get('location')) filled++;
    if (formData.get('severity')) filled++;
    if (formData.get('diver_name')) filled++;
    if (formData.get('description')) filled++;
    if (gpsCoords) filled++;
    if (photos.length > 0) filled++;

    const progress = (filled / total) * 100;
    document.getElementById('progress-fill').style.width = progress + '%';
}

// Auto-update progress
document.getElementById('incident-form').addEventListener('input', updateProgress);
document.getElementById('incident-form').addEventListener('change', updateProgress);

// Form Submission
document.getElementById('incident-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!confirm('Submit this incident report? This will notify management and may trigger PADI reporting.')) {
        return;
    }

    const formData = new FormData(this);

    // Add GPS coordinates
    if (gpsCoords) {
        formData.append('gps_latitude', gpsCoords.latitude);
        formData.append('gps_longitude', gpsCoords.longitude);
    }

    // Add photos
    photos.forEach((photo, index) => {
        formData.append(`photos[${index}]`, photo.file);
    });

    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner spinner-sm"></span> Submitting...';

    try {
        const response = await fetch('/api/incidents/submit', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Incident report submitted successfully. Report #' + result.incident_number);
            window.location.href = '/incidents/' + result.id;
        } else {
            alert('Error: ' + result.message);
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-send-fill"></i> Submit Report';
        }
    } catch (error) {
        alert('Network error. Please try again.');
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="bi bi-send-fill"></i> Submit Report';
    }
});

function saveDraft() {
    localStorage.setItem('incident_draft', JSON.stringify({
        formData: new FormData(document.getElementById('incident-form')),
        photos: photos.map(p => p.preview),
        gpsCoords: gpsCoords,
        timestamp: new Date().toISOString()
    }));

    alert('Draft saved locally');
}

// Load draft on page load
window.addEventListener('load', function() {
    const draft = localStorage.getItem('incident_draft');
    if (draft && confirm('Load saved draft?')) {
        // Implement draft loading
    }
});
</script>

</body>
</html>
