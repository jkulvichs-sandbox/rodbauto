/**
 * Work with cookies
 */
var cookie = {
    set: function (name, value, expires, path, theDomain, secure) {
        value = escape(value);
        var theCookie = name + "=" + value +
            ((expires) ? "; expires=" + expires.toGMTString() : "") +
            ((path) ? "; path=" + path : "") +
            ((theDomain) ? "; domain=" + theDomain : "") +
            ((secure) ? "; secure" : "");
        document.cookie = theCookie;
    },
    get: function (Name) {
        var search = Name + "="
        if (document.cookie.length > 0) { // if there are any cookies
            var offset = document.cookie.indexOf(search)
            if (offset != -1) { // if cookie exists
                offset += search.length
                // set index of beginning of value
                var end = document.cookie.indexOf(";", offset)
                // set index of end of cookie value
                if (end == -1) end = document.cookie.length
                return unescape(document.cookie.substring(offset, end))
            }
        }
    },
    remove: function (name, path, domain) {
        if (getCookie(name)) document.cookie = name + "=" +
            ((path) ? ";path=" + path : "") +
            ((domain) ? ";domain=" + domain : "") +
            ";expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
}
