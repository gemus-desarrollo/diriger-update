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
require_once "../../php/class/perspectiva.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$id= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : null;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

if (empty($id) || $id == 'undefined') 
    $id= 0;

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;

$obj = new Tperspectiva($clink);
$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
$result = $obj->listar();
?>

<script type="text/javascript" charset="utf-8">
    <?php while ($row = $clink->fetch_array($result)) { ?>
    array_item_name[<?=$row['_id']?>]= '<?=$row['_nombre']?>';
    <?php } ?>
</script>

<select name="perspectiva" id="perspectiva" class="form-control">
    <option value="0">... </option>
    <?php
    $clink->data_seek($result);
    while ($row = $clink->fetch_array($result)) {
    ?>
        <option value="<?= $row['_id'] ?>" <?php if ($row['_id'] == $id) echo "selected='selected'"; ?>><?= $row['_nombre'] ?></option>
    <?php } ?>
</select> 

