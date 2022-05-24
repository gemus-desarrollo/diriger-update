<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/grupo.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0; 
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;		

$obj= new Tgrupo($clink);
$obj->SetIdEntity($_SESSION['id_entity']);
$result_grp= $obj->listar();
$cant= $obj->GetCantidad();
	
if (!empty($id_usuario)) {
    $obj_user= new Tusuario($clink);
    $obj_user->SetIdUsuario($id_usuario);
    $obj_user->set();

    $nombre= $obj_user->GetNombre();
    $cargo= $obj_user->GetCargo();    
    
    $obj->SetIdUsuario($id_usuario);
    $obj->GetUserGrupos();
    $array_grupos= $obj->array_grupos;
}
?>
 
<div class="alert alert-info">
    <strong>Usuario: </strong><?=$nombre?>
    <strong style="margin-left: 10px;">Cargo: </strong><?php if (!empty($cargo)) echo $cargo; ?>
</div>        

<form id="frm_grp" name="frm_grp" action="javascript:ejecutar_grupo()" method="post">
    <input type="hidden" name="exect" value="<?= $action ?>" />
    <input type="hidden" name="id" value="<?= $id_usuario ?>" />
    <input type="hidden" id="menu" name="menu" value="user_grupo" /> 

    <input type="hidden" id="t_cant_tab_grp" name="t_cant_tab_grp" value=0 />
    <input type="hidden" id="cant_tab_grp" name="cant_tab_grp" value=0 />               

    <div class="container-fluid">
        <div class="row">
            <div class="col-5">
                <legend>Usuarios y grupos</legend>
            </div>
            <div class="col-2"></div>
            <div class="col-5">
                <legend>Seleccionados</legend>
            </div>
        </div>                    

        <div id="multiselect-grp"></div>  
    </div>

    <!-- buttom -->
    <div id="_submit" class="btn-block btn-app">
        <?php if ($action == 'edit') { ?>
            <button class="btn btn-primary" type="submit">Aceptar</button>
        <?php } ?>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>            
</form>    

<script type="text/javascript">
    $(document).ready(function() {
        var data_grp= [
        <?php 
        $i= 0; 
        $j= 0;

        while ($row= $clink->fetch_array($result_grp)) {
            $value= $array_grupos[$row['_id']] ? 1 : 0;
            ++$j; 
            if ($value) 
                ++$i;
            $colom= (int)$j > 1 ? "," : "";
        ?>

            <?=$colom?>['<?=$row['_id']?>',"<i class='fa fa-users text-danger'></i><?=$row['nombre']?>", <?=$value?>, 0, '']
        <?php } ?> 
        ];

        multiselect('multiselect-grp', data_grp);

        $("#t_cant_tab_grp").val(<?= $j ?>);
        $("#cant_tab_grp").val(<?= $i ?>);
    }); 

</script>          