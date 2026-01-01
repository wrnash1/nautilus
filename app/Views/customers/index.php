<?php
$pageTitle = 'Customers';
$activeMenu = 'customers';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Customers</h2>
    <div>
        <?php if (hasPermission('customers.export')): ?>
            <button onclick="exportCustomersCsv()" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </button>
        <?php endif; ?>

        <?php if (hasPermission('customers.create')): ?>
            <a href="/store/customers/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Customer
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-10">
                <input type="text" id="customerSearch" class="form-control"
                    placeholder="Search by name, email, phone, or company..." autocomplete="off">
                <div id="searchResults" class="position-absolute bg-white border rounded shadow-sm"
                    style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
            </div>
            <div class="col-md-2">
                <select id="typeFilter" class="form-select">
                    <option value="">All Types</option>
                    <option value="B2C">B2C</option>
                    <option value="B2B">B2B</option>
                </select>
            </div>
        </div>

        <?php if (empty($customers)): ?>
            <p class="text-muted text-center py-4">No customers found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td>
                                    <span
                                        class="badge bg-<?= $customer['customer_type'] === 'B2B' ? 'primary' : 'secondary' ?>">
                                        <?= $customer['customer_type'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/store/customers/<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($customer['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($customer['company_name'] ?? '-') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/store/customers/<?= $customer['id'] ?>" class="btn btn-outline-primary"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (hasPermission('customers.edit')): ?>
                                            <a href="/store/customers/<?= $customer['id'] ?>/edit" class="btn btn-outline-secondary"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('customers.delete')): ?>
                                            <form method="POST" action="/store/customers/<?= $customer['id'] ?>/delete"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="/store/customers?page=<?= $i ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = <<<'JS'
<script>
let searchTimeout;
const searchInput = document.getElementById('customerSearch');
const searchResults = document.getElementById('searchResults');
const typeFilter = document.getElementById('typeFilter');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`/store/customers/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(customers => {
            if (customers.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-muted">No customers found</div>';
                searchResults.style.display = 'block';
                return;
            }
            
            let html = '<div class="list-group list-group-flush">';
            customers.forEach(customer => {
                const badge = customer.customer_type === 'B2B' ? 
                    '<span class="badge bg-primary">B2B</span>' : 
                    '<span class="badge bg-secondary">B2C</span>';
                const company = customer.company_name ? 
                    `<br><small class="text-muted">${escapeHtml(customer.company_name)}</small>` : '';
                
                html += `
                    <a href="/store/customers/${customer.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${escapeHtml(customer.first_name)} ${escapeHtml(customer.last_name)}</strong>
                                ${badge}
                                ${company}
                                <br><small>${escapeHtml(customer.email || '')}</small>
                            </div>
                            <small class="text-muted">${escapeHtml(customer.phone || '')}</small>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
            searchResults.style.width = searchInput.offsetWidth + 'px';
        })
        .catch(error => {
            searchResults.innerHTML = '<div class="p-3 text-danger">Error searching customers</div>';
            searchResults.style.display = 'block';
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

searchInput.addEventListener('focus', function() {
    if (searchResults.innerHTML && this.value.length >= 2) {
        searchResults.style.display = 'block';
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

typeFilter.addEventListener('change', function() {
    window.location.href = '/store/customers?type=' + this.value;
});

function exportCustomersCsv() {
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '/store/customers/export';
    document.body.appendChild(iframe);

    setTimeout(() => {
        document.body.removeChild(iframe);
    }, 5000);
}
</script>
JS;

require __DIR__ . '/../layouts/admin.php';
?>