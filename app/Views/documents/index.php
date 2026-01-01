<?php
$pageTitle = 'Documents';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Documents', 'url' => null]
];

ob_start();
?>

<div class="documents-page">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Document Management</h1>
                <p class="subtitle">Store and organize business documents</p>
            </div>
            <div class="header-actions">
                <a href="/store/documents/create" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Document
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-label">Total Documents</div>
            <div class="stat-value"><?= number_format($stats['total_documents'] ?? 0) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Storage</div>
            <div class="stat-value"><?= formatBytes($stats['total_size'] ?? 0) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Average Size</div>
            <div class="stat-value"><?= formatBytes($stats['avg_size'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" action="/store/documents" class="filters-form">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search documents..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="type-filters">
                <a href="/store/documents" class="filter-btn <?= empty($_GET['type']) ? 'active' : '' ?>">
                    All Types
                </a>
                <?php foreach ($documentTypes as $type): ?>
                    <a href="/store/documents?type=<?= urlencode($type['document_type']) ?>"
                       class="filter-btn <?= ($_GET['type'] ?? '') === $type['document_type'] ? 'active' : '' ?>">
                        <?= ucfirst($type['document_type']) ?> (<?= $type['count'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <!-- Documents Grid -->
    <div class="documents-grid">
        <?php if (empty($documents)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open fa-3x"></i>
                <h3>No documents found</h3>
                <p>Upload your first document to get started.</p>
                <a href="/store/documents/create" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Document
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($documents as $doc): ?>
                <div class="document-card">
                    <div class="document-icon">
                        <?php
                        $icon = 'file';
                        $extension = pathinfo($doc['file_name'], PATHINFO_EXTENSION);
                        if (in_array($extension, ['pdf'])) $icon = 'file-pdf';
                        elseif (in_array($extension, ['doc', 'docx'])) $icon = 'file-word';
                        elseif (in_array($extension, ['xls', 'xlsx'])) $icon = 'file-excel';
                        elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'file-image';
                        elseif (in_array($extension, ['zip', 'rar', '7z'])) $icon = 'file-archive';
                        ?>
                        <i class="fas fa-<?= $icon ?> fa-3x"></i>
                    </div>

                    <div class="document-info">
                        <h3 class="document-title">
                            <a href="/store/documents/<?= $doc['id'] ?>">
                                <?= htmlspecialchars($doc['title']) ?>
                            </a>
                        </h3>

                        <div class="document-meta">
                            <span class="meta-item">
                                <i class="fas fa-tag"></i>
                                <?= ucfirst($doc['document_type']) ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-file"></i>
                                <?= formatBytes($doc['file_size']) ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($doc['uploaded_by_name'] ?? 'Unknown') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?= date('M j, Y', strtotime($doc['created_at'])) ?>
                            </span>
                        </div>

                        <?php if ($doc['description']): ?>
                            <p class="document-description">
                                <?= htmlspecialchars(substr($doc['description'], 0, 100)) ?>
                                <?= strlen($doc['description']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($doc['tags'])): ?>
                            <div class="document-tags">
                                <?php foreach ($doc['tags'] as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="document-actions">
                        <a href="/store/documents/<?= $doc['id'] ?>/download" class="btn btn-sm btn-primary" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="/store/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-secondary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="/store/documents/<?= $doc['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.documents-page {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-content h1 {
    margin: 0;
    font-size: 28px;
}

.subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.search-box {
    display: flex;
    gap: 10px;
}

.search-box input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.type-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 20px;
    text-decoration: none;
    color: #666;
    font-size: 13px;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #f8f9fa;
}

.filter-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.document-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.document-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.document-icon {
    text-align: center;
    color: #007bff;
    padding: 20px 0;
}

.document-info {
    flex: 1;
}

.document-title {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.document-title a {
    color: #333;
    text-decoration: none;
}

.document-title a:hover {
    color: #007bff;
}

.document-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 10px;
}

.meta-item {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.meta-item i {
    color: #999;
}

.document-description {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    margin: 10px 0;
}

.document-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 10px;
}

.tag {
    display: inline-block;
    padding: 3px 10px;
    background: #e9ecef;
    border-radius: 12px;
    font-size: 11px;
    color: #495057;
}

.document-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.empty-state p {
    margin: 0 0 20px 0;
    color: #999;
}
</style>

<?php
$content = ob_get_clean();

// Helper function
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

require __DIR__ . '/../layouts/admin.php';
?>
