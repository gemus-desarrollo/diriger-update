<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

include_once "../../../php/config.inc.php";
include_once "../../../php/class/connect.class.php";
include_once "../php/class/organismo.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Torganismo($clink);
}

$id= $obj->GetIdOrganismo(); 
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$url_page= "../form/forganismo.php?signal=$signal&action=$action&menu=organismo&exect=$action";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ORGANISMOS DE LA ADMINISTRACIÓN DEL ESTADO</title>

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>
 
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js"></script>
        
        <script type="text/javascript" src="../../../js/form.js"></script>

        <script language="javascript">
            function validar() {
                if (!Entrada($('#nombre').val())) {
                    $('#nombre').focus(focusin($('#nombre')));
                    alert('Introduzca el nombre del Organismo o Institución a registrar ');
                    return;
                }
                if (!Entrada($('#codigo').val())) {
                    $('#codigo').focus(focusin($('#codigo')));
                    alert('Introduzca el código o las siglas del Organismo o Institución');
                    return;
                } 

                document.forms[0].action= '../php/organismo.interface.php';
                document.forms[0].submit();
            }
        </script>
         <script type="text/javascript">
            $(document).ready(function () {
                <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>")<?php } ?>
            });
        </script>       
    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>
        
       <div class="app-body form">
            <div class="container">
                 <div class="card card-primary">
                    <div class="card-header">ORGANISMO DE LA ADMINISTRACIÓN DEL ESTADO</div>
                    
                    <div class="card-body">                 
                        <form class="form-horizontal" action='javascript:validar()'  method="post">	        
                            <input type="hidden" name="exect" value="<?=$action?>" />
                            <input type="hidden" name="id" value="<?=$id?>" />
                            <input type="hidden" name="menu" value="organismo" />        

                            <div class="form-group row">
                               <label class="col-form-label col-1">
                                    Nombre:
                               </label>
                               <div class="col-10">
                                   <input type="text" id="nombre" name="nombre" class="form-control" value="<?=$obj->GetNombre()?>" />                               
                               </div>
                           </div>

                            <div class="form-group row">
                                <label class="col-form-label col-1">
                                    Código:
                                </label>
                                <div class="col-sm-4 col-md-3 col-lg-3">
                                    <input type="text" id="codigo" name="codigo" class="form-control" value="<?=$obj->GetCodigo()?>" />
                                </div>                                  
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox">
                                    <input type="checkbox" id="use_anual_plan" name="use_anual_plan" value="1" />
                                    Utilizar esta Organización como destino en las actividades del Plan Anual de Actividades
                                </label>
                            </div>
                            
                            <div class="form-group row">
                               <label class="col-form-label col-2">
                                    Descripción:
                               </label>
                               <div class="col-10">
                                    <textarea name="descripcion" rows="5" id="descripcion" class="form-control"><?=$obj->GetDescripcion()?></textarea>
                               </div>
                           </div>

                            <!-- buttom -->
                            <div class="btn-block btn-app">
                                <?php if ($action == 'update' || $action == 'add') { ?>
                                    <button class="btn btn-primary" type="submit">Aceptar</button>
                                <?php } ?>
                                <button class="btn btn-warning" type="reset" onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                            </div>
                        </form>                    
                    </div> <!-- panel-body -->                      
                </div> <!-- panel -->
             </div>  <!-- container -->
           
       </div>      
    </body>
</html> 

