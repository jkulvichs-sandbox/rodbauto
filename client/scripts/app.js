/**
 * Class to control the app representation
 * @constructor
 */
function App() {
}

// Filters form control
App.prototype.filters = {
    name: {
        getValue: function () {
            return $("#filter-name").val();
        },
        setValue: function (val) {
            $("#filter-name").val(val);
        }
    },
    birthYear: {
        getValue: function () {
            return $("#filter-birth-year").val();
        },
        setValue: function (val) {
            $("#filter-birth-year").val(val);
        }
    },
    recruitOffice: {
        getValue: function () {
            return $("#filter-recruit-office").val();
        },
        setValue: function (val) {
            $("#filter-recruit-office").val(val);
        },
        // Set list of key=>val
        setList: function (list) {
            $("#filter-recruit-office option").remove();

            for (var i = 0; i < list.length; i++) {
                var option = document.createElement("option");
                option.setAttribute("value", list[i].id);
                option.innerText = list[i].name;
                if (list[i].disabled === true) option.disabled = true;
                $("#filter-recruit-office").append(option);
            }
        }
    },
    personalID: {
        getValue: function () {
            return $("#filter-personal-id").val();
        },
        setValue: function (val) {
            $("#filter-personal-id").val(val);
        }
    },
    localCommand: {
        getValue: function () {
            return $("#filter-local-command").val();
        },
        setValue: function (val) {
            $("#filter-local-command").val(val);
        }
    },
    localCommandNotEmpty: {
        getValue: function () {
            return $("#filter-local-command-not-empty").prop("checked")
        },
        setValue: function (val) {
            $("#filter-local-command-not-empty").prop("checked", val)
        }
    },
    // Return all filters in one object
    getAll: function () {
        return {
            name: this.name.getValue(),
            birthYear: this.birthYear.getValue(),
            recruitOffice: this.recruitOffice.getValue(),
            personalID: this.personalID.getValue(),
            localCommand: this.localCommand.getValue(),
            localCommandNotEmpty: this.localCommandNotEmpty.getValue()
        };
    },
    // Set all filters equal to the list specified
    setAll: function (filters) {
        if (filters.name !== undefined) this.name.setValue(filters.name);
        if (filters.birthYear !== undefined) this.birthYear.setValue(filters.birthYear);
        if (filters.recruitOffice !== undefined) this.recruitOffice.setValue(filters.recruitOffice);
        if (filters.personalID !== undefined) this.personalID.setValue(filters.personalID);
        if (filters.localCommand !== undefined) this.localCommand.setValue(filters.localCommand);
        if (filters.localCommandNotEmpty !== undefined) this.localCommandNotEmpty.setValue(filters.localCommandNotEmpty);
    },

    // Set search handler
    setSearchHandler: function (handler) {
        $("#search-btn").click(handler);
    }
}

App.prototype.action = {
    show: function (text, action, callback) {
        $("#action-text").text(text);
        $("#action-btn").text(action);
        $("#action-btn").off("click");
        $("#action-btn").click(callback);
        $("#action").fadeIn(0);
    },
    hide: function () {
        $("#action").hide();
    }
}

// Resulting table control
App.prototype.table = {
    hide: function () {
        $("#results").hide();
    },
    show: function () {
        $("#results").fadeIn(0);
    },
    clear: function () {
        $("#results div").remove();
    },
    append: function (domContainer, card, onExtraChanged) {
        // <div class="table-row">
        //     <div class="row-index">№</div>
        //     <div class="row-recruit-office">Военкомат</div>
        //     <div class="row-name">ФИО</div>
        //     <div class="row-birth">г.р.</div>
        //     <div class="row-personal-id">Жетон</div>
        //     <div class="row-local-command">Команда</div>
        //     <div class="row-comment">Примечание</div>
        // </div>

        // Main Person's Card
        var domCard = document.createElement("div");
        domCard.className = card.special ? "table-row-special" : "table-row";

        // Index Column
        var domIndex = document.createElement("div");
        domIndex.className = "row-index";
        domIndex.innerText = card.index || " ";
        domCard.appendChild(domIndex);

        // Recruit Office Column
        var domRecruitOffice = document.createElement("div");
        domRecruitOffice.className = "row-recruit-office";
        domRecruitOffice.innerText = card.recruitOfficeName || " ";
        domCard.appendChild(domRecruitOffice);

        // Name Column
        var domName = document.createElement("div");
        domName.className = "row-name";
        domName.innerText = card.name || " ";
        domCard.appendChild(domName);

        // Birth Date Column
        var domBirth = document.createElement("div");
        domBirth.className = "row-birth";
        domBirth.innerText = card.birth || " ";
        domCard.appendChild(domBirth);

        // Personal ID Column
        var domPersonalID = document.createElement("div");
        domPersonalID.className = "row-personal-id";
        domPersonalID.innerText = card.personalID || " ";
        domCard.appendChild(domPersonalID);

        // Local Comment Field
        var domLocalCommandField = document.createElement("input");
        domLocalCommandField.className = "row-local-command-field";
        domLocalCommandField.value = card.command || "";
        // Local Command Column
        var domLocalCommand = document.createElement("div");
        domLocalCommand.className = "row-local-command";
        domLocalCommand.appendChild(domLocalCommandField);
        domCard.appendChild(domLocalCommand);

        // Comment Field
        var domCommentField = document.createElement("input");
        domCommentField.className = "row-comment-field";
        domCommentField.value = card.comment || "";
        // Comment Column
        var domComment = document.createElement("div");
        domComment.className = "row-comment";
        domComment.appendChild(domCommentField);
        domCard.appendChild(domComment);

        // Handlers
        var updateExtra = function () {
            onExtraChanged(card.id, {
                comment: domCommentField.value,
                localCommand: domLocalCommandField.value,
                special: false
            })
        }
        $(domCommentField).on("change", updateExtra);
        $(domCommentField).on("keyup", function () {
            // Limit for input length
            if (domCommentField.value.length > 46) {
                domCommentField.value = domCommentField.value.substr(0, 46);
            }
            updateExtra();
        });
        $(domLocalCommandField).on("change", updateExtra);
        $(domLocalCommandField).on("keyup", function () {
            // Limit for input length
            if (domLocalCommandField.value.length > 3) {
                domLocalCommandField.value = domLocalCommandField.value.substr(0, 3);
            }
            updateExtra();
        });

        domContainer.appendChild(domCard);
    },
    // stepCallback - callback with added - count of rendered cards now and total - total count of cards
    appendAll: function (cards, stepCallback, finishCallback, onExtraChanged) {
        var step = stepCallback || function (prc) {
        };
        var that = this;
        var finish = finishCallback || function () {
        };

        // appends several cards from array
        var appendSeveral = function (domContainer, cards, start, end, callback) {
            var to = end;
            if (to > cards.length) to = cards.length;
            for (var i = start; i < to; i++) {
                cards[i].index = i + 1;
                that.append(domContainer, cards[i], onExtraChanged);
            }

            var prc = end / cards.length;
            if (prc > 1) prc = 1;
            step(prc);

            if (end < cards.length) {
                setTimeout(function () {
                    appendSeveral(domContainer, cards, start + 500, end + 500, callback);
                }, 100);
            } else {
                callback();
            }
        }

        // appends several cards and call callback
        // var appendCount = 100;
        // for (var i = 0; i < cards.length / appendCount; i++) {
        //     // (function (i) {
        //     //     setTimeout(function () {
        //     //         appendSeveral(cards, i * appendCount, i * appendCount + appendCount);
        //     //         step(i * appendCount, cards.length);
        //     //     }, i * 200);
        //     // })(i);
        //     // appendSeveral(cards, i * appendCount, i * appendCount + appendCount);
        //     // step(i * appendCount, cards.length);
        // }

        var domContainer = document.createElement("div");

        appendSeveral(domContainer, cards, 0, 500, function () {
            that.clear();
            $("#results").append(domContainer);
            finish();
        });
    }
}

/**
 * Hint Line
 */
App.prototype.hintLine = {
    set: function (text) {
        $("#hint-line-text").text(text);
        this.show();
    },
    get: function () {
        return $("#hint-line-text").text();
    },
    show: function () {
        $("#hint-line").fadeIn(0);
    },
    hide: function () {
        $("#hint-line").hide();
    }
}

/**
 * Load Spinner
 */
App.prototype.spinner = {
    hide: function () {
        $("#spinner").hide();
    },
    show: function () {
        $("#spinner").fadeIn(0);
    }
}

/**
 * Hide all other pages but selected
 */
App.prototype.showPage = function (name) {
    var pages = $(".page");
    for (var i = 0; i < pages.length; i++) {
        if (pages[i].attributes["name"].value === name) {
            $(pages[i]).fadeIn(0);
        } else {
            $(pages[i]).hide();
        }
    }
}

/**
 * Calc total data and make a table
 */
App.prototype.calcTotal = function (total) {
    // Map for deliveries and time
    // Delivery date (unix seconds) -> ROID with value count
    var deliveries = {};

    // Calc for deliveries matrix
    for (var roid in total) {
        var ro = total[roid];
        for (var i = 0; i < ro.deliveries.length; i++) {
            if (!deliveries[ro.deliveries[i].date]) {
                deliveries[ro.deliveries[i].date] = {};
            }
            deliveries[ro.deliveries[i].date][roid] = ro.deliveries[i].count;
        }
    }

    // Get table container
    var container = $("#total-container");

    // Create table element
    var table = document.createElement("table");
    container.append(table);

    // Create table header row
    var tableTr = document.createElement("tr");
    table.appendChild(tableTr);

    // Create table header cells
    {
        var titles = [];
        titles.push("ВК");
        titles.push("На учете");
        titles.push("В БД");
        titles.push("%");
        for (var date in deliveries) {
            var d = new Date(date * 1000);
            titles.push(d.getDate() + "." + (d.getMonth() + 1));
        }
        titles.push("Всего");
        titles.push("Задание");
        titles.push("%");

        var headers = [];
        titles.forEach(function (title) {
            var th = document.createElement("th");
            $(th).text(title);
            headers.push(th);
        });
        headers.forEach(function (header) {
            $(header).prop("scope", "col");
            tableTr.appendChild(header);
        });
    }

    var calcPrc = function (val, total) {
        var ratio = val/total;
        return ((ratio * 10000)|0)/100;
    }

    // Create table rows
    for (var roid in total) {
        var row = document.createElement("tr");
        table.appendChild(row);

        ro = total[roid];

        // Create table row cells
        {
            var titles = [];
            titles.push(ro.recruitOffice);
            titles.push(ro.registered);
            titles.push(ro.stored);
            titles.push(calcPrc(ro.stored, ro.registered) + "%");
            var dsum = 0;
            for (var date in deliveries) {
                var d = deliveries[date][roid];
                if (d) dsum += d;
                titles.push(d);
            }
            titles.push(dsum);
            titles.push(ro.taskPlan);
            titles.push(calcPrc(dsum, ro.taskPlan) + "%");

            titles.forEach(function (title) {
                var td = document.createElement("td");
                $(td).text(title);
                row.appendChild(td);
            });
        }
    }
}
