<?php
/**
 * PADI Incident Report Form (Form 10120)
 * Mobile-optimized for use at incident scene
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            padding-bottom: 80px; /* Space for fixed submit button */
        }
        .incident-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        .incident-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #0066cc;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .severity-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .severity-badge input[type="radio"] {
            display: none;
        }
        .severity-badge.critical { background: #fee; color: #c00; }
        .severity-badge.high { background: #ffede0; color: #d84315; }
        .severity-badge.medium { background: #fff3cd; color: #856404; }
        .severity-badge.low { background: #e7f3ff; color: #0066cc; }
        .severity-badge input:checked + label {
            border-color: currentColor;
            transform: scale(1.05);
        }
        .photo-upload-btn {
            width: 100%;
            min-height: 120px;
            border: 3px dashed #dee2e6;
            border-radius: 12px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s;
        }
        .photo-upload-btn:hover {
            border-color: #0066cc;
            background: #e7f3ff;
        }
        .photo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin: 5px;
        }
        .gps-display {
            background: #e7f3ff;
            border: 2px solid #0066cc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        .fixed-submit {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .btn-lg-mobile {
            padding: 16px;
            font-size: 18px;
        }
    </style>
</head>
<body class="bg-light">

<div class="incident-container">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="h3 mb-2">
            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            Incident Report
        </h1>
        <p class="text-muted small">PADI Form 10120</p>
    </div>

    <form id="incident-form" method="POST" action="/incidents/submit" enctype="multipart/form-data">
        <?php if ($customer): ?>
        <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
        <input type="hidden" name="injured_person_name" value="<?= htmlspecialchars($customer['full_name']) ?>">
        <?php endif; ?>

        <!-- Incident Details -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-calendar-event"></i>
                Incident Details
            </div>

            <div class="mb-3">
                <label class="form-label">Date & Time *</label>
                <div class="row g-2">
                    <div class="col-7">
                        <input type="date" class="form-control" name="incident_date"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-5">
                        <input type="time" class="form-control" name="incident_time"
                               value="<?= date('H:i') ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Location *</label>
                <input type="text" class="form-control" name="incident_location"
                       placeholder="Dive site name or description" required>
            </div>

            <div class="mb-3">
                <label class="form-label">GPS Location</label>
                <button type="button" class="btn btn-outline-primary w-100" onclick="captureGPS()">
                    <i class="bi bi-geo-alt"></i> Capture Current Location
                </button>
                <div id="gps-display" class="gps-display" style="display: none;">
                    <div class="d-flex justify-content-between">
                        <span>Latitude:</span>
                        <strong id="lat-display">-</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Longitude:</span>
                        <strong id="lon-display">-</strong>
                    </div>
                </div>
                <input type="hidden" name="gps_latitude" id="gps-latitude">
                <input type="hidden" name="gps_longitude" id="gps-longitude">
            </div>

            <div class="mb-3">
                <label class="form-label">Severity Level *</label>
                <div class="d-flex flex-wrap gap-2">
                    <label class="severity-badge critical">
                        <input type="radio" name="severity_level" value="critical" required>
                        <span>Critical</span>
                    </label>
                    <label class="severity-badge high">
                        <input type="radio" name="severity_level" value="high">
                        <span>High</span>
                    </label>
                    <label class="severity-badge medium">
                        <input type="radio" name="severity_level" value="medium">
                        <span>Medium</span>
                    </label>
                    <label class="severity-badge low">
                        <input type="radio" name="severity_level" value="low">
                        <span>Low</span>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Incident Type *</label>
                <select class="form-select" name="incident_type" required>
                    <option value="">Select type...</option>
                    <option value="decompression_sickness">Decompression Sickness (DCS)</option>
                    <option value="air_embolism">Air Embolism</option>
                    <option value="near_drowning">Near Drowning</option>
                    <option value="panic">Panic Attack</option>
                    <option value="equipment_failure">Equipment Failure</option>
                    <option value="injury">Physical Injury</option>
                    <option value="lost_separated">Lost/Separated Diver</option>
                    <option value="marine_life">Marine Life Incident</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <!-- Photos from Scene -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-camera"></i>
                Photos (Optional)
            </div>

            <input type="file" id="photo-input" name="photos[]" accept="image/*"
                   capture="environment" multiple style="display: none;">

            <div class="photo-upload-btn" onclick="document.getElementById('photo-input').click()">
                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                    <i class="bi bi-camera-fill fs-1 text-muted"></i>
                    <span class="mt-2">Tap to Take/Upload Photos</span>
                    <small class="text-muted">Multiple photos allowed</small>
                </div>
            </div>

            <div id="photo-previews" class="d-flex flex-wrap mt-3"></div>
        </div>

        <!-- Injured Person -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-person"></i>
                Injured Person
            </div>

            <?php if (!$customer): ?>
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" name="injured_person_name" required>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <strong><?= htmlspecialchars($customer['full_name']) ?></strong>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Age</label>
                    <input type="number" class="form-control" name="injured_person_age">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Certification</label>
                    <input type="text" class="form-control" name="injured_person_certification"
                           placeholder="e.g., Open Water">
                </div>
            </div>
        </div>

        <!-- Incident Description -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-file-text"></i>
                Description
            </div>

            <div class="mb-3">
                <label class="form-label">What Happened? *</label>
                <textarea class="form-control" name="incident_description" rows="4"
                          placeholder="Describe the incident in detail..." required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Immediate Actions Taken *</label>
                <textarea class="form-control" name="immediate_actions" rows="3"
                          placeholder="What actions were taken immediately?" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Medical Treatment Provided</label>
                <textarea class="form-control" name="medical_treatment" rows="3"
                          placeholder="First aid, oxygen, etc."></textarea>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="emergency_services_called"
                       id="emergency-services">
                <label class="form-check-label" for="emergency-services">
                    Emergency Services Called (911, Coast Guard, etc.)
                </label>
            </div>

            <div id="emergency-details" style="display: none;">
                <label class="form-label">Emergency Service Details</label>
                <textarea class="form-control" name="emergency_service_details" rows="2"
                          placeholder="Which service, arrival time, etc."></textarea>
            </div>
        </div>

        <!-- Environmental Conditions -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-cloud"></i>
                Conditions
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Water Temp (°F)</label>
                    <input type="number" class="form-control" name="water_temperature">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Depth (ft)</label>
                    <input type="number" class="form-control" name="depth_at_incident">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Visibility (ft)</label>
                    <input type="number" class="form-control" name="visibility">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Current</label>
                    <select class="form-select" name="current_conditions">
                        <option value="none">None</option>
                        <option value="light">Light</option>
                        <option value="moderate">Moderate</option>
                        <option value="strong">Strong</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Environmental Conditions</label>
                <textarea class="form-control" name="environmental_conditions" rows="2"
                          placeholder="Weather, waves, etc."></textarea>
            </div>
        </div>

        <!-- Witness Information -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-people"></i>
                Witnesses (Optional)
            </div>

            <div class="mb-3">
                <label class="form-label">Witness 1 Name</label>
                <input type="text" class="form-control" name="witness_1_name">
            </div>
            <div class="mb-3">
                <label class="form-label">Witness 1 Contact</label>
                <input type="text" class="form-control" name="witness_1_contact"
                       placeholder="Phone or email">
            </div>
            <div class="mb-3">
                <label class="form-label">Witness 1 Statement</label>
                <textarea class="form-control" name="witness_1_statement" rows="3"></textarea>
            </div>

            <hr class="my-3">

            <div class="mb-3">
                <label class="form-label">Witness 2 Name</label>
                <input type="text" class="form-control" name="witness_2_name">
            </div>
            <div class="mb-3">
                <label class="form-label">Witness 2 Contact</label>
                <input type="text" class="form-control" name="witness_2_contact">
            </div>
            <div class="mb-3">
                <label class="form-label">Witness 2 Statement</label>
                <textarea class="form-control" name="witness_2_statement" rows="3"></textarea>
            </div>
        </div>

        <!-- Instructor Information -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-person-badge"></i>
                Instructor/Guide
            </div>

            <div class="mb-3">
                <label class="form-label">Instructor Name</label>
                <input type="text" class="form-control" name="instructor_name">
            </div>
            <div class="mb-3">
                <label class="form-label">Instructor PADI Number</label>
                <input type="text" class="form-control" name="instructor_padi_number">
            </div>
        </div>

        <!-- Reporter Information -->
        <div class="incident-section">
            <div class="section-title">
                <i class="bi bi-person-check"></i>
                Reported By
            </div>

            <div class="mb-3">
                <label class="form-label">Your Name *</label>
                <input type="text" class="form-control" name="reported_by_name" required>
            </div>
        </div>

    </form>
</div>

<!-- Fixed Submit Button -->
<div class="fixed-submit">
    <div class="incident-container">
        <button type="submit" form="incident-form" class="btn btn-danger btn-lg-mobile w-100">
            <i class="bi bi-send-fill"></i>
            Submit Incident Report
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// GPS Capture
function captureGPS() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            document.getElementById('gps-latitude').value = lat;
            document.getElementById('gps-longitude').value = lon;
            document.getElementById('lat-display').textContent = lat.toFixed(6);
            document.getElementById('lon-display').textContent = lon.toFixed(6);
            document.getElementById('gps-display').style.display = 'block';
        }, function(error) {
            alert('Unable to get GPS location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by your device');
    }
}

// Photo preview
document.getElementById('photo-input').addEventListener('change', function(e) {
    const previews = document.getElementById('photo-previews');
    previews.innerHTML = '';

    for (let i = 0; i < e.target.files.length; i++) {
        const file = e.target.files[i];
        const reader = new FileReader();

        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'photo-preview';
            previews.appendChild(img);
        };

        reader.readAsDataURL(file);
    }
});

// Show emergency details if checked
document.getElementById('emergency-services').addEventListener('change', function() {
    document.getElementById('emergency-details').style.display = this.checked ? 'block' : 'none';
});

// Severity badge selection
document.querySelectorAll('.severity-badge').forEach(badge => {
    badge.addEventListener('click', function() {
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>

</body>
</html>
