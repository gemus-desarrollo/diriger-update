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

require_once "../php/class/unidad.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';
if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
}
else {
 $obj= new Tunidad($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;	
$result= $obj->listar();

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
$obj_prs->Set();

$proceso = $obj_prs->GetNombre();
$tipo_prs = $obj_prs->GetTipo();
?>

<html>

<head>
    <title>LISTADO DE UNIDADES DE MEDIDA</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="page center">
        <div class="container-fluid center">
            LISTADO DE UNIDADES DEFINIDAS EN EL SISTEMA
        </div>
    </div>

    <br /><br />
    <div class="page center">
        <table cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead">UNIDAD</th>
                    <th class="plhead">DECIMALES</th>
                    <th class="plhead" style="width: 400px;">NOMBRE/DESCRIPCIÓN</th>
                </tr>
            </thead>

            <tbody>
                <?php 
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                    ?>
                <tr>
                    <td class="plinner left"><?= ++$i?></td>
                    <td class="plinner"><?= $row['nombre'] ?></td>
                    <td class="plinner"><?= $row['decimales'] ?></td>
                    <td class="plinner"><?= nl2br($row['descripcion']) ?></td>
                </tr>
                <?php 

                } ?>
            </tbody>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>