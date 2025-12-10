<?php $this->layout('layouts/admin', ['title' => $title ?? 'FAQ']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h2>

    <div class="card mt-4">
        <div class="card-body">
            <div class="accordion" id="faqAccordion">
                <?php if (!empty($faqs ?? [])): ?>
                    <?php foreach ($faqs as $i => $faq): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                                <?= htmlspecialchars($faq['question']) ?>
                            </button>
                        </h2>
                        <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No FAQs available yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
