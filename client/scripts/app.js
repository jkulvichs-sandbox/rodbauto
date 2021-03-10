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

            for (var i = list.length - 1; i >= 0; i--) {
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
    // Return all filters in one object
    getAll: function () {
        return {
            name: this.name.getValue(),
            birthYear: this.birthYear.getValue(),
            recruitOffice: this.recruitOffice.getValue(),
            personalID: this.personalID.getValue(),
            localCommand: this.localCommand.getValue()
        };
    },
    // Set all filters equal to the list specified
    setAll: function (filters) {
        if (filters.name !== undefined) this.name.setValue(filters.name);
        if (filters.birthYear !== undefined) this.birthYear.setValue(filters.birthYear);
        if (filters.recruitOffice !== undefined) this.recruitOffice.setValue(filters.recruitOffice);
        if (filters.personalID !== undefined) this.personalID.setValue(filters.personalID);
        if (filters.localCommand !== undefined) this.localCommand.setValue(filters.localCommand);
    },

    // Set search handler
    setSearchHandler: function (handler) {
        $("#search-btn").click(handler);
    }
}

