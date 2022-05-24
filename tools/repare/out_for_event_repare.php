<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/14
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/base.class.php";
require_once "../../php/class/code.class.php";

require_once "../../php/class/indicador.class.php";
require_once "../../php/class/registro.class.php";
require_once "../../php/class/tipo_evento.class.php";

set_time_limit(0);
$cronos= date('Y-m-d H:i:s');
$location= $_SESSION['location'];
$year= 2019;

$array_usuarios= array();
$array_eventos= array();

function select_users() {
    global $clink;
    global $location;
    global $array_usuarios;
    
    if (empty($_SESSION['superior_proceso_id']))
        die ("<p><strong>NO ESTA DEFINIDO EL PROCESO SUPERIOR</strong></p>");
    
    $sql= "select * from tusuarios where global_user or id_proceso_jefe = {$_SESSION['local_proceso_id']} ";
    $sql.= "union ";
    $sql.= "select distinct tusuarios.* from tusuarios, tusuario_procesos where tusuarios.id = tusuario_procesos.id_usuario ";
    $sql.= "and (tusuario_procesos.id_proceso = {$_SESSION['superior_proceso_id']} and tusuarios.id_proceso != {$_SESSION['superior_proceso_id']}) ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        if (empty($row['noIdentidad'])) 
            continue;
        $array_usuarios[$row['id']]= array($row['id'], $row['noIdentidad']); 
    }
}

function select_eventos() {
    global $clink;
    global $location;
    global $year;
    global $array_usuarios;
    global $array_eventos;
    
    $i= 0;
    foreach ($array_usuarios as $user) {
        $sql= "select distinct id_evento_code, user_check, situs from treg_evento_$year where id_usuario = {$user[0]} ";
        $sql.= "and situs = '$location' ";
        $result= $clink->query($sql);
        
        while ($row= $clink->fetch_array($result)) {
            if (empty($row['id_evento_code'])) 
                continue;
            ++$i;
            $array_eventos[]= array($row['id_evento_code'], $row['user_check'] ? 1 : 0, $user[0], $row['situs'], $year);
            
        }
    }
    return $i;
}

function create_file() {
    global $year;
    global $array_usuarios;
    global $array_eventos;
    
    @unlink("salida_eventos_{$_SESSION['local_proceso_nombre']}.data");
    $fp= fopen("salida_eventos_{$_SESSION['local_proceso_nombre']}.data", 'a+');
    
    $i= 0;
    $line= null;
    foreach ($array_eventos as $event) {
        ++$i;
        $noIdentidad= $array_usuarios[$event[2]][1];
        $line= "$i //--> {$event[0]} //--> {$event[1]} //--> {$noIdentidad} //--> {$event[3]} //--> $year \n";
        echo "<br/>$line</br>";
        fwrite($fp, $line);
    }
    fclose($fp);
}
?>

<html>
    <head>
        <title>GENERAR DATOS DE EMPRESA: <?=$_SESSION['local_proceso_nombre']?> PARA GRUPO</title>
    </head>    
    <body>
        <p><?=$cronos?></p>
        <p>Espere por favor tomará varios minutos ... </p>
        <p>la información estará en el fichero <?="salida_eventos_{$_SESSION['local_proceso_nombre']}.data"?></p>
    </body>
</html>

<?php
select_users();
$nums= select_eventos();

if (empty($nums)) {
    die("<p><h1>No existen registros. En la tabla teventos no existen registros a importar</h1></p>");
}

create_file();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize("salida_eventos_{$_SESSION['local_proceso_nombre']}.data"));
header("Content-Disposition: attachment; filename=salida_eventos_{$_SESSION['local_proceso_nombre']}.data");
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');  

ob_clean();
flush();    
readfile("salida_eventos_{$_SESSION['local_proceso_nombre']}.data");     
exit;
?>

