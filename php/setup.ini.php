<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 4/02/15
 * Time: 18:52
 */


$csfr_token= !is_null($csfr_token) ? $csfr_token : $_GET['csfr_token'];
$csfr_token_parent= !empty($csfr_token_parent) ? $csfr_token_parent : false;

if ($csfr_token != '123abc') {
    $referer= !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $HTTP_REFERER;
    if (parse_url($referer, PHP_URL_HOST) != $_SERVER['HTTP_HOST']) 
        die("Anti-CSRF. Acceso desde una dirección no confiable. Posible intento de Hackeo. Acceso denegado.");
}
if ((!$csfr_token_parent || (!empty($csfr_token_parent) && $csfr_token_parent != "456def")) && $_SESSION['csfr_token'] != "123abc") {
    die("Usuario no autenticado, acceso no autorizado. Acceso denegado.");
}


// DEFINICIONES GENERALES
if (!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, "/");
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/");
}

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 1000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50300) {
    $error= "Su versión de servidor PHP es inferior a la que requiere Diriger. Por favor, ";
    $error.= "debe actualizar su servidor PHP a la versión 5.3 o superior. De lo contrario no ";
    $error.= "garantizamos el correcto funcionamiento del sistema, ni la seguridad de su información.";
?>
    <script language="javascript" charset="utf-8">alert(<?=$error?>)</script>
<?php
    die($error);
}

$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
$protocol = "HTTP";

if (isset($_SERVER['SERVER_PROTOCOL'])) {
    $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    define("_HTTP", $protocol);
} else {
    define("_HTTP", "HTTP");
}
$port= $_SERVER['SERVER_PORT'];
$port= $port != 80 ? ":$port" : null;
$_SERVER['SERVER_NAME']= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'].$port : "localhost".$port;

$os_system= strtoupper(substr(PHP_OS, 0, 3));
$sp= ($os_system === 'WIN') ? '\\' : '/';

if (!defined('SCRIPT_FILENAME')) {
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        define('SCRIPT_FILENAME', $_SERVER['SCRIPT_FILENAME']);
    } else {
        define('SCRIPT_FILENAME', getcwd()."/");
    }    
}

if (!defined('_SERVER_DIRIGER')) {
    $pos= false;
    $pos= stripos($_SERVER['PHP_SELF'], '/tools');
    if ($pos === false) 
        $pos= stripos($_SERVER['PHP_SELF'], '/html');
    if ($pos === false) 
        $pos= stripos($_SERVER['PHP_SELF'], '/form');
    if ($pos === false) 
        $pos= stripos($_SERVER['PHP_SELF'], '/php');
    if ($pos === false) 
        $pos= stripos($_SERVER['PHP_SELF'], '/print');
    if ($pos === false) 
        $pos= stripos($_SERVER['PHP_SELF'], '/test');          
    if ($pos === false) 
        $pos= strrpos($_SERVER['PHP_SELF'], '/');
    $path= substr($_SERVER['PHP_SELF'], 0, $pos);
	
    $root="{$protocol}://{$_SERVER['SERVER_NAME']}{$path}/";
    $root= str_replace("install/","",$root);
    define('_SERVER_DIRIGER', $root);
	
    $pos= false;
    $pos= stripos(SCRIPT_FILENAME, '/tools');
    if ($pos === false) 
        $pos= stripos(SCRIPT_FILENAME, '/html');
    if ($pos === false) 
        $pos= stripos(SCRIPT_FILENAME, '/form');
    if ($pos === false) 
        $pos= stripos(SCRIPT_FILENAME, '/php');
    if ($pos === false) 
        $pos= stripos(SCRIPT_FILENAME, '/print');
    if ($pos === false) 
        $pos= stripos(SCRIPT_FILENAME, '/test');        
    if ($pos === false) 
        $pos= strrpos(SCRIPT_FILENAME, '/');
    $root= substr(SCRIPT_FILENAME, 0, $pos).$sp;

    if ($os_system === 'WIN') 
        $root= str_replace('/', '\\', $root);
    $root= str_replace("install/","",$root);
    define('_ROOT_DIRIGER_DIR', $root);

    define("_SLASH_DIRIGER_DIR", $sp);

    $url= $os_system === 'WIN' ? "C:\\diriger\\php\\config.ini" : "/var/diriger/php/config.ini";
    if (is_readable($url)) {
        $root= $os_system === 'WIN' ? "C:\\diriger\\" : "/var/diriger/";

        define('_DIRIGER_DIR', $root);
        define('_DATA_DIRIGER_DIR', $root.'data'._SLASH_DIRIGER_DIR);
        define('_LOG_DIRIGER_DIR', $root.'log'._SLASH_DIRIGER_DIR); 
        define('_PHP_DIRIGER_DIR', $root.'php'._SLASH_DIRIGER_DIR);
        define('_UPLOAD_DIRIGER_DIR', _DATA_DIRIGER_DIR."upload"._SLASH_DIRIGER_DIR);
        define('_UPDATE_DIRIGER_DIR', $root.'update'._SLASH_DIRIGER_DIR);
        
    } else {
        $url= _ROOT_DIRIGER_DIR."php{$sp}config.ini";
      
        if (!is_readable($url)) {
            $error= "Hay error en la estructura de ficheros. No es posible leer los ficheros de configuración de Diriger";
        ?>
            <script language="javascript" charset="utf-8">alert(<?=$error?>)</script>
        <?php
            die($error);            
        }
        
        define('_DIRIGER_DIR', _ROOT_DIRIGER_DIR);
        define('_DATA_DIRIGER_DIR', _ROOT_DIRIGER_DIR."data"._SLASH_DIRIGER_DIR);
        define('_LOG_DIRIGER_DIR', _ROOT_DIRIGER_DIR."log"._SLASH_DIRIGER_DIR);
        define('_PHP_DIRIGER_DIR', _ROOT_DIRIGER_DIR."php"._SLASH_DIRIGER_DIR); 
        define('_UPLOAD_DIRIGER_DIR', _DATA_DIRIGER_DIR);
        define('_UPDATE_DIRIGER_DIR', _DATA_DIRIGER_DIR);
    }
}

?>
