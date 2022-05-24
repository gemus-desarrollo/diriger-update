<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/riesgo.class.php";

require_once "../php/inc_escenario_init.php";

$signal= 'lriesgo';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';
if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj']))  
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
}
else {
    $obj= new Triesgo($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new TTime();
$actual_year= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= $time->GetDay();

$inicio= $actual_year - 3;
$fin= $actual_year + 3;

$year= 0;
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['_year'];
if (empty($year)) 
    $year= $actual_year;

if (empty($month) || $month == -1 || $month == 13) 
    $month= 12;

$fin= $actual_year + 2;
if ($year == $actual_year) 
    $end_month= $actual_month;
else 
    $end_month= 12;

$_SESSION['_month']= $month;
$_SESSION['_year']= $year;

if ($month == -1 || empty($month)) 
    $month= $actual_month;

$month_init= 1; $month_end= 12;

$obj->SetDay(null);
$obj->SetMonth(null);
$obj->SetYear($year);

$ifestrategico= !is_null($_GET['estrategico']) ? $_GET['estrategico'] : 1;
$sst= !is_null($_GET['sst']) ? $_GET['sst'] : 1;
$ma= !is_null($_GET['ma']) ? $_GET['ma'] : 1;
$econ= !is_null($_GET['econ']) ? $_GET['econ'] : 1;
$reg= !is_null($_GET['reg']) ? $_GET['reg'] : 1;
$info= !is_null($_GET['info']) ? $_GET['info'] : 1;
$origen= !is_null($_GET['origen']) ? $_GET['origen'] : 1;
$calidad= !is_null($_GET['calidad']) ? $_GET['calidad'] : 1;

$obj->SetIfEconomico($econ);
$obj->SetIfEstrategico($ifestrategico);
$obj->SetIfMedioambiental($ma);
$obj->SetIfExterno($origen);
$obj->SetIfSST($sst);
$obj->SetIfRegulatorio($reg);
$obj->SetIfInformatico($info);
$obj->SetIfCalidad($info);
$obj->SetIdProceso($id_proceso);

if (!empty($_GET['id_proceso'])) 
    $id_proceso= $_GET['id_proceso'];
$obj->SetIdProceso($id_proceso);

$obj_user= new Tusuario($clink);
if (isset($obj_prs)) 
    unset($obj_prs);
$obj_prs= new Tproceso($clink);

$url_page= "../form/lriesgo.php?signal=$signal&action=$action&menu=riesgo&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&estrategico=$ifestrategico";
$url_page.= "&sst=$sst&ma=$ma&econ=$econ&origen=$origen&reg=$reg";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE RIESGOS</title>

    <?php require_once "../html/inc/_tablero_top_riesgo.inc.php" ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php require_once "../form/inc/_riesgo_top_div.inc.php"; ?>

    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="RIESGO" />

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">

                <?php
                $ncolumn = 6;
                $array_tmp_riesgo = array();
                ?>

                <thead>
                    <tr>
                        <th>No.</th>
                        <?php
                        if ($config->riskseeactivity) {
                            ++$ncolumn
                        ?>
                        <th>ACTIVIDAD</th>
                        <?php } ?>
                        <th>RIESGO</th>
                        <?php
                        if ($config->riskseedescription) {
                            ++$ncolumn
                        ?>
                        <th>MANIFESTACIÓN</th>
                        <?php } ?>
                        <?php
                        if ($config->riskseetype1) {
                            ++$ncolumn
                        ?>
                        <th>INTERNO</th>
                        <?php } ?>
                        <th>IMPACTO</th>
                        <th>FRECUENCIA</th>
                        <th>NIVEL DE RIESGO</th>
                        <?php
                        if ($config->riskseedetection) {
                            ++$ncolumn
                        ?>
                        <th>NIVEL DE DETECCIÓN</th>
                        <?php } ?>
                        <th>PRIORIDAD</th>
                        <?php
                        if ($config->riskseestate || $config->riskseeobserv) {
                            ++$ncolumn
                        ?>
                        <th>
                            <?php
                            if ($config->riskseestate)
                                echo "ESTADO /";
                            if ($config->riskseeobserv)
                                echo "OBSERVACIÓN";
                            ?>
                        </th>
                        <?php } ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i= 0;
                    $obj->SetIfEconomico($econ);
                    $obj->SetIfEstrategico($ifestrategico);
                    $obj->SetIfMedioambiental($ma);
                    $obj->SetIfExterno($origen);
                    $obj->SetIfSST($sst);
                    $obj->SetIfRegulatorio($reg);
                    $obj->SetIfInformatico($info);
                    $obj->SetIfCalidad($calidad);
                    $obj->SetIdProceso($id_proceso);

                    $ranking= $obj->listar_and_ranking(false, true);
                    /*
                    if ($config->riskseeprocess) {
                        $obj_prs = new Tproceso($clink);
                        $obj_prs->listar_in_order('eq_desc', false, _TIPO_PROCESO_INTERNO, true, 'desc');

                        foreach ($obj_prs->array_procesos as $prs) {
                            if ($prs['tipo'] != _TIPO_PROCESO_INTERNO) 
                                continue;
                            $cant = $obj->list_riesgos_in_process($prs['id']);
                            if ($cant == 0) 
                                continue;

                            reset($ranking);
                            foreach ($ranking as $array) {
                                if (array_key_exists($array['id'], $obj->array_riesgos))
                                    $array_tmp_riesgos[$array['id']]= $array['id'];                                
                            }
                    }   }
                    */
                    reset($ranking);
                    foreach ($ranking as $array) {
                        /*
                        if (array_search($array['id'], $array_tmp_riesgos))
                            continue;
                        */
                        write_riesgo($i, $array);
                    }
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <?php if ($config->riskseeactivity) { ?>
                            <td></td> 
                        <?php } ?>
                        <td></td>
                        <?php if ($config->riskseedescription) { ?>
                            <td></td>
                        <?php } ?>
                        <?php if ($config->riskseetype1) { ?>
                            <td></td>
                        <?php } ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php if ($config->riskseedetection) { ?>
                            <td></td>
                        <?php } ?>
                        <td></td>
                        <?php if ($config->riskseestate || $config->riskseeobserv) { ?> 
                            <td></td> 
                        <?php } ?>
                    </tr>                    
                </tbody>
            </table>
        </div>
    </form>

    <div id="div-ajax-panel" class="ajax-panel">

    </div>


    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-11 m-0">FILTRADO DE RIESGOS</div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="col-md-12">
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

</body>

</html>


<?php
function write_riesgo(&$i, $array) {
    global $clink;
    global $config;

    global $estado_riesgo_array, $frecuencia_array, $impacto_array, $nivel_array, $deteccion_array;
    $obj= new Triesgo($clink);
    $obj->SetIdRiesgo($array['id']);
    $obj->Set();

    $obj_prs= new Tproceso($clink);
    ?>
    <tr>
        <td>
            <?= ++$i?>
        </td>

        <?php if ($config->riskseeactivity) { ?>
            <td>
                <?= textparse($obj->GetLugar()) ?>
            </td>
        <?php } ?>

        <td>
            <?= textparse($obj->GetNombre()) ?>
        </td>
        <?php if ($config->riskseedescription) {?>
            <td>
                <?= purge_html($obj->GetDescripcion()) ?>
            </td>
        <?php } ?>
        <?php if ($config->riskseetype1) {?>
            <td>
                <?= $obj->GetIfEXterno() ? "E" : "I"?>
            </td>
        <?php } ?>

        <td>
            <?= $impacto_array[$array['impacto']].' ('.$array['impacto'].')' ?>
        </td>
        <td>
            <?= $frecuencia_array[$array['frecuencia']].' ('.$array['frecuencia'].')' ?>
        </td>
        <td>
            <?= $nivel_array[$array['nivel']].' ('.$array['nivel'].')' ?>
        </td>
        <?php if ($config->riskseedetection) { ?>
            <td>
                <?= $deteccion_array[$array['deteccion']].' ('.$array['deteccion'].')' ?>
            </td>
        <?php } ?>
        <td>
            <?= $array['prioridad'] ?>
        </td>

        <?php if ($config->riskseestate || $config->riskseeobserv) { ?>

        <td>
            <?php
                if ($config->riskseestate) 
                    echo $estado_riesgo_array[$array['estado']]."<br><br>";
                if ($config->riskseeobserv) 
                    echo purge_html($array['observacion']);
            ?>
        </td>
        <?php } ?>
    </tr>
<?php
}
?>