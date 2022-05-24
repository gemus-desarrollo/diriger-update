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
require_once "../php/class/riesgo.class.php";

require_once "../php/class/traza.class.php";

$obj = new Triesgo($clink);

$year = !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month = !empty($_GET['month']) ? $_GET['month'] : null;

$ifestrategico = !is_null($_GET['estrategico']) ? $_GET['estrategico'] : 1;
$sst = !is_null($_GET['sst']) ? $_GET['sst'] : 1;
$ma = !is_null($_GET['ma']) ? $_GET['ma'] : 1;
$econ = !is_null($_GET['econ']) ? $_GET['econ'] : 1;
$reg = !is_null($_GET['reg']) ? $_GET['reg'] : 1;
$info = !is_null($_GET['info']) ? $_GET['info'] : 1;
$origen = !is_null($_GET['origen']) ? $_GET['origen'] : 1;
$calidad = !is_null($_GET['calidad']) ? $_GET['calidad'] : 1;

$id_proceso = $_GET['id_proceso'];

$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);

$obj->SetIfEconomico($econ);
$obj->SetIfEstrategico($ifestrategico);
$obj->SetIfMedioambiental($ma);
$obj->SetIfExterno($origen);
$obj->SetIfSST($sst);
$obj->SetIfRegulatorio($reg);
$obj->SetIfInformatico($info);
$obj->SetIfCalidad($calidad);
$obj->SetIdProceso($id_proceso);

$obj->SetIdProceso($id_proceso);

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso = $obj_prs->GetNombre();
$tipo_prs = $obj_prs->GetTipo();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE RIESGOS", "Corresponde a periodo año: $year");
?>


<html>
    <head>
        <title>LISTADO DE RIESGOS</title>

        <?php require "inc/print_top.inc.php";?>


        <div class="page center">
            <div class="container-fluid center">
                <h1>
                    <p>ESTADO DE RIESGOS ACTIVOS</p>
                    <p><?= strtoupper($meses_array[(int)$month]) ?></p>
                    <p>A&Ntilde;O <?= $year ?></p>
                </h1>
            </div>


            <table class="center" width="100%">
                <thead>
                    <tr>
                        <?php
                        $ncolumn = 6;
                        $array_tmp_riesgo = array();
                        ?>

                        <th class="plhead left">No.</th>

                        <?php
                        if ($config->riskseeactivity) {
                            ++$ncolumn
                        ?>
                        <th class="plhead">ACTIVIDAD</th>
                        <?php } ?>
                        <th class="plhead">RIESGO</th>

                        <?php
                        if ($config->riskseedescription) {
                            ++$ncolumn
                        ?>
                            <th class="plhead">MANIFESTACIÓN</th>
                        <?php } ?>

                        <?php
                        if ($config->riskseetype1) {
                            ++$ncolumn
                        ?>
                            <th class="plhead">ORIGEN</th>
                        <?php } ?>

                        <th class="plhead">IMPACTO</th>
                        <th class="plhead">FRECUENCIA</th>
                        <th class="plhead">NIVEL DE RIESGO</th>

                        <?php if ($config->riskseedetection) {
                            ++$ncolumn
                                    ?>
                            <th>NIVEL DE DETECCIÓN</th>
                        <?php } ?>
                        <th class="plhead">PRIORIDAD</th>
                            <?php if ($config->riskseestate || $config->riskseeobserv) {
                                ++$ncolumn
                            ?>
                            <th class="plhead">
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
                    $i = 0;
                    $ranking = $obj->listar_and_ranking(false, true);
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
                                    $array_tmp_riesgo[] = $array['id'];
                            }
                        }
                    }
                    */
                    reset($ranking);
                    foreach ($ranking as $array) {
                        /*
                        if (array_search($array['id'], $array_tmp_riesgo))
                            continue;
                        */    
                        write_riesgo($i, $array);
                    }
                    /*
                    if ($config->riskseeprocess) {
                        reset($obj_prs->array_procesos);

                        foreach ($obj_prs->array_procesos as $prs) {
                            if ($prs['tipo'] != _TIPO_PROCESO_INTERNO)
                                continue;
                            $cant = $obj->list_riesgos_in_process($prs['id']);

                            if ($cant > 0) {
                                ?>
                                <tr>
                                    <td class="plinner left right" colspan="<?=$ncolumn?>">
                                        <span style="font-size: bolder!important;"><?= $prs['nombre'] ?></span>
                                    </td>
                                </tr>
                                <?php
                                reset($ranking);
                                foreach ($ranking as $array) {
                                    if (!array_key_exists($array['id'], $obj->array_riesgos))
                                        continue;
                                    write_riesgo($i, $array);
                                }
                            }
                        }
                    }
                    */
                    ?>
                </tbody>
            </table>

        </div>

    <?php require "inc/print_bottom.inc.php";?>


        <?php
        function write_riesgo(&$i, $array) {
            global $clink;
            global $config;

            global $estado_riesgo_array, $frecuencia_array, $impacto_array, $nivel_array, $deteccion_array;
            $obj = new Triesgo($clink);
            $obj->SetIdRiesgo($array['id']);
            $obj->Set();

            $obj_prs = new Tproceso($clink);
            ?>
            <tr>
                <td class="plinner left">
                <?= ++$i ?>
                </td>
                    <?php if ($config->riskseeactivity) { ?>
                    <td class="plinner">
                    <?= nl2br($obj->GetLugar()) ?>
                    </td>
                    <?php } ?>
                <td class="plinner">
                    <?= $obj->GetNombre() ?>
                </td>
                <?php if ($config->riskseedescription) { ?>
                    <td class="plinner">
                        <?= nl2br(purge_html($obj->GetDescripcion())) ?>
                    </td>
                <?php } ?>
                <?php if ($config->riskseetype1) { ?>
                    <td class="plinner">
                        <?= $obj->GetIfEXterno() ? "EXTERNO" : "INTERNO" ?>
                    </td>
                <?php } ?>
                <td class="plinner">
                    <?= $impacto_array[$array['impacto']] . ' (' . $array['impacto'] . ')' ?>
                </td>
                <td class="plinner">
                    <?= $frecuencia_array[$array['frecuencia']] . ' (' . $array['frecuencia'] . ')' ?>
                </td>
                <td class="plinner">
                    <?= $nivel_array[$array['nivel']] . ' (' . $array['nivel'] . ')' ?>
                </td>

                <?php if ($config->riskseedetection) { ?>
                    <td class="plinner">
                <?= $deteccion_array[$array['deteccion']] . ' (' . $array['deteccion'] . ')'; ?>
                    </td>
                <?php } ?>
                <td class="plinner">
                    <?= $array['prioridad']; ?>
                </td>
                <?php if ($config->riskseestate || $config->riskseeobserv) { ?>
                    <td class="plinner">
                <?php
                if ($config->riskseestate)
                    echo $estado_riesgo_array[$array['estado']] . "<br><br>";
                if ($config->riskseeobserv)
                    echo purge_html($array['observacion']);
                ?>
            </td>
        <?php } ?>
        </tr>
<?php } ?>