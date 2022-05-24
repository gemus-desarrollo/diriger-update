<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/planning.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/riesgo.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_select_pts= $id_proceso;

if (empty($month) || $month == -1 || $month == 13)
    $month= null;

$time= new TTime();

$ifestrategico= $_GET['estrategico'];
$print_all= !is_null($_GET['print_all']) ? $_GET['print_all'] : 0;

$sst= $_GET['sst'];
$ma= $_GET['ma'];
$econ= $_GET['econ'];
$origen= $_GET['origen'];
$reg= $_GET['reg'];
$info= $_GET['info'];
$calidad= $_GET['calidad'];

$obj= new Triesgo($clink);
$obj->SetDay(null);
$obj->SetMonth(null);
$obj->SetYear($year);

$obj->SetIfEstrategico($ifestrategico);
$obj->SetIfEconomico($econ);
$obj->SetIfMedioambiental($ma);
$obj->SetIfExterno($origen);
$obj->SetIfSST($sst);
$obj->SetIfRegulatorio($reg);
$obj->SetIfInformatico($info);
$obj->SetIfCalidad($calidad);

$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tusuarios(false);

$name_month= !is_null($month) ? strtoupper($meses_array[(int)$month])." " : null;

$obj_prs= new Tproceso($clink);
$obj_prs->set_use_copy_tprocesos(false);

if (!empty($id_proceso) && $id_proceso != -1)
    $obj_prs->Set($id_proceso);
else
    $obj_prs->Set($_SESSION['id_entity']);

$id_proceso_code= $obj_prs->get_id_proceso_code();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();
$conectado= $obj_prs->GetConectado();

require_once "../form/inc/escenario.ini.inc.php";

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN DE PREVENCIÓN DE RIESGOS", "Corresponde a periodo año: $year");
?>


<html>
    <head>
        <title>PLAN DE PREVENCIÓN DE RIESGOS</title>

         <?php require "inc/print_top.inc.php";?>

        <?php
        $obj_plan= new Tplan_ci($clink);

        if (!empty($id_proceso) && $id_proceso != -1)
            $obj_plan->SetIdProceso($id_proceso);
        else
            $obj_plan->SetIdProceso($_SESSION['id_entity']);

        $obj_plan->SetYear($year);
        $obj_plan->SetTipoPlan(_PLAN_TIPO_PREVENCION);
        $obj_plan->Set();

        $date_aprb= $obj_plan->GetAprobado();
        $id_responsable_aprb= $obj_plan->GetIdResponsable_aprb();
        
        unset($obj_user);
        $obj_user= new Tusuario($clink);
        $obj_user->SetIdUsuario($id_responsable_aprb);
        $obj_user->Set();
        $nombre_aprb= $obj_user->GetNombre();
        $cargo_aprb= $obj_user->GetCargo();
        $firma_aprb= $obj_user->GetParam();
        ?>

        <div class="page center">
            <div class="container-fluid center">
                <h1>PLAN DE PREVENCIÓN DE RIESGOS<br/><?=strtoupper($meses_array[(int)$month])?> <?=$year?></h1>
            </div>
        </div>

        <div class="page">
            <div class="container-fluid">
                <?php
                $obj_plan->SetIdProceso($id_proceso);
                $result= $obj_plan->listar_objetivos();
                $cant_obj= $obj_plan->GetCantidad();

                if ($cant_obj > 0) {
                ?>
                    <h1>OBJETIVOS DE CONTROL INTERNO</h1><br />

                    <div style="padding:5px;">
                        <ul>
                            <?php
                            $array_ids= array();
                            while ($row= $clink->fetch_array($result)) {
                                if ($array_ids[$row['id']])
                                    continue;
                                $array_ids[$row['id']]= 1;
                            ?>
                            <li><?= textparse(purge_html($row['objetivo'])) ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="page-break"></div>

        <div class="page center">
            <div class="container-fluid center">

            <?php
            $k_riesgo= 0;
            $i_riesgo= 0;
            $array_riesgos= array();
            $array_size= array();
            $total= 0;
            $totalShow= 0;

            if (!empty($id_proceso) && $id_proceso != -1) {
                $obj->set_id_proceso_code($id_proceso_code);
                $obj->SetIdProceso($id_proceso);
            } else {
                $obj->set_id_proceso_code(null);
                $obj->SetIdProceso(null);
            }

            $ranking= $obj->listar_and_ranking(false, false, false);
            $cant= $obj->GetCantidad();

            foreach ($ranking as $reg) {
                $array_riesgos[$id_proceso][$reg['id']]= array('id'=>$reg['id'], 'id_proceso'=>$reg['id_proceso'], 'tipo'=>null, 'flag'=> 0);
                ++$total;

                $obj->listar_tareas($reg['id']);
                $cant= $obj->GetCantidad();
                if ($cant > 0 || ($cant == 0 && $print_all))
                    ++$totalShow;
            }

            unset($obj_prs);

            if ($total > 0) {
                $obj_prs= new Tproceso($clink);
                $obj_prs->SetYear($year);
                $obj_prs->SetIdProceso($_SESSION['id_entity']);
                $obj_prs->SetTipo($_SESSION['entity_tipo']);
                $obj_prs->listar_in_order('eq_desc', false, null, null, 'asc');

                foreach ($obj_prs->array_procesos as $prs) {
                                            /*
                    * temporal ha ver que pasa
                    */
                   if ($prs['id'] != $id_proceso)
                       continue;
                    if ($tipo_prs != _TIPO_PROCESO_INTERNO && ($prs['tipo'] < $tipo_prs || ($prs['tipo'] == $tipo_prs && $prs['id'] != $id_proceso)))
                        continue;
                    if ($tipo_prs == _TIPO_PROCESO_INTERNO && ($prs['tipo'] == _TIPO_PROCESO_INTERNO) && $prs['id'] != $id_proceso)
                        continue;

                    $obj->SetIdProceso($prs['id']);
                    $result= $obj->listar(false, false);
                    $cant= $obj->GetCantidad();

                    $array_size[$prs['id']]= $cant;
                    if (empty($cant))
                        continue;

                    while ($row= $clink->fetch_array($result)) {
                        if (isset($array_riesgos[$id_proceso][$row['_id']])) {
                            $array_riesgos[$id_proceso][$row['_id']]['flag']= 1;
                            if ($prs['id'] != $id_proceso)
                                $array_riesgos[$prs['id']][$row['_id']]= array('id'=>$row['_id'], 'id_proceso'=>$row['_id_proceso'], 'tipo'=>$prs['tipo'], 'flag'=>1);
                        }
                    }
                }
            }
            ?>

            <div class="container-fluid pull-left">
                Riesgos identificados:<?=$total?> de ellos gestionandose y mostrados:<?=$totalShow?>
            </div>

            <br/><br/>

            <?php if ($total > 0) { ?>
                <table class="center" width="100%">
                    <thead>
                        <tr>
                            <th class="plhead left" width="30px">No</th>
                            <th class="plhead">ACTIVIDAD O ÁREA</th>
                            <th class="plhead">RIESGOS</th>
                            <th class="plhead">POSIBLES MANIFESTACIONES NEGATIVAS</th>
                            <th class="plhead">MEDIDAS A APLICAR</th>
                            <th class="plhead">RESPONSABLE</th>
                            <th class="plhead">EJECUTANTES</th>
                            <th class="plhead">FECHA DE CUMPLIMIENTO</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    reset($array_riesgos);
                    foreach ($array_riesgos as $id_prs => $array) {
                        /*
                         * temporal ha ver que pasa
                         */
                        if ($id_prs != $id_proceso)
                            continue;

                        foreach ($array as $reg) {
                            if ($id_prs != $id_proceso)
                                continue;
                            if ($reg['flag'])
                                continue;

                           ++$k_riesgo;
                           $_id_proceso= ($id_proceso == $reg['id_proceso']) ? null : $id_proceso;

                           write_riesgo($reg['id'], $_id_proceso, $k_riesgo);
                        }
                    }

                    reset($obj_prs->array_procesos);
                    foreach ($obj_prs->array_procesos as $prs) {
                        if ($tipo_prs != _TIPO_PROCESO_INTERNO && ($prs['tipo'] < $tipo_prs || ($prs['tipo'] == $tipo_prs && $prs['id'] != $id_proceso)))
                            continue;
                        if ($tipo_prs == _TIPO_PROCESO_INTERNO && ($prs['tipo'] == _TIPO_PROCESO_INTERNO) && $prs['id'] != $id_proceso)
                            continue;

                        if ($array_size[$prs['id']] > 0)  {
                    ?>
                      <tr>
                          <td colspan="8" class="plinner left"><strong><?="{$prs['nombre']}, {$Ttipo_proceso_array[$prs['tipo']]}"?></strong></td>
                      </tr>

                    <?php
                        reset($array_riesgos);
                        foreach ($array_riesgos as $id_prs => $array) {
                            foreach ($array as $reg) {
                                if ($id_prs != $prs['id'])
                                    continue;
                                if ($id_prs == $id_proceso && !$reg['flag'])
                                    continue;
                                ++$k_riesgo;

                                $_id_proceso= ($id_prs == $reg['id_proceso']) ? null : $id_prs;
                                if ($reg['id_proceso'] == $id_proceso)
                                    $_id_proceso= null;

                                write_riesgo($reg['id'], $_id_proceso, $k_riesgo);
                            }
                        }
                    }   }
                    ?>

                    </tbody>
                </table>
            <?php } ?>

            <?php
            if (!is_null($id_responsable_aprb)) {
                $objetivos= $obj_plan->GetObjetivo();
                $objetivos= textparse(purge_html($objetivos));
            ?>
            <div class="name text-left mt-3">
                <h1>APROBADO POR: </h1>
                <div class="row">
                    <div class="name col-md-6 col-lg-6">
                        <?php 
                        $nombre_aprb.= !empty($cargo_aprb) ? "<br/>$cargo_aprb" : null;
                        ?>
                       <?= textparse($nombre_aprb)?> <br />
                       En fecha:</span> <?=odbc2date($date_aprb)?>
                       
                       <hr class="mt-3" />
                       <p style="text-decoration:underline; font-weight: bold;">OBSERVACIONES:</p> 
                       <?= $objetivos ?><br />
                    </div>
                   <div class="pull-right col-md-4 col-lg-4">
                        <?php if ($firma_aprb['name']) {?>
                       <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_responsable_aprb?>" border="0" />
                       <?php } ?>
                   </div>                       
                </div>
                 
            </div>
        </div>
        <?php } ?>

        </div>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

<?php
function write_riesgo($id, $id_proceso, $k_riesgo) {
    global $config;
    global $cant;
    global $clink;
    global $year;
    global $print_all;
    global $i_riesgo;

    $obj_plan= new Tplanning($clink);
    $obj_plan->SetInicio($year);
    $obj_plan->SetFin($year);

    $obj= new Triesgo($clink);
    $obj_peso= new Tpeso($clink);
    $obj_user= new Tusuario($clink);

    $obj_peso->SetYear($year);
    $obj->SetIdRiesgo($id);
    $obj->Set();

    $obj->SetYear($year);
    $obj->SetIdProceso(null);
    $obj->listar_tareas($id, $id_proceso, true);
    $_cant= $obj->GetCantidad();

    if ($_cant == 0 && !$print_all)
        return;

    $rowspan= empty($_cant) ? 1 : $_cant;

    $obj_task= new Ttarea($clink);
    $obj_task->SetYear($year);
?>
    <tr>
        <td class="plinner left" rowspan="<?=$rowspan?>">
            <?=++$i_riesgo?>
        </td>

        <td class="plinner" rowspan="<?=$rowspan?>">
            <?=$obj->GetLugar()?>
        </td>

        <td class="plinner" rowspan="<?=$rowspan?>">
            <?=$obj->GetNombre()?>

            <spam style="font-weight:bold"><br /><br />Lineamientos:<br />
                <?php
                $obj_peso->listar_politicas_ref_riesgo($id);

                $k= 0;
                foreach ($obj_peso->array_politicas as $politica) {
                    if ($k > 0)
                        echo ", ";
                    echo "L".$politica['numero'];
                    ++$k;
                }

                unset($obj_peso);
            ?>
            </spam>
        </td>

       <td class="plinner" rowspan="<?=$rowspan?>">
            <?php
            $descripcion= $obj->GetDescripcion();
            $descripcion= textparse(purge_html($descripcion));
            echo $descripcion;

            $value= $obj->GetValue();
            if (!empty($value))
                echo "<p>Perdida estimada: $".$value."</p>";
            ?>
       </td>

    <?php
    $j= 0;
    reset($obj->array_tareas);
    foreach ($obj->array_tareas as $job) {
        ++$j;
        $menb= $obj_task->get_participantes($job['id'], 'tarea', null, $id_proceso);
        $_cant_menb= $obj_task->GetCantidad();
    ?>
       <?php if ($j > 1) { ?>
            <tr>
        <?php } ?>
                <td class="plinner">
                    <?= textparse($job['nombre'])?>
                </td>

               <td class="plinner">
                    <?php
                    $email= $obj_user->GetEmail($job['id_responsable']);
                    if ($config->onlypost)
                        echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                    else
                        echo $email['nombre'].(!empty($email['cargo']) ? " (".textparse($email['cargo']).")" : null);
                    ?>
               </td>

               <td class="plinner">
                    <?=$menb?>
                    <?php
                    $origen_data= $obj_user->GetOrigenData('participant', $job['origen_data']);
                    if (!is_null($origen_data))
                        echo "<br /> ".merge_origen_data_participant($origen_data);
                ?>
               </td>

               <td class="plinner">
                   <?php
                    if (empty($job['chk_date'])) {
                        echo odbc2date($job['fecha_fin'], false);
                    } else {
                        $obj_plan->get_child_events_by_table('ttareas', $job['id']);

                        $k= 0;
                        $cant_event= count($obj_plan->array_eventos);
                        foreach ($obj_plan->array_eventos as $evento) {
                            $flag_event= false;
                            if (is_null($evento))
                                continue;
                            if (!empty($evento['rechazado'])) {
                                if ($cant_event > 1)
                                    continue;
                                else
                                    $flag_event= true;
                            }
                            if ($evento['cumplimiento'] == _SUSPENDIDO || $evento['cumplimiento'] == _CANCELADO ||  $evento['cumplimiento'] == _REPROGRAMADO) {
                                if ($cant_event > 1)
                                    continue;
                                else
                                    $flag_event= true;
                            }
                            if ($k > 0)
                                echo ',  ';
                            ++$k;
                            $date= odbc2date($evento['fecha_fin_plan'], false);
                            if ($flag_event)
                                $date.= "<sup>*</sup>";
                            echo $date;
                        }
                    }
                    ?>
               </td>
            </tr>
        <?php } ?>

        <?php if ($j == 0) { ?>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
         </tr>
        <?php } ?>

 <?php } ?>
