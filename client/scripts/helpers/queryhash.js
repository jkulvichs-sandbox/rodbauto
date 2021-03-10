var queryhash = {
    /**
     * Set URL params object into location hash.
     * Omits empty strings.
     * @param params
     */
    set: function (params) {
        var hashParams = [];
        for (var key in params) {
            if (params[key]) hashParams[hashParams.length] = key + "=" + encodeURI(params[key]);
        }
        location.hash = hashParams.join("&");
    },
    /**
     * Get URL params object from location hash
     */
    get: function () {
        var hashPairs = location.hash.substr(1).split(/&/g);
        var params = {};
        for (var i = 0; i < hashPairs.length; i++) {
            var pair = hashPairs[i].split("=");
            params[pair[0]] = decodeURI(pair[1]);
        }
        return params;
    }
}
