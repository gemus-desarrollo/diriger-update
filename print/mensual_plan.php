<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
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

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'list' || $action == 'edit') 
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);

$year= !empty($_GET['year']) ?$_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_proceso_asigna= !empty($_GET['id_proceso_asigna']) ? $_GET['id_proceso_asigna'] : $_SESSION['id_entity'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_user= new Tusuario($clink);

$obj_plan= new Tplantrab($clink);

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetIdResponsable(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->SetRole(NULL);

$obj_plan->SetDay(NULL);
$obj_plan->SetMonth($month);
$obj_plan->SetYear($year);

$obj_plan->SetIfEmpresarial(1);
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);

$obj_plan->toshow= 1;
/*
if ($id_proceso == $_SESSION['local_proceso_id']) $obj_plan->SetRole(_SUPERUSUARIO); 
else $obj_plan->SetRole(_PLANIFICADOR); 
*/

$obj_plan->Set();

$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj_plan->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
$cumplimiento= $obj_plan->GetCumplimiento();

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetIdResponsable(null);
$obj_plan->SetIdUsuario(null);
$obj_plan->SetRole(null);

$obj_plan->SetDay(null);
$obj_plan->SetMonth($month);
$obj_plan->SetYear($year);

$obj_plan->SetIfEmpresarial(null);
$obj_plan->toshow= 1;
$obj_plan->SetCumplimiento(null);

$obj_plan->automatic_event_status($obj_plan->toshow);

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_OUT;

$obj_signal= new Tevento_signals($clink);
$obj_signal->print_reject= $print_reject;
$obj_signal->tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN GENERAL MENSUAL", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>PLAN GENERAL DE LA EMPRESA</title>
    <?php require "inc/print_top.inc.php";?>


    <div class="page center">
        <table class="center none-border" width="100%">
            <tr>
                <td class="none-border">

                    <?php if (!empty($array_aprb)) { ?>
                        Aprobado por: <?= $array_aprb['cargo'] ?><br />
                        <span style="margin-left: 70px"><?= $array_aprb['nombre'] ?></span><br />
                        <?php if (!is_null($array_aprb['firma'])) { ?>
                        <img id="img"
                            src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>"
                            border="0" />
                        <?php } ?>
                    <?php } ?>
                </td>
            </tr>

            <tr>
                <td class="none-border">
                    <div class="center">
                        <h1>PLAN DE TRABAJO <?= $proceso ?> PARA EL MES DE <?= strtoupper($meses_array[(int)$month]) ?>
                            <?= $year ?></h1>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="none-border pull-left">
                    <h1 style="text-decoration: underline">TAREAS PRINCIPALES</h1><br />
                    <?php
                    $objetivos= $obj_plan->GetObjetivo();
                    $objetivos= textparse($objetivos, false);
                    echo $objetivos;
                    ?>
                </td>
            </tr>
        </table>

    </div>

    ` <div class="page-break"></div>


    <div class="page center">
        <?php $colspan = 4; ?>
        <table class="container-fluid center none-border">
            <thead>
                <tr>
                    <th class="plhead left" width="20px">No.</th>

                    <th class="plhead left">
                        <?php
                        $i = 0;
                        $colum = "Actividad";

                        if (!$config->hourcolum) {
                            $colum .= ($i == 0) ? ", " : " y ";
                            ++$i;
                            $colum .= "Hora";
                        }
                        if (!$config->placecolum) {
                            $colum .= ($i == 0) ? ", " : " y ";
                            ++$i;
                            $colum .= "Lugar";
                        }
                        if (!$config->datecolum) {
                            $colum .= ($i == 0) ? ", " : " y ";
                            ++$i;
                            $colum .= "Fecha";
                        }

                        echo $colum;
                        ?>
                    </th>

                    <?php if ($config->hourcolum) { ?>
                    <th class="plhead" width="40px">Hora</th>
                    <?php 
                            ++$colspan;
                    } 
                    ?>
                    <?php if ($config->placecolum) { ?>
                    <th class="plhead" width="14%">Lugar</th>
                    <?php 
                        ++$colspan;
                    } 
                    ?>
                    <?php if ($config->datecolum) { ?>
                    <th class="plhead">Fecha</th>
                    <?php 
                            ++$colspan;
                        } 
                    ?>

                    <th class="plhead" width="15%">Dirige</th>
                    <th class="plhead" width="45%">Participan</th>

                    <?php if ($config->observcolum) { ?>
                    <th class="plhead" width="20%">Observaciones sobre el cumplimiento</th>
                    <?php 
                        ++$colspan;
                    } 
                    ?>
                </tr>
            </thead>

            <tbody>
                <?php
                    $obj_plan->monthstack = $config->monthstack;

                    $obj_event= new Tevento($clink);
                    $obj_event->set_print_reject($print_reject);
                    $obj_plan->copy_in_object($obj_event);
                    
                    $obj_event->set_procesos($id_proceso);
                    
                    $array_week = array('PRIMERA', 'SEGUNDA', 'TERCERA', 'CUARTA', 'QUINTA');
                    $nweek = 0;
                    $initdayweek = 1;
                    $enddayweek = NULL;
                    $j = 0;

                    $time->SetMonth($month);
                    $time->SetYear($year);
                    $lastday = $time->longmonth();

                    for ($day = 1; $day <= $lastday; ++$day) {
                        $time->SetDay($day);
                        $weekday = $time->weekDay();

                        if ($day == 1 || $weekday == 1) {
                            $enddayweek = $initdayweek + (7 - $weekday);
                            if ($enddayweek > $lastday)
                                $enddayweek = $lastday;
                            ?>
                <tr>
                    <td colspan="<?= $colspan ?>" class="colspan plinner left" align="center">
                        <?= $array_week[$nweek++] ?> SEMANA (<?php echo $initdayweek . '-' . $enddayweek ?>)
                    </td>
                </tr>

                <?php
                    $initdayweek = ++$enddayweek;
                }
                ?>
                <tr>
                    <td colspan="<?= $colspan ?>" class="colspan_dia plinner left">
                        <?= "{$dayNames[$weekday]}  {$day}" ?>
                    </td>
                </tr>

                <?php                        
                $obj_event->listday($day);
                $cant = $obj_event->GetCantidad();

                if (!empty($cant)) {

                    //	$j= 0;
                    foreach ($obj_event->array_eventos as $evento) {
                        if ((!empty($evento['rechazado']) && ($evento['cumplimiento'] == _SUSPENDIDO || $evento['cumplimiento'] == _REPROGRAMADO)) 
                            && $print_reject == _PRINT_REJECT_NO)
                            continue;
                        ?>

                <tr>
                    <td class="plinner left"><?= ++$j ?></td>

                    <td class="plinner">
                        <?php
                        echo $evento['evento'];

                        $k = 0;
                        if (!$config->hourcolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . odbc2ampm($evento['fecha']);
                        }

                        if (!$config->placecolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . $evento['lugar'];
                        }

                        if (!$config->datecolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . $dayNames[$weekday] . " " . $day;
                        }
                        ?>
                    </td>

                    <?php if ($config->hourcolum) { ?>
                        <td class="plinner"><?= odbc2ampm($evento['fecha']) ?></td>
                    <?php } ?>
                    <?php if ($config->placecolum) { ?>
                        <td class="plinner"><?= $evento['lugar'] ?></td>
                    <?php } ?>
                    <?php if ($config->datecolum) { ?>
                        <td class="plinner"><?= $dayNames[$weekday] . " " . $day ?></td>
                    <?php } ?>

                    <td class="plinner">
                        <?php
                        $email= $obj_user->GetEmail($evento['id_responsable_asigna']);
                        if (!empty($evento['funcionario']))
                            echo $evento['funcionario'];
                        else {
                            if ($config->onlypost)
                                echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                            else
                                echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);                            
                        }    
                        ?>
                    </td>

                    <td class="plinner">
                        <?php
                        $coma= $config->sipac_format ? ";" : "";
                        $obj_event->set_user_date_ref($evento['fecha_inicio']);
                        $array = $obj_event->get_participantes($evento['id'], null, null, $id_proceso);
                        if ($array) echo "{$array}{$coma}";
                        ?>
                    </td>

                    <?php if ($config->observcolum) { ?>
                    <td class="plinner">
                        <?= !is_null($evento['memo']) ? $evento['memo'] : '&nbsp;' ?>
                    </td>
                    <?php } ?>
                </tr>

                <?php } } } ?>

            </tbody>
        </table>


        <div class="page center" style="margin-top: 40px">
            <table>
                <tr>
                    <td width="70%" class="none-border">
                        &nbsp;
                    </td>
                    <td class="none-border">
                        <div class="container-fluid pull-right">
                            <strong>Elaborado por:</strong><br />
                            <?=$cargo_print?><br /><?=$usuario_print?><br /><?=$proceso_print?><br />
                            <img id="img"
                                src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$_SESSION['id_usuario']?>"
                                border="0" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <?php require_once "inc/print_bottom.inc.php";?>