document.addEventListener('DOMContentLoaded', function () {
    var preview = document.getElementById('kas-preview-output');
    if (!preview) {
        return; // Not on the settings page.
    }

    var showAi = document.querySelector('input[name="kas_settings[show_ai]"]');
    var showSocial = document.querySelector('input[name="kas_settings[show_social]"]');
    var buttonStyle = document.querySelector('select[name="kas_settings[button_style]"]');
    var showCopyLink = document.querySelector('input[name="kas_settings[show_copy_link]"]');

    function setRowVisibility(selector, visible) {
        var row = preview.querySelector(selector);
        if (row) {
            row.style.display = visible ? '' : 'none';
        }
    }

    function applyButtonStyle(styleValue) {
        var rows = preview.querySelectorAll('.kas-row');
        rows.forEach(function (row) {
            row.classList.remove('kas-style-pill', 'kas-style-rounded', 'kas-style-square');
            row.classList.add('kas-style-' + styleValue);
        });
    }

    function applyCopyLinkVisibility(visible) {
        var copyBtn = preview.querySelector('.kas-btn-copy');
        if (copyBtn) {
            copyBtn.style.display = visible ? '' : 'none';
        }
    }

    if (showAi) {
        showAi.addEventListener('change', function () {
            setRowVisibility('.kas-ai-row', showAi.checked);
        });
    }

    if (showSocial) {
        showSocial.addEventListener('change', function () {
            setRowVisibility('.kas-social-row', showSocial.checked);
        });
    }

    if (buttonStyle) {
        buttonStyle.addEventListener('change', function () {
            applyButtonStyle(buttonStyle.value);
        });
    }

    if (showCopyLink) {
        showCopyLink.addEventListener('change', function () {
            applyCopyLinkVisibility(showCopyLink.checked);
        });
    }
});
