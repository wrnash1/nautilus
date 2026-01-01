<?php
$pageTitle = 'Search Results';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Search', 'url' => null]
];

ob_start();
?>

<div class="search-results-page">
    <div class="search-header">
        <h1>Search Results</h1>
        <div class="search-box-container">
            <form method="GET" action="/store/search" class="search-form">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           name="q"
                           value="<?= htmlspecialchars($query) ?>"
                           placeholder="Search across all modules..."
                           class="search-input"
                           autocomplete="off"
                           id="globalSearch">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
            <div id="searchSuggestions" class="search-suggestions"></div>
        </div>
    </div>

    <div class="results-info">
        <span class="results-count">Found <?= number_format($totalResults) ?> result(s) for "<?= htmlspecialchars($query) ?>"</span>
        <div class="result-filters">
            <button class="filter-btn <?= empty($modules) ? 'active' : '' ?>" onclick="window.location.href='/store/search?q=<?= urlencode($query) ?>'">
                All Results
            </button>
            <?php
            $availableModules = ['customers', 'products', 'orders', 'courses', 'trips', 'documents', 'rentals'];
            foreach ($availableModules as $mod):
                if (!empty($results[$mod])):
            ?>
                <button class="filter-btn <?= in_array($mod, $modules) ? 'active' : '' ?>"
                        onclick="window.location.href='/store/search?q=<?= urlencode($query) ?>&modules[]=<?= $mod ?>'">
                    <?= ucfirst($mod) ?> (<?= count($results[$mod]) ?>)
                </button>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>

    <?php if ($totalResults === 0): ?>
        <div class="no-results">
            <i class="fas fa-search fa-3x"></i>
            <h3>No results found</h3>
            <p>Try different keywords or check your spelling</p>
            <div class="search-tips">
                <h4>Search Tips:</h4>
                <ul>
                    <li>Use specific keywords</li>
                    <li>Try customer names, product SKUs, or order numbers</li>
                    <li>Check spelling and try variations</li>
                    <li>Use fewer keywords for broader results</li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <div class="results-container">
            <?php foreach ($results as $moduleName => $moduleResults): ?>
                <?php if (!empty($moduleResults)): ?>
                    <div class="result-section">
                        <h2 class="section-title">
                            <i class="fas fa-<?= getModuleIcon($moduleName) ?>"></i>
                            <?= ucfirst($moduleName) ?>
                            <span class="section-count">(<?= count($moduleResults) ?>)</span>
                        </h2>

                        <div class="results-grid">
                            <?php foreach ($moduleResults as $result): ?>
                                <a href="<?= htmlspecialchars($result['url']) ?>" class="result-card">
                                    <div class="result-icon">
                                        <i class="fas fa-<?= $result['icon'] ?>"></i>
                                    </div>
                                    <div class="result-content">
                                        <div class="result-title"><?= htmlspecialchars($result['name']) ?></div>
                                        <?php if (isset($result['subtitle'])): ?>
                                            <div class="result-subtitle"><?= htmlspecialchars($result['subtitle']) ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($result['email'])): ?>
                                            <div class="result-meta">
                                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($result['email']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($result['phone'])): ?>
                                            <div class="result-meta">
                                                <i class="fas fa-phone"></i> <?= htmlspecialchars($result['phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="result-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.search-results-page {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.search-header {
    margin-bottom: 30px;
}

.search-header h1 {
    margin: 0 0 20px 0;
    font-size: 28px;
}

.search-box-container {
    position: relative;
}

.search-form {
    margin-bottom: 20px;
}

.search-input-group {
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    padding: 5px 10px 5px 20px;
    transition: all 0.3s;
}

.search-input-group:focus-within {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-input-group i {
    color: #999;
    margin-right: 10px;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 12px 10px;
    font-size: 16px;
}

.search-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    margin-top: 10px;
    max-height: 400px;
    overflow-y: auto;
    display: none;
    z-index: 1000;
}

.search-suggestions.show {
    display: block;
}

.suggestion-item {
    padding: 12px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-type {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.results-info {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.results-count {
    font-size: 16px;
    color: #666;
}

.result-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #f8f9fa;
}

.filter-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.result-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    margin: 0 0 20px 0;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-count {
    color: #999;
    font-size: 16px;
    font-weight: normal;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 15px;
}

.result-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
}

.result-card:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.result-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.result-content {
    flex: 1;
}

.result-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.result-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.result-meta {
    font-size: 13px;
    color: #999;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
}

.result-arrow {
    color: #999;
    font-size: 18px;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.no-results i {
    color: #ddd;
    margin-bottom: 20px;
}

.no-results h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.no-results p {
    margin: 0 0 30px 0;
    color: #999;
}

.search-tips {
    max-width: 500px;
    margin: 0 auto;
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.search-tips h4 {
    margin: 0 0 10px 0;
}

.search-tips ul {
    margin: 0;
    padding-left: 20px;
}

.search-tips li {
    margin: 5px 0;
    color: #666;
}

@media (max-width: 768px) {
    .results-grid {
        grid-template-columns: 1fr;
    }

    .results-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Autocomplete functionality
const searchInput = document.getElementById('globalSearch');
const suggestionsBox = document.getElementById('searchSuggestions');
let debounceTimer;

searchInput.addEventListener('input', function(e) {
    const query = e.target.value.trim();

    clearTimeout(debounceTimer);

    if (query.length < 2) {
        suggestionsBox.classList.remove('show');
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`/store/search/suggestions?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => {
                if (suggestions.length > 0) {
                    displaySuggestions(suggestions);
                } else {
                    suggestionsBox.classList.remove('show');
                }
            })
            .catch(error => console.error('Error:', error));
    }, 300);
});

function displaySuggestions(suggestions) {
    const html = suggestions.map(s => `
        <div class="suggestion-item" onclick="selectSuggestion('${s.suggestion}')">
            <span class="suggestion-type">${s.type}</span>
            <span>${s.suggestion}</span>
        </div>
    `).join('');

    suggestionsBox.innerHTML = html;
    suggestionsBox.classList.add('show');
}

function selectSuggestion(value) {
    searchInput.value = value;
    suggestionsBox.classList.remove('show');
    document.querySelector('.search-form').submit();
}

// Close suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box-container')) {
        suggestionsBox.classList.remove('show');
    }
});
</script>

<?php
$content = ob_get_clean();

// Helper function
function getModuleIcon($module) {
    $icons = [
        'customers' => 'users',
        'products' => 'box',
        'orders' => 'shopping-bag',
        'courses' => 'graduation-cap',
        'trips' => 'ship',
        'documents' => 'folder',
        'rentals' => 'life-ring'
    ];
    return $icons[$module] ?? 'search';
}

require __DIR__ . '/../layouts/admin.php';
?>
