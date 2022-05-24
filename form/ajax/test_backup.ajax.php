<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */
 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";

$test= $_GET['test'];
$url_backup= urldecode($_GET['url_backup']);
$user_backup= urldecode($_GET['user_backup']);
$passwd_backup= urldecode($_GET['passwd_backup']);
$smb_version= $_GET['smb_version'];

$fp= fopen(_ROOT_DIRIGER_DIR."readme.txt", 'r');

$content= date('Y-m-d H:i:s').'\n';
while (!feof($fp)) {
  $content.= fread($fp, 8192);
  $content.= '\n';
}
fclose($fp);

if (file_exists("/mnt/diriger.backup/")) {
    $fp= fopen(_DIRIGER_DIR."backup.sh", 'w');
    $dir_backup= _DATA_DIRIGER_DIR."sql/*";
    $line= "#!/etc/sh \n";
    $line.= "mount.cifs -o username={$user_backup},password={$passwd_backup}";
    if (!empty($smb_version)) 
        $line.= ",vers={$smb_version}";
    $line.= " {$url_backup}/ /mnt/diriger.backup/ \n";
    $line.= "cp -r {$dir_backup} /mnt/diriger.backup/";

    fwrite($fp, $line); 
    fclose($fp);
    
    exec("sh /var/diriger/backup.sh");
} else {
    $error= "Al parecer no existe la carpeta /mnt/diriger.backup/ o si existe no es posible escribir en ella";
}    
?>

<?php if ($test) { ?>
    <div id="alert-loading" class="center-block">
        <div class="alert alert-info">
            Verifique que el fichero readme.txt con el siguiente contenido:
            <?= nl2br($content)?>
            será copiado a la carpeta remota
        </div>
    </div>
<?php } ?>

<?php
if ($test) {
    $result= null;
    $fp= fopen(_DIRIGER_DIR."readme.txt", 'w');
    if ($fp) {
        $result= fwrite($fp, $content);
        fclose($fp);
    }
    
    if (!$fp || !$result) 
        $error= "No se ha podido crear el fichero de prueba '"._DIRIGER_DIR."readme.txt'"; 
    
    if (is_null($error)) {
        $cmd= "cp "._DIRIGER_DIR."readme.txt /mnt/diriger.backup/";
        $output= array();
        $return_var= null;    
        $result= exec($cmd, $output, $return_var);
        if ($return_var) {
            $error= "No se ha podido escribir en la carpeta '/mnt/diriger.backup/' ERROR: \n";
            foreach ($output as $er) 
              $error.= " $er \n";
        }   
    }
}    
?>

<script type="text/javascript">
    $("#alert-loading").hide();
</script>

<?php if ($error) { ?>
    <div class="alert alert-danger">
        ERROR: <?=$error?>
    </div>
<?php } else { ?>
    <?php if ($test) { ?>
        <div class="alert alert-success">
            OK: Todo parece bien verifique que en la carpeta remota este el fichero readme.txt actualizado.
        </div>
    <?php } else { ?> 
        <div class="alert alert-warning">
            Deberá ejecutar, como root, desde la consola de comando del sistema operativo, el script backup.sh 
            para que se haga efectiva la configuración.<br/><br/>
            sh /var/diriger/backup.sh
        </div>
<?php } } ?>

  <div id="_submit" class="submit btn-block" align="center">
      <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
  </div>
</center>


