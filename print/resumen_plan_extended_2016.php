<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/orgtarea.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/plan_ci.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();
$obj_user= new Tusuario($clink);

$tipo_plan= !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$if_date_eval= !empty($_GET['date_eval']) ? urldecode($_GET['date_eval']) : 0;

$print_reject= !empty($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$id_calendar = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

if ($tipo_plan >= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $tipo_plan <= _PLAN_TIPO_ACTIVIDADES_ANUAL)
    $obj_plan= new Tplantrab($clink);
else
    $obj_plan= new Tplan_ci($clink);

$obj_plan->SetIfEmpresarial(NULL);
!empty($id_calendar) ? $obj_plan->SetIdUsuario($id_calendar): $obj_plan->SetIdUsuario(null);

$obj_plan->SetYear($year);
$obj_plan->SetMonth($month);
$obj_plan->SetTipoPlan($tipo_plan);
$tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL ? $obj_plan->SetIdProceso($id_proceso) : $obj_plan->SetIdProceso(null);
if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)
    $obj_plan->SetIdProceso(null);

$obj_plan->Set();
$id_plan= $obj_plan->GetIdPlan();

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $toshow= _EVENTO_INDIVIDUAL;
    $tipo_plan_text= "DE TRABAJO INDIVIDUAL";
}    
elseif ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) {
    $toshow= _EVENTO_MENSUAL;
    $tipo_plan_text= "DE GENERAL MENSUAL DE ACTIVIADES";
}
else {
    $toshow= _EVENTO_ANUAL;
    $tipo_plan_text= "DE ANUAL DE ACTIVIDADES";
}

$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj_plan->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
$cumplimiento= $obj_plan->GetCumplimiento();

if ((!empty($date_eval)) && !is_null($obj->anual)) {
    $date= !empty($date_eval) ? $date_eval : $date_aprb;
    $date= odbc2time_ampm($date);
    $text= !empty($date_eval) ? "Evaluado" : "Aprobado";
    $note= "<p>Este plan ya fue $text en fecha $date. Los datos que se muestran en el resumen se corresponden ";
    $note.= "con dicho momento. <br/>Para actualizar la información deberá evaluar nuevamente el plan.</p>";
}

if (!empty($date_eval))
    $obj_plan->SetDate_eval_cutoff($if_date_eval ? $date_eval : null);
$obj_plan->set_cronos(date('Y-m-d H:i:s'));

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $obj_plan->SetIdProceso(null);
    $obj_plan->set_id_proceso_code(null);
}

$obj_plan->set_print_reject($print_reject);
$obj_plan->list_reg($toshow);

$_SESSION['plantrab_total']= $obj_plan->total;

if (empty($hh))
    $hh= $time->GetHour();
if (empty($mi))
    $mi= $time->GetMinute();

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
$proceso.= !empty($tipo_prs) ? ", {$tipo_proceso_array[$tipo_prs]}" : null;

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO PLAN $tipo_plan_text", "Corresponde a periodo mes/año: $month/$year");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>RESUMEN DEL PLAN</title>

        <?php require "inc/print_top.inc.php";?>

        <style type="text/css">
            .page.block {
                width: 800px;
                text-align: left;
            }

        </style>

        <div class="page center">
            <table class="center none-border" width="100%">
                <!--
                <tr>
                    <td class="none-border">
                        <strong>Aprobado por:</strong><br />
                        <?php if (!empty($array_aprb)) { ?>
                            <?= $array_aprb['cargo'] ?><br />
                            <span><?= $array_aprb['nombre'] ?></span><br/>
                            <?php if (!is_null($array_aprb['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
                -->

                <tr>
                    <td class="none-border">
                        <div class="center">
                            <h1>RESUMEN DEL CUMPLIMIENTO DEL PLAN INDIVIDUAL DEL MES DE <?= strtoupper($meses_array[(int)$month]) ?>, AÑO <?= $year ?>.<br /></h1>
                            <?php if (!empty($id_calendar)) { ?>
                                <strong>NOMBRE Y APELLIDOS: </strong><?= "{$usuario}" ?><br />
                                <strong>CARGO: </strong><?= "{$cargo}" ?>
                            <?php } else { ?>
                                <strong><?=$proceso?></strong>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class="none-border pull-left">
                        <h1 style="text-decoration: underline">TAREAS PRINCIPALES</h1><br />
                        <?php
                        $objetivos= $obj_plan->GetObjetivo();
                        $objetivos= purge_html($objetivos, false);
                        $objetivos= textparse($objetivos, false);
                        echo $objetivos;
                        ?>
                    </td>
                </tr>
                -->
                <!--
                <tr>
                    <td class="none-border">
                        <div class="container-fluid pull-right">
                            <strong>Elaborado por:</strong><br />
                            <?=$cargo_print?><br /><?=$usuario_print?><br /><?=$proceso_print?><br />
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$_SESSION['id_usuario']?>" border="0" />
                        </div>
                    </td>
                </tr>
                -->
            </table>
        </div>

        <div class="page center">
            <br /><br />

            <table class="none-border center" width="800px">
                <thead>
                    <tr>
                        <th rowspan="2" align="center" class="plhead left right">TOTAL DE TAREAS PLANIFICADAS</th>
                        <th colspan="3" align="center" class="plhead right bottom">DE ELLAS</th>
                        <th rowspan="2" align="center" class="plhead">NUEVAS TAREAS (EXTRA PLANES)</th>
                    </tr>
                    <tr>
                        <th align="center" class="plhead right">CUMPLIDAS</th>
                        <th align="center" class="plhead right">INCUMPLIDAS</th>
                        <th align="center" class="plhead right">SUSPENDIDAS O POSPUESTAS</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plinner left center none-bottom">
                            <?php
                            if ($obj_plan->efectivas > 0) {
                                echo $obj_plan->efectivas . " (100%)";
                            } else {
                                echo $obj_plan->total ? "El Plan no ha sido aprobado" : "No hay tareas asignadas";
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj_plan->efectivas_cumplidas)) {
                                echo $obj_plan->efectivas_cumplidas;
                                $ratio = ($obj_plan->efectivas_cumplidas / $obj_plan->efectivas) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj_plan->efectivas_incumplidas)) {
                                echo $obj_plan->efectivas_incumplidas;
                                $ratio = ($obj_plan->efectivas_incumplidas / $obj_plan->efectivas) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj_plan->efectivas_canceladas)) {
                                echo $obj_plan->efectivas_canceladas;
                                $ratio = ($obj_plan->efectivas_canceladas / $obj_plan->efectivas) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj_plan->extras)) {
                                echo $obj_plan->extras;

                                $ratio = ($obj_plan->extras / $obj_plan->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="none-border center" width="800px">
                <tr>
                    <td class="plinner left top center" colspan="2"><strong>OBSERVACIONES DEL CUMPLIMIENTO</strong></td>
                    <td class="plinner top" style="text-align:center">
                        <strong><?=$config->responsable_planwork ? "RESPONSABLE" : "QUIEN LAS ORIGINO"?></strong>
                    </td>
                    <td class="plinner top" style="text-align:center"><strong>CAUSAS</strong></td>
                </tr>

                <tr>
                    <td colspan="4" class="plinner left">
                        <strong>TAREAS INCUMPLIDAS</strong>
                    </td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj_plan->efectivas_incumplidas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>
                        <td class="plinner">
                            <?= textparse($array['evento']) ?><br /><?= odbc2time_ampm($array['plan']) ?>
                        </td>

                        <td class="plinner">
                            <?php
                            $email = $config->responsable_planwork ? $obj_user->GetEmail($array['id_responsable']) : $obj_user->GetEmail($array['id_user_asigna']);
                            echo textparse($email['nombre']).' <br>'.$email['cargo'];
                            ?>
                        </td>

                        <td class="plinner"><?= textparse($array['observacion']); ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" class="plinner left">
                        <strong>SUSPENDIDAS O POSPUESTAS</strong>
                    </td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj_plan->efectivas_canceladas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>
                        <td class="plinner"><?= textparse(purge_html($array['evento'])) ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <td class="plinner">
                            <?php
                            $email = $config->responsable_planwork ? $obj_user->GetEmail($array['id_responsable']) : $obj_user->GetEmail($array['id_user_asigna']);
                            echo textparse($email['nombre']) . ' <br>' . $email['cargo'];
                            ?>
                        </td>

                        <td class="plinner"><?= textparse(purge_html($array['observacion'])) ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" class="plinner left">
                        <strong>NUEVAS TAREAS(EXTRA PLANES)</strong>
                    </td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj_plan->extras_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>

                        <td class="plinner">
                            <?= textparse($array['evento']) ?>
                            <br />
                            <?= odbc2time_ampm($array['plan']) ?>
                        </td>

                        <td class="plinner">
                            <?php
                            $email = $config->responsable_planwork ? $obj_user->GetEmail($array['id_responsable']) : $obj_user->GetEmail($array['id_user_asigna']);
                            echo textparse($email['nombre']).' <br>'.$email['cargo'];
                            ?>
                        </td>

                        <td class="plinner"><?= textparse(purge_html($array['observacion'])) ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="2" class="none-border top"></td>
                    <td class="none-border top"></td>
                    <td colspan="2" class="none-border top"></td>
                </tr>
            </table>
        </div>


        <div class="page block text">
            <strong>ANALISIS CUALITATIVO</strong>

            <div class="col-12" style="margin-left: 20px;">
                <?php
                $text= $obj_plan->GetEvaluacion();
                echo textparse(purge_html($text, false), false);
                echo "<br/>";

                $evaluado = $obj_plan->GetEvaluado();

                if ($evaluado) {
                    echo "<br />EVALUACIÓN CUANTITATIVA: " . $evaluacion_array[$cumplimiento];

                    $id_user = $obj_plan->GetIdResponsable_eval();
                    $email = $obj_user->GetEmail($id_user);
                ?>
                    <br />
                    Evaluación hecha por <?= textparse($email['nombre'])." ({$email['cargo']})" ?> en fecha <?= odbc2date($evaluado) ?>
                    <?php
                }
                ?>
            </div>

            <br/>
            <strong>AUTOEVALUACIÓN</strong>

            <div class="col-12" style="margin-left: 20px;">
                <?php
                $auto_evaluado = $obj_plan->GetAutoEvaluado();

                if ($auto_evaluado) {
                    $id_user = $obj_plan->GetIdResponsable_auto_eval();
                    $email = $obj_user->GetEmail($id_user);

                    $text= $obj_plan->GetAutoEvaluacion();
                    echo textparse(purge_html($text, false), false);
                    echo "<br />";
                    ?>
                    Auto evaluación hecha por <?= "{$email['nombre']} ({$email['cargo']})" ?> en fecha <?= odbc2date($auto_evaluado) ?>
                    <?php
                } else {
                    echo "<br />";
                }
                ?>
            </div>


            <table class="none-border center" width="800px">
                <tr>
                    <td class="none-border" style="padding-top: 80px; width: 60%;">
                        <!--
                        <?php $mail = $obj_user->GetEmail($_SESSION['id_usuario']); ?>
                        Confecionado por: <?= textparse($mail['nombre']) ?><br />
                        <?= $mail['cargo'] ?><br />
                        <?php if (!is_null($email['firma'])) { ?>
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $_SESSION['id_usuario'] ?>" border="0" />
                        <?php } ?>
                        -->
                    </td>

                    <td width="100px" class="none-border"></td>

                    <td class="none-border">
                        <?php
                        $id_aprobado = $obj_plan->GetIdResponsable_aprb();
                        $mail = !empty($id_aprobado) ? $obj_user->GetEmail($id_aprobado) : null;

                        if (!is_null($mail)) {
                        ?>
                            Aprobado por: <?= textparse($mail['nombre']) ?><br />
                            <?= $mail['cargo'] ?><br />
                            <?php if (!is_null($array_aprb['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>


    <?php
    $auto_evaluado= $obj_plan->GetAutoEvaluado();
    $evaluado= $obj_plan->GetEvaluado();

    if ($auto_evaluado || $evaluado) {
    ?>

    <?php } ?>

    <?php require "inc/print_bottom.inc.php";?>
