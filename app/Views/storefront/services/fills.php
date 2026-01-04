<?php ob_start(); ?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden py-5 bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-700) 0%, var(--primary-900) 100%);">
    <div class="container position-relative z-1 text-center">
        <h1 class="display-4 fw-bold font-heading mb-3">Fill Station</h1>
        <p class="lead text-light opacity-75 mb-0">Pure Air, Nitrox, and Trimix fills available instantly.</p>
    </div>
</section>

<!-- Content -->
<section class="py-5">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-uppercase text-muted small fw-bold">Gas Type</th>
                                <th scope="col" class="px-4 py-3 text-uppercase text-muted small fw-bold">Description</th>
                                <th scope="col" class="px-4 py-3 text-uppercase text-muted small fw-bold text-end">Price per Fill</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr>
                                <td class="px-4 py-3 fw-bold text-gray-900">Air</td>
                                <td class="px-4 py-3 text-muted">Standard filtered air</td>
                                <td class="px-4 py-3 fw-bold text-primary text-end">$8.00</td>
                            </tr>
                             <tr>
                                <td class="px-4 py-3 fw-bold text-gray-900">Nitrox 32%</td>
                                <td class="px-4 py-3 text-muted">Enriched Air Nitrox</td>
                                <td class="px-4 py-3 fw-bold text-primary text-end">$15.00</td>
                            </tr>
                             <tr>
                                <td class="px-4 py-3 fw-bold text-gray-900">Nitrox 36%</td>
                                <td class="px-4 py-3 text-muted">Enriched Air Nitrox</td>
                                <td class="px-4 py-3 fw-bold text-primary text-end">$15.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/main.php'; ?>
