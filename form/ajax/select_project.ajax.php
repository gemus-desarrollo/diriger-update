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
require_once "../../php/class/proyecto.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$acc= $_SESSION['acc_planproject'];

$obj_prs= new Tproceso($clink);

$obj_prs->SetIdResponsable(null);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetConectado(null);
$obj_prs->SetTipo(null);

$corte_prs= _TIPO_ARC;

if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
    $obj_prs->SetIdUsuario(null);
    $array_procesos= $obj_prs->listar_in_order('eq_desc', false,  $corte_prs, false);
} elseif ($acc == _ACCESO_BAJA) {
    $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
    $array_procesos= $obj_prs->get_procesos_by_user('eq_desc', $corte_prs, false, null, $exclude_prs);
}

$obj_prs->SetIdUsuario(null);
$j= 0;

if ($acc == _ACCESO_ALTA) {
    if (!array_key_exists($_SESSION['id_entity'], $array_procesos)) {
        $array= array('id'=>$_SESSION['id_entity'], 'id_code'=>$_SESSION['id_entity_code'], 'nombre'=>$_SESSION['entity_nombre'],   
            'tipo'=>$_SESSION['entity_tipo'], 'id_responsable'=>$_SESSION['entity_id_responsable'], 'conectado'=>$_SESSION['entity_conectado'], 
            'id_proceso'=>$_SESSION['superior_entity_id']);

        array_unshift($array_procesos, $array);
    }
}

$obj_proj= new Tproyecto($clink);

$obj_proj->SetYear($year);
$obj_proj->SetMonth($month);
$obj_proj->SetDay($day);
$obj_proj->SetIdProceso($id_proceso);
$obj_proj->SetIdPrograma($id_programa);

$obj_proj->SetIdResponsable($_SESSION['id_usuario']);
$obj_proj->listar(true, false);
$array_proyectos= $obj_proj->array_proyectos;

foreach ($array_procesos as $proceso) {
    $obj_proj->GetProyectosProceso($proceso['id']);
}

$j= 0; $pos= 0;
$array_proyectos= $obj_proj->array_proyectos;
reset($array_proyectos);


foreach ($array_proyectos as $row) {
?>
    <input type="hidden" id="proyecto_code_<?=$row['id']?>" name="proyecto_code_<?=$row['id']?>" value="<?=$row['id_code']?>" />
    <input type="hidden" id="proyecto_fecha_origen_<?=$row['id']?>" name="proyecto_fecha_origen_<?=$row['id']?>" value="<?=odbc2date($row['fecha_inicio_plan'])?>" />
    <input type="hidden" id="proyecto_fecha_termino_<?=$row['id']?>" name="proyecto_fecha_termino_<?=$row['id']?>" value="<?=odbc2date($row['fecha_fin_plan'])?>" />
<?php } ?>

<select name="proyecto" id="proyecto" class="texta" style="width:600px;" <?=$disable?> onchange="refreshp()" >
    <?php if (!($action == 'add' && !empty($signal) && !empty($id_proyecto))) { ?>
        <option value=0>...</option>
    <?php } ?>

 <?php
   reset($array_proyectos);
    foreach ($array_proyectos as $row) {
        if ($action == 'add' && !empty($signal) && !empty($id_proyecto) && ($row['_id'] != $id_proyecto)) 
            continue;
  ?>
        <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_proyecto) echo "selected='selected'"; ?>><?php echo $row['nombre']."  (".odbc2date($row['fecha_inicio_plan'])." - ".odbc2date($row['fecha_fin_plan']).")" ?></option>
   <?php }?>
</select>
