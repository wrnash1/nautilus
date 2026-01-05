/**
 * Customer Risk Display Module
 * Shows risk scores and purchase likelihood in POS
 */

// Display Customer Risk Scores in POS
window.displayRiskScores = (data) => {
    // Find or create risk display container
    let riskContainer = document.getElementById('customerRiskDisplay');

    if (!riskContainer) {
        const displayCard = document.getElementById('customerDisplayCard');
        if (displayCard) {
            const cardBody = displayCard.querySelector('.d-flex.align-items-center');
            if (cardBody) {
                const riskHtml = `<div id="customerRiskDisplay" class="d-flex gap-2 mt-2 flex-wrap w-100 pt-2 border-top"></div>`;
                cardBody.insertAdjacentHTML('afterend', riskHtml);
                riskContainer = document.getElementById('customerRiskDisplay');
            }
        }
    }

    if (!riskContainer) return;

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    // Determine colors and labels
    const returnRisk = data.return_risk || 0;
    const purchaseLikelihood = data.purchase_likelihood || 50;
    const isFlagged = data.is_flagged || false;

    let returnBadgeColor = 'success';
    if (returnRisk >= 70) returnBadgeColor = 'danger';
    else if (returnRisk >= 40) returnBadgeColor = 'warning';

    let purchaseBadgeColor = 'secondary';
    if (purchaseLikelihood >= 80) purchaseBadgeColor = 'success';
    else if (purchaseLikelihood >= 60) purchaseBadgeColor = 'info';
    else if (purchaseLikelihood >= 40) purchaseBadgeColor = 'warning';
    else purchaseBadgeColor = 'danger';

    let html = '';

    // Flagged warning
    if (isFlagged) {
        html += `
            <span class="badge bg-danger d-flex align-items-center gap-1 px-2 py-1" 
                  title="${data.flag_reason || 'Customer flagged'}" style="animation: pulse 1s infinite;">
                <i class="bi bi-exclamation-triangle-fill"></i> FLAGGED
            </span>
        `;
    }

    // Return Risk Badge
    html += `
        <span class="badge bg-${returnBadgeColor} d-flex align-items-center gap-1 px-2 py-1" 
              title="Return Risk: ${returnRisk.toFixed(0)}%">
            <i class="bi bi-arrow-return-left"></i> 
            ${data.return_risk_label || 'Minimal'}
        </span>
    `;

    // Purchase Likelihood Badge
    html += `
        <span class="badge bg-${purchaseBadgeColor} d-flex align-items-center gap-1 px-2 py-1"
              title="Purchase Likelihood: ${purchaseLikelihood.toFixed(0)}%">
            <i class="bi bi-cart-check"></i>
            ${data.purchase_likelihood_label || 'Moderate'}
        </span>
    `;

    // Lifetime Value
    if (data.lifetime_value > 0) {
        html += `
            <span class="badge bg-primary d-flex align-items-center gap-1 px-2 py-1"
                  title="Lifetime Value">
                <i class="bi bi-coin"></i>
                ${formatCurrency(data.lifetime_value)}
            </span>
        `;
    }

    // Return Count Warning
    if (data.return_count > 0) {
        html += `
            <span class="badge bg-secondary d-flex align-items-center gap-1 px-2 py-1"
                  title="Total Returns">
                <i class="bi bi-box-arrow-left"></i>
                ${data.return_count} returns
            </span>
        `;
    }

    riskContainer.innerHTML = html;

    // Add pulse animation CSS if not already added
    if (!document.getElementById('riskDisplayStyles')) {
        const style = document.createElement('style');
        style.id = 'riskDisplayStyles';
        style.textContent = `
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }
        `;
        document.head.appendChild(style);
    }
};

// Clear risk display when customer is cleared
document.addEventListener('DOMContentLoaded', function () {
    const clearBtn = document.getElementById('clearSelectedCustomerBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            const riskContainer = document.getElementById('customerRiskDisplay');
            if (riskContainer) riskContainer.remove();
        });
    }
});
