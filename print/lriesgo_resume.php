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

$obj->SetDay(null);
$obj->SetMonth(null);
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
                <h1><p>LEVANTAMIENTO DE RIESGOS</p> <p>A&Ntilde;O <?= $year ?></p></h1>
            </div>


            <table class="center" width="100%">
                <thead>
                    <tr>
                        <?php
                        $ncolumn = 8;
                        $array_tmp_riesgo = array();
                        ?>

                        <th class="plhead left" rowspan="2">No.</th>

                        <?php
                        if ($config->riskseeactivity) {
                            ++$ncolumn;
                        ?>
                            <th class="plhead" rowspan="2">ACTIVIDAD</th>
                        <?php } ?>
                        <th class="plhead" rowspan="2">RIESGO</th>

                        <?php
                        if ($config->riskseedescription) {
                            ++$ncolumn;
                        ?>
                            <th class="plhead" rowspan="2">MANIFESTACIÓN</th>
                        <?php } ?>

                        <?php
                        if ($config->riskseetype1) {
                            ++$ncolumn;
                        ?>
                            <th class="plhead" rowspan="2">ORIGEN</th>
                        <?php } ?>

                        <th class="plhead" rowspan="2">IMPACTO</th>
                        <th class="plhead" rowspan="2">FRECUENCIA</th>
                        <th class="plhead" rowspan="2">NIVEL DE RIESGO</th>

                        <?php if ($config->riskseedetection) {
                            ++$ncolumn;
                        ?>
                            <th class="plhead" rowspan="2">NIVEL DE DETECCIÓN</th>
                        <?php } ?>

                        <th class="plhead" rowspan="2">PRIORIDAD</th>

                        <?php if ($config->riskseestate || $config->riskseeobserv) {
                            ++$ncolumn
                        ?>
                        <th class="plhead" rowspan="2">
                        <?php
                        if ($config->riskseestate)
                            echo "ESTADO";
                        if ($config->riskseeobserv) {
                            if ($config->riskseestate)
                                echo " / ";
                            echo "OBSERVACIÓN";
                        }
                        ?>
                        </th>
                        <?php } ?>

                        <th class="plhead" colspan="2">CONDICIONES Y CAUSAS</th>
                    </tr>

                    <tr>
                        <th class="plhead">FECHA</th>
                        <th class="plhead">CONDICIÓN O CAUSA</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    $ranking = $obj->listar_and_ranking(false, false, false, false);
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
                        if (array_search($array['id'], $array_tmp_riesgo) === false) 
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
        $obj= new Triesgo($clink);
        $obj->SetIdRiesgo($array['id']);
        $obj->Set();

        $obj_prs = new Tproceso($clink);
        $obj_user = new Tusuario($clink);

        $result= $obj->listar_causas();
        $cant= $obj->GetCantidad();
        $rowspan= $cant > 1 ? $cant : 1;

        ?>
        <tr>
            <td class="plinner left" rowspan="<?=$rowspan?>">
            <?= ++$i ?>
            </td>
            <?php if ($config->riskseeactivity) { ?>
            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= textparse($obj->GetLugar()) ?>
            </td>
            <?php } ?>

            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= textparse(purge_html($obj->GetNombre())) ?>
            </td>

            <?php if ($config->riskseedescription) { ?>
                <td class="plinner" rowspan="<?=$rowspan?>">
                    <?= textparse(purge_html($obj->GetDescripcion())) ?>
                </td>
            <?php } ?>

            <?php if ($config->riskseetype1) { ?>
                <td class="plinner" rowspan="<?=$rowspan?>">
                    <?= $obj->GetIfEXterno() ? "EXTERNO" : "INTERNO" ?>
                </td>
            <?php } ?>

            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= $impacto_array[$array['impacto']] . ' (' . $array['impacto'] . ')' ?>
            </td>

            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= $frecuencia_array[$array['frecuencia']] . ' (' . $array['frecuencia'] . ')' ?>
            </td>

            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= $nivel_array[$array['nivel']] . ' (' . $array['nivel'] . ')' ?>
            </td>

            <?php if ($config->riskseedetection) { ?>
                <td class="plinner" rowspan="<?=$rowspan?>">
                    <?= $deteccion_array[$array['deteccion']] . ' (' . $array['deteccion'] . ')'; ?>
                </td>
            <?php } ?>

            <td class="plinner" rowspan="<?=$rowspan?>">
                <?= $array['prioridad']; ?>
            </td>
            <?php if ($config->riskseestate || $config->riskseeobserv) { ?>
                <td class="plinner" rowspan="<?=$rowspan?>">
                    <?php
                    if ($config->riskseestate)
                        echo $estado_riesgo_array[$array['estado']] . "<br><br>";
                    if ($config->riskseeobserv)
                        echo textparse(purge_html($array['observacion']));
                    ?>
                </td>
            <?php } ?>

            <?php
            if ($cant > 0) {
                $j= 0;
                while ($row= $clink->fetch_array($result)) {
                    ++$j;
                ?>
                    <?php if ($j > 1) { ?>
                        <tr>
                    <?php } ?>
                        <td class="plinner">
                            <?=odbc2date($row['fecha'])?>
                        </td>
                        <td class="plinner">
                            <?=textparse($row['descripcion'])?>
                        </td>
                </tr>
                <?php }  ?>
            <?php } else { ?>
                <td class="plinner"></td>
                <td class="plinner"></td>
            </tr>
        <?php } ?>
    <?php } ?>
