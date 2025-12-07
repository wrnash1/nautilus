<?php $this->layout('layouts/admin', ['title' => $title ?? 'Create Conservation Initiative']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/conservation">Conservation</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-globe-americas me-2"></i>Create Conservation Initiative</h5>
                </div>
                <div class="card-body">
                    <form action="/store/conservation" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="initiative_name" class="form-label">Initiative Name *</label>
                                <input type="text" class="form-control" id="initiative_name" name="initiative_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="initiative_type" class="form-label">Type *</label>
                                <select class="form-select" id="initiative_type" name="initiative_type" required>
                                    <option value="cleanup">Beach/Ocean Cleanup</option>
                                    <option value="reef_restoration">Reef Restoration</option>
                                    <option value="species_monitoring">Species Monitoring</option>
                                    <option value="education">Environmental Education</option>
                                    <option value="research">Scientific Research</option>
                                    <option value="advocacy">Advocacy & Awareness</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Describe the initiative goals and activities..."></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="partner_organizations" class="form-label">Partner Organizations</label>
                                <input type="text" class="form-control" id="partner_organizations" name="partner_organizations"
                                       placeholder="Comma-separated (e.g., WWF, PADI AWARE)">
                            </div>
                            <div class="col-md-6">
                                <label for="certification_program" class="form-label">Certification Program</label>
                                <input type="text" class="form-control" id="certification_program" name="certification_program"
                                       placeholder="e.g., Green Fins, Blue Star, Project AWARE">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date (if not ongoing)</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="col-md-4">
                                <label for="meeting_frequency" class="form-label">Meeting Frequency</label>
                                <input type="text" class="form-control" id="meeting_frequency" name="meeting_frequency"
                                       placeholder="e.g., Monthly, Weekly">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_ongoing" name="is_ongoing" checked>
                                <label class="form-check-label" for="is_ongoing">This is an ongoing initiative</label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/store/conservation" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Create Initiative
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-globe-americas me-2"></i>Why Conservation?</h6>
                    <p class="small mb-0">
                        Dive shops play a crucial role in marine conservation. By tracking your initiatives,
                        you can measure impact, engage customers, and demonstrate environmental commitment.
                    </p>
                </div>
            </div>

            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-lightbulb me-2"></i>Initiative Ideas</h6>
                    <ul class="small mb-0">
                        <li>Dive Against Debris cleanups</li>
                        <li>Coral nursery programs</li>
                        <li>Fish count surveys</li>
                        <li>Eco-diving workshops</li>
                        <li>Plastic-free initiatives</li>
                        <li>Reef monitoring projects</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
