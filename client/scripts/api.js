/**
 * Server API implementation
 * @constructor
 */
function API(errCallback, baseURL) {
    $(document).ajaxError(errCallback || function (event, jqxhr, settings, thrownError) {
        log.error("AJAX Unhandled Error", thrownError, JSON.stringify(event));
        alert("Ошибка подключения. Проверьте своё интернет-соединение и обновите страницу");
    });

    this.baseURL = baseURL || "/server/api";
    // this.baseURL = baseURL || "/rodb/server/api";
}

/**
 * Get list of recruit offices with {"office_ID": "office name"} association
 * @param result
 */
API.prototype.getRecruitOffices = function (result) {
    var url = this.baseURL + "/recruit-offices/list.php";
    $.get(url, function (resp) {
        log.info("API Search", url, resp);
        for (var i = 0; i < resp.data.length; i++) {
            resp.data[i].name = (i + 1) + ". " + resp.data[i].name;
        }
        result(resp.data);
    });
}

/**
 * Get list of recruit offices with {"office_ID": "office name"} association
 * @param result
 */
API.prototype.search = function (filters, result) {
    var paramsPairs = ["rand=" + Math.random()];
    for (var filter in filters) {
        if (filters[filter]) {
            paramsPairs[paramsPairs.length] = filter + "=" + encodeURI(filters[filter]);
        }
    }
    var params = paramsPairs.join("&");

    var url = this.baseURL + "/persons/search.php?" + params;
    $.get(url, function (resp) {
        log.info("API Search", url, resp);
        result(resp.data);
    });
}

/**
 * Update person's extra info
 * @param result
 */
API.prototype.updateExtra = function (personID, extra, result) {
    var url = this.baseURL + "/persons/extra.php?rand=" + Math.random() + "&id=" + personID;
    $.ajax({
        url: url,
        contentType: "application/json",
        dataType: "json",
        method: "PUT",
        data: JSON.stringify(extra),
        success: function (resp) {
            log.info("API UpdateExtra", url, resp);
            result(resp.data);
        }
    });
}
