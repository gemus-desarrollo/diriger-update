<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2015
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

require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";
require_once "../php/class/plan_ci.class.php";

require_once "../php/class/tarea.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/riesgo.class.php";

require_once "../form/class/tarea.signal.class.php";

require_once "../php/class/traza.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$if_all_year= !empty($month) && $month != -1 ? false : true;

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;

$ifestrategico= $_GET['estrategico'];
$sst= $_GET['sst'];
$ma= $_GET['ma'];
$econ= $_GET['econ'];
$origen= $_GET['origen'];
$reg= $_GET['reg'];
$info= $_GET['info'];
$calidad= $_GET['calidad'];

if (empty($month) || $month == -1 || $month == 13) 
    $month= null;

$time= new TTime();

$auditoria= null;
if (!empty($id_auditoria)) {
    $obj_audit= new Tauditoria($clink);
    $obj_audit->Set($id_auditoria);
    
    $origen= $obj_audit->GetOrigen();
    $origen= $Ttipo_nota_origen_array[(int)$origen];
    $tipo= $obj_audit->GetTipo_auditoria();
    $tipo= $Ttipo_auditoria_array[(int)$tipo];
    
    $auditoria= "$origen $tipo";
    $jefe_equipo= $obj_audit->GetJefe_equipo();
    $fecha_inicio= odbc2date($obj_audit->GetFechaInicioPlan());
    $fecha_fin= odbc2date($obj_audit->GetFechaFinPlan());
    
    $auditoria= "<br/>$auditoria</br/> Inicia: $fecha_inicio hasta $fecha_fin<br/>Jefe del equipo auditor:{$jefe_equipo}";
}

$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tusuarios(false);

if (!is_null($month)) 
    $name_month= strtoupper($meses_array[(int)$month])." ";
else 
    $name_month= null;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_signal= new Ttarea_signals();
$obj_task= new Ttarea($clink);
$obj_evento= new Tevento($clink);
$obj_reg= new Tregister_planning($clink);

$obj_task->SetYear($year);
$obj_evento->SetYear($year);
$obj_reg->SetYear($year);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN DE MEDIDAS RIESGOS", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>PLAN DE MEDIDAS</title>

        <style type="text/css">
             .tipo {
                 text-align: center;
                 vertical-align: middle;
                 font-weight: bolder;
                 font-size: 1.3em;
             }
             .alert.small {
                 padding: 2px 2px 2px 4px;
                 margin: 1px;
                 text-align: center;
             }             
         </style>

        <?php
        $title= null;
        if ($noconf) 
            $title= "No Conformidades";
        if ($observ) 
            $title.= !is_null($title) ? ", Observaciones" : "Observaciones";
        if ($mej) 
            $title.= !is_null($title) ? ", Notas de Mejora" : "Notas de Mejora";
        ?>

        <?php require "inc/print_top.inc.php";?>	
             
        <div class="container-fluid center">
            <div align="center" class="title-header">
                <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                    ESTADO DE LAS ACCCIONES O MEDIDAS <br/><?=strtoupper($meses_array[(int)$month])?> <?=$year?>
                    <br/>
                    <?php 
                    if (!empty($id_auditoria)) 
                        echo $auditoria; 
                    ?>
                    
                    <?php 
                    if ($title) 
                        echo "<br/>$title<br/>";
                    ?>
                </div>
            </div>
        </div>

        <?php
        $k_nota = 0;
        $array_notas = array();
        $array_size = array();
        $total = 0;

        $obj = new triesgo($clink);

        $obj->SetYear($year);
        $obj->SetMonth($month);
        $obj->SetIdProceso($id_proceso);

        $obj->SetIfEstrategico($ifestrategico);
        $obj->SetIfEconomico($econ);
        $obj->SetIfMedioambiental($ma);
        $obj->SetIfExterno($origen);
        $obj->SetIfSST($sst);
        $obj->SetIfRegulatorio($reg);
        $obj->SetIfInformatico($info);
        $obj->SetIfCalidad($calidad);

        $result = $obj->listar();
        $cant = $obj->GetCantidad();

        while ($row= $clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'id_proceso'=>$row['_id_proceso'], 'riesgo'=>$row['nombre'],
                    'fecha_cierre'=>$row['fecha_fin_plan']);
            $array_riesgos[$prs['id']][$row['_id']]= $array;
            ++$total;
        }
        
        $totalShow= 0;
        ?>
        
        <div class="page center">
            <div class="row">
                <table width="100%" border=1 cellspacing=0 cellpadding=2>
                    <thead>
                        <tr>
                            <th rowspan="2" style="text-align: center">No.</th>
                            <th rowspan="2" style="text-align: center">RIESGOS</th>
                            <th colspan="4" style="text-align: center">TAREAS</th>
                        </tr>
                        <tr>
                            <th>TAREA</th>
                            <th>RESPONSABLE</th>
                            <th>CUMPLIMIENTO</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php    
                    reset($array_riesgos);
                    foreach ($array_riesgos as $prs => $array) {
                        $cant_task= 0;
                        foreach ($array as $reg) {
                            ++$k_riesgo;

                            $obj->listar_tareas($reg['id'], $reg['id_proceso'], true);
                            $cant_task = $obj->GetCantidad();
                            $cant_event= 0;
                            foreach ($obj->array_tareas as $job) {  
                                $obj_evento->get_eventos_by_tarea($job['id']);

                                $_cant= 0;
                                foreach ($obj_evento->array_eventos as $key => $event) {
                                    ++$_cant;
                                    $array_tarea_eventos[$job['id']]['evento'][$key]= $event;
                                }

                                $array_tarea_eventos[$job['id']]['cant_evento']= $_cant;
                                $cant_event+= $_cant;
                            }

                            $totalShow+= $cant_event; 
                            $rowspan = empty($cant_event) ? 1 : $cant_event;
                            ?> 
                        
                            <tr>
                                <td class="plinner left" rowspan="<?=$rowspan?>"><?= $k_riesgo?></td>
                                <td class="plinner" rowspan="<?=$rowspan?>"><?= purge_html($reg['riesgo'])?></td>

                            <?php if (empty($cant_event)) { ?>
                                <td class="plinner"></td>
                                <td class="plinner"></td>
                                <td class="plinner"></td>
                                <td class="plinner"></td>
                            </tr> 
                            <?php 
                                continue;
                            } 
                            ?>
                              
                            <?php  
                            $nt= 0;
                            reset($obj->array_tareas);
                            foreach ($obj->array_tareas as $job) {
                                ++$nt;

                                $cant_event= $array_tarea_eventos[$job['id']]['cant_evento'];
                                $ne= 0;
                                foreach ($array_tarea_eventos[$job['id']]['evento'] as $event) {
                                    ++$ne;                                        
                                ?>
                              
                                <?php if ($ne > 1) { ?><tr><?php } ?>
                                    <td class="plinner"><?=$event['nombre']?></td>

                                    <td class="plinner">
                                         <?php
                                         $email= $obj_user->GetEmail($event['id_responsable']);
                                        if ($config->onlypost) 
                                            echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                                        else 
                                            echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                                         ?>
                                    </td>

                                    <td class="plinner">
                                        <?=odbc2date($event['fecha_fin'])?>
                                    </td>

                                    <td class="plinner">
                                        <?php
                                        if (isset($obj_reg)) unset ($obj_reg);
                                        $obj_reg= new Tregister_planning($clink);
                                        $obj_reg->SetIdEvento($event['id']);
                                        $obj_reg->SetYear($year);
                                        $row= $obj_reg->getEvento_reg(null, array('id_responsable'=>$event['id_responsable']));

                                        $alarm= $obj_signal->get_alarm($row);
                                        ?>

                                        <div class="alert bg-<?=$alarm['class']?> small"><?=$eventos_cump[$row['cumplimiento']]?></div>
                                    </td>
                                </tr>
                            <?php
                                    }
                                }   
                            }
                        }  
                        ?>
                    </tbody>
                </table> 
            </div>
        </div>        
    <?php require "inc/print_bottom.inc.php";?>
