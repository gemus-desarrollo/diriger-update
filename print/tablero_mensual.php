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
require_once "../php/class/usuario.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/tablero.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/cell.class.php";
require_once "../php/class/cell_list.class.php";

require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$id_tablero= $_GET['id_tablero'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$obj= new Tcell_list($clink);

$obj->SetDay($day);
$obj->SetMonth($month);
$obj->SetYear($year);

$obj_ind= new Tindicador($clink);
$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tprocesos(false);

$obj_tablero= new Ttablero($clink);
$obj_tablero->SetIdTablero($id_tablero);
$obj_tablero->Set();
$use_perspectiva= $obj_tablero->use_perspectiva;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN MENSUAL DE INDICADORES TABLERO ".$obj_tablero->GetNombre(), "Corresponde a periodo mes/año: $mes/$year");
?>

<?php include "inc/_tablero_indicador_mensual.inc.php"; ?>

<html>
    <head>
        <title>RESUMEN MENSUAL DE INDICADORES</title>
        
         <?php require "inc/print_top.inc.php";?>	

        <div class="container-fluid center">
            <div class="title-header">
                RESUMEN DE ESTADO DE LOS INDICADORES <br />
                TABLERO: <?=$obj_tablero->GetNombre()?><br/>
                <?=$meses_array[(int)$month]?>/<?=$year?>
            </div>
        </div>

        <div class="page center">
            <?php
            if ($use_perspectiva) {
                $obj_perspectiva= new Tperspectiva($clink);
                $obj_perspectiva->SetIdProceso(null);
                $obj_perspectiva->SetYear($year);
                $result_persp= $obj_perspectiva->listar();

                if (isset($obj_indicador)) unset($obj_indicador);
                $obj_indicador= new Tindicador($clink);

                $i= 0; $j= 0;
                while ($row_perspectiva= $clink->fetch_array($result_persp)) {
                    $id_perspectiva= $row_perspectiva['_id'];
                    $perspectiva= $row_perspectiva['nombre'];
                    $color= '#'.$row_perspectiva['color'];

                    $obj_tablero->SetIdTablero($id_tablero);
                    $obj_tablero->SetIdPerspectiva($id_perspectiva);
                    $obj_tablero->SetYear($year);
                    $obj_tablero->SetIdProceso($id_proceso);

                    $result_indi= $obj_tablero->listar_indicadores(true, true);
                    $cantidad= $obj_tablero->GetCantidad();

                    if ($cantidad == 0) 
                        continue;

                    $nombre_prs= $row_perspectiva['proceso'].'  '.$Ttipo_proceso_array[$row_perspectiva['tipo']];

                    write_html($result_indi);
                }

                $id_perspectiva= null;
                $obj_tablero->SetIdTablero($id_tablero);
                $obj_tablero->SetIdPerspectiva(null);
                $obj_tablero->SetIdProceso(null);
                $obj_tablero->SetYear($year);

                $result_indi= $obj_tablero->listar_indicadores(true, false);
                $cantidad= $obj_tablero->GetCantidad();

                if ($cantidad > 0) {
                    $cant_indi+= $cantidad;
                    write_html($result_indi);
                }
            }

            if (!$use_perspectiva) {
                $result_indi= $obj_tablero->listar_indicadores();
                $cantidad= $obj_tablero->GetCantidad();

                if ($cantidad > 0) 
                    write_html($result_indi);
            }
            ?>

    </div>

    <?php require "inc/print_bottom.inc.php";?>
    