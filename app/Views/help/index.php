<?php $this->layout('layouts/admin', ['title' => $title ?? 'Help']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-question-circle me-2"></i>Help & Support</h2>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Frequently Asked Questions</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I process a sale?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Navigate to the POS section from the sidebar and scan or search for products to add to the cart.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I add a new customer?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Go to Customers > Add New Customer and fill in the required information.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-headset display-4 text-primary"></i>
                    <h5 class="mt-3">Need More Help?</h5>
                    <p class="text-muted">Contact our support team</p>
                    <a href="/help/contact" class="btn btn-primary">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</div>
