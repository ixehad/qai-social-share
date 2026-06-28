document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-kas-copy-url]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-kas-copy-url');
            var originalText = btn.textContent;

            function showCopied() {
                btn.textContent = 'Copied!';
                btn.classList.add('kas-copied');
                setTimeout(function () {
                    btn.textContent = originalText;
                    btn.classList.remove('kas-copied');
                }, 1800);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(showCopied).catch(function () {
                    fallbackCopy(url, showCopied);
                });
            } else {
                fallbackCopy(url, showCopied);
            }
        });
    });

    function fallbackCopy(text, onSuccess) {
        var temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.focus();
        temp.select();
        try {
            document.execCommand('copy');
            onSuccess();
        } catch (e) {
            // Silently ignore; clipboard access may be blocked in this context.
        }
        document.body.removeChild(temp);
    }
});
