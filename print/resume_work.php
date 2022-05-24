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
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$year= $_GET['year'];
$month= $_GET['month'];
$id_calendar= $_GET['id_calendar'];
$print_reject= $_GET['print_reject'];

$obj= new Tplantrab($clink);

$obj->SetIfEmpresarial(NULL);
$obj->SetIdUsuario($id_calendar);

$obj->SetYear($year);
$obj->SetMonth($month);
$obj->set_print_reject($print_reject);
$obj->list_reg();

if (empty($hh)) $hh= $time->GetHour();
if (empty($mi)) $mi= $time->GetMinute();

$obj_user= new Tusuario($clink);
$mail= $obj_user->GetEmail($id_calendar);

switch ($empresarial) {
    case 0:
        $title = "INDIVIDUAL";
        break;
    case 1:
        $title = "MENSUAL";
        break;
    case 2:
        $title = "ANUAL";
        break;
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE TRABAJO $title", "Corresponde a periodo mes/año: $month/$year");
?>

<html>
    <head>
        <title>RESUMEN DE TRABAJO</title>

        <?php include "inc/print_top.inc.php"?>

        <div class="page center">
            <h1>RESUMEN PLAN DE TRABAJO DE <?=$title ?> DE <?= $mail['nombre'] ?></h1> <br/>
                <?php if (($empresarial < 2) && !empty($month)) { echo "DEL MES DE ".strtoupper($meses_array[(int)$month]); } ?> DEL AÑO <?= $year ?>
            <br />


            <table  class="center" width="800px">
                <tr>
                    <td colspan="6">TOTAL DE TAREAS <u> <?= $obj->total?> </u>&nbsp; DE ELLAS <u> <?= $obj->extras ?> </u>&nbsp; NUEVAS TAREAS EXTRAPLAN</td>
                </tr>
                <tr style="border:none">
                    <td colspan="7"><hr /></td>
                </tr>
                <tbody id="tb-resume">
                    <tr>
                        <td class="tdhead" style="border-left:none">TOTAL DE TAREAS</td>
                        <td class="tdhead">PLANIFICADAS</td>
                        <td class="tdhead">CUMPLIDAS</td>
                        <td class="tdhead">INCUMPLIDAS</td>
                        <td class="tdhead">MODIFICADAS</td>
                        <td class="tdhead">EXTRA-PLAN</td>
                        <td class="tdhead">CANCELADAS O POSPUESTAS</td>
                    </tr>
                    <tr>
                        <td style="border-left:none"><?=$obj->total?></td>
                        <td><?= ($obj->total - $obj->extras) ?></td>
                        <td><?= $obj->cumplidas ?></td>
                        <td><?= $obj->incumplidas ?></td>
                        <td><?= $obj->modificadas ?></td>
                        <td><?= $obj->extras ?></td>
                        <td><?= $obj->canceladas ?></td>
                    </tr>
                </tbody>
            </table>


            <div class="res-head" style="margin-top:30px;">RELACIÓN DE TAREAS INCUMPLIDAS</div>

            <div id="div-incump">
                <table border='0' cellpadding='0' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>TAREA</th>
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
                                ++$i;
                                ?>
                                <tr>
                                    <td><?=$i?></td>
                                    <td><?=$array['evento']?></td>
                                    <td><?= textparse($array['descripcion'])?></td>
                                    <td><?=odbc2date($array['plan']) ?></td>
                                    <td><?= textparse($array['observacion'])?></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>


            <div class="res-head" style="clear:both; margin-top:30px;">RELACIÓN DE TAREAS MODIFICADAS</div>

            <div id="div-incump">
                <table border='0' cellpadding='0' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>TAREA</th>
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
                                ++$i;
                                ?>

                                <tr>
                                    <td><?=$i?></td>
                                    <td><?=$array['evento']?></td>
                                    <td><?=nl2br($array['descripcion'])?></td>
                                    <td><?=odbc2date($array['plan']) ?></td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <?php
                                        $usr = $obj_user->GetEmail($array['id_responsable']);
                                        if (!is_null($usr))
                                            echo $usr['nombre'] . ' (' . $usr['cargo'] . ')';
                                        ?>
                                    </td>
                                    <td><?=nl2br($array['observacion'])?></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>

            <div class="res-head" style="clear:both; margin-top:30px;">RELACIÓN DE TAREAS NUEVAS EXTRA PLAN</div>

            <div id="div-incump">
                <table border='0' cellpadding='0' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>TAREA</th>
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
                                ++$i;
                                ?>

                                <tr>
                                    <td><?=$i?></td>
                                    <td><?=$array['evento']?></td>
                                    <td><?=nl2br($array['descripcion'])?></td>
                                    <td><?=odbc2date($array['plan']) ?></td>
                                    <td>
                                        <?php
                                        $usr = $obj_user->GetEmail($array['id_responsable']);
                                        if (!is_null($usr))
                                            echo $usr['nombre'] . ' (' . $usr['cargo'] . ')';
                                        ?>
                                    </td>
                                    <td><?=nl2br($array['observacion'])?></td>
                                </tr>
                                <?php }
                            } ?>
                    </tbody>
                </table>
            </div>

            <div class="res-head" style="clear:both; margin-top:30px;">RELACIÓN DE TAREAS CANCELADAS O POSPUESTAS</div>

            <div id="div-incump">
                <table border='0' cellpadding='0' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>TAREA</th>
                            <th>DESCRIPCIÓN</th>
                            <th>FECHA PLANIFICADA</th>
                            <th>FECHA DE CUMPLIMIENTO REAL</th>
                            <th>CANCELADA POR</th>
                            <th>OBSERVACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        if (is_array($obj->canceladas_list)) {
                            foreach ($obj->canceladas_list as $array) {
                                ++$i;
                                ?>

                                <tr>
                                    <td><?=$i?></td>
                                    <td><?=$array['evento']?></td>
                                    <td><?=nl2br($array['descripcion'])?></td>
                                    <td><?=odbc2date($array['plan']) ?></td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <?php
                                        $usr = $obj_user->GetEmail($array['id_responsable']);
                                        if (!is_null($usr))
                                            echo $usr['nombre'] . ' (' . $usr['cargo'] . ')';
                                        ?>
                                    </td>
                                    <td><?=nl2br($array['observacion'])?></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>

    <?php require "inc/print_bottom.inc.php";?>
