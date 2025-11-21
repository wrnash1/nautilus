<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket"></i> Create Trip Booking</h2>
    <a href="/trips/bookings" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/trips/bookings">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="mb-3">
                <label for="schedule_id" class="form-label">Trip Schedule *</label>
                <select class="form-select" id="schedule_id" name="schedule_id" required>
                    <option value="">Select a scheduled trip...</option>
                    <?php foreach ($schedules as $schedule): ?>
                        <option value="<?= $schedule['id'] ?>">
                            <?= htmlspecialchars($schedule['trip_name']) ?> - 
                            <?= htmlspecialchars($schedule['destination']) ?> 
                            (<?= date('M j, Y', strtotime($schedule['departure_date'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="customer_search" class="form-label">Customer *</label>
                <input type="text" class="form-control" id="customer_search" placeholder="Search by name, email, or phone...">
                <input type="hidden" id="customer_id" name="customer_id" required>
                <div id="customer_results" class="dropdown-menu" style="width: 100%;"></div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="number_of_participants" class="form-label">Number of Participants *</label>
                    <input type="number" class="form-control" id="number_of_participants" name="number_of_participants" value="1" min="1" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="total_amount" class="form-label">Total Amount *</label>
                    <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="deposit_amount" class="form-label">Deposit Amount</label>
                    <input type="number" step="0.01" class="form-control" id="deposit_amount" name="deposit_amount" value="0">
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Booking
                </button>
                <a href="/trips/bookings" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
let customerSearchTimeout;
const customerSearchInput = document.getElementById('customer_search');
const customerIdInput = document.getElementById('customer_id');
const resultsDiv = document.getElementById('customer_results');

customerSearchInput.addEventListener('input', function() {
    clearTimeout(customerSearchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        resultsDiv.classList.remove('show');
        return;
    }
    
    customerSearchTimeout = setTimeout(() => {
        fetch(`/store/customers/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(customers => {
                if (customers.length === 0) {
                    resultsDiv.innerHTML = '<div class="dropdown-item text-muted">No customers found</div>';
                } else {
                    resultsDiv.innerHTML = customers.map(customer => `
                        <a href="#" class="dropdown-item customer-result" data-id="${customer.id}" data-name="${customer.name}">
                            <strong>${customer.name}</strong><br>
                            <small>${customer.email || ''} ${customer.phone || ''}</small>
                        </a>
                    `).join('');
                    
                    document.querySelectorAll('.customer-result').forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            customerIdInput.value = this.dataset.id;
                            customerSearchInput.value = this.dataset.name;
                            resultsDiv.classList.remove('show');
                        });
                    });
                }
                resultsDiv.classList.add('show');
            });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!customerSearchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.classList.remove('show');
    }
});
</script>
