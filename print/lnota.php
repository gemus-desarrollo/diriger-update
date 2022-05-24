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

require_once "../php/class/register_nota.class.php";
require_once "../php/class/nota.class.php";

require_once "../php/class/traza.class.php";

$obj= new Tnota($clink);

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : 12;
$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);
$obj->SetTipo($tipo);
$obj->SetIdProceso($id_proceso);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE NOTAS DE HALLAZGOS", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>LISTADO DE NOTAS DE HALLAZGOS</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                <p>RELACIÓN DE NOTAS DE HALLAZGOS</p> <p>A&Ntilde;O <?= $year ?></p>
            </div>
        </div>

    <div class="page center">

        <?php
        $obj_causa= new Tregister_nota($clink);

        $i= 0;
        $obj->SetTipo(null);
        $obj->SetIdProceso($id_proceso == $_SESSION['id_entity'] || $id_proceso == -1 ? null : $id_proceso);
        $obj->SetYear($year);

        $result_nota= $obj->listar($noconf, $mej, $observ);
        ?>

        <table width="100%" cellpadding="0" cellspacing="0" border=1 style="margin: 10px;">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2">No.</th>
                    <th class="plhead" rowspan="2">HALLAZGO</th>
                    <th class="plhead" rowspan="2">LUGAR</th>
                    <th class="plhead" rowspan="2">PROCESO</th>
                    <th class="plhead" rowspan="2">FECHA <br/>DE DETECCIÓN</th>
                    <th class="plhead" rowspan="2">FECHA <br/>DE CIERRE <br/>PLANIFICADA</th>
                    <th class="plhead" colspan="2">ANÁLISIS DE CAUSAS / FACTIBILIDAD</th>
                </tr>
                <tr>
                    <th class="plhead">FECHA</th>
                    <th class="plhead">CAUSAS / FACTIBILIDAD</th>
                </tr>
            </thead>

            <tbody>
            <?php
            if ($obj->GetCantidad() > 0) {
                while ($row= $clink->fetch_array($result_nota)) {
                    $obj_causa->SetIdNota($row['_id']);
                    $obj_causa->listar_causas(true);
                    $array_causas= $obj_causa->array_causas;
                    $cant= $obj_causa->GetCantidad();
                    $rowspan= $cant > 1 ? $cant : 1;
            ?>
                    <tr>
                        <td class="plinner left" rowspan="<?=$rowspan?>">
                            <?=++$i?>
                        <!-- <a name="<?= $row['_id'] ?>"></a> -->
                        </td>
                        <td class="plinner" rowspan="<?=$rowspan?>">
                            <?="({$Ttipo_nota_array[$row['tipo']]}) <br/>{$row['descripcion']}" ?>
                        </td>

                        <td class="plinner" rowspan="<?=$rowspan?>">
                            <?= purge_html($row['_lugar'])?>
                        </td>
                        <td class="plinner" rowspan="<?=$rowspan?>">
                            <?php
                            $obj_prs->Set($row['_id_proceso']);
                            $proceso= $obj_prs->GetNombre();
                            $proceso.= ", ". $Ttipo_proceso_array[$obj_prs->GetTipo()];
                            echo $proceso;
                            ?>
                        </td>
                        <td class="plinner" rowspan="<?=$rowspan?>">
                            <?= odbc2date($row['fecha_inicio_real'])?>
                        </td>
                        <td class="plinner" rowspan="<?=$rowspan?>">
                            <?= odbc2date($row['fecha_fin_plan'])?>
                        </td>
                        <?php
                        $j= 0;
                        foreach ($array_causas as $causa) {
                            ++$j;
                        ?>
                            <?php if ($j > 1) { ?>
                            <tr>
                            <?php } ?>
                            <td class="plinner">
                                <?= odbc2date($causa['fecha'])?>
                            </td>
                            <td class="plinner">
                                <?= textparse($causa['descripcion'])?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php if ($cant == 0) { ?>
                    <td class="plinner"></td>
                    <td class="plinner"></td>
                </tr>
            <?php } } ?>
            <?php } else { ?>
                <tr>
                    <td class="plinner left">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    </div>

    <?php require "inc/print_bottom.inc.php";?>
