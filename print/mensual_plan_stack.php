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

require_once "../php/class/base_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$_SESSION['debug']= 'no';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'list' || $action == 'edit')
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);

$year= !empty($_GET['year']) ?$_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_proceso_asigna= !empty($_GET['id_proceso_asigna']) ? $_GET['id_proceso_asigna'] : $_SESSION['id_entity'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;
$monthstack= !is_null($_GET['monthstack']) ? $_GET['monthstack'] : 1;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_plan= new Tplantrab($clink);
$obj_user= new Tusuario($clink);

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetIdResponsable(null);
$obj_plan->SetIdUsuario(null);
$obj_plan->SetRole(null);

$obj_plan->SetDay(null);
$obj_plan->SetMonth($month);
$obj_plan->SetYear($year);

$obj_plan->SetIfEmpresarial(1);
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);

$obj_plan->toshow= 1;
$obj_plan->monthstack= $monthstack;

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

$obj_signal= new Tevento_signals($clink);
$obj_signal->print_reject= $print_reject;
$obj_signal->tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;

$obj= new Tevento($clink);
$obj_plan->copy_in_object($obj);
$obj->set_print_reject($print_reject);

$obj->set_procesos($id_proceso);

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
            <?php if (!empty($array_aprb)) { ?>
            <tr>
                <td class="none-border">
                    Aprobado por: <?= $array_aprb['cargo'] ?><br />
                    <span style="margin-left: 70px"><?= $array_aprb['nombre'] ?></span><br />
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>"
                        border="0" />
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td class="none-border">
                    <div class="center">
                        <h1>PLAN DE TRABAJO DE <?= $proceso ?> PARA EL MES DE
                            <?= strtoupper($meses_array[(int)$month]) ?> <?= $year ?></h1>
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

        <div class="page-break"></div>

        <?php $colspan = 4; ?>
        <table class="container-fluid center none-border">
            <thead>
                <tr>
                    <th class="plhead left" width="20px">No.</th>

                    <th class="plhead" style="min-width: 150px">
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
                    <th class="plhead" style="min-width: 100px">Lugar</th>
                    <?php
                        ++$colspan;
                    } ?>

                    <?php if ($config->datecolum) { ?>
                    <th class="plhead" width="30px">Fecha</th>
                    <?php
                        ++$colspan;
                    }
                    ?>

                    <th class="plhead" style="min-width: 100px">Dirige</th>
                    <th class="plhead">Participan</th>

                    <?php if ($config->observcolum) { ?>
                    <th class="plhead">Observaciones sobre el cumplimiento</th>
                    <?php
                        ++$colspan;
                    }
                    ?>
                </tr>
            </thead>

            <tbody>

                <?php

                $obj->set_print_reject(-1);
                $obj->listmonth();
                $cant = $obj->GetCantidad();

                $array_eventos = $obj->array_eventos;        
                $obj_signal->array_status_eventos_ids= $obj->array_status_eventos_ids;
                $show= $obj_signal->get_status_intervals($array_eventos);
                $cant_print_reject= $obj_signal->cant_print_reject;

                if (empty($cant) || !$show) {
                    ?>
                <tr>
                    <td class="plinner left">&nbsp;</td>
                    <td class="plinner"></td>
                    <?php if ($config->hourcolum) { ?>
                    <td class="plinner"></td>

                    <?php if (!$config->datecolum) { ?>
                    <td class="plinner"></td>
                    <?php } ?>
                    <?php if ($config->placecolum) { ?>
                    <td class="plinner"></td>
                    <?php } ?>
                    <td class="plinner"></td>
                    <td class="plinner"></td>
                    <?php if ($config->observcolum) { ?>
                    <td class="plinner"></td>
                    <?php } ?>
                </tr>
                <?php } ?>

                <?php
                    } else {

                        $j = 0;
                        foreach ($obj->array_eventos as $evento) {
                            if ((!empty($evento['rechazado']) && ($evento['cumplimiento'] == _SUSPENDIDO || $evento['cumplimiento'] == _REPROGRAMADO)) 
                                && $print_reject == _PRINT_REJECT_NO)
                                continue;
                            ?>

                <tr>
                    <td class="plinner left"><?= ++$j ?></td>

                    <td class="plinner">
                        <?php
                        echo $evento['evento'] . "<br />";

                        $k = 0;
                        if (!$config->hourcolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . odbc2ampm($evento['fecha']);
                        }

                        if (!$config->datecolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . "Días:";
                            build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject);
                        }

                        if (!$config->placecolum) {
                            $br = ($k == 0) ? "<br />" : ", ";
                            ++$k;
                            echo $br . $evento['lugar'];
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
                    <td class="plinner">
                        <?php build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject) ?>
                    </td>
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
                        $obj->set_user_date_ref($evento['fecha_inicio']);
                        $array = $obj->get_participantes($evento['id'], null, null, $id_proceso);
                        if ($array) 
                            echo "{$array}{$coma}";
                        ?>
                    </td>

                    <?php if ($config->observcolum) { ?>
                    <td class="plinner"><?php echo!is_null($evento['memo']) ? $evento['memo'] : '&nbsp;' ?></td>
                    <?php } ?>
                </tr>

                <?php } } ?>
            </tbody>
        </table>

        <?php require_once "inc/print_bottom.inc.php";?>