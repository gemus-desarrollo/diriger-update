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
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$obj= new Tescenario($clink);

$month= !empty($_GET['month']) ? $_GET['month'] : (int)$_SESSION['current_month'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
$result= $obj->listar();

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
$obj_traza->add("IMPRIMIR", "LISTADO DE ESCENARIOS", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>LISTADO DE ESCENARIOS</title>

         <?php require "inc/print_top.inc.php";?>


        <div id="headerpage" style="width: <?= $widthpage?>cm">
            <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                LISTADO DE ESCENARIOS <br /><?= $meses_array[(int)$month]?>/<?= $year?>
            </div>
        </div>

    <div class="page center">
        <?php
        $i= 0;
        while ($row= $clink->fetch_array($result)) {
        ?>

             <div class="paneltableblock">
                 <table width="100%" border="0">
                     <tr>
                         <td colspan="2" class="plinner none-border">
                             <strong>PERIODO: </strong><?= "{$row['inicio']} - {$row['fin']}"; ?><br />
                         </td>
                     </tr>

                     <tr>
                         <td class="plinner none-border">
                                <strong>MISIÓN:</strong><br />
                                <?= textparse($row['mision']) ?>
                         </td>
                     </tr>
                     <tr>
                         <td class="plinner none-border">
                                <strong>VISIÓN:</strong><br />
                                <?= textparse($row['vision']) ?>
                         </td>
                     </tr>
                     <tr>
                         <td class="plinner none-border">
                                 <strong>DESCRIPCIÓN:</strong><br />
                                 <?= textparse($row['descripcion']) ?>
                         </td>
                     </tr>

                    <?php if (!empty($row['mapa'])) { ?>
                        <tr>
                            <td class="plinner none-border" style="margin-top:30px;"><strong>MAPA ESTRATÉGICO</strong></td>
                        </tr>
                        <tr>
                            <td class="plinner none-border">
                                <img id="img<?= $row['id'] ?>" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&amp;signal=strat&amp;id=<?= $row['id'] ?>"  <?= $obj->GetDim('strat', $row['mapa_param'], 1) ?> border="0" />
                            </td>
                        <tr>
                        <?php } ?>

                        <?php if (!empty($row['proc_mapa'])) { ?>
                        <tr>
                            <td class="plinner none-border"><strong>MAPA DE PROCESOS INTERNOS</strong></td>
                        </tr>
                        <tr>
                            <td class="plinner none-border">
                                <img id="img<?= $row['id'] ?>" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&amp;signal=proc&amp;id=<?= $row['id'] ?>"  <?= $obj->GetDim('proc', $row['proc_param'], 1) ?> border="0" />
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (!empty($row['org_mapa'])) { ?>
                        <tr>
                            <td class="plinner none-border"><strong>ORGANIGRAMA FUNCIONAL</strong></td>
                        </tr>
                        <tr>
                            <td class="plinner none-border">
                                <img id="img<?= $row['id'] ?>" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&amp;signal=org&amp;id=<?= $row['id'] ?>"  <?= $obj->GetDim('org', $row['org_param'], 1) ?> border="0" />
                            </td>
                        </tr>
                    <?php } ?>
                 </table>
             </div>
             <?php ++$i;
         } ?>

    </div>

     <?php require "inc/print_bottom.inc.php";?>
