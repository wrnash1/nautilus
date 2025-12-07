<?php $this->layout('layouts/admin', ['title' => $title ?? 'Help Article']) ?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/help">Help</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($article['title'] ?? 'Article') ?></li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <?php if (isset($article)): ?>
                <h2><?= htmlspecialchars($article['title']) ?></h2>
                <hr>
                <div class="article-content">
                    <?= $article['content'] ?? '' ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Article not found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
