<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../php/class/organismo.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

// para pruebas
$acc= _ACCESO_ALTA;
// $acc= $_SESSION['acc_archive'];
if(empty($acc) || ($acc < _ACCESO_MEDIA && $id_proceso == $_SESSION['id_entity']))
    die("Acceso denegado a los archivos.");

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
}
else {
    $obj= new Torganismo($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;	
$result= $obj->listar();	

$url_page= "../form/lorganismo.php?signal=$signal&action=$action&menu=organismo&id_proceso=$id_proceso";

set_page($url_page);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        
        <title>LISTADO DE ORGANISMOS DEL ESTADO</title>

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        
        <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>         
        
        <link rel="stylesheet" type="text/css" media="screen" href="../../../css/table.css" />
        
        <link href="../../../libs/alert-panel/alert-panel.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/alert-panel/alert-panel.js"></script>
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js"></script>
        

        <script type="text/javascript" src="../../../js/form.js"></script>

        <script language="javascript">
            function add() {
                self.location.href= 'forganismo.php?action=add&signal=list';
            }
            function _edit(id, action) {
                self.location.href= '../php/organismo.interface.php?action=edit&id='+id;
            }
            function _delete(id) {
                self.location.href= '../php/organismo.interface.php?action=delete&id='+id;
            }
        </script>
        
        <script type="text/javascript" charset="utf-8">
            $(document).ready( function () {
                <?php if (!is_null($error)) { ?>
                    alert("<?= str_replace("\n", " ", $error) ?>");
                <?php } ?>   
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <!-- Docs master nav -->
        <div id="navbar-secondary">
            <nav class="navd-content">
                <div class="navd-container">
                    <div id="dismiss" class="dismiss">
                        <i class="fa fa-arrow-left"></i>
                    </div> 
                
                    <a href="#" class="navd-header">
                        LISTADO DE ORGANISMOS DEL ESTADO
                    </a>

                    <div class="navd-menu" id="navbarSecondary">
                        <ul class="navbar-nav mr-auto">
                            <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO) { ?>   
                            <li>
                                <a href="#" class="" onclick="add()" title="">
                                    <i class="fa fa-plus"></i>
                                    Agregar
                                </a>
                            </li>
                        <?php } ?>   
                    </ul>
                    
                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li>
                                <a href="#" onclick="open_help_window('../help/manual.html#listas')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul> 
                    </div>                        
                </div>       
            </nav>          
        </div>
        
        <form action='javascript:' method=post>
            <input type="hidden" name="exect" id="exect" value='' />	
            <input type="hidden" name="menu" id="menu" value="organismo" /> 
            
            <div class="app-body container-fluid table onebar">
                <table id="table" class="table table-striped"
                       data-toggle="table"
                       data-search="true"
                        data-show-columns="true">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <?php if ($action != 'list') { ?>
                            <th></th>
                            <?php } ?>
                            <th>NOMENCLADOR</th>
                            <th>NOMBRE</th>
                            <th>PLAN ANUAL</th>
                            <th>DESCRIPCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0;
                        while ($row = $clink->fetch_array($result)) { ?>
                            <tr>
                                <td><?=++$i?></td>
                                <?php if ($action != 'list') { ?> 
                                <td>
                                    <a class="btn btn-warning btn-sm" href="javascript:_edit(<?=$row['id']?>,'<?=$action?>');">
                                        <i class="fa fa-edit"></i>Editar
                                    </a>
                                    <a class="btn btn-danger btn-sm" href="javascript:delete(<?=$row['id']?>)">
                                        <i class="fa fa-trash"></i>Eliminar
                                    </a>
                                </td>
                                <?php } ?>
                                <td>
                                    <?=$row['codigo']?>
                                </td>
                                <td>
                                    <?=stripslashes($row['nombre'])?>
                                </td>  
                                <td>
                                    <?php if ($row['use_anual_plan']) { ?>
                                    <i class="fa fa-check-square"></i>
                                    <?php } ?>
                                </td>		
                                <td>
                                    <?=nl2br(stripslashes($row['descripcion']))?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </body>
    </html>
