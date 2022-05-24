 <?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/perspectiva.class.php";
require_once "../php/class/tablero.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/cell.class.php";
require_once "../php/class/cell_list.class.php";

require_once "../php/class/unidad.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$day= $_GET['day'];

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

if (empty($id_tablero)) 
    exit;

$obj= new Tcell_list($clink);

$obj->SetDay($day);
$obj->SetMonth($month);
$obj->SetYear($year);

$obj_ind= new Tindicador($clink);
$obj_um= new Tunidad($clink);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_tab= new Ttablero($clink);
$obj_tab->SetIdTablero($id_tablero);
$obj_tab->Set();
$use_perspectiva= $obj_tab->use_perspectiva;

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE INDICADORES TABLERO: ".$obj_tab->GetNombre(), "Corresponde a periodo año: $year");
?>

<html>
    <head>

        <title>RESUMEN DE INDICADORES</title>

         <?php require "inc/print_top.inc.php";?>	

        <div class="container-fluid center">
            <div class="title-header">
                RESUMEN DE ESTADO DE LOS INDICADORES <br />
                TABLERO: <?=$obj_tab->GetNombre()?><br/>
                <?=$meses_array[(int)$month]?>/<?=$year?>
            </div>
        </div>

        <div class="page center">
	
            <?php
            if ($use_perspectiva) {
                $obj_persp = new Tperspectiva($clink);
                $obj_persp->SetIdProceso(null);
                $obj_persp->SetYear($year);
                $result_persp = $obj_persp->listar(null, true);

                if (isset($obj_indi)) unset($obj_indi);
                $obj_indi = new Tindicador($clink);

                $i = 0;
                $j = 0;
                while ($row_persp = $clink->fetch_array($result_persp)) {
                    $id_perspectiva = $row_persp['_id'];
                    $perspectiva = $row_persp['nombre'];
                    $color = '#' . $row_persp['color'];

                    $obj_tab->SetIdTablero($id_tablero);
                    $obj_tab->SetIdPerspectiva($id_perspectiva);
                    $obj_tab->SetYear($year);
                    $obj_tab->SetIdProceso(null);

                    $result_indi = $obj_tab->listar_indicadores(true, true);
                    $cantidad = $obj_tab->GetCantidad();
                    if ($cantidad == 0) 
                        continue;

                    $nombre_prs = $row_persp['proceso'] . '  ' . $Ttipo_proceso_array[$row_persp['tipo']];
                    ?>

                    <br/><br/>
                    <div class=plhead style="background-color:<?= $color ?>; text-align:left;"><strong>PERSPECTIVA:</strong>
                    <?= $row_persp['nombre'] ?> / <span class="text-persp"> <?= $nombre_prs ?></span>
                    </div>

                    <?php include "inc/_table_resumen.inc.php" ?>

                <?php } ?>

                <?php
                $id_perspectiva = null;
                $obj_tab->SetIdTablero($id_tablero);
                $obj_tab->SetIdPerspectiva(null);
                $obj_tab->SetIdProceso(null);
                $obj_tab->SetYear($year);

                $result_indi = $obj_tab->listar_indicadores(true, false);
                $cantidad = $obj_tab->GetCantidad();

                if ($cantidad > 0) {
                ?>
                    <br /><br />
                    <span class="text-persp" style="font-weight: bold"> <?= $proceso ?></span>                       
                <?php    
                    $cant_indi += $cantidad;
                    include "inc/_table_resumen.inc.php";
                }
            }
            ?>

            <?php
            if (!$use_perspectiva) {
                $obj_tab->SetIdTablero($id_tablero);
                $obj_tab->SetIdPerspectiva(null);
                $obj_tab->SetIdProceso(null);
                $obj_tab->SetYear($year);

                $result_indi = $obj_tab->listar_indicadores(null, null);
                $cantidad = $obj_tab->GetCantidad();

                if ($cantidad > 0) {
                ?>
                    <br /><br />
                    <span class="text-persp" style="font-weight: bold"> <?= $proceso ?></span>                  
                <?php    
                    include "inc/_table_resumen.inc.php";
            }   }
            ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

