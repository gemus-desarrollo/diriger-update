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

require_once "../php/class/tarea.class.php";
require_once "../php/class/planning.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/nota.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$show_all_notes= !empty($_GET['show_all_notes']) ? 1 : 0;

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

$print_all= !is_null($_GET['print_all']) ? $_GET['print_all'] : 0;
$print_prs_inner= !is_null($_GET['print_prs_inner']) ? $_GET['print_prs_inner'] : 0;
$print_prs_inner= boolean($print_prs_inner);
$print_prs_inner= true;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

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

$obj= new Tnota($clink);
$obj->SetDay(null);
$obj->SetMonth($month);
$obj->SetYear($year);

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

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN DE MEDIDAS", "Corresponde a periodo mes/año: $month/$year");
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

    <?php
    $obj_plan= new Tplan_ci($clink);

    $obj_plan->SetIdProceso($id_proceso);
    $obj_plan->SetYear($year);
    $obj_plan->SetTipoPlan(_PLAN_TIPO_ACCION);
    $obj_plan->Set();

    $id_aprobado= $obj_plan->GetIdResponsable_aprb();
    $date_aprb= $obj_plan->GetAprobado();
    $array_aprb= $obj_user->GetEmail($obj_plan->GetIdResponsable_aprb());

    $date_eval= $obj_plan->GetEvaluado();
    $array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
    $cumplimiento= $obj_plan->GetCumplimiento();
    ?>

    <div class="page center">
        <table class="center none-border" width="100%">
            <?php if (!empty($array_aprb)) { ?>
            <tr>
                <td class="none-border">
                    <strong>Aprobado por: <br /></strong>
                    <?=$array_aprb['cargo']?><br />
                    <?=$array_aprb['nombre']?><br />
                    <?php if (!is_null($array_aprb['firma'])) {?>
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>"
                        border="0" />
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td class="center none-border">
                    <h1>
                        PLAN DE ACCIONES PREVENTIVAS, CORRECTIVAS Y DE MEJORA
                        <br /><?=strtoupper($meses_array[(int)$month])?> <?=$year?>
                        <br />
                        <?php
                        if (!empty($id_auditoria))
                            echo $auditoria;
                        if ($title)
                            echo "<br/>$title<br/>";
                        ?>
                    </h1>
                </td>
            </tr>

            <tr>
                <td class="none-border pull-left">
                    <h1 style="text-decoration: underline">OBJETIVOS DEL PLAN</h1><br />
                    <?php
                    $objetivos= $obj->GetObjetivo();
                    $objetivos= purge_html($objetivos, false);
                    $objetivos= textparse($objetivos, false);
                    echo $objetivos;
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <?php
    $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(), 
            'tipo'=>$obj_prs->GetTipo(), 'descripcion'=>$obj_prs->GetDescripcion(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(), 
            'conectado'=>$obj_prs->GetConectado(), 'id_responsable'=>$obj_prs->GetIdResponsable(), 'codigo'=>$obj_prs->GetCodigo(), 
            'local_archive'=> boolean($obj_prs->GetLocalArchive()), 'inicio'=>$obj_prs->GetInicio(), 'fin'=>$obj_prs->GetFin());


    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->SetTipo($tipo_prs);
    $obj_prs->listar_in_order('desc', true, null, true, 'desc');

    $obj_prs->array_procesos[$id_proceso]= $array;
    $array_procesos= $obj_prs->array_procesos;
    
    unset($array);
    $array= null;
    foreach ($array_procesos as $id => $prs) {
        $array[]= $prs['tipo'];
    }

    array_multisort($array, SORT_NUMERIC, SORT_ASC, $array_procesos);  

    unset($array);
    $array= null;
    foreach ($array_procesos as $prs) {
        $array[]= $prs['id'];
    }
    
    $k_nota = 0;
    $array_notas = array();
    $array_size = array();
    $total = 0;

    $obj->SetIdProceso($id_proceso);
    $obj->SetIdEntity(null);
    $obj->SetIdAuditoria($id_auditoria);
    $obj->set_show_all_notes($show_all_notes);

    $result = $obj->listar($noconf, $mej, $observ, true, $array);
    $cant = $obj->GetCantidad();

    while ($row = $clink->fetch_array($result)) {
        if ($row['_id_proceso'] != $id_proceso)
            continue; 
        $array_notas[$id_proceso][$row['_id']] = array('id' => $row['_id'], 'id_proceso' => $row['_id_proceso'], 
                                                        'tipo' => null, 'flag' => 0);
        ++$total;
    }

    if ($cant > 0) {
        reset($array_procesos);
        foreach ($array_procesos as $prs) {
            if ($id_proceso != $prs['id']) {
                if ($tipo_prs == _TIPO_PROCESO_INTERNO)
                    continue;

                if ($tipo_prs != _TIPO_PROCESO_INTERNO) {
                    if ($id_proceso != $_SESSION['local_proceso_id'] && $tipo_prs <= $prs['tipo']) {
                        if (!$print_prs_inner && $prs['tipo'] == _TIPO_PROCESO_INTERNO)
                            continue;
                    }
                }
            }

            $obj->SetIdProceso($prs['id']);
            $result= $obj->listar($noconf, $mej, $observ, true);
            $cant= $obj->GetCantidad();
            if (empty($cant))
                continue;

            $total+= $cant;
            $array_size[$prs['id']]= $cant;
            while ($row= $clink->fetch_array($result)) {
                $array_notas[$id_proceso][$row['_id']]['flag']= 1;
                $array_notas[$prs['id']][$row['_id']]= array('id'=>$row['_id'], 'id_proceso'=>$row['_id_proceso'], 
                                                            'tipo'=>$prs['tipo'], 'flag'=>1);
            }
        }
    }
    ?>

    <div class="page-break"></div>

    <div class="page">
        <div style="margin: 10px 10px 5px 20px;">
            <strong>NO:</strong> no conformidad
            <strong>OBS:</strong> observación
            <strong>NM:</strong> nota de mejora
        </div>
    </div>


    <?php if ($total > 0) { ?>
    <div class="page center">
        <table border="1" cellspacing="0" cellpadding="2">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30px">No</th>
                    <th class="plhead" rowspan="2"><?= $title ?></th>
                    <th class="plhead" colspan="3" style="border-bottom: none; border-left: none;">TIPO</th>
                    <th class="plhead right" rowspan="2">ACCIONES</th>
                    <th class="plhead right" rowspan="2" width="150px">CONTROLA</th>
                    <th class="plhead right" rowspan="2" width="150px">EJECUTA</th>
                    <th class="plhead right" rowspan="2" width="80px">FECHA DE CUMPLIMIENTO</th>
                    <th class="plhead" rowspan="2">COMENTARIOS</th>
                </tr>
                <tr>
                    <th width="20px" class="plhead right">NO</th>
                    <th width="20px" class="plhead right">OBS</th>
                    <th width="20px" class="plhead right">NM</th>
                </tr>
            </thead>

            <tbody>
                <?php
                reset($array_notas);
                foreach ($array_notas as $id_prs => $array) {
                    foreach ($array as $reg) {
                        if (empty($reg['id_proceso']))
                            continue;
                        if ($id_prs != $id_proceso)
                            continue;

                        ++$k_nota;
                        $_id_proceso = ($id_proceso == $reg['id_proceso']) ? null : $id_proceso;

                        write_nota($reg['id'], $_id_proceso, $k_nota);
                    }
                }

                reset($array_procesos);
                foreach ($array_procesos as $process) {
                    if ($process['id'] == $id_proceso)
                        continue;
                    if (!$print_prs_inner && $process['tipo'] == _TIPO_PROCESO_INTERNO)
                        continue;
                    if ($process['id_proceso'] != $id_proceso)
                        continue;

                    if ($array_size[$process['id']] > 0) {
                    ?>
                        <tr>
                            <td colspan="10" class="plinner none-right">
                                <br />
                                <strong><?="{$process['nombre']}, {$Ttipo_proceso_array[$process['tipo']]}"?></strong>
                            </td>
                        </tr>

                        <?php
                        reset($array_notas);
                        foreach ($array_notas as $prs => $array) {
                            if ($prs != $process['id'])
                                continue;

                            foreach ($array as $reg) {
                                if (!$reg['flag'])
                                    continue;
                                ++$k_nota;

                                $_id_proceso = ($prs == $reg['id_proceso']) ? null : $prs;
                                if ($reg['id_proceso'] == $id_proceso)
                                    $_id_proceso = null;

                                write_nota($reg['id'], $_id_proceso, $k_nota);
                            }
                        }
                    }
                }
                ?>

            </tbody>
        </table>
        <?php } ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

    <?php

    function write_nota($id, $id_proceso, $k_nota) {
        global $config;
        global $clink;
        global $month;
        global $year;
        global $print_all;
        global $i_nota;

        $obj_plan = new Tplanning($clink);
        $obj_plan->SetYear($year);

        $obj_task= new Ttarea($clink);
        $obj_task->SetYear($year);

        $obj = new Tnota($clink);
        $obj->SetMonth($month);
        $obj->SetYear($year);

        $obj_peso = new Tpeso($clink);
        $obj_user = new Tusuario($clink);

        $obj_peso->SetYear($year);
        $obj->SetIdnota($id);
        $obj->Set();

        $obj->SetYear($year);
        $obj->SetMonth(null);
        $obj->SetIdProceso(null);
        $obj->listar_tareas($id, $id_proceso, true);
        $_cant = $obj->GetCantidad();

        if ($_cant == 0 && !$print_all)
            return;

        $rowspan = empty($_cant) ? 1 : $_cant;
        $tipo= $obj->GetTipo();
        ?>

    <tr>
        <td class="plinner left" style="border-left:1px solid #000;" rowspan="<?= $rowspan ?>">
        <?= ++$i_nota ?>
        </td>
        <td class="plinner" rowspan="<?= $rowspan ?>">
            <?php
            $riesgo= $obj->GetDescripcion();
            $riesgo= purge_html($riesgo);
            echo textparse($riesgo);
            ?>
        </td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?= $tipo == _NO_CONFORMIDAD ? "X" : "" ?></td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?= $tipo == _OBSERVACION ? "X" : "" ?></td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?= $tipo == _OPORTUNIDAD ? "X" : "" ?></td>

        <?php
            $obj_reg= new Tregister_planning($clink);

            $j = 0;
            if ($_cant > 0) {
                foreach ($obj->array_tareas as $job) {
                    ++$j;

                    $menb = $obj_task->get_participantes($job['id'], 'tarea', null, $id_proceso);
                    $_cant_menb = $obj->GetCantidad();

                    $origen_data = $obj_user->GetOrigenData('participant', $job['origen_data']);
                    if (!is_null($origen_data))
                        echo "<br /> " . merge_origen_data_participant($origen_data);
                    /*
                    if (empty($_cant_menb) && empty($origen_data)) 
                        continue;
                    */
                    ?>
        <?php if ($j > 1) { ?>
    <tr>
        <?php } ?>

        <td class="plinner"><?= $job['nombre'] ?></td>

        <td class="plinner">
            <?php
            $email = $obj_user->GetEmail($job['id_responsable']);
            if ($config->onlypost)
                echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
            else
                echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
            ?>
        </td>

        <td class="plinner"><?= $menb ?></td>

        <td class="plinner">
            <?php
            if (empty($job['chk_date'])) {
                echo odbc2date($job['fecha_fin'], false);
            } else {
                $obj_plan->get_child_events_by_table('ttareas', $job['id']);

                $k = 0;
                foreach ($obj_plan->array_eventos as $evento) {
                    if (is_null($evento))
                        continue;
                    if (!empty($evento['rechazado']))
                        continue;
                    if ($evento['cumplimiento'] == _SUSPENDIDO || $evento['cumplimiento'] == _CANCELADO ||  $evento['cumplimiento'] == _REPROGRAMADO)
                        continue;
                    if ($k > 0)
                        echo ',  ';
                    ++$k;
                    echo odbc2date($evento['fecha_inicio_plan'], false);
                }
            }
            ?>
        </td>

        <td class="plinner right">
            <?php
            $obj_reg->SetIdEvento(null);
            $obj_reg->SetIdTarea($job['id']);
            $array= array('id_responsable'=>$job['id_responsable'], 'id_responsable_2'=>$job['id_responsable_2'],
                        'responsable_2_reg_date'=>$job['responsable_2_reg_date']);
            $row_cmp= $obj_reg->getTarea_reg($job['id'], $array, false);
            echo $row_cmp['observacion'];
            ?>
        </td>
    </tr>

    <?php
            }
        } else {
    ?>

    <td class="plinner right"></td>
    <td class="plinner"></td>
    <td class="plinner"></td>
    <td class="plinner"></td>
    <td class="plinner"></td>
    </tr>
    <?php } ?>

    <?php } ?>