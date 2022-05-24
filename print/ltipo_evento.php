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
require_once "../php/class/tipo_evento.class.php";

require_once "../php/class/proceso.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] :  date('Y');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj= new Ttipo_evento($clink);
$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
?>


<html>
    <head>
        <title>CLASIFICACIÓN DE ACTIVIDADES</title>

        <?php require "inc/print_top.inc.php";?>

        <style type="text/css">
            ul {
                list-style:none;
                text-align: left;
                margin-top: 10px;
            }
            li {
                list-style:none;
                text-align: left;
                margin-top: 10px;
            }
            span {
                margin-right: 10px;
            }
        </style>

        <div class="page center">

            <div class="container-fluid center">
                <div align="center" class="title-header">
                    <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                        CLASIFICACIÓN DE ACTIVIDADES <br/> <?=$year?>
                    </div>
                </div>
            <ul>
            <?php for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                <li>
                    <strong style="font-size: 1.3em;">
                        <?= number_format_to_roman($i - 1) ?>
                        <span><?= $tipo_actividad_array[$i] ?></span>
                    </strong>

                    <?php
                    $result = $obj->listar($i, 0);
                    $cant = $obj->GetCantidad();
                    if (empty($cant))
                        continue;
                    ?>
                    <ul>
                        <?php while ($row = $clink->fetch_array($result)) { ?>
                            <li>
                                <strong><?= $row['numero'] ?></strong>
                                <span><?= $row['nombre'] ?></span>
                                <span><?= $row['descripcion'] ?></span>

                                <?php
                                $result_sub = $obj->listar($i, $row['id']);
                                $cant = $obj->GetCantidad();
                                if (empty($cant)) continue;
                                ?>
                                <ul>
                                    <?php while ($row_sub = $clink->fetch_array($result_sub)) { ?>
                                        <li>
                                            <strong><?= $row_sub['numero'] ?></strong>
                                            <span><?= $row_sub['nombre'] ?></span>
                                            <span><?= $row_sub['descripcion'] ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        </div>

        <?php require "inc/print_bottom.inc.php";?>
