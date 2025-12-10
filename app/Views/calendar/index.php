<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Nautilus</title>
    <link href="/assets/css/professional-theme.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .calendar-container {
            background: var(--bg-page);
            min-height: 100vh;
            padding: var(--spacing-lg);
        }

        .calendar-header {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }

        .calendar-title {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .calendar-actions {
            display: flex;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
        }

        .calendar-main {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .fc {
            font-family: var(--font-family-base);
        }

        .fc .fc-button {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            transition: all var(--transition-fast);
        }

        .fc .fc-button:hover {
            background-color: var(--primary-blue-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .fc .fc-button:disabled {
            opacity: 0.6;
        }

        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: var(--primary-blue-dark);
        }

        .fc-event {
            border: none;
            border-radius: var(--border-radius);
            padding: 4px 8px;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
        }

        .fc-daygrid-event {
            margin: 2px;
        }

        .fc-timegrid-event {
            box-shadow: var(--shadow-sm);
        }

        /* Event Type Colors */
        .fc-event.event-course {
            background: var(--primary-blue);
            border-left: 4px solid var(--deep-blue);
        }

        .fc-event.event-trip {
            background: var(--ocean-teal);
            border-left: 4px solid var(--ocean-teal-dark);
        }

        .fc-event.event-rental {
            background: var(--success-green);
            border-left: 4px solid #2E7D32;
        }

        .fc-event.event-maintenance {
            background: var(--warning-yellow);
            color: var(--text-primary);
            border-left: 4px solid #F57C00;
        }

        .fc-event.event-meeting {
            background: var(--gray-600);
            border-left: 4px solid var(--gray-800);
        }

        .resource-legend {
            display: flex;
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-sm);
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: var(--border-radius);
            border-left: 4px solid rgba(0,0,0,0.3);
        }

        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: 400px;
            background: white;
            box-shadow: var(--shadow-xl);
            transform: translateX(100%);
            transition: transform var(--transition-base);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .sidebar-body {
            padding: var(--spacing-lg);
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition-base);
            z-index: 999;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .quick-add-form {
            display: grid;
            gap: var(--spacing-md);
        }

        .resource-availability {
            margin-top: var(--spacing-lg);
            padding: var(--spacing-md);
            background: var(--bg-hover);
            border-radius: var(--border-radius);
        }

        .resource-item {
            padding: var(--spacing-sm);
            margin-bottom: var(--spacing-xs);
            background: white;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .resource-item.available { border-left: 3px solid var(--success-green); }
        .resource-item.busy { border-left: 3px solid var(--error-red); }

        .filter-buttons {
            display: flex;
            gap: var(--spacing-xs);
            flex-wrap: wrap;
        }

        .filter-button {
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: var(--font-size-sm);
            transition: all var(--transition-fast);
        }

        .filter-button.active {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .filter-button:hover {
            border-color: var(--primary-blue);
        }
    </style>
</head>
<body>

<div class="calendar-container">
    <div class="calendar-header">
        <h1 class="calendar-title">
            <i class="bi bi-calendar-event"></i>
            Calendar & Scheduling
        </h1>

        <div class="calendar-actions">
            <div class="filter-buttons">
                <button class="filter-button active" data-filter="all">All Events</button>
                <button class="filter-button" data-filter="course">Courses</button>
                <button class="filter-button" data-filter="trip">Trips</button>
                <button class="filter-button" data-filter="rental">Rentals</button>
                <button class="filter-button" data-filter="maintenance">Maintenance</button>
            </div>

            <button class="btn btn-primary" onclick="openQuickAdd()">
                <i class="bi bi-plus-circle"></i>
                Add Event
            </button>

            <button class="btn btn-outline-primary" onclick="showResources()">
                <i class="bi bi-boxes"></i>
                Resources
            </button>
        </div>
    </div>

    <div class="calendar-main">
        <div id="calendar"></div>

        <div class="resource-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: var(--primary-blue); border-left-color: var(--deep-blue);"></div>
                <span>Courses</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--ocean-teal); border-left-color: var(--ocean-teal-dark);"></div>
                <span>Trips</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--success-green); border-left-color: #2E7D32;"></div>
                <span>Rentals</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--warning-yellow); border-left-color: #F57C00;"></div>
                <span>Maintenance</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: var(--gray-600); border-left-color: var(--gray-800);"></div>
                <span>Meetings</span>
            </div>
        </div>
    </div>
</div>

<!-- Event Sidebar -->
<div class="sidebar-overlay" onclick="closeSidebar()"></div>
<div class="sidebar" id="event-sidebar">
    <div class="sidebar-header">
        <h3 id="sidebar-title">Event Details</h3>
        <button class="btn btn-ghost" onclick="closeSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="sidebar-body" id="sidebar-content">
        <!-- Dynamic content loaded here -->
    </div>
</div>

<!-- Quick Add Sidebar -->
<div class="sidebar" id="quickadd-sidebar">
    <div class="sidebar-header">
        <h3>Add New Event</h3>
        <button class="btn btn-ghost" onclick="closeSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="sidebar-body">
        <form class="quick-add-form" onsubmit="createEvent(event)">
            <div class="form-group">
                <label class="form-label">Event Type</label>
                <select class="form-control" name="event_type" required>
                    <option value="course">Course</option>
                    <option value="trip">Trip</option>
                    <option value="rental">Rental</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="meeting">Meeting</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>

            <div class="form-group">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" class="form-control" name="start_datetime" required>
            </div>

            <div class="form-group">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-control" name="end_datetime" required>
            </div>

            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Max Participants</label>
                <input type="number" class="form-control" name="max_participants" min="1">
            </div>

            <div class="resource-availability">
                <h5>Resource Availability</h5>
                <div id="resource-check"></div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="bi bi-check-circle"></i>
                Create Event
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let calendar;
let currentFilter = 'all';

// Sample Events Data
const sampleEvents = [
    {
        id: 1,
        title: 'Open Water Course',
        start: '2025-11-20T09:00:00',
        end: '2025-11-20T17:00:00',
        type: 'course',
        location: 'Classroom A + Pool',
        participants: '6/8',
        instructor: 'John Smith',
        className: 'event-course'
    },
    {
        id: 2,
        title: 'Wreck Dive Trip',
        start: '2025-11-22T07:00:00',
        end: '2025-11-22T16:00:00',
        type: 'trip',
        location: 'SS Thistlegorm',
        participants: '12/20',
        boat: 'Dive Boat Alpha',
        className: 'event-trip'
    },
    {
        id: 3,
        title: 'Equipment Rental - Smith',
        start: '2025-11-21T10:00:00',
        end: '2025-11-23T18:00:00',
        type: 'rental',
        customer: 'Mike Smith',
        equipment: 'Full Scuba Set #12',
        className: 'event-rental'
    },
    {
        id: 4,
        title: 'Compressor Maintenance',
        start: '2025-11-24T14:00:00',
        end: '2025-11-24T17:00:00',
        type: 'maintenance',
        technician: 'Dave Johnson',
        className: 'event-maintenance'
    },
    {
        id: 5,
        title: 'Advanced OW - Pool Session',
        start: '2025-11-25T09:00:00',
        end: '2025-11-25T12:00:00',
        type: 'course',
        location: 'Training Pool',
        participants: '4/6',
        instructor: 'Sarah Williams',
        className: 'event-course'
    }
];

// Initialize Calendar
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        editable: true,
        droppable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        events: sampleEvents,
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        select: function(info) {
            openQuickAdd(info.startStr, info.endStr);
        },
        eventDrop: function(info) {
            updateEvent(info.event);
        },
        eventResize: function(info) {
            updateEvent(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.title;
        }
    });

    calendar.render();

    // Filter buttons
    document.querySelectorAll('.filter-button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterEvents();
        });
    });
});

function filterEvents() {
    calendar.getEvents().forEach(event => {
        if (currentFilter === 'all' || event.extendedProps.type === currentFilter) {
            event.setProp('display', 'auto');
        } else {
            event.setProp('display', 'none');
        }
    });
}

function showEventDetails(event) {
    const sidebar = document.getElementById('event-sidebar');
    const content = document.getElementById('sidebar-content');

    content.innerHTML = `
        <div class="pro-card">
            <div class="pro-card-header">
                <div>
                    <h3 class="pro-card-title">${event.title}</h3>
                    <div class="pro-card-subtitle">${event.extendedProps.type || 'Event'}</div>
                </div>
                <span class="badge badge-primary">${event.extendedProps.type}</span>
            </div>
            <div class="pro-card-body">
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-clock"></i> Time</label>
                    <div>${formatDateTime(event.start)} - ${formatDateTime(event.end)}</div>
                </div>

                ${event.extendedProps.location ? `
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-geo-alt"></i> Location</label>
                    <div>${event.extendedProps.location}</div>
                </div>
                ` : ''}

                ${event.extendedProps.participants ? `
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-people"></i> Participants</label>
                    <div>${event.extendedProps.participants}</div>
                </div>
                ` : ''}

                ${event.extendedProps.instructor ? `
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-person-badge"></i> Instructor</label>
                    <div>${event.extendedProps.instructor}</div>
                </div>
                ` : ''}

                ${event.extendedProps.boat ? `
                <div class="form-group">
                    <label class="form-label"><i class="bi bi-tsunami"></i> Boat</label>
                    <div>${event.extendedProps.boat}</div>
                </div>
                ` : ''}

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" style="flex:1;">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Manage
                    </button>
                    <button class="btn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    openSidebar(sidebar);
}

function openQuickAdd(startStr, endStr) {
    const sidebar = document.getElementById('quickadd-sidebar');

    if (startStr) {
        sidebar.querySelector('[name="start_datetime"]').value = startStr.substring(0, 16);
    }
    if (endStr) {
        sidebar.querySelector('[name="end_datetime"]').value = endStr.substring(0, 16);
    }

    openSidebar(sidebar);
    checkResourceAvailability();
}

function checkResourceAvailability() {
    const resourceCheck = document.getElementById('resource-check');
    resourceCheck.innerHTML = `
        <div class="resource-item available">
            <span><i class="bi bi-tsunami"></i> Dive Boat Alpha</span>
            <span class="badge badge-success">Available</span>
        </div>
        <div class="resource-item available">
            <span><i class="bi bi-building"></i> Classroom A</span>
            <span class="badge badge-success">Available</span>
        </div>
        <div class="resource-item busy">
            <span><i class="bi bi-water"></i> Training Pool</span>
            <span class="badge badge-danger">Busy</span>
        </div>
    `;
}

function openSidebar(sidebar) {
    document.querySelector('.sidebar-overlay').classList.add('active');
    // Close other sidebars
    document.querySelectorAll('.sidebar').forEach(s => s.classList.remove('open'));
    sidebar.classList.add('open');
}

function closeSidebar() {
    document.querySelector('.sidebar-overlay').classList.remove('active');
    document.querySelectorAll('.sidebar').forEach(s => s.classList.remove('open'));
}

function showResources() {
    alert('Resource management view - showing boats, classrooms, equipment availability');
}

function createEvent(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const newEvent = {
        id: Date.now(),
        title: formData.get('title'),
        start: formData.get('start_datetime'),
        end: formData.get('end_datetime'),
        type: formData.get('event_type'),
        location: formData.get('location'),
        description: formData.get('description'),
        className: 'event-' + formData.get('event_type')
    };

    calendar.addEvent(newEvent);
    closeSidebar();
    e.target.reset();

    alert('Event created successfully!');
}

function updateEvent(event) {
    console.log('Event updated:', event.id);
    // Send to server
}

function formatDateTime(date) {
    if (!date) return '';
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

</body>
</html>
