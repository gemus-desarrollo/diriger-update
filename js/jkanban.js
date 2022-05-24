var id_proyecto;
var id_kanban_task;
var id_kanban_column = 0;
var id_kanban_column_origen;
var id_kanban_column_target;
var kanban_task_numero = 0;
var kanban_column_class = 0;
var kanban_columns_order = [];

function kanban_getId(stringId) {
    var id;
    id = stringId.match(/\d+/);
    return id[0];
}

function cerrar(flag, error) {
    if (Entrada(error)) {
        alert(error);
    }
    if (flag == 0)
        CloseWindow('div-ajax-panel');
    if (flag == 1)
        refreshp();
}

function dragTask_ajax() {
    id_proyecto = $('#id_proyecto').val();
    id_calendar = $('#id_calendar').val();
    var year = $('#year').val();

    var url = '../php/jkanban.interface.php?action=drag_task&ajax_win=1&id_proyecto=' + id_proyecto;
    url += '&id_calendar=' + id_calendar + '&id_tarea=' + id_kanban_task + '&numero=' + kanban_task_numero;
    url += '&id_kanban_column_origen=' + id_kanban_column_origen + '&id_kanban_column_target=' + id_kanban_column_target;
    url += '&year=' + year;

    var capa = 'div-ajax-exec';
    var metodo = 'GET';
    var valores = '';
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
}

function dragColum_ajax() {
    id_proyecto = $('#id_proyecto').val();
    id_calendar = $('#id_calendar').val();

    for (var i in kanban_columns_order) {
        kanban_columns_order[i][0] = kanban_getId(kanban_columns_order[i][0]);
    }
    // console.log(kanban_columns_order);
    JSON.stringify(kanban_columns_order);

    var url = '../php/jkanban.interface.php?action=drag_column&ajax_win=1&id_proyecto=' + id_proyecto;
    url += '&id_calendar=' + id_calendar + '&kanban_columns_order=' + encodeURIComponent(JSON.stringify(kanban_columns_order));

    var capa = 'div-ajax-exec';
    var metodo = 'GET';
    var valores = '';
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
}

function deleteBoard_ajax() {
    id_proyecto = $('#id_proyecto').val();

    var url = '../php/jkanban.interface.php?action=delete_column&ajax_win=1&id_proyecto=' + id_proyecto;
    url += '&id_kanban_column=' + id_kanban_column;

    var capa = 'div-ajax-exec';
    var metodo = 'GET';
    var valores = '';
    var funct= '';
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
}

function displayWindow(panel) {
    var w = 50;
    var title;

    title = "AGREGAR COLUMNA";
    displayFloatingDiv('div-ajax-panel-kanban', title, w, 0, 10, 20);
    kanban_column_class = 0;
    charts_color_pick_class = 0;
}

function add_column_ajax() {
    id_proyecto = $('#id_proyecto').val();

    if (!Entrada($('#nombre').val())) {
        alert("Debe especificar el título o nombre para la columna");
        return false;
    }

    kanban_column_class = charts_color_pick_class;
    if (kanban_column_class == 0) {
        alert("Debe especificar el color para la nueva columna");
        return false;
    }

    var descripcion = $('#descripcion').val();
    descripcion = descripcion == 'undefined' ? "" : encodeURIComponent(descripcion);

    var url = '../php/jkanban.interface.php?action=add&ajax_win=1&id_proyecto=' + id_proyecto;
    url += '&nombre=' + encodeURIComponent($('#nombre').val());
    url += '&descripcion=' + descripcion + '&kanban_column_class=' + kanban_column_class;

    var capa = 'div-ajax-panel-kanban';
    var metodo = 'GET';
    var valores = '';
    var funct= '';
    
    FAjax(url, capa, valores, metodo, funct);
}