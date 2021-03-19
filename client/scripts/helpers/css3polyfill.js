// Update CSS3 polyfill for all components
function initCSS3Polyfill(interval) {
    log.warn("PIE3 Polyfill", "CSS3 polyfill trying to start" + (interval ? (" every " + interval + "ms") : ""));
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
