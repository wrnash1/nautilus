<?php $this->layout('layouts/admin', ['title' => $title ?? 'Club Events']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/clubs">Clubs</a></li>
                    <li class="breadcrumb-item"><a href="/store/clubs/<?= $club['id'] ?? '' ?>"><?= htmlspecialchars($club['club_name'] ?? 'Club') ?></a></li>
                    <li class="breadcrumb-item active">Events</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars($club['club_name'] ?? '') ?> - Events</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
            <i class="bi bi-plus-lg me-1"></i>Create Event
        </button>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No events found for this club</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                Create First Event
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Registrations</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <?php
                                            $eventDate = strtotime($event['event_date']);
                                            $isPast = $eventDate < strtotime('today');
                                            $isToday = date('Y-m-d', $eventDate) === date('Y-m-d');
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($event['event_name']) ?></strong>
                                                <?php if ($event['description']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($event['description'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($event['event_type'] ?? 'dive') ?></span>
                                            </td>
                                            <td>
                                                <?= date('M j, Y', $eventDate) ?>
                                                <?php if ($event['start_time']): ?>
                                                    <br><small class="text-muted"><?= date('g:i A', strtotime($event['start_time'])) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($event['location'] ?? '-') ?></td>
                                            <td>
                                                <?= $event['registered_count'] ?? 0 ?>
                                                <?php if ($event['max_participants']): ?>
                                                    / <?= $event['max_participants'] ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                $<?= number_format($event['member_price'] ?? 0, 2) ?>
                                                <?php if (($event['non_member_price'] ?? 0) > 0): ?>
                                                    <br><small class="text-muted">Non-member: $<?= number_format($event['non_member_price'], 2) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($isPast): ?>
                                                    <span class="badge bg-secondary">Past</span>
                                                <?php elseif ($isToday): ?>
                                                    <span class="badge bg-warning">Today</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Upcoming</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/store/clubs/<?= $club['id'] ?? '' ?>/events" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="event_name" class="form-label">Event Name *</label>
                            <input type="text" class="form-control" id="event_name" name="event_name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="event_type" class="form-label">Event Type</label>
                            <select class="form-select" id="event_type" name="event_type">
                                <option value="dive">Dive</option>
                                <option value="training">Training</option>
                                <option value="social">Social</option>
                                <option value="conservation">Conservation</option>
                                <option value="competition">Competition</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="event_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time">
                        </div>
                        <div class="col-md-4">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                        <div class="col-md-4">
                            <label for="max_participants" class="form-label">Max Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" min="0">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="registration_deadline" class="form-label">Registration Deadline</label>
                            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline">
                        </div>
                        <div class="col-md-4">
                            <label for="member_price" class="form-label">Member Price ($)</label>
                            <input type="number" class="form-control" id="member_price" name="member_price" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="non_member_price" class="form-label">Non-Member Price ($)</label>
                            <input type="number" class="form-control" id="non_member_price" name="non_member_price" min="0" step="0.01" value="0">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
