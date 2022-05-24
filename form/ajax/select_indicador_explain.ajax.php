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
require_once "../../php/class/indicador.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : null;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$cumulative= !is_null($_GET['cumulative']) ? $_GET['cumulative'] : 1;

if (empty($id_indicador) || $id_indicador == 'undefined') 
    $id_indicador= 0;
?>

<script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>

<?php
/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;

$obj = new Tindicador($clink);
$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
$result = $obj->listar();
?>

<select id="indicador" name="indicador" class="form-control" onchange="select_indi()">
    <option value="0">Seleccione....</option>
    <?php
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetYear($year);

    $clink->data_seek($result);
    $i = 0;
    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        if ($array_ids[$row['_id']])
            continue;
        $array_ids[$row['_id']]= 1;
        
        if ($row['_id'] == $id_indicador)
            continue;
        
        if ($row['id_proceso'] != $_SESSION['id_entity']) {
            if (!$obj->test_if_in_proceso($_SESSION['id_entity'], $row['_id']))
                continue;
        }
        
        ++$i;
        $tips_title = $row['_nombre'];
        $descripcion = $row['_descripcion'];

        $obj_prs->Set($row['_id_proceso']);
        $nombre_prs = $obj_prs->GetNombre();
        $proceso_tips = $img_tipo . "&nbsp;" . $img_conectdo . "<br />";
        $proceso_tips .= "<strong>Gestionado por:</strong> " . $nombre_prs . ", <em class=\'tooltip_em\'>" . $Ttipo_proceso_array[$obj_prs->GetTipo()] . "</em>";
        $proceso_tips .= "<br /><strong>Tipo de Conexion:</strong> " . $Ttipo_conexion_array[$obj_prs->GetConectado()];

        $descripcion .= "<br />" . $proceso_tips;
        ?>
        <option value="<?=$row['_id']?>" id="<?= $row['_id_code'] ?>" value="<?= $row['_id_code'] ?>" title="<?= $descripcion ?>"><?= "{$row['numero']}. {$row['nombre']}, {$row['_inicio']}-{$row['_fin']}, {$nombre_prs}" ?></option>
    <?php } ?>
</select>

