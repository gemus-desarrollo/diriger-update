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
require_once "../php/class/tablero.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/inc_escenario_init.php";

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

$obj= new Ttablero($clink);
$result= $obj->listar();
?>

<html>
    <head>
        <title>LISTADO DE TABLEROS DE INDICADORES</title>

        <?php require "inc/print_top.inc.php";?>

        <style type="text/css">
            hr {
                margin: 2px 0px 4px 0px;
                border: 1px solid black;
            }
        </style>

        <div class="page center">
            <div class="container-fluid center">
                LISTADO DE TABLEROS DE INDICADORES<br/><?= $meses_array[(int) $month] ?>/<?= $year ?>
            </div>
        </div>

        <br/><br/>
        <div class="page center">
            <table cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th class="plhead left" style="min-width: 140px;">TABLERO</th>
                        <th class="plhead" style="min-width: 230px;">PROPÓSITO</th>
                        <th class="plhead" style="min-width: 400px;">INDICADORES</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= $row['nombre'] ?></td>
                        <td class="plinner"><?= textparse($row['descripcion']) ?></td>

                        <td class="plinner">
                            <?php
                            $obj->SetYear($year);
                            $obj->SetIdTablero($row['id']);
                            $result_indi = $obj->listar_indicadores();

                            $i = 0;
                            $array_ids= array();
                            while ($row_indi = $clink->fetch_array($result_indi)) {
                                if ($array_ids[$row_indi['_id']])
                                    continue;
                                $array_ids[$row_indi['_id']]= 1;
                                
                                ++$i;
                                if ($i > 1) echo "<hr />";
                                echo $row_indi['_nombre'];

                                $obj_prs->Set($row_indi['_id_proceso']);
                                echo "<span class='comment' style='margin-left: 8px'>(" . $obj_prs->GetNombre() . ', ' . $Ttipo_proceso_array[$obj_prs->GetTipo()] . ")</span>";
                                echo "<br />";
                            }
                            ?>
                        </td>

                    </tr>
                    <?php ++$i;
                } ?>
                </tbody>
            </table>

        </div>

    <?php require "inc/print_bottom.inc.php";?>

