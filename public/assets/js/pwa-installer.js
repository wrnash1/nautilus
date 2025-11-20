/**
 * PWA Installer
 * 
 * Handles PWA installation prompt and service worker registration
 */

class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.init();
    }

    init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            this.registerServiceWorker();
        }

        // Listen for install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            this.deferredPrompt = null;
            this.hideInstallButton();

            if (window.toast) {
                toast.success('Nautilus installed! You can now use it offline.');
            }
        });

        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('Running as installed PWA');
        }
    }

    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            console.log('Service Worker registered:', registration);

            // Check for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;

                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // New service worker available
                        this.showUpdateNotification(registration);
                    }
                });
            });

            // Check for updates every hour
            setInterval(() => {
                registration.update();
            }, 60 * 60 * 1000);

        } catch (error) {
            console.error('Service Worker registration failed:', error);
        }
    }

    showInstallButton() {
        // Create install button if it doesn't exist
        let installBtn = document.getElementById('pwa-install-btn');

        if (!installBtn) {
            installBtn = document.createElement('button');
            installBtn.id = 'pwa-install-btn';
            installBtn.className = 'pwa-install-button';
            installBtn.innerHTML = `
                <i class="bi bi-download"></i>
                <span>Install App</span>
            `;
            installBtn.addEventListener('click', () => this.install());

            // Add to page (top right corner)
            document.body.appendChild(installBtn);
        }

        installBtn.style.display = 'flex';
    }

    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    }

    async install() {
        if (!this.deferredPrompt) {
            return;
        }

        // Show the install prompt
        this.deferredPrompt.prompt();

        // Wait for the user's response
        const { outcome } = await this.deferredPrompt.userChoice;

        console.log(`User response to install prompt: ${outcome}`);

        if (outcome === 'accepted') {
            console.log('User accepted the install prompt');
        } else {
            console.log('User dismissed the install prompt');
        }

        this.deferredPrompt = null;
        this.hideInstallButton();
    }

    showUpdateNotification(registration) {
        if (window.toast) {
            const updateToast = toast.info(
                'A new version is available! Click to update.',
                0 // Don't auto-hide
            );

            updateToast.addEventListener('click', () => {
                if (registration.waiting) {
                    registration.waiting.postMessage({ action: 'skipWaiting' });
                    window.location.reload();
                }
            });
        }
    }
}

// Initialize PWA installer
window.pwaInstaller = new PWAInstaller();

// Add CSS for install button
const style = document.createElement('style');
style.textContent = `
    .pwa-install-button {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #0066cc, #0052a3);
        color: white;
        border: none;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        transition: all 0.3s;
        animation: slideIn 0.3s ease-out;
    }

    .pwa-install-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 102, 204, 0.4);
    }

    .pwa-install-button i {
        font-size: 1rem;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @media (max-width: 640px) {
        .pwa-install-button {
            top: auto;
            bottom: 20px;
            right: 20px;
            left: 20px;
            justify-content: center;
        }
    }
`;
document.head.appendChild(style);
