<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/usuario.class.php";

require_once "../php/class/organismo.class.php";
require_once "../php/class/persona.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add' && empty($_GET['error'])) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}
if (!empty($_GET['signal'])) $signal = $_GET['signal'];

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj = new Tpersona($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$noIdentidad= !empty($_GET['noIdentidad']) ? $_GET['noIdentidad'] : $obj->GetNoIdentidad();
$id_prov= !empty($_GET['id_prov']) ? $_GET['id_prov'] : $obj->GetProvincia();
$id_mcpo= !empty($_GET['id_mcpo']) ? $_GET['id_mcpo'] : $obj->GetMunicipio();
$organismo= !empty($_GET['organismo']) ? $_GET['organismo'] : $obj->GetOrganismo();
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : $obj->GetLugar();

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$cargo= !empty($_GET['cargo']) ? urldecode($_GET['cargo']) : $obj->GetCargo();
$direccion= !empty($_GET['direccion']) ? urldecode($_GET['direccion']) : $obj->GetDireccion();

$telefono= !empty($_GET['telefono']) ? $_GET['telefono'] : $obj->GetTelefono();
$movil= !empty($_GET['movil']) ? $_GET['movil'] : $obj->GetMovil();
$email= !empty($_GET['email']) ? $_GET['email'] : $obj->GetMail_address();

$url_page = "../form/fperson.php?signal=$signal&action=$action&menu=person&exect=$action&id_proceso=$id_proceso";
$url_page .= "&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>DESTINATARIO O REMITENTE</title>

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>

        <link href="../../../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>        
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js?version="></script>

        <script type="text/javascript" src="../../../js/form.js?version="></script>

        <script type="text/javascript">
            function FAjaxMcpo() {
                var id_prov= $('#provincia').val();
                var id_mcpo= $('#id_mcpo').val();
                
                if (id_prov == 0 || id_prov == null) {
                    $('#municipio').empty();
                    $('#municipio').val(0);
                    $('#id_mcpo').val(0);
                    refreshp();                    
                }

                $.ajax({
                    url: 'ajax/municipio.ajax.php?csfr_token=123abc',
                    data: {id_prov: id_prov, id_mcpo: id_mcpo},
                    type: 'get',
                    dataType: 'html',
                    
                    success: function (response) {
                        $('#ajax-municipio').html(response);
                    },
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- FAjaxMcpo');
                    }                    
                });
            }            
            
            
            function validar() {
                if (!Entrada($('#nombre').val()) || !Entrada($('#cargo').val())) {
                    $('#nombre').focus(focusin($('#nombre')));
                    alert('Introduzca el nombre o el cargo del Destinatario o Remitente');
                    return;
                }

                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();
                document.forms[0].action = '../php/persona.interface.php?menu=person';
                document.forms[0].submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                InitDragDrop();
                
                $('#provincia').change(function() {                   
                    if ($(this).val() == 0) {
                        $('#municipio').empty();
                    }
                    
                    FAjaxMcpo();
                });
                
                <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>")<?php } ?>
            });
        </script>

    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="app-body form">
                <div class="container">
                 <div class="card card-primary">
                     <div class="card-header">REMITENTE O DESTINATARIO</div>
                     <div class="card-body">                    

                         <form class="form-horizontal" action="javascript:validar()" method="POST">    
                             <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
                             <input type="hidden" name="id" value="<?= $id_persona ?>" />
                             <input type="hidden" id="menu" name="menu" value="person" />
                            <input type="hidden" id="id_mcpo" value="<?=$id_mcpo?>" />
                            <input type="hidden" id="id_prov" value="<?=$id_prov?>" />
        
                            <!-- Destino -->
                            <div id="tr-datos" class="row">                 
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2 col-sm-2">No. Identidad:</label>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="noIdentidad" name="noIdentidad" maxlength="11" value="<?=$noIdentidad?>" />  
                                    </div>
                                </div>    
                                <div class="form-group row">    
                                    <label class="col-form-label col-md-2 col-sm-2">Nombre y Apellidos:</label>
                                    <div class="col-md-8 col-sm-8">
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= textparse($nombre)?>" />  
                                    </div>                            
                                </div>    

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2 col-sm-2">Cargo:</label>
                                    <div class="col-md-8 col-sm-8">
                                        <input type="text" class="form-control" id="cargo" name="cargo" value="<?=$cargo?>" />  
                                    </div>
                                </div>

                                <div class="form-group row"> 
                                    <label class="col-form-label col-md-2 col-sm-2">Organismo:</label>
                                    <div class="col-md-6 col-sm-6">
                                        <select class="form-control" id="organismo" name="organismo" >
                                            <option value="0">Seleccione .... </option>
                                            <?php 
                                            $obj_org= new Torganismo($clink);
                                            $result_org= $obj_org->listar();
                                            
                                            while ($row= $clink->fetch_array($result_org)) { ?>
                                                <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_organismo) echo "selected='selected'"?>><?=$row['nombre']?></option>
                                            <?php } ?>
                                        </select>
                                     </div>
                                </div>
                                
                                <div class="form-group row">    
                                    <label class="col-form-label col-md-2 col-sm-2">Lugar:</label>
                                    <div class="col-md-8 col-sm-8">
                                        <input type="text" id="lugar" name="lugar" class="form-control ui-autocomplete-input" autocomplete="no" value="<?= textparse($lugar)?>" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-6 col-md-offset-0 col-sm-offset-0">
                                        <label class="col-form-label col-md-2 col-sm-2 col-md-offset-0 col-sm-offset-0">Provincia:</label>
                                        <div class="col-md-9">
                                            <select class="form-control" id="provincia" name="provincia">
                                                <option value="0"></option>
                                                <?php foreach ($Tarray_provincias as $key => $value) { ?>
                                                    <option value="<?= $key ?>" <?php if ($key == $id_prov) { ?>selected="selected"<?php } ?>><?= utf8_encode($value) ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>     
                                    </div>

                                    <div class="col-md-6 col-sm-6">
                                        <label class="col-form-label col-md-2 col-sm-2">Municipio:</label>
                                        <div id="ajax-municipio" class="ajax-select col-md-10 col-sm-10">
                                            <select class="form-control" id="municipio" name="municipio">
                                            </select>
                                        </div>     
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-6">
                                        <label class="col-form-label col-md-4 col-sm-4">Teléfono:</label>
                                        <div class="col-md-8 col-sm-8">
                                            <input type="tel" class="form-control" id="telefono" name="telefono" value="<?=$telefono?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-6">
                                        <label class="col-form-label col-md-2 col-sm-2">Movil:</label>
                                        <div class="col-md-9 col-sm-9">
                                            <input type="tel" class="form-control" id="movil" name="movil" value="<?=$movil?>" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2 col-sm-2">Correo:</label>
                                    <div class="col-md-10 col-sm-10">
                                        <input type="email" class="form-control" id="email" name="email" value="<?=$email?>" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-1 col-sm-1">Dirección:</label>
                                    <div class="col-md-11 col-sm-11">
                                        <textarea class="form-control" id="direccion" name="direccion" rows="3"><?= textparse($direccion)?></textarea> 
                                    </div>
                                </div>                        
                            </div>

                             <!-- buttom -->
                             <div id="_submit" class="btn-block btn-app">
                                 <?php if ($action == 'update' || $action == 'add') { ?>
                                     <button class="btn btn-primary" type="submit">Aceptar</button>
                                 <?php } ?>
                                 <button class="btn btn-warning" type="reset" onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                                 <button class="btn btn-danger" type="button" onclick="open_help_window('../../../help/02_usuarios.htm#02_4.3')">Ayuda</button>
                             </div>

                             <div id="_submited" style="display:none">
                                 <img src="../../../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                             </div>

                         </form>                    

                     </div> <!-- panel-body -->                      
                 </div> <!-- panel -->
             </div>  <!-- container -->
           
        </div>
        
              
    </body>
</html>     
