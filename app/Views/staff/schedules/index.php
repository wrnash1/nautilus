<?php
$pageTitle = 'Staff Schedules';
$activeMenu = 'staff';

// Add FullCalendar CSS and JS
$additionalCss = '
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<style>
.fc {
    max-width: 100%;
}
.fc-event {
    cursor: pointer;
}
.staff-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-right: 0.5rem;
}
.schedule-modal .modal-dialog {
    max-width: 600px;
}
</style>
';

ob_start();
?>

<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="bi bi-calendar3"></i> Staff Schedules
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/staff">Staff</a></li>
                    <li class="breadcrumb-item active">Schedules</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if (hasPermission('staff.create')): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle"></i> Add Schedule
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="exportSchedules()">
                <i class="bi bi-download"></i> Export
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Calendar Card -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-week"></i> Schedule Calendar
                </h5>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeView('dayGridMonth')">
                        Month
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary active" onclick="changeView('timeGridWeek')">
                        Week
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeView('timeGridDay')">
                        Day
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeView('listWeek')">
                        List
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Add/Edit Schedule Modal -->
<div class="modal fade schedule-modal" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Add Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST" action="/staff/schedules">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="schedule_id" id="schedule_id">

                    <div class="mb-3">
                        <label for="staff_id" class="form-label">Staff Member *</label>
                        <select name="staff_id" id="staff_id" class="form-select" required>
                            <option value="">Select Employee...</option>
                            <?php foreach ($staff as $employee): ?>
                            <option value="<?= $employee['id'] ?>">
                                <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                - <?= htmlspecialchars($employee['role_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="schedule_date" class="form-label">Date *</label>
                            <input type="date" name="schedule_date" id="schedule_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role/Position</label>
                            <select name="role" id="role" class="form-select">
                                <option value="">Select Role...</option>
                                <option value="cashier">Cashier</option>
                                <option value="instructor">Instructor</option>
                                <option value="dive_master">Dive Master</option>
                                <option value="equipment_tech">Equipment Technician</option>
                                <option value="sales">Sales Associate</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shift_start" class="form-label">Start Time *</label>
                            <input type="time" name="shift_start" id="shift_start" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shift_end" class="form-label">End Time *</label>
                            <input type="time" name="shift_end" id="shift_end" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="break_duration" class="form-label">Break (minutes)</label>
                            <input type="number" name="break_duration" id="break_duration" class="form-control" min="0" value="30">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="Main Store">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <small>Conflicts with existing schedules will be checked before saving.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Schedule Details Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event"></i> Schedule Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php if (hasPermission('staff.delete')): ?>
                <button type="button" class="btn btn-danger" onclick="deleteSchedule()">
                    <i class="bi bi-trash"></i> Delete
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script>
let calendar;
let currentScheduleId = null;

document.addEventListener("DOMContentLoaded", function() {
    const calendarEl = document.getElementById("calendar");

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "timeGridWeek",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: ""
        },
        slotMinTime: "06:00:00",
        slotMaxTime: "22:00:00",
        height: "auto",
        events: "/staff/schedules/data",
        eventClick: function(info) {
            viewScheduleDetails(info.event);
        },
        dateClick: function(info) {
            document.getElementById("schedule_date").value = info.dateStr;
            const modal = new bootstrap.Modal(document.getElementById("addScheduleModal"));
            modal.show();
        },
        eventColor: "#0d6efd",
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.title + "\n" +
                           info.event.start.toLocaleTimeString() + " - " +
                           info.event.end.toLocaleTimeString();
        }
    });

    calendar.render();

    // Form submission
    document.getElementById("scheduleForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        fetch("/staff/schedules", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById("addScheduleModal"));
                modal.hide();
                calendar.refetchEvents();
                showToast("Schedule saved successfully", "success");
            } else {
                showToast(result.error || "Failed to save schedule", "danger");
            }
        })
        .catch(error => {
            showToast("Error saving schedule: " + error.message, "danger");
        });
    });
});

function changeView(view) {
    calendar.changeView(view);
    // Update active button
    document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.remove("active"));
    event.target.classList.add("active");
}

function viewScheduleDetails(event) {
    currentScheduleId = event.id;

    const content = `
        <div class="schedule-detail">
            <h6><i class="bi bi-person"></i> ${event.title}</h6>
            <hr>
            <p><strong>Date:</strong> ${event.start.toLocaleDateString()}</p>
            <p><strong>Time:</strong> ${event.start.toLocaleTimeString()} - ${event.end.toLocaleTimeString()}</p>
            <p><strong>Duration:</strong> ${calculateDuration(event.start, event.end)}</p>
            ${event.extendedProps.role ? `<p><strong>Role:</strong> ${event.extendedProps.role}</p>` : ""}
            ${event.extendedProps.location ? `<p><strong>Location:</strong> ${event.extendedProps.location}</p>` : ""}
            ${event.extendedProps.break_duration ? `<p><strong>Break:</strong> ${event.extendedProps.break_duration} minutes</p>` : ""}
            ${event.extendedProps.notes ? `<p><strong>Notes:</strong><br>${event.extendedProps.notes}</p>` : ""}
        </div>
    `;

    document.getElementById("scheduleDetailsContent").innerHTML = content;
    const modal = new bootstrap.Modal(document.getElementById("viewScheduleModal"));
    modal.show();
}

function calculateDuration(start, end) {
    const diff = end - start;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}h ${minutes}m`;
}

function deleteSchedule() {
    if (!currentScheduleId || !confirm("Are you sure you want to delete this schedule?")) {
        return;
    }

    fetch(`/staff/schedules/${currentScheduleId}/delete`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("viewScheduleModal"));
            modal.hide();
            calendar.refetchEvents();
            showToast("Schedule deleted successfully", "success");
        } else {
            showToast(result.error || "Failed to delete schedule", "danger");
        }
    })
    .catch(error => {
        showToast("Error deleting schedule: " + error.message, "danger");
    });
}

function exportSchedules() {
    const start = calendar.view.currentStart.toISOString();
    const end = calendar.view.currentEnd.toISOString();
    window.location.href = `/staff/schedules/export?start=${start}&end=${end}`;
}

function showToast(message, type) {
    const toast = document.createElement("div");
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = "9999";
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
';

require __DIR__ . '/../../layouts/app.php';
?>
