<?php
$pageTitle = 'Add Employee/Instructor';
$activeMenu = 'staff';
$user = currentUser();

// Get certification agencies for instructors
$agencies = [];
$certifications = [];
try {
    $db = \App\Core\Database::getInstance();
    $agencies = $db->query("SELECT * FROM certification_agencies ORDER BY name")->fetchAll();
    $certifications = $db->query("SELECT * FROM certifications ORDER BY name")->fetchAll();
} catch (Exception $e) {
    // Agencies not loaded yet
}

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/staff">Staff</a></li>
            <li class="breadcrumb-item active">Add Staff Member</li>
        </ol>
    </nav>
    <h2><i class="bi bi-person-plus"></i> Add Staff Member</h2>
</div>

<form method="POST" action="/staff" id="staffForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="row">
        <div class="col-md-8">
            <!-- Staff Type Selection -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase"></i> Employment Type</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="staff_type"
                                           id="typeEmployee" value="employee" checked onchange="toggleStaffType()">
                                    <label class="form-check-label" for="typeEmployee">
                                        <i class="bi bi-person"></i> Employee
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="staff_type"
                                           id="typeInstructor" value="instructor" onchange="toggleStaffType()">
                                    <label class="form-check-label" for="typeInstructor">
                                        <i class="bi bi-mortarboard"></i> Instructor
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="staff_type"
                                           id="typeContractor" value="contractor" onchange="toggleStaffType()">
                                    <label class="form-check-label" for="typeContractor">
                                        <i class="bi bi-briefcase"></i> Contractor
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="staff_type"
                                           id="typeManager" value="manager" onchange="toggleStaffType()">
                                    <label class="form-check-label" for="typeManager">
                                        <i class="bi bi-person-badge"></i> Manager
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ssn_last4" class="form-label">SSN (Last 4 digits)</label>
                            <input type="text" class="form-control" id="ssn_last4" name="ssn_last4" maxlength="4" pattern="[0-9]{4}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Employment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position/Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position" name="position" required
                                   placeholder="e.g., Sales Associate, Dive Master, Instructor">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <option value="Sales">Sales</option>
                                <option value="Instruction">Instruction</option>
                                <option value="Retail">Retail</option>
                                <option value="Service">Equipment Service</option>
                                <option value="Management">Management</option>
                                <option value="Administration">Administration</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employment_status" class="form-label">Status</label>
                            <select class="form-select" id="employment_status" name="employment_status">
                                <option value="active" selected>Active</option>
                                <option value="on_leave">On Leave</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compensation -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Compensation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="pay_type" class="form-label">Pay Type</label>
                            <select class="form-select" id="pay_type" name="pay_type">
                                <option value="hourly">Hourly</option>
                                <option value="salary">Salary</option>
                                <option value="commission">Commission Only</option>
                                <option value="contractor">Contractor Rate</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pay_rate" class="form-label">Pay Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="pay_rate" name="pay_rate" step="0.01" min="0">
                                <span class="input-group-text">/hr</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="commission_rate" class="form-label">Commission Rate</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="commission_rate" name="commission_rate" step="0.01" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor Certifications (Only for Instructors) -->
            <div class="card mb-3" id="instructorCertifications" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-award"></i> Instructor Certifications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Add instructor certifications and specialties</p>

                    <div id="certificationsList">
                        <div class="certification-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Certification Agency</label>
                                    <select class="form-select" name="instructor_certs[0][agency_id]">
                                        <option value="">Select Agency</option>
                                        <?php foreach ($agencies as $agency): ?>
                                        <option value="<?= $agency['id'] ?>"><?= htmlspecialchars($agency['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Instructor Level</label>
                                    <input type="text" class="form-control" name="instructor_certs[0][level]"
                                           placeholder="e.g., Open Water Instructor, Master Instructor">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Instructor Number</label>
                                    <input type="text" class="form-control" name="instructor_certs[0][number]">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Issue Date</label>
                                    <input type="date" class="form-control" name="instructor_certs[0][issue_date]">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Expiration Date</label>
                                    <input type="date" class="form-control" name="instructor_certs[0][expiry_date]">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="instructor_certs[0][status]">
                                        <option value="active">Active</option>
                                        <option value="expired">Expired</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Specialties/Ratings</label>
                                <input type="text" class="form-control" name="instructor_certs[0][specialties]"
                                       placeholder="e.g., Nitrox, Wreck, Deep, Night">
                                <small class="text-muted">Separate multiple specialties with commas</small>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-success btn-sm" onclick="addCertification()">
                        <i class="bi bi-plus-circle"></i> Add Another Certification
                    </button>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-telephone"></i> Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                        <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship"
                               placeholder="e.g., Spouse, Parent, Sibling">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-sticky"></i> Notes</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="notes" name="notes" rows="4"
                              placeholder="Additional notes about this staff member..."></textarea>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Staff Member
                </button>
                <a href="/staff" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle"></i> Staff Type Guide</h6>

                    <p><strong>Employee:</strong> Regular full-time or part-time staff members (sales, retail, service technicians)</p>

                    <p><strong>Instructor:</strong> Certified dive instructors who teach courses. Requires instructor certifications.</p>

                    <p><strong>Contractor:</strong> Independent contractors or freelancers (paid per project/service)</p>

                    <p><strong>Manager:</strong> Management and supervisory staff</p>

                    <hr>

                    <h6><i class="bi bi-mortarboard"></i> For Instructors</h6>
                    <p class="small">Make sure to add all instructor certifications and specialties. This helps with course scheduling and customer trust.</p>

                    <hr>

                    <h6><i class="bi bi-cash-coin"></i> Compensation</h6>
                    <p class="small">
                        <strong>Hourly:</strong> Regular hourly employees<br>
                        <strong>Salary:</strong> Annual salary (enter yearly amount)<br>
                        <strong>Commission:</strong> Sales commission percentage<br>
                        <strong>Contractor:</strong> Contract rate per service
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let certCount = 1;

function toggleStaffType() {
    const staffType = document.querySelector('input[name="staff_type"]:checked').value;
    const instructorSection = document.getElementById('instructorCertifications');

    if (staffType === 'instructor') {
        instructorSection.style.display = 'block';
    } else {
        instructorSection.style.display = 'none';
    }
}

function addCertification() {
    const template = document.querySelector('.certification-item').cloneNode(true);

    // Update field names with new index
    template.querySelectorAll('input, select').forEach(field => {
        if (field.name) {
            field.name = field.name.replace('[0]', '[' + certCount + ']');
            field.value = '';
        }
    });

    document.getElementById('certificationsList').appendChild(template);
    certCount++;
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
