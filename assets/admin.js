document.addEventListener('DOMContentLoaded', function () {

    // ── Enable/disable dimming ────────────────────────────────────────────
    function wireEnableToggle(checkboxId, fieldsId) {
        var cb = document.getElementById(checkboxId);
        var fields = document.getElementById(fieldsId);
        if (!cb || !fields) return;

        function applyState() {
            if (cb.checked) {
                fields.style.opacity = '';
                fields.style.pointerEvents = '';
            } else {
                fields.style.opacity = '0.45';
                fields.style.pointerEvents = 'none';
            }
        }

        cb.addEventListener('change', applyState);
        applyState();
    }

    wireEnableToggle('kas-ai-enabled',     'kas-ai-fields');
    wireEnableToggle('kas-social-enabled', 'kas-social-fields');

    // ── Button shape live preview ─────────────────────────────────────────
    var shapeSelect = document.querySelector('select[name="kas_settings[button_shape]"]');
    if (shapeSelect) {
        shapeSelect.addEventListener('change', function () {
            var previews = document.querySelectorAll('#kas-preview-ai .kas-row, #kas-preview-social .kas-row');
            previews.forEach(function (row) {
                row.classList.remove('kas-shape-pill', 'kas-shape-rounded', 'kas-shape-square');
                row.classList.add('kas-shape-' + shapeSelect.value);
            });
        });
    }

    // ── Button style live preview (AI) ────────────────────────────────────
    var aiStyleSelect = document.querySelector('select[name="kas_settings[ai_button_style]"]');
    if (aiStyleSelect) {
        aiStyleSelect.addEventListener('change', function () {
            updateButtonStyle('#kas-preview-ai', aiStyleSelect.value);
        });
    }

    // ── Button style live preview (Social) ───────────────────────────────
    var socialStyleSelect = document.querySelector('select[name="kas_settings[social_button_style]"]');
    if (socialStyleSelect) {
        socialStyleSelect.addEventListener('change', function () {
            updateButtonStyle('#kas-preview-social', socialStyleSelect.value);
        });
    }

    function updateButtonStyle(containerSel, newStyle) {
        var btns = document.querySelectorAll(containerSel + ' .kas-btn');
        btns.forEach(function (btn) {
            btn.classList.remove('kas-btn-style-text', 'kas-btn-style-icon', 'kas-btn-style-icon_text');
            btn.classList.add('kas-btn-style-' + newStyle);
        });
    }

    // ── Copy link checkbox (social preview) ──────────────────────────────
    var copyLinkCb = document.querySelector('input[name="kas_settings[show_copy_link]"]');
    if (copyLinkCb) {
        copyLinkCb.addEventListener('change', function () {
            var copyBtn = document.querySelector('#kas-preview-social .kas-btn-copy');
            if (copyBtn) copyBtn.style.display = copyLinkCb.checked ? '' : 'none';
        });
    }
});
