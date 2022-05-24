// JavaScript Document
var _item_global;
var i_global;

function set_indicador(i) {
    $('#id_indicador').val(i);

    $('#win-title').html($('#nombre_' + i).val());

    $('#observacion_real').html($('#observacion_real_' + i).val());
    $('#observacion_plan').html($('#observacion_plan_' + i).val());

    $('#cumulative').val($('#cumulative_' + i).val());
    $('#formulated').val($('#formulated_' + i).val());

    $('#registro_real').html($('#registro_real_' + i).val());
    $('#registro_plan').html($('#registro_plan_' + i).val());

    $('#valor_real').html($('#valor_real_' + i).val());
    $('#valor_plan').html($('#valor_plan_' + i).val());

    $('#responsable_real').html($('#responsable_real_' + i).val());
    $('#responsable_plan').html($('#responsable_plan_' + i).val());

    $('#id_user_real').val($('#id_user_real_' + i).val());
    $('#id_user_plan').val($('#id_user_plan_' + i).val());

    $('#trend').val($('#trend_' + i).val());
}

function set_item(_item, i) {
    $('#_descripcion_item').val($('#descripcion_' + _item + '_' + i).val());
    $('#_observacion_item').val($('#observacion_' + _item + '_' + i).val());
    $('#_registro_item').val($('#registro_' + _item + '_' + i).val());

    $('#registro_item').html($('#_registro_item').val());
    $('#observacion_item').html($('#_observacion_item').val());
    $('#descripcion_item').html($('#_descripcion_item').val());

    var title;

    switch (_item) {
        case 'pol':
            title = 'POLíTICA O LINEAMIENTO';
            break;
        case 'obj_sup':
            title = 'OBJETIVOS ESTRATÉGICOS DEL ORGANO SUPERIOR DE DIRECCIÓN';
            break;
        case 'obj':
            title = 'OBJETIVO ESTRATÉGICO';
            break;
        case 'ind':
            title = 'OBJETIVO DE TRABAJO';
            break;
        case 'indi':
            title = 'INDICADOR';
            break;
        case 'per':
            title = 'PERSPECTIVA';
            break;
        case 'prog':
            title = 'PROGRAMA';
            break;
        case 'obj_ci':
            title = 'OBJETIVO DE CONTROL INTERNO';
            break;
    }

    $('#win-title-item').html(title);
}

function set_task(i) {
    $('#_date_task').val($('#date_task_' + i).val());
    $('#_titulo_task').val($('#titulo_task_' + i).val());
    $('#_descripcion_task').val($('#descripcion_task_' + i).val());
    $('#_responsable_task').val($('#responsable_task_' + i).val());

    $('#_observacion_task').val($('#observacion_task_' + i).val());
    $('#_registro_task').val($('#registro_task_' + i).val());

    $('#titulo_task').html($('#titulo_task_' + i).val());
    $('#descripcion_task').html($('#descripcion_task_' + i).val());
    $('#responsable_task').html($('#responsable_task_' + i).val());

    $('#observacion_task').html($('#observacion_task_' + i).val());
    $('#registro_task').html($('#registro_task_' + i).val());

    $('#win-title-task').html($('#date_task_' + i).val());
}

function recompute() {
    var month = $('#month').val();
    var year = $('#year').val();
    var day = $('#day').val();
    var id_proceso = $('#proceso').val();
    var signal = $('#signal').val();

    var url = '../php/recompute.interface.php?id_proceso=' + id_proceso + '&day=' + day + '&month=' + month + '&year=' + year;
    url += '&item_recompute=' + signal;

    parent.app_menu_functions = false;

    var form = document.forms["treeForm"];
    form.action = url;
    form.submit();
}

function ShowContentItem(_item, i, _item_sup, i_sup) {
    CloseWindow('win-board-signal');
    CloseWindow('win-board-resum');
    CloseWindow('win-board-task');

    var dd;
    _item_global = _item;
    i_global = i;

    if (_item == 'indi') {
        dd = 'win-board-signal';
        set_indicador(i);
    }
    if (_item == 'task') {
        dd = 'win-board-task';
        set_task(i);
    }
    if (_item != 'indi' && _item != 'task') {
        dd = 'win-board-resum';
        set_item(_item, i);
    }

    if (_item == 'pol') {
        $('#_if_titulo').val($('#if_titulo_pol_' + i).val());
        $('#_if_inner').val($('#if_inner_pol_' + i).val());
    }

    $('#_item').val(_item);

    if (_item != 'indi')
        $('#_id').val($('#id_' + _item + '_' + i).val());
    else {
        $('#id_indicador').val(i);
        $('#_id').val(i);
    }

    if (_item_sup) {
        $('#_item_sup').val(_item_sup);
        $('#_id_sup').val($('#id_' + _item_sup + '_' + i_sup).val());
    } else {
        $('#_item_sup').val(0);
        $('#_id_sup').val(0);
    }

    $('#_if_entity').val($('#if_entity_' + _item + '_' + i).val());

    AssignPosition(dd);

    $('#img_edit').show();
    $('#img_delete').show();

    if (_item == 'indi') {
        $('#img_planning').show();
        $('#img_register').show();
    }

    if (parseInt($('#_if_entity').val()) == 0) {
        if (_item == 'indi') {
            $('#img_edit_indi').hide();
            $('#img_delete_indi').hide();
            $('#img_planning').hide();
            $('#img_register').hide();
        } else {
            $('#img_edit').hide();
            $('#img_delete').hide();
        }
    }

    /*
    var formulated= parseInt($('#formulated_'+i).val());
    document.getElementById("icon_plan").style.display= formulated ? "none" : "table-row";
    document.getElementById("icon_real").style.display= formulated ? "none" : "table-row";
    */
}

function _editar(action) {
    var _item = $('#_item').val();
    var _if_inner = $('#_if_inner').val();
    var _if_titulo = $('#_if_titulo').val();

    if (_item == 'pol' && _if_inner == 0) {
        alert("No se puede modificar un Lineamiento de la política económica y social del Partido y la Revolución.");
        return;
    }

    enviar_to_(action);
}

function _eliminar() {
    var _item = $('#_item').val();
    var signal = $('#signal').val();
    var _item_sup = $('#_item_sup').val();

    var _if_inner = $('#_if_inner').val();
    var _if_titulo = $('#_if_titulo').val();

    if (_item == 'pol' && _if_inner == 0) {
        alert("No se puede eliminar un Lineamiento de la política económica y social del Partido y la Revolución.");
        return;
    }

    if (signal == 'objetivo')
        if (_item == 'obj' && _item_sup == 0)
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (_item == 'indi')
        enviar_to_mostrar('delete');
    if (signal == 'objetivo_sup')
        if (_item == 'obj_sup')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (signal == 'inductor')
        if (_item == 'ind')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (signal == 'perspectiva')
        if (_item == 'per')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (signal == 'programa')
        if (_item == 'prog')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (signal == 'objetivo_ci')
        if (_item == 'obj_ci')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
    if (signal == 'politica')
        if (_item == 'pol')
            enviar_to_('delete');
        else
            enviar_to_mostrar('delete');
}

function _registrar() {
    var _item = $('#_item').val();
    var _if_titulo = $('#_if_titulo').val();

    if (_item == 'pol' && _if_titulo == 1) {
        alert("Se trata de un encabezado o titulo. No hay nada que hacer.");
        return;
    }

    enviar_to_mostrar('register');
}

function _graficar() {
    var _item = $('#_item').val();
    var _if_titulo = $('#_if_titulo').val();

    if (_item == 'pol' && _if_titulo == 1) {
        alert("Se trata de un encabezado o titulo. No hay nada que hacer.");
        return;
    }

    var _item = $('#_item').val();
    var _id = $('#_id').val();

    switch (_item) {
        case 'pol':
            graficar('politica', _id);
            break;
        case 'obj_sup':
            graficar('objetivo', _id);
            break;
        case 'obj':
            graficar('objetivo', _id);
            break;
        case 'obj_ci':
            graficar('objetivo_ci', _id);
            break;
        case 'ind':
            graficar('inductor', _id);
            break;
        case 'per':
            graficar('perspectiva', _id);
            break;
        case 'prog':
            graficar('programa', _id);
            break;
    }
}

function displayWindow(action) {
    var title;

    var signal = $('#signal').val();
    var _item = $('#_item').val();

    var title1 = '';

    if (action == 'delete') {
        if ((_item == 'obj' && _item_sup != 0) && signal == 'objetivo')
            title1 = "LA RELACION DEL OBJETIVO ESTRATÉGICO CON ";
        if (_item == 'indi') title1 = "LA RELACIÓN DEL INDICADOR CON ";
        if (_item == 'obj_sup' && signal != 'objetivo_sup')
            title1 = "LA RELACIÓN DEL OBJETIVO ESTRATÉGICO SUPERIOR CON ";
        if (_item == 'ind' && signal != 'inductor')
            title1 = "LA RELACIÓN DEL OBJETIVO DE TRABAJO CON ";
        if (_item == 'per' && signal != 'perspectiva')
            title1 = "LA RELACIÓN DE LA PERSPECTIVA CON ";
        if (_item == 'prog' && signal != 'programa')
            title1 = "LA RELACIÓN DEL PROGRAMA CON ";
        if (_item == 'obj_ci' && signal != 'objetivo_ci')
            title1 = "LA RELACIÓN DEL OBJETIVO DE CONTROL INTERNO CON ";
        if (_item == 'pol' && signal != 'politica')
            title1 = "LA RELACIÓN DE LA POLÍTICA O LINEAMIENTO CON ";


        if (signal == 'inductor')
            title = "ELIMINAR " + title1 + "OBJETIVO DE TRABAJO";
        if (signal == 'objetivo')
            title = "ELIMINAR " + title1 + "OBJETIVO ESTRATÉGICO";
        if (signal == 'perspectiva')
            title = "ELIMINAR " + title1 + "PERSPECTIVA";
        if (signal == 'programa')
            title = "ELIMINAR " + title1 + "  PROGRAMA";
        if (signal == 'politica')
            title = "ELIMINAR " + title1 + "POLÍTICA O LINEAMIENTO";
        if (signal == 'indicador')
            title = "ELIMINAR " + title1 + "INDICADOR";
        if (signal == 'objetivo_sup')
            title = "ELIMINAR " + title1 + "OBJETIVO ESTRATÉGICO DEL ORGANO SUPERIOR DE DIRECCIÓN Y CONTROL";
        if (signal == 'objetivo_ci')
            title = "ELIMINAR " + title1 + "OBJETIVO DE CONTROL INTERNO";
    }

    if (action == 'register') {
        if (_item == 'ind')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DEL OBJETIVO DE TRABAJO";
        if (_item == 'obj')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DEL OBJETIVO ESTRATÉGICO";
        if (_item == 'per')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DE LA PERSPECTIVA";
        if (_item == 'prog')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DEL PROGRAMA";
        if (_item == 'pol')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DE LA POLÍTICA O LINEAMIENTO";
        if (_item == 'obj_sup')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DEL OBJETIVO ESTRATÉGICO DEL ORGANO SUPERIOR";
        if (_item == 'obj_ci')
            title = "EVALUACIÓN CUALITATIVA DEL CUMPLIMIENTO DEL OBJETIVO DE CONTROL INTERNO";
    }

    displayFloatingDiv('div-ajax-panel', title, 70, 0, 15, 25);
}

function enviar_to_mostrar(action) {
    var _item = $('#_item').val();
    var id = $('#_id').val();

    var _item_sup = $('#_item_sup').val();
    var id_sup = $('#_id_sup').val();

    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();
    var id_proceso = $('#proceso').val();
    var signal = $('#signal').val();

    var url = '?_item=' + _item + '&id=' + id + '&action=' + action + '&signal=' + signal + '&year=' + year + '&month=' + month + '&day=' + day;
    url += '&id_proceso=' + id_proceso + '&_item_sup=' + _item_sup + '&id_sup=' + id_sup + '&_item_global=' + _item_global + '&i_global=' + i_global;

    if (action == 'delete')
        url = '../form/ajax/fdelete.ajax.php' + url;
    if (action == 'register')
        url = '../form/ajax/fstatus_tree.ajax.php' + url;

    var capa = 'div-ajax-panel';
    var metodo = 'GET';
    var valores = '';
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
    setTimeout("displayWindow('" + action + "')", 500);
}

function enviar_to_(action) {
    var msg;

    var signal = $('#signal').val();
    var _item = $('#_item').val();
    var _action = $('#exect').val();
    var id = $('#_id').val();

    if (action == 'delete' && _action != 'edit') {
        alert("Esta operación no esta permitida a su nivel de acceso o en esta instalación del sistema");
        return false;
    }

    switch (_item) {
        case 'pol':
            msg = "La politica o lineamiento será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'obj':
            msg = "El objetivo estrategico será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'obj_sup':
            msg = "El objetivo estrategico del Organismo Superior de Dirección será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'ind':
            msg = "El objetivo de trabajo será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'per':
            msg = "La perspectiva será eliminada. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'prog':
            msg = "El programa será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'obj_ci':
            msg = "El objetivo de control interno será eliminado. ¿Usted esta seguro? ¿Desea continuar?";
            break;
        case 'indi':
            msg = "El el indicador será eliminado y con el se perderán todos sus registros para el actual escenario. ¿Usted esta seguro? ¿Desea continuar?";
            break;
    }

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return false;
            else {
                enviar_to_mostrar(action);
                return false;
            }
        });
    } else {
        this_1();
    }

    function this_1() {
        switch (_item) {
            case 'pol':
                signal = "politica";
                break;
            case 'obj':
                signal = "objetivo";
                break;
            case 'obj_sup':
                signal = "objetivo";
                break;
            case 'ind':
                signal = "inductor";
                break;
            case 'per':
                signal = "perspectiva";
                break;
            case 'prog':
                signal = "programa";
                break;
            case 'obj_ci':
                signal = "objetivo";
                break;
            case 'indi':
                signal = "indicador";
                break;
        }

        var if_control_interno = _item == 'obj_ci' ? 1 : 0;
        var if_objsup = _item == 'obj_sup' ? 1 : 0;

        $('#if_control_interno').val(if_control_interno);

        document.forms[0].action = '../php/' + signal + '.interface.php?id=' + id + '&action=' + action + '&if_control_interno=' + if_control_interno + '&if_objsup=' + if_objsup;

        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].submit();
    }
}

function toUpper(text) {
    switch (text) {
        case 'inductor':
            return 'OBJETIVOS DE TRABAJO';
        case 'objetivo':
            return 'OBJETIVOS ESTRATÉGICOS';
        case 'objetivo_sup':
            return 'OBJETIVOS ESTRATÉGICOS DEL ORGANO SUPERIOR DE DIRECCIÓN';
        case 'perspectiva':
            return 'PERSPECTIVAS';
        case 'programa':
            return 'PROGRAMA';
        case 'objetivo_ci':
            return 'OBJETIVOS DE CONTROL INTERNO';
        case 'politica':
            return 'POLITICAS O LINEAMIENTOS';
    }
}

function refreshp(flag) {
    var action = $('#exect').val();
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var signal = $('#signal').val();

    var chk_inner = 0;
    var chk_sys = 0;
    var chk_title = 0;

    if (flag == 1) { month = 0; }

    try {
        chk_inner = $('#chk_inner').is(':checked') ? 1 : 0;
        chk_sys = $('#chk_sys').is(':checked') ? 1 : 0;
        chk_title = $('#chk_title').is(':checked') ? 1 : 0;
    } catch (e) {; }

    var url = 'l' + signal + '.php?action=' + action + '&id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
    url += '&chk_sys=' + chk_sys + '&chk_inner=' + chk_inner + '&chk_title=' + chk_title;

    parent.app_menu_functions = false;

    self.location.href = url;
}

function imprimir() {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var signal = $('#signal').val();

    var chk_inner = 0;
    var chk_sys = 0;
    var chk_title = 0;

    try {
        chk_inner = $('#chk_inner').is(':checked') ? 1 : 0;
        chk_sys = $('#chk_sys').is(':checked') ? 1 : 0;
        chk_title = $('#chk_title').is(':checked') ? 1 : 0;
    } catch (e) {; }

    var text = toUpper(signal);
    var url = '../print/l' + signal + '.php?id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
    url += '&chk_sys=' + chk_sys + '&chk_inner=' + chk_inner + '&chk_title=' + chk_title;
    show_imprimir(url, "IMPRIMIENDO RESUMEN " + text, "width=700,height=400,toolbar=no,location=no, scrollbars=yes");
}

function add() {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var signal = $('#signal').val();

    self.location.href = 'f' + signal + '.php?action=add&signal=list&id_proceso=' + id_proceso + '&year=' + year;
}

function ejecutar(form) {
    var signal = document.forms[form].signal.value;
    var id = document.forms[form].id.value;
    var action = document.forms[form].exect.value;
    var urlplus = action == 'delete' ? '&ajax_win=1' : '';

    var metodo = 'POST';
    var capa = 'div-ajax-panel';

    if (signal == 'perspectiva')
        document.forms[form].menu.value = "perspectiva";

    if (signal == 'objetivo_sup') {
        signal = 'objetivo';
        urlplus += '&if_objsup=1';
    }
    if (signal == 'objetivo_ci') {
        signal = 'objetivo';
        urlplus += '&if_ci=1';
    }

    var valores = $("#" + form).serialize();
    var url = '../php/' + signal + '.interface.php?id=' + id + '&action=' + action + urlplus;
    var funct= '';

    $('#_submit').hide();
    $('#_submited').show();

    FAjax(url, capa, valores, metodo, funct);

    parent.app_menu_functions = false;
}

function eliminar_link(form) {
    var metodo = 'POST';
    var capa = 'div-ajax-panel';
    var valores = $("#" + form).serialize();
    var funct= '';

    var signal = document.forms[form].signal.value;
    var id = document.forms[form].id.value;
    var _item = document.forms[form]._item.value;
    var id_sup = document.forms[form].id_sup.value;
    var _item_sup = document.forms[form]._item_sup.value;

    var url = '../php/reference.interface.php?action=delete&id=' + id + '&_item=' + _item + '&id_sup=' + id_sup + '&_item_sup=' + _item_sup;

    $('#_submit').hide();
    $('#_submited').show();

    FAjax(url, capa, valores, metodo, funct);

    alert('Por favor espere la operación puede tardar unos minutos ........');
    parent.app_menu_functions = false;
}

function _ejecutar() {
    var _item = document.forms['frm']._item.value;
    var id = document.forms['frm'].id.value;
    var url = '../php/status_tree_register.interface.php?id=' + id + '&_item=' + _item;

    var metodo = 'POST';
    var capa = 'div-ajax-panel';
    var valores = $("#frm").serialize();
    var funct= '';
    
    parent.app_menu_functions = false;
    $('#_submit').hide();
    $('#_submited').show();

    FAjax(url, capa, valores, metodo, funct);
}

function cerrar() {
    CloseWindow('div-ajax-panel');
    // self.location.reload();
}