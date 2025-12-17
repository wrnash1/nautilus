<?php
$pageTitle = 'Create Customer Tag';
$activeMenu = 'customers';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-tag-fill"></i> Create New Tag</h1>
        <a href="/store/customers/tags" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Tags
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tag Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/store/customers/tags/store" id="createTagForm">
                        <div class="mb-3">
                            <label class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g., VIP, Wholesale, Instructor">
                            <small class="text-muted">A short, descriptive name for this tag</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" name="color" value="#3498db" id="tagColor">
                            <small class="text-muted">Choose a color to identify this tag visually</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icon (Bootstrap Icon class)</label>
                            <input type="text" class="form-control" name="icon" placeholder="bi-star-fill" id="tagIcon">
                            <small class="text-muted">
                                Browse icons at <a href="https://icons.getbootstrap.com/" target="_blank">icons.getbootstrap.com</a>
                                (e.g., bi-star-fill, bi-briefcase-fill, bi-award-fill)
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="What does this tag represent? When should it be used?"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/store/customers/tags" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Tag
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Preview</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Tag will appear as:</strong></p>
                    <div class="mb-3">
                        <span class="badge" id="tagPreview" style="background-color: #3498db; color: white; font-size: 1rem; padding: 8px 12px;">
                            <i class="bi bi-tag-fill" id="previewIcon"></i>
                            <span id="previewName">Tag Name</span>
                        </span>
                    </div>

                    <hr>

                    <p class="mb-2"><strong>Suggested Use Cases:</strong></p>
                    <ul class="small mb-0">
                        <li><strong>VIP:</strong> High-value customers, special treatment</li>
                        <li><strong>Wholesale:</strong> Bulk buyers, special pricing</li>
                        <li><strong>Instructor:</strong> Certified dive instructors</li>
                        <li><strong>New Customer:</strong> First-time buyers</li>
                        <li><strong>Inactive:</strong> Hasn't purchased in 6+ months</li>
                        <li><strong>Corporate:</strong> Business accounts</li>
                        <li><strong>Newsletter:</strong> Subscribed to communications</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Tag Templates -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Templates</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Click a template to auto-fill the form:</p>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('VIP', '#f39c12', 'bi-star-fill', 'VIP Customer - Highest priority')">
                                <i class="bi bi-star-fill"></i> VIP
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Wholesale', '#3498db', 'bi-briefcase-fill', 'Wholesale Customer')">
                                <i class="bi bi-briefcase-fill"></i> Wholesale
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Instructor', '#2ecc71', 'bi-mortarboard-fill', 'Certified Diving Instructor')">
                                <i class="bi bi-mortarboard-fill"></i> Instructor
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('New Customer', '#1abc9c', 'bi-person-plus-fill', 'New or first-time customer')">
                                <i class="bi bi-person-plus-fill"></i> New Customer
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Corporate', '#34495e', 'bi-building-fill', 'Corporate or business account')">
                                <i class="bi bi-building-fill"></i> Corporate
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Newsletter', '#9b59b6', 'bi-envelope-fill', 'Subscribed to newsletter')">
                                <i class="bi bi-envelope-fill"></i> Newsletter
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Inactive', '#95a5a6', 'bi-pause-circle-fill', 'Inactive customer')">
                                <i class="bi bi-pause-circle-fill"></i> Inactive
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="applyTemplate('Referral', '#e74c3c', 'bi-share-fill', 'Referred by another customer')">
                                <i class="bi bi-share-fill"></i> Referral
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('[name="name"]');
    const colorInput = document.getElementById('tagColor');
    const iconInput = document.getElementById('tagIcon');
    const previewName = document.getElementById('previewName');
    const previewIcon = document.getElementById('previewIcon');
    const tagPreview = document.getElementById('tagPreview');

    // Update preview in real-time
    function updatePreview() {
        const name = nameInput.value || 'Tag Name';
        const color = colorInput.value;
        const icon = iconInput.value || 'bi-tag-fill';

        previewName.textContent = name;
        previewIcon.className = icon;
        tagPreview.style.backgroundColor = color;
    }

    nameInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);
});

function applyTemplate(name, color, icon, description) {
    document.querySelector('[name="name"]').value = name;
    document.getElementById('tagColor').value = color;
    document.getElementById('tagIcon').value = icon;
    document.querySelector('[name="description"]').value = description;

    // Update preview
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewIcon').className = icon;
    document.getElementById('tagPreview').style.backgroundColor = color;

    // Scroll to form
    document.getElementById('createTagForm').scrollIntoView({ behavior: 'smooth' });
}
</script>


