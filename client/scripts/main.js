/**
 * Main App logic
 */
window.onload = function () {
    // Welcome notification
    log.success("RODBAuto :: The Recruit Offices Automation", "Пенза, локальная БД");

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

            // Update all page instead of concrete components
            // It's more easy for IE6
            // search(api, app);
            location.reload();
        });

        // Auto save when card's data have been changed
        // $("#filter-name").change(function() { console.log("ok"); });
    });
}

/**
 * Load and apply app resources from the server
 */
function installResources(api, app, next) {
    // Get list of recruit offices from server and set modified list into select
    api.getRecruitOffices(function (resp) {
        var list = $.merge([
            {id: "", name: ""}
        ], resp);
        app.filters.recruitOffice.setList(list);
        next();
    });
}

/**
 * Search request and data rendering
 */
function search(api, app, next) {
    app.action.hide();
    var filters = app.filters.getAll()

    var startSearch = function () {
        app.hintLine.set("Поиск и фильтрация. Это может занять продолжительное время ...");
        app.spinner.show();
        setTimeout(function () {
            api.search(filters, function (cards) {
                var ETA = (cards.length * 0.005) | 0;

                var showCards = function () {
                    app.spinner.show();
                    var startTime = +(new Date());
                    app.hintLine.set("Отображение результатов, до завершения осталось примерно " + ETA + " сек");
                    setTimeout(function () {

                        var hintMainText = "";
                        var timeoutHintMain = null;

                        app.table.appendAll(cards, function (prc) {
                            var TimeLeft = ETA - (ETA * prc) | 0;
                            if (prc < 0.9) {
                                app.hintLine.set("Отображение результатов, до завершения осталось примерно " + TimeLeft + " сек");
                            } else {
                                app.hintLine.set("Отображение результатов, до завершения осталось несколько секунд");
                            }
                        }, function () {
                            var endTime = +(new Date());
                            var FTA = ((endTime - startTime) / 1000) | 0;
                            hintMainText = "Всего результатов: " + cards.length + ", выполнено за " + (FTA + 1) + " сек";
                            app.hintLine.set(hintMainText);
                            app.spinner.hide();
                            if (next) next();
                        }, function (id, extra) {
                            app.hintLine.set("Сохранение изменений ...");
                            api.updateExtra(id, extra, function () {
                                app.hintLine.set("Изменения сохранены!");
                                clearTimeout(timeoutHintMain);
                                timeoutHintMain = setTimeout(function () {
                                    app.hintLine.set(hintMainText);
                                }, 1000);
                            });
                        });
                    }, 100);
                }

                if (cards.length > 1000) {
                    app.spinner.hide();
                    app.hintLine.hide();
                    app.action.show(
                        "Найдено " + cards.length + " записей, отображение может занять примерно " + ETA + " сек",
                        "ПРОДОЛЖИТЬ",
                        function () {
                            app.action.hide();
                            showCards();
                        });
                } else {
                    showCards();
                }

            });
        }, 200)
    }

    // Check that any filter is active
    var isEnabledFilters = false;
    for (filterName in filters) {
        if (filters[filterName]) {
            isEnabledFilters = true;
            break;
        }
    }

    // If no one filter is enabled, then print warning
    if (isEnabledFilters) {
        startSearch();
    } else {
        app.hintLine.hide();
        app.action.show(
            "Загрузка без фильтров может занять продолжительное время",
            "ПРОДОЛЖИТЬ",
            function () {
                app.action.hide();
                startSearch();
            });
    }
}
