//---------------------------------------------------------------------------------------------------------
// Funciones generales
//---------------------------------------------------------------------------------------------------------
function ajaxPanelScrollY(panel, posy, minH) {
    // var body= $('#'+panel+' .card-body');
    var body= $('#'+panel).find('.card-body.info-panel');
    // console.log(body);
    var delta= $(window).height() - ($('#'+panel).position().top + body.position().top + body.height() + minH + posy); 
    /*
    console.log('window='+$(window).height());
    console.log('pos1='+$('#'+panel).position().top);
    console.log('pos2='+body.position().top);
    console.log('heigth='+body.height());
    console.log('delta='+delta);
    */
    if (delta < 0) {
        body.css('height',  (body.height() + minH + delta) + 'px');
        // console.log("height="+body.height());
    }
}


function _show(_e, _break) {
    if (_break.length > 0) {
        _break = _break.length > 0 ? '-' + _break : '';
    } else {
        _e.removeClass('d-none');
    }

    _e.addClass('d' + _break + '-inline-block');
}

function _hide(_e, _break) {
    _break = _break.length > 0 ? '-' + _break : '';
    _e.removeClass('d' + _break + '-inline-block');
    _e.addClass('d-none');
}


function ShowContent(i, d, ipersp) {
    if (d.length < 1)
        return;

    $('#id_indicador').val(i);
    $('#id_persp').val(ipersp);

    $('#win-title').html($('#nombre_' + i).val());

    $('#observacion_real').html($('#observacion_real_' + i).val());
    $('#observacion_plan').html($('#observacion_plan_' + i).val());

    $('#registro_real').html($('#registro_real_' + i).val());
    $('#registro_plan').html($('#registro_plan_' + i).val());

    $('#proyecto').html($('#proyecto_' + i).val());

    $('#valor_real').html($('#valor_real_' + i).val());
    $('#valor_plan').html($('#valor_plan_' + i).val());

    $('#responsable_real').html($('#responsable_real_' + i).val());
    $('#responsable_plan').html($('#responsable_plan_' + i).val());

    $('#id_user_real').val($('#id_user_real_' + i).val());
    $('#id_user_plan').val($('#id_user_plan_' + i).val());

    $('#trend').val($('#trend_' + i).val());
    $('#cumulative').val($('#cumulative_' + i).val());
    $('#formulated').val($('#formulated_' + i).val());

    AssignPosition(d);
    $('#' + d).css('display', "block");

    $('#if_entity').val($('#if_entity_' + i).val());

    _show($('#img_edit'), 'md');
    _show($('#img_delete'), 'md');
    _show($('#img_planning'), 'md');
    _show($('#img_register'), 'md');

    if (parseInt($('#if_entity').val()) == 0) {
        _hide($('#img_edit'), 'md');
        _hide($('#img_delete'), 'md');
        _hide($('#img_planning'), 'md');
        _hide($('#img_register'), 'md');
    }
    /*
    var formulated= parseInt($('#formulated').val());
    document.getElementById("icon_plan").css('display',formulated ? "none" : "table-row";
    document.getElementById("icon_real").css('display',formulated ? "none" : "table-row";
    */
}

function ReverseContentDisplay(d) {
    if (d.length < 1) {
        return;
    }

    AssignPosition(dd);
    var dd = $('#' + d);

    if (dd.css('display') == "none") {
        dd.css('display', "block");
    } else {
        dd.css('display', "none");
    }
}
//-->

//---------------------------------------------------------------------------------------------------------
// Funciones para el efecto de los tableros con indicadores
//---------------------------------------------------------------------------------------------------------
function delete_indicador(signal) {
    var id = $("#id_indicador").val();
    enviar_indicador(id, 'delete', signal);
}

function edit_indicador(signal, action) {
    var id = $("#id_indicador").val();
    enviar_indicador(id, action, signal);
}

function grafico_indicador() {
    var id = $("#id_indicador").val();
    var year = $('#year').val();
    var month = $('#month').val();

    var radio_cumulative;
    var radio_formulated;

    try {
        radio_cumulative = $('#_radio_cumulative').val();
    } catch (e) {
        radio_cumulative = 1;
    }
    try {
        radio_formulated = $('#_radio_formulated').val();
    } catch (e) {
        radio_formulated = 1;
    }

    var url = '../form/ajax/fgraph_select.ajax.php?id_indicador=' + id + '&year=' + year + '&month=' + month;
    url += '&radio_cumulative=' + radio_cumulative + '&radio_formulated=' + radio_formulated;

    var capa = 'div-ajax-graph-select';
    var metodo = 'GET';
    var valores = '';
    var funct= '';
    
    FAjax(url, capa, valores, metodo, funct);

    var title = "SELECCIONAR TIPO DE GRAFICO A CONSTRUIR";
    var w = 50;
    displayFloatingDiv('div-ajax-graph-select-panel', title, w, 30, 10, 20);
}

function _grafico_indicador() {
    var id = $("#id_indicador").val();
    var id_persp = $('#id_persp').val();
    var trend = $('#trend').val();

    if (trend == 0) {
        text = "No ha sido especificada la escala de colores de este indicador en el año en curso. ";
        text += "Deberá editar el indicador y especificar la escala de colores.";
        alert(text);
        return;
    }

    graficar('indicador', id);
}

function plan_indicador() {
    var text;
    var id = $("#id_indicador").val();
    var id_tablero = $('#tablero').val();
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();
    var signal = $('#signal').val();

    function _this_1() {
        if (($("#id_user_plan").val() != $("#id_usuario").val()) && $("#nivel").val() < 4) {
            alert('Usted no esta autorizado a Planificar o definir criterios de éxito o medida para este indicador.');
            return false;
        }

        if (year < $('#actual_year').val() && month < 12) {
            confirm("Esta intentando modificar el plan en un año que ya ha transcurrido. Está seguro de querrer continuar?", function(ok) {
                if (!ok)
                    return false;
                else
                    _this_2();
            });
        } else {
            _this_2();
        }
    }

    function _this_2() {
        var url = '&id_indicador=' + id + '&id_tablero=' + id_tablero + '&id_proceso=' + id_proceso;
        url += '&year=' + year + '&month=' + month + '&day=' + day;;

        url = '../form/plan.php?action=update&signal=' + signal + url;
        self.location.href = url;
    }

    if (parseInt($('#formulated').val())) {
        text = "Este es un indicador calculado por el Sistema a partir de otros indicadores. Sus valores de plan y ";
        text += "real no deberían ser asignados por un usuario. Desea continuar?";
        confirm(text, function(ok) {
            if (!ok)
                return false;
            else
            if (!_this_1())
                return false;
        });
    } else
    if (!_this_1())
        return false;
}

function real_indicador() {
    var text;

    var id = $("#id_indicador").val();
    var id_tablero = $('#tablero').val();
    var id_proceso = $('#proceso').val();
    var id_persp = $('#id_persp').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();
    var signal = $('#signal').val();

    function _this() {
        if (($("#id_user_real").val() != $("#id_usuario").val() && $("#id_user_plan").val() != $("#id_usuario").val()) &&
            $("#nivel").val() < 4) {
            alert('Usted no está autorizado a actualizar este indicador.');
            return false;
        }

        var url = '&id_indicador=' + id + '&id_tablero=' + id_tablero + '&id_proceso=' + id_proceso;
        url += '&year=' + year + '&month=' + month + '&day=' + day + '&id_perspectiva=' + id_persp;

        url = '../form/real.php?action=update&signal=' + signal + url;
        self.location.href = url;
    }

    if (parseInt($('#formulated').val())) {
        text = "Este es un indicador calculado por el Sistema a partir de otros indicadores. Sus valores de plan y ";
        text += "real no pueden ser asignados por un usuario. Desea continuar?";
        confirm(text, function(ok) {
            if (!ok)
                return;
            else
            if (!_this())
                return false;
        });
    } else
    if (!_this())
        return false;
}

//---------------------------------------------------------------------------------------------------------
// Funciones para la planificacion de act
//---------------------------------------------------------------------------------------------------------
function ShowContentTask(i) {
    $('#id_tarea').val($('#id_tarea_' + i).val());
    $('#win-ptitle').html($('#evento_' + i).val());
    $('#p_lugar').html($('#lugar_' + i).val());
    $('#p_descripcion').html($('#descripcion_' + i).val());
    $('#p_responsable').html($('#responsable_' + i).val());
    $('#p_asignado').html($('#usuario_' + i).val());
    $('#id_asignado').val($('#id_usuario_' + i).val());

    var _class = '<div class="alarm ' + SetClassAlarm($('#cumplimiento_' + i).val()) + '">' + $('#status_' + i).val() + '</div>';
    $('#p_status').empty();
    $('#p_status').append(_class);

    $('#p_proyecto').html($('#proyecto_' + i).val());
}

function SetClassAlarm(_status) {
    _status = parseInt(_status);
    var _class;

    switch (_status) {
        case 1:
            _class = 'blank';
            break;
        case 2:
            _class = 'yellow';
            break;
        case 3:
            _class = 'green';
            break;
        case 4:
            _class = 'orange';
            break;
        case 5:
            _class = 'orange';
            break;
        case 6:
            _class = 'dark';
            break;
        case 7:
            _class = 'dark';
            break;
        case 8:
            _class = 'red';
            break;
        case 9:
            _class = 'gray';
            break;
        default:
            _class = 'blank';
    }

    return _class;
}

var rightside = true;

function rightsideClicked() {
    rightside = true;
}

function leftsideClicked() {
    rightside = false;
}

function _ShowContentEvent(i, div, day, id_proceso, block_reg) {
    CloseWindow('win-board-signal');
    CloseWindow('win-board-signal-project');

    if (div.length < 1)
        return;

    AssignPosition(div);

    $('#id').val(i);
    $('#day').val(day);
    $('#id_responsable').val($('#id_responsable_' + i).val());
    $('#_id_proceso').val(id_proceso);

    $('#signal-title').html($('#evento_' + i).val());

    $('#_lugar').html($('#lugar_' + i).val());
    $('#_descripcion').html($('#descripcion_' + i).val());
    $('#_responsable').html($('#responsable_' + i).val());

    $('#_asignado').html($('#usuario_' + i).val());
    $('#id_asignado').val($('#id_usuario_' + i).val());

    $('#if_synchronize').val($('#if_synchronize_' + i).val());
    $('#if_entity').val($('#if_entity_' + i).val());
    $('#entity_tipo_user').val($('#entity_tipo_user_' + i).val());

    $('#_fecha').html($('#fecha_' + i).val());

    $('#cumplimiento').val($('#cumplimiento_' + i).val());

    $('#_status').empty();
    var _class = '<div class="alarm ' + SetClassAlarm($('#cumplimiento_' + i).val()) + '">' + $('#status_' + i).val() + '</div>';
    $('#_status').append(_class);

    $('#id_evento').val($('#id_evento_' + i).val());
    $('#id_tarea').val($('#id_tarea_' + i).val());
    $('#id_auditoria').val($('#id_auditoria_' + i).val());
    $('#id_tematica').val($('#id_tematica_' + i).val());
    $('#id_archivo').val($('#id_archivo_' + i).val());

    $('#id_nota').val($('#id_nota_' + i).val());
    $('#id_riesgo').val($('#id_riesgo_' + i).val());
    $('#id_politica').val($('#id_politica_' + i).val());

    $('#toshow').val($('#toshow_' + i).val());

    $('#ifmeeting').val($('#ifmeeting_' + i).val());
    $('#id_secretary').val($('#id_secretary_' + i).val());
    $('#secretary').val($('#secretary_' + i).val());
    $('#if_participant').val($('#if_participant_' + i).val());

    $('#acc_planwork_user').val($('#acc_planwork_user_' + i).val());
    $('#acc_planaudit_user').val($('#acc_planaudit_user_' + i).val());
    $('#id_proceso_user').val($('#id_proceso_user_' + i).val());
    $('#tipo_proceso_user').val($('#tipo_proceso_user_' + i).val());

    $('#id_proyecto').val($('#id_proyecto_' + i).val());
    $('#id_responsable_proyecto').val($('#id_responsable_proyecto_' + i).val());
    $('#proyecto').val($('#proyecto_' + i).val());

    if (parseInt($('#id_secretary').val())) {
        $('#div_secretary').show();
        $('#_secretary').html($('#secretary').val());
    } else {
        $('#div_secretary').hide();
    }

    if (parseInt($('#id_proyecto_' + i).val()) > 0)
        ShowContentTask(i);

    try {
        parseInt(block_reg) ? _hide($('#img_register'), '') : _show($('#img_register'), '');
        parseInt(block_reg) ? _hide($('#img_repro'), '') : _show($('#img_repro'), '');
    } catch (e) {; }
}

function _tipo_plan(div) {
    var tipo_plan = parseInt($("#tipo_plan").val());
    var id_usuario = parseInt($('#id_usuario').val());
    var id_secretary = parseInt($('#id_secretary').val());

    if (tipo_plan != _PLAN_TIPO_MEETING) {
        $('#' + div + ' > div.btn-toolbar').css('display', 'none');
    }
    if (tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL) {
        _hide($('#img_repro'), '');
    }
    if (($("#permit_change").val() && tipo_plan != _PLAN_TIPO_MEETING) ||
        ((tipo_plan == _PLAN_TIPO_MEETING || ($("#ifmeeting").val()) && id_secretary == id_usuario))) {
        show = true;
        $('#' + div + ' > div.btn-toolbar').css('display', 'block');
    }
    return show;
}

function _permite_change(acc_permit) {
    var tipo_plan = parseInt($("#tipo_plan").val());
    var ifmeeting = parseInt($('#ifmeeting').val());
    var id_secretary = parseInt($('#id_secretary').val());
    var tipo_plan = parseInt($("#tipo_plan").val());
    var id_usuario = parseInt($('#id_usuario').val());
    var id_responsable = parseInt($('#id_responsable').val());
    var if_entity = parseInt($('#if_entity').val());
    var ifmeeting = parseInt($('#ifmeeting').val());

    try {
        _hide($('#img_repro'), '');
    } catch (e) {}

    if (id_usuario == id_responsable || acc_permit) {
        _show($('#img_delegate'), '');
    } else {
        _hide($('#img_delegate'), '');
    }

    try {
        if ((!ifmeeting && id_usuario != id_responsable) || (ifmeeting && id_usuario != id_secretary))
            _show($('#img_reject'), '');
        else
            _hide($('#img_reject'), '');
    } catch (e) {}

    try {
        if ((tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL && tipo_plan != _PLAN_TIPO_MEETING && if_entity))
            _show($('#img_repro'), '');
        if (ifmeeting && id_usuario != id_responsable && id_usuario != id_secretary)
            _hide($('#img_repro'), '');
    } catch (e) {}
    try {
        if (parseInt($('#if_synchronize').val()) || !if_entity || (if_entity && !acc_permit))
            _hide($('#img_copy'), 'md');
    } catch (e) {}

    if ((tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL || tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) ||
        !if_entity || (if_entity && !acc_permit)) {
        try {
            _hide($('#img_copy'), 'md');
        } catch (e) {}
    }
    if ((tipo_plan == _PLAN_TIPO_MEETING || tipo_plan == _PLAN_TIPO_AUDITORIA || tipo_plan == _PLAN_TIPO_SUPERVICION) ||
        !if_entity || (if_entity && !acc_permit)) {
        if (rightside) {
            try {
                _hide($('#img_copy'), 'md');
            } catch (e) {}
        } else
            try {
                _hide($('#img_repro'), 'md');
            } catch (e) {}
    }

    if (if_entity) {
        if (id_usuario == id_responsable || acc_permit) {
            _show($('#img_delete'), '');
            _show($('#img_edit'), 'md');
        } else {
            _hide($('#img_delete'), '');
            _hide($('#img_edit'), 'md');
        }
    } else {
        _hide($('#img_edit'), 'md');
        _hide($('#img_delete'), '');
    }
}

function _no_permite_change(acc_permit) {
    var tipo_plan = parseInt($("#tipo_plan").val());
    var ifmeeting = parseInt($('#ifmeeting').val());
    var id_secretary = parseInt($('#id_secretary').val());
    var id_usuario = parseInt($('#id_usuario').val());
    var id_responsable = parseInt($('#id_responsable').val());
    var if_entity = parseInt($('#if_entity').val());

    try {
        _hide($('#img_repro'), '');
    } catch (e) {}
    try {
        _hide($('#img_copy'), 'md');
    } catch (e) {}

    if (id_usuario == id_responsable || acc_permit) {
        _show($('#img_delegate'), '');
    } else {
        _hide($('#img_delegate'), '');
    }

    try {
        if (id_usuario == id_responsable || (ifmeeting && id_usuario == id_secretary)) {
            _hide($('#img_reject'), '');
            if (tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL && if_entity)
                _show($('#img_repro'), '');
        } else
            _show($('#img_reject'), '');
    } catch (e) {}

    if (if_entity) {
        if (acc_permit ||
            (tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && (id_usuario == id_responsable || id_usuario == id_secretary)))
            _show($('#img_delete'), '');
        else
            _hide($('#img_delete'), '');

        if (id_usuario == id_responsable || acc_permit)
            _show($('#img_edit'), 'md');
        else
            _hide($('#img_edit'), 'md');
    } else {
        _hide($('#img_edit'), 'md');
        _hide($('#img_delete'), '');
    }
}

function ShowContentEvent(i, div, day, id_proceso, block_reg) {
    _ShowContentEvent(i, div, day, id_proceso, block_reg);

    var ifmeeting = parseInt($('#ifmeeting').val());
    var ifJefe = false;
    var id_usuario = parseInt($('#id_usuario').val());
    var id_secretary = parseInt($('#id_secretary').val());
    var id_responsable = parseInt($('#id_responsable').val());
    var if_entity = parseInt($('#if_entity').val());
    var if_participant = parseInt($('#if_participant').val());

    var acc_permit = false;
    if ($('#nivel').val() >= 5)
        acc_permit = true;

    if (if_entity && $('#acc_planwork').val() > 0) {
        if ($('#acc_planwork').val() >= $('#acc_planwork_user').val())
            acc_permit = true;
        else {
            if ($('#tipo_proceso_user').val() > $('#tipo_proceso').val() &&
                $('#tipo_proceso_user').val() > $('#usuario_proceso_tipo').val())
                acc_permit = true;
        }
    }

    try {
        if (!parseInt($('#if_synchronize').val()) && !if_entity)
            _show($('#img_copy'), 'md');
    } catch (e) {}

    _tipo_plan(div);

    if (ifmeeting && (id_usuario == id_secretary || id_usuario == id_responsable || $('#nivel').val() >= 5)) {
        ifJefe = true;
        _show($('#img_assist'), 'md');
    } else {
        _hide($('#img_assist'), 'md');
    }

    if (parseInt($("#permit_change").val())) {
        _permite_change(acc_permit);
    }

    if (!parseInt($("#permit_change").val())) {
        _no_permite_change(acc_permit);
    }

    if (!if_entity && $('#entity_tipo_user').val() > $('#entity_tipo').val()) {
        _hide($('#img_delegate'), '');
    }

    if (ifmeeting && !if_participant && !ifJefe) {
        _hide($('#img_dox'), 'md');
        _hide($('#img_register'), '');
    } else {
        _show($('#img_dox'), 'md');
        _show($('#img_register'), '');
    }


    set_ifmeeting(ifmeeting, ifJefe);
}

function set_ifmeeting(ifmeeting, ifJefe) {
    var if_entity = parseInt($('#if_entity').val());
    var if_participant = parseInt($('#if_participant').val());

    /*
    ifmeeting=3  Es la reunion Planificada Para todo el ano. Programa de Reuniones
    ifmeeting=2  Es la reunion Planificada Para todo el ano. Plan anual
    ifmeeting=1  Es la reunion de un dia en especifico en el Programa de Reuniones
    ifmeeting=0  No es una reunion
    */

    _hide($('#img_tematica'), 'md');
    _hide($('#img_acuerdo'), 'md');
    _hide($('#img_debate'), 'md');
    _hide($('#img_assist'), 'md');
    _hide($('#img_print_tematica'), 'lg');
    _hide($('#img_print_acuerdo'), 'lg');
    _hide($('#img_print_debate'), 'lg');

    if (ifmeeting == 1) {
        if (ifJefe && if_entity) {
            _show($('#img_assist'), 'md');
            _show($('#img_tematica'), 'md');
            _show($('#img_acuerdo'), 'md');
            _show($('#img_debate'), 'md');
            _show($('#img_repro'), '');

            _show($('#img_print_tematica'), 'lg');
            _show($('#img_print_acuerdo'), 'lg');
            _show($('#img_print_debate'), 'lg');
        }
        if ((!ifJefe || !if_entity) && if_participant) {
            _show($('#img_print_tematica'), 'lg');
            _show($('#img_print_acuerdo'), 'lg');
            _show($('#img_print_debate'), 'lg');
        }
    } else if (ifmeeting == 2) {
        _hide($('#img_dox'), 'md');

        if (ifJefe || if_participant) {
            _show($('#img_print_tematica'), 'lg');
            _show($('#img_print_acuerdo'), 'lg');
            _show($('#img_print_debate'), 'lg');
        } else {
            _hide($('#img_print_tematica'), 'lg');
            _hide($('#img_print_acuerdo'), 'lg');
            _hide($('#img_print_debate'), 'lg');
        }
    } else if (ifmeeting == 3) {
        _hide($('#img_register'), '');
        _hide($('#img_dox'), 'md');

        if (ifJefe && if_entity) {
            _show($('#img_tematica'), 'md');
            _show($('#img_copy'), 'md');
        }
        if (ifJefe || if_participant) {
            _show($('#img_print_tematica'), 'lg');
            _show($('#img_print_acuerdo'), 'lg');
        }
    }
}

function test_if_chief(id) {
    for (i = 0; i < array_chief_id.length; ++i)
        if (array_chief_id[i] == id)
            return (array_chief_nombre[i] + ' (' + array_chief_cargo[i] + ')');

    return null;
}

function show2mostrar(_panel) {
    var panel = _panel;
    var msg_tmp;

    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return;
    }

    var i = $('#id').val();
    var id_usuario = $('#id_usuario').val();
    var eject = true;

    if (panel == 'register')
        eject = true;

    var if_jefe = false;
    var jefe_asign = false;
    var jefe_resp = false;
    var _jefe = false;

    if (parseInt($('#id_auditoria_' + i).val()) > 0 && (panel == 'delete' || panel == 'edit' || panel == 'reject') &&
        parseInt($('#toshow').val()) != 0) {
        msg_tmp = "Esta actividad es una acción de control o auditoría, para editar, modificar o eliminar la actividad deberá ir ";
        msg_tmp += "al Plan Anual de Acciones de Control o al Plan Anual de Auditorías, según corresponda";
        alert(msg_tmp);
        eject = false;
        return;
    }
    if (parseInt($('#id_archivo_' + i).val()) > 0 && (panel == 'delete' || panel == 'edit' || panel == 'reject' || panel == 'delegate')) {
        msg_tmp = "Esta es una actividad generada a partir de una Indicación emitida desde la Oficina de Archivo o Despacho. ";
        msg_tmp += "Para ser rechazada, modificada o eliminada debe hacerlo desde la Gestión de Archivos.";
        alert(msg_tmp);
        eject = false;
        return;
    }
    if (parseInt($('#id_auditoria_' + i).val()) > 0 && panel == 'repro' && parseInt($('#toshow').val()) != 0) {
        msg_tmp = "Una auditoria, supervisión o cualquier acción de control no puede ser reprogramada desde los planes de trabajo. ";
        msg_tmp += "Debe acceder al correspondiente \"Programa de Auditorías o de Acciones de Control\".";
        alert(msg_tmp);
        eject = false;
        return;
    }
    /*
    if ($('#id_tarea_'+i).val() > 0 && panel == 'repro') {
        msg_tmp= "Esta actividad no puede ser copiada para el próximo año. La actividad es resultado de la planificación de una tarea de un ";
        msg_tmp+= "Proyecto o una medida asociada a la Gestión de Riesgos o Cierre de Notas de Hallazgos (No-conformidad, Observación o Nota de Mejora).";
        alert(msg_tmp);
        eject= false;
        return;
    }
    */
    if ($('#cumplimiento_' + i).val() == 3 && (panel == 'repro' || panel == 'delete' || panel == 'reject')) {
        msg_tmp = "Usted no puede reprogramar, eliminar o rechazar una actividad o tarea registrada como cumplida. ";
        msg_tmp += "Primero deberá registrarla como incumplida o suspendida.";
        alert(msg_tmp);
        eject = false;
    }

    if (parseInt($('#ifmeeting').val())) {
        if (parseInt($('#fixed_' + i).val()) && (panel == 'repro' || panel == 'delete' || panel == 'reject' || panel == 'delegate')) {
            msg_tmp = "Usted no puede reprogramar, eliminar o rechazar una reunión que parece que ya se realizó. ";
            msg_tmp += "Deberá eliminarles los acuerdos, participantes, debates, etc.";
            alert(msg_tmp);
            eject = false;
            return;
        } else
            _this_6();
    } else {
        _this_6();
    }

    function _this_6() {
        if (id_usuario == $('#id_responsable_' + i).val() && panel == 'reject') {
            msg_tmp = "Usted es responsable de esta tarea o actividad. Debería editarla y delegarla antes de continuar. ";
            msg_tmp += "Desea continuar?";
            confirm(msg_tmp, function(ok) {
                if (ok) {
                    eject = true;
                    _this_7();
                } else {
                    return;
                }
            });
        } else {
            _this_7();
        }
    }

    function _this_7() {
        _jefe = test_if_chief($('#id_usuario').val());
        jefe_resp = test_if_chief($('#id_responsable_' + i).val());
        jefe_asign = test_if_chief($('#id_usuario_' + i).val());

        if (id_usuario == $('#id_responsable_' + i).val() || $('#nivel').val() >= 4)
            if_jefe = true;
        if (parseInt($('#acc_planwork').val()) > 0)
            if_jefe = true;

        if ($('#toshow_' + i).val() > 0 && (panel == 'edit' || panel == 'reject')) {
            if (!if_jefe) {
                msg_tmp = "Usted no puede rechazar o modificar una tarea o actividad registrada en los " + $('#msg_prs_' + i).val();
                msg_tmp += ". Esta tarea o actividad solo se puede rechazar o modificar desde " + $('#msg_prs_' + i).val() + " .";

                alert(msg_tmp);
                return false;

            } else {
                msg_tmp = "Tenga cuidado cuando intenta rechazar o modificar una tarea o actividad registrada en el " + $('#msg_prs_' + i).val();
                msg_tmp += ". Esta tarea o actividad es conveniente rechazar o modificar desde el " + $('#msg_prs_' + i).val() + ".";
                alert(msg_tmp, function() {
                    eject = true;
                    _this_8();
                });
            }
        } else {
            _this_8();
        }
    }

    function _this_8() {
        if (panel != 'delete') {
            _this_9();
            return;
        }
        if ($('#toshow_' + i).val() == 0) {
            _this_9();
            return;
        }

        if ($('#nivel').val() < 4 && parseInt($('#id_responsable_' + i).val()) != id_usuario) {
            var msg_tmp = "Usted no puede eliminar una tarea o actividad registrada en los " + $('#msg_prs_' + i).val();
            msg_tmp += ". Esta tarea o actividad, o su participación en ella, solo se puede eliminar desde " + $('#msg_prs_' + i).val() + " .";

            alert(msg_tmp);
            return false;
        } else {
            if (parseInt($('#id_responsable_' + i).val()) != parseInt($('#id_calendar').val())) {
                msg_tmp = "Usted está tratando de eliminar una actividad o tarea proveniente del " + $('#msg_prs_' + i).val() + ". ";
                msg_tmp += "En este caso es recomendable usar la opción de reprogramar la actividad. Desea continua?";
                confirm(msg_tmp, function(ok) {
                    if (ok) {
                        eject = true;
                        _this_9();
                    } else {
                        return false;
                    }
                });
            } else {
                _this_9();
            }
        }
    }

    function _this_9() {
        if (panel != 'delete') {
            _this_10();
            return;
        }

        if (parseInt($('#id_responsable_' + i).val()) == parseInt($('#id_calendar').val())) {
            msg_tmp = "Usted es responsable de esta tarea o actividad. Debería editarla y delegarla antes de continuar. ";
            msg_tmp += "Desea continuar?";
            confirm(msg_tmp, function(ok) {
                if (ok) {
                    eject = true;
                    _this_10();
                } else {
                    return;
                }
            });
        } else {
            _this_10();
        }
    }

    function _this_10() {
        if (panel == 'delete') {
            if (Entrada($('#aprobado_' + i).val())) {
                msg_tmp = "Usted no puede eliminar una tarea o actividad aprobada. ";
                msg_tmp = "Si no elije la opción de borrado físico esta se mostrará como suspendida";
                alert(msg_tmp);
            }
            if (jefe_asign && !if_jefe) {
                alert("Usted no puede eliminar o rechazar una tarea o actividad que le fue asignada por su jefe " + jefe_asign);
                eject = false;
            }
            if (jefe_resp && !if_jefe) {
                alert("Usted no puede eliminar o rechazar una tarea o actividad de la que es responsable su jefe " + jefe_resp);
                eject = false;
            }
        }

        if (!if_jefe && (panel != 'register' && panel != 'repro') && eject) {
            if (Entrada($('#aprobado_' + i).val()) && _jefe == null) {
                msg_tmp = "Usted no puede eliminar, rechazar o modificar un evento que ya fue aprobado: " + $('#aprobado_' + i).val();

                if (panel == 'delete')
                    alert(msg_tmp + ". Pruebe a cancelarla o posponerla, o pídale a su jefe inmediato o que se la elimine del plan.");
                if (panel == 'reject')
                    alert(msg_tmp + ". Pruebe a cancelarla o posponerla.");
                if (panel == 'edit')
                    alert(msg_tmp + ". Pruebe a cancelarla o posponerla, o pídale al responsable del evento o tarea que la modifique.");

                eject = false;
            }
        }

        if (panel == 'matter' || panel == 'accords' || panel == 'docs' || panel == 'assist' || panel == 'debate') {
            action = eject ? 'add' : 'list';
            showOpenWindow(panel, action);
            return false;
        }

        if (panel != 'edit' && eject) {
            _mostrar(panel, i);
            return;
        }
        if (panel == 'edit' && eject) {
            edit(i);
            return;
        }

        return true;
    }
}

function p_show2mostrar(panel) {
    var text;
    var msg_tmp;
    var action;

    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return;
    }

    var i = $('#id').val();
    var id_usuario = $('#id_usuario').val();
    var eject = true;

    if (panel == 'register')
        eject = true;

    var if_jefe = false;
    var jefe_asign = false;
    var jefe_resp = false;
    var _jefe = false;

    _jefe = test_if_chief($('#id_usuario').val());
    jefe_resp = test_if_chief($('#id_responsable_' + i).val());
    jefe_asign = test_if_chief($('#id_usuario_' + i).val());

    if (id_usuario == $('#id_responsable_' + i).val() || $('#nivel').val() >= 4)
        if_jefe = true;
    if (parseInt($('#acc_planwork').val()) > 0)
        if_jefe = true;

    if ((jefe_asign && !if_jefe) && panel == 'delete') {
        alert("Usted no puede eliminar o rechazar una tarea o actividad que le fue asignada por su jefe " + jefe_asign);
        eject = false;
    }
    if ((jefe_resp && !if_jefe) && panel == 'delete') {
        alert("Usted no puede eliminar o rechazar una tarea o actividad de la que es responsable su jefe " + jefe_resp);
        eject = false;
    }
    if (!if_jefe && panel == 'delete' && eject) {
        if (Entrada($('#aprobado_' + i).val()) && _jefe == null) {
            text = "Usted no puede eliminar o rechazar una tarea que ya fue aprobado: " + $('#aprobado_' + i).val();
            alert(text + ". Pídale a su jefe inmediato que se la elímine del plan.");

            eject = false;
        }
    }
    if (Entrada($('#aprobado_' + i).val()) && panel == 'delete') {
        msg_tmp = "Usted no puede eliminar una tarea o actividad aprobada. ";
        msg_tmp = "Si no elije la opción de borrado físico esta se mostrará como suspendida";
        alert(msg_tmp);
    }
    if (parseInt($('#id_proyecto_' + i).val()) == 0 && panel == 'advance') {
        text = "Esta funcionalidad solo está disponible para las tareas que están asociadas a la ejecución de un proyecto. ";
        text += "Y solo tienen acceso el responsable de la tarea o el del proyecto al que pertenece la tarea.";
        alert(text);

        eject = false;
    }
    if (($('#id_responsable_' + i).val() != id_usuario && $('#id_usuario_' + i).val() != id_usuario) && panel == 'advance' && eject) {
        text = "Solo el responsable de la tarea: " + $('#responsable_' + i).val();
        alert(text + " puede registrar el estado o % de ejecución de la tarea.");

        eject = false;
    }
    if (panel == 'matter' || panel == 'accords' || panel == 'docs') {
        action = eject ? 'add' : 'list';
        showOpenWindow(panel, action);
        return;
    }
    if (eject)
        _mostrar(panel, i);
}

function m_show2mostrar(_panel) {
    var panel = _panel;
    var text;
    var msg_tmp;
    var action;

    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return;
    }

    var text;
    var i = $('#id').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var id_usuario = $('#id_usuario').val();
    var eject = true;

    var if_jefe = false;
    var jefe_asign = false;
    var jefe_resp = false;
    //var _jefe= false;

    if (parseInt($('#id_auditoria_' + i).val()) && (panel == 'delete' || panel == 'edit' || panel == 'reject')) {
        text = "Esta actividad es una acción de control o auditoría, para editar, modificar o eliminar la actividad deberá ";
        text += "ir al Plan Anual de Acciones de Control o al Plan Anual de Auditorías, según corresponda";
        alert(text);
        eject = false;
        return;
    }
    if (parseInt($('#id_archivo_' + i).val()) > 0 && (panel == 'delete' || panel == 'edit' || panel == 'reject')) {
        text = "Esta es una actividad generada a partir de una Indicación emitida desde la Oficina de Archivo o Despacho. ";
        text += "Para ser rechazada, modificada o eliminada debe hacerlo desde la Gestión de Archivos.";
        alert(text);
        eject = false;
        return;
    }
    if (parseInt($('#id_auditoria_' + i).val()) > 0 && panel == 'repro') {
        text = "Una auditoria, supervisión o cualquier acción de control no puede ser reprogramada desde los planes de trabajo. ";
        text += "Debe acceder al correspondiente \"Programa de Auditorías o de Acciones de Control\".";
        alert(text);
        eject = false;
        return;
    }
    if (parseInt($('#ifmeeting').val())) {
        if (parseInt($('#fixed_' + i).val()) && (panel == 'repro' || panel == 'delete' || panel == 'reject' || panel == 'delegate')) {
            msg_tmp = "Usted no puede reprogramar, eliminar o rechazar una reunión que parece que ya se realizó. ";
            msg_tmp += "Deberá eliminarles los acuerdos, participantes, debates, etc.";
            alert(msg_tmp);
            eject = false;
            return;
        }
    }

    // _jefe= test_if_chief($('#id_usuario').val());
    jefe_resp = test_if_chief($('#id_responsable_' + i).val());
    jefe_asign = test_if_chief($('#id_usuario_' + i).val());

    if (id_usuario == $('#id_responsable_' + i).val() || $('#nivel').val() >= 4)
        if_jefe = true;
    if (parseInt($('#acc_planwork').val()) > 0)
        if_jefe = true;

    if (!if_jefe && jefe_asign && panel == 'delete') {
        alert("Usted no puede eliminar o rechazar una tarea o actividad que le fue asignada por su jefe " + jefe_asign);
        eject = false;
        return;
    }
    if ((jefe_resp && !if_jefe) && panel == 'delete') {
        alert("Usted no puede eliminar o rechazar una tarea o actividad de la que es responsable su jefe " + jefe_resp);
        eject = false;
        return;
    }
    if (Entrada($('#aprobado_' + i).val()) && panel == 'delete') {
        msg_tmp = "Usted no puede eliminar una tarea o actividad aprobada. Si no elije la opción de borrado físico ";
        smqg_tipo += "esta se mostrará como suspendida";
        alert(msg_tmp);
        eject = false;
        return;
    }

    if ($('#cumplimiento_' + i).val() == 3 && (panel == 'repro' || panel == 'delete' || panel == 'reject')) {
        text = "Usted no puede reprogramar, eliminar o rechazar una actividad o tarea registrada como cumplida. ";
        text += "Primero deberá registrarla como incumplida o suspendida.";
        alert(text);
        eject = false;
        return;
    }

    if ($('#toshow_' + i).val() > 1 && (panel == 'edit' || panel == 'reject')) {
        if (!if_jefe) {
            msg_tmp = "Usted no puede rechazar o modificar una tarea o actividad registrada en los " + $('#msg_prs_' + i).val();
            msg_tmp += ". Esta tarea o actividad solo se puede rechazar o modificar desde " + $('#msg_prs_' + i).val() + " ."
            alert(msg_tmp);

            eject = false;
            return;
        } else {
            msg_tmp = "Tenga cuidado cuando intenta rechazar o modificar una tarea o actividad registrada en los " + $('#msg_prs_' + i).val();
            msg_tmp += ". Esta tarea o actividad es conveniente rechazar o modificar desde " + $('#msg_prs_' + i).val() + " ."
            alert(msg_tmp, function() {
                eject = true;
                _this_3();
            });
        }
    } else {
        _this_3();
    }

    function _this_1() {
        if (panel == 'matter' || panel == 'accords' || panel == 'docs' || panel == 'assist' || panel == 'debate') {
            action = eject ? 'add' : 'list';
            showOpenWindow(panel, action);
            return false;
        }
        return true;
    }

    function _this_2() {
        if ((panel == 'delete' || panel == 'register' || panel == 'repro' || panel == 'delegate') && eject)
            _mostrar(panel, i);
        if (panel == 'edit' && eject)
            edit(i);
    }

    function _this_3() {
        if (($('#toshow_' + i).val() > 1 && panel == 'delete')) {
            msg_tmp = "Usted no puede eliminar una tarea o actividad registrada en los " + $('#msg_prs_' + i).val();
            msg_tmp += ". Esta tarea o actividad, o su participación en ella, solo se puede eliminar desde " + $('#msg_prs_' + i).val() + " ."

            if (!$('#if_jefe').val()) {
                alert(msg_tmp);
                eject = false;
            } else {
                msg_tmp = "Usted esta tratando de eliminar una actividad o tarea proveniente del " + $('#msg_prs_' + i).val();
                msg_tmp += ". Es recomendable la opcion de reprogramar la actividad. Desea continua?";
                confirm(msg_tmp, function(ok) {
                    if (!ok)
                        return;
                    else {
                        eject = true;
                        _this_2();
                    }
                });
            }
        } else {
            if (!_this_1())
                return;
            else {
                eject = true;
                _this_2();
            }
        }
    }
}

function y_show2mostrar(panel) {
    var text;

    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return;
    }

    var msg_tmp;
    var action;

    var i = $('#id').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var id_usuario = $('#id_usuario').val();
    var id_proceso = $('#id_proceso').val();
    var eject = true;

    var if_jefe = false;
    var jefe_asign = false;
    var jefe_resp = false;
    //var _jefe= false;

    if (parseInt($('#id_auditoria_' + i).val()) && (panel == 'delete' || panel == 'edit' || panel == 'reject')) {
        text = "Esta actividad es una acción de control o auditoría, para editar, modificar o eliminar la actividad deberá ";
        text += "ir al Plan Anual de Acciones de Control o al Plan Anual de Auditorías, según corresponda"
        alert(text);
        return;
    }
    if (parseInt($('#id_archivo_' + i).val()) > 0 && (panel == 'delete' || panel == 'edit' || panel == 'reject')) {
        text = "Esta es una actividad generada a partir de una Indicación emitida desde la Oficina de Archivo o Despacho. ";
        text += "Para ser rechazada, modificada o eliminada debe hacerlo desde la Gestión de Archivos.";
        alert(text);
        eject = false;
        return;
    }
    if (parseInt($('#id_auditoria_' + i).val()) > 0 && panel == 'copy') {
        text = "Una auditoria, supervisión o cualquier acción de control no puede ser copiada. ";
        text += "Debe acceder al correspondiente \"Programa de Auditorías o de Acciones de Control\".";
        alert(text);
        eject = false;
        return;
    }
    if (parseInt($('#ifmeeting').val())) {
        if (parseInt($('#fixed_' + i).val())) {
            if (panel == 'repro' || panel == 'delete' || panel == 'edit' || panel == 'reject' || panel == 'delegate') {
                msg_tmp = "Usted no puede editar, reprogramar, eliminar, rechazar o delegar una reunión que parece ";
                msg_tmp += "que ya se realizó. Deberá eliminarles los acuerdos, participantes, debates, etc.";
                alert(msg_tmp);
                eject = false;
                return;
            }
        }
    }

    // _jefe= test_if_chief($('#id_usuario').val());
    jefe_resp = test_if_chief($('#id_responsable_' + i).val());
    jefe_asign = test_if_chief($('#id_usuario_' + i).val());
    if (parseInt($('#acc_planwork').val()) > 0)
        if_jefe = true;

    if (id_usuario == $('#id_responsable_' + i).val() || $('#if_jefe').val())
        if_jefe = true;

    if (!if_jefe) {
        text = "Solo un administrador o superusuario del sistema, o el Jefe del proceso puede modificar, eliminar o ";
        text += "actualizar las tareas o actividades incluidas en el Plan Anual.";
        alert(text);
        eject = false;
        return;
    }
    if ((jefe_asign && panel == 'delete') && !if_jefe) {
        alert("Usted no puede eliminar o rechazar una tarea o actividad que le fue asignada por su jefe " + jefe_asign);
        eject = false;
        return;
    }
    if (Entrada($('#aprobado_' + i).val()) && !if_jefe && panel == 'delete') {
        msg_tmp = "Usted no puede eliminar una tarea o actividad aprobada. Si no elije la opción de borrado físico ";
        msg_tmp += "esta se mostrará como suspendida";
        alert(msg_tmp);
        eject = false;
        return;
    }
    if (((jefe_resp && $('#id_usuario').val() != $('#id_responsable_' + i).val()) && panel == 'delete') && !if_jefe) {
        alert("Usted no puede eliminar o rechazar una tarea o actividad de la que es responsable su jefe " + jefe_resp);
        eject = false;
        return;
    }
    if (panel == 'matter' || panel == 'accords' || panel == 'docs' || panel == 'assist' || panel == 'debate') {
        /*
        action= eject ? 'add' : 'list';
        if (action == 'add' && (parseInt($('#fixed_'+i).val()) && parseInt($('#ifmeeting').val())))
            action= 'list';
        */
        showOpenWindow(panel, "add");
        return;
    }
    if ((panel == 'delete' || panel == 'register' || panel == 'delegate') && eject) {
        _mostrar(panel, i);
        return;
    }
    if ((panel == 'edit') && eject) {
        var signal = parseInt($('#ifmeeting').val()) > 0 ? 'anual_plan_meeting' : 'anual_plan';

        var fecha_origen = $('#fecha_origen_' + i).val();
        var fecha_termino = $('#fecha_termino_' + i).val();
        var _radio_date = 2;

        if (parseInt($('#ifmeeting').val()) > 0) {
            _radio_date = rightside ? 0 : 2;
        }

        var url = "../form/fevento.php?action=update&signal=" + signal + "&year=" + year + '&month=' + month;
        url += '&id=' + i + '&id_proceso=' + id_proceso + '&fecha_origen=' + encodeURI(fecha_origen);
        url += '&fecha_termino=' + encodeURI(fecha_termino) + '&_radio_date=' + _radio_date;
        self.location.href = url;
        return;
    }

    function this_4() {
        _mostrar(panel, i);
        return false;
    }

    if (!this_4())
        return;
}

function y_audit_show2mostrar(panel) {
    var text;
    var action;

    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return;
    }

    var i = $('#id').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var id_usuario = $('#id_usuario').val();
    var eject = true;

    var if_jefe = false;
    var jefe_asign = false;
    var jefe_resp = false;
    //var _jefe= false;

    // _jefe= test_if_chief($('#id_usuario').val());
    jefe_resp = test_if_chief($('#id_responsable_' + i).val());
    jefe_asign = test_if_chief($('#id_usuario_' + i).val());
    if ($('#acc_planaudit').val())
        if_jefe = true;

    if (id_usuario == $('#id_responsable_' + i).val() || $('#if_jefe').val())
        if_jefe = true;

    if (!if_jefe) {
        text = "Solo un administrador o superusuario del sistema, o el Jefe del proceso puede modificar, eliminar o ";
        text += "actualizar los controles o auditorías incluidas en el Plan Anual.";
        alert(text);
        eject = false;
    }
    if ((jefe_asign && panel == 'delete') && !if_jefe) {
        alert("Usted no puede eliminar o rechazar una acción de control o auditoría que le fue asignada por su jefe " + jefe_asign);
        eject = false;
    }
    if (Entrada($('#aprobado_' + i).val()) && panel == 'delete') {
        text = "Usted no puede eliminar una acción de control o auditoría aprobada. Si no elije la opción de borrado físico esta ";
        text += "se mostrará como suspendida";
        alert(text);
    }
    if ($('#cumplimiento_' + i).val() == 3 && (panel == 'repro' || panel == 'delete' || panel == 'reject')) {
        text = "Usted no puede reprogramar, eliminar o rechazar una acción de control o auditoría registrada como cumplida. ";
        text += "Primero deberá registrarla como incumplida o suspendida.";
        alert(text);
        eject = false;
    }

    function this_1() {
        if ((panel == 'delete' || panel == 'delegate') && eject) {
            _mostrar(panel, i);
            return false;
        }
        return true;
    }

    if (panel == 'delete' && parseInt($('#day').val()) == 0) {
        text = "Al eliminar esta acción de control o auditoría la eliminará en todas las fechas planificadas en el año en curso. ";
        text += "Desea continuar?";
        confirm(text, function(ok) {
            if (!ok) {
                eject = false;
            } else {
                eject = true;
                if (!this_1())
                    return;
            }
        });
    } else {
        this_1();
    }

    if ((panel == 'register') && eject) {
        _mostrar(panel, i);
        return false;
    }
    if ((panel == 'edit') && eject) {
        var fecha_origen = $('#fecha_origen_' + i).val();
        var fecha_termino = $('#fecha_termino_' + i).val();

        var url = "../form/fauditoria.php?action=update&signal=anual_plan&year=" + year + '&month=' + month + '&id=' + i;
        url += '&fecha_origen=' + encodeURI(fecha_origen) + '&fecha_termino=' + encodeURI(fecha_termino);
        self.location.href = url;
    }
    if ((panel == 'copy' || panel == 'repro') && eject) {
        if (panel == 'copy')
            text = 'Esta acción de control o auditoría será copiada para el Plan del proximo año. Desea Continuar?';
        if (panel == 'repro')
            text = 'Esta acción de control o auditoría será programada para otro día. Desea Continuar?';

        confirm(text, function(ok) {
            if (ok) {
                _mostrar(panel, i);
                return false;
            }
        });
    }
    if (panel == 'docs') {
        action = eject ? 'add' : 'list';
        showOpenWindow(panel, action);
        return false;
    }
}

function ShowContentRiesgo(i) {
    var dd = 'win-board-signal';
    CloseWindow(dd);

    $('#id_riesgo').val(i);
    $('#win-title').html($('#riesgo_' + i).val());

    $('#estado').html($('#estado_' + i).val());
    $('#_probabilidad').html($('#_probabilidad_' + i).val());
    $('#_impacto').html($('#_impacto_' + i).val());
    $('#_deteccion').html($('#_deteccion_' + i).val());
    $('#registro').html($('#registro_' + i).val());
    $('#observacion').html($('#observacion_' + i).val());
    $('#usuario').html($('#usuario_' + i).val());

    $('#manifestacion').html($('#manifestacion_' + i).val());
    $('#reg_fecha').html($('#reg_fecha_' + i).val());
    $('#probabilidad').html($('#probabilidad_' + i).val());
    $('#impacto').html($('#impacto_' + i).val());
    $('#deteccion').html($('#deteccion_' + i).val());
    $('#if_entity').val($('#if_entity_' + i).val());

    AssignPosition(dd);

    $('#img_edit').show();
    $('#img_delete').show();
    $('#img_copy').show();

    _show($('#img_edit'), '');
    _show($('#img_delete'), '');
    _show($('#img_copy'), '');

    if (parseInt($('#if_entity').val()) == 0) {
        $('#img_edit').hide();
        $('#img_delete').hide();
        $('#img_copy').hide();

        _hide($('#img_edit'), '');
        _hide($('#img_delete'), '');
        _hide($('#img_copy'), '');
    }
}

function ShowContentNota(i, id_proceso) {
    var dd = 'win-board-signal';
    CloseWindow(dd);

    $('#id_nota').val(i);
    $('#win-title').html($('#tipo_' + i).val());
    $('#proceso-name').html($('#proceso_' + i).val());
    $('#estado').html($('#estado_' + i).val());
    $('#registro_date_init').html($('#registro_date_init_' + i).val());
    $('#registro').html($('#registro_' + i).val());
    $('#observacion_item').html($('#observacion_' + i).val());
    $('#date_interval').html($('#date_interval_' + i).val());
    $('#usuario').html($('#usuario_' + i).val());
    // $('#descripcion').html($('#descripcion_'+i).val());
    $('#reg_fecha').html($('#reg_fecha_' + i).val());
    $('#registro_date').html($('#registro_date_' + i).val());
    $('#id_proceso_item').val(id_proceso);

    AssignPosition(dd);
}

var win_document = null;
var win_assist = null;
var win_debate = null;

function showOpenWindow(panel, action) {
    var id_proceso = $('#proceso').val();
    var id_evento;
    var id_indicador;
    var id_auditoria;
    var id_proyecto;
    var id_tarea;
    var id_riesgo;
    var id_nota;
    var id_politica;

    try {
        id_evento = $('#id_evento').val();
    } catch (e) {
        id_evento = 0;
    }
    try {
        id_auditoria = $('#id_auditoria').val();
    } catch (e) {
        id_auditoria = 0;
    }
    try {
        id_tarea = $('#id_tarea').val();
    } catch (e) {
        id_tarea = 0;
    }
    try {
        id_proyecto = $('#id_proyecto').val();
    } catch (e) {
        id_proyecto = 0;
    }
    try {
        id_riesgo = $('#id_riesgo').val();
    } catch (e) {
        id_riesgo = 0;
    }
    try {
        id_nota = $('#id_nota').val();
    } catch (e) {
        id_nota = 0;
    }
    try {
        id_politica = $('#id_politica').val();
    } catch (e) {
        id_politica = 0;
    }
    try {
        id_indicador = $('#id_indicador').val();
    } catch (e) {
        id_indicador = 0;
    }

    id_riesgo = id_riesgo != undefined ? id_riesgo : 0;
    id_nota = id_nota != undefined ? id_nota : 0;
    id_politica = id_politica != undefined ? id_politica : 0;

    //   var id_usuario= $('#id_usuario').val();

    var year = $('#year').val();
    var month = 0;
    try {
        month = $('#month').val();
    } catch (e) {
        month = 0;
    }

    var ifmeeting = parseInt($('#ifmeeting').val());
    var url = '?action=' + action + '&id_evento=' + id_evento + '&id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
    url += '&id_auditoria=' + id_auditoria + '&id_proyecto=' + id_proyecto + '&id_indicador=' + id_indicador;
    url += '&id_nota=' + id_nota + '&id_riesgo=' + id_riesgo + '&id_politica=' + id_politica;

    if (panel == 'matter' || panel == 'accords') {
        var _url = (panel == 'matter' && ifmeeting == 3) ? '../form/lmatter.php' : '../form/fmatter.php';
        url = _url + url;
        url += (panel == 'matter') ? '&ifaccords= 0' : '&ifaccords=1';

        //   window.showModalDialog(url,'','dialogWidth:856px;dialogHeight:532px;center:yes;scroll:off');
        document.open(url, "_blank", "width=960,height=640,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=yes");
        return;
    }

    if (panel == 'assist') {
        url = '../form/fassist.php' + url;
        win_assist = document.open(url, "_blank", "width=900,height=600,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=yes");
        return;
    }

    if (panel == 'docs') {
        url = '../form/fdocument.php' + url;
        // window.showModalDialog(url,'','dialogWidth:948px;dialogHeight:605px;center:yes;scroll:off');
        win_document = document.open(url, "_blank", "width=900,height=640,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=no");
    }

    if (panel == 'debate') {
        url = '../form/fdebate.php' + url;
        //    url+= '&id_usuario='+id_usuario;
        // window.showModalDialog(url,'','dialogWidth:948px;dialogHeight:605px;center:yes;scroll:off');
        win_debate = document.open(url, "_blank", "width=900,height=640,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=no");
    }
}