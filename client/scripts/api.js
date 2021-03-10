/**
 * Server API implementation
 * @constructor
 */
function API(errCallback, baseURL) {
    $(document).ajaxError(errCallback || function (event, jqxhr, settings, thrownError) {
        alert("jQuery AJAX unhandled error: " + event + ": " + thrownError);
    });

    this.baseURL = baseURL || "/server/api";
}

/**
 * Get list of recruit offices with {"office_ID": "office name"} association
 * @param result
 */
API.prototype.getRecruitOffices = function (result) {
    $.get(this.baseURL + "/recruit-offices/list.php", function (resp) {
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

    $.get(this.baseURL + "/persons/search.php?" + params, function (resp) {
        result(resp.data);
    });
}
