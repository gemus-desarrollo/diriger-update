<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../../php/config.inc.php";

require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/plantrab.class.php";
require_once "../../php/class/plan_ci.class.php";

$time= new TTime();

$tipo_plan= $_GET['tipo_plan'];
$year= $_GET['year'];
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$print_reject= !empty($_GET['print_reject']) ? $_GET['print_reject'] : 0;

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$id_calendar = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

if ($tipo_plan >= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $tipo_plan <= _PLAN_TIPO_ACTIVIDADES_ANUAL) 
    $obj= new Tplantrab($clink);
else 
    $obj= new Tplan_ci($clink);

$obj->SetIfEmpresarial(NULL);
!empty($id_calendar) ? $obj->SetIdUsuario($id_calendar): $obj->SetIdUsuario(null);

$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetIdProceso($id_proceso);
$obj->SetTipoPlan($tipo_plan);

$obj->Set();

$id_plan= $obj->GetIdPlan();

$id_responsable= $obj->GetIdResponsable_eval();
$id_responsable_aprb= $obj->GetIdResponsable_aprb();
$observacion= $obj->GetObservacion();
$evaluado= odbc2date($obj->GetEvaluado());
$aprobado= odbc2date($obj->GetAprobado());
$cumplimiento= $obj->GetCumplimiento();

$obj->set_cronos(date('Y-m-d H:i:s'));

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) 
    $toshow= _EVENTO_INDIVIDUAL;
elseif ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) 
    $toshow= _EVENTO_MENSUAL;
else 
    $toshow= _EVENTO_ANUAL;

$obj->set_print_reject($print_reject);

$obj->list_reg($toshow);

$_SESSION['plantrab_total']= $obj->total;

if (empty($hh)) $hh= $time->GetHour();
if (empty($mi)) $mi= $time->GetMinute();

$obj_user= new Tusuario($clink);
$mail= $obj_user->GetEmail($id_calendar);

$obj_prs= new Tproceso($clink);

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
	$obj_user= new Tusuario($clink);
	$row= $obj_user->GetEmail($id_calendar);
	$usuario= $row['nombre'];
	$cargo= $row['cargo'];
    $id_proceso= $row['id_proceso'];
}

$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$tipo_plan_text= null;

switch($tipo_plan) {
  case _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL:
    $tipo_plan_text= "DE TRABAJO INDIVIDUAL";
    break;
  case _PLAN_TIPO_ACTIVIDADES_MENSUAL:
    $tipo_plan_text= "ANUAL DE ACTIVIDADES";
    break;
  case _PLAN_TIPO_ACTIVIDADES_ANUAL:
    $tipo_plan_text= "GENERAL MENSUAL DE ACTIVIADES";
    break;
  case _PLAN_TIPO_PREVENCION:
    $tipo_plan_text= "DE PREVENCIÓN";
    break;
  case _PLAN_TIPO_AUDITORIA:
    $tipo_plan_text= "ANUAL DE AUDITORÍAS";
    break;
  case _PLAN_TIPO_SUPERVICIONL:
    $tipo_plan_text= "ANUAL DE ACCIONES DE CONTROL";
    break;
}

$tipo_plan_text.= " DE ". !is_null($usuario) ? $usuario : $proceso ." PARA EL ";
if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) {
  $tipo_plan_text.= "DEL MES DE ".strtoupper($meses_array[(int)$month]);
}
$tipo_plan_text.= "AÑO $year";


$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO DEL  PLAN $tipo_plan_text", "Corresponde a periodo mes/año: $month/$year");
?>

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>RESUMEN DEL PLAN</title>
    <?php include "../inc/print.ini.php";?>
</head>

<center>

    <div id="headerpage" style="width: <?php echo $widthpage?>cm">
        <div align="center" class="title-header">
            RESUMEN DEL CUMPLIMIENTO DEL PLAN <?=$tipo_plan_text?>
        </div>
        <br />
    </div>

    <table width="700px" height="98" border=0 cellspacing="0" cellpadding=0>

        <thead>
            <tr>
                <th width="103" height="25" class="plhead">TOTAL DE TAREAS</strong></th>
                <th colspan="5" align="center" class="plhead">DE ELLAS</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td rowspan="2" align="center" class="plinner left">
                    <?php
                    if ($obj->total > 0) 
                      echo $obj->total." (100%)";
                    else 
                      echo "No hay tareas asignadas";
                ?>
                </td>

                <td width="79" height="20" align="center" class="plinner"><strong>CUMPLIDAS</strong></td>
                <td width="81" align="center" class="plinner"><strong>INCUMPLIDAS</strong></td>
                <td width="80" align="center" class="plinner"><strong>MODIFICADAS</strong></td>
                <td width="80" align="center" class="plinner"><strong>ELIMINADAS</strong></td>
                <td width="80" align="center" class="plinner"><strong>Nuevas Tareas<br />(Extra plan)</strong></td>
            </tr>

            <tr>
                <td height="20" align="center" class="plinner">
                <?php
                    if (!empty($obj->cumplidas)) {
                        echo $obj->cumplidas;
                        $ratio= ($obj->cumplidas/$obj->total)*100;
                        $ratio= setNULL($ratio, true);
                        echo  ' ('.number_format($ratio,1).'%)';
                    }
                ?>
                </td>

                <td align="center" class="plinner">
                <?php
                    if (!empty($obj->incumplidas)) {
                        echo $obj->incumplidas;
                        $ratio= ($obj->incumplidas/$obj->total)*100;
                        $ratio= setNULL($ratio, true);
                        echo  ' ('.number_format($ratio,1).'%)';
                    }
                ?>
                </td>

                <td align="center" class="plinner">
                <?php
                      if (!empty($obj->modificadas)) {
                          echo $obj->modificadas;
                          $ratio= ($obj->modificadas/$obj->total)*100;
                          $ratio= setNULL($ratio, true);
                          echo  ' ('.number_format($ratio,1).'%)';
                      }
                ?>
                </td>

                <td align="center" class="plinner">
                <?php
                    if (!empty($obj->canceladas)) {
                        echo $obj->canceladas;
                        $ratio= ($obj->canceladas/$obj->total)*100;
                        $ratio= setNULL($ratio, true);
                        echo  ' ('.number_format($ratio,1).'%)';
                    }
                ?>
                </td>

                <td align="center" class="plinner">
                <?php
                    if (!empty($obj->extras)) {
                        echo $obj->extras;
                        $ratio= ($obj->extras/$obj->total)*100;
                        $ratio= setNULL($ratio, true);
                        echo  ' ('.number_format($ratio,1).'%)';
                    }
                ?>
                </td>

            </tr>
        </tbody>
    </table>

    <br />
    <br />

    <table border=0 cellspacing="0" cellpadding=0>
        <tr>
            <td class="plinner left top" colspan="2" style="min-width: 400px"><strong>RELACIÓN DE TAREAS INCUMPLIDAS,
                    MODIFICADAS Y NUEVAS</strong></td>
            <td width="344" class="plinner top" style="min-width: 300px;"><strong>QUIEN LAS ORIGINO</strong></td>
        </tr>
        <tr>
            <td colspan="3" class="plinner left"><strong>MODIFICADAS</strong></td>
        </tr>

        <?php
   	$i= 0;
	foreach ($obj->modificadas_list as $array) {
  ?>
        <tr>
            <td class="plinner left" width="20px"><?php echo ++$i?></td>
            <td class="plinner"><?php echo $array['evento']; ?><br /><?php echo odbc2time_ampm($array['plan'])?></td>
            <td class="plinner">
              <?php
              $email= $obj_user->GetEmail($array['id_responsable']);
              echo $email['nombre'] .' ('.$email['cargo'], ') ';
              ?>
            </td>
        </tr>
        <?php } ?>

        <tr>
            <td colspan="3" class="inner"><strong>NUEVAS TAREAS(EXTRA PLAN)</strong></td>
        </tr>

        <?php
          $i= 0;
        foreach ($obj->extras_list as $array) {
        ?>

        <tr>
            <td class="plinner left"><?php echo ++$i?></td>
            <td class="plinner"><?php echo $array['evento']; ?><br /><?php echo odbc2time_ampm($array['plan'])?></td>
            <td class="plinner">
            <?php
              $email= $obj_user->GetEmail($array['id_user_asigna']);
              echo $email['nombre'] .' ('.$email['cargo'], ') ';
              ?>
            </td>
        </tr>
        <?php } ?>

        <tr>
            <td colspan="3" class="inner"><strong>ELIMINADAS</strong></td>
        </tr>
        <?php
    $i= 0;
	foreach ($obj->canceladas_list as $array) {
  ?>
        <tr>
            <td class="plinner left"><?php echo ++$i?></td>
            <td class="plinner"><?php echo $array['evento']; ?><br /><?php echo odbc2time_ampm($array['plan'])?></td>
            <td class="plinner">
              <?php
              $email= $obj_user->GetEmail($array['id_user_asigna']);
              echo $email['nombre'] .' ('.$email['cargo'], ') ';
              ?>
            </td>
        </tr>

        <?php } ?>
        <tr>
            <td colspan="3" class="inner"><strong>INCUMPLIDAS</strong></td>
        </tr>
        <?php
        $i= 0;
        foreach ($obj->incumplidas_list as $array) {
        ?>
        <tr>
            <td class="plinner left"><?php echo ++$i?></td>
            <td class="plinner"><?php echo $array['evento']; ?><br /><?php echo odbc2time_ampm($array['plan'])?></td>
            <td class="plinner">
              <?php
              $email= $obj_user->GetEmail($array['id_user_asigna']);
              echo $email['nombre'] .' ('.$email['cargo'], ') ';
              ?>
            </td>
        </tr>
        <?php } ?>

        <tr>
            <td class="none-border top"></td>
            <td class="none-border top"></td>
            <td class="none-border top"></td>
        </tr>
    </table>
</center>

<br />

<center>
    <?php
    $auto_evaluado= $obj->GetAutoEvaluado();
    $evaluado= $obj->GetEvaluado();

    if ($auto_evaluado || $evaluado) {
    ?>
    <div style="width:800px; border: none; border-bottom: 1px solid #000000; text-align: left">
        <?php
          if ($auto_evaluado) {
              $id_user= $obj->GetIdResponsable_auto_eval();
              $email= $obj_user->GetEmail($id_user);
        ?>
        <strong style="text-decoration:underline">AUTOVALORACIÓN CUALITATIVA :</strong><br />
        <?php echo nl2br($obj->GetAutoEvaluacion()); ?>
        <br />Auto evaluación hecha por <?php echo $email['nombre'].' ('.$email['cargo'].')'; ?> en fecha
        <?php echo odbc2date($auto_evaluado) ?>
        <?php } ?>

        <?php
        if ($evaluado) {
            $id_user= $obj->GetIdResponsable_eval();
            $email= $obj_user->GetEmail($id_user);
        ?>
        <br /><br />
        <strong style="text-decoration:underline">VALORACIÓN CUANTITATIVA:</strong>
        <?php echo $evaluacion_array[$cumplimiento]?><br />
        <br />
        <strong style="text-decoration:underline">VALORACIÓN CUALITATIVA:</strong><br />
        <?php echo nl2br($obj->GetEvaluacion()); ?> <br />
        <?php } ?>
        <br /><br />
    </div>
    <?php } ?>


    <br />
    <div style="width:700px; border: none; text-align: right">
        <table width="100%" border="0" class="none-border">
            <tr>

                <td class="none-border" style="text-align: left">
                    <?php $mail= $obj_user->GetEmail($_SESSION['id_usuario']); ?>
                    Confecionado por: <?php echo $mail['nombre'] ?><br />
                    <?php echo $mail['cargo'];?><br />
                    <?php if (!is_null($email['firma'])) {?><img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$_SESSION['id_usuario']?>"
                        border="0" /><?php } ?>
                </td>

                <td class="none-border" style="min-width: 200px;"></td>

                <td class="none-border" style="text-align: right">
                    <?php
                    $id_aprobado= $obj->GetIdResponsable_aprb();
                    $mail= !empty($id_aprobado) ? $obj_user->GetEmail($id_aprobado) : null;

                    if (!is_null($mail)) {
                    ?>
                    Aprobado por: <?php echo $mail['nombre'] ?><br />
                    <?php echo $mail['cargo'];?><br />
                    <?php if (!is_null($array_aprb['firma'])) {?> <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?php echo $id_aprobado ?>"
                        border="0" /><?php } ?>
                    <?php } ?>

                </td>
            </tr>
        </table>
    </div>

</center>
</body>

</html>