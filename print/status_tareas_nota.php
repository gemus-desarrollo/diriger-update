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

require_once "../php/class/planning.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/nota.class.php";

require_once "../form/class/tarea.signal.class.php";

require_once "../php/class/traza.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$show_all_notes= !empty($_GET['show_all_notes']) ? 1 : 0;
$if_all_year= !empty($month) && $month != -1 ? false : true;

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}
$print_prs_inner= !is_null($_GET['print_prs_inner']) ? $_GET['print_prs_inner'] : 0;
$print_prs_inner= boolean($print_prs_inner);
$print_prs_inner= true;

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
$tipo_prs= $obj_prs->GetTipo();

$obj_signal= new Ttarea_signals();

$obj_tarea= new Ttarea($clink);
$obj_evento= new Tevento($clink);

$obj_tarea->SetYear($year);
$obj_evento->SetYear($year);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ESTADO DE LAS ACCCIONES O MEDIDAS", "Corresponde a periodo año: $year");
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
                    ESTADO DE LAS ACCCIONES O MEDIDAS <br/> <?=$year?>
                    <br/>
                    <?php
                    if (!empty($id_auditoria))
                        echo $auditoria;
                    if ($title)
                        echo "<br/>$title<br/>";
                    ?>
                </div>
            </div>
        </div>

        <?php
        $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(), 'tipo'=>$obj_prs->GetTipo(),
                'descripcion'=>$obj_prs->GetDescripcion(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(), 'conectado'=>$obj_prs->GetConectado(),
                'id_responsable'=>$obj_prs->GetIdResponsable(), 'codigo'=>$obj_prs->GetCodigo(), 'local_archive'=> boolean($obj_prs->GetLocalArchive()),
                'inicio'=>$obj_prs->GetInicio(), 'fin'=>$obj_prs->GetFin());

        unset($obj_prs);     
        $obj_prs= new Tproceso($clink);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->SetTipo($tipo_prs);
        $obj_prs->listar_in_order('desc', false, null, true, 'desc');

        $obj_prs->array_procesos[$id_proceso]= $array;
        $array_procesos= $obj_prs->array_procesos;

        unset($array);
        $array= null;
        foreach ($array_procesos as $prs)
            $array[]= $prs['tipo'];
        array_multisort($array, SORT_NUMERIC, SORT_ASC, $array_procesos);

        unset($array);
        $array= array();
        reset($array_procesos);
        foreach ($array_procesos as $prs)
            $array[]= $prs['id'];
        ?>

        <?php
        $k_nota = 0;
        $array_notas = array();
        $array_size = array();
        $total = 0;

        $obj= new Tnota($clink);
        $obj->SetDay(null);
        $obj->SetMonth($month);
        $obj->SetYear($year);

        $obj->SetIdProceso($id_proceso);
        $obj->SetIdEntity(null);
        $obj->SetIdAuditoria($id_auditoria);
        $obj->set_show_all_notes($show_all_notes);

        $result = $obj->listar($noconf, $mej, $observ, true, $array);
        $cant = $obj->GetCantidad();

        while ($row = $clink->fetch_array($result)) {
            $array_notas[$id_proceso][$row['_id']] = array('id' => $row['_id'], 'id_proceso' => $row['_id_proceso'], 'tipo' =>$row['tipo'],
                                                    'origen'=>$row['origen'], 'nota'=>$row['descripcion'], 'fecha_cierre'=>$row['fecha_fin_real'],
                                                    'flag' => 0);
            ++$total;
        }

        if ($total > 0) {
            reset($array_procesos);
            foreach ($array_procesos as $prs) {
                if ($id_proceso != $prs['id']) {
                    if ($tipo_prs == _TIPO_PROCESO_INTERNO)
                        continue;

                    if ($tipo_prs != _TIPO_PROCESO_INTERNO) {
                        if ($id_proceso != $_SESSION['id_entity'] && $tipo_prs <= $prs['tipo']) {
                            if (!$print_prs_inner && $prs['tipo'] == _TIPO_PROCESO_INTERNO)
                                continue;
                }   }   }

                $obj->SetIdProceso($prs['id']);
                $result= $obj->listar($noconf, $mej, $observ, true);
                $cant= $obj->GetCantidad();
                if (empty($cant))
                    continue;

                $array_size[$prs['id']]= $cant;
                while ($row= $clink->fetch_array($result)) {
                    $array_notas[$id_proceso][$row['_id']]['flag']= 1;
                    $array_notas[$prs['id']][$row['_id']]= array('id'=>$row['_id'], 'id_proceso'=>$row['_id_proceso'],
                                    'tipo'=>$row['tipo'], 'origen'=>$row['origen'], 'nota'=>$row['descripcion'],
                                    'fecha_cierre'=>$row['fecha_fin_real'], 'flag'=>1);
                }
            }
        }
        ?>

        <div class="page center">
            <?php
            $totalShow= 0;

            reset($array_procesos);
            foreach ($array_procesos as $process) {
                if ($array_size[$process['id']] == 0)
                    continue;

            ?>  
                <br/>
                <div class="row mt-3">
                    <h1 class="pull-left"><?="{$process['nombre']}, {$Ttipo_proceso_array[$process['tipo']]}"?></h1>
                </div>

                <div class="row mb-1">
                    <table width="100%" border="1" cellspacing="0" cellpadding="2">
                        <thead>
                            <tr>
                                <th rowspan="2" style="text-align: center">No.</th>
                                <th colspan="3" style="text-align: center">HALLAZGO</th>
                                <th colspan="4" style="text-align: center">TAREAS</th>
                            </tr>
                            <tr>
                                <th width="200px">DESCRIPCIÓN</th>
                                <th>TIPO</th>
                                <th>ORIGEN</th>

                                <th width="200px">TAREAS</th>
                                <th>RESPONSABLE</th>
                                <th>CUMPLIMIENTO</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php
                        $obj->SetYear($year);

                        reset($array_notas);
                        foreach ($array_notas as $prs => $array) {
                            if ($prs != $process['id'])
                                continue;

                            foreach ($array as $reg) {
                                ++$k_nota;

                                $obj->listar_tareas($reg['id'], $id_proceso, true);
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
                                    <td class="plinner left" rowspan="<?=$rowspan?>"><?= $k_nota?></td>
                                    <td class="plinner" rowspan="<?=$rowspan?>">
                                        <?= purge_html($reg['nota'])?>
                                    </td>
                                    <td class="plinner" rowspan="<?=$rowspan?>"><?=$Ttipo_nota_array[$reg['tipo']]?></td>
                                    <td class="plinner" rowspan="<?=$rowspan?>"><?=$Ttipo_nota_origen_array[$reg['origen']]?></td>

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
                                            <td class="plinner">
                                                <?=$event['nombre']?>
                                            </td>

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

        <?php  } ?>
        </div>
    <?php require "inc/print_bottom.inc.php";?>
