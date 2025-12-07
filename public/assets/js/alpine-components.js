/**
 * Alpine.js Components for Nautilus
 * 
 * Reusable interactive components using Alpine.js
 */

// Searchable Dropdown Component
document.addEventListener('alpine:init', () => {
    Alpine.data('searchableDropdown', (options = []) => ({
        open: false,
        search: '',
        selected: null,
        options: options,

        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(option =>
                option.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        select(option) {
            this.selected = option;
            this.open = false;
            this.search = '';
            this.$dispatch('option-selected', option);
        },

        clear() {
            this.selected = null;
            this.search = '';
        }
    }));

    // Modal Component
    Alpine.data('modal', (initialOpen = false) => ({
        open: initialOpen,

        show() {
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        hide() {
            this.open = false;
            document.body.style.overflow = '';
        },

        toggle() {
            this.open ? this.hide() : this.show();
        }
    }));

    // Tabs Component
    Alpine.data('tabs', (defaultTab = 0) => ({
        activeTab: defaultTab,

        isActive(tab) {
            return this.activeTab === tab;
        },

        setActive(tab) {
            this.activeTab = tab;
        }
    }));

    // Accordion Component
    Alpine.data('accordion', () => ({
        openItems: [],

        toggle(item) {
            if (this.openItems.includes(item)) {
                this.openItems = this.openItems.filter(i => i !== item);
            } else {
                this.openItems.push(item);
            }
        },

        isOpen(item) {
            return this.openItems.includes(item);
        }
    }));

    // Notification Component
    Alpine.data('notifications', () => ({
        notifications: [],

        add(message, type = 'info', duration = 3000) {
            const id = Date.now();
            this.notifications.push({ id, message, type });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }

            return id;
        },

        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },

        success(message, duration) {
            return this.add(message, 'success', duration);
        },

        error(message, duration) {
            return this.add(message, 'error', duration);
        },

        warning(message, duration) {
            return this.add(message, 'warning', duration);
        }
    }));

    // Data Table Component
    Alpine.data('dataTable', (initialData = []) => ({
        data: initialData,
        sortColumn: null,
        sortDirection: 'asc',
        searchQuery: '',
        currentPage: 1,
        perPage: 10,

        get filteredData() {
            if (!this.searchQuery) return this.data;

            return this.data.filter(item => {
                return Object.values(item).some(value =>
                    String(value).toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            });
        },

        get sortedData() {
            if (!this.sortColumn) return this.filteredData;

            return [...this.filteredData].sort((a, b) => {
                const aVal = a[this.sortColumn];
                const bVal = b[this.sortColumn];

                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },

        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.sortedData.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.sortedData.length / this.perPage);
        },

        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        goToPage(page) {
            this.currentPage = page;
        }
    }));

    // Form Wizard Component
    Alpine.data('wizard', (totalSteps) => ({
        currentStep: 1,
        totalSteps: totalSteps,

        get isFirstStep() {
            return this.currentStep === 1;
        },

        get isLastStep() {
            return this.currentStep === this.totalSteps;
        },

        get progress() {
            return (this.currentStep / this.totalSteps) * 100;
        },

        next() {
            if (!this.isLastStep) {
                this.currentStep++;
            }
        },

        prev() {
            if (!this.isFirstStep) {
                this.currentStep--;
            }
        },

        goTo(step) {
            if (step >= 1 && step <= this.totalSteps) {
                this.currentStep = step;
            }
        }
    }));

    // Countdown Timer Component
    Alpine.data('countdown', (targetDate) => ({
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        expired: false,

        init() {
            this.updateCountdown();
            setInterval(() => this.updateCountdown(), 1000);
        },

        updateCountdown() {
            const now = new Date().getTime();
            const target = new Date(targetDate).getTime();
            const distance = target - now;

            if (distance < 0) {
                this.expired = true;
                return;
            }

            this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
        }
    }));

    // Image Upload Preview Component
    Alpine.data('imageUpload', () => ({
        preview: null,
        fileName: '',

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.fileName = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        clear() {
            this.preview = null;
            this.fileName = '';
        }
    }));
});

// Add Alpine.js CDN if not already included
if (!window.Alpine) {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
    script.defer = true;
    document.head.appendChild(script);
}
