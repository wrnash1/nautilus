<?php
$pageTitle = 'Customize Dashboard';
$activeMenu = 'dashboard';

ob_start();
?>

<style>
.customize-container {
    max-width: 1400px;
    margin: 0 auto;
}

.customize-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.customize-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.customize-header p {
    margin: 0;
    opacity: 0.9;
}

.customization-panel {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.widget-library {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: fit-content;
}

.widget-library h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.category-section {
    margin-bottom: 20px;
}

.category-title {
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.widget-item {
    background: #f7fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    cursor: move;
    transition: all 0.2s;
}

.widget-item:hover {
    background: #edf2f7;
    border-color: #667eea;
    transform: translateX(4px);
}

.widget-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.widget-item-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.widget-item-icon {
    color: #667eea;
    font-size: 18px;
}

.widget-item-name {
    font-weight: 600;
    font-size: 14px;
    color: #2d3748;
}

.widget-item-desc {
    font-size: 12px;
    color: #718096;
    margin: 0;
}

.dashboard-preview {
    background: #f7fafc;
    border-radius: 12px;
    padding: 20px;
    min-height: 600px;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.preview-header h3 {
    margin: 0;
    font-size: 18px;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-save {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-save:hover {
    transform: translateY(-2px);
}

.btn-reset {
    background: #ef4444;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-reset:hover {
    transform: translateY(-2px);
}

.widget-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.widget-placeholder {
    background: white;
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 20px;
    position: relative;
    cursor: move;
    transition: all 0.2s;
}

.widget-placeholder:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.widget-placeholder.dragging {
    opacity: 0.5;
}

.widget-placeholder.drag-over {
    background: #edf2f7;
    border-color: #667eea;
}

.widget-placeholder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.widget-placeholder-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
}

.widget-actions {
    display: flex;
    gap: 8px;
}

.widget-action-btn {
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}

.widget-action-btn:hover {
    background: #edf2f7;
    color: #667eea;
}

.widget-action-btn.remove:hover {
    color: #ef4444;
}

.widget-size-selector {
    display: flex;
    gap: 4px;
    margin-top: 10px;
}

.size-option {
    background: #edf2f7;
    border: 1px solid #e2e8f0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.size-option:hover {
    background: #e2e8f0;
}

.size-option.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.widget-placeholder.size-small {
    grid-column: span 1;
}

.widget-placeholder.size-medium {
    grid-column: span 1;
    min-height: 200px;
}

.widget-placeholder.size-large {
    grid-column: span 2;
    min-height: 250px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

@media (max-width: 1024px) {
    .customization-panel {
        grid-template-columns: 1fr;
    }

    .widget-placeholder.size-large {
        grid-column: span 1;
    }
}
</style>

<div class="customize-container">
    <div class="customize-header">
        <h1><i class="bi bi-grid-3x3-gap"></i> Customize Your Dashboard</h1>
        <p>Drag and drop widgets to customize your dashboard layout. Click the gear icon to configure widget settings.</p>
    </div>

    <div class="customization-panel">
        <!-- Widget Library -->
        <div class="widget-library">
            <h3><i class="bi bi-box"></i> Available Widgets</h3>

            <?php foreach ($categories as $categoryId => $category): ?>
                <?php
                $categoryWidgets = array_filter($availableWidgets, function($widget) use ($categoryId) {
                    return $widget['category'] === $categoryId;
                });
                if (empty($categoryWidgets)) continue;
                ?>

                <div class="category-section">
                    <div class="category-title">
                        <i class="bi bi-<?= $category['icon'] ?>"></i>
                        <?= htmlspecialchars($category['name']) ?>
                    </div>

                    <?php foreach ($categoryWidgets as $widget): ?>
                        <div class="widget-item"
                             draggable="true"
                             data-widget-id="<?= $widget['id'] ?>"
                             data-widget-name="<?= htmlspecialchars($widget['name']) ?>"
                             data-widget-icon="<?= $widget['icon'] ?>"
                             data-widget-size="<?= $widget['default_size'] ?>"
                             data-configurable="<?= $widget['configurable'] ? 'true' : 'false' ?>">
                            <div class="widget-item-header">
                                <i class="bi bi-<?= $widget['icon'] ?> widget-item-icon"></i>
                                <span class="widget-item-name"><?= htmlspecialchars($widget['name']) ?></span>
                            </div>
                            <p class="widget-item-desc"><?= htmlspecialchars($widget['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Dashboard Preview -->
        <div class="dashboard-preview">
            <div class="preview-header">
                <h3><i class="bi bi-eye"></i> Dashboard Preview</h3>
                <div class="action-buttons">
                    <button type="button" class="btn-reset" onclick="resetLayout()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                    </button>
                    <button type="button" class="btn-save" onclick="saveLayout()">
                        <i class="bi bi-check-circle"></i> Save Layout
                    </button>
                </div>
            </div>

            <div id="widget-grid" class="widget-grid">
                <?php if (empty($currentLayout)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Drag widgets from the left panel to start customizing your dashboard.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($currentLayout as $index => $layoutWidget): ?>
                        <?php
                        $widgetDef = $availableWidgets[$layoutWidget['widget_id']] ?? null;
                        if (!$widgetDef) continue;
                        ?>
                        <div class="widget-placeholder size-<?= htmlspecialchars($layoutWidget['size']) ?>"
                             draggable="true"
                             data-widget-id="<?= htmlspecialchars($layoutWidget['widget_id']) ?>"
                             data-position="<?= $index ?>">
                            <div class="widget-placeholder-header">
                                <div class="widget-placeholder-title">
                                    <i class="bi bi-grip-vertical"></i>
                                    <i class="bi bi-<?= $widgetDef['icon'] ?>"></i>
                                    <span><?= htmlspecialchars($widgetDef['name']) ?></span>
                                </div>
                                <div class="widget-actions">
                                    <?php if ($widgetDef['configurable']): ?>
                                        <button class="widget-action-btn"
                                                onclick="configureWidget('<?= $layoutWidget['widget_id'] ?>')"
                                                title="Configure">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="widget-action-btn remove"
                                            onclick="removeWidget(this)"
                                            title="Remove">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="widget-size-selector">
                                <span class="size-option <?= $layoutWidget['size'] === 'small' ? 'active' : '' ?>"
                                      onclick="changeWidgetSize(this, 'small')">Small</span>
                                <span class="size-option <?= $layoutWidget['size'] === 'medium' ? 'active' : '' ?>"
                                      onclick="changeWidgetSize(this, 'medium')">Medium</span>
                                <span class="size-option <?= $layoutWidget['size'] === 'large' ? 'active' : '' ?>"
                                      onclick="changeWidgetSize(this, 'large')">Large</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let draggedElement = null;
let draggedFromLibrary = false;

// Drag events for widget library items
document.querySelectorAll('.widget-item').forEach(item => {
    item.addEventListener('dragstart', function(e) {
        draggedElement = this;
        draggedFromLibrary = true;
        e.dataTransfer.effectAllowed = 'copy';
        this.style.opacity = '0.5';
    });

    item.addEventListener('dragend', function() {
        this.style.opacity = '1';
    });
});

// Drag events for widgets in grid
const widgetGrid = document.getElementById('widget-grid');

widgetGrid.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = draggedFromLibrary ? 'copy' : 'move';
});

widgetGrid.addEventListener('drop', function(e) {
    e.preventDefault();

    if (draggedFromLibrary) {
        // Adding new widget from library
        const widgetId = draggedElement.dataset.widgetId;
        const widgetName = draggedElement.dataset.widgetName;
        const widgetIcon = draggedElement.dataset.widgetIcon;
        const widgetSize = draggedElement.dataset.widgetSize;
        const configurable = draggedElement.dataset.configurable === 'true';

        addWidgetToGrid(widgetId, widgetName, widgetIcon, widgetSize, configurable);
    }

    draggedElement = null;
    draggedFromLibrary = false;
});

// Widget placeholder drag events
function initWidgetDrag(element) {
    element.addEventListener('dragstart', function(e) {
        if (draggedFromLibrary) return;
        draggedElement = this;
        e.dataTransfer.effectAllowed = 'move';
        this.classList.add('dragging');
    });

    element.addEventListener('dragend', function() {
        this.classList.remove('dragging');
    });

    element.addEventListener('dragover', function(e) {
        if (draggedFromLibrary) return;
        e.preventDefault();
        this.classList.add('drag-over');
    });

    element.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });

    element.addEventListener('drop', function(e) {
        if (draggedFromLibrary) return;
        e.preventDefault();
        this.classList.remove('drag-over');

        if (draggedElement && draggedElement !== this) {
            // Swap positions
            const parent = this.parentNode;
            const draggedIndex = Array.from(parent.children).indexOf(draggedElement);
            const targetIndex = Array.from(parent.children).indexOf(this);

            if (draggedIndex < targetIndex) {
                parent.insertBefore(draggedElement, this.nextSibling);
            } else {
                parent.insertBefore(draggedElement, this);
            }

            updatePositions();
        }
    });
}

document.querySelectorAll('.widget-placeholder').forEach(initWidgetDrag);

function addWidgetToGrid(widgetId, widgetName, widgetIcon, widgetSize, configurable) {
    // Check if widget already exists
    const existing = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (existing && !existing.classList.contains('widget-item')) {
        alert('This widget is already on your dashboard.');
        return;
    }

    const position = document.querySelectorAll('#widget-grid .widget-placeholder').length;

    const widgetHtml = `
        <div class="widget-placeholder size-${widgetSize}"
             draggable="true"
             data-widget-id="${widgetId}"
             data-position="${position}">
            <div class="widget-placeholder-header">
                <div class="widget-placeholder-title">
                    <i class="bi bi-grip-vertical"></i>
                    <i class="bi bi-${widgetIcon}"></i>
                    <span>${widgetName}</span>
                </div>
                <div class="widget-actions">
                    ${configurable ? `<button class="widget-action-btn" onclick="configureWidget('${widgetId}')" title="Configure">
                        <i class="bi bi-gear"></i>
                    </button>` : ''}
                    <button class="widget-action-btn remove" onclick="removeWidget(this)" title="Remove">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="widget-size-selector">
                <span class="size-option ${widgetSize === 'small' ? 'active' : ''}" onclick="changeWidgetSize(this, 'small')">Small</span>
                <span class="size-option ${widgetSize === 'medium' ? 'active' : ''}" onclick="changeWidgetSize(this, 'medium')">Medium</span>
                <span class="size-option ${widgetSize === 'large' ? 'active' : ''}" onclick="changeWidgetSize(this, 'large')">Large</span>
            </div>
        </div>
    `;

    // Remove empty state if exists
    const emptyState = widgetGrid.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }

    widgetGrid.insertAdjacentHTML('beforeend', widgetHtml);
    const newWidget = widgetGrid.lastElementChild;
    initWidgetDrag(newWidget);

    updatePositions();
}

function removeWidget(button) {
    if (!confirm('Remove this widget from your dashboard?')) return;

    const widget = button.closest('.widget-placeholder');
    widget.remove();

    updatePositions();

    // Show empty state if no widgets
    if (document.querySelectorAll('#widget-grid .widget-placeholder').length === 0) {
        widgetGrid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Drag widgets from the left panel to start customizing your dashboard.</p>
            </div>
        `;
    }
}

function changeWidgetSize(button, size) {
    const widget = button.closest('.widget-placeholder');
    widget.className = `widget-placeholder size-${size}`;

    button.parentElement.querySelectorAll('.size-option').forEach(opt => {
        opt.classList.remove('active');
    });
    button.classList.add('active');
}

function updatePositions() {
    document.querySelectorAll('#widget-grid .widget-placeholder').forEach((widget, index) => {
        widget.dataset.position = index;
    });
}

function configureWidget(widgetId) {
    alert(`Widget configuration UI would open here for: ${widgetId}`);
    // TODO: Implement widget configuration modal
}

async function saveLayout() {
    const widgets = [];
    document.querySelectorAll('#widget-grid .widget-placeholder').forEach((widget, index) => {
        const sizeClass = Array.from(widget.classList).find(c => c.startsWith('size-'));
        const size = sizeClass ? sizeClass.replace('size-', '') : 'medium';

        widgets.push({
            widget_id: widget.dataset.widgetId,
            position: index,
            size: size,
            config: {}
        });
    });

    try {
        const response = await fetch('/dashboard/widgets/save-layout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ widgets })
        });

        const result = await response.json();

        if (result.success) {
            alert('Dashboard layout saved successfully!');
            window.location.href = '/dashboard';
        } else {
            alert('Failed to save layout: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error saving layout: ' + error.message);
    }
}

async function resetLayout() {
    if (!confirm('Reset dashboard to default layout? This will remove all customizations.')) {
        return;
    }

    try {
        const response = await fetch('/dashboard/widgets/reset-layout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            alert('Dashboard layout reset successfully!');
            location.reload();
        } else {
            alert('Failed to reset layout: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error resetting layout: ' + error.message);
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
