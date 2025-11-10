<?php
/**
 * Language Switcher Component
 * Dropdown for changing application language
 */

use App\Core\Translator;

$translator = Translator::getInstance();
$currentLocale = $translator->getLocale();
$availableLocales = $translator->getAvailableLocales();
?>

<div class="language-switcher dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-globe"></i>
        <span class="d-none d-sm-inline ms-1"><?= $availableLocales[$currentLocale] ?? 'English' ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
        <?php foreach ($availableLocales as $code => $name): ?>
            <li>
                <a class="dropdown-item <?= $code === $currentLocale ? 'active' : '' ?>"
                   href="#"
                   data-locale="<?= $code ?>"
                   onclick="changeLanguage('<?= $code ?>'); return false;">
                    <?php if ($code === $currentLocale): ?>
                        <i class="bi bi-check me-2"></i>
                    <?php else: ?>
                        <span class="me-4"></span>
                    <?php endif; ?>
                    <?= $name ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
function changeLanguage(locale) {
    // Show loading indicator
    const switcher = document.querySelector('.language-switcher button');
    const originalContent = switcher.innerHTML;
    switcher.innerHTML = '<i class="bi bi-hourglass-split"></i> <span class="d-none d-sm-inline ms-1">Loading...</span>';
    switcher.disabled = true;

    // Send AJAX request to change language
    fetch('/settings/change-locale', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ locale: locale })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to apply new language
            window.location.reload();
        } else {
            // Restore button and show error
            switcher.innerHTML = originalContent;
            switcher.disabled = false;
            alert(data.error || 'Failed to change language');
        }
    })
    .catch(error => {
        // Restore button and show error
        switcher.innerHTML = originalContent;
        switcher.disabled = false;
        console.error('Error changing language:', error);
        alert('Failed to change language. Please try again.');
    });
}
</script>

<style>
.language-switcher .dropdown-menu {
    min-width: 200px;
}

.language-switcher .dropdown-item {
    display: flex;
    align-items: center;
}

.language-switcher .dropdown-item.active {
    background-color: var(--bs-primary);
    color: white;
    font-weight: 500;
}

.language-switcher .dropdown-item:hover:not(.active) {
    background-color: var(--bs-light);
}

.language-switcher .bi-globe {
    font-size: 1.1em;
}

.language-switcher .bi-check {
    color: white;
    font-weight: bold;
}
</style>
