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

require_once "../php/class/proceso.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";

$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();
$id_proceso= empty($_GET['id_proceso']) || $_GET['id_proceso'] == -1 ? $_SESSION['local_proceso_id'] : $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso)) {
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $proceso= $obj_prs->GetNombre();
    $tipo_prs= $obj_prs->GetTipo();
}

$obj= new Ttipo_lista($clink);
?>


<html>
    <head>
        <title>ESTRUCTURA DE LA GUÍA DE CONTROL</title>

        <?php require "inc/print_top.inc.php";?>

        <style type="text/css">
            ul {
                list-style:none;
                text-align: left;
                margin-top: 6px;
            }
            li {
                list-style:none;
                text-align: left;
                margin-top: 6px;
            }
            span {
                margin-right: 10px;
            }
        </style>

        <div class="page center">

            <div class="container-fluid center">
                <div align="center" class="title-header">
                    <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                        ESTRUCTURA DE LA GUÍA DE CONTROL<br/> <?=$year?>
                    </div>
                </div>

                <ul>
                    <?php
                    $nshow= 0;
                    $nhide= 0;

                    $obj->SetIdLista($id_lista);
                    $obj->SetYear($year);
                    $obj->SetIdProceso($id_proceso);

                    for ($i = 1; $i < _MAX_COMPONENTES_CI; ++$i) {
                    ?>
                        <li>
                            <strong style="font-size: 1.3em;">
                                <?= number_format_to_roman($i) ?>
                                .&nbsp;
                                <?= $Tambiente_control_array[$i] ?>
                            </strong>

                            <?php
                            $result = $obj->listar($i, 0);
                            $cant = $obj->GetCantidad();
                            if (!empty($cant)) {
                            ?>

                                <ul>
                                    <?php
                                    while ($row = $clink->fetch_array($result)) {
                                        if (!empty($year) && (!empty($row['year']) && $row['year'] < $year)) {
                                               ++$nhide;
                                               continue;
                                           }
                                           ++$nshow;
                                    ?>
                                        <li>
                                            <strong><?= $row['numero'] ?></strong>
                                            &nbsp;
                                            <a name="<?= $row['id'] ?>"></a>

                                            <span class="year-label">
                                                (<?="{$row['inicio']} - {$row['fin']}"?>)
                                            </span>

                                            <?= $row['nombre'] ?>
                                            &nbsp;
                                            <?= textparse($row['descripcion']) ?>

                                            <?php
                                            $result_sub = $obj->listar($i, $row['id']);
                                            $cant = $obj->GetCantidad();

                                            if (!empty($cant)) {
                                            ?>
                                                <ul>
                                                    <?php
                                                    while ($row_sub = $clink->fetch_array($result_sub)) {
                                                        if (!empty($year) && (!empty($row_sub['year']) && $row_sub['year'] < $year)) {
                                                            ++$nhide;
                                                            continue;
                                                        }
                                                        ++$nshow;
                                                    ?>
                                                        <li>
                                                            <strong><?= $row_sub['numero'] ?></strong>
                                                            &nbsp;

                                                            <span class="year-label">
                                                                (<?="{$row_sub['inicio']} - {$row_sub['fin']}"?>)
                                                            </span>

                                                            <?= textparse($row_sub['nombre']) ?>
                                                            &nbsp;
                                                            <?= textparse($row_sub['descripcion']) ?>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php require "inc/print_bottom.inc.php";?>
