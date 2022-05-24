/*
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
*/


$.widget("custom.combobox", {
    _create: function() {
        this.wrapper = $("<span>")
            .addClass("custom-combobox")
            .insertAfter(this.element);

        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
    },

    _createAutocomplete: function() {
        var selected = this.element.children(":selected"),
            value = selected.val() ? selected.text() : "",
            idInput = this.element.attr("ID")

        this.input = $("<input>")
            .appendTo(this.wrapper)
            .val(value)
            .attr("title", "")
            .attr("ID", "custom-combobox-" + idInput)
            .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
            .autocomplete({
                delay: 0,
                minLength: 0,
                source: $.proxy(this, "_source")
            })
            .tooltip({
                classes: {
                    "ui-tooltip": "ui-state-highlight"
                }
            });

        this._on(this.input, {
            autocompleteselect: function(event, ui) {
                ui.item.option.selected = true;
                this._trigger("select", event, {
                    item: ui.item.option
                });
            },

            autocompletechange: "_removeIfInvalid"
        });
    },

    _createShowAllButton: function() {
        var input = this.input,
            wasOpen = false

        $("<a>")
            .attr("tabIndex", -1)
            .attr("title", "Listar todos")
            .attr("height", "")
            .tooltip()
            .appendTo(this.wrapper)
            .button({
                icons: {
                    primary: "ui-icon-triangle-1-s"
                },
                text: "false"
            })
            .removeClass("ui-corner-all")
            .addClass("custom-combobox-toggle ui-corner-right")
            .on("mousedown", function() {
                wasOpen = input.autocomplete("widget").is(":visible");

                /* mustelier ------------------------------------------- */
                var widget = input.autocomplete("widget");
                var ywidget = input.offset().top - $(window).scrollTop();
                var hwidget = widget.parent().height();
                var hcontaint = $(window).height() - ywidget;

                console.log('conta=' + hcontaint);
                console.log(ywidget);
                console.log('widg=' + hwidget);

                if (hwidget > hcontaint) {
                    widget.css('max-height', hcontaint - 50);
                    widget.addClass('widget-combobox');
                }
                //----------------------------------------------------- */
            })
            .on("click", function() {
                input.trigger("focus");

                // Close if already visible
                if (wasOpen) {
                    return;
                }

                // Pass empty string as value to search for, displaying all results
                input.autocomplete("search", "");
            });
    },

    _source: function(request, response) {
        var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
        response(this.element.children("option").map(function() {
            var text = $(this).text();
            if (this.value && (!request.term || matcher.test(text)))
                return {
                    label: text,
                    value: text,
                    option: this
                };
        }));
    },

    _removeIfInvalid: function(event, ui) {
        // Selected an item, nothing to do
        if (ui.item) {
            return;
        }

        // Search for a match (case-insensitive)
        var value = this.input.val(),
            valueLowerCase = value.toLowerCase(),
            valid = false;
        this.element.children("option").each(function() {
            if ($(this).text().toLowerCase() === valueLowerCase) {
                this.selected = valid = true;
                return false;
            }
        });

        // Found a match, nothing to do
        if (valid) {
            return;
        }

        // Remove invalid value
        this.input
            .val("")
            .attr("title", value + " no tiene coincidencia con ninguna de las opciones")
            .tooltip("open");
        this.element.val("");
        this._delay(function() {
            this.input.tooltip("close").attr("title", "");
        }, 2500);
        this.input.autocomplete("instance").term = "";
    },

    _destroy: function() {
        this.wrapper.remove();
        this.element.show();
    }
});

/*
$(document).ready(function() {
    jqueryCombobox("combobox-id-1");
});
*/
/*
<div class="ui-widget">
    <label>Your preferred programming language: </label>
    <select id="combobox-id-1">
        <option value>Select one...</option>
        <option value="ActionScript">ActionScript</option>
        <option value="AppleScript">AppleScript</option>
        <option value="Asp">Asp</option>
        <option value="BASIC">BASIC</option>
        <option value="C">C</option>
        <option value="C++">C++</option>
        <option value="Clojure">Clojure</option>
        <option value="COBOL">COBOL</option>
        <option value="ColdFusion">ColdFusion</option>
        <option value="Erlang">Erlang</option>
        <option value="Fortran">Fortran</option>
        <option value="Groovy">Groovy</option>
        <option value="Haskell">Haskell</option>
        <option value="Java">Java</option>
        <option value="JavaScript">JavaScript</option>
        <option value="Lisp">Lisp</option>
        <option value="Perl">Perl</option>
        <option value="PHP">PHP</option>
        <option value="Python">Python</option>
        <option value="Ruby">Ruby</option>
        <option value="Scala">Scala</option>
        <option value="Scheme">Scheme</option>
    </select>
</div>
*/