<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storefront Builder | Nautilus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
        }

        .builder-container {
            display: grid;
            grid-template-columns: 320px 1fr 400px;
            height: 100vh;
        }

        /* Left Sidebar - Sections */
        .sections-panel {
            background: white;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
        }

        .panel-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .panel-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #333;
        }

        .panel-header p {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .section-list {
            padding: 1rem;
        }

        .section-item {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: move;
            transition: all 0.2s;
        }

        .section-item:hover {
            border-color: #0066cc;
            box-shadow: 0 4px 12px rgba(0,102,204,0.1);
        }

        .section-item.dragging {
            opacity: 0.5;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .section-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .section-desc {
            font-size: 0.75rem;
            color: #666;
        }

        /* Center - Canvas */
        .canvas {
            background: #f5f7fa;
            overflow-y: auto;
            padding: 2rem;
        }

        .canvas-toolbar {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .canvas-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .canvas-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #0066cc;
            color: white;
        }

        .btn-primary:hover {
            background: #0052a3;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .canvas-preview {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-height: 600px;
            overflow: hidden;
        }

        .dropzone {
            min-height: 200px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            color: #999;
            margin: 1rem;
        }

        .dropzone.drag-over {
            border-color: #0066cc;
            background: rgba(0,102,204,0.05);
        }

        .placed-section {
            background: white;
            border: 2px solid transparent;
            margin: 1rem;
            border-radius: 8px;
            position: relative;
            transition: all 0.2s;
        }

        .placed-section:hover {
            border-color: #0066cc;
        }

        .placed-section.selected {
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }

        .section-controls {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .placed-section:hover .section-controls {
            opacity: 1;
        }

        .control-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: white;
            border: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .control-btn:hover {
            background: #f8f9fa;
            border-color: #0066cc;
            color: #0066cc;
        }

        /* Right Sidebar - Settings */
        .settings-panel {
            background: white;
            border-left: 1px solid #e0e0e0;
            overflow-y: auto;
        }

        .settings-content {
            padding: 1.5rem;
        }

        .setting-group {
            margin-bottom: 2rem;
        }

        .setting-group h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .setting-item {
            margin-bottom: 1.25rem;
        }

        .setting-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .setting-input {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .setting-input:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }

        .color-picker-wrapper {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 24px;
            transition: 0.3s;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: 0.3s;
        }

        input:checked + .toggle-slider {
            background-color: #0066cc;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .image-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .image-upload:hover {
            border-color: #0066cc;
            background: rgba(0,102,204,0.05);
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 6px;
            margin-top: 0.75rem;
        }

        .save-notice {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #28a745;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }

        .save-notice.show {
            display: block;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Section Previews */
        .section-preview {
            padding: 2rem;
        }

        .hero-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 4rem 2rem;
        }

        .hero-preview h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .products-preview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 2rem;
        }

        .product-card-preview {
            background: #f8f9fa;
            border-radius: 8px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="builder-container">
        <!-- Left Sidebar - Available Sections -->
        <div class="sections-panel">
            <div class="panel-header">
                <h2><i class="bi bi-grid-3x3"></i> Page Sections</h2>
                <p>Drag sections to build your page</p>
            </div>

            <div class="section-list">
                <div class="section-item" draggable="true" data-section-type="hero">
                    <div class="section-icon">
                        <i class="bi bi-image"></i>
                    </div>
                    <div class="section-name">Hero Banner</div>
                    <div class="section-desc">Large banner with heading and CTA</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="featured-products">
                    <div class="section-icon">
                        <i class="bi bi-bag"></i>
                    </div>
                    <div class="section-name">Featured Products</div>
                    <div class="section-desc">Showcase your best products</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="categories">
                    <div class="section-icon">
                        <i class="bi bi-grid"></i>
                    </div>
                    <div class="section-name">Product Categories</div>
                    <div class="section-desc">Display product categories</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="courses">
                    <div class="section-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="section-name">Dive Courses</div>
                    <div class="section-desc">Show available courses</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="trips">
                    <div class="section-icon">
                        <i class="bi bi-airplane"></i>
                    </div>
                    <div class="section-name">Dive Trips</div>
                    <div class="section-desc">Highlight upcoming trips</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="stats">
                    <div class="section-icon">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <div class="section-name">Statistics</div>
                    <div class="section-desc">Show business achievements</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="testimonials">
                    <div class="section-icon">
                        <i class="bi bi-chat-quote"></i>
                    </div>
                    <div class="section-name">Testimonials</div>
                    <div class="section-desc">Customer reviews and quotes</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="newsletter">
                    <div class="section-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="section-name">Newsletter</div>
                    <div class="section-desc">Email subscription form</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="brands">
                    <div class="section-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="section-name">Brand Logos</div>
                    <div class="section-desc">Partner and brand logos</div>
                </div>

                <div class="section-item" draggable="true" data-section-type="text-image">
                    <div class="section-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="section-name">Text + Image</div>
                    <div class="section-desc">Content with side image</div>
                </div>
            </div>
        </div>

        <!-- Center - Canvas -->
        <div class="canvas">
            <div class="canvas-toolbar">
                <div class="canvas-title">
                    <i class="bi bi-house-door"></i> Homepage Builder
                </div>
                <div class="canvas-actions">
                    <button class="btn btn-secondary" onclick="previewStorefront()">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button class="btn btn-primary" onclick="saveStorefront()">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </div>

            <div class="canvas-preview">
                <div id="dropzone" class="dropzone">
                    <i class="bi bi-plus-circle" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                    <h3>Drag sections here to build your page</h3>
                    <p style="margin-top: 0.5rem;">Click on a section to edit its settings</p>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Settings -->
        <div class="settings-panel">
            <div class="panel-header">
                <h2><i class="bi bi-gear"></i> Settings</h2>
                <p id="settings-subtitle">Select a section to edit</p>
            </div>

            <div class="settings-content" id="settings-content">
                <!-- Global Settings (shown when no section selected) -->
                <div id="global-settings">
                    <div class="setting-group">
                        <h3>Site Branding</h3>

                        <div class="setting-item">
                            <label class="setting-label">Business Name</label>
                            <input type="text" class="setting-input" value="Nautilus Dive Shop" id="business-name">
                        </div>

                        <div class="setting-item">
                            <label class="setting-label">Logo</label>
                            <div class="image-upload" onclick="document.getElementById('logo-upload').click()">
                                <i class="bi bi-upload"></i> Upload Logo
                                <input type="file" id="logo-upload" style="display: none;" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="setting-group">
                        <h3>Colors</h3>

                        <div class="setting-item">
                            <label class="setting-label">Primary Color</label>
                            <div class="color-picker-wrapper">
                                <div class="color-preview" style="background: #0066cc;" onclick="document.getElementById('primary-color').click()"></div>
                                <input type="color" id="primary-color" class="setting-input" value="#0066cc">
                            </div>
                        </div>

                        <div class="setting-item">
                            <label class="setting-label">Accent Color</label>
                            <div class="color-picker-wrapper">
                                <div class="color-preview" style="background: #ff6b35;" onclick="document.getElementById('accent-color').click()"></div>
                                <input type="color" id="accent-color" class="setting-input" value="#ff6b35">
                            </div>
                        </div>
                    </div>

                    <div class="setting-group">
                        <h3>Contact Information</h3>

                        <div class="setting-item">
                            <label class="setting-label">Phone</label>
                            <input type="tel" class="setting-input" value="(555) 123-4567" id="phone">
                        </div>

                        <div class="setting-item">
                            <label class="setting-label">Email</label>
                            <input type="email" class="setting-input" value="info@nautilus.com" id="email">
                        </div>

                        <div class="setting-item">
                            <label class="setting-label">Address</label>
                            <input type="text" class="setting-input" value="123 Ocean Ave, Miami, FL" id="address">
                        </div>
                    </div>
                </div>

                <!-- Section-specific settings (populated dynamically) -->
                <div id="section-settings" style="display: none;">
                    <!-- Populated by JavaScript when section is selected -->
                </div>
            </div>
        </div>
    </div>

    <div class="save-notice" id="save-notice">
        <i class="bi bi-check-circle"></i> Changes saved successfully!
    </div>

    <script>
        let draggedElement = null;
        let selectedSection = null;
        let pageData = {
            sections: [],
            settings: {
                businessName: 'Nautilus Dive Shop',
                primaryColor: '#0066cc',
                accentColor: '#ff6b35',
                phone: '(555) 123-4567',
                email: 'info@nautilus.com',
                address: '123 Ocean Ave, Miami, FL'
            }
        };

        // Drag and Drop functionality
        document.querySelectorAll('.section-item').forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
            });

            item.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });
        });

        const dropzone = document.getElementById('dropzone');

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        dropzone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (draggedElement) {
                const sectionType = draggedElement.dataset.sectionType;
                addSection(sectionType);
            }
        });

        function addSection(type) {
            const section = {
                id: Date.now(),
                type: type,
                settings: getDefaultSettings(type)
            };

            pageData.sections.push(section);
            renderCanvas();
        }

        function getDefaultSettings(type) {
            const defaults = {
                'hero': {
                    title: 'Dive Into Adventure',
                    subtitle: 'Premium dive equipment and expert training',
                    ctaText: 'Shop Now',
                    ctaLink: '/shop',
                    backgroundImage: ''
                },
                'featured-products': {
                    title: 'Featured Products',
                    count: 8
                },
                'categories': {
                    title: 'Shop by Category'
                },
                'stats': {
                    stats: [
                        { value: '500+', label: 'Products' },
                        { value: '2,000+', label: 'Customers' },
                        { value: '15+', label: 'Years' },
                        { value: '50+', label: 'Courses' }
                    ]
                }
            };

            return defaults[type] || {};
        }

        function renderCanvas() {
            const dropzoneEl = document.getElementById('dropzone');

            if (pageData.sections.length === 0) {
                dropzoneEl.innerHTML = `
                    <i class="bi bi-plus-circle" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                    <h3>Drag sections here to build your page</h3>
                    <p style="margin-top: 0.5rem;">Click on a section to edit its settings</p>
                `;
                dropzoneEl.style.display = 'block';
            } else {
                dropzoneEl.style.display = 'none';
                const canvas = document.querySelector('.canvas-preview');
                canvas.innerHTML = '';

                pageData.sections.forEach(section => {
                    const sectionEl = createSectionElement(section);
                    canvas.appendChild(sectionEl);
                });
            }
        }

        function createSectionElement(section) {
            const div = document.createElement('div');
            div.className = 'placed-section';
            div.dataset.sectionId = section.id;

            div.innerHTML = `
                <div class="section-controls">
                    <button class="control-btn" onclick="moveSection(${section.id}, -1)" title="Move Up">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button class="control-btn" onclick="moveSection(${section.id}, 1)" title="Move Down">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button class="control-btn" onclick="editSection(${section.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="control-btn" onclick="deleteSection(${section.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                ${getSectionPreview(section)}
            `;

            div.addEventListener('click', () => editSection(section.id));

            return div;
        }

        function getSectionPreview(section) {
            switch(section.type) {
                case 'hero':
                    return `
                        <div class="hero-preview">
                            <h1>${section.settings.title || 'Hero Title'}</h1>
                            <p>${section.settings.subtitle || 'Hero subtitle'}</p>
                        </div>
                    `;
                case 'featured-products':
                    return `
                        <div class="section-preview">
                            <h2 style="text-align: center; margin-bottom: 1rem;">${section.settings.title || 'Featured Products'}</h2>
                            <div class="products-preview">
                                ${Array(section.settings.count || 3).fill('<div class="product-card-preview"><i class="bi bi-box" style="font-size: 2rem;"></i></div>').join('')}
                            </div>
                        </div>
                    `;
                default:
                    return `
                        <div class="section-preview">
                            <h3>${section.type.replace('-', ' ').toUpperCase()}</h3>
                        </div>
                    `;
            }
        }

        function editSection(id) {
            const section = pageData.sections.find(s => s.id === id);
            if (!section) return;

            selectedSection = section;

            // Highlight selected section
            document.querySelectorAll('.placed-section').forEach(el => {
                el.classList.remove('selected');
            });
            document.querySelector(`[data-section-id="${id}"]`).classList.add('selected');

            // Show section settings
            showSectionSettings(section);
        }

        function showSectionSettings(section) {
            document.getElementById('global-settings').style.display = 'none';
            document.getElementById('section-settings').style.display = 'block';
            document.getElementById('settings-subtitle').textContent = `Editing: ${section.type}`;

            // Build settings form based on section type
            const settingsHtml = `
                <div class="setting-group">
                    <h3>${section.type.replace('-', ' ').toUpperCase()} Settings</h3>
                    ${getSettingsForm(section)}
                </div>
                <button class="btn btn-secondary" onclick="closeSettings()" style="width: 100%;">
                    <i class="bi bi-x"></i> Close
                </button>
            `;

            document.getElementById('section-settings').innerHTML = settingsHtml;
        }

        function getSettingsForm(section) {
            let html = '';

            for (let [key, value] of Object.entries(section.settings)) {
                html += `
                    <div class="setting-item">
                        <label class="setting-label">${key.charAt(0).toUpperCase() + key.slice(1)}</label>
                        <input type="text" class="setting-input" value="${value}"
                               onchange="updateSectionSetting(${section.id}, '${key}', this.value)">
                    </div>
                `;
            }

            return html;
        }

        function updateSectionSetting(id, key, value) {
            const section = pageData.sections.find(s => s.id === id);
            if (section) {
                section.settings[key] = value;
                renderCanvas();
            }
        }

        function closeSettings() {
            document.getElementById('section-settings').style.display = 'none';
            document.getElementById('global-settings').style.display = 'block';
            document.getElementById('settings-subtitle').textContent = 'Select a section to edit';
            document.querySelectorAll('.placed-section').forEach(el => {
                el.classList.remove('selected');
            });
        }

        function moveSection(id, direction) {
            const index = pageData.sections.findIndex(s => s.id === id);
            if (index === -1) return;

            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= pageData.sections.length) return;

            [pageData.sections[index], pageData.sections[newIndex]] =
            [pageData.sections[newIndex], pageData.sections[index]];

            renderCanvas();
        }

        function deleteSection(id) {
            if (confirm('Delete this section?')) {
                pageData.sections = pageData.sections.filter(s => s.id !== id);
                renderCanvas();
                closeSettings();
            }
        }

        function saveStorefront() {
            // Save to backend
            fetch('/store/storefront/save-builder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(pageData)
            })
            .then(response => response.json())
            .then(data => {
                const notice = document.getElementById('save-notice');
                notice.classList.add('show');
                setTimeout(() => notice.classList.remove('show'), 3000);
            });
        }

        function previewStorefront() {
            window.open('/', '_blank');
        }

        // Initialize
        renderCanvas();
    </script>
</body>
</html>
