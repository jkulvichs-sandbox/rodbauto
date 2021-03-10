// Update CSS3 polyfill for all components
function initCSS3Polyfill(interval) {
    var fixCSS3 = function () {
        if (window.PIE) {
            $('*').each(function () {
                PIE.attach(this);
            });
        }
    }
    fixCSS3();
    if (interval) setTimeout(fixCSS3, interval)
}
