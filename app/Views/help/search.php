<?php $this->layout('layouts/admin', ['title' => $title ?? 'Search Help']) ?>

<div class="container-fluid py-4">
    <h2><i class="bi bi-search me-2"></i>Search Help</h2>

    <div class="card mt-4">
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search help articles..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <?php if (!empty($results ?? [])): ?>
                <h5>Search Results</h5>
                <ul class="list-group">
                    <?php foreach ($results as $result): ?>
                    <li class="list-group-item">
                        <a href="/help/article/<?= $result['id'] ?>"><?= htmlspecialchars($result['title']) ?></a>
                        <p class="text-muted small mb-0"><?= htmlspecialchars(substr($result['excerpt'] ?? '', 0, 150)) ?>...</p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif (isset($_GET['q'])): ?>
                <p class="text-muted">No results found for "<?= htmlspecialchars($_GET['q']) ?>"</p>
            <?php endif; ?>
        </div>
    </div>
</div>
