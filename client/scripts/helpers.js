// Update CSS3 polyfill for all components
function updateCSS3Tricks() {
    if (window.PIE) {
        $('*').each(function () {
            PIE.attach(this);
        });
    }
}
