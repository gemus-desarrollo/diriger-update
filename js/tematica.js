// JavaScript Document

var oId = 0;
var max_numero_accords = 0;

var array_asistencias = Array();

var table_matter;
var array_ids_matter = new Array();
var $table_accords;
var array_ids_accords = new Array();

var arrayIndex = new Array();
var maxIndex = -1;
var index = -1;

var title_th;

function form_matter(action, index) {
    var text = "Temáticas";
    var _text = "";
    var id;
    oId = index;

    if (action == 'add') {
        ifnew = true;
        id = 0;

        if ($('#menu').val() != "evento" && ($('#nivel').val() < 5 &&
                (parseInt($("#id_responsable").val()) != parseInt($("#id_usuario").val()) && parseInt($("#id_secretary").val()) != parseInt($("#id_usuario").val())))) {

            if (parseInt($("#ifaccords").val()) == 1)
                text = "Acuerdos";
            _text = "Usted no tiene permiso para Agregar " + text + " de la Reunión. ";
            _text += "Los nuevos " + text + " solo pueden ser adicionados por el responsable de la reunión o el secretario.";
            alert(_text);
            return false;
        }
    }

    if (action == 'edit') {
        ifnew = false;
        id = $('#id_matter_' + index).val();

        if ($('#menu').val() != "evento" && ($('#nivel') < 5 &&
                (parseInt($("#id_responsable").val()) != parseInt($("#id_usuario").val()) && parseInt($("#id_secreatry").val()) != parseInt($("#id_usuario").val())))) {

            if (parseInt($("#ifaccords").val()) == 1)
                text = "Acuerdos";
            _text = "Usted no tiene permiso para Agregar, modificar o eliminar " + text + " de la Reunión. ";
            _text += "El Plan Temático y los Acuerdos de solo pueden ser gestionados por el responsable de la reunión o el secretario.";
            alert(_text);
            return false;
        }
    }

    $('#id').val(id);
    var ifaccords = parseInt($('#ifaccords').val()) ? 1 : 0;
    var url = '../form/ajax/fmatter.ajax.php?action=' + action + '&id_evento=' + $('#id_evento').val() + '&id=' + id + '&ifaccords=' + ifaccords;
    url += '&ajax_win=1&signal=' + $('#signal').val();

    var capa = 'div-ajax-panel';
    var metodo = 'GET';
    var valores = '';
    var funct= '';
    FAjax(url, capa, valores, metodo, funct);

    var title = parseInt($("#ifaccords").val()) == 0 ? "TEMÁTICA (ORDEN DEL DÍA)" : "ACUERDO (CONTROL DE ACUERDOS)";

    displayFloatingDiv('div-ajax-panel', title, 80, 0, 5, 10);
}

function set_form_spinit() {
    $('#numero_matter').val(max_numero_accords + 1);
}

function form_accords() {
    displayFloatingDiv('div-ajax-panel-accords', "ESTADO DEL ACUERDO", 80, 0, 5, 10);
    limpiar_accords();
}

function close_matter(flag) {
    parent.app_menu_functions = false;
    try {
        CloseWindow('div-ajax-panel');
    } catch (e) {; }
    if (flag)
        self.location.reload();
}

function close_accords() {
    parent.app_menu_functions = false;
    HideContent('div-ajax-panel-accords');
}

var ifnew = true;

function add_matter() {
    if (!validar_matter())
        return;

    var action = ifnew ? 'add' : 'update';
    var ifaccords = parseInt($('#ifaccords').val()) ? 1 : 0;
    var id = $('#id').val();
    var signal = $('#signal').val();

    var url = '../php/tematica.interface.php?action=' + action + '&id_evento=' + $('#id_evento').val() + '&id=' + id + '&ifaccords=' + ifaccords;
    url += '&ajax_win=1&signal=' + signal;

    var capa = 'div-panel-body-ajax';
    var metodo = 'POST';
    var valores = signal == 'fmatter' ? $("#form-list-matter").serialize() : $("#form-matter").serialize();
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
}

function register_matter() {
    if (!validar_matter())
        return;

    var time_accords;
    var observacion_accords;
    var cumplimiento = 1;
    var id_responsable_eval;

    if (ifnew) {
        oId = parseInt($("#cant_matter").val()) + 1;
        $("#cant_matter").val(oId);
    } else {
        if (parseInt($("#ifaccords").val()) == 1) {
            time_accords = $("#time_accords_" + oId).val();
            observacion_accords = $("#observacion_accords_" + oId).val();
            cumplimiento = $("#cumplimiento_" + oId).val() > 1 ? $("#cumplimiento_" + oId).val() : 1;
            id_responsable_eval = $("#id_responsable_eval_" + oId).val();
        }
    }

    var numero = parseInt($("#numero_matter").val());

    var time = '';
    if (parseInt($("#ifaccords").val()) == 1)
        time = $("#fecha_matter").val() + ' ';
    time += $("#hora_matter").val();

    var id_asistencia = $("#asistencia_resp").val();
    var asistencia = array_asistencias[id_asistencia];
    var observacion = $("#observacion_matter").val();
    observacion = text_parse(observacion);

    var strHtml1 = numero;

    var strHtml2 = '<a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="del_matter(' + oId + ');">' +
        '<i class="fa fa-trash"></i>Eliminar' +
        '</a>' +
        '<a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit_matter(' + oId + ');">' +
        '<i class="fa fa-pencil"></i>Editar' +
        '</a>';

    if (parseInt($("#ifaccords").val()) == 1) {
        strHtml2 += '<a href="#" class="btn btn-info btn-sm" title="registrar situación o cumplimiento" onclick="edit_accords(' + oId + ');">' +
            '<i class="fa fa-check"></i>Registrar' +
            '</a>';
    }

    var strHtml3 = '';

    if (parseInt($("#ifaccords").val()) == 1) {
        strHtml3 += '<label class="alarm blank" id="cumplimiento_text_' + oId + '" onclick="edit_accords(' + oId + ');">NO INICIADO</label><br />';
    }

    strHtml3 += '<p>' + observacion + '</p>';
    strHtml3 += '<input type="hidden" id="numero_matter_' + oId + '" name="numero_matter_' + oId + '" value="' + numero + '"/>';

    strHtml3 += '<input type="hidden" id="matter_' + oId + '" name="matter_' + oId + '" value="' + observacion + '"/>';

    if (parseInt($("#ifaccords").val()) == 1) {
        strHtml3 += '<input type="hidden" id="cumplimiento_' + oId + '" name="cumplimiento_' + oId + '" value="1"/>';
        strHtml3 += '<input type="hidden" id="time_accords_' + oId + '" name="time_accords_' + oId + '" value="">';
        strHtml3 += '<input type="hidden" id="id_responsable_eval_' + oId + '" name="id_responsable_eval_' + oId + '" value=""/>';
        strHtml3 += '<input type="hidden" id="observacion_accords_' + oId + '" name="observacion_accords_' + oId + '" value=""/>';
    }

    var strHtml4 = time + '<input type="hidden" id="time_matter_' + oId + '" name="time_matter_' + oId + '" value="' + time + '"/>';
    var strHtml5 = asistencia + '<input type="hidden" id="id_asistencia_resp_' + oId + '" name="id_asistencia_resp_' + oId + '" value="' + id_asistencia + '"/>';

    if (!ifnew)
        $("#tab_matter_" + oId).val(2);

    if (!ifnew && parseInt($("#ifaccords").val()) == 1) {
        $("#time_accords_" + oId).val(time_accords);
        $("#observacion_accords_" + oId).val(observacion_accords);
        $("#cumplimiento_" + oId).val(cumplimiento);
        $("#id_responsable_eval_" + oId).val(id_responsable_eval);

        $("#cumplimiento_text_" + oId).html(eventos_cump[cumplimiento]);
        $("#cumplimiento_text_" + oId).removeClass('blank');
        $("#cumplimiento_text_" + oId).addClass('alarm');
        $("#cumplimiento_text_" + oId).addClass(eventos_cump_class[cumplimiento]);
    }

    if (ifnew) {
        array_ids_matter.push(numero);
        index = ++maxIndex;
        arrayIndex['-' + numero] = index;

        $table_matter.bootstrapTable('insertRow', {
            index: index,
            row: {
                id: strHtml1,
                icons: strHtml2,
                nombre: strHtml3,
                fecha: strHtml4,
                responsable: strHtml5
            }
        });
    }

    if (!ifnew) {
        array_ids_matter[oId] = numero;
        index = arrayIndex['-' + oId];
        $("#tab_guest_" + oId).val(2);

        $table_matter.bootstrapTable('updateRow', {
            index: index,
            row: {
                id: strHtml1,
                icons: strHtml2,
                nombre: strHtml3,
                fecha: strHtml4,
                responsable: strHtml5
            }
        });
    }

    if (ifnew)
        max_numero_accords = parseInt($("#numero_matter").val());

    ifnew = true;
    limpiar_matter();
    close_matter();
}

function reg_accords() {
    if (!validar_reg_accords())
        return;

    var time = $("#fecha_accords").val() + ' ' + $('#hora_accords').val();
    var prev = parseInt($("#prev").val()) ? "prev_" : "";

    $("#time_accords_" + prev + oId).val(time);
    $("#observacion_accords_" + prev + oId).val($("#observacion_accords").val());
    if (prev == "prev_")
        $("#observacion_accords_prev_text_" + oId).text($("#observacion_accords").val());
    $("#id_responsable_eval_" + prev + oId).val($("#id_usuario").val());

    var cumplimiento = $("#cumplimiento").val();
    $("#cumplimiento_" + prev + oId).val(cumplimiento);

    $("#cumplimiento_text_" + prev + oId).html(eventos_cump[cumplimiento]);
    $("#cumplimiento_text_" + prev + oId).removeClass();
    $("#cumplimiento_text_" + prev + oId).addClass('alarm');
    $("#cumplimiento_text_" + prev + oId).addClass(eventos_cump_class[cumplimiento]);

    $("#tab_matter_" + prev + oId).val(2);
    $("#tab_accords_" + prev + oId).val(2);

    limpiar_accords();
    close_accords();
}

function limpiar_accords() {
    ifnew = false;
    var prev = parseInt($("#prev").val()) ? "prev_" : "";

    $("#fecha_accords").val($("#time_matter_" + prev + oId).val());
    $("#observacion_accords").val($("#observacion_accords_" + prev + oId).val());
}

function del_matter(index) {
    var id = $('#id_matter_' + index).val();
    var signal = $('#signal').val();
    var text = "Temáticas";
    var _text = "";
    ifnew = true;
    oId = index;

    if ($('#menu').val() != "evento" && (parseInt($('#nivel').val()) < 5 &&
            (parseInt($("#id_responsable").val()) != parseInt($("#id_usuario").val()) && parseInt($("#id_secretary").val()) != parseInt($("#id_usuario").val())))) {

        if (parseInt($("#ifaccords").val()) == 1)
            text = "Acuerdos";
        _text = "Usted no tiene permiso para Agregar, modificar o eliminar " + text + " de la Reunión. ";
        _text += "El Plan Temático y los Acuerdos de solo pueden ser gestionados por el responsable de la reunión o el secretario.";
        alert(_text);
        return false;
    }

    function _this() {
        var ifaccords = parseInt($('#ifaccords').val()) ? 1 : 0;
        var url = '../php/tematica.interface.php?action=delete' + '&id_evento=' + $('#id_evento').val() + '&id=' + id + '&ifaccords=' + ifaccords;
        url += '&ajax_win=1';

        var capa = 'div-ajax-panel';
        var metodo = 'POST';
        var valores = signal == 'fmatter' ? $("#form-list-matter").serialize() : $("#form-matter").serialize();
        var title = parseInt($("#ifaccords").val()) == 0 ? "TEMÁTICA (ORDEN DEL DÍA)" : "ACUERDO (CONTROL DE ACUERDOS)";
        var funct= '';
        
        displayFloatingDiv('div-ajax-panel', title, 80, 0, 5, 10);
        FAjax(url, capa, valores, metodo, funct);
    }

    if (parseInt($("#ifaccords").val()) == 1) {
        confirm('Realmente desea eliminar este acuerdo?', function(ok) {
            if (!ok)
                return false;
            else
                _this();
        });
    } else {
        confirm('Realmente desea eliminar esta temática del Orden del Día?', function(ok) {
            if (!ok)
                return false;
            else
                _this();
        });
    }
}

function edit_accords(_id, prev) {
    var id = _id;
    var text = "Temáticas";
    var _text = "";

    $("#prev").val(prev);

    if ($('#menu').val() != "evento" && ($('#nivel') < 5 &&
            (parseInt($("#id_responsable").val()) != parseInt($("#id_usuario").val()) && parseInt($("#id_secreatry").val()) != parseInt($("#id_usuario").val())))) {

        if (parseInt($("#ifaccords").val()) == 1)
            text = "Acuerdos";
        _text = "Usted no tiene permiso para Agregar, modificar o eliminar " + text + " de la Reunión. ";
        _text += "El Plan Temático y los Acuerdos de solo pueden ser gestionados por el responsable de la reunión o el secretario.";
        alert(_text);
        return false;
    }

    oId = id;
    form_accords();

    ifnew = false;

    prev = parseInt($("#prev").val()) ? "prev_" : "";
    var cumplimiento = parseInt($("#cumplimiento_" + prev + oId).val());

    var time = cumplimiento > 0 ? $("#time_accords_" + prev + oId).val() : $("#time_matter_" + prev + oId).val();
    if (time.length == 0 || time == 'undefined')
        time = $("#time_matter_" + id).val();
    var _time = time.split(' ');

    $("#cumplimiento").val(cumplimiento);
    $("#fecha_accords").val(_time[0]);
    $("#hora_accords").val(_time[1] + ' ' + _time[2]);
    $("#observacion_accords").val($("#observacion_accords_" + prev + oId).val());
}

function validar_matter() {
    if (parseInt($("#ifaccords").val()) == 1 && parseInt($("#numero_matter").val()) == 0) {
        alert("Debe especificar el número del acuerdo");
        return false;
    }

    if (!parseInt($("#asistencia_resp").val())) {
        if (parseInt($("#ifaccords").val()) == 1)
            alert("No ha especificado el responsable del cumplimiento del acuerdo");
        else
            alert("No ha especificado el responsable del tema en el orden del día");
        return false;
    } else {
        if (parseInt($("#ifaccords").val()) == 1) {
            if (!parseInt($('#asistencia_usuario_' + $("#asistencia_resp").val()).val())) {
                alert("El responsable del acuerdo no puede ser un invitado");
                return false;
            }
        }
    }

    var date_actual = '01/01/2000';
    var date_inicio = '01/01/2000';

    if (parseInt($("#ifaccords").val()) == 1) {
        if (!Entrada($("#fecha_matter").val())) {
            alert("Debe de especificar la fecha de cumplimiento del acuerdo");
            return false
        }
        date_actual = $("#fecha_matter").val();
        date_inicio = $("#fecha_inicio").val();
    }

    if (!Entrada($("#hora_matter").val())) {
        alert("Debe de especificar la hora");
        return false
    }

    var actual = ampm2time(date_actual, $("#hora_matter").val(), $("#minute_matter").val(), $("#am_matter").val());
    var inicio = ampm2time(date_inicio, $("#hora_inicio").val(), $("#minute_inicio").val(), $("#am_inicio").val());
    var fin = ampm2time(date_inicio, $("#hora_fin").val(), $("#minute_fin").val(), $("#am_fin").val());

    if (DiferenciaFechas(actual, inicio, 's') < 0) {
        if (parseInt($("#ifaccords").val()) == 1)
            alert("La fecha y hora para el cumplimiento del acuerdo no puede ser anterior a la hora de inicio de la reunión o evento.");
        else
            alert("La hora de inicio de la temática no puede ser anterior a la hora de inicio de la reunión o evento.");
        return false;
    }

    if (DiferenciaFechas(fin, actual, 's') < 0 && parseInt($("#ifaccords").val()) == 0) {
        alert("La hora de inicio de la temática no puede ser posterior a la hora de finalizar la reunión o evento.");
        return false;
    }

    if (!Entrada($("#observacion_matter").val())) {
        if (parseInt($("#ifaccords").val()) == 1)
            alert("Aún no ha definido el acuerdo tomado");
        else
            alert("Aún no ha definido la temática, tema u orden del día");
        return false;
    }

    var cant_matter = Math.max(max_numero_accords, parseInt($("#cant_matter").val()));
    var found_number = false;
    var found_text = false;

    var time = '';
    if (parseInt($("#ifaccords").val()) == 1) time = $("#fecha_matter").val() + ' ';
    time += $("#hora_matter").val();

    for (var i = 1; i <= cant_matter; ++i) {
        if (parseInt(oId) == parseInt(i) && !ifnew)
            continue;
        try {
            if (parseInt($("#numero_matter_" + i).val()) == parseInt($("#numero_matter").val()))
                found_number = true;

            if ($("#matter_" + i).val() == $("#observacion_matter").val() &&
                (parseInt($("#ifaccords").val()) != 1 ||
                    (parseInt($("#ifaccords").val()) == 1 && $("#time_matter_" + i).val() == time)))
                found_text = true;

        } catch (e) {}

        if (found_number || found_text)
            break;
    }

    var texto = parseInt($("#ifaccords").val()) == 1 ? "el acuerdo" : "la temática";

    if (found_number) {
        alert("El número de " + texto + " ya esta asignado. No se puede repetir el número de la " + texto + ".");
        return false;
    }

    if (found_text) {
        alert("Ya fue definida exactamente igual. No pueden existir " + texto + " repetidas en la reunión.");
        return false;
    }

    return true;
}

function validar_reg_accords() {
    if (!parseInt($("#cumplimiento").val())) {
        alert("No ha especificado el estado o situación del acuerdo");
        return false;
    }

    var date_actual = $("#fecha_accords").val();
    var prev = parseInt($("#prev").val()) ? "prev_" : "";
    var time = $("#time_matter_" + prev + oId).val();

    var actual = ampm2time(date_actual);
    var inicio = ampm2time(time);

    if (DiferenciaFechas(actual, inicio, 's') <= 0) {
        alert("La fecha y hora para el cumplimiento del acuerdo no puede ser igual o anterior al inicio de la reunión o evento.");
        return false;
    }

    return true;
}

function add_debate(index) {
    var id_evento = $('#id_evento').val();
    var id_proceso = $('#proceso').val();
    var id_tematica = document.getElementById("id_matter_" + index).value;
    var action = document.getElementById("exect").value;

    var url = "fdebate.php?action=" + action + "&id_evento=" + id_evento + "&id_tematica=" + id_tematica + "&id_proceso=" + id_proceso;
    url += "&signal=tematica";
    self.location.href = url;
}