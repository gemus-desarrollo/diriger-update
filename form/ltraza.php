<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/code.class.php";

require_once "../php/class/traza.class.php";

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new TTime();
$time->splitTime();
$year= $time->GetYear();
$month= (int)$time->GetMonth();
$lastday= $time->longmonth();

$inicio= !empty($_GET['inicio']) ? urldecode($_GET['inicio']) : "1/".$month."/".$year;
$fin= !empty($_GET['fin']) ? urldecode($_GET['fin']) : $lastday."/".$month."/".$year;
$action= !empty($_GET['action']) ? $_GET['action'] : 'IMPRIMIR';
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

$obj= new Ttraza($clink);
$obj->SetFechaInicioPlan(time2odbc($inicio. " 00:00:00"));
$obj->SetFechaFinPlan(time2odbc($fin. " 23:59:00"));

$array= !empty($action) ? $array_tablas[$action] : null;

$result= $obj->listar($action, $id_usuario);

$array_actions= array(
    'ENTRADA' => array('evento', 'Entradas al sistema', 'Entradas al sistema'),
    'IMPRIMIR' => array('tarea', 'Impresion de documentos', 'Impresion de documentos')
);

$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tusuarios(false);
$obj_user->SetIdProceso($_SESSION['id_entity']);
$obj_user->set_user_date_ref($fecha_inicio);
$result_user= $obj_user->listar(null, null, null, null, null, null, $action == 'add' ? true : false);

$obj_user= new Tusuario($clink);
$obj_prs= new Tproceso($clink);

$url_page= "../form/ltraza.php?signal=$signal&action=$action&menu=evento&action=$action";
$url_page.= "&inicio=". urlencode($inicio)."&fin=".urlencode($fin);

set_page($url_page);
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE ENTRADAS E IMPRESIONES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/moment.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.es.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript" type="text/javascript">
    function refreshp() {
        var inicio = $('#fecha_inicio').val();
        var fin = $('#fecha_fin').val();
        var id_usuario = $('#usuario').val();
        var action = $('#action').val();

        if (DiferenciaFechas(fin, inicio, 'd') < 0) {
            alert(
                "Existe incongruencia en las fechas. La fecha de inicio del intervalo no puede ser anterior al cierre"
                );
            return false;
        }

        var url = 'ltraza.php?inicio=' + encodeURIComponent(inicio) + '&fin=' + encodeURIComponent(fin) + '&action=' +
            action;
        url += "&id_usuario=" + id_usuario;
        self.location = url;
    }

    function filtrar() {
        displayFloatingDiv('div-ajax-panel-filter', "FILTRADO DE REGISTROS", 50, 0, 10, 15);
    }

    function cerrar() {
        CloseWindow('div-ajax-panel');
        refreshp();
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_opr(id) {
        $('#action').val(id);
        refreshp();
    }

    $(document).ready(function() {
        InitDragDrop();

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$init_year?>',
            maxDate: '31/12/<?=$end_year?>',
            autoclose: true,
            inline: true
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$init_year?>',
            maxDate: '31/12/<?=$end_year?>',
            autoclose: true,
            inline: true
        });

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>  

                <a href="#" class="navd-header">
                    LISTADO DE ENTRADAS E IMPRESIONES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a href="#" onclick="filtrar()">
                                <i class="fa fa-filter"></i>Filtrar
                            </a>
                        </li>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-cogs"></i>actiones<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <?php
                                    foreach ($array_actions as $key => $opr) {
                                        if (empty($action))
                                            $action= $key;
                                    ?>
                                <li class="nav-item">
                                    <a href="#" class="<?php if ($actions == $key) echo "active"?>"
                                        onclick="_dropdown_opr('<?=$key?>')" title="<?=$opr[2]?>">
                                        <?=$opr[1]?>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>

                            <input type="hidden" id="action" name="action" value="<?=$action?>" />
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/manual.htm#11_13.2')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>    
        </nav>
    </div>


    <div id="navbar-third" class="row app-nav d-none d-md-block d-none d-lg-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$inicio?>, <?=$fin?>
                </label>
            </li>

            <li class="col">
                <div class="col-sm-12">
                    <label class="badge badge-warning">
                        <?=!empty($action) ? $array_actiones[$action][1] : "Todas las actiones ... "?>
                    </label>
                </div>

            </li>
        </ul>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
        <input type="hidden" name="menu" id="menu" value="evento" />

        <div class="app-body container-fluid table threebar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true" data-row-style="rowStyle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ACCIÓN</th>
                        <th>USUARIO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>PROCESOS</th>
                        <th>OBSERVACIÓN</th>
                        <th>FECHA Y HORA</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                            $i = 0;
                            $_id = array(null);

                            foreach ($result as $key => $row) {
                            ?>
                    <tr>
                        <td><?=++$j?></td>
                        <td>
                            <?= $action ?>
                        </td>
                        <td>
                            <?=$row['_nombre']?>
                        </td>
                        <td>
                            <?= $row['_descripcion'] ?>
                        </td>
                        <td>
                            <?=$row['_proceso']?>
                        </td>
                        <td>
                            <?= $row['_observacion'] ?>
                        </td>
                        <td>
                            <?=odbc2time_ampm($row['_cronos'])?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- div-ajax-panel -->
    <div id="div-ajax-panel-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-10">FILTRADO DE REGISTROS</div>
                <div class="col-1pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-ajax-panel-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="container-fluid form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-sm-2">Desde:</label>
                    <div class="col-md-4">
                        <div id="div_fecha_inicio" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control input-sm" id="fecha_inicio" name="fecha_inicio"
                                readonly value="<?= $inicio?>">
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <label class="col-form-label col-sm-2">Hasta:</label>
                    <div class="col-md-4">
                        <div id="div_fecha_fin" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control input-sm" id="fecha_fin" name="fecha_fin"
                                readonly value="<?= $fin?>">
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>

                    <br />
                    <div class="form-group row" style="margin-top:40px;">
                        <label class="col-form-label col-2">Usuario:</label>

                        <div class="col-md-10 col-lg-10">
                            <select id="usuario" name="usuario" class="form-control input-sm">
                                <option value="0">... </option>
                                <?php
                                    $clink->data_seek($result_user);
                                    while ($row_user = $clink->fetch_array($result_user)) {
                                        if (empty($row_user['nombre']))
                                            continue;
                                        if ($id_proceso == $_SESSION['id_entity'] && array_key_exists($row_user['id_proceso'], $array_procesos_up))
                                            continue;

                                        $name= $row_user['nombre'];
                                        if (!empty($row_user['cargo']))
                                            $name.= ", ".textparse($row_user['cargo']);
                                        ?>
                                <option value="<?= $row_user['_id'] ?>"
                                    <?php if ($row_user['_id'] == $id_usuario) echo "selected" ?>><?= $name ?> </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="btn" onclick="refreshp()">Filtrar</button>
                    <button class="btn btn-warning" type="reset"
                        onclick="CloseWindow('div-ajax-panel-filter');">Cerrar</button>
                </div>
            </div>

        </div>
    </div> <!-- div-ajax-panel -->
</body>

</html>