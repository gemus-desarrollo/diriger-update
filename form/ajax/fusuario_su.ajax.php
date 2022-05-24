<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/grupo.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/proceso.class.php";

$action= !empty($_GET['action'])? $_GET['action']: 'list';
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$error= !empty($_GET['error']) ? urldecode($_GET['error']): null;
$year= !empty($_GET['year'])? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];

$obj= new Torgtarea($clink);
$obj_user= new Tusuario($clink);

$user_date_ref= $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($day, 2, "0", STR_PAD_LEFT);

$obj_user->set_user_date_ref($user_date_ref);

if (!empty($id_usuario)) {
    $obj_user->SetIdUsuario($id_usuario);
    $obj_user->set();
    
    $nombre= $obj_user->GetNombre();
    $cargo= $obj_user->GetCargo(); 
    
    $obj->SetIdResponsable($id_usuario);
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;
}

$obj_prs= new Tproceso($clink);

$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetTipo($_SESSION['entity_tipo']);

$result_prs= $obj_prs->listar_in_order('eq_asc_desc', true, null, null, 'desc');
$result_prs= $obj_prs->sort_array_procesos($result_prs);

$obj_user= new Tusuario($clink);
$obj_user->Set($id_usuario);
?>


<div class="alert alert-info">
    <strong>Usuario: </strong><?=$nombre?>
    <strong style="margin-left: 10px;">Cargo: </strong><?=!empty($cargo) ? $cargo : null ?>
</div> 

<form id="frm_su" name="frm_su" action="javascript:ejecutar('su')"  method="post">
    <input type="hidden" name="exect" value="<?= $action ?>" />
    <input type="hidden" name="id" value="<?= $id_usuario ?>" />
    <input type="hidden" name="menu" value="user_subordinado" />

    <?php
    $id= $id_usuario;
    $id_user_restrict= $id_usuario;
    $user_ref_date= "$year-$month-$day";
    $restrict_prs= array(_TIPO_PROCESO_INTERNO);
    $config->freeassign= true;

    require "../inc/usuario_tabs.inc.php";
    ?>                   

    <!-- buttom -->
    <div id="_submit" class="btn-block btn-app">
        <?php if ($action == 'edit') { ?>
            <button class="btn btn-primary" type="submit">Aceptar</button>
        <?php } ?>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>            
</form>    
