<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/base_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/evento_numering.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../tools/archive/php/class/organismo.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$signal= "anual_plan";
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list' || $action == 'edit') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$year= $_GET['year'];
$if_numering= !empty($_GET['if_numering']) ? $_GET['if_numering'] : null;
$id_proceso= $_GET['id_proceso'];
$id_proceso_asigna= !empty($_GET['id_proceso_asigna']) ? $_GET['id_proceso_asigna'] : $_SESSION['id_entity'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_signal= new Tevento_signals($clink);
$obj_signal->tipo_plan= _PLAN_TIPO_ACTIVIDADES_ANUAL;

$obj_user= new Tusuario($clink);

$obj_plan= new Tplantrab($clink);

$obj_plan->SetYear($year);
$obj_plan->SetMonth(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->signal= $signal;
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_ANUAL);

$obj_plan->set_id_proceso_asigna($id_proceso_asigna);
$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetIfEmpresarial(2);

$obj_plan->Set();
$id_plan= $obj_plan->GetIdPlan();

$objetivos= $obj_plan->GetObjetivo();
$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj_plan->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
$cumplimiento= $obj_plan->GetCumplimiento();

$obj_plan->SetIdResponsable(null);
$obj_plan->SetIdUsuario(null);
$obj_plan->SetRole(null);
$obj_plan->SetIfEmpresarial(null);
$obj_plan->toshow= 2;

$_if_numering= $obj_plan->GetIfNumering();
$_if_numering= is_null($_if_numering) ? _ENUMERACION_MANUAL : $_if_numering;
$if_teventos= false;
$if_treg_evento= false;

if ((is_null($if_numering) && $_if_numering != _ENUMERACION_MANUAL)
    || (!is_null($if_numering) && $if_numering != $_if_numering)) {
    $obj_num= new Tevento_numering($clink);

    $obj_num->SetIdPlan($id_plan);
    $obj_num->SetYear($year);
    $obj_num->SetMonth(null);
    $obj_num->SetIfEmpresarial(null);
    $obj_num->SetIdProceso($id_proceso);
    $obj_num->set_id_proceso_asigna($id_proceso_asigna);
    $obj_num->toshow= $obj_plan->toshow;
    $obj_num->signal= $signal;
    $obj_num->SetIfNumering($if_numering);

    $obj_num->build_numering();
    $if_teventos= $obj_num->if_teventos;
    $if_treg_evento= $obj_num->if_treg_evento;
}

$obj_plan->create_temporary_treg_evento_table= false;
$obj_plan->if_teventos= $if_teventos;
$obj_plan->if_treg_evento= $if_treg_evento;
$obj_plan->SetIfNumering($if_numering);

$obj_plan->automatic_event_status($obj_plan->toshow);

$obj_tipo1= new Ttipo_evento($clink);
// $obj_tipo1->SetYear($year);
$obj_tipo1->SetIdProceso($id_proceso);
$obj_tipo1->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo2= new Ttipo_evento($clink);
// $obj_tipo2->SetYear($year);
$obj_tipo2->SetIdProceso($id_proceso);
$obj_tipo2->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo3= new Ttipo_evento($clink);
// $obj_tipo3->SetYear($year);
$obj_tipo3->SetIdProceso($id_proceso);
$obj_tipo3->set_id_proceso_asigna($id_proceso_asigna);

$obj= new Tevento($clink);
$obj_plan->copy_in_object($obj);

$obj->SetIfNumering($if_numering);
$obj->SetIdProceso($id_proceso);
$obj->set_id_proceso_asigna($id_proceso_asigna);
$obj->SetYear($year);

$obj->if_tidx= $obj_plan->if_tidx;
$obj->tidx_array= $obj_plan->tidx_array;
$obj->tidx_array_evento= $obj_plan->tidx_array_evento;
$obj->tidx_array_auditoria= $obj_plan->tidx_array_auditoria;
$obj->tidx_array_tarea= $obj_plan->tidx_array_tarea;

if ($config->use_anual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id']) {
    $obj_org= new Torganismo($clink);
    $obj_org->SetYear($year);

    $result_org= $obj_org->listar(false, true);
    $cant_org= $obj_org->GetCantidad();    
}

$obj->set_procesos($id_proceso);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN ANUAL", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>PLAN ANUAL</title>

         <?php require "inc/print_top.inc.php";?>

        <div class="page center">
            <table class="center none-border" width="100%">
                <?php if (!empty($array_aprb)) { ?>
                <tr>
                    <td class="none-border">
                        Aprobado por: <?=$array_aprb['cargo']?><br />
                        <span style="margin-left: 70px"><?=$array_aprb['nombre']?></span><br/>
                        <?php if (!is_null($array_aprb['firma'])) {?>
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>" border="0" />
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <td class="center none-border">
                        <h1>II. PLAN DE ACTIVIDADES PRINCIPALES DE <?=$proceso?> PARA EL AÑO <?=$year?></h1>
                    </td>
                </tr>

                <tr>
                    <td class="none-border pull-left">
                        <h1 style="text-decoration: underline">OBJETIVOS DEL TRABAJO</h1><br />
                        <?php
                        $objetivos= textparse($objetivos, false);
                        echo $objetivos;
                        ?>
                        <br/>
                    </td>
                </tr>
            </table>
        </div>

            <div class="page-break"></div>

            <div class="page center">
                <?php $colspan= 16; ?>
                <table class="center" width="100%">
                  <thead>
                      <tr>
                        <th rowspan="2" class="plhead left">No.</th>
                        <th rowspan="2" class="plhead" style="min-width: 200px;">
                            <?php
                            $i= 0;
                            $colum= "Actividad";

                            if (!$config->hourcolum_y) {
                                if ($i == 0) $colum.= ", ";
                                $colum.= "Hora";
                            }
                            if (!$config->placecolum_y) {
                                $colum.= ($i == 0) ? ", " : " y ";
                                $colum.= "Lugar";
                            }

                            echo $colum;
                            ?>
                        </th>

                        <?php if ($config->hourcolum_y) { ?>
                            <th rowspan="2" class="plhead" width="40px">Hora</th>
                        <?php 
                            ++$colspan; 
                        }
                        ?>
                        <?php if ($config->placecolum_y) { ?>
                            <th rowspan="2" class="plhead" style="min-width: 100px">Lugar</th>
                        <?php 
                            ++$colspan; 
                        } 
                        ?>

                        <th colspan="12" class="plhead">Meses</th>
                        <th rowspan="2" class="plhead" style="min-width: 150px;">Dirige</th>
                        <th rowspan="2" class="plhead" style="min-width: 200px;">Participantes</th>
                        <?php if ($config->observcolum_y) { ?>
                            <th rowspan="2" class="plhead top">Observaciones sobre el cumplimiento</th>
                        <?php 
                            ++$colspan; 
                        } 
                        ?>
                        <?php if ($config->use_anual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id']) { ?>
                            <th colspan="<?=$cant_org?>>" class="plhead" width="20%">Instituciones y Organismos involucrados</th>
                        <?php } ?>
                    </tr>

                    <tr>
                        <th class="plhead month">E</th>
                        <th class="plhead month">F</th>
                        <th class="plhead month">M</th>
                        <th class="plhead month">A</th>
                        <th class="plhead month">M</th>
                        <th class="plhead month">J</th>
                        <th class="plhead month">J</th>
                        <th class="plhead month">A</th>
                        <th class="plhead month">S</th>
                        <th class="plhead month">O</th>
                        <th class="plhead month">N</th>
                        <th class="plhead month">D</th>

                        <?php 
                        if ($config->use_anual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id']) {
                            while ($row_org= $clink->fetch_array($result_org)) {
                        ?>
                            <th class="plhead month"><?=$row_org['codigo']?></th>
                        <?php
                            }
                        } 
                        ?>                        
                    </tr>
                </thead>

                <tbody>
                    <?php
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
                        $num_rows= anual_plan($header0, $i, 0, null, null);

                        $result1= $obj_tipo1->listar($i, 0);
                        $k= 0;
                        while ($row1= $clink->fetch_array($result1)) {
                            $print_bar1= false;
                            $header1= (!empty($row1['numero']) ? "{$row1['numero']}) " : "{$j}.{$k}) "). " {$row1['nombre']}";
                            $num_rows1= anual_plan($header0, $i, $row1['id'], $header1, null);

                            $result2= $obj_tipo2->listar($i, $row1['id']);
                            while ($row2= $clink->fetch_array($result2)) {
                                $print_bar2= false;
                                $header2= "{$row2['numero']}) {$row2['nombre']}";
                                $num_rows2= anual_plan($header0, $i, $row2['id'], $header1, $header2);
                            }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        $obj_prs= new Tproceso($clink);
        $obj_prs->Set($id_proceso);
        $id_responsable= $obj_prs->GetIdResponsable();
        $nombre_prs= $obj_prs->GetNombre();

        $obj_user= new Tusuario($clink);
        $email= $obj_user->GetEmail($id_responsable);
        ?>

        <div class="page" style="margin-top: 60px;">
            <table>
                <tr>
                    <td class="none-border" width="70%"></td>
                    <td class="none-border">
                        <div class="container-fluid pull-right">
                            <strong>Elaborado por:</strong><br />
                            <?=$nombre_prs?><br />
                            <?=$email['nombre']?><br />
                            <?=$email['cargo'] ? "{$email['cargo']}<br />" : ""?>
                            <?php if (!is_null($email['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_responsable?>" border="0" />
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <?php require "inc/print_bottom.inc.php";?>

<?php
function anual_plan($header0, $empresarial, $id_tipo_evento, $header1= null, $header2= null) {
    global $config;
    global $clink;
    global $signal;
    global $obj;
    global $ktotal;

    global $print_bar0;
    global $print_bar1;
    global $print_bar2;

    global $id_proceso;
    global $if_numering;

    global $obj_org;
    global $result_org;
    global $cant_org;

    $obj_user= new Tusuario($clink);
    
    $obj_tipo= new Tbase($clink);
    $obj->listyear($empresarial, $id_tipo_evento, false);
    $cant= $obj->GetCantidad();

    if (empty($cant)) 
        return 0;

    $colspan= 19 + $cant_org;
    ?>

    <?php if ($cant > 0 && !$print_bar0) { ?>
        <tr>
            <td colspan="<?=$colspan?>" class="colspan plinner left top">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header0?></div>
            </td>
        </tr>
    <?php
        $print_bar0= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header1) && !$print_bar1) { ?>
        <tr>
            <td colspan="<?=$colspan?>" class="colspan plinner left top">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header1?></div>
            </td>
        </tr>
    <?php
        $print_bar1= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header2) && !$print_bar2) { ?>
        <tr>
            <td colspan="<?=$colspan?>" class="colspan plinner left">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header2?></div>
            </td>
        </tr>
    <?php
        $print_bar2= true;
    }
    ?>

    <?php
    $j= 0;
    $obj->sort_eventos();
    foreach ($obj->array_eventos as $evento) {
        ++$ktotal;
        $memo= textparse($evento['memo'], false);

        $numero=  $if_numering == _ENUMERACION_MANUAL || is_null($if_numering) ? $evento['numero'] : $evento['numero_tmp'];
        $numero= !empty($numero) ? $numero : null;
        if (!empty($evento['numero']))
            $numero.= !empty($evento['numero_plus']) ? ".{$evento['numero_plus']}" : null;
    ?>

        <tr>
            <td class="plinner left"><?= !empty($numero) ? $numero : ++$j ?></td>
            <!-- <td class="plinner"><?=++$j?></td> -->
            <?php // $obj_event->funcion_temporal_eti(!empty($evento['id_evento']) ? $evento['id_evento'] : $evento['id'], $j); ?>

            <td class="plinner">
                <?php
                echo stripslashes($evento['evento']);
                if (!$config->hourcolum_y) 
                    echo "<br />".odbc2ampm($evento['fecha']);
                $br= !$config->hourcolum_y ? "<br />" : ' ';
                if (!$config->placecolum_y) 
                    echo $br.stripslashes($evento['lugar']);
                ?>
            </td>

            <?php if ($config->hourcolum_y) { ?>
                <td class="plinner"><?=odbc2ampm($evento['fecha'])?></td>
            <?php } ?>
            <?php if ($config->placecolum_y) { ?>
                <td class="plinner"><?=stripslashes($evento['lugar'])?></td>
            <?php } ?>

            <?php for ($k= 1; $k < 13; ++$k) { ?>
                <td class="plinner month" valign="middle" align="center">
                    <?php build_intervals($evento['month'][$k]); ?>
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
                $array= $obj->get_participantes($evento['id'], null, null, $id_proceso);
                if ($array) 
                    echo "{$array}{$coma}";

                $origen_data= $obj_tipo->GetOrigenData('participant', $evento['origen_data_asigna']);
                if (!is_null($origen_data)) 
                    echo "<br /> ".merge_origen_data_participant($origen_data).$coma;
                ?>
            </td>

            <?php if ($config->observcolum_y) { ?>
                <td class="plinner"><?=$memo?></td>
            <?php } ?>

            <?php
            if ($cant_org > 0) {
                $obj_org->SetIdEvento(null);
                $obj_org->SetIdOrganismo(null);
                $array_organismos= $obj_org->listar_organismos_by_evento($evento['id']);

                $clink->data_seek($result_org);
                while ($row_org= $clink->fetch_array($result_org)) {
            ?>
                <td class="plinner">
                    <?php if (array_key_exists($row_org['_id'], $array_organismos)) { ?>
                    X
                    <?php } ?>
                </td>
            <?php
                }
            } 
            ?>            
        </tr>

    <?php
    }

    return $j;
}
?>