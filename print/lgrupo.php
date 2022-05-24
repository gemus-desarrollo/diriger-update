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
require_once "../php/class/grupo.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$signal= $_GET['signal'];
$month= !empty($_GET['month']) ? $_GET['month'] : (int)$_SESSION['current_month'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$user_date_ref= $_GET['user_date_ref'];

$obj= new Tgrupo($clink);
$result= $obj->listar();
$cant= $obj->GetCantidad();

$obj_tmp= new Tgrupo($clink);

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();

    $proceso= $obj_prs->GetNombre();
    $tipo_prs= $obj_prs->GetTipo();
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE GRUPOS DE USUARIOS");
?>

<html>
    <head>
        <title>LISTADO DE GRUPOS DE USUARIOS</title>

         <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                RELACIÓN DE GRUPOS DE USUARIOS <br /><?=$meses_array[(int)$month]?>/<?=$year?>
            </div>
        </div>


    <?php
    if (empty($cant)) {
        echo "No hay grupos definidos en el Sistema.";
        exit;
    }
    ?>

    <div class="page center">
        <table width="700px" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left" width="40px">No.</th>
                    <th class="plhead" style="min-width:200px">GRUPO</th>
                    <th class="plhead" style="min-width:200px">DESCRIPCIÓN</th>
                </tr>
            </thead>

            <tbody>
                <?php $i = 0;
                while ($row = $clink->fetch_array($result)) { ?>
                    <tr>
                        <td class="plinner left bottom"><?= ++$i ?></td>
                        <td class="plinner"><?= $row['nombre'] ?></td>
                        <td class="plinner"><?= nl2br($row['descripcion']) ?></td></tr>

                        <?php if (!empty($signal)) { ?>
                        <tr>
                            <td class="plinner left"></td>

                            <td colspan=2 class="plinner right-prn">
                                <table border=0 cellpadding=2 class="none-border">
                                    <?php
                                    $obj_tmp->SetIdGrupo($row['id']);
                                    $obj_tmp->listar_usuarios();

                                    foreach ($obj_tmp->array_usuarios as $array) {
                                    ?>
                                        <tr>
                                            <td class="none-border"><?= $array['nombre'] ?></td>
                                            <td class="none-border"><?= !empty($array['cargo']) ? $array['cargo'] : "" ?></td>
                                            <td class="none-border"> <?= $array['email'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </td>
                        </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>
