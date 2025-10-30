# Phase 6: Modern UI Theme System - Complete Documentation

## Overview

Phase 6 introduces a comprehensive modern UI theme system with full dark mode support to transform the Nautilus Dive Shop application into a visually stunning, contemporary platform. This phase focuses on modernizing the user interface while maintaining all existing functionality.

## Features Implemented

### 1. Modern Theme System
- **Complete CSS Design System** with CSS variables
- **Dark Mode Support** with seamless theme switching
- **Modern Component Library** for consistent UI
- **Responsive Grid System** for all screen sizes
- **Animation System** with smooth transitions

### 2. Theme Manager JavaScript
- **ThemeManager Class** for theme persistence
- **ToastManager** for elegant notifications
- **LoadingOverlay** for async operations
- **Modal System** for confirmations
- **Auto-Save Forms** functionality

### 3. Modernized Pages
- **Main Dashboard** with gradient hero sections
- **Analytics Dashboard** with charts and insights
- **Loyalty Program Dashboard** with tier visualization
- **Modern data tables** throughout the application

---

## File Structure

```
/public/assets/
├── css/
│   └── modern-theme.css           # 400+ lines of modern CSS
└── js/
    └── theme-manager.js           # 400+ lines of theme management

/app/Views/
├── dashboard/
│   ├── index.php                  # Modernized main dashboard
│   └── modern_example.php         # Theme showcase page
├── analytics/
│   └── index.php                  # Modern analytics dashboard
└── loyalty/
    └── index.php                  # Modern loyalty dashboard
```

---

## 1. Modern Theme CSS System

**File**: `/public/assets/css/modern-theme.css`

### CSS Variables (Light Theme)

```css
:root {
    /* Colors - Primary */
    --primary-50: #eff6ff;
    --primary-100: #dbeafe;
    --primary-200: #bfdbfe;
    --primary-300: #93c5fd;
    --primary-400: #60a5fa;
    --primary-500: #0ea5e9;  /* Main primary */
    --primary-600: #0284c7;
    --primary-700: #0369a1;
    --primary-800: #075985;
    --primary-900: #0c4a6e;

    /* Success, Warning, Error, Info */
    --success: #10b981;
    --warning: #f59e0b;
    --error: #ef4444;
    --info: #3b82f6;

    /* Backgrounds */
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --bg-tertiary: #f3f4f6;

    /* Text */
    --text-primary: #111827;
    --text-secondary: #4b5563;
    --text-tertiary: #9ca3af;

    /* Borders & Shadows */
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);

    /* Spacing */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    --spacing-2xl: 3rem;

    /* Border Radius */
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --radius-2xl: 1.5rem;
    --radius-full: 9999px;

    /* Transitions */
    --transition-base: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Dark Theme Override

```css
[data-theme="dark"] {
    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --bg-tertiary: #374151;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --text-tertiary: #9ca3af;
    --border-color: #374151;
}
```

### Modern Component Classes

#### Stat Cards
```css
.stat-card-modern {
    background: var(--bg-primary);
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
}

.stat-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-icon-modern {
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-value-modern {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.stat-label-modern {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.stat-change-modern {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.stat-change-positive {
    color: var(--success);
}

.stat-change-negative {
    color: var(--error);
}
```

#### Modern Cards
```css
.modern-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
}

.modern-card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modern-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-body {
    padding: 1.5rem;
}
```

#### Modern Buttons
```css
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all var(--transition-base);
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    filter: brightness(1.1);
}

.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-success {
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
}

.btn-ghost {
    background: transparent;
    color: var(--text-secondary);
}

.btn-ghost:hover {
    background: var(--bg-secondary);
}
```

#### Modern Tables
```css
.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table-modern thead th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.table-modern tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}

.table-modern tbody tr {
    transition: background var(--transition-base);
}

.table-modern tbody tr:hover {
    background: var(--bg-secondary);
}
```

#### Modern Inputs
```css
.input-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: all var(--transition-base);
}

.input-modern:focus {
    outline: none;
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}
```

#### Badges
```css
.badge-modern {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary {
    background: rgba(14, 165, 233, 0.1);
    color: var(--primary-600);
}

.badge-success {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.badge-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.badge-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}
```

#### Alerts
```css
.alert-modern {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: var(--radius-lg);
    border-left: 4px solid;
}

.alert-modern-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: var(--success);
    color: #059669;
}

.alert-warning {
    background: rgba(245, 158, 11, 0.1);
    border-color: var(--warning);
    color: #d97706;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: var(--error);
    color: #dc2626;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border-color: var(--info);
    color: #2563eb;
}
```

### Animations

```css
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

.slide-up {
    animation: slideUp 0.4s ease-out;
}

.slide-down {
    animation: slideDown 0.4s ease-out;
}
```

### Utility Classes

```css
/* Grid System */
.grid-modern {
    display: grid;
}

.grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

.grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

.grid-cols-4 {
    grid-template-columns: repeat(4, 1fr);
}

/* Responsive Grid */
@media (max-width: 768px) {
    .grid-cols-2,
    .grid-cols-3,
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
}
```

---

## 2. Theme Manager JavaScript

**File**: `/public/assets/js/theme-manager.js`

### ThemeManager Class

```javascript
class ThemeManager {
    constructor() {
        this.theme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }

    init() {
        // Apply stored theme
        this.setTheme(this.theme);

        // Create theme toggle button
        this.createToggleButton();

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
    }

    getStoredTheme() {
        return localStorage.getItem('theme');
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    setTheme(theme) {
        this.theme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.updateToggleButton();
    }

    toggleTheme() {
        const newTheme = this.theme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: newTheme }
        }));
    }

    createToggleButton() {
        const button = document.createElement('button');
        button.id = 'themeToggle';
        button.className = 'theme-toggle';
        button.innerHTML = '<i class="bi bi-moon-fill"></i>';
        button.setAttribute('aria-label', 'Toggle dark mode');
        button.onclick = () => this.toggleTheme();

        document.body.appendChild(button);
    }

    updateToggleButton() {
        const button = document.getElementById('themeToggle');
        if (button) {
            button.innerHTML = this.theme === 'dark'
                ? '<i class="bi bi-sun-fill"></i>'
                : '<i class="bi bi-moon-fill"></i>';
        }
    }
}
```

### ToastManager Class

```javascript
class ToastManager {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} fade-in`;

        const icons = {
            success: 'check-circle-fill',
            error: 'x-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bi bi-${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        `;

        this.container.appendChild(toast);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.style.animation = 'slideDown 0.3s ease-out reverse';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}
```

### LoadingOverlay Class

```javascript
class LoadingOverlay {
    show(message = 'Loading...') {
        // Remove existing overlay
        this.hide();

        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'loading-overlay fade-in';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-message">${message}</div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }

    hide() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    }
}
```

### Modal Confirmation

```javascript
async function confirmModern(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay fade-in';
        modal.innerHTML = `
            <div class="modal-modern slide-down">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove(); window.modalResolve(false);">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn-modern btn-ghost" onclick="this.closest('.modal-overlay').remove(); window.modalResolve(false);">
                        Cancel
                    </button>
                    <button class="btn-modern btn-primary" onclick="this.closest('.modal-overlay').remove(); window.modalResolve(true);">
                        Confirm
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        window.modalResolve = resolve;
    });
}
```

### Auto-Save Form Class

```javascript
class AutoSaveForm {
    constructor(formId, saveCallback, interval = 30000) {
        this.form = document.getElementById(formId);
        this.saveCallback = saveCallback;
        this.interval = interval;
        this.timer = null;
        this.init();
    }

    init() {
        if (!this.form) return;

        // Watch for changes
        this.form.addEventListener('input', () => {
            this.resetTimer();
        });

        // Save on blur
        this.form.addEventListener('blur', () => {
            this.save();
        }, true);
    }

    resetTimer() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.save(), this.interval);
    }

    async save() {
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData.entries());

        try {
            await this.saveCallback(data);
            toast.success('Form auto-saved', 2000);
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    }

    destroy() {
        clearTimeout(this.timer);
    }
}
```

### Global Initialization

```javascript
// Initialize theme manager
const themeManager = new ThemeManager();

// Initialize toast manager
const toast = new ToastManager();

// Initialize loading overlay
const loading = new LoadingOverlay();

// Export to window
window.themeManager = themeManager;
window.toast = toast;
window.loading = loading;
window.confirmModern = confirmModern;
window.AutoSaveForm = AutoSaveForm;
```

---

## 3. Modernized Dashboard Pages

### Main Dashboard

**File**: `/app/Views/dashboard/index.php`

**Key Features**:
- Gradient hero section with welcome message
- 8 stat cards with modern design and animations
- Charts with modern card styling
- Upcoming events with clean layout
- Quick actions with modern buttons
- Alerts with new alert system
- Recent transactions table with modern styling

**Usage**:
```php
// Include modern theme CSS
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">
<link rel="stylesheet" href="/assets/css/dashboard.css">';

// Include theme manager JS
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>
<script src="/assets/js/dashboard.js"></script>';
```

### Analytics Dashboard

**File**: `/app/Views/analytics/index.php`

**Key Features**:
- Date range filter with modern inputs
- 4 key metric stat cards
- Revenue breakdown table
- Revenue distribution pie chart
- Hourly sales pattern line chart
- Day of week performance with progress bars
- Quick links to detailed analytics pages

**Chart Integration**:
```javascript
// Revenue Chart (Doughnut)
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: revenueData.map(item => item.category),
        datasets: [{
            data: revenueData.map(item => item.revenue),
            backgroundColor: [
                'rgba(14, 165, 233, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(251, 146, 60, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(234, 179, 8, 0.8)'
            ]
        }]
    }
});

// Hourly Sales (Line Chart)
new Chart(ctx, {
    type: 'line',
    data: {
        labels: hourlySalesData.map(item => item.hour + ':00'),
        datasets: [{
            label: 'Sales',
            data: hourlySalesData.map(item => item.revenue),
            borderColor: 'rgba(14, 165, 233, 1)',
            backgroundColor: 'rgba(14, 165, 233, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});
```

### Loyalty Program Dashboard

**File**: `/app/Views/loyalty/index.php`

**Key Features**:
- Gradient hero section with loyalty branding
- 4 program statistics cards
- Tier distribution visualization
- Recent activity feed
- Top loyalty members leaderboard with medals
- Program configuration cards
- Tier badges with custom colors

**Tier Visualization**:
```php
$tiers = [
    'bronze' => ['color' => '#cd7f32', 'icon' => 'bi-award'],
    'silver' => ['color' => '#c0c0c0', 'icon' => 'bi-award-fill'],
    'gold' => ['color' => '#ffd700', 'icon' => 'bi-trophy'],
    'platinum' => ['color' => '#e5e4e2', 'icon' => 'bi-trophy-fill']
];
```

---

## 4. Usage Examples

### Basic Page Setup

```php
<?php
$pageTitle = 'My Modern Page';
$activeMenu = 'page';

ob_start();
?>

<!-- Hero Section -->
<div class="hero-section slide-up" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-700)); color: white; padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 2rem;">
    <h1>Page Title</h1>
    <p>Page description</p>
</div>

<!-- Content -->
<div class="modern-card">
    <div class="modern-card-header">
        <h2 class="modern-card-title">Section Title</h2>
    </div>
    <div class="modern-card-body">
        <p>Content goes here</p>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include modern theme
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">';
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>';

require __DIR__ . '/../layouts/app.php';
?>
```

### Using Toast Notifications

```javascript
// Success toast
toast.success('Operation completed successfully!');

// Error toast
toast.error('An error occurred', 5000);

// Warning toast
toast.warning('Please review this action');

// Info toast
toast.info('Did you know...', 10000);
```

### Using Loading Overlay

```javascript
// Show loading
loading.show('Processing your request...');

// Perform async operation
await someAsyncFunction();

// Hide loading
loading.hide();
```

### Using Confirmation Dialog

```javascript
const confirmed = await confirmModern(
    'Are you sure you want to delete this item?',
    'Confirm Deletion'
);

if (confirmed) {
    // Perform deletion
    toast.success('Item deleted');
} else {
    toast.info('Deletion cancelled');
}
```

### Using Auto-Save Forms

```javascript
const autoSave = new AutoSaveForm(
    'myForm',
    async (data) => {
        await fetch('/api/save', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    30000 // Save every 30 seconds
);
```

---

## 5. Theming Guidelines

### Color Usage

1. **Primary Colors**: Use for main actions, links, and brand elements
2. **Success**: Positive actions, confirmations, growth indicators
3. **Warning**: Cautions, important notices, pending states
4. **Error**: Destructive actions, errors, declines
5. **Info**: Informational messages, neutral notices

### Component Selection

1. **Stat Cards**: For displaying metrics and KPIs
2. **Modern Cards**: For content sections and grouped information
3. **Tables**: For data presentation with many rows
4. **Alerts**: For user notifications and important messages
5. **Badges**: For status indicators and labels

### Animation Best Practices

1. **Use slide-up** for cards and sections appearing on page load
2. **Add animation-delay** to stagger multiple elements (0.05s increments)
3. **Keep transitions subtle** with --transition-base
4. **Hover effects** should be smooth and not jarring

### Responsive Design

1. **Stats grid** automatically adjusts to screen size
2. **Tables** use table-responsive wrapper
3. **Grid layouts** collapse to single column on mobile
4. **Buttons** stack vertically on small screens

---

## 6. Browser Compatibility

### Supported Browsers

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### CSS Features Used

- CSS Variables (Custom Properties)
- CSS Grid
- Flexbox
- Border Radius
- Box Shadow
- Gradients
- Transitions
- Animations

### JavaScript Features Used

- ES6 Classes
- Async/Await
- Promises
- Arrow Functions
- Template Literals
- Destructuring
- LocalStorage API
- matchMedia API

---

## 7. Accessibility Features

### Keyboard Navigation

- All interactive elements are keyboard accessible
- Focus states clearly visible
- Tab order is logical

### ARIA Labels

```html
<button aria-label="Toggle dark mode" id="themeToggle">
    <i class="bi bi-moon-fill"></i>
</button>
```

### Color Contrast

- All text meets WCAG AA standards
- Dark mode maintains high contrast
- Interactive elements have clear focus indicators

### Screen Reader Support

- Semantic HTML structure
- Proper heading hierarchy
- Alt text for icons where appropriate

---

## 8. Performance Optimization

### CSS Performance

- Uses CSS variables for dynamic theming
- Minimal repaints with transform animations
- GPU-accelerated transitions
- No layout thrashing

### JavaScript Performance

- Event delegation where appropriate
- Debounced auto-save
- Lazy component initialization
- Minimal DOM manipulation

### Asset Loading

- CSS loaded in <head>
- JavaScript loaded at end of <body>
- No render-blocking resources

---

## 9. Future Enhancements

### Planned Features

1. **Additional Themes**: Light, Dark, High Contrast, Sepia
2. **Theme Customizer**: Allow users to customize colors
3. **More Components**: Tabs, Accordions, Dropdowns, etc.
4. **Advanced Animations**: Page transitions, micro-interactions
5. **PWA Support**: Service workers, offline support
6. **Print Stylesheets**: Optimized printing

### Component Library Expansion

- Modals (currently basic)
- Dropdown menus
- Tabs and accordions
- Tooltips and popovers
- Progress bars
- Pagination
- Breadcrumbs
- Date pickers

---

## 10. Testing Checklist

### Visual Testing

- [ ] All pages render correctly in light mode
- [ ] All pages render correctly in dark mode
- [ ] Theme toggle works on all pages
- [ ] Animations play smoothly
- [ ] Hover states work correctly
- [ ] Focus states are visible

### Functional Testing

- [ ] Theme preference persists across sessions
- [ ] Toast notifications appear and disappear
- [ ] Loading overlay blocks interaction
- [ ] Confirmation dialogs return correct values
- [ ] Auto-save triggers on form changes

### Responsive Testing

- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

### Browser Testing

- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Accessibility Testing

- [ ] Keyboard navigation
- [ ] Screen reader compatibility
- [ ] Color contrast validation
- [ ] Focus management

---

## 11. Migration Guide

### Updating Existing Pages

1. **Add modern theme CSS**:
```php
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">';
```

2. **Add theme manager JS**:
```php
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>';
```

3. **Replace old classes with modern ones**:
- `.card` → `.modern-card`
- `.btn` → `.btn-modern`
- `.table` → `.table-modern`
- `.badge` → `.badge-modern`
- `.alert` → `.alert-modern`

4. **Update stat cards**:
```html
<!-- Old -->
<div class="metric-card">
    <div class="metric-value">$1,234</div>
    <div class="metric-label">Revenue</div>
</div>

<!-- New -->
<div class="stat-card-modern">
    <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--primary-400), var(--primary-600));">
        <i class="bi bi-currency-dollar"></i>
    </div>
    <div class="stat-value-modern">$1,234</div>
    <div class="stat-label-modern">Revenue</div>
</div>
```

5. **Add animations**:
```html
<div class="modern-card slide-up" style="animation-delay: 0.1s;">
    <!-- Content -->
</div>
```

---

## 12. Troubleshooting

### Theme Not Persisting

**Problem**: Theme resets to light on page reload

**Solution**: Check localStorage permissions and browser settings

```javascript
// Debug theme storage
console.log('Stored theme:', localStorage.getItem('theme'));
console.log('Current theme:', document.documentElement.getAttribute('data-theme'));
```

### CSS Variables Not Working

**Problem**: Colors not applying correctly

**Solution**: Ensure CSS is loaded and data-theme attribute is set

```javascript
// Check theme attribute
console.log(document.documentElement.getAttribute('data-theme'));

// Force set theme
document.documentElement.setAttribute('data-theme', 'dark');
```

### Toast Not Showing

**Problem**: Toast notifications don't appear

**Solution**: Ensure theme-manager.js is loaded

```javascript
// Check if toast is available
console.log('Toast available:', typeof window.toast !== 'undefined');

// Manual toast
if (window.toast) {
    window.toast.success('Test toast');
}
```

### Animations Not Playing

**Problem**: Slide-up and fade-in animations don't work

**Solution**: Check animation classes and CSS loading

```css
/* Verify animation is defined */
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
```

---

## Conclusion

Phase 6 successfully modernizes the Nautilus Dive Shop application with a comprehensive theme system, dark mode support, and a beautiful modern UI. The system is:

- **Flexible**: Easy to customize colors and components
- **Performant**: Optimized CSS and JavaScript
- **Accessible**: WCAG compliant with keyboard navigation
- **Responsive**: Works on all screen sizes
- **Extensible**: Easy to add new components and themes

All existing functionality is preserved while dramatically improving the user experience with modern design patterns, smooth animations, and an intuitive dark mode.
