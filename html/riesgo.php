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
require_once "../php/class/proceso.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/riesgo.class.php";

require_once "../form/class/riesgo.signal.class.php";

$_SESSION['debug']= 'no';
$signal= 'riesgo';

$force_user_process= true;
require_once "../php/inc_escenario_init.php";

if (empty($month) || $month == -1 || $month == 13)
    $month= 12;

$fin= $actual_year + 2;
if ($year == $actual_year)
    $end_month= $actual_month;
else
    $end_month= 12;

$obj= new Triesgo($clink);
$obj->SetDay(null);
$obj->SetMonth(null);
$obj->SetYear($year);

$date_cut= $year.'-'.str_pad($month, ' 0', 2, STR_PAD_LEFT).'-'.str_pad($day, '0', 2, STR_PAD_LEFT);

$obj_signal= new Triesgo_signals($clink);
$obj_user= new Tusuario($clink);

$ifestrategico= !is_null($_GET['estrategico']) ? $_GET['estrategico'] : 1;
$sst= !is_null($_GET['sst']) ? $_GET['sst'] : 1;
$ma= !is_null($_GET['ma']) ? $_GET['ma'] : 1;
$econ= !is_null($_GET['econ']) ? $_GET['econ'] : 1;
$reg= !is_null($_GET['reg']) ? $_GET['reg'] : 1;
$info= !is_null($_GET['info']) ? $_GET['info'] : 1;
$calidad= !is_null($_GET['calidad']) ? $_GET['calidad'] : 1;
$origen= !is_null($_GET['origen']) ? $_GET['origen'] : 1;

$url_page= "../html/riesgo.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&estrategico=$ifestrategico&sst=$sst&ma=$ma&econ=$econ&origen=$origen&reg=$reg";
$url_page.= "&year=$year&month=$month&day=$day";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <?php 
    if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
        $delay= (int)$config->delay*60;
        header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
    } 
    ?>

    <title>TABLERO DE RIESGOS</title>

    <?php
    $tipo_plan= _PLAN_TIPO_PREVENCION;
    $array_id_show= null;

    require_once "inc/_tablero_top_riesgo.inc.php";
    ?>

    <script language="javascript" type="text/javascript">
    function listar_objetivos_ci() {
        var id_proceso = $('#proceso').val();
        // var month= $('#month').val();
        var year = $('#year').val();
        var action = $('#permit_change').val() ? "edit" : "list";
        var permit_change = $('#permit_change').val() ? 1 : 0;

        var url = '../form/lobjetivo_ci.php?version=&action=' + action +
            '&signal=<?=$signal?>&id_proceso=' + id_proceso;
        // url+= '&month=' + month;
        url += '&year=' + year + '&permit_change=' + permit_change;

        self.location.href = url;
    }
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
    $acc= $_SESSION['acc_planrisk'];
    require_once "../form/inc/_riesgo_top_div.inc.php";

    $action= 'list';
    if (($id_responsable == $_SESSION['id_usuario'] && $_SESSION['nivel'] >= _PLANIFICADOR) || $_SESSION['nivel'] >= _SUPERUSUARIO)
        $action= 'edit';
    $permit_aprove= false;
    if (($id_responsable == $_SESSION['id_usuario'] && $_SESSION['nivel'] >= _PLANIFICADOR) || $_SESSION['nivel'] >= _SUPERUSUARIO)
        $permit_aprove= true;
    ?>

    <div class="app-body dashboard container-fluid sixthbar">
        <?php
        $obj->SetIfEconomico($econ);
        $obj->SetIfEstrategico($ifestrategico);
        $obj->SetIfMedioambiental($ma);
        $obj->SetIfExterno($origen);
        $obj->SetIfSST($sst);
        $obj->SetIfRegulatorio($reg);
        $obj->SetIfInformatico($info);
        $obj->SetIfCalidad($calidad);

        if (!empty($id_select_prs) && $id_select_prs != -1) {
            $obj->SetIdProceso($id_select_prs);
            $obj->set_id_proceso_code($id_proceso_code);
        }
        if ($id_select_prs == -1)
            $obj->SetIdProceso(null);

        $obj->SetIdUsuario(null);
        $obj->setEstado(null);
        $ranking= $obj->listar_and_ranking(false, true, $config->automatic_risk);

        $array_id_show= array();

        if (is_array($ranking)) {
            reset($ranking);
            foreach ($ranking as $array) {
                if (empty($array['id'])) {
                    ++$nhide;
                    continue;
                }

                if (array_key_exists($array['id'], $array_id_show))
                    continue;
                else
                    $array_id_show[$array['id']]= $array['id'];

                $id= $array['id'];
                $obj->SetIdRiesgo($id);
                $obj->Set();

                $prs= $array_procesos_entity[$obj->GetIdProceso()];
                $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
                $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;

                $nombre= $obj->GetNombre();
                $id_proceso_asigna= $obj->GetIdProceso();
                $id_usuario= $obj->GetIdResponsable();
                $email= $obj_user->GetEmail($id_usuario);
                $usuario= textparse($email['nombre']).', '.textparse($email['cargo']).'  '.odbc2date($obj->get_kronos());

                $email= $obj_user->GetEmail($_array['id_usuario']);
                $registro= textparse($email['nombre']).' '.textparse($email['cargo']).'  '.odbc2time_ampm($_array['cronos']);

                if ($id_select_prs == -1 || $id_select_prs == $_SESSION['id_entity'])
                    $obj->SetIdProceso($array['id_proceso']);
                else
                    $obj->SetIdProceso($id_proceso);

                $obj->SetIdUsuario(null);
                $obj->setEstado(null);
                $_array= $obj->getRiesgo_reg(null, null, "desc");

                $month1= (int)date('m', strtotime($obj->GetFechaInicioPlan()));
                $month2= (int)date('m', strtotime($obj->GetFechaFinPlan()));

                ++$nshow;
                $string_id_show.= ($nshow > 1) ? ",".$id : $id;
        ?>
        <input type="hidden" id="riesgo_<?=$id?>" value="<?= textparse($obj->GetNombre())?>" />

        <input type="hidden" id="estado_<?=$id?>" value="<?=$estado_riesgo_array[$_array['estado']]?>" />
        <!--
        <input type="hidden" id="fecha_inicio_<?=$id?>" value="<?= $obj->GetFechaInicioPlan()?>" />
        <input type="hidden" id="fecha_fin_<?=$id?>" value="<?= $obj->GetFechaFinPlan()?>" />
        -->
        <input type="hidden" id="_probabilidad_<?=$id?>" value="<?= $frecuencia_array[$array['frecuencia']]?>" />
        <input type="hidden" id="_impacto_<?=$id?>" value="<?= $impacto_array[$_array['impacto']]?>" />
        <input type="hidden" id="_deteccion_<?=$id?>" value="<?= $deteccion_array[$array['deteccion']]?>" />
        <input type="hidden" id="reg_fecha_<?=$id?>" value="<?= odbc2date($array['reg_fecha'])?>" />

        <input type="hidden" id="registro_<?=$id?>" value="<?=$registro?>" />
        <input type="hidden" id="observacion_<?=$id?>" value="<?= textparse(purge_html($array['observacion']))?>" />

        <input type="hidden" id="usuario_<?=$id?>" value="<?=$usuario?>" />
        <input type="hidden" id="manifestacion_<?=$id?>" value="<?= textparse(purge_html($obj->GetDescripcion()))?>" />

        <input type="hidden" id="probabilidad_<?=$id?>" value="<?=$frecuencia_array[$obj->getFrecuencia()]?>" />
        <input type="hidden" id="impacto_<?=$id?>" value="<?=$impacto_array[$obj->getImpacto()]?>" />
        <input type="hidden" id="deteccion_<?=$id?>" value="<?=$deteccion_array[$obj->getDeteccion()]?>" />

        <input type="hidden" id="if_entity_<?=$id?>" value="<?=$if_entity?>" />


        <div class="box box-<?=$obj_signal->write_img($_array)?> box-solid win-board">
            <div class="box-header with-border">
                <?php if (array_key_exists($id_proceso_asigna, $array_proceso_conected) && $id_proceso_asigna != $_SESSION['id_entity']) { ?>
                <img class='img-rounded icon' src='<?=_SERVER_DIRIGER ?>img/transmit.ico'
                    alt='desde la transmisión de datos' style="margin-right: 5px;" />
                <?php } ?>
                <h3 class="box-title">
                    <?= "{$nivel_array[$_array['nivel']]} ({$_array['prioridad']})" ?>
                    <?= odbc2date($_array['reg_fecha']) ?>
                </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" onclick="ShowContentRiesgo(<?= $id ?>);">
                        <i class="fa fa-wrench"></i>
                    </button>
                </div>
                <!-- /.box-tools -->
            </div>

            <!-- /.box-header -->
            <div class="box-body">
                <div class="row col-12">
                    <?= textparse(purge_html($nombre)) ?>
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <div class="row">
                    <div class="col border-right">
                        <div class="description-block">
                            <div class="description-header">Vigencia:</div>
                            <div class="description-text">
                                <?= "{$meses_short_array[$month1]} / {$meses_short_array[$month2]}, " . date('Y', strtotime($obj->GetFechaInicioPlan())) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col border-right">
                        <div class="description-block">
                            <div class="description-header">Probabilidad: </div>
                            <div class="description-text">
                                <?= $frecuencia_array[$_array['frecuencia']] ?>
                            </div>
                        </div>
                    </div>
                    <div class="col border-right">
                        <div class="description-block">
                            <div class="description-header">Impacto:</div>
                            <div class="description-text">
                                <?= $impacto_array[$_array['impacto']] ?>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="description-block">
                            <div class="description-header">Nivel Detección:</div>
                            <div class="description-text">
                                <?= $deteccion_array[$_array['deteccion']] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->

        <?php } } ?>
    </div> <!-- app-body -->


    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-11">FILTRADO DE RIESGOS</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body info-panel">
            <div class="col-12">
                <label class="checkbox text">
                    <input type="checkbox" name="econ" id="econ" value="1"
                        <?php if (!empty($econ)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos con impacto económico/financiero.
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="ifestrategico" id="ifestrategico" value="1"
                        <?php if (!empty($ifestrategico)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos Estratégicos (Afectan el cumplimiento de los Objetivos Estratégicos)
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="sst" id="sst" value="1"
                        <?php if (!empty($sst)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos relacionados con la Gestión de la Seguridad y Salud en el Trabajo. (Riesgos
                    Laborales)
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="ma" id="ma" value="1"
                        <?php if (!empty($ma)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos relacionados con la Gestión Mediambiental. (Impacta la Gestión Mediaombiental
                    y/o al entorno natural o medioambiente)
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="info" id="info" value="1"
                        <?php if (!empty($info)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos relacionados con la Tecnologías informáticas o con el Plan de Seguridad
                    Informática
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="calidad" id="calidad" value="1"
                        <?php if (!empty($calidad)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos relacionados con la el Sistema de Gestión de la Calidad
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="reg" id="reg" value="1"
                        <?php if (!empty($reg)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos que contituyen violaciones de lo establecidos. (Estan asociados a la violación
                    de procedimientos, normas y legislaciones vigentes aplicables)
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="origen" id="origen" value="1"
                        <?php if (!empty($origen)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos de origen externo. (Se originan por el accionar de entes externos a la
                    organización)
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="calidad" id="calidad" value="1"
                        <?php if (!empty($origen)) echo "checked='checked'" ?> />
                    Mostrar los Riesgos relacionados con el Sistema de Gestion de la Calidad. (Violación de los
                    requisitos de las normas de gestión)
                </label>
            </div>

            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="refreshp()">Aceptar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
            </div>
        </div>
    </div>


    <div id="bit" class="loggedout-follow-normal" style="width: 30%">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>

        <div id="bitsubscribe">
            <div class="row">
                <div class="row">
                    <div class="col-3">
                        <div class="alarm-box small bg-red"></div>
                    </div>
                    <label class="text col-8">
                        MUY ALTO o SEVERO
                    </label>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="alarm-box small bg-orange"></div>
                    </div>
                    <label class="text col-8">
                        SIGNIFICATIVO o ALTO
                    </label>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="alarm-box small bg-yellow"></div>
                    </div>
                    <label class="text col-8">
                        MODERADO
                    </label>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="alarm-box small bg-aqua"></div>
                    </div>
                    <label class="text col-8">
                        BAJO
                    </label>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="alarm-box small bg-gray"></div>
                    </div>
                    <label class="text col-8">
                        TRIVIAL o INSIGNIFICANTE
                    </label>
                </div>
            </div>
        </div><!-- #bitsubscribe -->
    </div><!-- #bit -->

    <!-- #win-board-signal -->
    <div id="win-board-signal" class="card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="signal-title" class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>    
            </div>
        </div>

        <div class="card-body">
            <div id="win-board-signal-project-icon" class="btn-toolbar">
                <a id="img_dox" class="btn btn-app btn-primary text-white" onclick="showWindow('<?=$action?>')"
                    title="Gestión de documentos y actas adjuntos">
                    <i class="fa fa-file-text"></i>Documentos
                </a>

                <a class="btn btn-app btn-info text-white" title="ver el estado de las tareas" onclick="mostrar('tareas')">
                    <i class="fa fa-check-square"></i>Estado
                </a>

                <?php if ($permit_change) { ?>
                <a class="btn btn-app btn-primary text-white" title="definir estado de la gestión" onclick="mostrar('register')">
                    <i class="fa fa-check"></i>Registrar
                </a>
                <?php } ?>
                <?php if ($permit_repro) { ?>
                <a id="img_copy" class="btn btn-app btn-primary text-white" title="copiar para en el proximo año"
                    onclick="mostrar('repro')">
                    <i class="fa fa-copy"></i>Copiar
                </a>
                <?php } ?>

                <?php if ($permit_change) { ?>
                <a id="img_edit" class="btn btn-app btn-warning text-white" title="editar" onclick="_edit()">
                    <i class="fa fa-edit"></i>Editar
                </a>

                <a id="img_delete" class="btn btn-app btn-danger text-white" title="eliminar el riesgo" onclick="_delete()">
                    <i class="fa fa-trash"></i>Eliminar
                </a>
                <?php } ?>
            </div>

            <div class="container-fluid">
                <div class="win-body">
                    <label class="label label-primary">ÚLTIMO REGISTRO:</label>

                    <ul class="list-inline" style="margin-top: 4px; margin-bottom: 2px;">
                        <strong>En fecha: </strong>
                        <label class="text" id="reg_fecha"></label>
                    </ul>


                    <ul class="row" style="margin-top: 0px;">
                        <li class="col">
                            <strong>Estado</strong><br />
                            <label class="text" id="estado"></label>
                        </li>
                        <li class="col">
                            <strong>Probabilidad</strong><br />
                            <label class="text" id="_probabilidad"></label>
                        </li>
                        <li class="col">
                            <strong>Impacto</strong><br />
                            <label class="text" id="_impacto"></label>
                        </li>
                        <li class="col">
                            <strong>Nivel de detección</strong><br />
                            <label class="text" id="_deteccion"></label>
                        </li>
                    </ul>

                    <div class="list-group" style="margin-bottom: 4px;">
                        <strong>Registro: </strong><label class="text" id="registro"></label>
                    </div>
                    <strong>Observaciones:</strong>
                    <p id="observacion"></p>

                    <label class="label label-primary">ESTADO INICIAL:</label>
                    <ul class="row" style="margin-top: 8px; margin-bottom: 2px;">
                        <li class="col">
                            <strong>Probabilidad</strong><br />
                            <label class="text" id="probabilidad"></label>
                        </li>
                        <li class="col">
                            <strong>Impacto</strong><br />
                            <label class="text" id="impacto"></label>
                        </li>
                        <li class="col">
                            <strong>Nivel de detección</strong><br />
                            <label class="text" id="deteccion"></label>
                        </li>
                    </ul>

                    <div class="row" style="margin-bottom: 4px;">
                        <strong>Identificado por: </strong> 
                        <label class="text" id="usuario"></label>
                    </div>
                    <strong>Manifestación: </strong>
                    <p id="manifestacion"></p>

                </div>
            </div>
        </div>
    </div><!-- #win-board-signal -->


    <?php require_once "inc/_tablero_bottom_riesgo.inc.php"; ?>

    <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

    </div>

    <input type="hidden" id="nums_id_show" name="nums_id_show" value="<?=$nshow?>" />
    <input type="hidden" id="array_id_show" name="array_id_show" value="<?=$string_id_show?>" />

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$nshow?>;
    document.getElementById('nhide').innerHTML = <?=$nhide?>;
    </script>

</body>

</html>