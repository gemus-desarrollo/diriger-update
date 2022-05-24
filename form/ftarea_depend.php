<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";
require_once "../php/class/register_tarea.class.php";

require_once "../php/class/badger.class.php";

$id_tarea= $_GET['id_tarea'];
$if_jefe= !is_null($_GET['if_jefe']) ? $_GET['if_jefe'] : false;

$obj= new Ttarea($clink);
$obj->SetIdTarea($id_tarea);
$obj->Set();

$id_proyecto= $obj->GetIdProyecto();
$id_proceso= $obj->GetIdProceso();
$id_tarea_grupo= $obj->GetIdTarea_grupo();

$fecha_inicio= $obj->GetFechaInicioPlan();
$fecha_fin= $obj->GetFechaFinPlan();
$year= date('Y', strtotime($fecha_inicio));

$array_target= null;
$array_sources= null;

unset($obj_task);
$obj_task= new Tregister_tarea($clink);
$obj_task->SetYear($year);
$obj_task->SetIfGrupo(false);
$obj_task->SetIdProyecto($id_proyecto);
$obj_task->SetIdProceso($id_proceso);

$t_result_task= $obj_task->listar(true, true);
$t_not_cant_rows= $obj_task->GetCantidad();

$obj_task->SetIdTarea($id_tarea);
$obj_task->SetIdProceso(null);
$array_tareas= $obj_task->get_restrictions_depend();

$array_targets= $obj_task->array_target_tareas;
$array_sources= $obj_task->array_source_tareas;

if (empty($t_not_cant_rows))
    $t_not_cant_rows= 0;
if (empty($cant_target_rows))
    $cant_target_rows= 0;
if (empty($cant_source_rows))
    $cant_source_rows= 0;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title><?=$title?></title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/alarm.css?">

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js"></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script language="javascript">
        var $table_depend;
        var row_depend;
        var cant_source_rows= 0;

        var array_tareas= new Array();
        <?php
        $i= 0;
        while ($row= $clink->fetch_array($t_result_task)) {
            if ($row['ifgrupo'])
                continue;
            if ($row['_id'] == $id_tarea)
                continue;
        ?>
        array_tareas[<?=$row['_id']?>]= [
            <?=$row['_id']?>,
            '<?=textparse($row['nombre'])?>',
            '<?=odbc2date($row['fecha_inicio_plan'])?>',
            '<?=odbc2date($row['fecha_fin_plan'])?>'
        ];
        <?php } ?>

        var array_sources = new Array();
        <?php
        $i= 0;
        foreach ($array_sources as $array) {
        ?>
        array_sources[<?=++$i?>] = <?=$array['id']?>;
        <?php } ?>

        var numero_depend = <?=$i?>;

        var array_targets= new Array();
        <?php
        $i= 0;
        $array= null;
        foreach ($array_targets as $array) {
        ?>
        array_targets[<?=++$i?>] = <?=$array['id']?>;
        <?php } ?>

        var numero_target = <?=$i?>;

        function delete_depend(id) {
            $("#source_" + id).val(0);

            var ids = new Array();
            ids.push(id);

            $table_depend.bootstrapTable('remove', {
                field: 'id',
                values: ids
            });

            var irow = array_sources.indexOf(id);
            array_sources[irow] = 0;

            for (var i = 1; i <= $('#cant_source_rows').val(); i++) {
                try {
                    $('#option_' + i + '_' + id).css('display', 'block');
                }  catch (e) {;}
            }
        }

        function add_depend() {
            var cant_source_rows = $('#cant_source_rows').val();
            ++cant_source_rows;
            $('#cant_source_rows').val(cant_source_rows);

            ++numero_depend;

            var strHtml1 = numero_depend;
            var strHtml2 = '<a href="#" class="btn btn-danger btn-sm" onclick="delete_depend(' + numero_depend + ')">' +
                '<i class="fa fa-trash"></i>Eliminar' +
                '</a>';

            var strHtml3 = '<select id="source_' + numero_depend + '" name="source_' + numero_depend +
                '" class="form-control" onchange="taskrow_select(' + numero_depend + ')">' +
                '<option id="option_' + numero_depend + '_0" value="0"> ... </option>' +
                <?php
                $clink->data_seek($t_result_task, 0);
                while ($row = $clink->fetch_array($t_result_task)) {
                    if ($row['_id'] == $id_tarea)
                        continue;
                    if (boolean($row['ifgrupo']))
                        continue;
                    if (array_key_exists($row['id'], $array_targets))
                        continue;
                ?>
                    '<option id="option_' + numero_depend +
                    '_<?=$row['_id'] ?>" value="<?= $row['_id'] ?>">' +
                    <?php
                    $tarea= "{$row['nombre']} ==> ". odbc2date($row['fecha_inicio_plan']).' - '. odbc2date($row['fecha_fin_plan']);
                    echo "'$tarea'+";
                    ?> '</option>' +
                <?php } ?>
                '</select>';

            var strHtml4 = '<select id="tipo_depend_' + numero_depend + '" name="tipo_depend_' + numero_depend +
                '" class="form-control" onchange="tiporow_select(' + numero_depend + ')">' +
                '<option value="ND">No hay dependencias. No se relacionan</option>' +
                '<option value="FS">Al Finalizar Comienza (FC). Cuando finaliza esta tarea, comienza </option>' +
                '<option value="SF">Al Comenzar Finaliza (CF). Al comenzar esta tarea, finaliza </option>' +
                '<option value="SS">Al Comenzar Comienza (CC). Al comenzar esta tarea, tambien comienza </option>' +
                '<option value="FF">Al Finaliza Finaliza(FF). Al terminar esta tarea, tambien termina </option>' +
                '</select>';

            $table_depend.bootstrapTable('insertRow', {
                index: numero_depend,
                row: {
                    id: strHtml1,
                    icon: strHtml2,
                    nombre: strHtml3,
                    depend: strHtml4
                }
            });
        }

        array_sources[cant_source_rows] = 0;
    </script>

    <script type='text/javascript'>
        function tiporow_select(irow) {
            if ($('#source_' + irow).val() == 0) {
                $('#tipo_depend_' + irow).val('ND');
                alert('Primero seleccione la tarea con la que establecerá la dependencia de esta tarea');
                return;
            }
            if ($('#source_' + irow).val() > 0 && $('#tipo_depend_' + irow).val() == 'ND') {
                $('#tipo_depend_' + irow).val(0);
                alert('Se debe de especificar un tipo de dependencia que se tendrá esta tarea de la tarea seleccionada');
                return;
            }
            if (!validate_row(irow)) {
                $('#tipo_depend_' + irow).val(0);
                return;
            }
        }

        function validate_row(irow, id) {
            if (id == 0) {
                $('#tipo_depend_' + irow).val('ND');
                return false;
            }

            for (var i = 1; i < $('#cant_source_rows').val(); i++) {
                if (i == irow)
                    continue;

                if (id == $('#source_' + i).val()) {
                    $('#source_' + irow).val(0);
                    alert('Ya esta definida una relación con esta tarea. Deberá eliminar primero la relación anterior.');
                    return false;
                }
            }

            for (var i = 1; i < $('#cant_reject_rows').val(); $i++) {
                if (array_reject[i] == id) {
                    $('#source_' + irow).val(0);
                    alert(
                        "la actual tarea deteermina el inicio o finalización  de esta tarea selecionada. No puede establecer una nueva realación");
                    return false;
                }
            }
            return true;
        }

        function taskrow_select(irow) {
            var id = $('#source_' + irow).val();
            var found = false;

            if (!validate_row(irow, id))
                return;

            if (irow > $('#cant_source_rows').val()) {
                var _irow = irow;
                irow = parseInt($('#cant_source_rows').val()) + 1;
                $('#cant_source_rows').val(irow);

                $('#source_' + irow).val($('#source_' + _irow).val());
                $('#tipo_depend_' + irow).val($('#tipo_depend_' + _irow).val());

                if (_irow != irow) {
                    $('#source_' + _irow).val(0);
                    $('#tipo_depend_' + _irow).val('ND');
                }
            }

            if ($('#source_' + irow).val() > 0) {
                $('#tipo_depend_' + irow).val('ND');

                select_option_set(irow, $('#source_' + irow).val());
            }
        }

        function select_option_set(irow, val) {
            if (array_sources[irow] > 0) {
                for (var i = 1; i <= $('#cant_source_rows').val(); i++) {
                    $('#option_' + i + '_' + array_sources[irow]).css('display', 'block');

                    if (i == irow)
                        continue;

                    $('#option_' + i + '_' + val).css('display', 'none');
                }
            }
            array_sources[irow] = val;
        }
    </script>

    <script type='text/javascript'>
        function closep() {
            if (opener)
                opener.location.reload();

            self.close();
        }

        function ejecutar() {
            var form = document.forms['ftarea_depend']
            form.action = '../php/tarea_register.interface.php';

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            form.submit();
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            InitDragDrop();

            $table_depend = $('#table-depend');
            $table_depend.bootstrapTable('append', row_depend);
        });
    </script>

    <script type="text/javascript" src="../js/form.js?version="></script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">REALACIÓN CON OTRAS TAREAS</div>
                <div class="card-body">

                    <?php
                    $obj= new Ttarea($clink);
                    $obj->SetIdTarea($id_tarea);
                    $obj->Set();
                    $tarea= $obj->GetNombre();
                    $fecha_inicio= odbc2date($obj->GetFechaInicioPlan());
                    $fecha_fin= odbc2date($obj->GetFechaFinPlan());
                    ?>

                    <div class="alert alert-info">
                        <strong>Tarea:</strong> <?=$tarea?><br />
                        <strong>Duración:</strong> Desde: <?=$fecha_inicio?> Hasta: <?=$fecha_fin?>
                    </div>

                    <form id="ftarea_depend" action='javascript:ejecutar()' method="post">
                        <input type="hidden" id="menu" name="menu" value="tarea_depend" />
                        <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea?>" />
                        <input type="hidden" id="id" name="id" value="<?=$id_tarea?>" />

                        <?php
                        $obj_task = new Ttarea($clink);
                        $obj_task->SetYear($year);
                        $obj_task->SetIdProceso($id_proceso);
                        $obj_task->SetIdProyecto($id_proyecto);
                        $result_task = $obj_task->listar(false, true);
                        ?>

                        <div id="toolbar1" class="btn-toolbar">
                            <label class="text mr-2">
                                Definir dependencias de otras tareas
                            </label>
                            <button type="button" class="btn btn-primary" id="btn-depend-add" onclick="add_depend()">
                                <i class="fa fa-plus"></i>Nueva
                            </button>
                        </div>

                        <?php
                        $i = 0;
                        reset($array_sources);
                        foreach ($array_sources as $array) {
                        ?>
                            <input type="hidden" id="init_source_<?= ++$i ?>" name="init_source_<?= ++$i ?>"
                            value="<?= $array['id'] ?>" />
                        <?php } ?>

                        <script type='text/javascript'>
                        row_depend = [
                            <?php
                            $i = 0;
                            reset($array_sources);
                            foreach ($array_sources as $source) {
                                if ($i > 1)
                                    echo ",";
                            ?> {
                                id: <?= ++$i ?>,
                                icon: '' +
                                    '<a href="#" class="btn btn-danger btn-sm" onclick="delete_depend(<?=$i?>)">' +
                                    '<i class="fa fa-trash"></i>Eliminar' +
                                    '</a>' +
                                    '',

                                nombre: '' +
                                    '<select id="source_<?= $i ?>" name="source_<?= $i ?>" class="form-control" onchange="taskrow_select(<?= $i ?>)">' +
                                    '<option id="option_<?= $i . '_0' ?>" value="0"></option>' +
                                    <?php
                                    $clink->data_seek($result_task, 0);
                                    $tarea= null;
                                    while ($row = $clink->fetch_array($result_task)) {
                                        if ($row['_id'] == $id_tarea)
                                            continue;
                                        if (boolean($row['ifgrupo']))
                                            continue;
                                        if (array_key_exists($row['id'], $array_targets))
                                            continue;
                                    ?>
                                        '<option id="option_<?="$i_{$row['_id']}"?>" value="<?= $row['_id'] ?>" <?php if ($row['_id'] == $source['id']) echo "selected"; ?>>' +
                                    <?php
                                    $tarea= "{$row['nombre']} ==> ";
                                    $tarea.= odbc2date($row['fecha_inicio_plan']).' - '. odbc2date($row['fecha_fin_plan']);
                                    echo "'$tarea'+";
                                    ?> '</option>' +
                                    <?php } ?>
                                    '</select>' +
                                    '',

                                depend: '' +
                                    '<select id="tipo_depend_<?= $i ?>" name="tipo_depend_<?= $i ?>" class="form-control" onchange="tiporow_select(<?= $i ?>)">' +
                                    '<option value="ND<?php if (empty($source['tipo_depend'])) echo "selected"; ?>>No hay dependencia</option>' +
                                    '<option value="FS" <?php if ($source['tipo_depend'] == 'FS') echo "selected"; ?>>Al Finalizar Comienza (FC). Cuando finaliza esta tarea, comienza </option>' +
                                    '<option value="SF" <?php if ($source['tipo_depend'] == 'SF') echo "selected"; ?>>Al Comenzar Finaliza (CF). Al comenzar esta tarea, finaliza </option>' +
                                    '<option value="SS" <?php if ($source['tipo_depend'] == 'SS') echo "selected"; ?>>Al Comenzar Comienza (CC). Al comenzar esta tarea, tambien comienza </option>' +
                                    '<option value="FF" <?php if ($source['tipo_depend'] == 'FF') echo "selected"; ?>>Al Finalizar Finaliza(FF). Al terminar esta tarea, tambien termina </option>' +
                                    '</select>' +
                                    ''
                            }
                            <?php } ?>
                        ];
                        </script>

                        <table id="table-depend"
                            class="table table-hover table-striped"
                            data-toggle="table"
                            data-height="450"
                            data-toolbar="#toolbar1"
                            data-row-style="rowStyle"
                            data-search="true">
                            <thead>
                                <tr>
                                    <th data-field="id">No</th>
                                    <th data-field="icon"></th>
                                    <th data-field="nombre">Tarea</th>
                                    <th data-field="depend">Dependencia</th>
                                </tr>
                            </thead>
                        </table>

                        <hr style="margin: 6px 0px 25px 0px;">
                        </hr>

                        <input type="hidden" id="cant_target_rows" name="cant_target_rows" value="<?= $t_not_cant_rows ?>" />
                        <input type="hidden" id="cant_source_rows" name="cant_source_rows" value="<?= $i ?>" />


                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($if_jefe) { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="button" onclick="self.close();">Cerrar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div>
        </div>

    </div>

</body>

</html>