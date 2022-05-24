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
require_once "../php/class/auditoria.class.php";
require_once "../php/class/nota.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$id_proceso= $_GET['id_proceso'];

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;
$show_all_notes= !empty($_GET['show_all_notes']) ? 1 : 0;

$print_all= !is_null($_GET['print_all']) ? $_GET['print_all'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

if (empty($month) || $month == -1 || $month == 13) $month= null;

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
$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tusuarios(false);

if (!is_null($month)) $name_month= strtoupper($meses_array[(int)$month])." ";
else $name_month= null;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);
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
        if ($noconf) $title= "No Conformidades";
        if ($observ) $title.= !is_null($title) ? ", Observaciones" : "Observaciones";
        if ($mej) $title.= !is_null($title) ? ", Notas de Mejora" : "Notas de Mejora";
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
                    <?php if (!is_null($array_aprb['firma'])) {?><img id="img"
                        src="../php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>"
                        border="0" /><?php } ?>
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
                            if (!empty($id_auditoria)) {
                                echo $auditoria;
                            }
                            ?>

                        <?php
                            if ($title) {
                                echo "<br/>$title<br/>";
                            }
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
        $k_nota = 0;
        $array_notas = array();
        $array_size = array();
        $total = 0;

        $obj->SetIdProceso($id_proceso);
        $obj->SetIdAuditoria($id_auditoria);
        $obj->set_show_all_notes($show_all_notes);

        $result = $obj->listar($noconf, $mej, $observ, true);
        $cant = $obj->GetCantidad();

        while ($row = $clink->fetch_array($result)) {
            $array_notas[$id_proceso][$row['_id']] = array('id' => $row['_id'], 'id_proceso' => $row['_id_proceso'], 'tipo' => null, 'flag' => 0);
            ++$total;
        }

        unset($obj_prs);

        if ($total > 0) {
            $obj_prs= new Tproceso($clink);
            $obj_prs->SetYear($year);
            $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
            $obj_prs->SetTipo($_SESSION['local_proceso_tipo']);

            $obj_prs->listar_in_order('desc', true, null, true, 'desc');

            foreach ($obj_prs->array_procesos as $prs) {
                if ($tipo_prs != _TIPO_PROCESO_INTERNO && $prs['tipo'] != _TIPO_PROCESO_INTERNO) 
                    continue;
                if ($tipo_prs == _TIPO_PROCESO_INTERNO && $prs['tipo'] == _TIPO_PROCESO_INTERNO) 
                    continue;

                $obj->SetIdProceso($prs['id']);
                $result= $obj->listar($noconf, $mej, $observ, true);
                $cant= $obj->GetCantidad();

                $array_size[$prs['id']]= $cant;
                if (empty($cant)) 
                    continue;

                while ($row= $clink->fetch_array($result)) {
                    $array_notas[$id_proceso][$row['_id']]['flag']= 1;
                    $array_notas[$prs['id']][$row['_id']]= array('id'=>$row['_id'], 'id_proceso'=>$row['_id_proceso'], 'tipo'=>$prs['tipo'], 'flag'=>1);
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
        <table border=1 cellspacing=0 cellpadding=2>
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30px">No</th>
                    <th class="plhead" rowspan="2"><?= $title ?></th>
                    <th class="plhead" colspan="3" style="border-bottom: none; border-left: none;">TIPO</th>
                    <th class="plhead right" rowspan="2">ACCIONES</th>
                    <th class="plhead right" rowspan="2" width="150px">RESPONSABLE</th>
                    <th class="plhead right" rowspan="2" width="150px">EJECUTANTES</th>
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
                    foreach ($array_notas as $prs => $array) {
                        foreach ($array as $reg) {
                            if ($prs != $id_proceso) continue;
                            if ($reg['flag']) continue;

                            ++$k_nota;
                            $_id_proceso = ($id_proceso == $reg['id_proceso']) ? null : $id_proceso;

                            write_nota($reg['id'], $_id_proceso, $k_nota);
                        }
                    }

                    reset($obj_prs->array_procesos);

                    foreach ($obj_prs->array_procesos as $process) {
                        if ($tipo_prs != _TIPO_PROCESO_INTERNO && $process['tipo'] != _TIPO_PROCESO_INTERNO)
                            continue;
                        if ($tipo_prs == _TIPO_PROCESO_INTERNO && $process['tipo'] == _TIPO_PROCESO_INTERNO)
                            continue;

                        if ($array_size[$process['id']] > 0) {
                            ?>
                <tr>
                    <td colspan="10" class="plinner">
                        <br /><br />
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
        global $id_escenario;
        global $month;
        global $year;
        global $print_all;
        global $i_nota;

        $obj_plan = new Tplanning($clink);

        $obj = new Tnota($clink);
        $obj->SetMonth($month);
        $obj->SetYear($year);

        $obj_peso = new Tpeso($clink);
        $obj_user = new Tusuario($clink);

        $obj_peso->SetYear($year);
        $obj->SetIdnota($id);
        $obj->Set();

        $obj->SetYear(null);
        $obj->SetMonth(null);
        $obj->listar_tareas($id, $id_proceso, true);
        $_cant = $obj->GetCantidad();

        if ($_cant == 0 && !$print_all) return;

        $rowspan = empty($_cant) ? 1 : $_cant;
        $tipo= $obj->GetTipo();
        ?>

    <tr>
        <td class="plinner left" style="border-left:1px solid #000;" rowspan="<?= $rowspan ?>"><?= ++$i_nota ?></td>
        <td class="plinner" rowspan="<?= $rowspan ?>"><?= $obj->GetDescripcion() ?></td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?php echo $tipo == _NO_CONFORMIDAD ? "X" : "" ?></td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?php echo $tipo == _OBSERVACION ? "X" : "" ?></td>
        <td class="plinner tipo" rowspan="<?= $rowspan ?>"><?php echo $tipo == _OPORTUNIDAD ? "X" : "" ?></td>

        <?php
            $j = 0;
            if ($_cant > 0) {

                foreach ($obj->array_tareas as $job) {
                    ++$j;

                    $menb = $obj->get_participantes($job['id'], 'tarea', null, $id_proceso);
                    $_cant_menb = $obj->GetCantidad();

                    $origen_data = $obj_user->GetOrigenData('participant', $job['origen_data']);
                    if (!is_null($origen_data))
                        echo "<br /> " . merge_origen_data_participant($origen_data);

                    // if (empty($_cant_menb) && empty($origen_data)) continue;
                    ?>
        <?php if ($j > 1) { ?>
    <tr>
        <?php } ?>

        <td class="plinner"><?= $job['nombre'] ?></td>

        <td class="plinner">
            <?php
                        $email = $obj_user->GetEmail($job['id_responsable']);
                        echo ($config->onlypost) ? $email['cargo'] : $email['nombre'] . (!empty($email['cargo']) ? ', ' . $email['cargo'] : null);
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
                                if (is_null($evento)) continue;
                                if ($k > 0)
                                    echo ',  ';
                                ++$k;
                                echo odbc2date($evento['fecha_inicio_plan'], false);
                            }
                        }
                        ?>
        </td>

        <td class="plinner right">
            <?= $evento['descripcion'] ?>
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