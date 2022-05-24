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

$id_proceso= !empty($_POST['id_proceso']) ? $_POST['id_proceso'] : null;
$location_prs= null;

if (!empty($id_proceso)) {
    $sql= "select * from tprocesos where id = $id_proceso";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $nombre_prs= $row['nombre'];
    $location_prs= $row['codigo'];
}


function set_init() {
    global $clink;
    global $location;
    global $year;
    global $array_usuarios;
    global $id_proceso;
    
    global $location_prs;
    if (is_null($location_prs)) 
        die();
    
    $sql= "select * from tusuarios where noIdentidad is not null and id_proceso != {$_SESSION['local_proceso_id']} ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        if (empty($row['noIdentidad'])) 
            continue;
        $array_usuarios[(int)$row['noIdentidad']]= $row['id']; 
    }
    
    $sql= "update treg_evento_$year, teventos set hide_synchro = 1 where treg_evento_$year.id_evento = teventos.id ";
    $sql.= "and teventos.cronos_syn is not null and teventos.id_proceso = $id_proceso";  
    $clink->query($sql); 
    
}

function read_file() {
    global $year;
    global $array_usuarios;
    global $array_eventos;    
    global $nombre_prs;

    $fp= fopen("salida_eventos_$nombre_prs.data", 'r');
    if (!$fp) die ("No ha sido leido el fichero salida_eventos_$nombre_prs.data"); 
    echo "<br/>";
   
    $i= 0;
    while (($line = fgets($fp)) !== false) {
        if (strlen($line) <= 2) 
            break;
        ++$i;
        echo "$i ---> $line <br/>";
        $array= explode("//-->", $line);
        $array_eventos[]= array('id_evento_code'=>trim($array[1]), 'user_check'=>trim($array[2]), 
            'id_usuario'=>$array_usuarios[(int)$array[3]], 'situs'=>$array[4], 'year'=>(int)$array[5]);
    }
    
    fclose(fp);
}    

function update_eventos() {
    global $clink;
    global $location;
    global $year;
    global $array_usuarios;
    global $array_eventos;
    
    $i= 0;
    foreach ($array_eventos as $event) {
        ++$i;
        $user_check= setNULL_empty($event['user_check']);
        $sql= "update treg_evento_{$event['year']} set user_check = $user_check, hide_synchro= null ";
        $sql.= "where id_evento_code = '{$event['id_evento_code']}' and id_usuario = {$event['id_usuario']} ";
        $result= $clink->query($sql); 
    }
}
?>

<html>
    <head>
        <title>REPARAR GRUPO. LEER DATOS</title>
        <style>
            option {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <form method="post" action="input_for_event_repare.php">
            <label>
                <h2>Empresa a reparar:</h2>
            </label>
            
            <div style="margin: 10px;">
                <select id="id_proceso" name="id_proceso" style="width: 600px; padding: 6px;">
                    <option value="0">seleccione ...</option>
                    <?php
                    $sql= "select * from tprocesos where $conectado "._LAN. " and tipo = "._TIPO_EMPRESA;
                    $result= $clink->query($sql);
                    while ($row= $clink->fetch_array($result)) {
                    ?>    
                        <option value="<?=$row['id']?>" <?=$row['id'] == $id_proceso ? "selected" : ""?> ><?=$row['nombre']?></option>
                    <?php } ?>
                </select>
            </div>

            <?php if (!empty($id_proceso)) { ?>
                <p><?=$cronos?></p>
                <p>Espere por favor tomará varios minutos ... </p>
                <p>Cargando información del fichero <?="salida_eventos_{$nombre_prs}.data"?></p>
            <?php } else { ?>
                <p>
                    <button type="submit" style="font-size: 2em;">Ejecutar</button>
                </p>
            <?php } ?>  
        </form>        
    </body>
</html>

<?php
if (!empty($id_proceso)) {
    set_init();
    read_file(); 
    update_eventos(); 
}
exit;
?>

