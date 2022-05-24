<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2016
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_OUT;
$date_eval= !empty($_GET['date_eval']) ? urldecode($_GET['date_eval']) : 0;

$month= (int)$month;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_plan= new Tplantrab($clink);

if (!empty($date_eval))
    $obj_plan->SetDate_eval_cutoff($date_eval);
$obj_plan->set_cronos(date('Y-m-d H:i:s'));
$obj_plan->set_print_reject($print_reject);

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetYear($year);
$obj_plan->SetMonth($month);
$obj_plan->SetIfEmpresarial(1);
$obj_plan->SetIdUsuario(null);
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);
$obj_plan->toshow= _EVENTO_MENSUAL;

$obj_plan->Set();

$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$cumplimiento= $obj_plan->GetCumplimiento();
$id_evaluado= $obj_plan->GetIdResponsable_eval();
$date_eval= $obj_plan->GetEvaluado();

$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($id_evaluado);
$obj_user->Set();
$nombre_eval= $obj_user->GetNombre();
$cargo_eval= $obj_user->GetCargo();
$firma_eval= $obj_user->GetParam();

unset($obj_user);
$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($id_aprobado);
$obj_user->Set();
$nombre_aprb= $obj_user->GetNombre();
$cargo_aprb= $obj_user->GetCargo();
$firma_aprb= $obj_user->GetParam();

if ((!empty($date_eval)) && !is_null($obj_plan->anual)) {
    $date= !empty($date_eval) ? $date_eval : $date_aprb;
    $date= odbc2time_ampm($date);
    $text= !empty($date_eval) ? "Evaluado" : "Aprobado";
    $note= "<p>Este plan ya fue $text en fecha $date. Los datos que se muestran en el resumen se corresponden ";
    $note.= "con dicho momento. <br/>Para actualizar la información deberá evaluar nuevamente el plan.</p>";
}

$notes= null;

$total_cumulative= 0;
$cumplidas_cumulative= 0;
$incumplidas_cumulative= 0;
$canceladas_cumulative= 0;
$modificadas_cumulative= 0;
$delegadas_cumulative= 0;
$reprogramadas_cumulative= 0;

$efectivas_cumulative= 0;
$efectivas_cumplidas_cumulative= 0;
$efectivas_incumplidas_cumulative= 0;
$efectivas_canceladas_cumulative= 0;

$externas_cumulative= 0;

$extras_cumulative= 0;
$extras_externas_cumulative= 0;
$extras_propias_cumulative= 0;

$anual_efectivas_cumulative= 0;
$anual_propias_cumulative= 0;
$mensual_propias_efectivas_cumulative= 0;
$anual_externas_cumulative= 0;

$mensual_cumulative= 0;
$mensual_propias_cumulative= 0;
$mensual_externas_cumulative= 0;
$mensual_externas_efectivas_cumulative= 0;

for ($i= 1; $i <= $month; $i++) {
    unset($obj_plan);
    $obj_plan= new Tplantrab($clink);

    $obj_plan->SetIdProceso($id_proceso);
    $obj_plan->SetYear($year);
    $obj_plan->SetMonth($i);
    $obj_plan->SetIfEmpresarial(1);
    $obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);
    $obj_plan->toshow= _EVENTO_MENSUAL;
    $obj_plan->set_print_reject($print_reject);
    $obj_plan->Set();

    if (empty($obj_plan->GetAprobado()))
        $notes.= "<strong>{$meses_array[$i]}:</strong> El Plan Mensual no ha sido aprobado.<br />";
        
    if (!empty($obj_plan->GetAprobado()) || $i == $month) {
        $obj_plan->SetIfEmpresarial(null);
        $obj_plan->list_reg(_EVENTO_MENSUAL);
        if ($i == $month)
            $obj_plan->update();
    } 

    if ($i < $month) {
        $total_cumulative+= $obj_plan->total;
        $cumplidas_cumulative+= $obj_plan->cumplidas;
        $incumplidas_cumulative+= $obj_plan->incumplidas;
        $canceladas_cumulative+= $obj_plan->canceladas;
        $modificadas_cumulative+= $obj_plan->modificadas;
        $delegadas_cumulative+= $obj_plan->delegadas;
        $reprogramadas_cumulative+= $obj_plan->reprogramadas;

        $efectivas_cumulative+= $obj_plan->efectivas;
        $efectivas_cumplidas_cumulative+= $obj_plan->efectivas_cumplidas;
        $efectivas_incumplidas_cumulative+= $obj_plan->efectivas_incumplidas;
        $efectivas_canceladas_cumulative+= $obj_plan->efectivas_canceladas;

        $externas_cumulative+= $obj_plan->externas;

        $extras_cumulative+= $obj_plan->extras;
        $extras_externas_cumulative+= $obj_plan->extras_externas;
        $extras_propias_cumulative+= $obj_plan->extras_propias;

        $anual_cumulative+= $obj_plan->anual;
        $anual_propias_cumulative+= $obj_plan->anual_propias;
        $anual_externas_cumulative+= $obj_plan->anual_externas;

        $mensual_efectivas_cumulative+= ($obj_plan->mensual - $obj_plan->mensual_extras);
        $mensual_propias_cumulative+= $obj_plan->mensual_propias;
        $mensual_propias_efectivas_cumulative+= ($obj_plan->mensual_propias - $obj_plan->mensual_propias_extras);
        $mensual_externas+= $obj_plan->mensual_externas;
        $mensual_externas_efectivas_cumulative+= ($obj_plan->mensual_externas - $obj_plan->mensual_externas_extras);
    }
}

$obj_signal= new Tevento_signals($clink);
$obj_signal->print_reject= $print_reject;

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO DEL  PLAN DE TRABAJO", "Corresponde a periodo mes/año: $month/$year");
?>

<html>
    <head>
        <title>PLAN MENSUAL MODELO 2016</title>

        <?php require "inc/print_top.inc.php";?>

        <style type="text/css">
            .cell {
                width:100px;
                text-align:center;
                vertical-align:text-top;
            }
        </style>

        <div class="page center">
            <h1>RESUMEN DEL CUMPLIMIENTO DEL PLAN DE TRABAJO DEL MES DE:</h1>  <?= strtoupper($meses_array[(int)$month]) ?>, <?= $year ?><br />
            <strong>ENTIDAD:</strong>  <?= $proceso ?><br />

            <br />
            <table class="center" width="800px">
                <thead>
                    <tr>
                        <th colspan="14">TAREAS PLANIFICADAS</th>
                    </tr>
                    <tr>
                        <th class="plhead left" colspan="3" rowspan="2">TOTAL TAREAS DEL PLAN MENSUAL</th>
                        <th class="plhead" colspan="5">DEL PLAN ANUAL PARA EL MES</th>
                        <th class="plhead" colspan="5">NUEVAS TAREAS INCORPORADAS  EN LA PUNTUALIZACIÓN MENSUAL</th>
                        <th class="plhead" rowspan="2">% INCORPORADAS vs PLAN ANUAL</th>
                    </tr>
                    <tr>
                        <th class="plhead left">SUB TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">SUB TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <?php 
                        $mensual_efectivas= $obj_plan->mensual - $obj_plan->mensual_extras;
                        $mensual_externas_efectivas= $obj_plan->mensual_externas - $obj_plan->mensual_externas_extras;
                        $mensual_propias_efectivas= $obj_plan->mensual_propias - $obj_plan->mensual_propias_extras;
                        ?>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">MES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?= $obj_plan->anual + $mensual_efectivas ?></td>
                        <td class="plinner"><?= $obj_plan->anual ?></td>
                        <td class="plinner"><?= $obj_plan->anual_externas ?></td>
                        <td class="plinner"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->anual_externas / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $obj_plan->anual_propias ?></td>
                        <td class="plinner"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->anual_propias / (float) $obj_plan->anual) * 100, 1) : ''?></td>
                        <td class="plinner"><?= $mensual_efectivas ?></td>
                        <td class="plinner"><?= $mensual_externas_efectivas ?></td>
                        <td class="plinner"><?= $mensual_efectivas > 0 ? number_format(($mensual_externas_efectivas / (float) $mensual_efectivas) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $mensual_propias_efectivas ?></td>
                        <td class="plinner"><?= $obj_plan->mensual > 0 ? number_format(($mensual_propias_efectivas / (float) $mensual_efectivas) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $obj_plan->anual > 0 ? number_format(($mensual_efectivas / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                    </tr>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">ACUMULADAS DE MESES ANTERIORES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?= $anual_cumulative + $mensual_efectivas_cumulative?></td>
                        <td class="plinner"><?= $anual_cumulative ?></td>
                        <td class="plinner"><?= $anual_externas_cumulative ?></td>
                        <td class="plinner"><?= $anual_cumulative > 0 ? number_format(($anual_externas_cumulative / (float) $anual_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $anual_propias_cumulative ?></td>
                        <td class="plinner"><?= $anual_cumulative > 0 ? number_format(($anual_propias_cumulative / (float) $anual_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $mensual_efectivas_cumulative ?></td>
                        <td class="plinner"><?= $mensual_externas_efectivas_cumulative ?></td>
                        <td class="plinner"><?= $mensual_efectivas_cumulative > 0 ? number_format(($mensual_externas_efectivas_cumulative / (float) $mensual_efectivas_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $mensual_propias_efectivas_cumulative ?></td>
                        <td class="plinner"><?= $mensual_efectivas_cumulative > 0 ? number_format(($mensual_propias_efectivas_cumulative / (float) $mensual_efectivas_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $anual_cumulative > 0 ? number_format(($mensual_efectivas_cumulative / (float) $anual_cumulative) * 100, 1) : '' ?></td>
                    </tr>
                </tbody>
            </table>

            <br /><br />
            <table class="center" width="800px">
                <thead>
                    <tr>
                        <th colspan="13">TAREAS CUMPLIDAS</th>
                    </tr>
                    <tr>
                        <th class="plhead left" colspan="2" rowspan="2">TOTAL TAREAS CUMPLIDAS EN EL MES</th>
                        <th class="plhead" colspan="5">PLANIFICADAS EN EL PLAN MENSUAL (INCLUYE LAS DEL PLAN ANUAL Y LAS PUNTUALIZADAS)</th>
                        <th class="plhead" colspan="6">EXTRA PLANES</th>
                    </tr>
                    <tr>
                        <th class="plhead left">PLANIFICADAS</th>
                        <th class="plhead">CUMPLIDAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">INCUMPLIDAS</th>
                        <th class="plhead">POSPUESTAS  O SUSPENDIDAS</th>
                        <th class="plhead">TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">% EXTRA PLANES vs TAREAS PLANIFICADAS</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">MES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?= $obj_plan->efectivas ?></td>
                        <td class="plinner"><?= $obj_plan->efectivas_cumplidas ?></td>
                        <td class="plinner"><?= $obj_plan->efectivas ? number_format(($obj_plan->efectivas_cumplidas / (float) $obj_plan->efectivas) * 100, 1) : ''?></td>
                        <td class="plinner"><?= $obj_plan->efectivas_incumplidas ?></td>
                        <td class="plinner"><?= $obj_plan->efectivas_canceladas ?></td>
                        <td class="plinner"><?= $obj_plan->extras ?></td>
                        <td class="plinner"><?= $obj_plan->extras_externas ?></td>
                        <td class="plinner"><?= $obj_plan->extras > 0 ? number_format(($obj_plan->extras_externas / (float) $obj_plan->extras) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $obj_plan->extras_propias ?></td>
                        <td class="plinner"><?= $obj_plan->extras > 0 ? number_format(($obj_plan->extras_propias / (float) $obj_plan->extras) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $obj_plan->efectivas > 0 ? number_format(($obj_plan->extras / (float) $obj_plan->efectivas) * 100, 1) : '' ?></td>
                    </tr>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">ACUMULADAS DE MESES ANTERIORES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?= $efectivas_cumulative ?></td>
                        <td class="plinner"><?= $efectivas_cumplidas_cumulative ?></td>
                        <td class="plinner"><?= $efectivas_cumulative > 0 ? number_format(($efectivas_cumplidas_cumulative / (float) $efectivas_cumulative) * 100, 1) : ''?></td>
                        <td class="plinner"><?= $efectivas_incumplidas_cumulative ?></td>
                        <td class="plinner"><?= $efectivas_canceladas_cumulative ?></td>
                        <td class="plinner"><?= $extras_cumulative ?></td>
                        <td class="plinner"><?= $extras_externas_cumulative ?></td>
                        <td class="plinner"><?= $extras_cumulative > 0 ? number_format(($extras_externas_cumulative / (float) $extras_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $extras_propias_cumulative ?></td>
                        <td class="plinner"><?= $extras_cumulative > 0 ? number_format(($extras_propias_cumulative / (float) $extras_cumulative) * 100, 1) : '' ?></td>
                        <td class="plinner"><?= $efectivas_cumulative > 0 ? number_format(($extras_cumulative / (float) $efectivas_cumulative) * 100, 1) : '' ?></td>
                    </tr>
                </tbody>
            </table>


            <br /><br />
            <table class="center none-border" width="100%">
                <tr>
                    <td class="plinner left top" colspan="2"><strong>OBSERVACIONES DEL CUMPLIMIENTO</strong></td>
                    <?php if ($config->responsable_planwork) { ?>
                    <td class="plinner center top"><strong>RESPONSABLE</strong></td>
                    <?php } else { ?>
                    <td class="plinner top" width="200px"><strong>QUIEN LAS ORIGINO</strong></td>
                    <?php } ?>
                    <td class="plinner top"><strong>CAUSAS</strong></td>
                </tr>

                <tr>
                    <td colspan="4" class="inner"><strong>TAREAS INCUMPLIDAS</strong></td>
                </tr>
                <?php
                $i = 0;
                foreach ($obj_plan->efectivas_incumplidas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left" width="30px"><?= ++$i ?></td>
                        <td class="plinner"><?= textparse($array['evento']) ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <?php if ($config->responsable_planwork) { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_responsable']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } else { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_user_asigna']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } ?>

                        <td class="plinner"><?= textparse($array['observacion']) ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" class="inner">
                        <strong>SUSPENDIDAS O POSPUESTAS</strong>
                    </td>
                </tr>
                <?php
                $i = 0;
                foreach ($obj_plan->efectivas_canceladas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left" width="30px"><?= ++$i ?></td>
                        <td class="plinner"><?= textparse($array['evento']) ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <?php if ($config->responsable_planwork) { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_responsable']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } else { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_user_asigna']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } ?>

                        <td class="plinner"><?= textparse($array['observacion']) ?></td>
                    </tr>
                <?php } ?>


                <tr>
                    <td colspan="4" class="inner"><strong>NUEVAS TAREAS(EXTRA PLANES)</strong></td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj_plan->extras_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left" width="30px"><?= ++$i ?></td>
                        <td class="plinner"><?= $array['evento'] ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <?php if ($config->responsable_planwork) { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_responsable']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } else { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_user_asigna']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } ?>

                        <td class="plinner"><?= textparse($array['observacion']) ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td class="none-border top"></td>
                    <td class="none-border top"></td>
                    <td colspan="2" class="none-border top"></td>
                </tr>
            </table>

            <br />
            <div style="width: 800px; text-align: left">
                <h1 style="font-size: 1.2em;">Observaciones:</h1>
                <p><?= $notes ?></p>
                <p>
                    Las actividades registradas Planes de Actividades que no han sido aprobados
                    no son considerardas en los calculos del acumulado hasta el actual mes de <?= $meses_array[(int) $month] ?>
                </p>
            </div>
        </div>

        <div class="page">
            <div class="row" style="margin-top: 20px; margin-left: 0px;">
                <strong>ANALISIS CUALITATIVO</strong>
            </div>

            <div class="row" style="margin-left: 20px;">
                <?php
                $text= $obj_plan->GetEvaluacion();
                echo textparse(purge_html($text, false), false);
                echo "<br/>";

                $evaluado = $obj_plan->GetEvaluado();
                if ($evaluado) {
                    echo "EVALUACIÓN CUANTITATIVA: " . $evaluacion_array[$cumplimiento];

                    $id_user = $obj_plan->GetIdResponsable_eval();
                    $email = $obj_user->GetEmail($id_user);
                ?>
                    <br />
                    Evaluación hecha por: <?= textparse($email['nombre'])." ({$email['cargo']})" ?> en fecha <?= odbc2date($evaluado) ?>
                    <?php
                }
                ?>
            </div>

            <div class="row" style="margin-top: 20px; margin-left: 0px;">
                <strong>AUTOEVALUACIÓN</strong>
            </div>

            <div class="row" style="margin-left: 20px;">
                <?php
                $auto_evaluado = $obj_plan->GetAutoEvaluado();

                if ($auto_evaluado) {
                    $id_user = $obj_plan->GetIdResponsable_auto_eval();
                    $email = $obj_user->GetEmail($id_user);

                    $text= $obj_plan->GetAutoEvaluacion();
                    echo textparse(purge_html($text, false), false);
                    echo "<br />";
                    ?>
                    Auto evaluación hecha por: <?= "{$email['nombre']} ({$email['cargo']})" ?> en fecha <?= odbc2date($auto_evaluado) ?>
                    <?php
                } else {
                    echo "<br />";
                }
                ?>
            </div>

            <div class="row" style="">
                <table width="100%" class="none-border" style="margin-top: 20px;">
                    <tr>
                        <td class="none-border" style="text-align: left">
                            <?php
                            if (!empty($id_aprobado)) {
                                ?>
                                <strong>Aprobado por:</strong> <?= $nombre_aprb ?><br />
                                <?= $cargo_aprb ?><br />
                                <?php if ($firma_aprb['name']) { ?> 
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                                <?php } ?>
                            <?php } ?>
                        </td>

                        <td class="none-border" style="min-width: 150px;"></td>

                        <td class="none-border" style="text-align: right">
                            <?php
                            if (!empty($id_evaluado)) {    
                            ?>
                                <strong>Evaluado por:</strong> <?= $nombre_eval ?><br />
                                <?= $cargo_eval ?><br />
                                <?php if ($firma_eval['name']) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_evaluado ?>" border="0" />
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <?php require "inc/print_bottom.inc.php";?>