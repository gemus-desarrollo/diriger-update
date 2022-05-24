<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */

 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/usuario.class.php";

require_once "../../php/class/badger.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null;
$nivel= !empty($_GET['nivel']) ? $_GET['nivel'] : 0;
$name= !empty($_GET['name']) ? $_GET['name'] : "responsable";
$plus_name= !empty($_GET['plus_name']) ? $_GET['plus_name'] : "";
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$user_date_ref= !is_null($_GET['user_date_ref']) ? urldecode($_GET['user_date_ref']) : null;
if (!empty($user_date_ref))
    $user_date_ref= date2odbc($user_date_ref);

/**
 * configuracion de usuarios y procesos segun las prioridades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
if ($signal != "proyecto")
    $badger->set_planwork();
else
    $badger->set_planproject();

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$badger->freeassign != _TO_ALL_ENTITIES ? $obj_prs->SetIdEntity($_SESSION['id_entity']) : $obj_prs->SetIdEntity(null);
$obj_prs->get_procesos_up_cascade();

$array_procesos_up= array();
foreach ($obj_prs->array_cascade_up as $key => $array) {
    $array_procesos_up[$array['id']]= $array;
}

// $badger->set_user_date_ref($fecha_origen);
$obj_user= new Tusuario($clink);
$_SESSION['nivel'] == _GLOBALUSUARIO ? $obj_user->set_use_copy_tusuarios(false) : $obj_user->set_use_copy_tusuarios(true);

$obj_prs= new Tproceso($clink);
$_SESSION['nivel'] == _GLOBALUSUARIO ? $obj_prs->set_use_copy_tusuarios(false) : $obj_prs->set_use_copy_tusuarios(true);

$id_proceso= ($badger->acc == 3 || $badger->freeassign >= _TO_ENTITY) ? $_SESSION['id_entity'] : $_SESSION['usuario_proceso_id'];

if($badger->freeassign == _TO_ALL_ENTITIES) {
    $obj_prs->SetIdProceso(null);
    $obj_prs->SetIdEntity(null);
} else {
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->SetIdEntity($_SESSION['id_entity']);
}

$user_show_reject= !is_null($user_date_ref) && empty($user_date_ref) ? true : false;
$user_date_ref= !empty($user_date_ref) ? $user_date_ref : (!is_null($user_date_ref) ? null : date('Y-m-d'));

$obj_prs->set_user_date_ref($user_date_ref, is_null($user_date_ref) ? true : false);

// contruyendo array de usuarios ---------------------------------------------------------->
if ($badger->freeassign <= _TO_ENTITY) {
    $obj_prs->listar_usuarios_proceso($id_proceso, null, null, $user_show_reject, false);
    $array_usuarios= $obj_prs->array_usuarios;
} else {
    $obj_user->SetIdEntity(null);
    $obj_user->SetIdProceso(null);
    $obj_user->listar(false);
    $array_usuarios= $obj_user->array_usuarios;
}

reset($badger->obj_sub->array_usuarios);
foreach ($badger->obj_sub->array_usuarios as $id_user => $array) {
    $array_usuarios[$id_user]= $array; 
}
// -----------------------------------------------------------------------------------------------------

$obj_prs= new Tproceso($clink);

reset($array_procesos_entity);
foreach ($array_procesos_entity as $id_prs => $prs) {
    if (!$prs['if_entity'] && $prs['id_entity'] != $_SESSION['id_entity'])
        continue;
    if ($badger->freeassign != _TO_ALL_ENTITIES && ($prs['if_entity'] && $prs['id_entity'] != $_SESSION['id_entity']))
        continue;
    if ($badger->freeassign == _TO_SUBORDINADOS && !array_search($prs['id'], $badger->array_procesos_down)) 
        continue;

    $array= $obj_prs->getJefe($id_prs);
    $array_usuarios[$array['id']]= $array; 
}

if (count($array_usuarios) == 0) {
    $array= array('id'=>$_SESSION['id_usuario'], 'nombre'=>$_SESSION['usuario'], 'usuario'=>$_SESSION['usuario'],
                'cargo'=>$_SESSION['cargo'], 'nivel'=>$_SESSION['nivel'], 'email'=>$_SESSION['email'], 
                'id_proceso'=>$_SESSION['usuario_proceso_id']);
    $array_usuarios[$_SESSION['id_usuario']]= $array;             
}
?>

<select name="<?=$name?><?=$plus_name?>" id="<?=$name?><?=$plus_name?>" class="form-control">
    <option value <?php if (empty($id_responsable)) echo "selected='selected'" ?>>Seleccione ... </option>

    <?php
    $found_user= false;
    foreach ($array_usuarios as $user) {
        if ($user['id'] == _USER_SYSTEM) 
            continue;
        if (!empty($nivel) && $user['nivel'] < $nivel) 
            continue;
        if ($user['nivel'] == _ADMINISTRADOR)
            continue;    
        if (empty($user['nombre'])) 
            continue;
        
    if ($id_proceso == $_SESSION['id_entity'] 
        && ($badger->freeassign < _TO_ALL_ENTITIES && array_key_exists($user['id_proceso'], $array_procesos_up))) 
        continue;

    if ($user['id'] == $id_responsable)
        $found_user= true; 
        $name= $user['nombre'];
        if (!empty($user['cargo'])) 
            $name.= ", ".textparse($user['cargo']);
        else {
            $name.= ", ". $array_procesos_entity[$user['id_proceso']]['nombre'];
        }
        ?>
    
        <option value="<?= $user['id'] ?>" <?php if ($user['id'] == $id_responsable) echo "selected='selected'"; ?>><?=$name?></option>
    <?php } ?>
</select>

<?php
if (!$found_user && (!empty($id_responsable) && $id_responsable > 0)) {
    if (isset($obj_user)) 
        unset($obj_user);
    $obj_user= new Tusuario($clink);
    $obj_user->SetIdUsuario($id_responsable);
    $obj_user->Set();
?>
    <div class="col-10">
        <div class="alert alert-danger col-md-offset-2 col-lg-offset-2">
            EL usuario(a) <strong><?=$obj_user->GetNombre()?></strong> no esta registrado en esta entidad o fue eliminado(a) del sistema.
            Debe reasignar o delegar a otro responsable a la tarea.
        </div>
    </div>
<?php } ?>
