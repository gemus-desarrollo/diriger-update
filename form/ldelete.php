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

$obj= new Tcode($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new TTime();
$time->splitTime();
$year= $time->GetYear();
$month= (int)$time->GetMonth();
$lastday= $time->longmonth();

$inicio= !empty($_GET['inicio']) ? urldecode($_GET['inicio']) : "1/".$month."/".$year;
$fin= !empty($_GET['fin']) ? urldecode($_GET['fin']) : $lastday."/".$month."/".$year;
$operacion= !empty($_GET['operacion']) ? $_GET['operacion'] : 'evento';

$obj->SetFechaInicioPlan(time2odbc($inicio. " 00:00:00"));
$obj->SetFechaFinPlan(time2odbc($fin. " 23:59:00"));

$array_operaciones= array(
    'evento' => array('evento', 'Actividades o eventos', 'Actividades o eventos eliminadas'),
    'tarea' => array('tarea', 'Tareas', 'Tareas eliminadas o desvinculadas de Riesgos, Notas de Hallazgos y Proyectos'),
    'riesgo' => array('riesgo', 'Riesgos', 'Riesgos eliminados'),
    'nota' => array('nota', 'Notas', 'Notas de Hallazgos eliminadas'),
    'proyecto' => array('proyecto', 'Proyectos', 'Proyectos Eliminados'),

    'objetivo' => array('objetivo', 'Objetivos Estratégigos o de Control Interno', 'Objetivos Estratégigos o de Control Interno Eliminados'),
    'perspectiva' => array('perspectiva', 'Perspectivas', 'Perspectivas Eliminadas'),
    'inductor' => array('inductor', 'Objetivos de Trabajo', 'Objetivos de Trabajo Eliminados'),
    'programa' => array('programa', 'Programas', 'Programas Eliminados'),
    'indicador' => array('indicador', 'Indicadores', 'Indicadores Eliminados')
);

$array_tablas= array(
    'evento' => array("'teventos'", "'treg_evento'", "'tusuario_eventos'", "'tproceso_eventos'"),
    'tarea' => array("'ttareas'", "'triesgo_tareas'"),
    'riesgo' => array("'triesgos'", "'tproceso_riesgos'"),
    'nota' => array("'tnotas'"),
    'proyecto' => array("'tproyectos'", "'tproceso_proyectos'"),

    'objetivo' => array("'tobjetivos'", "'tproceso_objetivos'"),
    'perspectiva' => array("'tperspectiva'"),
    'inductor' => array("'tinductores'"),
    'programa' => array("'tprogramas'", "'tproceso_programas'"),
    'indicador' => array("'tindicadores'", "'tproceso_indicadores'")
);

$array= !empty($operacion) ? $array_tablas[$operacion] : null;

$result= $obj->listar($array);

$obj_user= new Tusuario($clink);
$obj_prs= new Tproceso($clink);

$url_page= "../form/ldelete.php?signal=$signal&action=$action&menu=evento&operacion=$operacion";
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

    <title>LISTADO DE OPERACIONES DE BORRADO</title>

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
        var operacion = $('#operacion').val();

        if (DiferenciaFechas(fin, inicio, 'd') < 0) {
            alert(
                "Existe incongruencia en las fechas. La fecha de inicio del intervalo no puede ser anterior al cierre");
            return false;
        }

        var url = 'ldelete.php?inicio=' + encodeURIComponent(inicio) + '&fin=' + encodeURIComponent(fin) +
            '&operacion=' + operacion;

        self.location = url;
    }

    function filtrar() {
        displayFloatingDiv('div-ajax-panel-filter', "ESTADO DE EJECUCIÓN DEL ACTIVIDAD", 50, 0, 10, 15);
    }

    function enviar_evento(id, action) {
        var _action = action;

        function _this() {
            parent.app_menu_functions = false;

            document.forms[0].exect.value = _action;
            document.forms[0].action = '../php/evento.interface.php?id=' + id + '&action=' + _action;
            document.forms[0].submit();
        }

        var msg =
            "IMPORTANTE!! La actividad será eliminado y a igual que toda referencia al mismo en los calendarios de todos los participantes. Desea continuar?";

        if (_action == 'delete') {
            confirm(msg, function(ok) {
                if (!ok) return;
                else _this();
            });
        } else {
            _this();
        }
    }

    function cerrar() {
        CloseWindow('div-ajax-panel');
        refreshp();
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_opr(id) {
        $('#operacion').val(id);
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
                    LISTADO DE OPERACIONES DE BORRADO
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <a href="#" onclick="filtrar()">
                                <i class="fa fa-filter"></i>Intervalo
                            </a>
                        </li>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-cogs"></i>Operaciones<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <?php
                                foreach ($array_operaciones as $key => $opr) {
                                    if (empty($operacion))
                                        $operacion= $key;
                                ?>
                                <li>
                                    <a href="#" class="<?php if ($operaciones == $key) echo "active"?>"
                                        onclick="_dropdown_opr('<?=$key?>')" title="<?=$opr[2]?>">
                                        <?=$opr[1]?>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>

                            <input type="hidden" id="operacion" name="operacion" value="<?=$operacion?>" />
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li>
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

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$inicio?>, <?=$fin?>
                </label>
            </li>

            <li class="col">
                <div class="col-sm-12">
                    <label class="badge badge-warning">
                        <?=!empty($operacion) ? $array_operaciones[$operacion][1] : "Todas las Operaciones ... "?>
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
                        <th>BORRADO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>USUARIO</th>
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
                            <?= $operacion ?>
                            <?php
                                        if (!empty($row['id_usuario'])) {
                                            $email= $obj_user->GetEmail($row['id_usuario']);
                                            echo "<br/>A USUARIO: {$email['nombre']}";
                                            echo !empty($email['cargo']) ? ", ".textparse($email['cargo']) : "";
                                        }
                                        ?>
                            <?php
                                        if (!empty($row['id_proceso'])) {
                                            $obj_prs->SetIdProceso(null);
                                            $obj_prs->Set(null, $row['id_proceso']);
                                            echo "<br/>A PROCESO: ".$obj_prs->GetNombre();
                                        }
                                        ?>
                        </td>
                        <td>
                            <?= $row['observacion'] ?>
                        </td>
                        <td>
                            <?php
                                        $email= $obj_user->GetEmail($row['id_responsable']);
                                        echo $email['nombre'];
                                        if (!empty($email['cargo']))
                                            echo "<br /> ".textparse($email['cargo']);
                                        ?>
                        </td>
                        <td>
                            <?=odbc2time_ampm($row['cronos'])?>
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
                <div class="panel-title win-drag col-md-10">FILTRADO DE LOS EVENTOS</div>
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
                    <label class="col-form-label col-md-2">Hasta:</label>
                    <div class="col-md-4">
                        <div id="div_fecha_fin" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control input-sm" id="fecha_fin" name="fecha_fin"
                                readonly value="<?= $fin?>">
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
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