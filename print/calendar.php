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

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : _USER_SYSTEM;

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

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

$obj_plan->SetIfEmpresarial(null);
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
            <table class="center none-border" border="0" width="100%">
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
                            <?=$usuario?> <?= $cargo ? ", $cargo" :  null?>
                        </div>
                    </td>
                </tr>

                <tr class="none-border">
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

    `   <div class="page-break"></div>

        <div class="page center">
            <table class="container-fluid center none-border">
                <?php
                $time->SetDay(1);
                $time->SetYear($year);
                $time->SetMonth($month);
                $firstday= $time->weekDay();
                $lastday= $time->longmonth();
                $dd= 0;
                $tdd= 0;
                ?>

                <tr height="20px">
                    <?php
                    for ($k= 1; $k < $firstday; ++$k) {
                        $class= 'none-border';
                    ?>
                        <td class="none-border"></td>
                    <?php
                    }

                    for ($k= $firstday; $k < 8; ++$k) {
                        $time->SetDay(++$dd);

                        if ($k == 7)
                            $class='sunday';
                        else {
                            $class= ' ';
                            if ($k == $firstday) 
                                $class.= ' first';
                        }
                    ?>
                        <td class='head <?=$class?>'><?= $dayNames[$k].'  '.$dd; ?></td>
                    <?php   } ?>
		        </tr>

                <tr>
                    <?php for ($i= 1; $i < 8; ++$i) {
                    if ($i >= $firstday) {
                        $tdd++;
                        $cellclass= 'plinner';

                        if ($i == 7)
                            $cellclass.= ' sunday';
                        if ($i == $firstday)
                            $cellclass.= ' first left';

                    } else {
                        $cellclass= 'none-border';
                    }
                    ?>
                        <td class='<?=$cellclass?>'>
                            <?php
                            if ($tdd >= 1) {
                                $obj->listday($tdd); 
                                $array= $obj->array_eventos;
                                $obj_signal->list_day($array, _PRINT_IND);
                            }
                            ?>
                            <br /><br />
                        </td>
                    <?php } ?>
                </tr>

                <?php
                $col= 1;

                for ($k= $dd; $k < $lastday; ++$k) {
                    if ($col == 1)
                        echo "<tr>";
                    $time->SetDay(++$dd);

                    if ($col == 7)
                        $class='sunday';
                    else
                        $class= null;

                    if ($col == 1) 
                        $class= ' left';
                    ?>
                        <td class='head <?=$class?>'><?= $dayNames[$col].'  '.$dd ?></td>
                    <?php
                        ++$col;
                        if ($col == 8) {
                            $col= 1; echo "</tr><tr>";

                            for ($i= 1; $i < 8; ++$i) {
                                $tdd++;

                                if ($i == 7)
                                    $class='sunday';
                                else {
                                    $class= 'plinner';
                                    if ($i == 1) 
                                        $class.= ' left';
                                }
                        ?>
                        <td class='<?=$class?>'>
                            <?php
                            $array= $obj->listday($tdd);
                            $array= $obj->array_eventos;
                            $obj_signal->list_day($array, _PRINT_IND);
                            ?>
                            <br /><br />
                        </td>
                        <?php
                            }

                            $col= 1; 
                            echo "</tr>";
                        }
                    }

                    for ($k= $col; $k < 8; ++$k)
                        echo "<td class='none-border'></td>";

                    if ($col < 8) {
                        echo "</tr><tr>";

                        for ($k= 1; $k < 8; ++$k) {
                            if ($tdd < $lastday) {
                                $cellclass= 'plinner';
                                if ($k == 7)
                                    $class='sunday in';
                                if ($k == 1)
                                    $cellclass.= ' left';
                            } else {
                                $cellclass= 'none-border';
                                if ($k == 7)
                                    $class= null;
                            }

                            $tdd++;
                        ?>
                        <td class='<?php echo $cellclass; if (!empty($class)) echo ' '.$class; ?>'>
                            <?php
                            if ($tdd <= $lastday) {
                                $array= $obj->listday($tdd);
                                $array= $obj->array_eventos;
                                $obj_signal->list_day($array, _PRINT_IND);
                            }
                            ?>
                            <br /><br />
                        </td>

                        <?php
                        }

                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <div class="container-fluid page margin-bottom: 30px;">
            <div class="pull-left col-12">
                <strong>Elaborado por:</strong><br />
                <?=$usuario?> <?php if ($cargo) echo "<br/> {$cargo}"?><br />
                <?php if ($firma['name']) { ?>
                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_usuario?>" border="0" />
                <?php } ?>
            </div>
        </div>
        
        <br/><br/>
        <?php require_once "inc/print_bottom.inc.php";?>
