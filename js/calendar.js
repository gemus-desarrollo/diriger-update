// JavaScript Document
var edit_control = true;
var nselected_days = 0;


function InitCalendarEvent() {
    $('#periodicidad0').on('change', function() {
        if (!$(this).is(':checked'))
            return;
        select_freq(0, true);
    });
    $('#periodicidad1').on('change', function() {
        if (!$(this).is(':checked'))
            return;
        select_freq(1, true);
    });
    $('#periodicidad2').on('change', function() {
        if (!$(this).is(':checked'))
            return;
        select_freq(2, true);
    });
    $('#periodicidad3').on('change', function() {
        if (!$(this).is(':checked'))
            return;
        select_freq(3, true);
    });
    $('#periodicidad4').on('click', function(e) {
        select_freq(4, true);
    });

    $('#div-semanal').hide();
    $('#div-diaria').hide();
    $('#div-mensual').hide();

    if ($('#periodicidad1').is(':checked')) {
        $('#div-diaria').show();
    }
    if ($('#periodicidad2').is(':checked')) {
        $('#div-semanal').show();
    }
    if ($('#periodicidad3').is(':checked')) {
        $('#div-mensual').show();
    }
}

function validate_act() {
    if ($('#toshow2').is(':checked')) {
        $('#toshow1').prop("checked", true);
        if (!$('#user_check').is(':checked'))
            $('#toshow0').prop("checked", true);
    }

    if ($('#toshow1').is(':checked')) {
        if (!$('#user_check').is(':checked'))
            $('#toshow0').prop("checked", true);
    }

    if ((!$('#toshow0').is(':checked')) && (!$('#toshow1').checke && !$('#toshow2').is(':checked'))) {
        $('#toshow0').prop("checked", true);
        $('#user_check').prop("checked", false);
    }
}

function validar_interval(flag) {
    var text = '';
    var ifgrupo = false;

    try {
        ifgrupo = $('#ifgrupo0').is(':checked') ? true : false;
    } catch (e) {
        ifgrupo = parseInt($('#ifgrupo').val()) == 0 ? false : true;
    }

    if (!ifgrupo && $("#periodic0").is(':checked'))
        $('#_periodic').val(0);
    if (!ifgrupo && $("#periodic1").is(':checked'))
        $('#_periodic').val(1);

    if (!Entrada($('#fecha_inicio').val()) || !Entrada($('#hora_inicio').val())) {
        alert('Introduzca la fecha y hora en la que debe iniciarse ');
        $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
        return false;
    }

    var fecha_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
    var fecha_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();
    var time = null;

    if (Entrada($('#fecha_origen').val()) && (parseInt($('#id_auditoria').val()) > 0 || parseInt($('#id_tarea').val()) > 0)) {
        if (DiferenciaFechas(fecha_inicio, $('#fecha_origen').val(), 'd') < 0) {
            text = "La fecha de inicio no puede ser anterior a " + $('#fecha_origen').val();
            text += ". Sí se trata de una auditoría, control o tarea deberá modificarla desde la auditoría, control o tarea, según sea el caso.";
            alert(text);

            time = $('#fecha_origen').val().split(' ');
            $('#fecha_inicio').val(time[0]);
            $('#hora_inicio').val(time[1]);

            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            return false;
        }
    }

    if (!Entrada($('#fecha_fin').val()) || !Entrada($('#hora_fin').val())) {
        alert('Introduzca la fecha y hora en la que se debe culminar');
        $('#fecha_fin').focus(focusin($('#fecha_fin')));
        return false;
    }

    if (Entrada($('#fecha_termino').val())) {
        if (DiferenciaFechas(fecha_fin, $('#fecha_termino').val(), 'd') > 0 && (parseInt($('#id_auditoria').val()) > 0 || parseInt($('#id_tarea').val()) > 0)) {
            text = "La fecha final no puede ser superior a " + $('#fecha_termino').val();
            text += ". Si se trata de una auditoría, control o tarea deberá modificarla desde la auditoría, control o tarea, según sea el caso.";
            alert(text);

            time = $('#fecha_termino').val().split(' ');
            $('#fecha_fin').val(time[0]);
            $('#hora_fin').val(time[1]);

            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            return false;
        }
    }

    var diff_days = DiferenciaFechas(fecha_fin, fecha_inicio, 'd');

    if (ifgrupo) {
        if (diff_days < 1) {
            alert("La cantidad de días de la periodicidad no puede ser 0");
            return false;
        } else {
            return true;
        }
    }

    if ($('#periodicidad1').is(':checked') && diff_days > 0 && parseInt($('#input_carga1').val()) <= 0) {
        $('#input_carga1').focus(focusin($('#input_carga1')));
        alert("La cantidad de días de la periodicidad no puede ser 0");
        return false;
    }

    if (diff_days > 30)
        diff_days = 30;

    var cant_days = 0;

    try {
        cant_days = $('#cant_days').val();

        if ($('#periodicidad1').is(':checked') && diff_days > 30)
            diff_days = 30;
        if ($('#periodicidad2').is(':checked') && diff_days > 7)
            diff_days = 7;
        if ($('#periodicidad3').is(':checked') && diff_days > 24)
            diff_days = 24;
        if ($('#periodicidad4').is(':checked') && diff_days > 48)
            diff_days = 48;

        if (!Entrada(cant_days) || parseInt(cant_days) == 0 || cant_days == 'undefined' || parseInt(cant_days) > diff_days)
            $('#cant_days').val(diff_days ? diff_days : 1);

    } catch (e) {; }

    if (diff_days < 0) {
        alert('La fecha de finalización no puede ser menor que la de inicio.');
        $('#fecha_fin').val($('#fecha_inicio').val());
        $('#periodicidad0').prop('checked', true);
        return false;
    }

    var diff_month = DiferenciaFechas(fecha_fin, fecha_inicio, 'm');
    fecha_inicio = new Fecha(fecha_inicio);
    fecha_fin = new Fecha(fecha_fin);

    /*
        if ($('#periodicidad4').is(':checked')) {
            if (diff_month == 0 && ($('#input_carga4').val() < fecha_inicio.dia || $('#input_carga4').val() > fecha_fin.dia)) {
                alert("El intervalo de fecha escogido solo es un mes, y el día del mes que ha seleccionado no se encuentra en dicho intervalo.");
                $('#fecha_fin').focus(focusin($('#fecha_fin')));
                return false
            }
        }
     */

    if (diff_days > 0 && $('#periodicidad0').is(':checked')) {
        $('#periodicidad1').prop('checked', true);

        select_freq(1, false);
        $('#input_carga1').val(1);
        $('#input_carga1').focus(focusin($('#input_carga1')));
        return false;
    }

    if (diff_days == 0 && $("#periodic1").is(':checked')) {
        try {
            $("#periodic0").prop("checked", true);
            $('#_periodic').val(0);
            $('#cant_days').val(1);
        } catch (e) {; }
    }


    function _this() {
        $('#periodicidad0').prop('checked', true);
        select_freq(0, false);
        $('#input_carga1').val(1);
    }

    if (diff_days == 0 && !$('#periodicidad0').is(':checked')) {
        if (flag) {
            text = "Las fechas de inicio y fin son iguales, por lo que no se puede considerar la actividad como repetitiva. ";
            text += "Diriger definirá la tarea como que ocurrire en un solo día. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok) {
                    _this();
                    return false;
                } else {
                    _this();
                    return true;
                }
            });
        } else {
            alert('Las fechas de inicio y fin son iguales, por lo que no se puede considerar la actividad como repetitiva');
            _this();
            return false;
        }
    } else {
        return true;
    }
}

function select_freq(id, flag_validar) {
    if (flag_validar) {
        if (!validar_interval(false))
            return;
    }

    $('#div-diaria').css('display', 'none');
    $('#div-semanal').css('display', 'none');
    $('#div-mensual').css('display', 'none');

    if (id == 1)
        $('#div-diaria').css('display', 'block');
    if (id == 2)
        $('#div-semanal').css('display', 'block');
    if (id == 3)
        $('#div-mensual').css('display', 'block');

    if (id == 4 && edit_control)
        displayWindow();
}

function select_carga(id) {
    if (id == 1) {
        $('#sel_carga').attr('disabled', false);
        $('#dayweek0').attr('disabled', false);

    } else {
        $('#sel_carga').attr('disabled', true);
        $('#sel_carga').val(0);
        $('#dayweek0').attr('disabled', true);
        $('#dayweek0').val(0);
    }

    if (id == 0) {
        $('#fixed_day0').prop("checked", true)
        $('#input_carga4').attr('disabled', false);
    } else {
        $('#fixed_day1').prop("checked", true)
        $('#input_carga4').attr('disabled', true);
        $('#input_carga4').val(1);
    }

    var fecha_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
    var fecha_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();
    var diff_days = DiferenciaFechas(fecha_fin, fecha_inicio, 'd');
    if (diff_days > 30) diff_days = 30;

    try {
        if ($('#periodicidad1').is(':checked') && diff_days > 30)
            diff_days = 30;
        if ($('#periodicidad2').is(':checked') && diff_days > 7)
            diff_days = 7;
        if ($('#periodicidad3').is(':checked') && diff_days > 24)
            diff_days = 24;
        if ($('#periodicidad4').is(':checked') && diff_days > 48)
            diff_days = 48;

        $('#cant_days').val(diff_days ? diff_days : 1);

    } catch (e) {; }
}

function displayWindow() {
    create_chain();
    var chain = encodeURIComponent($('#_chain').val());

    displayFloatingDiv('div-panel-calendar', '', 80, 0, 5, 1);

    var table = null;
    if ($('#menu').val() == 'evento')
        table = 'teventos';
    if ($('#menu').val() == 'auditoria')
        table = 'tauditorias';
    if ($('#menu').val() == 'tarea')
        table = 'ttareas';

    var id = ($('#id').val() > 0 || $('#id').val() != 'undefined') ? $('#id').val() : 0;
    var fecha_inicio = encodeURIComponent($('#fecha_inicio').val());
    var fecha_fin = encodeURIComponent($('#fecha_fin').val());

    var periodicidad = 0;
    if ($('#periodicidad0').is(':checked'))
        periodicidad = 0;
    if ($('#periodicidad1').is(':checked'))
        periodicidad = 1;
    if ($('#periodicidad2').is(':checked'))
        periodicidad = 2;
    if ($('#periodicidad3').is(':checked'))
        periodicidad = 3;
    if ($('#periodicidad4').is(':checked'))
        periodicidad = 4;

    var url = "../form/ajax/fcalendar.ajax.php?id=" + id + "&table=" + table + "&fecha_inicio=" + fecha_inicio;
    url += '&fecha_fin=' + fecha_fin + '&periodicidad=' + periodicidad + '&chain=' + chain;


    $.ajax({
        //   data:  parametros,
        url: url,
        type: 'get',
        beforeSend: function() {
            $("#ajax-calendar").html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-calendar").html(response);

            var height = $('.app-body.container > div.panel.panel-primary').height();
            $('.calendar-year-container .panel-body').css('maxHeight', (height - 40) + 'px');
        }
    });
}

var _array_td_days = [];

function fix_td_calendar() {
    var id;

    $('td.day').each(function(i) {
        if (!$(this).hasClass('new')) {
            id = $(this).attr('id');
            $('#' + id).addClass('day ' + _array_td_days[id]);
        }
    });
}

function selectday(att, day, month, year, weekday) {
    var valid = true;
    var _date = null;
    var date = null;
    var event = null;
    var reject = null;
    var _value = 0;

    _date = year + '-' + month + '-' + day;
    date = $('#' + _date);
    if (date.length)
        _value = parseInt(date.val());

    if (_value == 0) {
        $('#y_m_d').val(_date);
        if (!test_interval()) valid = false;
        if (valid)
            if (!test_holliday(day, month))
                valid = false;
        if (valid)
            if (!test_weekend(weekday))
                valid = false;

        event = _array_td_days['td-' + _date].indexOf('event') != -1 ? " event" : "";
        reject = _array_td_days['td-' + _date].indexOf('reject') != -1 ? " reject" : "";

        if (valid) {
            $(att).addClass('active');
            _array_td_days["td-" + _date] = "day active" + event + reject;
            ++nselected_days;

            if (date.length) {
                date.val(1);
            } else {
                $('body').append('<input class="input-calendar" type="hidden" value="1" id="' + _date + '" name="' + _date + '" />');
            }

        } else {
            return;
        }
    }

    if (_value == 1) {
        date.val(0);
        $(att).removeClass('active');
        $(att).removeClass('event');

        reject = _array_td_days['td-' + _date].indexOf('reject') != -1 ? " reject" : "";

        _array_td_days["td-" + _date] = "day" + event + reject;
        --nselected_days;

        if (reject.length > 1) {
            var text = "Esta actividad ya está aprobada o registrada como cumplida en el Plan de Trabajo Individual. "
            text += "Desea realmente eliminarla y perder la información en los planes de trabajo?";

            confirm(text, function(ok) {
                if (ok) {
                    $(att).removeClass('reject');
                    $('#' + _date + '-go_delete').val(1);
                    _this_1();
                } else {
                    $(att).addClass('reject');
                    $('#' + _date + '-go_delete').val(0);
                    _this_1();
                }
            });

        } else {
            _this_1();
        }

    } else {
        _this_1();
    }

    function _this_1() {
        if ($('#changed_chain').val() == 0)
            $('#changed_chain').val(parseInt(date.val()) != _value ? 1 : 0);
    }
}

function unmark_all_days() {
    var id;
    var question = false;
    var _reject = true;
    var reject;
    var text;

    $('table.table-calendar td').each(function(i) {
        $(this).removeClass('active');
        $(this).removeClass('event');

        id = $(this).attr('id');

        if (id != undefined) {
            reject = _array_td_days[id].indexOf('reject') != -1 ? " reject" : "";

            if (reject.length > 1) {
                if (!question) {
                    text = "Hay actividades que ya están aprobadas o registradas como cumplidas en el los Planes Individuales. "
                    text += "Al cambiar las fechas y reprogramar se perdera la información asociada. Desea realmente eliminarlas y perder esta información?";

                    confirm(text, function(ok) {
                        if (ok) {
                            _reject = true;

                            --nselected_days;
                            $(this).removeClass('reject');
                            $('#' + id + '-go_delete').val(1);
                            _array_td_days[id] = 'day';
                        } else {
                            _reject = false;
                            $(this).addClass('reject');
                            _array_td_days[id] = 'day' + reject;
                        }
                    });
                } else {
                    if (!_reject) {
                        _array_td_days[id] = 'day' + (_reject ? reject : '');

                    } else {
                        --nselected_days;
                        $(this).removeClass('reject');
                        $('#' + id + '-go_delete').val(1);
                        _array_td_days[id] = 'day';
                    }
                }

                question = true;

            } else {
                --nselected_days;
                _array_td_days[id] = 'day';
            }
        }
    });

    $('input.input-calendar').val(0);
    $('#changed_chain').val(1);
}

function test_interval() {
    var fecha_inicio = $('#fecha_inicio').val() + ' 00:00';
    var fecha_fin = $('#fecha_fin').val() + ' 00:00';
    var y_m_d = $('#y_m_d');

    if (DiferenciaFechas(fecha_fin, y_m_d.val(), 'd') < 0 || DiferenciaFechas(y_m_d.val(), fecha_inicio, 'd') < 0) {
        alert('La fecha selecionada está fuera del intervalo especificado para la tarea o actividad.');
        return false;
    }

    $('#y_m_d').val(null);
    return true;
}

var day_feriados = new Array(10);
for (var i = 0; i < day_feriados.length; ++i)
    day_feriados[i] = null;

function test_holliday(day, month) {
    var freeday = false;

    for (var i = 0; i < day_feriados.length; ++i) {
        if (!IsAlphaNumeric(day_feriados[i]))
            continue;
        mday = day_feriados[i].split('/');
        if (parseInt(day) == parseInt(mday[0]) && parseInt(month) == parseInt(mday[1])) {
            freeday = true;
            break;
        }
    }

    if (freeday && !$('#freeday').is(':checked')) {
        alert("No puede programar tarea para los dias feriados. Primero deberá especificar que para esta tarea trabajará los feriados.");
        return false;
    }

    return true;
}

function test_weekend(d) {
    var saturday = false;
    var sunday = false;
    var periodicidad = 0;
    if ($('#periodicidad1').is(':checked'))
        periodicidad = 1;
    if ($('#periodicidad2').is(':checked'))
        periodicidad = 2;
    if ($('#periodicidad3').is(':checked'))
        periodicidad = 3;
    if ($('#periodicidad4').is(':checked'))
        periodicidad = 4;

    if (d == 6 && !$('#saturday').is(':checked')) {
        saturday = true;
        if (periodicidad == 2)
            $('#dayweek6').prop('checked', false);
        if (periodicidad == 3)
            $('#dayweek0').val(0);
    }

    if (d == 7 && !$('#sunday').is(':checked')) {
        sunday = true;
        if (periodicidad == 2)
            $('#dayweek7').prop("checked", false);
        if (periodicidad == 3)
            $('#dayweek0').val(0);
    }

    if (saturday) {
        alert("No puede programar tarea para el sábado. Primero deberá especificar que para esta tarea trabajará el sábado.");
        return false;
    }
    if (sunday) {
        alert("No puede programar tarea para el domingo. Primero deberá especificar que para esta tarea trabajará el domingo.");
        return false;
    }

    return true;
}

function create_chain() {
    $('#_chain').val(null);
    var day;
    var ndays_selected = 0;

    var k = 0;
    var i = 0;
    var j = 0;
    var y = 0;

    var _year = parseInt(year) + 1;
    var lastday = 0;

    for (y = $('#init_year').val(); y <= $('#end_year').val(); ++y) {
        for (i = 1; i < 13; ++i) {
            lastday = longmonth(i, y);
            for (j = 1; j <= lastday; ++j) {
                day = y + '-' + i + '-' + j;
                if ($('#' + day).val() == 1) {
                    ++k;
                    if (k > 1) $('#_chain').val($('#_chain').val() + ',');
                    $('#_chain').val($('#_chain').val() + day);
                    ++ndays_selected;
                }
            }
        }
    }

    return ndays_selected;
}
