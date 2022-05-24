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
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";

require_once "../php/class/traza.class.php";

$obj= new Ttarea($clink);

if (!empty($_GET['id_proceso'])) 
    $id_proceso= $_GET['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];
$planning= !is_null($_GET['planning']) ? $_GET['planning'] : 1;

$time= new TTime();
$year= $time->GetYear();
$month= $time->GetMonth();
$lastday= $time->longmonth();

if (!empty($_GET['fecha_inicio'])) 
    $fecha_inicio= urldecode($_GET['fecha_inicio']);
else 
    $fecha_inicio= "1/".$month."/".$year;

if (!empty($_GET['fecha_final'])) 
    $fecha_final= urldecode($_GET['fecha_final']);
else 
    $fecha_final= $lastday."/".$month."/".$year;

//temporal
$id_usuario= $_SESSION['id_usuario'];
$id_responsable= $_SESSION['id_usuario'];

$obj->SetIdUsuario($id_usuario);
$obj->SetIdResponsable($id_responsable);

$obj_user= new Tusuario($clink);
$obj_reg= new Tregister_planning($clink);
$obj_prs= new Tproceso($clink);

if ($id_proceso != -1) {
    $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['local_proceso_id']);
    $obj_prs->Set();
    $tipo_prs= $obj_prs->GetTipo();
    $proceso= $obj_prs->GetNombre();
} else {
    $proceso= "Todos los procesos";
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE TAREAS", "Corresponde a periodo: $fecha_inicio / $fecha_fin");
?>

<html>
    <head>
        <title>LISTADO DE TAREAS</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                LISTADO DE TAREAS DESDE <?= $fecha_inicio ?> HASTA <?= $fecha_final ?>
            </div>
        </div>

        <div class="page center">
             <table cellspacing="0">
                 <thead>
                     <tr>
                         <th class="plhead left">No.</th>
                         <th class="plhead">TAREA</th>
                         <th class="plhead">%</th>
                         <th class="plhead">RESPONSABLE</th>
                         <th class="plhead">INICIO (plan)</th>
                         <th class="plhead">FIN(plan)</th>
                         <th class="plhead">INICIO (real)</th>
                         <th class="plhead">FIN (real)</th>
                         <th class="plhead">DESCRIPCI&Oacute;N</th>
                         <th class="plhead">PARTICIPANTES</th>
                     </tr>
                 </thead>

                 <tbody>
                     <?php
                     $obj_pr = new Tproyecto($clink);

                     $obj_reg = new Tregtarea($clink);
                     $obj_reg->SetFecha($fecha_final);

                     $i = 0;

                     $obj->SetYear($year);
                     $obj->SetIdPrograma($id_programa);
                     (!empty($id_proceso) && $id_proceso != -1) ? $obj->SetIdProceso($id_proceso) : $obj->SetIdProceso(null);

                     $obj->SetFechaInicioPlan(date2odbc($fecha_inicio));
                     $obj->SetFechaFinPlan(date2odbc($fecha_final));

                     $result = $obj->listar(true, $planning);
                     $i = 0;

                     while ($row = $clink->fetch_array($result)) {
                         if (!empty($terminado))
                             if (!empty($row['fecha_fin_real'])) 
                                continue;
                         ?>
                         <tr>
                             <td class="plinner left">
                                <?= ++$i ?>
                             </td>
                             <td class="plinner">
                                 <?= nl2br($row['tarea']) ?>
                             </td>

                             <td class="plinner">
                                 <?php
                                 $avance = $obj_reg->getAvance($row['id_tarea']);
                                 echo $avance;
                                 ?>
                             </td>

                             <td class="plinner"><?= nl2br($row['responsable']) ?></td>

                             <?php $fecha = odbc2date($row['fecha_inicio_plan']); ?>

                             <td class="plinner">
                                 <?= $fecha ?>
                             </td>

                             <?php $fecha = odbc2date($row['fecha_fin_plan']); ?>

                             <td class="plinner">
                                 <?= $fecha ?>
                             </td>

                             <?php
                             if (empty($row['fecha_inicio_real']))
                                 $fecha = '&nbsp;';
                             else {
                                 $fecha = odbc2date($row['fecha_inicio_real']);
                             }
                             ?>

                             <td class="plinner">
                                 <?= $fecha ?>
                             </td>

                             <?php
                             if (empty($row['fecha_fin_real']))
                                 $fecha = '&nbsp;';
                             else {
                                 $fecha = odbc2date($row['fecha_fin_real']);
                             }

                             if (empty($fecha))
                                 $fecha = '&nbsp;';
                             ?>
                             <td class="plinner"><?= $fecha ?></td>

                             <td class="plinner">
                                 <?= textparse($row['descripcion']) ?>
                             </td>

                             <td class="plinner">
                                 <?php
                                 $string = $obj_reg->get_participantes($row['_id'], 'tarea');
                                 echo $string;
                                 ?>
                             </td>

                         </tr>
                     <?php
                     ++$i;
                 } ?>
                 </tbody>
             </table>
        </div>

         <?php require "inc/print_bottom.inc.php";?>

