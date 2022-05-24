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
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/plan_ci.class.php";

require_once "../php/class/traza.class.php";

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
$obj->SetTipoPlan($tipo);

$obj->Set();
$id_plan= $obj->GetIdPlan();

$cumplimiento= $obj->GetCumplimiento();
$auto_evaluacion= $obj->GetAutoEvaluacion();
$id_responsable= $obj->GetIdResponsable_eval();
$id_responsable_aprb= $obj->GetIdResponsable_aprb();
$observacion= $obj->GetObservacion();
$evaluado= odbc2date($obj->GetEvaluado());
$aprobado= odbc2date($obj->GetAprobado());

$obj->set_cronos(date('Y-m-d H:i:s'));

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) 
    $toshow= _EVENTO_MENSUAL;
elseif ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL) 
    $toshow= _EVENTO_ANUAL;
else 
    $toshow_plan= _EVENTO_INDIVIDUAL;

$obj->set_print_reject($print_reject);
$obj->list_reg($toshow);

$_SESSION['plantrab_total']= $obj->total;

if (empty($hh)) $hh= $time->GetHour();
if (empty($mi)) $mi= $time->GetMinute();

$obj_user= new Tusuario($clink);
$mail= $obj_user->GetEmail($id_calendar);

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && !empty($id_calendar)) {
    $obj_user= new Tusuario($clink);
    $row= $obj_user->GetEmail($id_calendar);
    $usuario= $row['nombre'];
    $cargo= $row['cargo'];
    $id_proceso= $row['id_proceso'];
}

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();

if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) 
    $_nombre= $proceso;

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
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO PLAN $tipo_plan_text", "Corresponde a periodo mes/año: $month/$year");    
?>

<html>
    <head>
        <title>RESUMEN DE PLAN</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center title-header">
            RESUMEN DEL CUMPLIMIENTO DEL PLAN <?=$tipo_plan_text?>
            <br />
        </div>


        <div class="page center">
            <table width="400px" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="plinner left top"><strong>No</strong></td>
                    <td class="plinner top"><strong>Desglose</strong></td>
                    <td class="plinner top"><strong>Cantidad</strong></td>
                    <td class="plinner top"><strong>%</strong></td>
                </tr>
                <tr>
                    <td class="plinner left">1</td>
                    <td class="plinner">Total de Tareas</td>
                    <td class="plinner"><?= $obj->total ?></td>
                    <td class="plinner">100</td>
                </tr>
                <tr>
                    <td class="plinner left">2</td>
                    <td class="plinner">Total de Tareas Planificadas (aprobadas)</td>
                    <td class="plinner">
                        <?php
                        echo $obj->total - $obj->extras;
                        if (empty($aprobado))
                            $text = "<em>Plan de trabajo individual aún sin aprobar. Probablemente todas las tareas consideradas como extra-plan.</em>";
                        ?>
                    </td>

                    <td class="plinner"><?php
                        $ratio = (($obj->total - $obj->extras) / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?></td>
                </tr>
                <tr>
                    <td class="plinner left">2</td>
                    <td class="plinner">Cumplidas</td>
                    <td class="plinner"><?= $obj->cumplidas ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->cumplidas / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="plinner left">3</td>
                    <td class="plinner">Aplazadas</td>
                    <td class="plinner"><?= $obj->canceladas ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->canceladas / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="plinner left">4</td>
                    <td class="plinner">Delegadas</td>
                    <td class="plinner"><?= $obj->delegadas ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->delegadas / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="plinner left">5</td>
                    <td class="plinner">Nuevas (exra plan) </td>
                    <td class="plinner"><?= $obj->extras ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->extras / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?></td>
                </tr>
                <tr>
                    <td class="plinner left">6</td>
                    <td class="plinner">Eliminadas</td>
                    <td class="plinner"><?= $obj->canceladas ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->canceladas / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="plinner left">7</td>
                    <td class="plinner">Rechazadas no planificadas</td>
                    <td class="plinner"><?= $obj->rechazada_extras ?></td>
                    <td class="plinner">
                        <?php
                        $ratio = ($obj->rechazada_extras / $obj->total) * 100;
                        echo number_format($ratio, 1);
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="page">
            <?php
            if ($tipo_plan >= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $tipo_plan <= _PLAN_TIPO_ACTIVIDADES_ANUAL) {
                $auto_evaluado = $obj->GetAutoEvaluado();

                if (!empty($auto_evaluado)) {
                    $id_user = $obj->GetIdResponsable_auto_eval();
                    $email = $obj_user->GetEmail($id_user);
                    ?>
                    <strong>(auto evaluación) <br /> VALORACIÓN CUALITATIVA :</strong><br /><?= $auto_evaluacion ?>
                    <br />Auto evaluación hecha por <?= $email['nombre'] . ' (' . $email['cargo'] . ')' ?> en fecha <?= odbc2date($auto_evaluado) ?>
                <?php } ?>

                <?php
                $evaluado = $obj->GetEvaluado();

                if (!empty($evaluado)) {
                    $id_user = $obj->GetIdResponsable_eval();
                    $email = $obj_user->GetEmail($id_user);
                    ?>
                    <br/><br/><strong>(evaluación) <br /> VALORACIÓN CUANTITATIVA:  <?= $evaluacion_array[$cumplimiento] ?></strong><br />
                    <br /><strong>VALORACIÓN CUALITATIVA:</strong><BR /><?= nl2br($obj->GetEvaluacion()) ?>
                <?php } ?>
            <?php } ?>
        </div>

        <br /><br />

        <div class="page">
            <table width="100%" class="none-border">
                <tr>
                    <td class="none-border" style="text-align: left">
                        <?php
                        $id_aprobado = $obj->GetIdResponsable_aprb();
                        $mail = !empty($id_aprobado) ? $obj_user->GetEmail($id_aprobado) : null;

                        if (!is_null($mail)) {
                            ?>
                            Aprobado por: <?= $mail['nombre'] ?><br />
                            <?= $mail['cargo'] ?><br />
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                        <?php } ?>
                    </td>

                    <td class="none-border" style="min-width: 200px;"></td>

                    <td class="none-border" style="text-align: right">
                        <?php if ($tipo_plan >= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $tipo_plan <= _PLAN_TIPO_ACTIVIDADES_ANUAL) { ?>
                            <?php
                            $id_eval = $obj->GetIdResponsable_eval();
                            $mail = $obj_user->GetEmail($id_eval);

                            if (!is_null($mail)) {
                                ?>
                                Evaluado por: <?= $mail['nombre'] ?><br />
                                <?= $mail['cargo'] . ')' ?><br />
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_eval ?>" border="0" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>

    <?php require "inc/print_bottom.inc.php";?>
