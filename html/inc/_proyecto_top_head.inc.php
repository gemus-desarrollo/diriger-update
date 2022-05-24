<?php require '../form/inc/_page_init.inc.php'; ?>

<link rel="stylesheet" href="../css/color-charts.css" />
<script type="text/javascript" src="../js/color-charts.js"></script>
    
<link rel="stylesheet" type="text/css" href="../css/general.css?version=">
<link rel="stylesheet" type="text/css" href="../css/table.css?version=">

<link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

<link rel="stylesheet" href="../libs/windowmove/windowmove.css?version=" />
<script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

<script type="text/javascript" src="../js/windowcontent.js?version="></script>

<link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
<script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

<script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
<script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

<link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css?version=">
<script type="text/javascript" src="../js/widget.js?version="></script>

<link rel="stylesheet" type="text/css" href="../css/tablero.css?version=" />
<script type="text/javascript" src="../js/tablero.js?version=" charset="utf-8"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

<link href="../libs/combobox/jquery-combobox.css" rel="stylesheet">
<script type="text/javascript" src="../libs/combobox/jquery-combobox.js"></script>

<link rel="stylesheet" type="text/css" href="../css/custom.css?version=" />

<link rel="stylesheet" type="text/css" href="../css/menu.css?version=" />

<script type="text/javascript" charset="utf-8" src="../js/time.js?version="></script>

<script type="text/javascript" src="../js/ajax_core.js?version=" charset="utf-8"></script>

<script language="javascript">
var array_chief_id = new Array(10);
var array_chief_nombre = new Array(10);
var array_chief_cargo = new Array(10);
</script>

<script type="text/javascript">
function refreshp() {
    var year = $('#year').val();
    var month = $('#month').val();
    var id_proceso = $('#proceso').val();
    var id_programa = $('#programa').val();

    var id_calendar = $('#id_calendar').val();

    var url = '<?=$signal?>.php?version=&action=<?= $action ?>' + '&id_programa=' + id_programa;
    url += '&month=' + month + '&year=' + year + '&id_proceso=' + id_proceso + '&id_calendar=' + id_calendar;

    self.location = url;
}

function loadpage(id) {
    $('#id_calendar').val(id);
    refreshp();
}

function add_project() {
    var id_proceso = $("#proceso").val();
    var year = $("#year").val();
    var month = $("#month").val();
    var day = $("#day").val();
    var id_programa = $("#programa").val();

    var url = "../form/fproyecto.php?action=add&id_proceso=" + id_proceso + "&year=" + year + "&month=" + month +
        "&day=" + day;
    url += "&id_programa=" + id_programa;

    self.location.href = url;
}

function edit_project() {
    var id_proceso = $('#proceso').val();
    var id_proyecto = $('#proyecto').val();
    var id_programa = $('#programa').val();
    var year = $("#year").val();
    var month = $("#month").val();

    var url = "../php/proyecto.interface.php?menu=tablero&signal=tablero&&action=edit&id=" + id_proyecto +
        "&id_proceso=" + id_proceso;
    url += "&year=" + year + "&month=" + month + "&id_programa=" + id_programa;

    self.location.href = url;
}

function add_tarea() {
    var id_proceso = $('#proceso').val();
    var id = $('#id_proyecto').val();
    var id_programa = $('#programa').val();
    var month = $('#month').val();
    var year = $('#year').val();

    var fecha_origen = encodeURI($('#fecha_origen').val());
    var fecha_termino = encodeURI($('#fecha_termino').val());

    var url = '../form/ftarea.php?&action=add&exect=add&signal=proyecto&&id_proyecto=' + id;
    url += '&id_programa=' + id_programa + '&id_proceso=' + id_proceso + '&month=' + month + '&year=' + year +
        '&menu=tablero';
    url += '&fecha_origen=' + fecha_origen + '&fecha_termino=' + fecha_termino;

    self.location.href = url;
}

function enviar_tarea(action) {
    var id_proceso = $('#proceso').val();
    var id_proyecto = $('#id_proyecto').val();
    var id_programa = $('#programa').val();
    var id_tarea = $('#id_tarea').val();
    var month = $('#month').val();
    var year = $('#year').val();

    function _this() {
        var url = '../php/tarea.interface.php?&action=' + action + '&exect=' + action + '&signal=proyecto&id=' +
            id_tarea;
        url += '&id_proyecto=' + id_proyecto + '&id_programa=' + id_programa + '&id_proceso=' + id_proceso;
        url += '&month=' + month + '&year=' + year + '&menu=tablero';
        self.location.href = url;
    }

    if (action == 'delete') {
        var text = "Esta seguro de querer eliminar esta tarea del sistema? Se perderán la tarea y todas las actividades asociadas. ¿Desea continuar?";
        confirm(text, function(ok) {
            if (ok)
                _this();
            else
                return;
        });
    } else
        _this();
}
</script>