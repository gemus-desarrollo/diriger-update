<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/proceso.class.php";
require_once "../../php/class/escenario.class.php";

require_once "../php/class/traza.class.php";

$id_proceso= $_GET['id_proceso'];
$id_escenario= $_GET['id_escenario'];
$id_tablero= $_GET['id_tablero'];

$month= !empty($_GET['month']) ? $_GET['month'] : (int)$_SESSION['current_month'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$obj_prs= new Tproceso($clink);

$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
$obj_prs->SetTipo($_SESSION['local_proceso_tipo']);
$obj_prs->SetIdResponsable(null);

$result_prs= $obj_prs->listar_in_order('eq_desc');

switch($id_tablero) {
    case 1:	
        $signal= 'strat'; 
        $title= "MAPA ESTRATÉGICO"; 
        break;
    case 2:	
        $signal= 'proc'; 
        $title= "MAPA DE PROCESOS"; 
        break;
    case 3:	
        $signal= 'org'; 
        $title= "ORGANIGRAMA FUNCIONAL";
         break;
}

$obj= new Tescenario($clink);
$obj->Set($id_escenario);

switch($id_tablero) {
    case 1: 
        $observacion= $obj->GetDescripcion(); 
        break;
    case 2: 
        $observacion= $obj->get_observacion('proc'); 
        break;
    case 3: 
        $observacion= $obj->get_observacion('org'); 
        break;
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ESCENARIO", "Corresponde a periodo año: $year");
?>

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ESCENARIO</title>

    <?php include "../inc/print.ini.php";?>
</head>

<div id="headerpage" style="width: <?php echo $widthpage?>cm">
    <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
        <?php echo $title ?> <br /><?php echo $meses_array[(int)$month]?>/<?php echo $year?>
    </div>
</div>

</head>

<style>
.block {
    clear: both;

    background: white;
    margin: 4px 10px;
    padding: 5px;
    color: black;
}
</style>

<center>
    <div class="block">
        <p style="font-weight: bold; text-align:left"><?=$proceso?></p>
        <p style="font-weight: bold; text-align:justify;">Observación:</p>
        <p style="text-align:left"><?php echo nl2br(stripslashes($observacion)) ?></p>
    </div>

    <img id="img"
        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&amp;signal=<?=$signal?>&amp;id=<?php echo $id_escenario ?>"
        border="0" />
</center>

</body>

</html>