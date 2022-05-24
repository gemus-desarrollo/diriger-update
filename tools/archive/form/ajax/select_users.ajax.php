<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */


session_start();
include_once "../../../../php/setup.ini.php";
include_once "../../../../php/class/config.class.php";

include_once "../../../../php/config.inc.php";
include_once "../../../../php/class/connect.class.php";
include_once "../../../../php/class/proceso.class.php";
include_once "../../../../php/class/usuario.class.php";

include_once "../../../../php/class/badger.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$year= !empty($_GET['year']) ? $_GET['id_usuario'] : date('Y');
$name_select= $_GET['name_select'];

if (isset($obj_prs)) unset($obj_prs);
$obj_prs= new Tproceso($clink);

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger();
$badger->SetYear($year);
// $badger->set_user_date_ref($fecha_origen);

 if ($badger->freeassign) 
     $obj_prs->set_use_copy_tusuarios(false);
 else 
     $obj_prs->set_use_copy_tusuarios(true);

 if (empty($id_proceso)) 
     $id_proceso= $_SESSION['local_proceso_id'];
 $obj_prs->SetIdProceso($id_proceso);

 $obj_prs->set_user_date_ref($fecha_inicio);
 $obj_prs->listar_usuarios_proceso($id_proceso, null, null, null, false);
 $list= $obj_prs->array_usuarios;
?>

 <select name="<?=$name_select?>" id="<?=$name_select?>" class="form-control">
     <option value=0 <?php if (empty($id_responsable)) echo "selected='selected'" ?>>Seleccione ... </option>
     <?php
     foreach ($list as $array) {
         if ($array['id'] == _USER_SYSTEM) 
             continue;
         if (empty($array['nombre'])) 
             continue;
         $cargo= !empty($array['cargo']) ? ", {$array['cargo']}" : "";
         ?>
         <option value="<?= $array['id'] ?>" <?php if ($array['id'] == $id_usuario) echo "selected='selected'"; ?>><?= "{$array['nombre']}{$cargo}" ?></option>
     <?php } ?>
</select>

