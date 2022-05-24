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
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$signal= $_GET['signal'];
$month= !empty($_GET['month']) ? $_GET['month'] : (int)$_SESSION['current_month'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$tipo= $_GET['tipo'];

$obj= new Tproceso($clink);

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
$obj_traza->add("IMPRIMIR", "LISTADO DE UNIDADES O PROCESOS", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>LISTADO DE UNIDADES O PROCESOS</title>

         <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                LISTADO DE UNIDADES O PROCESOS <br /><?= $meses_array[(int)$month]?>/<?= $year?>
            </div>
        </div>

        <?php
        $i= 0;
        unset($obj_prs); 
        $obj_prs= new Tproceso($clink);
        
        if ($id_proceso != $_SESSION['local_proceso_id']) 
            $obj->SetIdProceso($id_proceso);
        if (!empty($tipo)) 
            $obj->SetTipo($tipo);

        $result= $obj->listar(null, "t2.id");
        $cant= $obj->GetCantidad();

        if ($cant > 0) {
        ?>
            <div class="page center">
                <table cellpadding="0" cellspacing="0" border="0">
                    <thead>
                        <tr>
                            <th>UNIDAD</th>
                            <th class="plhead left">TIPO</th>
                            <th class="plhead">LUGAR</th>
                            <th class="plhead">RESPONSABLE</th>
                            <th class="plhead">CONEXIÓN</th>
                            <th class="plhead">LAN/WAM</th>
                            <th class="plhead">CÓDIGO</th>
                            <th class="plhead">ARCHIVO</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($row= $clink->fetch_array($result)) { ?>
                        <tr>
                            <td class="plinner left"><?= $row['_nombre'] ?></td>

                            <td class="plinner"><?= $Ttipo_proceso_array[$row['tipo']] ?></td>
                            <td class="plinner"><?=  nl2br($row['lugar']) ?></td>
                            <td class="plinner"><?= $row['responsable'].' ('.$row['cargo'].')' ?></td>
                            <td class="plinner"><?= $Ttipo_conexion_array[$row['conectado']] ?></td>

                            <td class="plinner">
                                <?php
                                if (!is_null($row['email'])) echo "E-CORREO: ".$row['email'];
                                if (!is_null($row['url'])) echo "<BR>URL: ".$row['url'];
                                if (!is_null($row['ip']) && $row['ip'] != '...:') echo "<BR>TCP/IP: ".$row['ip'];
                                ?>
                            </td>
                            <td class="plinner"><?= $row['codigo'] ?></td>
                            <td class="plinner"><?= $row['codigo_archive'] ?></td>
                        </tr>
                    <?php ++$i; } ?>
                   </tbody>
                </table>
        <?php } ?>

    </div>

    <?php require "inc/print_bottom.inc.php";?>
