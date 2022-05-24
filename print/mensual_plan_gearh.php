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
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/evento_numering.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$_SESSION['debug']= 'no';

$action= !empty($_GET['action']) ? $_GET['action'] : $action= 'list';

if ($action == 'list' || $action == 'edit') 
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);

$year= !empty($_GET['year']) ?$_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_proceso_asigna= !empty($_GET['id_proceso_asigna']) ? $_GET['id_proceso_asigna'] : $_SESSION['id_entity'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;
$if_numering= !empty($_GET['if_numering']) ? $_GET['if_numering'] : null;
$monthstack= !is_null($_GET['monthstack']) ? $_GET['monthstack'] : 0;

$time= new TTime();
$time->SetMonth($month);
$time->SetYear($year);
$lastday= $time->longmonth();

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_tipo= new Ttipo_evento($clink);
$obj_user= new Tusuario($clink);

$obj_plan= new Tplantrab($clink);

$obj_plan->SetYear($year);
$obj_plan->SetMonth($month);
$obj_plan->SetIdUsuario(NULL);

$obj_plan->SetIdProceso($id_proceso);
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
$obj_plan->SetIdResponsable(NULL);
$obj_plan->SetIdUsuario(NULL);

$obj_plan->SetDay(NULL);
$obj_plan->SetMonth($month);
$obj_plan->SetYear($year);

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->set_id_proceso_asigna($id_proceso_asigna);
$obj_plan->SetIfEmpresarial(NULL);
$obj_plan->toshow= 1;
$obj_plan->SetRole(NULL);
$obj_plan->SetCumplimiento(null);

$obj_plan->set_print_reject($print_reject);

$_if_numering= $obj_plan->GetIfNumering();
$_if_numering= is_null($_if_numering) ? _ENUMERACION_MANUAL : $_if_numering;
$if_teventos= false;
$if_treg_evento= false;

if ((is_null($if_numering) && $_if_numering != _ENUMERACION_MANUAL) 
    || (!is_null($if_numering) && $if_numering != $_if_numering)) {
    $obj_num= new Tevento_numering($clink);

    $obj_num->SetIdPlan($id_plan);
    $obj_num->SetYear($year);
    $obj_num->SetMonth($month);
    $obj_num->SetIfEmpresarial(NULL);
    $obj_num->SetIdProceso($id_proceso);
    $obj_num->set_id_proceso_asigna($id_proceso_asigna);
    $obj_num->toshow= $obj_plan->toshow;
    $obj_num->signal= $signal;
    $obj_num->SetIfNumering($if_numering);

    $obj_num->build_numering(); 
    $if_teventos= $obj_num->if_teventos;
    $if_treg_evento= $obj_num->if_treg_evento;
}

$obj_plan->create_temporary_treg_evento_table= true;
$obj_plan->if_teventos= $if_teventos;
$obj_plan->if_treg_evento= $if_treg_evento;
$obj_plan->SetIfNumering($if_numering);

$obj_plan->automatic_event_status($obj_plan->toshow);

$obj_plan->monthstack= $monthstack;

$obj_signal= new Tevento_signals($clink);
$obj_signal->print_reject= $print_reject;

$obj_tipo1= new Ttipo_evento($clink);
$obj_tipo2= new Ttipo_evento($clink);
$obj_tipo3= new Ttipo_evento($clink);

$obj_tipo1->SetIdProceso($id_proceso);
$obj_tipo1->set_id_proceso_asigna($id_proceso_asigna);
// $obj_tipo1->SetYear($year);
$obj_tipo2->SetIdProceso($id_proceso);
$obj_tipo2->set_id_proceso_asigna($id_proceso_asigna);
// $obj_tipo2->SetYear($year);
$obj_tipo3->SetIdProceso($id_proceso);
$obj_tipo3->set_id_proceso_asigna($id_proceso_asigna);
// $obj_tipo3->SetYear($year);

$obj= new Tevento($clink);
$obj_plan->copy_in_object($obj);

$obj->SetIfNumering($if_numering);
$obj->SetIdProceso($id_proceso);
$obj->set_id_proceso_asigna($id_proceso_asigna);
$obj->set_print_reject($print_reject);

$obj->set_procesos($id_proceso);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN GENERAL MENSUAL", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>PLAN GENERAL MENSUAL</title>

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
    </style>

    <div class="page center">
        <table class="center none-border" width="100%">
            <?php if (!empty($array_aprb)) { ?>
            <tr>
                <td class="none-border">
                    <strong>Aprobado por:</strong> <?= $array_aprb['cargo'] ?><br />
                    <span style="margin-left: 70px"><?= $array_aprb['nombre'] ?></span><br />
                    <?php if (!empty($array_aprb['firma'])) { ?>
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>"
                        border="0" />
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td class="none-border">
                    <div class="center">
                        <h1>PLAN GENERAL MENSUAL <br /> <?= strtoupper($meses_array[(int)$month]) ?> <?= $year ?></h1>
                        <br />
                        <?=$usuario?> <?php if ($cargo) echo ", {$cargo}"?>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="none-border pull-left">
                    <h1 style="text-decoration: underline">TAREAS PRINCIPALES</h1><br />
                    <p> <?= textparse($obj_plan->GetObjetivo()) ?></p>
                    <br />
                </td>
            </tr>
        </table>
    </div>

    ` <div class="page-break"></div>

    <div class="page center">

        <?php $colspan= 4; ?>
        <table class="container-fluid center none-border">
            <thead>
                <tr>
                    <th class="plhead left" width="20px">No.</th>

                    <th class="plhead"><?php
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
                    <th class="plhead">Hora</th>
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

                    <th class="plhead" width="15%">Dirigente</th>
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
                    mensual_plan(null, 1, 0, null, null);
    
                    for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) {
                        $ktotal= 0;
                        $j= $i-1;

                        $print_bar0= false;
                        $print_bar1= false;
                        $print_bar2= false;

                        $header0= null;
                        $header1= null;
                        $header2= null;
                        
                        $header0=  number_format_to_roman($j).'. '.$tipo_actividad_array[$i];
                        $num_rows= mensual_plan($header0, $i, 0, null, null);

                        $result1= $obj_tipo1->listar($i, 0);
                        $k= 0;
                        while ($row1= $clink->fetch_array($result1)) {
                            $print_bar1= false;
                            $header1= (!empty($row1['numero']) ? "{$row1['numero']}) " : "{$j}.{$k}) "). " {$row1['nombre']}";
                            $num_rows1= mensual_plan($header0, $i, $row1['id'], $header1, null);

                            $result2= $obj_tipo2->listar($i, $row1['id']);
                            while ($row2= $clink->fetch_array($result2)) {
                                $print_bar2= false;
                                $header2= "{$row2['numero']}) {$row2['nombre']}";
                                $num_rows2= mensual_plan($header0, $i, $row2['id'], $header1, $header2);
                            }
                        }
                    }    
                    ?>
            </tbody>

        </table>

    </div>

    <?php require_once "inc/print_bottom.inc.php";?>


    <?php
function mensual_plan($header0, $empresarial, $id_tipo_evento, $header1= null, $header2= null) {    
    global $config;
    global $clink;
    global $obj;
    global $ktotal;
    global $if_numering;
    
    global $print_bar0;
    global $print_bar1;
    global $print_bar2;
    
    global $id_proceso;
    
    global $dayNames;
    global $monthstack;

    $obj_user= new Tusuario($clink);
        
    $obj_tipo= new Tbase($clink);
    $obj->listmonth($empresarial, $id_tipo_evento, null);
    $cant= $obj->GetCantidad();  
    ?>

    <?php if ($cant > 0 && !$print_bar0) { ?>
    <tr>
        <td colspan="8" class="colspan plinner left top">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header0?></div>
        </td>
    </tr>
    <?php
        $print_bar0= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header1) && !$print_bar1) { ?>
    <tr>
        <td colspan="8" class="colspan plinner left top">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header1?></div>
        </td>
    </tr>
    <?php 
        $print_bar1= true;
    } 
    ?>

    <?php if ($cant > 0 && !empty($header2) && !$print_bar2) { ?>
    <tr>
        <td colspan="8" class="colspan plinner left">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header2?></div>
        </td>
    </tr>
    <?php 
        $print_bar2= true;
    } 
    ?>

    <?php
    $j= 0;
    foreach ($obj->array_eventos as $evento) {
        ++$ktotal;
        $memo= textparse($evento['memo'], false);

        ++$j;
        $numero=  $if_numering == _ENUMERACION_MANUAL || is_null($if_numering) ? $evento['numero'] : $evento['numero_tmp'];
        $numero= !empty($numero) ? $numero : null; 
        if (!empty($numero)) 
            $numero.= !empty($evento['numero_plus']) ? ".{$evento['numero_plus']}" : null;        
    ?>

    <tr>
        <td class="plinner left">
            <?php
                ++$j;
                $numero=  $if_numering == _ENUMERACION_MANUAL || is_null($if_numering) ? $evento['numero'] : $evento['numero_tmp'];
                $numero= !empty($numero) ? $numero : $j; 
                if (!empty($numero)) 
                    $numero.= !empty($evento['numero_plus']) ? ".{$evento['numero_plus']}" : null;                 
                
                echo $numero
                ?>
        </td>
        <!-- <td class="plinner"><?php //++$j; echo $j;?></td> -->
        <?php // $obj_event->funcion_temporal_eti(!empty($evento['id_evento']) ? $evento['id_evento'] : $evento['id'], $j); ?>

        <td class="plinner">
            <?php
                echo stripslashes($evento['evento']);
                if (!$config->hourcolum) echo "<br />".odbc2ampm($evento['fecha']);
                $br= !$config->hourcolum ? "<br />" : ' ';
                if (!$config->placecolum) echo $br.stripslashes($evento['lugar']);
                ?>
        </td>

        <?php if ($config->hourcolum) { ?>
            <td class="plinner"><?=odbc2ampm($evento['fecha'])?></td>
        <?php } ?>
        <?php if ($config->placecolum) { ?>
            <td class="plinner"><?=stripslashes($evento['lugar'])?></td>
        <?php } ?>

        <?php if ($config->datecolum) { ?>
        <td class="plinner left">
            <?php 
            echo $dayNames[(int)date('N', strtotime($evento['fecha']))].', '.(int)date('d', strtotime($evento['fecha']));  
            ?>
        </td>
        <?php } ?>

        <td class="plinner">
            <?php
            $email= $obj_user->GetEmail($evento['id_responsable_asigna']);
            if (!empty($evento['funcionario']))
                echo $evento['funcionario']};
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
                $array= $obj->get_participantes($evento['id'], null, null, $id_proceso);
                if ($array) 
                    echo "{$array}{$coma}";
                
                $origen_data= $obj_tipo->GetOrigenData('participant', $evento['origen_data_asigna']);
                if (!is_null($origen_data)) 
                    echo "<br /> ".merge_origen_data_participant($origen_data).$coma;
                ?>
        </td>

        <?php if ($config->observcolum) { ?><td class="plinner"><?=$memo?></td><?php } ?>
    </tr>

    <?php
    }

    return $j;
}
?>