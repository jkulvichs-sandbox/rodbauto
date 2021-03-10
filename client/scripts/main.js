/**
 * Main App logic
 */
window.onload = function () {
    // PIE3 CSS3 polyfill for IE
    // Needs to be updated every time when CSS changes
    initCSS3Polyfill();

    // Server API
    var api = new API();
    // App API & interaction
    var app = new App();

    // Wait for all server's resources loaded
    // Here you can safely work with form inputs
    installResources(api, app, function () {

        // Restore filters' values from location hash
        app.filters.setAll(queryhash.get());
        // Make search
        search(api, app);

        // Reaction when search button pressed
        app.filters.setSearchHandler(function () {
            // Save search params into location hash
            queryhash.set(app.filters.getAll());
            // Make search
            search(api, app);
        });
    });
}

/**
 * Load and apply app resources from the server
 */
function installResources(api, app, next) {
    // Get list of recruit offices from server and set modified list into select
    api.getRecruitOffices(function (resp) {
        var list = $.merge(resp, [
            {id: "", name: ""}
        ]);
        app.filters.recruitOffice.setList(list);
        next();
    });
}

function search(api, app, next) {
    next();
}
