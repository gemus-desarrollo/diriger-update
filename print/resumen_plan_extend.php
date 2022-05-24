<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/orgtarea.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_auditoria.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/plan_ci.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$_SESSION['debug']= 'no';

$tipo_plan= !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_ACCION;
$year= !empty($_GET['year']) ? $_GET['year'] :  date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_calendar = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$id_auditoria = !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_DEFEAT;

$lastday= $time->longmonth(!empty($month) ? $month : 12, $year);
$date_cut= "$year-12-31";

//-----  ESTADO DEL PLAN ----------------------------------------------------
$obj_plan= new Tplan_ci($clink);
$obj_plan->toshow= 0;
$obj_plan->SetIfEmpresarial(null);
$obj_plan->SetIdUsuario(null);

$obj_plan->SetYear($year);
$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetTipoPlan($tipo_plan);

$obj_plan->Set();
$id_plan= $obj_plan->GetIdPlan();

$objetivos= $obj_plan->GetObjetivo();
$id_responsable_aprb= $obj_plan->GetIdResponsable_aprb();
$observacion= $obj_plan->GetObservacion();
$aprobado= odbc2date($obj_plan->GetAprobado());

if (empty($hh))
    $hh= $time->GetHour();
if (empty($mi))
    $mi= $time->GetMinute();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

if ($tipo_plan == _PLAN_TIPO_PREVENCION || $tipo_plan == _PLAN_TIPO_SUPERVICION)
    $table= "triesgos";
if ($tipo_plan == _PLAN_TIPO_ACCION)
    $table= "tnotas";

$array_prs= null;

$obj_user= new Tusuario($clink);

// -----------  NOTAS DE HALLAZGOS -------------------------------------------------------
if ($tipo_plan == _PLAN_TIPO_ACCION) {
    $noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
    $mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
    $observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

    if (empty($noconf) && empty($mej) && empty($observ)) {
        $noconf= 1;
        $mej= 1;
        $observ= 1;
    }

    /*
     * Para totalizar las tareas de una entidad
     */
    $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(), 'tipo'=>$obj_prs->GetTipo(),
        'descripcion'=>$obj_prs->GetDescripcion(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(), 'conectado'=>$obj_prs->GetConectado(),
        'id_responsable'=>$obj_prs->GetIdResponsable(), 'codigo'=>$obj_prs->GetCodigo(), 'local_archive'=> boolean($obj_prs->GetLocalArchive()),
        'inicio'=>$obj_prs->GetInicio(), 'fin'=>$obj_prs->GetFin());

    $obj_prs= new Tproceso($clink);
    $obj_prs->SetYear($year);
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

    $array_prs= array();
    reset($array_procesos);
    foreach ($array_procesos as $prs)
        $array_prs[]= $prs['id'];
    /*
     * Fin de totalizar
     */

    $obj= new Tnota($clink);
    $obj->SetIdProceso($id_proceso);
    $obj->SetIdEntity(null);
    $obj->SetMonth(null);
    $obj->SetYear($year);
    $obj->SetIdAuditoria($id_auditoria);
    $result_reg= $obj->listar($noconf, $mej, $observ, true, $array_prs);
}

// -----------  RIESGOS -------------------------------------------------------
if ($tipo_plan == _PLAN_TIPO_PREVENCION || $tipo_plan == _PLAN_TIPO_SUPERVICION) {
    $ifestrategico= !is_null($_GET['estrategico']) ? $_GET['estrategico'] : 1;
    $sst= !is_null($_GET['sst']) ? $_GET['sst'] : 1;
    $ma= !is_null($_GET['ma']) ? $_GET['ma'] : 1;
    $econ= !is_null($_GET['econ']) ? $_GET['econ'] : 1;
    $reg= !is_null($_GET['reg']) ? $_GET['reg'] : 1;
    $info= !is_null($_GET['info']) ? $_GET['info'] : 1;
    $calidad= !is_null($_GET['calidad']) ? $_GET['calidad'] : 1;

    $obj= new Triesgo($clink);
    $obj->SetIdProceso($id_proceso);
    $obj->SetMonth(null);
    $obj->SetYear($year);

    $obj->SetIfEstrategico($ifestrategico);
    $obj->SetIfSST($sst);
    $obj->SetIfMedioambiental($ma);
    $obj->SetIfEconomico($econ);
    $obj->SetIfRegulatorio($reg);
    $obj->SetIfInformatico($info);
    $obj->SetIfCalidad($calidad);

    $result_reg= $obj->listar();
}
//---------------------------------------------------------------------------------------

$obj_plan->SetYear($year);
$obj_plan->SetMonth(null);
/*
($tipo_plan == _PLAN_TIPO_PREVENCION || $tipo_plan == _PLAN_TIPO_SUPERVICION) ? $obj_plan->SetIdProceso($id_proceso) : $obj_plan->SetIdProceso(null);
 */
$obj_plan->SetTipoPlan($tipo_plan);
$obj_plan->set_print_reject($print_reject);

$obj_plan->create_tmp_teventos_from_($table, $result_reg, null);
if ($tipo_plan == _PLAN_TIPO_ACCION || $tipo_plan == _PLAN_TIPO_PREVENCION || $tipo_plan == _PLAN_TIPO_SUPERVICION) 
    $obj_plan->SetIdProceso(null);

$obj_plan->list_reg();
//-----------------------------------------------------------------------------------------

$tipo_plan_text= null;

switch($tipo_plan) {
  case _PLAN_TIPO_PREVENCION:
    $tipo_plan_text= "DE PREVENCIÓN";
    break;
  case _PLAN_TIPO_ACCION:
    $tipo_plan_text= "DE ACCIONES CORRECTIVAS, CORRECTORAS Y DE MEJORA";
    break;
}    
$tipo_plan_text.= "$year";

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO DEL  PLAN $tipo_plan_text", "Corresponde a periodo mes/año: $month/$year");
?>

<html>
    <head>
        <title>RESUMEN DEL PLAN</title>

        <?php require "inc/print_top.inc.php";?>


        <div class="page center">

            <div class="container-fluid center">
                <div align="center">
                    <h1>RESUMEN DEL CUMPLIMIENTO <?=$tipo_plan_text?></h1>

                    <?php
                    if (!empty($id_auditoria)) {
                        $obj_audit= new Tauditoria($clink);
                        $obj_audit->SetIdAuditoria($id_auditoria);
                        $obj_audit->Set();
                        $fecha_inicio= $obj_audit->GetFechaInicioPlan();
                        $fecha_fin= $obj_audit->GetFechaFinPlan();

                        $obj_tipo= new Ttipo_auditoria($clink);
                        $obj_tipo->Set($obj_audit->GetIdTipo_auditoria());
                        echo "<strong>AUDITORIA:</strong> {$obj_tipo->GetNombre()} ". odbc2date($fecha_inicio). "  --- ". odbc2date($fecha_fin) ;
                    }
                    ?>
                </div>
            </div>

            <br/>
            <table class="center none-border" width="600px">
                <thead>
                    <tr>
                        <th rowspan="2" class="left center">PLANIFICADAS</th>
                        <th colspan="4" class="center">DE ELLAS</th>
                    </tr>
                    <tr>
                        <th align="center" class="plhead right">EN ESPERA</th>
                        <th align="center" class="plhead right">CUMPLIDAS</th>
                        <th align="center" class="plhead right">INCUMPLIDAS</th>
                        <th align="center" class="plhead">SUSPENDIDAS O POSPUESTAS</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td align="center" class="plinner center left">
                            <?= $obj_plan->total > 0 ? $obj_plan->total : "0" ?>
                        </td>

                        <td align="center" class="plinner center">
                            <?php
                            if (!empty($obj_plan->no_iniciadas)) {
                                echo $obj_plan->no_iniciadas;
                                $ratio = ($obj_plan->no_iniciadas / $obj_plan->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            } else {
                                echo "0";
                            }
                            ?>
                        </td>

                        <td align="center" class="plinner center">
                            <?php
                            if (!empty($obj_plan->cumplidas)) {
                                echo $obj_plan->cumplidas;
                                $ratio = ($obj_plan->cumplidas / $obj_plan->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            } else {
                                echo "0";
                            }
                            ?>
                        </td>

                        <td align="center" class="plinner center">
                            <?php
                            if (!empty($obj_plan->incumplidas)) {
                                echo $obj_plan->incumplidas;
                                $ratio = ($obj_plan->incumplidas / $obj_plan->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            } else {
                                echo "0";
                            }
                            ?>
                        </td>

                        <td align="center" class="plinner center">
                            <?php
                            if (!empty($obj_plan->canceladas)) {
                                echo $obj_plan->canceladas;
                                $ratio = ($obj_plan->canceladas / $obj_plan->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            } else {
                                echo "0";
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>


            <br /><br />
            <table class="center none-border" width="100%">
                <tr>
                    <td class="plinner center left top" colspan="2"><strong>OBSERVACIONES DEL CUMPLIMIENTO</strong></td>
                    <?php if ($config->responsable_planwork) { ?>
                    <td class="plinner center top"><strong>RESPONSABLE</strong></td>
                    <?php } else { ?>
                    <td class="plinner top" width="200px"><strong>QUIEN LAS ORIGINO</strong></td>
                    <?php } ?>

                    <td class="right center top"><strong>CAUSAS</strong></td>
                </tr>

                <tr>
                    <td colspan="4" class="right"><strong>TAREAS INCUMPLIDAS</strong></td>
                </tr>
                <?php
                $i = 0;
                foreach ($obj_plan->incumplidas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left" width="40px"><?= ++$i ?></td>
                        <td class="plinner"><?= $array['evento'] ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <?php if ($config->responsable_planwork) { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_responsable']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } else { ?>
                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_user_asigna']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>
                        <?php } ?>

                        <td class="right"><?= textparse(purge_html($array['observacion'])) ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td class="right"></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" class="right"><strong>SUSPENDIDAS O POSPUESTAS</strong></td>
                </tr>
                <?php
                $i = 0;
                foreach ($obj_plan->canceladas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>
                        <td class="plinner"><?= textparse(purge_html($array['evento'])); ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <td class="plinner">
                            <?php
                            $email = $obj_user->GetEmail($array['id_responsable']);
                            $nombre= $email['nombre'];
                            $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                            echo $nombre;
                            ?>
                        </td>

                        <td class="plinner"><?= $array['observacion'] ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td class="right"></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td class="none-border top bottom"></td>
                    <td class="none-border top bottom"></td>
                    <td colspan="2" class="none-border top"></td>
                </tr>
            </table>



            <br />
            <table width="800px">
                <tr>
                    <td colspan="3" class="none-border"><?= $objetivos ?></td>
                </tr>
                <tr>
                    <td class="none-border">
                        <!--
                        <?php $mail = $obj_user->GetEmail($_SESSION['id_usuario']); ?>
                        <strong>Confecionado por: </strong><br/>
                        <?= textparse($mail['nombre']) ?><br />
                        <?= textparse($mail['cargo']) ?><br />

                        <?php if (!is_null($email['firma'])) { ?>
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $_SESSION['id_usuario'] ?>" border="0" />
                        <?php } ?>
                        -->
                    </td>

                    <td width="200" class="none-border"></td>

                    <td class="none-border">
                        <?php
                        $id_aprobado = $obj_plan->GetIdResponsable_aprb();
                        $mail = !empty($id_aprobado) ? $obj_user->GetEmail($id_aprobado) : null;

                        if (!is_null($mail)) {
                        ?>
                            <strong>Aprobado por:</strong><br/>
                            <?= textparse($mail['nombre']) ?><br />
                            <?= textparse($mail['cargo']) ?><br />
                            <?php if (!is_null($array_aprb['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>

        </div>

    <?php require "inc/print_bottom.inc.php";?>
