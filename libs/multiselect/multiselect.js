/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var use_undefined;

var multiselect = function(multiselectName, data, _use_undefined) {
    _use_undefined || (_use_undefined = false);
    use_undefined = _use_undefined;

    multiselect_write(multiselectName, data);
    multiselect_append(multiselectName);

    $('#' + multiselectName).append(
        "<input type='hidden' id='" + multiselectName + "_use_undefined' name='" + multiselectName + "_use_undefined' value='" + (use_undefined ? 1 : 0) + "' />"
    );
}

function multiselect_append(multiselectName) {
    var isCtrl = false;
    $(document).keydown(function(e) {
        isCtrl = e.which == 17 ? true : false;
    });
    $(document).keyup(function(e) {
        isCtrl = e.which == 17 ? false : true;
    });

    $('#' + multiselectName).click(function(e) {
        e.preventDefault();
        var item = e.target;
        var parent = $(item).parent();
        var items = null;

        if (parseInt(item.className.indexOf('input')) == -1 && parseInt($(parent).attr('class').indexOf('input')) == -1) {
            if ($(parent).attr('class').indexOf(' left') > -1) {
                var divs = $('#' + multiselectName + ' > div > div > div.right > div');
                divs.removeClass('active');

                items = $('#' + multiselectName + ' div.left > .' + multiselectName + '_' + $(item).attr('data-bind'));
            };
            if ($(parent).attr('class').indexOf(' right') > -1) {
                var divs = $('#' + multiselectName + ' > div > div > div.left > div');
                divs.removeClass('active');

                items = $('#' + multiselectName + ' div.right > .' + multiselectName + '_' + $(item).attr('data-bind'));
            };

            if (!isCtrl)
                $('.item').removeClass('active');
            if ($(item).attr('class').indexOf('active') == -1)
                $(items).addClass('active');
            else
                $(items).removeClass('active');
        }
    });

    $('#' + multiselectName + '-right').click(function(e) {
        e.preventDefault();
        moveItemPanel(multiselectName, 'right');
    });

    $('#' + multiselectName + '-rightAll').click(function(e) {
        e.preventDefault();
        moveItemPanel(multiselectName, 'rightAll');
    });

    $('#' + multiselectName + '-left').click(function(e) {
        e.preventDefault();
        moveItemPanel(multiselectName, 'left');
    });

    $('#' + multiselectName + '-leftAll').click(function(e) {
        e.preventDefault();
        moveItemPanel(multiselectName, 'leftAll');
    });

    $('select.multiselect').click(function(e) {
        e.preventDefault();
    });
}

function multiselect_write(multiselectName, data) {
    var hidden;
    var value;
    var ifmeeting;
    var div;
    var list = new Array();

    $('#' + multiselectName).append(
        "<div id='" + multiselectName + "-panel' class='container-fluid'>" +
        "<div class='row multiselect'>" +
        "<div class='col-5'>" +
        "<div class='panel-multiselect left'>" +

        "</div>" +
        "</div>" +

        "<div class='col-2'>" +
        "<button type='button' id='" + multiselectName + "-rightAll' class='btn btn-block btn-info'><i class='fa fa-forward'></i></button>" +
        "<button type='button' id='" + multiselectName + "-right' class='btn btn-block btn-info'><i class='fa fa-angle-right'></i></button>" +
        "<button type='button' id='" + multiselectName + "-left' class='btn btn-block btn-info'><i class='fa fa-angle-left'></i></button>" +
        "<button type='button' id='" + multiselectName + "-leftAll' class='btn btn-block btn-info'><i class='fa fa-backward'></i></button>" +
        "</div>" +

        "<div class='col-5'>" +
        "<div class='panel-multiselect right'>" +

        "</div>" +
        "</div>" +
        "</div>" +
        "</div>"
    );

    for (i = 0; i < data.length; i++) {
        if (!use_undefined)
            value = parseInt(data[i][2]) ? parseInt(data[i][2]) : "0";
        else
            value = data[i][2] != undefined ? parseInt(data[i][2]) : "undefined";

        ifmeeting = parseInt(data[i][4]) == 1 ? "1" : "0";

        if (!searchKey(list, data[i][0])) {
            $('#' + multiselectName).append(
                "<input type='hidden' id='" + multiselectName + "_" + data[i][0] + "' name='" + multiselectName + "_" + data[i][0] + "' value='" + value + "' />"
            );
            if ((!use_undefined && parseInt(value) > 0) || (use_undefined && value != undefined)) {
                $('#' + multiselectName).append(
                    "<input type='hidden' id='" + multiselectName + "_init_" + data[i][0] + "' name='" + multiselectName + "_init_" + data[i][0] + "' value='" + value + "' />"
                );
            }

            $('#' + multiselectName).append(
                "<input type='hidden' id='" + multiselectName + "_ifmeeting_" + data[i][0] + "' value=" + ifmeeting + " />"
            );

            list.push(data[i][0]);
        }

        if (data[i][0]) {
            hidden = (!use_undefined && data[i][2]) || (use_undefined && data[i][2] != undefined) ? " hidden" : "";
            div = "<div class='" + multiselectName + "_" + data[i][0] + " item" + hidden + "' data-bind='" + data[i][0] + "'>" + data[i][1] + "</div>";
            $('#' + multiselectName + '-panel > div > div > div.panel-multiselect.left').append(div);

            hidden = (!use_undefined && !data[i][2]) || (use_undefined && data[i][2] == undefined) ? " hidden" : "";
            div = "<div class='" + multiselectName + "_" + data[i][0] + " item" + hidden + "' data-bind='" + data[i][0] + "'>";
            div += data[i][1];

            if (data[i][3])
                div += '<br>' + data[i][3];
            div += "</div>";
            $('#' + multiselectName + '-panel > div > div > div.panel-multiselect.right').append(div);

        } else {
            div = "<div class='title " + data[i][5] + "'>" + data[i][1] + "</div>";
            $('#' + multiselectName + '-panel > div > div > div.panel-multiselect.right').append(div);
            $('#' + multiselectName + '-panel > div > div > div.panel-multiselect.left').append(div);
        }
    }
}

function moveItemPanel(multiselectName, btn) {
    var data = new Array();
    var i = 0;
    var imax = 0;
    var j = 0;

    var origen = '';
    var posting = '';
    var active = '';

    if (btn == 'right' || btn == 'rightAll') {
        origen = '.left';
        posting = '.right';
        active = '.active';
    }
    if (btn == 'left' || btn == 'leftAll') {
        origen = '.right';
        posting = '.left';
        active = '.active';
    }

    if (btn == 'rightAll' || btn == 'leftAll')
        active = '.item';

    var use_undefined = parseInt($('#' + multiselectName + '_use_undefined').val());

    var divs = $('#' + multiselectName + ' > div > div > div > div' + origen + ' > div' + active);
    divs.each(function(i) {
        if ($(this).attr('class').indexOf('hidden') == -1) {
            if (posting == '.right') {
                data.push($(this).attr('data-bind'));
                $(this).addClass('hidden');

                $('#' + multiselectName + '_' + $(this).attr('data-bind')).val(1);

            } else {
                if (parseInt($('#' + multiselectName + '_ifmeeting_' + $(this).attr('data-bind')).val()))
                    alert("Este usuario es responsable de alguna temática o tiene alguna intervención. No puede ser eliminado");
                else {
                    data.push($(this).attr('data-bind'));
                    $(this).addClass('hidden');

                    if (!use_undefined)
                        $('#' + multiselectName + '_' + $(this).attr('data-bind')).val(0);
                    else
                        $('#' + multiselectName + '_' + $(this).attr('data-bind')).val('undefined');
                }
            }
        }
    });

    divs = $('#' + multiselectName + ' > div > div > div > div' + posting + ' > div');
    divs.each(function(i) {
        if (parseInt($('#' + multiselectName + '_ifmeeting_' + $(this).attr('data-bind')).val()) == 0) {
            if (searchKey(data, $(this).attr('data-bind'))) {
                ++imax;
                $(this).removeClass('hidden');
                $(this).focus();
            }
        }
    });

    var _init = parseInt($('#cant_' + multiselectName).val());
    if (posting == '.left')
        $('#cant_' + multiselectName).val(_init - parseInt(imax));
    if (posting == '.right')
        $('#cant_' + multiselectName).val(_init + parseInt(imax));
}

function searchKey(array, val) {
    var i = 0;
    for (i = 0; i < array.length; i++)
        if (array[i] == val)
            return true;
    return false;
}