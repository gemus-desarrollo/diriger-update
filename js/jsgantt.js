var id_kanban_column = 0;

function cerrar() {
    parent.app_menu_functions = false;
    CloseWindow('div-ajax-panel');
    refreshp();
}

function _register() {
    var signal = $('#signal').val();
    var id_proyecto = $('#id_proyecto').val();
    var id_tarea = $('#id_tarea').val();
    var id_calendar = $('#id_calendar').val();

    var _url = '../form/ajax/ftarea_register.ajax.php?id_tarea=' + id_tarea + '&id_proyecto=' + id_proyecto;
    _url += '&signal=' + signal + '&id_evento=0&id_calendar=' + id_calendar;

    var capa = 'div-ajax';

    $.ajax({
        url: _url,
        method: 'POST',

        cache: false,
        processData: false,
        evalScripts: true,

        beforeSend: function() {
            $("#" + capa).html("<div class='loading-indicator'>Procesando, espere por favor...</div>");
        },
        success: function(response) {
            $("#" + capa).html(response);
            $("#eventos").focus(function() {
                $("#eventos").combobox();
                $("#eventos").addClass("form-control");
            });
        },
        error: function(xhr, status) {
            alert('Disculpe, existió un problema en la conexión AJAX');
        }
    });
}

function _column() {
    var capa = 'div-ajax';
    var metodo = 'GET';
    var valores = '';
    var funct= '';

    var signal = $('#signal').val();
    var id_proyecto = $('#id_proyecto').val();
    var id_tarea = $('#id_tarea').val();
    var id_calendar = $('#id_calendar').val();

    var url = '../form/ajax/ftarea_kanban_column.ajax.php?id_tarea=' + id_tarea + '&id_proyecto=' + id_proyecto;
    url += '&signal=' + signal + '&id_evento=0&id_calendar=' + id_calendar + '&id_kanban_column=' + id_kanban_column;

    FAjax(url, capa, valores, metodo, funct);
}

function mostrar(panel) {
    var title;
    var w = 70;

    if (panel == 'hit' || panel == 'depend' || panel == 'docs') {
        _mostrar(panel);
        return;
    }

    if (panel == 'register') {
        w = 70;
        title = "ESTADO DE EJECUCIÓN DE LA TAREA";
    }
    if (panel == 'column') {
        w = 50;
        title = "MOVER A COLUMNA DE ESTADO";
    }
    displayFloatingDiv('div-ajax-panel', title, w, 0, 10, 10);

    if (panel == 'register') {
        _register();
    }
    if (panel == 'column') {
        _column();
    }
}

function ejecutar(panel) {
    var url;
    var metodo = 'POST';
    var capa = 'div-ajax';
    var valores;
    var funct= '';

    if (panel == 'register') {
        url = '../php/tarea_register.interface.php?';
        valores = $("#fregtarea").serialize();
    }
    if (panel == 'column') {
        url = '../php/jkanban.interface.php?action=drag_task&';
        valores = $("#fkanban_column").serialize();
    }

    FAjax(url, capa, valores, metodo, funct);
}

function saveGantt() {
    var xmlString = gantt.xml.serialize();
    var id_proceso = $('#proceso').val();
    var id = $('#id_proyecto').val();
    var id_programa = $('#programa').val();
    var month = $('#month').val();
    var year = $('#year').val();

    var url = '../php/proyecto.interface.php?menu=gantt&action=add&exect=add&signal=proyecto';
    url += '&id=' + id + '&id_proyecto=' + id + '&xmlString=' + xmlString;
    url += '&id_programa=' + id_programa + '&id_proceso=' + id_proceso + '&month=' + month + '&year=' + year;

    var metodo = 'get';
    var capa = 'div-ajax';
    var funct= '';
    
    FAjax(url, capa, valores, metodo, funct);
}

function _mostrar(panel) {
    var url;

    if (panel == 'hit')
        url = '../form/ftarea_hito.php';
    if (panel == 'depend')
        url = '../form/ftarea_depend.php';
    if (panel == 'docs')
        url = '../form/fdocument.php';

    url += '?id_tarea=' + $('#id_tarea').val() + '&id_proyecto=' + $('#id_proyecto').val() + '&if_jefe=' + $('#if_jefe').val();
    url += '&action=' + $('#exect').val();
    win_document = document.open(url, "_blank", "width=900,height=700,toolbar=no,location=0,menubar=no,location=no,titlebar=yes,scrollbars=no");
}

function _ShowContentTask(i) {
    var ifGrupo = parseInt($('#ifgrupo_' + i).val());

    if (ifGrupo) {
        _hide($('#img_register'), 'md');
        _hide($('#img_depend'), 'md');
        _hide($('#img_hit'), 'md');
    } else {
        _show($('#img_register'), 'md');
        _show($('#img_depend'), 'md');
        _show($('#img_hit'), 'md');
    }
}

function ShowContentTask(pID) {
    CloseWindow('win-board-signal');
    displayFloatingDiv('win-board-signal', $('#tarea_' + pID).val(), 60, 0, 10, 20);

    _ShowContentTask(pID);

    $('#id_tarea').val($('#id_tarea_' + pID).val());
    $('#tarea').val($('#tarea_' + pID).val());
    $('#responsable').val($('#responsable_' + pID).val());
    $('#descripcion').val($('#descripcion_' + pID).val());
    $('#avance').val($('#avance_' + pID).val());
    $('#fecha_inicio').val($('#fecha_inicio_' + pID).val());
    $('#fecha_fin').val($('#fecha_fin_' + pID).val());
    $('#numero').val($('#numero_' + pID).val());

    $('#p_tarea').html($('#tarea_' + pID).val());
    $('#p_responsable').html($('#responsable_' + pID).val());
    $('#p_descripcion').html($('#descripcion_' + pID).val());
    $('#p_avance').html($('#avance_' + pID).val());
    $('#p_fecha_inicio').html($('#fecha_inicio_' + pID).val());
    $('#p_fecha_fin').html($('#fecha_fin_' + pID).val());
}