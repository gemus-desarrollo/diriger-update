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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/base_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$year= $_GET['year'];
$month= $_GET['month'];
$id_usuario= $_GET['id_usuario'];

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : 0;

$obj_signal= new Tevento_signals($clink);
$obj_signal->print_reject= $print_reject;

$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($id_usuario);
$obj_user->Set();
$usuario= $obj_user->GetNombre();
$cargo= $obj_user->GetCargo();
$id_proceso= $obj_user->GetIdProceso();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

$obj_plan= new Tplantrab($clink);
$obj_plan->SetMonth($month);
$obj_plan->SetYear($year);

$obj_plan->SetIfEmpresarial(NULL);
$obj_plan->SetIdUsuario($id_usuario);
$obj_plan->SetIdResponsable($id_responsable);
$obj_plan->SetIdProceso(null);
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL);

$obj_plan->Set();

$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj_plan->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
$cumplimiento= $obj_plan->GetCumplimiento();

$email= $obj_user->GetEmail($id_usuario);

$obj_plan->SetCumplimiento(null);
$obj_plan->set_cronos(date('Y-m-d H:i:s'));

$obj_plan->SetIfEmpresarial(NULL);
$obj_plan->SetIdUsuario($id_usuario);

$obj_plan->SetYear($year);
$obj_plan->SetMonth($month);

$obj_plan->automatic_event_status(0);

$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($_SESSION['id_usuario']);
$obj_user->Set();
$usuario_print= $obj_user->GetNombre();
$cargo_print= $obj_user->GetCargo();
$id_proceso_print= $obj_user->GetIdProceso();
$firma= $obj_user->GetParam();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso_print);
$obj_prs->Set();
$proceso_print= $obj_prs->GetNombre();

$obj= new Tevento($clink);
$obj_plan->copy_in_object($obj);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN DE TRABAJO INDIVIDUAL", "Corresponde a periodo mes/año: $mont/$year Usuario: {$email['nombre']}");
?>

<html>
    <head>
        <title>PLAN DE TRABAJO INDIVIDUAL</title>

         <?php require "inc/print_top.inc.php";?>

            <style type="text/css">
                .head {
                    border: 1px solid #000;
                    background: #f9f9f9;
                    color: #000;
                    font-weight: bold;
                    padding: 2px;
                    min-width: 120px;
                    min-height: 0.9in;
                }
                .sunday {
                    border: 1px solid #000;
                }
                #objetivos td {
                    border: none;
                }
            </style>

        <div class="page center">
            <table class="center none-border" width="100%">
                <tr>
                    <td class="none-border">
                        <?php if (!empty($array_aprb)) { ?>
                            <strong>Aprobado por:</strong> <?= $array_aprb['cargo'] ?><br />
                            <span style="margin-left: 70px"><?= $array_aprb['nombre'] ?></span><br/>
                            <?php if (!is_null($array_aprb['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td class="none-border">
                        <div class="center">
                            <h1>PLAN DE TRABAJO INDIVIDUAL PARA EL MES DE <?= strtoupper($meses_array[(int)$month]) ?> <?= $year ?></h1>
                            <br />
                            <?=$usuario?> <?php if ($cargo) echo ", {$cargo}"?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td id="objetivos" class="none-border pull-left">
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

        <?php
        $time->SetDay(1);
        $time->SetYear($year);
        $time->SetMonth($month);
        $firstday= $time->weekDay();
        $lastday= $time->longmonth();
        $d= 0;
        $dd= 0;
        $tdd= 0;
        ?>

       <?php for ($tdd=0; $tdd <= $lastday;) {
           if ($tdd >= $lastday) {
               break;
           }
        ?>
        `   <div class="page-break"></div>

            <div class="page center">
                <h1>Semana <?= number_format_to_roman(++$d)?></h1>
                <table class="container-fluid center none-border">
                    <tr>
                    <?php
                    for ($k= 1; $k < 8; ++$k) {
                        if ($dd > $lastday) 
                            continue;
                    ?>
                        <?php if (($dd == 0 && $k < $firstday) || $dd >= $lastday) { ?>
                            <td class="none-border"></td>
                        <?php } else {
                            ++$dd;
                            if ($k == 7) 
                                $class='sunday';
                            else {
                                $class= ' ';
                                if ($k == $firstday || $k == 1) 
                                    $class.= ' first left';
                            }
                        ?>
                            <td class='head <?=$class?>'><?= $dayNames[$k].'  '.$dd; ?></td>
                    <?php } } ?>
                    </tr>

                    <tr>
                    <?php for ($k= 1; $k < 8; ++$k) {
                        if ($tdd > $lastday) 
                            continue;
                    ?>
                        <?php if (($tdd == 0 && $k < $firstday) || $tdd >= $lastday) { ?>
                            <td class="none-border"></td>
                        <?php } else {
                            ++$tdd;
                            $cellclass= 'plinner';

                            if ($k == 7) 
                                $cellclass.= ' sunday';
                            if ($k == $firstday || $k == 1) 
                                $cellclass.= ' first left';
                        ?>

                        <td class='<?=$cellclass?>'>
                            <?php
                            if ($tdd >= 1 && $tdd <= $lastday) {
                                $obj->listday($tdd);
                                $array= $obj->array_eventos;
                                $obj_signal->list_day($array, _PRINT_IND);
                            }
                            ?>
                            <br /><br />
                        </td>
                    <?php } } ?>
                    </tr>
                </table>
            </div>
        <?php } ?>

        <div class="container-fluid page" style="margin-top: 20px;">
            <div class="pull-left">
                <strong>Elaborado por:</strong><br />
                <?=$usuario?> <?php if ($cargo) echo "<br/> {$cargo}"?><br />

                <?php if ($firma['name']) { ?>            
                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_usuario?>" border="0" />
                <?php } ?>
            </div>

        </div>

    <?php require_once "inc/print_bottom.inc.php";?>
