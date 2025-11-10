<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> Create Reservation</h2>
    <a href="/rentals/reservations" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/rentals/reservations" id="reservationForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_search" class="form-label">Customer *</label>
                    <input type="text" class="form-control" id="customer_search" placeholder="Search customer..." autocomplete="off">
                    <input type="hidden" id="customer_id" name="customer_id" required>
                    <div id="customer_results" class="dropdown-menu" style="width: 100%;"></div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date *</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date *</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
            </div>
            
            <hr>
            
            <h5 class="mb-3">Equipment Items</h5>
            
            <div id="equipment_items">
                <div class="alert alert-info">
                    Select dates above to search for available equipment
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Reservation
                </button>
                <a href="/rentals/reservations" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    let equipmentData = [];
    
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
    
    $('#start_date, #end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            loadAvailableEquipment(startDate, endDate);
        }
    });
    
    function loadAvailableEquipment(startDate, endDate) {
        $.get('/rentals/available-equipment', { start_date: startDate, end_date: endDate }, function(data) {
            equipmentData = data;
            renderEquipmentList();
        });
    }
    
    function renderEquipmentList() {
        if (equipmentData.length === 0) {
            $('#equipment_items').html('<div class="alert alert-warning">No equipment available for selected dates</div>');
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Equipment</th><th>Category</th><th>Daily Rate</th><th>Quantity</th><th>Total</th></tr></thead><tbody>';
        
        equipmentData.forEach(item => {
            html += `<tr>
                <td>${item.name}</td>
                <td>${item.category_name}</td>
                <td>${formatCurrency(item.daily_rate)}</td>
                <td><input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" data-rate="${item.daily_rate}" min="0" max="1" value="0"></td>
                <td class="item-total" data-id="${item.id}">$0.00</td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        html += '<div class="text-end"><h5>Total: <span id="grand_total">$0.00</span></h5></div>';
        
        $('#equipment_items').html(html);
    }
    
    $(document).on('input', '.qty-input', function() {
        calculateTotals();
    });
    
    function calculateTotals() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) || 1;
        
        let grandTotal = 0;
        
        $('.qty-input').each(function() {
            const qty = parseInt($(this).val()) || 0;
            const rate = parseFloat($(this).data('rate'));
            const total = qty * rate * days;
            
            grandTotal += total;
            
            const id = $(this).data('id');
            $(`.item-total[data-id="${id}"]`).text(formatCurrency(total));
        });
        
        $('#grand_total').text(formatCurrency(grandTotal));
    }
    
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }
});
</script>
