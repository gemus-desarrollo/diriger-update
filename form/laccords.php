<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/asistencia.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../form/class/tarea.signal.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if ($action == 'add')
    $action= 'edit';
if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$tipo_reunion= !empty($_GET['tipo_reunion']) ? $_GET['tipo_reunion'] : null;
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$obj= new Ttematica($clink);

$obj->SetIdEvento($id_evento);
$obj->SetIdProceso($id_proceso);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$obj->SetMonth(null);
$obj->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_event= new Tevento($clink);
$obj_meeting= new Ttipo_reunion($clink);
$obj_assist= new Tasistencia($clink);
$obj_signal= new Ttarea_signals();

require_once "../php/config.inc.php";

$url_page= "../form/laccords.php?signal=$signal&action=$action&menu=grupo&id_proceso=$id_proceso";
$url_page.= "&exect=$action&year=$year&month=$month&day=$day";

set_page($url_page);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>LISTADO DE ACUERDOS TOMADOS</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link rel="stylesheet" type="text/css" href="../css/table.css" />
        <link rel="stylesheet" type="text/css" href="../css/custom.css">

        <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
        <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

        <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
        <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
        <script type="text/javascript" src="../js/widget.js"></script>

        <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <script language="javascript">
            function refreshp() {
                var id_proceso= $('#proceso').val();
                var year= $('#year').val();
                var tipo_reunion= $('#tipo_reunion').val();
                var cumplimiento= $('#cumplimiento').val();

                var url= '../form/laccords.php?&action=<?=$action?>&id_proceso='+id_proceso;
                url+= '&year='+year+'&tipo_reunion='+tipo_reunion+'&cumplimiento='+cumplimiento;

                self.location.href= url;
            }

            function closep() {
                var id_proceso= $('#proceso').val();
                var year= $('#year').val();
                self.location.href= '../html/tablero_planning.php?signal=anual_plan_meeting&action=<?=$action?>&id_proceso='+id_proceso+'&year='+year;
            }

            function show_filter() {
                var w= 50;
                displayFloatingDiv('div-filter', false, w, 0, 10, 15);
            }

            function imprimir(index) {
                var id_proceso= $('#proceso').val();
                var year= $('#year').val();
                var tipo_reunion= $('#tipo_reunion').val();
                var cumplimiento= $('#cumplimiento').val();
                var url

                url= index == 1 ? "../print/laccords.php?" : "../print/resumen_accords.php?";
                url+= '&id_proceso='+id_proceso+'&year='+year+'&tipo_reunion='+tipo_reunion+'&cumplimiento='+cumplimiento;

                show_imprimir(url,"LISTADO DE ACUERDOS","width=750,height=500,toolbar=no,location=no, scrollbars=yes");
            }

        </script>

        <script type="text/javascript" charset="utf-8">
            $(document).ready( function () {
                <?php if (!is_null($error)) { ?>
                    alert("<?= str_replace("\n", " ", $error) ?>");
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
                        LISTADO DE ACUERDOS TOMADOS
                    </a>

                    <div class="navd-menu" id="navbarSecondary">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" class="" onclick="show_filter()">
                                    <i class="fa fa-filter"></i>Filtrar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="" onclick="imprimir(2)">
                                    <i class="fa fa-bar-chart-o"></i>Resumen
                                </a>
                            </li>
                            <li class="nav-item d-none d-lg-block">
                                <a href="#" class="" onclick="imprimir(1)">
                                    <i class="fa fa-print"></i>Imprimir
                                </a>
                            </li>
                        </ul>

                        <div class="navd-end">
                            <ul class="navbar-nav mr-auto"> 
                                <li class="nav-item">
                                    <a href="#" onclick="open_help_window('../help/02_usuarios.htm#02_4.3')">
                                        <i class="fa fa-question"></i>Ayuda
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="#" onclick="closep()">
                                        <i class="fa fa-close"></i>Cerrar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>  
                </div>      
            </nav>
        </div>


        <form action='javascript:' method=post>
            <input type="hidden" name="exect" id="exect" value='' />
            <input type="hidden" name="menu" id="menu" value="grupo" />
            <input type="hidden" name="proceso" id="proceso" value="<?=$id_proceso?>" />
            <input type="hidden" name="year" id="year" value="<?=$year?>" />

            <div class="app-body container table onebar">
                <table id="table" class="table table-striped"
                       data-toggle="table"
                       data-search="true"
                       data-show-columns="true">
                    <thead>
                        <th class="plhead left">No.</th>
                        <th class="plhead">ACUERDO</th>
                        <th class="plhead" width="200px">REGISTRO</th>
                        <th class="plhead" width="100px">FECHA/HORA<br/>CUMPLIMIENTO</th>
                        <th class="plhead" width="200px">RESPONSABLE</th>
                        <th class="plhead" width="200px">PARTICIPANTES</th>
                        <th class="plhead">OBSERVACIONES SOBRE EL CUMPLIMIENTO</th>
                    </thead>

                    <tbody>
                    <?php
                    $result= $obj->listar_all_accords($year, true, true);

                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                        $obj_event->Set($row['id_evento']);
                        if (!empty($tipo_reunion) && $tipo_reunion != $obj_event->GetIdTipo_reunion())
                            continue;

                        $obj_assist->Set($row['id_asistencia_resp']);
                        $id_responsable= $obj_assist->GetIdUsuario();

                        if (isset($obj_reg)) unset ($obj_reg);
                        $obj_reg= new Tregister_planning($clink);
                        $obj_reg->SetIdEvento($row['id_evento_accords']);
                        $obj_reg->SetYear(date('Y', strtotime($row['fecha_inicio_plan'])));
                        $row_reg= $obj_reg->getEvento_reg(null, array('id_responsable'=>$id_responsable));

                        if (!empty($cumplimiento) && $cumplimiento != $row_reg['cumplimiento'])
                            continue;
                    ?>
                        <tr>
                            <td><?=$row['numero']?></td>
                            <td>
                                <?= $row['observacion'] ?>
                            </td>
                            <td>
                                <?php
                                echo $obj_event->GetNombre();
                                echo "<p>".odbc2time_ampm($row['cronos'])."</p>";
                                ?>
                            </td>
                            <td>
                                <?=odbc2time_ampm($row['fecha_inicio_plan'])?>
                            </td>
                            <td>
                                <?php
                                $nombre= $obj_assist->GetNombre();
                                $cargo= $obj_assist->GetCargo();

                                if (empty($nombre)) {
                                    $email= $obj_user->GetEmail($id_responsable);
                                    $nombre= $email['nombre'];
                                    $cargo= $email['cargo'];
                                }

                                $email = $obj_user->GetEmail($row['id_responsable']);
                                if ($config->onlypost)
                                    echo !empty($cargo) ? $cargo : $nombre;
                                else
                                    echo $nombre.(!empty($cargo) ? " ($cargo)" : null);
                                ?>
                            </td>
                            <td>
                                <?php
                                $obj_event->SetYear(date('Y', strtotime($row['fecha_inicio_plan'])));
                                $obj_event->SetIdEvento($row['id_evento_accords']);
                                echo $obj_event->get_participantes();
                                ?>
                            </td>
                            <td>
                                <?php
                                $alarm= $obj_signal->get_alarm($row_reg);
                                ?>
                                <div class="alert bg-<?=$alarm['class']?> small" style="max-width: 200px;"><?=$eventos_cump[(int)$row_reg['cumplimiento']]?></div>
                                <?=$row_reg['observacion']?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-md-10">FILTRADO DE ACUERDOS</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href= "javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-md-2">
                        Reunión:
                    </label>
                    <div class="col-md-10 col-lg-10">
                        <?php
                        $obj_meeting->SetYear($year);
                        $obj_meeting->SetIdProceso($id_proceso);
                        $result= $obj_meeting->listar();
                        ?>

                        <select id="tipo_reunion" name="tipo_reunion" class="form-control">
                            <option value="0">... </option>
                            <?php while ($row= $clink->fetch_array($result)) { ?>
                            <option value="<?=$row['id']?>" <?php if (!empty($tipo_reunion) && $tipo_reunion == $row['id']) echo "selected='selected'" ?>><?=$row['nombre']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Estado:
                    </label>
                    <div class="col-md-5 col-lg-5">
                        <select id="cumplimiento" name="cumplimiento" class="form-control">
                            <?php
                            $i= 0;
                            foreach ($eventos_cump as $name) { ?>
                            <option value="<?=$i?>" <?php if (!empty($cumplimiento) && $cumplimiento == $i) echo "selected='selected'" ?>><?=$name?></option>
                            <?php ++$i; } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="refreshp()">Aceptar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
            </div>
        </div>
    </div>
            </div>
        </form>
    </body>
</html>
