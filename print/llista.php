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

require_once "../php/class/lista.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : 12;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$obj= new Tlista($clink);

$obj->SetDay(null);
$obj->SetMonth($month);
$obj->SetYear($year);
$obj->SetTipo($tipo);
$obj->SetIdProceso(!empty($id_proceso) && $id_proceso > 0 ? $id_proceso : null);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

?>


<html>
    <head>
        <title>LISTADO DE GUIAS DE CONTROL OCHEQUEOS APLICABLES</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                LISTADO DE GUIAS DE CONTROL OCHEQUEOS APLICABLES. A&Ntilde;O <?= $year ?>
            </div>
        </div>

        <div class="page center">
            <table width="100%" cellpadding="0" cellspacing="0" border="1" style="margin: 10px;">
                <thead>
                    <tr>
                        <th class="plhead left">No.</th>
                        <th class="plhead">TÍTULO</th>
                        <th class="plhead">UNIDAD ORGANIZATIVA</th>
                        <th class="plhead">PERIODO</th>
                        <th class="plhead">RESULTADO</th>
                        <th class="plhead">FECHA</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $result= $obj->listar(true);

                $i = 0;
                $array_ids= array();
                while ($row= $clink->fetch_array($result)) {
                    if ($array_ids[$row['_id']])
                        continue;
                    $array_ids[$row['_id']]= $row['_id'];
                ?>
                    <tr>
                        <td class="plinner left">
                            <?= ++$i ?>
                        </td>

                        <td class="plinner">
                            <?= textparse($row['nombre']) ?>
                        </td>

                        <td class="plinner">
                            <?php
                            $obj_prs->Set($row['_id_proceso']);
                            $nombre= $obj_prs->GetNombre();
                            $tipo= $obj_prs->GetTipo();

                            echo $nombre.', '.$Ttipo_proceso_array[$tipo];
                            ?>
                        </td>
                        <td class="plinner">
                            <?="{$row['inicio']} - {$row['fin']}"?>
                        </td>
                        <td class="plinner">

                        </td>

                        <td class="plinner">

                        </td>

                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </center>
    </body>
</html>
