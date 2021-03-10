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
