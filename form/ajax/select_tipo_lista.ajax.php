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
require_once "../../php/class/lista.class.php";
require_once "../../php/class/tipo_lista.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : 0;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : 0;

$componente= !empty($_GET['componente']) ? $_GET['componente'] : 0;
$id_capitulo= !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : 0;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;

$panel= !empty($_GET['panel']) ? $_GET['panel'] : "";
$width= !empty($_GET['width']) ? $_GET['width'] : 600;

$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : null;
$name_tipo= null;

if ($tipo == "cap") 
    $name_tipo= "sub";

if ($id_capitulo == -1) 
    $id_capitulo= 0;

if ($componente >= 1) {
    $obj_tipo= new Ttipo_lista($clink);
    $obj_tipo->SetYear($year);
    $obj_tipo->SetIdProceso(null);
    $obj_tipo->SetIdLista($id_lista);
    $result= $obj_tipo->listar($componente, $id_capitulo);

    $obj_req= new Tlista_requisito($clink);
    $obj_req->SetIdLista($id_lista);
    $obj_req->SetIdCapitulo($id_capitulo);
    $numero= !empty($numero) ? $numero : $obj_req->find_numero($componente, $year, !empty($id) ? $id : $id_capitulo);
    if (empty($numero)) {
        $numero= 1;
    } ?>

    <?php if ($tipo != "epi") { ?>
    <select class="form-control input-sm" id="<?=$name_tipo?>capitulo<?=$panel?>" name="<?=$name_tipo?>capitulo<?=$panel?>" 
            <?php if (is_null($tipo)) { ?>onchange="refresh_ajax_select_capitulo('<?=$panel?>', 0, 0)"<?php } ?> 
            <?php if ($tipo == "cap") { ?>onchange="refresh_ajax_select_subcapitulo('<?=$panel?>', 0, 0)"<?php } ?> 
            >
        <option value=0>...</option>

        <?php
        while ($row= $clink->fetch_array($result)) {
            if (!empty($year) && (!empty($row['year']) && $row['year'] < $year)) {
                continue;
            }
            $_numero= !empty($row['numero']) ? "{$row['numero']}) " : null; ?>
            <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_tipo_lista) {
                echo "selected='selected'";
            } ?>>
                <?="{$_numero} {$row['nombre']}"?>
            </option>
        <?php
        } ?>
    </select>
    <?php } ?>

    <?php 
    if ((is_null($tipo) && $componente > 0) || ($tipo == "cap" && $id_capitulo != -1) || ($tipo == "epi" && $id != -1)) { 
    ?>
    <script language="javascript">setNumero(<?=$numero?>);</script>
    <?php } ?>

<?php } else { ?>
    <select class="form-control input-sm" id="<?=$name_tipo?>capitulo<?=$panel?>" name="<?=$name_tipo?>capitulo<?=$panel?>" >
        <option value=0>...</option>
    </select>
    
    <script language="javascript">setNumero(0);</script>
<?php } ?>