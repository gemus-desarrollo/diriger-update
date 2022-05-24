<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";

require_once "../php/class/code.class.php";

$time= new TTime();

$action= 'edit';

require_once "../php/inc_escenario_init.php";

$time= new TTime();

$actual_year= $time->GetYear();
$actual_month= $time->GetMonth();
$actual_day= $time->GetDay();
$lastday= $time->longmonth();


$inicio= $actual_year - 2;
$fin= $actual_year + 3;

$_SESSION['current_month']= $month;
$_SESSION['current_year']= $year;

if (!empty($_GET['id_calendar'])) $id_calendar= $_GET['id_calendar'];
if (empty($id_calendar)) $id_calendar= $_SESSION['id_calendar'];
if (empty($id_calendar)) $id_calendar= $_SESSION['id_usuario'];
$_SESSION['id_calendar']= $id_calendar;

if (empty($hh)) $hh= $time->GetHour();
if (empty($mi)) $mi= $time->GetMinute();

$obj_user= new Tusuario($clink);

$obj= new Tplantrab($clink);

$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetIdUsuario($id_calendar);
$obj->SetIfEmpresarial(0);
$obj->SetIdProceso(null);

$id_plan= $obj->Set();

if (empty($id_plan)) {
    $obj_code= new Tcode($clink);
    $id_proceso_code= $obj_code->get_code_from_table('tprocesos', $id_proceso);

    $obj->set_id_proceso_code($id_proceso_code);
	$obj->SetIdResponsable($_SESSION['id_usuario']);
    $id_plan= $obj->add_plan();
}

$obj_pry= new Tproyecto($clink);

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : 0;

if (empty($hh)) $hh= $time->GetHour();
if (empty($mi)) $mi= $time->GetMinute();

$obj_user= new Tusuario($clink);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>RESUMEN DE TRABAJO</title>

        <?php require '../form/inc/_page_init.inc.php'; ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../css/general.css?version=">
        <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css?version=">

        <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css?version=">
        <script type="text/javascript" src="../js/widget.js?version=" charset="utf-8"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../css/tablero.css?version="  />
        <script type="text/javascript" src="../js/tablero.js?version="></script>
        <link rel="stylesheet" type="text/css" media="screen" href="../css/scheduler.css?version=" />

        <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

        <script type="text/javascript" src="../js/windowcontent.js?version="></script>

        <script type="text/javascript" src="../js/ajax_core.js?version="></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

        <script type="text/javascript" charset="utf-8" src="../js/form.js?version="></script>

        <style type="text/css">
            .app-body.container-fluid {
                align-content: center!important;
                align-items: center!important;
                padding: 3px 15px 10px 10px;
            }
            .table-resume {
                background: #fff;
                align-content: center;
                margin-left: 10%;
            }
            .table-resume th {
                border: 1px solid #000;
                padding: 6px 10px;
                text-align: center;
            }
            .table-resume td {
                border: 1px solid #000!important;
                border-left: none!important;
                padding: 6px 10px;
                border: none;
                text-align: center;
            }
            .table-resume:last-of-type tr {
                border: 1px solid #000;
                border-left: none;
                border-right: none;
            }
            .alert {
                margin: 15px 10px 2px 10px;
            }
        </style>

        <script language="javascript" type="text/javascript">
            function imprimir() {
               var id_proceso= $('#proceso').val();
               var month= $('#month').val();
               var year= $('#year').val();
               var id_calendar= $('#id_calendar').val();

                var url= '../print/resume_work.php?id_proceso='+id_proceso+'&id_calendar='+id_calendar+'&month=' + month + '&year=' + year;
                prnpage= show_imprimir(url,"IMPRIMIENDO RESUMEN DEL PLAN DE TRAABAJO","width=900,height=600,toolbar=no,location=no, scrollbars=yes");
           }


            function refreshp() {
               var id_proceso= $('#proceso').val();
               var month= $('#month').val();
               var year= $('#year').val();
               var id_calendar= $('#id_calendar').val();

               var url= 'resume_work.php?id_proceso='+id_proceso+'&&id_calendar=' + id_calendar + '&month=' + month + '&year=' + year;;
                self.location= url;
            }
        </script>

        <script type="text/javascript">
            function _dropdown_year(year) {
                $('#year').val(year);
                refreshp();
            }
            function _dropdown_month(month) {
                $('#month').val(month);
                refreshp();
            }

            $(document).ready(function() {
                resizeBody();
                InitDragDrop();
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="page-header" class="app-nav navbar-fixed-top bs-docs-nav">
        <div class="container-fluid">
            <div class="navbar-header">
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#first-navbar">
                    <span class="sr-only">Opciones</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <a href="#" class="navbar-brand navbar-left">REPORTE DE INCUMPLIMIENTOS</a>
            </div>

            <div id="first-navbar" class="sub-navbar navbar-collapse collapse" role="navegation">
                <ul class="nav navbar-nav">
                    <li>
                        <a class="icon <?php if ($id_calendar == -1) echo "active" ?>" href="#" title="Relación de Indicadores Desactualizados" onclick="load_resume_work(-1)">
                            <img src="../img/indicator.ico" />
                            Indicadores
                        </a>
                    </li>
                    <li>
                        <a class="icon <?php if ($id_calendar == -2) echo "active" ?>" href="#" title="Relación de actividades y tareas Desactualizadas" onclick="load_resume_work(-2)">
                            <img src="../img/task.ico" />
                            Actividades y tareas
                        </a>
                    </li>
                    <li class="navd-dropdown">
                        <a href="#" class="icon dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="../img/user.ico" />
                            Usuarios <span class="caret"></span>
                        </a>

                        <?php $email= $obj_user->GetEmail($_SESSION['id_usuario']); ?>
                        <ul class="dropdown-menu">
                            <li class="<?php if ($id_calendar == $_SESSION['id_usuario']) echo "active"?>">
                                <a href="#" title="<?= $email['cargo'] ?>" onclick="load_resume_work(<?=$_SESSION['id_usuario']?>)">
                                    <?=$_SESSION['nombre']?>
                                </a>
                            </li>

                            <?php
                            $obj_gantt = new Torgtarea($clink);
                            $obj_gantt->SetIdResponsable($_SESSION['id_usuario']);
                            $obj_gantt->get_subordinados_array();

                            $pos = 0;
                            $j = 0;
                            if (is_array($obj_gantt->array_usuarios)) {
                                foreach ($obj_gantt->array_usuarios as $array) {
                                    $id= $array['id'];
                                    if ($id == $_SESSION['id_usuario']) continue;
                                    if (empty($id_calendar)) $id_calendar= $id;
                                    ++$j;

                                    $str = str_replace("\n", "", nl2br($array['cargo']));
                                    $str = "<b>CARGO:</b> " . str_replace("\r", "", $str);
                                    ?>
                                    <li class="<?php if ($id_calendar == $id) echo "active"?>">
                                        <a href="#" title="<?=$str?>" onclick="load_resume_work(<?=$id?>)">
                                           <?=$array['nombre']?>
                                       </a>
                                   </li>
                                <?php
                                }
                            }

                            $_SESSION['id_calendar'] = $id_calendar;
                            ?>

                        </ul>
                    </li>

                    <li class="navd-dropdown">
                        <a href="#" class="icon dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i>
                            Opciones <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu">
                        <?php if (!$print_reject) { ?>
                             <li>
                                 <a class="icon" href="javascript:ver()" title="mostrar los controles rechazados">
                                     <i class="fa fa-eye"></i>
                                     Mostrar
                                 </a>
                             </li>
                         <?php } else { ?>
                             <li>
                                 <a class="icon" href="javascript:ver()" title="ocultar los controles rechazadas">
                                     <i class="fa fa-eye-slash"></i>
                                     Ocultar
                                 </a>
                             </li>
                         <?php } ?>
                        </ul>
                    </li>

                    <?php
                    $use_select_year= true;
                    $use_select_month= true;
                    $use_select_day= false;
                    include "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <li>
                        <a id="open" class="icon d-none d-lg-block" href="#" onclick="imprimir()">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li>
                       <a class="icon" href="open_help_window('<?=$help?>')">
                           <i class="fa fa-question-circle"></i>
                           Ayuda
                       </a>
                   </li>
                </ul>
            </div>

            <div id="sub-navbar-page" class="sub-navbar two navbar-collapse collapse">

                <ul class="nav nav-justified">
                   <li>
                       <label class="label label-success">
                           <?=$date?>
                       </label>
                   </li>
                   <li>
                       <label class="label col-sm-7">Muestra:</label>
                       <div id="nshow" class="label label-warning col-sm-4"></div>
                   </li>
                   <li>
                       <label class="label col-sm-7">Ocultas:</label>
                       <div id="nhide" class="label label-warning col-sm-4"></div>
                   </li>
                   <li>
                       <label class="label label-danger">
                           <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i class="fa fa-wifi"></i><?php } ?>
                           <?=$nombre?>
                       </label>
                   </li>
                   <li>
                       <label class="label col-sm-2">Filtrado:</label>
                       <?php if (!empty($capitulo) || !empty($id_tipo_evento) || (!empty($like_name) && is_string($like_name))) { ?>
                       <div class="label label-warning col-sm-9 col-sm-offset-1">
                            <?php
                           if ($tipo_plan == _PLAN_TIPO_AUDITORIA || $tipo_plan == _PLAN_TIPO_SUPERVICION) {
                               if (!empty($origen)) echo "{$Ttipo_nota_origen_array[(int)$origen]} / {$Ttipo_auditoria_array[(int)$tipo]}";
                               if (!empty($organismo)) echo " / {$organismo}";
                           } else {
                               if (!empty($capitulo)) echo "Capítulo: <span class=nshow>".number_format_to_roman($capitulo)."</span>";
                               if (!empty($id_tipo_evento)) {
                                   $obj_tipo= new Ttipo_evento($clink);
                                   $obj_tipo->Set($id_tipo_evento);
                                   $numero= $obj_tipo->GetNumero();

                                   echo "Clasificación: <span class=nshow>{$numero}</span>";
                               }
                               if (!empty($like_name) && is_string($like_name)) echo "  Contiene el texto: <span class=nshow>{$like_name}</span>";
                           }
                           ?>
                       </div>
                       <?php } ?>
                   </li>
                    <li class="navd-dropdown">
                        <a href="#" class="menu dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="../img/user-add.ico" />
                            Estado del Plan <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                           <li>
                               <label class="lable col-sm-3">Aprobado por:</label>
                               <div id="label-aprobado" class="label label-danger col-sm-6"></div>
                               <div id="label-aprobado-date" class="label label-danger col-sm-6"></div>
                           </li>
                           <li>
                               <label>Autoevaluado por:</label>
                               <div id="label-autoevaluado" class="label label-danger col-md-3"></div>
                               <div id="label-autoevaluado-date" class="label label-danger col-md-3"></div>
                           </li>
                           <li>
                               <label class="col-md-6">Evaluado por:</label>
                               <div id="label-evaluado" class="col-md-6"></div>
                               <div id="label-evaluado-date" class="label label-danger col-md-3"></div>
                               <div id="label-evaluado-val" class="label label-danger col-md-3"></div>
                           </li>
                       </ul>
                   </li>
               </ul>
            </div>
        </div>
    </div>




<input id=proceso type="hidden" value="<?=$id_proceso?>" />
<input type="hidden" id="day" value="<?=$day?>" />
<input type="hidden" id="id_calendar" value="<?=$id_calendar?>" />

    <?php
    $date_auto_eval= $obj->GetAutoEvaluado();
    $array_auto_eval= $obj_user->GetEmail($obj->GetIdResponsable_auto_eval());

    $date_aprb= $obj->GetAprobado();
    $array_aprb= $obj_user->GetEmail($obj->GetIdResponsable_aprb());

    $date_eval= $obj->GetEvaluado();
    $array_eval= $obj_user->GetEmail($obj->GetIdResponsable_eval());
    $cumplimiento= $obj->GetCumplimiento();
    ?>

    <script type="text/javascript">
        $('#label-aprobado').val("<?php if (!is_null($array_aprb)) echo $array_aprb['nombre'].' ('.$array_aprb['cargo'].')'?>");
        $('#label-aprobado-date').val("<?=odbc2time_ampm($date_aprb)?>");
        $('#label-autoevaluado').val("<?php if (!is_null($array_auto_eval)) echo $array_auto_eval['nombre'].' ('.$array_auto_eval['cargo'].')' ?>");
        $("#label-autoevaluado-date").val(<?=odbc2time_ampm($date_auto_eval)?>);
        $('#label-evaluado').val("<?php if (!is_null($array_auto_eval)) echo $array_eval['nombre'].' ('.$array_eval['cargo'].')' ?>");
        $('#label-evaluado-date').val("<?=odbc2time_ampm($date_eval)?>");
        $('#label-evaluado-val').val("<?php if (!empty($date_eval)) echo $evaluacion_array[$cumplimiento] ?>");
    </script>

    <div class="app-body container-fluid fivebar">

        <?php
        $obj->set_print_reject($print_reject);
        $obj->list_reg();
        ?>

        <div class="alert alert-info">
            RESUMEN DEL PLAN DE TRABAJO - <?= "DEL MES DE " . strtoupper($meses_array[(int) $month]) . ' A&Ntilde;O ' . $year ?>
        </div>

        <table class="table-resume">
            <thead>
                <tr>
                    <th colspan="7" style="border-left:none; border-right: none;">
                        TOTAL DE TAREAS <u> <?= $obj->total ?> </u>&nbsp; DE ELLAS <u> <?= $obj->extras ?> </u>&nbsp; NUEVAS TAREAS EXTRAPLAN
                    </th>
                </tr>
                <tr>
                    <th style="border-left:none">TOTAL DE TAREAS</th>
                    <th>PLANIFICADAS</th>
                    <th>CUMPLIDAS</th>
                    <th>INCUMPLIDAS</th>
                    <th>MODIFICADAS</th>
                    <th>EXTRA-PLAN</th>
                    <th style="border-right: none">CANCELADAS O POSPUESTAS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border-left:none"><?= $obj->total ?> </td>
                    <td><?= ($obj->total - $obj->extras) ?></td>
                    <td><?= $obj->cumplidas ?></td>
                    <td><?= $obj->incumplidas ?></td>
                    <td><?= $obj->modificadas ?></td>
                    <td><?= $obj->extras ?></td>
                    <td style="border-right: none!important;"><?= $obj->canceladas ?></td>
                </tr>
            </tbody>
        </table>


        <div class="alert alert-danger">RELACIÓN DE TAREAS INCUMPLIDAS</div>

        <table id="table" class="table table-hover table-striped"
               data-toggle="table"
               data-height="300"
               data-pagination="true"
               data-search="true"
                data-show-columns="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ACTIVIDAD O TAREA</th>
                    <th>DESCRIPCIÓN</th>
                    <th>FECHA PLANIFICADA</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if (is_array($obj->incumplidas_list)) {
                    foreach ($obj->incumplidas_list as $array) {
                ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><?= $array['evento'] ?></td>
                            <td><?= $array['descripcion'] ?></td>
                            <td><?= odbc2date($array['plan']) ?></td>
                            <td><?= $array['observacion'] ?></td>
                        </tr>
                <?php } } ?>
            </tbody>
        </table>

        <div class="alert alert-warning">RELACIÓN DE TAREAS MODIFICADAS</div>

        <table id="table" class="table table-hover table-striped"
               data-toggle="table"
               data-height="300"
               data-pagination="true"
               data-search="true"
                data-show-columns="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ACTIVIDAD O TAREA</th>
                    <th>DESCRIPCIÓN</th>
                    <th>FECHA INICIAL</th>
                    <th>NUEVA FECHA</th>
                    <th>MODIFICADA POR</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if (is_array($obj->modificadas_list)) {
                    foreach ($obj->modificadas_list as $array) {
                ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><?= $array['evento']; ?></td>
                            <td><?= nl2br($array['descripcion']); ?></td>
                            <td><?= odbc2date($array['plan']) ?></td>
                            <td>&nbsp;</td>
                            <td>
                                <?php
                                $id_user = $array['id_responsable'];

                                if (!empty($id_user)) {
                                    $email = $obj_user->GetEmail($id_user);
                                    echo $email['nombre'] . ", " . textparse($email['cargo']);
                                }
                                ?>
                            </td>
                            <td><?= nl2br($array['observacion']) ?></td>
                        </tr>
                <?php } } ?>
            </tbody>
        </table>


        <div class="alert alert-info">RELACIÓN DE TAREAS NUEVAS EXTRA PLAN</div>

        <table id="table" class="table table-hover table-striped"
               data-toggle="table"
               data-height="300"
               data-pagination="true"
               data-search="true"
                data-show-columns="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ACTIVIDAD O TAREA</th>
                    <th>DESCRIPCIÓN</th>
                    <th>FECHA PLANIFICADA</th>
                    <th>ASIGNADA POR</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                if (is_array($obj->extras_list)) {
                    foreach ($obj->extras_list as $array) {
                ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><?= $array['evento']; ?></td>
                            <td><?= nl2br($array['descripcion']); ?></td>
                            <td><?= odbc2date($array['plan']) ?></td>
                            <td>
                                <?php
                                $id_user = $array['id_responsable'];

                                if (!empty($id_user)) {
                                    $email = $obj_user->GetEmail($id_user);
                                    echo $email['nombre'] . ", " . textparse($email['cargo']);
                                }
                                ?>
                            </td>
                            <td><?= nl2br($array['observacion']) ?></td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>



            <div class="alert alert-danger">RELACIÓN DE TAREAS CANCELADAS O POSPUESTAS</div>

            <table id="table" class="table table-hover table-striped"
                   data-toggle="table"
                   data-height="300"
                   data-pagination="true"
                   data-search="true"
                    data-show-columns="true">
                <thead>
                   <tr>
                       <th>No.</th>
                       <th>ACTIVIDAD O TAREA</th>
                       <th>DESCRIPCIÓN</th>
                       <th>FECHA PLANIFICADA</th>
                       <th>CANCELADA POR</th>
                       <th>OBSERVACIÓN</th>
                   </tr>
               </thead>
                <tbody>
                    <?php
                    $i = 0;
                    if (is_array($obj->canceladas_list)) {
                        foreach ($obj->canceladas_list as $array) {
                    ?>
                            <tr>
                                <td><?= ++$i ?></td>
                                <td><?= $array['evento'] ?></td>
                                <td><?= nl2br($array['descripcion']) ?></td>
                                <td><?= odbc2date($array['plan']) ?></td>
                                <td>
                                    <?php
                                    $id_user = $array['id_responsable'];

                                    if (!empty($id_user)) {
                                        $email = $obj_user->GetEmail($id_user);
                                        echo $email['nombre'] . ", " . textparse($email['cargo']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo nl2br($array['observacion']) ?></td>
                            </tr>
                    <?php } } ?>
                </tbody>
            </table>

            <br/><br/>

            <div id="div-ajax-panel" class="ajax-panel">

            </div>
        </div>
    </body>
</html>