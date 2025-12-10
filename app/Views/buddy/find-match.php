<?php $this->layout('layouts/admin', ['title' => $title ?? 'Find Buddy Match']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/buddies">Buddy Pairs</a></li>
                    <li class="breadcrumb-item active">Find Match</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-search me-2"></i>Find Buddy Match</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Select a diver to find potential buddy matches based on certification level, experience, and preferences.
                    </p>

                    <form id="matchForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="diver_id" class="form-label">Select Diver</label>
                                <select class="form-select" id="diver_id" name="diver_id">
                                    <option value="">Choose a diver...</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary d-block" onclick="findMatches()">
                                    <i class="bi bi-search me-1"></i>Find Matches
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="matchResults" class="d-none">
                        <hr>
                        <h5>Potential Matches</h5>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Buddy matching considers certification level, dive experience, and location preferences.
                            Manual review is recommended for optimal pairing.
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Diver</th>
                                        <th>Certification</th>
                                        <th>Experience</th>
                                        <th>Compatibility</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="matchTableBody">
                                    <!-- Results loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="noMatches" class="d-none text-center py-4">
                        <i class="bi bi-emoji-frown display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No suitable matches found. Try adjusting criteria or browse all divers.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-lightbulb me-2"></i>Matching Tips</h6>
                    <ul class="small mb-0">
                        <li>Match similar certification levels when possible</li>
                        <li>Consider experience (total dive count)</li>
                        <li>Check for compatible diving interests</li>
                        <li>Review any medical considerations</li>
                        <li>Consider language preferences for safety communication</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2"></i>Safety First</h6>
                    <p class="small mb-0">
                        Always verify that both divers have current certifications and medical clearances
                        before pairing for any dive activity.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function findMatches() {
    const diverId = document.getElementById('diver_id').value;
    if (!diverId) {
        alert('Please select a diver first');
        return;
    }

    // For now, show sample matches - in production this would be an AJAX call
    document.getElementById('matchResults').classList.remove('d-none');
    document.getElementById('noMatches').classList.add('d-none');

    // Sample data display
    const tbody = document.getElementById('matchTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted">
                <i class="bi bi-info-circle me-2"></i>
                Select from the available divers list above and create a manual pairing.
                Automated matching will be available in a future update.
            </td>
        </tr>
    `;
}
</script>
