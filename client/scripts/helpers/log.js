/**
 * Advanced logger
 */
var log = {
    isChrome: false,
    isFirefox: false,
    isFeatured: false,

    _log: function (variant, label, desc, data) {
        // For IE6
        if (!window.console) {
            if (variant === "error") alert("ОШИБКА ПРИЛОЖЕНИЯ\n\n" + label + "\n" + desc + "\n" + data);
            return;
        }

        var variants = {
            debug: {
                prefix: "DBG: ",
                style: "color: white; background: #546E7A; padding: 5px; border-radius: 5px 5px 0 0; font-weight: bold;",
                descStyle: "color: white; background: #455A64; padding: 5px; border-radius: 0 0 5px 5px; font-size: 90%;"
            },
            info: {
                prefix: "INF: ",
                style: "color: white; background: #1E88E5; padding: 5px; border-radius: 5px 5px 0 0; font-weight: bold;",
                descStyle: "color: white; background: #1976D2; padding: 5px; border-radius: 0 0 5px 5px; font-size: 90%;"
            },
            success: {
                prefix: "",
                style: "color: white; background: #7CB342; padding: 5px; border-radius: 5px 5px 0 0; font-weight: bold;",
                descStyle: "color: white; background: #689F38; padding: 5px; border-radius: 0 0 5px 5px; font-size: 90%;"
            },
            warn: {
                prefix: "WRN: ",
                style: "color: white; background: #FB8C00; padding: 5px; border-radius: 5px 5px 0 0; font-weight: bold;",
                descStyle: "color: white; background: #F57C00; padding: 5px; border-radius: 0 0 5px 5px; font-size: 90%;"
            },
            error: {
                prefix: "ERR: ",
                style: "color: white; background: #E53935; padding: 5px; border-radius: 5px 5px 0 0; font-weight: bold;",
                descStyle: "color: white; background: #D32F2F; padding: 5px; border-radius: 0 0 5px 5px; font-size: 90%;"
            }
        };
        var deco = variants[variant];
        if (this.isFeatured) {
            console.log("%c" + deco.prefix + label + "%c\n%c" + desc, deco.style, "", deco.descStyle, data);
        } else {
            console.log(deco.prefix + label + ": " + desc, data);
        }
    },

    debug: function (label, desc, data) {
        this._log("debug", label, desc, data);
    },
    info: function (label, desc, data) {
        this._log("info", label, desc, data);
    },
    success: function (label, desc, data) {
        this._log("success", label, desc, data);
    },
    warn: function (label, desc, data) {
        this._log("warn", label, desc, data);
    },
    error: function (label, desc, data) {
        this._log("error", label, desc, data);
    }
}

log.isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
log.isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
log.isFeatured = log.isChrome || log.isFirefox;
