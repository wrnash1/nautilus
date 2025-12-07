// Storefront JavaScript - Themed Online Store

// Add to Cart Function
function addToCart(productId, quantity = 1) {
    fetch('/shop/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount();

            // Show success message
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Add to Wishlist Function
function addToWishlist(productId) {
    fetch('/account/wishlist/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Added to wishlist!', 'success');
        } else {
            showNotification(data.message || 'Failed to add to wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Please login to add to wishlist', 'info');
    });
}

// Update Cart Count
function updateCartCount() {
    fetch('/shop/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge) {
                cartBadge.textContent = data.count || 0;
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Show Notification Toast
function showNotification(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(toast);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Product Quick View (Modal)
function quickView(productId) {
    // Fetch product details and show in modal
    fetch(`/shop/product/${productId}/quick-view`)
        .then(response => response.json())
        .then(data => {
            // Show modal with product details
            // Implementation depends on your modal system
            console.log('Quick view:', data);
        })
        .catch(error => console.error('Error:', error));
}

// Newsletter Subscription
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForms = document.querySelectorAll('form[action="/newsletter/subscribe"]');

    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch('/newsletter/subscribe', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Thank you for subscribing!', 'success');
                    form.reset();
                } else {
                    showNotification(data.message || 'Subscription failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        });
    });
});

// Sticky Header on Scroll
window.addEventListener('scroll', function() {
    const header = document.querySelector('.storefront-header .navbar');
    if (header && header.style.position === 'sticky') {
        if (window.scrollY > 100) {
            header.classList.add('shadow');
        } else {
            header.classList.remove('shadow');
        }
    }
});

// Lazy Load Images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// Product Image Gallery
function changeProductImage(imageSrc) {
    const mainImage = document.querySelector('.product-main-image');
    if (mainImage) {
        mainImage.src = imageSrc;
    }
}

// Search Suggestions (if enabled)
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);

        const query = this.value;
        if (query.length < 2) return;

        searchTimeout = setTimeout(() => {
            fetch(`/shop/search-suggestions?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    // Show search suggestions
                    // Implementation depends on your UI
                    console.log('Search suggestions:', data);
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });
}

// Quantity Selector
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('qty-increase')) {
        const input = e.target.previousElementSibling;
        input.value = parseInt(input.value) + 1;
    } else if (e.target.classList.contains('qty-decrease')) {
        const input = e.target.nextElementSibling;
        const newValue = parseInt(input.value) - 1;
        if (newValue >= 1) {
            input.value = newValue;
        }
    }
});

// Update on page load
updateCartCount();
