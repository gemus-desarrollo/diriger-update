<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2020
 */

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";

require_once "../../libs/phpseclib/phpcrypt.class.php";
require_once "../../php/class/base.class.php";
require_once "../common/file.class.php";

$execute= $_GET['execute'];
$origen= !empty($_POST['origen']) ? $_POST['origen'] : null;
$destino= !empty($_POST['destino']) ? $_POST['destino'] : null;
$compressed= !empty($_POST['compressed']) ? $_POST['compressed'] : null;

$_SESSION['output_signal']= 'shell';

$obj_file= new Tfile($clink);
$dir= _DATA_DIRIGER_DIR."temp/";

if ($execute) {
    $obj_file->origen_code= strtoupper($origen);
    $obj_file->destino_code= strtoupper($destino);
    
    $obj_file->upload($dir);
    $obj_file->url= $dir. $obj_file->filename;
    
    if ($execute == 'encrypt') {
        if ($compressed)
            $obj_file->compress();
        $obj_file->encrypt_file();
    }  
    
    if ($execute == 'descrypt') {
        $obj_file->decrypt_file($dir);
        if ($compressed)
            $obj_file->uncompress($dir);
        
        @unlink($dir. $obj_file->filename.".gz");
        @unlink($dir. $obj_file->filename.".gz.mcrypt");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ENCRIPTAR / DESENCRIPTAR LOTE</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>
        
        <script language='javascript' type="text/javascript" charset="utf-8">
            function execute(flag) {
                var form= document.forms[0];                    
                form.action= 'phpCrypt.php?execute='+flag;
                form.submit();                    
            }
        </script> 
        
        <script language='javascript' type="text/javascript" charset="utf-8">
            $(document).ready(function() {
                <?php if ($execute == 'encrypt') { ?>
                    alert("Fichero encriptado");
                <?php } ?>
                <?php if ($execute == 'descrypt') { ?>
                    alert("Fichero desencriptado");
                <?php } ?>                
            });
        </script>

    <body>
        <div class="container" style="margin: 30px;"> 
            <div class="card card-primary">
                <div class="card-header">ENCRIPTAR / DESENCRIPTAR LOTE</div>
                
                <div class="card-body">                
                    <form class="form-horizontal" name="fevento" id="fevento" action="javascript:#" method="POST" enctype="multipart/form-data">
                        <div class="form-group row">
                           <label class="col-form-label col-1">
                                Origen:
                           </label>   
                            <div class=" col-3">
                                <input type="text" name="origen" id="origen" class="form-control" value="<?=$origen?>" />
                            </div>                            
                           <label class="col-form-label col-1">
                                Destino:
                           </label>
                            <div class=" col-3">
                                <input type="text" name="destino" id="destino" class="form-control" value="<?=$destino?>" />
                            </div>                            
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12 col-lg-12">
                                <div class="checkbox" style="margin-left: 20px;">
                                    <input type="checkbox" id="compressed" name="compressed" <?php if ($compressed) echo "checked='yes'"?> value="1" />
                                    El fichero esta comprimido en formato .gz
                                </div>   
                            </div>    
                        </div>   
                        
                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Fichero(lote):
                            </label>
                            <div class="col-10">
                                <input type="file" id="lote" name="lote" class="btn btn-info" value="Fichero Lote" />
                            </div>
                        </div>                        
                        <div id="_submit" class="btn-block btn-app">
                            <button class="btn btn-primary" type="button" onclick="execute('encrypt')">Encriptar</button>
                            <button class="btn btn-warning" type="button" onclick="execute('descrypt')">Desemcriptar</button>
                        </div>                
                    </form>
                    
                    <?php if ($execute && !empty($obj_file->url)) { ?>
                    <div class="alert alert-success">
                        <a href="<?="file:///".$dir.$obj_file->filename?>"><?=$obj_file->filename?></a>
                    </div>
                    <?php } ?>
                    
                </div> <!-- panel-body -->                 
            </div> <!-- panel --> 
        </div>   <!-- container -->        