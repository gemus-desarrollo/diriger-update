<?php
/**
 * author Geraudis Mustelier
 * copyright 2015
 */

$clink= null;
$not_load_config_class= null;

set_time_limit(0);
session_cache_expire(720);
session_start();

require_once "inc.php";
$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "php/setup.ini.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/config_ldap.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

$execfromshell= !is_null($_GET['execfromshell']) ? $_GET['execfromshell'] : 1;
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;

require_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/connect.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/usuario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/entity.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/ldap.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";

global $error;

if (!$uplink) {
    $text= "No se ha establecido comunicación con el servidor.";
    if (!$execfromshell) 
        die("<div class='alert alert-danger'>$text</div>");
    else 
        die($text);
}
if (empty($config->ldap_login)) {
    $text= "Este sistema no está configurado para sincronizar con el Directorio activo.";
    if (!$execfromshell) 
        die("<div class='alert alert-danger'>$text</div>");
    else 
        die($text);
}

$obj_prs= new Tproceso($uplink);
$obj_prs->SetYear($year);
$array_procesos= $obj_prs->listar(false);

$obj_user= new Tusuario($uplink);
$obj_user->SetIdEntity(null);
$result_user= $obj_user->listar(null, null, null, true);

$array_db_usuarios= array();
while ($row= $uplink->fetch_array($result_user)) {
    $prs= $array_procesos[$row['id_proceso']];
    $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
    $array= array('id'=>$row['id'], 'usuario'=>$row['usuario'], 'nombre'=>$row['nombre'], 'email'=>$row['email'], 
                'uid'=>$row['user_ldap'], 'eliminado'=>$row['eliminado'], 'id_entity'=>$id_entity, 'flag'=>0);
    $array_db_usuarios[$row['usuario']]= $array;
}
?>

<?php if (!$execfromshell) { ?>
<script type="text/javascript" charset="utf-8"
    src="<?=_SERVER_DIRIGER?>js/general.js?version="></script>

<script language="javascript">
_SERVER_DIRIGER = '<?= addslashes(_SERVER_DIRIGER)?>';
</script>

<div id="wait-alert">
    <div class="form-group row">
        <label>
            Esta operación puede tardar varios minutos, por favor espere.....
        </label>
    </div>

    <div class="form-group row">
        <div id="progressbar-0" class="progress-block col-12">
            <div id="progressbar-0-alert" class="alert alert-success">
                Comenzando
            </div>
            <div id="progressbar-0-" class="progress progress-striped active">
                <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div id="progressbar-1" class="progress-block col-12">
            <div id="progressbar-1-alert" class="alert alert-success">
                Comenzando
            </div>
            <div id="progressbar-1-" class="progress progress-striped active">
                <div id="progressbar-1-bar" class="progress-bar bg-success" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="div-log">

</div>
<?php } ?>


<?php 
if (!$execfromshell) 
    $_SESSION['in_javascript_block']= false;

global $config;
global $clink;

global $ip;
global $domain;
global $port;
global $cn;
global $admin;
global $passwd;
global $id_proceso;
global $tls;

global $situs_prs;

$config= new _Tconfig_ldap($uplink);
$config->get_servers();

$nserver= 0;
$nconnected= 0;
foreach ($config->array_ldap_servers as $server) {
    $ip= $server['servers'];
    $domain= $server['domain'];
    $port= $server['port'];
    $cn= $server['cn'];
    $admin= $server['admin'];
    $passwd= $server['passwd'];
    $id_proceso= $server['proceso'];
    $tls= $server['tls'];
    $ssl= $server['ssl'];
    $utf8= $server['utf8'];

    $obj_prs->Set($id_proceso);
    $id_proceso_code= $obj_prs->get_id_proceso_code();
    $nombre_prs= $obj_prs->GetNombre();
    $prs= $array_procesos[$id_proceso];
    $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $id_proceso;
    $id_entity_code= !empty($prs['id_entity']) ? $prs['id_entity_code'] : $id_proceso_code;
    
    ++$nserver;
    if (!$execfromshell) {
        $perc= (float)$i/$config->cant_ldap_servers;
        bar_progressCSS(1, $nombre_prs, $perc);
    } 
    
    if ($server['use_ldap_not_login'])
        continue;
        
    read_users($id_entity, $id_entity_code);
}

if (!empty($nserver) && $nserver == $nconnected) 
    delete_users();

if (!$execfromshell) 
    $_SESSION['in_javascript_block']= null;
?>

<?php if (!$execfromshell) { ?>
<script language="javascript">
progressbarCSS(0, "Actualización terminada", 100);

<?php if (empty($nconnected)) { ?>
$('#wait-alert').hide();
<?php } ?>

function refresh_ldap() {
    CloseWindow('div-ajax-panel');
    refreshp();
}
</script>

<div class="submit btn-block" align="center">
    <?php if (empty($nconnected)) { ?>
    <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
    <?php } else { ?>
    <button type="reset" class="btn btn-primary" onclick="refresh_ldap()">Recargar Usuarios</button>
    <?php } ?>
</div>
<?php } ?>


<?php
function user_exist($ldap, $id_entity) {
    global $array_db_usuarios;

    if (array_key_exists(strtolower($ldap['usuario']), $array_db_usuarios)) {
        if ($array_db_usuarios[strtolower($ldap['usuario'])]['id_entity'] == $id_entity) 
            if (empty($user['uid'])) 
                return true;
    }
    reset($array_db_usuarios);
    foreach ($array_db_usuarios as $user) {
        if (empty($user['uid'])) 
            continue;
        if ($user['uid'] == $ldap['uid']) 
            return true;
    }
    
    return false;
}

function error_msg($error) {
    global $execfromshell;

    if (!$execfromshell && (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block'])) {
        ?> <script language="javascript">
        innerHtml = $('#div-log').html();
        innerHtml += "<div class='alert alert-danger'><?=$error?></div>";
        $('#div-log').html(innerHtml);
        </script>
        <?php        
    } else 
        echo "$$error \n";
}

function read_users($id_entity, $id_entity_code) {
    global $config;
    global $uplink;

    global $ip;
    global $domain;
    global $port;
    global $cn;
    global $admin;
    global $passwd;
    global $tls;
    global $ssl;
    global $utf8;  
    
    global $nombre_prs;
    
    global $execfromshell;
    global $nconnected;
    
    global $array_db_usuarios;
    reset($array_db_usuarios);
    
    $obj_prs= new Tproceso($uplink);
    $obj_user= new Tusuario($uplink);
    $obj_ldap= new Tldap();
    
    $obj_entity= new Tentity($uplink);

    // Set up all options.
    $options = [
        'account_suffix' => $domain,
        'base_dn' => $cn,
        'domain_controllers' => $ip,
        'admin_username' => $admin,
        'admin_password' => $passwd,
        'real_primarygroup' => '',
        'use_ssl' => $ssl ? true : false,
        'use_tls' => $tls ? true : false,
        'use_utf8' => $utf8 ? true : false,
        'recursive_groups' => true,
        'ad_port' => $port,
        'sso' => '',
    ];

    $cldap= $obj_ldap->connect($options);

    if (!is_null($cldap)) {
        error_msg($ldap);
        return;
    } else 
        ++$nconnected;

    $array_ldap_usuarios= $obj_ldap->list_users();
    $cant= count($array_ldap_usuarios);
    
    $i= 0;
    foreach ($array_ldap_usuarios as $ldap) {      
        ++$i;
        $user= strtolower($ldap['usuario']);
        if ($user == $_SESSION['email_app'] || "{$user}@{$domain}" == $_SESSION['email_app']) 
            continue;

        $email= null;
        $email= !empty($ldap['mail']) ? $ldap['mail'] : "{$user}@{$domain}";
        $found= user_exist($ldap, $id_entity) ? $array_db_usuarios[$user]['id'] : null;
        if (!$execfromshell) 
            bar_progressCSS(0, "Procesando al usuario ".textparse($ldap['usuario'], true, true)."..... ", (float)$i/$cant);

        if (!$found) {
            $obj_user->Init();
            
            $obj_user->SetIdProceso($id_entity);
            $obj_user->set_id_proceso_code($id_entity_code);
            if ($config->mail_use_ldap) 
                $obj_user->SetMail_address($email);
            $obj_user->SetUsuario($user);
            $obj_user->SetClave(null);
            $obj_user->set_user_ldap($ldap['uid'], false);
            $name_user= $utf8 ? utf8_encode(textparse($ldap['nombre'], true, true)) : textparse($ldap['nombre'], true, true);
            $obj_user->SetNombre($name_user, false);
            $obj_user->SetRole(_REGISTRO);

            if ($obj_entity->block_users()) {
                error_msg("Se ha alcanzado el límite de usuarios permitido por la licencia. $name_user no se ha adicionado");
                continue;
            }

            $error= $obj_user->add();
            $id_usuario= empty($error) ? $obj_user->GetIdUsuario() : null;
            
            if (!empty($id_usuario) && empty($error)) {
                $obj_prs->SetIdProceso($id_entity);
                $obj_prs->set_id_code($id_entity_code);
                $obj_prs->SetIdUsuario($id_usuario);
                $obj_prs->setUsuario();                
            }
            
        } else {
            $array_db_usuarios[$user]['flag']= 1;
            $usuario= $array_db_usuarios[$user]['usuario'];
            $nombre= $array_db_usuarios[$user]['nombre'];

            $equal= false;
            if ($user != strtolower($usuario) && strtolower(textparse($ldap['nombre'], true, true)) == strtolower($nombre)) 
                $equal= true;
            if (($user == strtolower($usuario) || strtolower($ldap['givenname']) == strtolower($usuario))
                    && strtolower($ldap['nombre']) != strtolower($nombre)) 
                $equal= true;
            if ($user == strtolower($usuario) || strtolower($ldap['givenname']) == strtolower($usuario)) 
                $equal= true;
            if (!empty($ldap['email']) && $ldap['email'] != $array_db_usuarios[$user]['email']) 
                $equal= true;
                
            if ($equal && (empty($array_db_usuarios[$user]['eliminado']) || empty($array_db_usuarios[$user]['uid']))) {
                $obj_user->Init();
                
                $obj_user->Set($array_db_usuarios[$user]['id']);
                $obj_user->SetIdUsuario($array_db_usuarios[$user]['id']);
                $obj_user->SetUsuario($user);

                $uid= $array_db_usuarios[$user]['uid'] ? $array_db_usuarios[$user]['uid'] : $ldap['uid'];
                $update_uid= $array_db_usuarios[$user]['uid'] == $ldap['uid'] ? false : true;
                $obj_user->set_user_ldap($uid, $update_uid);

                $obj_user->SetIdProceso(null);
                $obj_user->set_id_proceso_code(null); 
                $obj_user->SetFirma(null, null);
                
                $name_user= $utf8 ? utf8_encode(textparse($ldap['nombre'], true, true)) : textparse($ldap['nombre'], true, true);
                $obj_user->SetNombre($name_user, false);

                if ($config->mail_use_ldap && empty($array_db_usuarios[$user]['email'])) 
                    $obj_user->SetMail_address($email);
                $obj_user->update();
            }
        }

        if (!$execfromshell) {
            $perc= (float)$i/$cant;
            bar_progressCSS(1, textparse($ldap['nombre'], true, true), $perc);
        } 
    }  
    
    $obj_ldap->close();
    
    if (!$execfromshell) {
        bar_progressCSS(1, "Actualización terminada", 1);
    }      
} 

function delete_users() {
    global $config;
    global $uplink;
    global $array_db_usuarios;
    
    reset($array_db_usuarios);
    $obj_user= new Tusuario($uplink);
    
    foreach ($array_db_usuarios as $user) {
        if ($user['flag'] == 1) 
            continue;
        if ($user['flag'] == 0 && (!empty($user['uid']) && empty($user['eliminado']))) {
            $obj_user->SetIdUsuario($user['id']);
            $obj_user->set_eliminado();
        }
    }
}
?>