<?php
use App\Services\QuickActions\QuickActionsService;

$groupedActions = QuickActionsService::getGroupedActions();
$userId = \App\Core\Auth::userId();
$recentActions = $userId ? QuickActionsService::getRecentActions($userId) : [];
?>

<!-- Quick Actions Modal -->
<div id="quickActionsModal" class="quick-actions-modal">
    <div class="quick-actions-content">
        <div class="modal-header">
            <h2>Quick Actions</h2>
            <button class="close-btn" onclick="closeQuickActions()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text"
                   id="quickActionSearch"
                   placeholder="Search actions or press shortcut keys..."
                   autocomplete="off">
            <span class="shortcut-hint">Ctrl+K</span>
        </div>

        <div class="actions-container">
            <!-- Recent Actions -->
            <?php if (!empty($recentActions)): ?>
            <div class="actions-section">
                <h3><i class="fas fa-clock"></i> Recent</h3>
                <div class="actions-grid">
                    <?php foreach ($recentActions as $action): ?>
                        <a href="<?= htmlspecialchars($action['url']) ?>" class="action-item" data-action-id="recent">
                            <div class="action-icon">
                                <i class="fas fa-<?= $action['icon'] ?>"></i>
                            </div>
                            <div class="action-label"><?= htmlspecialchars($action['label']) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grouped Actions -->
            <?php foreach ($groupedActions as $category => $actions): ?>
                <div class="actions-section" data-category="<?= strtolower($category) ?>">
                    <h3>
                        <i class="fas fa-<?= getCategoryIcon($category) ?>"></i>
                        <?= htmlspecialchars($category) ?>
                    </h3>
                    <div class="actions-grid">
                        <?php foreach ($actions as $action): ?>
                            <a href="<?= htmlspecialchars($action['url'] ?? '#') ?>"
                               class="action-item"
                               data-action-id="<?= $action['id'] ?>"
                               data-shortcut="<?= $action['shortcut'] ?? '' ?>"
                               data-search="<?= strtolower($action['label']) ?>">
                                <div class="action-icon">
                                    <i class="fas fa-<?= $action['icon'] ?>"></i>
                                </div>
                                <div class="action-label"><?= htmlspecialchars($action['label']) ?></div>
                                <?php if (!empty($action['shortcut'])): ?>
                                    <div class="action-shortcut"><?= htmlspecialchars($action['shortcut']) ?></div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="modal-footer">
            <div class="keyboard-hints">
                <span><kbd>↑</kbd> <kbd>↓</kbd> Navigate</span>
                <span><kbd>Enter</kbd> Select</span>
                <span><kbd>Esc</kbd> Close</span>
            </div>
        </div>
    </div>
</div>

<style>
.quick-actions-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.2s;
}

.quick-actions-modal.show {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 10vh;
}

.quick-actions-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    animation: slideDown 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #999;
    cursor: pointer;
    padding: 5px 10px;
}

.close-btn:hover {
    color: #333;
}

.search-box {
    padding: 15px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-box i {
    color: #999;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 16px;
    padding: 8px;
}

.shortcut-hint {
    padding: 4px 8px;
    background: #f0f0f0;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
    font-family: monospace;
}

.actions-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px 25px;
}

.actions-section {
    margin-bottom: 30px;
}

.actions-section h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
    position: relative;
}

.action-item:hover,
.action-item.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.action-item.selected .action-shortcut {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 12px;
    color: #667eea;
}

.action-item:hover .action-icon {
    background: rgba(255, 255, 255, 0.9);
}

.action-label {
    font-size: 14px;
    font-weight: 500;
    text-align: center;
}

.action-shortcut {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 3px 6px;
    background: #e9ecef;
    border-radius: 4px;
    font-size: 10px;
    font-family: monospace;
    color: #666;
}

.modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.keyboard-hints {
    display: flex;
    gap: 20px;
    justify-content: center;
    font-size: 13px;
    color: #666;
}

.keyboard-hints kbd {
    padding: 3px 6px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
    margin: 0 2px;
}

@media (max-width: 768px) {
    .actions-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .action-item {
        padding: 15px;
    }
}
</style>

<script>
// Quick Actions Modal functionality
let selectedActionIndex = -1;
let visibleActions = [];

// Open modal with Ctrl+K
document.addEventListener('keydown', function(e) {
    // Ctrl+K or Cmd+K to open
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openQuickActions();
    }

    // ESC to close
    if (e.key === 'Escape') {
        closeQuickActions();
    }

    // Handle keyboard shortcuts
    handleKeyboardShortcuts(e);
});

function openQuickActions() {
    const modal = document.getElementById('quickActionsModal');
    modal.classList.add('show');
    document.getElementById('quickActionSearch').focus();
    updateVisibleActions();
}

function closeQuickActions() {
    const modal = document.getElementById('quickActionsModal');
    modal.classList.remove('show');
    document.getElementById('quickActionSearch').value = '';
    selectedActionIndex = -1;
    updateVisibleActions();
}

// Close on backdrop click
document.getElementById('quickActionsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQuickActions();
    }
});

// Search functionality
document.getElementById('quickActionSearch').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const actions = document.querySelectorAll('.action-item');

    actions.forEach(action => {
        const searchText = action.dataset.search || '';
        const category = action.closest('.actions-section');

        if (searchText.includes(query) || query === '') {
            action.style.display = 'flex';
        } else {
            action.style.display = 'none';
        }
    });

    // Hide empty sections
    document.querySelectorAll('.actions-section').forEach(section => {
        const visibleItems = section.querySelectorAll('.action-item:not([style*="display: none"])');
        section.style.display = visibleItems.length > 0 ? 'block' : 'none';
    });

    updateVisibleActions();
    selectedActionIndex = -1;
});

// Keyboard navigation
document.getElementById('quickActionSearch').addEventListener('keydown', function(e) {
    updateVisibleActions();

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedActionIndex = Math.min(selectedActionIndex + 1, visibleActions.length - 1);
        updateSelection();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedActionIndex = Math.max(selectedActionIndex - 1, -1);
        updateSelection();
    } else if (e.key === 'Enter' && selectedActionIndex >= 0) {
        e.preventDefault();
        visibleActions[selectedActionIndex].click();
    }
});

function updateVisibleActions() {
    visibleActions = Array.from(document.querySelectorAll('.action-item:not([style*="display: none"])'));
}

function updateSelection() {
    document.querySelectorAll('.action-item').forEach(item => item.classList.remove('selected'));

    if (selectedActionIndex >= 0 && selectedActionIndex < visibleActions.length) {
        visibleActions[selectedActionIndex].classList.add('selected');
        visibleActions[selectedActionIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

// Handle keyboard shortcuts (Alt+Key)
function handleKeyboardShortcuts(e) {
    if (!e.altKey) return;

    const actions = document.querySelectorAll('.action-item[data-shortcut]');
    actions.forEach(action => {
        const shortcut = action.dataset.shortcut;
        if (shortcut && matchesShortcut(e, shortcut)) {
            e.preventDefault();
            window.location.href = action.href;
        }
    });
}

function matchesShortcut(event, shortcut) {
    const parts = shortcut.toLowerCase().split('+');
    const key = parts[parts.length - 1];

    if (event.key.toLowerCase() !== key) return false;
    if (parts.includes('ctrl') && !event.ctrlKey) return false;
    if (parts.includes('alt') && !event.altKey) return false;
    if (parts.includes('shift') && !event.shiftKey) return false;

    return true;
}

// Focus global search helper
function focusGlobalSearch() {
    const searchInput = document.querySelector('#globalSearch, [name="search"], [type="search"]');
    if (searchInput) {
        searchInput.focus();
    }
}
</script>

<?php
function getCategoryIcon($category) {
    $icons = [
        'General' => 'bolt',
        'Customers' => 'users',
        'Sales' => 'cash-register',
        'Inventory' => 'boxes',
        'Rentals' => 'life-ring',
        'Courses' => 'graduation-cap',
        'Appointments' => 'calendar',
        'Documents' => 'folder',
        'Reports' => 'chart-bar',
        'Navigation' => 'compass'
    ];
    return $icons[$category] ?? 'circle';
}
?>
