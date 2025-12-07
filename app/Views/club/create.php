<?php $this->layout('layouts/admin', ['title' => $title ?? 'Create Diving Club']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/clubs">Clubs</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Create New Diving Club</h5>
                </div>
                <div class="card-body">
                    <form action="/store/clubs" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="club_name" class="form-label">Club Name *</label>
                                <input type="text" class="form-control" id="club_name" name="club_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="club_code" class="form-label">Club Code *</label>
                                <input type="text" class="form-control" id="club_code" name="club_code" required
                                       placeholder="e.g., SCUBA-001" maxlength="20">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="club_type" class="form-label">Club Type</label>
                                <select class="form-select" id="club_type" name="club_type">
                                    <option value="general">General Diving Club</option>
                                    <option value="technical">Technical Diving</option>
                                    <option value="freediving">Freediving</option>
                                    <option value="underwater_photography">Underwater Photography</option>
                                    <option value="conservation">Marine Conservation</option>
                                    <option value="wreck">Wreck Diving</option>
                                    <option value="cave">Cave Diving</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="membership_type" class="form-label">Membership Type</label>
                                <select class="form-select" id="membership_type" name="membership_type">
                                    <option value="open">Open (Anyone can join)</option>
                                    <option value="invitation">By Invitation Only</option>
                                    <option value="application">Application Required</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="meeting_schedule" class="form-label">Meeting Schedule</label>
                                <input type="text" class="form-control" id="meeting_schedule" name="meeting_schedule"
                                       placeholder="e.g., First Saturday of each month">
                            </div>
                            <div class="col-md-6">
                                <label for="meeting_location" class="form-label">Meeting Location</label>
                                <input type="text" class="form-control" id="meeting_location" name="meeting_location">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="min_certification_level" class="form-label">Min Certification Level</label>
                                <select class="form-select" id="min_certification_level" name="min_certification_level">
                                    <option value="">No Requirement</option>
                                    <option value="Open Water">Open Water</option>
                                    <option value="Advanced Open Water">Advanced Open Water</option>
                                    <option value="Rescue Diver">Rescue Diver</option>
                                    <option value="Divemaster">Divemaster</option>
                                    <option value="Instructor">Instructor</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="annual_dues" class="form-label">Annual Dues ($)</label>
                                <input type="number" class="form-control" id="annual_dues" name="annual_dues"
                                       min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="discount_percentage" class="form-label">Member Discount (%)</label>
                                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage"
                                       min="0" max="100" step="0.01" value="0">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/clubs" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Create Club
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Club Types</h6>
                    <ul class="small mb-0">
                        <li><strong>General</strong> - Open to all diving enthusiasts</li>
                        <li><strong>Technical</strong> - Advanced diving techniques</li>
                        <li><strong>Freediving</strong> - Breath-hold diving</li>
                        <li><strong>Photography</strong> - Underwater photography focus</li>
                        <li><strong>Conservation</strong> - Marine conservation activities</li>
                        <li><strong>Wreck</strong> - Wreck exploration diving</li>
                        <li><strong>Cave</strong> - Cave and cavern diving</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
