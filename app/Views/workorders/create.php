<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools"></i> Create Work Order</h2>
    <a href="/workorders" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/workorders">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <h5 class="mb-3">Customer Information</h5>
            
            <div class="mb-3">
                <label for="customer_search" class="form-label">Customer (optional)</label>
                <input type="text" class="form-control" id="customer_search" placeholder="Search customer..." autocomplete="off">
                <input type="hidden" id="customer_id" name="customer_id">
                <div id="customer_results" class="dropdown-menu" style="width: 100%;"></div>
                <small class="text-muted">Leave blank for walk-in customers</small>
            </div>
            
            <hr>
            
            <h5 class="mb-3">Equipment Details</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="equipment_type" class="form-label">Equipment Type *</label>
                    <input type="text" class="form-control" id="equipment_type" name="equipment_type" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="equipment_brand" class="form-label">Brand</label>
                    <input type="text" class="form-control" id="equipment_brand" name="equipment_brand">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="equipment_model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="equipment_model" name="equipment_model">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="serial_number" class="form-label">Serial Number</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="issue_description" class="form-label">Issue Description *</label>
                <textarea class="form-control" id="issue_description" name="issue_description" rows="4" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="priority" class="form-label">Priority *</label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Work Order
                </button>
                <a href="/workorders" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customer_search').on('keyup', function() {
        const query = $(this).val();
        
        if (query.length < 2) {
            $('#customer_results').hide();
            return;
        }
        
        $.get('/customers/search', { q: query }, function(data) {
            if (data.length > 0) {
                let html = '';
                data.forEach(customer => {
                    html += `<a href="#" class="dropdown-item customer-result" data-id="${customer.id}">
                        ${customer.first_name} ${customer.last_name} - ${customer.email}
                    </a>`;
                });
                $('#customer_results').html(html).show();
            } else {
                $('#customer_results').hide();
            }
        });
    });
    
    $(document).on('click', '.customer-result', function(e) {
        e.preventDefault();
        const customerId = $(this).data('id');
        const customerName = $(this).text();
        
        $('#customer_id').val(customerId);
        $('#customer_search').val(customerName);
        $('#customer_results').hide();
    });
});
</script>
