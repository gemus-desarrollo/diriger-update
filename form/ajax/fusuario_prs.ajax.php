<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_usuario = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$id_proceso= null;

$obj = new Tproceso($clink);
$obj->SetYear($_SESSION['current_year']);
$obj->SetIdProceso($_SESSION['local_proceso_id']);
$obj->SetTipo($_SESSION['local_proceso_tipo']);

$result_prs_array = $obj->listar_in_order('eq_asc_desc', true, false);
$cant = $obj->GetCantidad();

if (!empty($id_usuario)) {
    $obj_user = new Tusuario($clink);
    $obj_user->SetIdUsuario($id_usuario);
    $obj_user->set();
    
    $id_proceso= $obj_user->GetIdProceso();
    $nombre= $obj_user->GetNombre();
    $cargo= $obj_user->GetCargo(); 
    
    $obj->SetIdResponsable($id_usuario);
    $obj->SetIdUsuario($id_usuario);
    $array_procesos= $obj->get_procesos_by_user();
}
?>

<div class="alert alert-info">
    <strong>Usuario: </strong><?=$nombre?>
    <strong style="margin-left: 10px;">Cargo: </strong><?php if (!empty($cargo)) echo $cargo; ?>
</div>   

<form id="frm_prs" name="frm_prs" class="form-in-win" action="javascript:ejecutar('prs')"  method=post>
    <input type="hidden" name="exect" value="<?= $action ?>" />
    <input type="hidden" name="id" value="<?= $id_usuario ?>" />
    <input type="hidden" name="menu" value="user_proceso" /> 

    <?php
    $name_form= "fusuario_prs.ajax";
    $id_prs_restrict= $id_proceso;
    $restrict_prs = null;
    $restrict_up_prs = true;
    $filter_by_toshow = true;

    $create_select_input= false;
    require "../inc/proceso_tabs.inc.php";
    ?> 

    <!-- buttom -->
    <div id="_submit" class="btn-block btn-app">
        <?php if ($action == 'edit') { ?>
            <button class="btn btn-primary" type="submit">Aceptar</button>
        <?php } ?>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>            
</form>    

