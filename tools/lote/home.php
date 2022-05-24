<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once("../../php/setup.ini.php");
include_once("../../php/class/config.class.php");
session_start();

$_SESSION['debug']= 'no';

include_once('../../inc.php');
include_once('../../php/config.ini');
include_once('../../php/config.inc.php');
include_once('../../php/class/base.class.php');

$opage= !empty($_GET['opage']) ? $_GET['opage'] : NULL;
$action= !empty($_GET['action']) ? $_GET['action'] : 'resume';
$signal= !empty($_GET['signal']) ? $_GET['signal']: 'home';
$file= !empty($_GET['file']) ? $_GET['file'] : NULL;
$email= !empty($_GET['email']) ? $_GET['email'] : null;
$sendmail= $_GET['sendmail'];

$destino= $_GET['destino'];
$observacion= $_GET['observacion'];
$fecha= $_GET['fecha'];

$lote= $_GET['lote'];
//if(!is_null) $lote= urldecode($lote);
$error= !empty($_GET["error"]) ? $_GET["error"] : null;
$tmp_name = $_GET["tmp_name"];

$exect= $_GET['exect'];

if($action == 'export') {$url= "php/export.interface.php";}
if($action == 'import') {$url= "php/import.interface.php";}

if($action == 'upload' || $action == 'download') {$url= "form/flote.php";}
if($action == 'resume') {$url= "form/resume.php";}

$url.= "?signal=$signal&action=$action&file=$file&email=$email&lote=$lote&fecha=$fecha&observacion=$observacion&exect=$exect&destino=$destino";
$url.= "&tmp_name=$tmp_name&error=$error&sendmail=$sendmail";

if(!is_null($error)) $error= urldecode($error);
//echo $url; exit;
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0014)about:internet -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Diriger v<?=_VERSION_DIRIGER?> &nbsp;&nbsp;MODULO PARA LA TRANSFERENCIA Y ACTUALIZACION DE DATOS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" type="text/css" media="screen" href="../../css/menu.css?version=<?=$_SESSION['update_no']?>"  />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/main.css?version=<?=$_SESSION['update_no']?>"  />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/table.css?version=<?=$_SESSION['update_no']?>">
<link rel="stylesheet" type="text/css" media="screen" href="../../css/form.css?version=<?=$_SESSION['update_no']?>"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/main.css?version=<?=$_SESSION['update_no']?>"  />

<script type="text/javascript" src="../../js/string.js?version=<?=$_SESSION['update_no']?>"></script>
<script type="text/javascript" src="../../js/general.js?version=<?=$_SESSION['update_no']?>"></script>
<script type="text/javascript" src="../../js/menu.js?version=<?=$_SESSION['update_no']?>"></script>

<script language="javascript" type="text/javascript">
function ejecutar(action) {
	var _action= document.getElementById('action').value;
	var signal;
	
	if(_action == 'import' || _action == 'export') {
		alert("Se esta realizando una operación de transferencia de datos. Espere por favor...... Sí detiene el proceso pueden ocurrir perjuicios a la integridad delos datos.");
		return;
	}

	if(action == 'export' || action == 'import') signal='home';
	if(action == 'download' || action == 'upload') signal='form';
	
	parent.location.href= 'home.php?signal='+signal+'&action='+action;
	
	if(action == 'salir') {
		parent.location.href= '<?php echo (isset($opage)) ? _SERVER_DIRIGER.'index.php' : _SERVER_DIRIGER.'tools/lote/index.php'; ?>';
		//parent.close();
	}
}
</script>

</head>


<body>

<div id="container" style="overflow:visible;">

<div id="header"><?php $show_mainmenu= true; include_once('../../html/header.php')?></div>

<div id="baricon" style="height:24px; position:relative;">
	<div id="icon-title">MODULO PARA LA TRANSFERENCIA Y ACTUALIZACION DE DATOS v1.0</div>
    
    <div class="_icon icon-help" onclick="open_help_window('../../help/18_herramientas.htm#18_30.4')">Ayuda</div>
    
    <div class="_icon icon-print d-none d-lg-block" style="right:100px;"  onclick="imprimir()">Imprimir</div>
   
   <div class="_icon" style="background-image:url(img/download_database.png);" onclick="ejecutar('download')">Exportar (1)</div>)
   
   <div class="_icon" style="background-image:url(img/database_add.png);" onclick="ejecutar('upload')">Importar (1)</div>

    <?php
    $text_btn_transmit= "Exportar (todos)";
    if((strlen($_SESSION['email_server']) && $_SESSION['email_server'] != '127.0.0.1')
    && (strlen($_SESSION['email_app']) && $_SESSION['email_app'] != 'diriger@127.0.0.1')) $text_btn_transmit= "Transmitir (todos)";
    ?>

    <div class="_icon" style="background-image:url(img/transmit_go.png); width: 100px;" onclick="ejecutar('export')"><?=$text_btn_transmit?></div>

    <?php
    if((strlen($_SESSION['email_server']) && $_SESSION['email_server'] != '127.0.0.1')
        && (strlen($_SESSION['email_app']) && $_SESSION['email_app'] != 'diriger@127.0.0.1')) {
    ?>
        <div class="_icon" style="background-image:url(img/transmit_add.png); width: 100px;" onclick="ejecutar('import')">Recibir (todos)</div>
   <?php } ?>
</div>	 

<div style="position:relative; height:100%">
	<iframe id="win-applay" frameborder="0" scrolling="yes" src="<?=$url?>" style="width:100%; height:500px; overflow:auto;"></iframe>
</div>

</div> <!-- end .container -->


<div id="sideborder"></div>


<input type="hidden" id="action" name="action" value="<?=$action?>" />
<?php if(!is_null($error)) {?><script language="javascript">alert('<?=$error?>')</script><?php } ?>

<input type="hidden" name="nivel" id="nivel" value="<?=$_SESSION['nivel']?>" />

</body>
</html>
