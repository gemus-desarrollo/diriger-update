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
require_once "../../php/class/evento.class.php";
require_once "../../php/class/tipo_evento.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$id_subcapitulo= !empty($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : 0;
$empresarial= !empty($_GET['empresarial']) ? $_GET['empresarial'] : 0;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$tipo_actividad_flag= !empty($_GET['tipo_actividad_flag']) ? $_GET['tipo_actividad_flag'] : null;

$width= !empty($_GET['width']) ? $_GET['width'] : 600;

$tipo= !empty($id_subcapitulo) ? "3" : "2";
$subcapitulo= !empty($id_subcapitulo) ? "_subcapitulo" : "";

if ($id_proceso != $_SESSION['id_entity']) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->SetYear($year);
    $id_proceso_asigna= !empty($obj_prs->GetIdEntity()) ? $obj_prs->GetIdEntity() : $id_proceso;
}

if ($empresarial >= 1 && $id_subcapitulo != -1) {
    $obj_tipo= new Ttipo_evento($clink);
    $obj_tipo->SetYear($year);
    $obj_tipo->SetIdProceso($id_proceso);
    $obj_tipo->set_id_proceso_asigna($id_proceso_asigna);
    $result= $obj_tipo->listar($empresarial, $id_subcapitulo);

    $obj_event= new Tevento($clink);
    $numero= !empty($numero) ? $numero : $obj_event->find_numero($year,$empresarial,$id); 
    ?>

    <select class="form-control input-sm" id="tipo_actividad<?=$tipo_actividad_flag?><?=$tipo?>" name="tipo_actividad<?=$tipo?>" 
        onchange="refresh_ajax_select<?=$subcapitulo?>(0,0)" >
        <option value="0">...</option>

        <?php
        while ($row= $clink->fetch_array($result)) {
            $_numero= !empty($row['numero']) ? "{$row['numero']}) " : null;
        ?>
            <option value="<?=$row['id']?>" <?php if ($row['id'] == $id) echo "selected='selected'" ?>>
                <?="{$_numero} {$row['nombre']}"?>
            </option>
        <?php } ?>
    </select>

    <script language="javascript">setNumero(<?=$numero?>);</script>

<?php } else { ?>
    <select class="form-control input-sm" id="tipo_actividad<?=$tipo_actividad_flag?><?=$tipo?>" name="tipo_actividad<?=$tipo?>" >
        <option value="0">...</option>
    </select>
<?php } ?>