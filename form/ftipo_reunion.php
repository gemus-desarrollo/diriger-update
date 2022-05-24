<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

 
session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/tipo_reunion.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add' && empty($_GET['id_redirect'])) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}
if (!empty($_GET['signal']))
    $signal = $_GET['signal'];

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else
    $obj = new Ttipo_reunion($clink);

$id = $obj->GetIdTipo_reunion();
$redirect = $obj->redirect;
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];
$obj->SetIdProceso($id_proceso);

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$nombre= !empty($_GET['nombre']) ? $_GET['nombre'] : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? $_GET['descripcion'] : $obj->GetDescripcion();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

if ($action == 'add' && empty($numero)) {
    $obj->SetIdProceso($id_proceso);
    $numero= $obj->find_numero();
}

$url_page = "../form/ftipo_reunion.php?signal=$signal&action=$action&menu=tipo_reunion";
$url_page .= "&exect=$action&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ÓRGANO O GRUPO QUE SE REUNE</title>

        <?php require 'inc/_page_init.inc.php'; ?>
        
        <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <script type="text/javascript">
            function validar() {
                if (!Entrada($('#numero').val())) {
                    $('#numero').focus(focusin($('#numero')));
                    alert('Especifique el número que le corresponde al tipo reunion');
                    return;
                }

                if (!Entrada($('#nombre').val())) {
                    $('#nombre').focus(focusin($('#nombre')));
                    alert('Introduzca el nombre del tipo, órgano, comisión o grupo que reunion');
                    return;
                }

                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();
                document.forms[0].action = '../php/tipo.interface.php?menu=tipo_reunion';
                document.forms[0].submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                new BootstrapSpinnerButton('spinner-numero',0,5000);

                <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>")<?php } ?>
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="app-body form">
                <div class="container">
                 <div class="card card-primary">
                     <div class="card-header">ÓRGANO O GRUPO QUE SE REUNE</div>
                     <div class="card-body">

                         <form class="form-horizontal" action="javascript:validar()" method="POST">
                             <input type="hidden" name="exect" value="<?= $action ?>" />
                             <input type="hidden" name="id" value="<?= $id ?>" />
                             <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                             <input type="hidden" id="menu" name="menu" value="tipo_reunion" />

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Número:
                                </label>

                                <div class="col-md-10 input-group">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control" value="<?= $numero ?>" >
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa">
                                                    <span class="fa fa-caret-up"></span></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa">
                                                    <span class="fa fa-caret-down"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Nombre:
                                </label>
                                <div class="col-md-10">
                                    <input id="nombre" name="nombre" class="form-control" value="<?=$obj->GetNombre()?>" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Descripción:
                                </label>
                                <div class="col-md-10">
                                    <textarea name="descripcion" rows="8" id="descripcion" class="form-control"><?=$obj->GetDescripcion()?></textarea>
                                </div>
                            </div>


                             <!-- buttom -->
                             <div id="_submit" class="btn-block btn-app">
                                 <?php if ($action == 'update' || $action == 'add') { ?>
                                     <button class="btn btn-primary" type="submit">Aceptar</button>
                                 <?php } ?>
                                 <button class="btn btn-warning" type="reset" onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                                 <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.htm')">Ayuda</button>
                             </div>

                             <div id="_submited" style="display:none">
                                 <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                             </div>

                         </form>

                     </div> <!-- panel-body -->
                 </div> <!-- panel -->
             </div>  <!-- container -->

        </div>


    </body>
</html>
