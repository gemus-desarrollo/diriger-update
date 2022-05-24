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

require_once "../../php/class/orgtarea.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$name= !empty($_GET['name']) ? $_GET['name'] : "subordinado";
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$user_date_ref= !is_null($_GET['user_date_ref']) ? urldecode($_GET['user_date_ref']) : null;
$fecha_inicio= !is_null($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : null;

if (!empty($user_date_ref))
    $user_date_ref= date2odbc($user_date_ref);

/*
 * configuracion de usuarios y procesos segun las prioridades del usuario
 */
$obj_sub= new Torgtarea($clink);
$obj_sub->SetIdResponsable($_SESSION['id_usuario']);
$obj_sub->set_user_date_ref($user_date_ref);
$obj_sub->get_subordinados_array(true);

if (!empty($id_usuario)) {
    $obj_user= new Tusuario($clink);
    $email= $obj_user->GetEmail($id_usuario);
}
?>

<select id="<?=$name?>" name="<?=$name?>" class="form-control">
    <option value >selecciona...</option>

    <option id="option_usr" value="<?= $id_usuario ?>" <?php if ((int)$id_usuario == (int)$id_responsable) echo "selected='selected'" ?> >
        <?= stripslashes($email['nombre']) ?>, <?= textparse($email['cargo']) ?> 
    </option>

    <?php
    reset($obj_sub->array_usuarios);
    foreach ($obj_sub->array_usuarios as $id_user => $array) {
        $nombre = $array['nombre'];
        $cargo = textparse($array['cargo']);
        if ($id_user == $id_usuario)
            continue;
        if ($id_user == $_SESSION['id_usuario'])
            continue;
        ?>
        <option class="option-<?=$name?>" value="<?= $id_user ?>" <?php if ((int)$id_user == (int)$id_responsable) echo "selected='selected'" ?> >
            <?= $nombre ?>, <?= $cargo ?> 
        </option>
    <?php } ?>
</select>

