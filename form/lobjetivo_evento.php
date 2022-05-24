<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/evento.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

$signal= 'objetivo';
$restrict_prs= array(_TIPO_DIRECCION);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tobjetivo($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_inductor= new Tinductor($clink);
$obj_indicador= new Tindicador($clink);
$obj_peso= new Tpeso($clink);

$url_page= "../form/lobjetivo.php?signal=$signal&action=$action&menu=objetivo&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action";

set_page($url_page);

$signal= 'objetivo';
$restrict_prs= null;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <?php 
        if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
            $delay= (int)$config->delay*60;
            header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
        } 
        ?>

    <title>ACTIVIDADES POR OBJETIVOS</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <div class="app-body container-fluid threebar">

        <ul class=ul_top>
            <?php
            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear($year);
            $obj_peso->set_matrix();

            $obj->SetIdProceso($id_proceso);
            $obj->SetIdEscenario(null);
            $obj->SetYear($year);

            $result_obj_sup= $obj->listar();

            $k_obj= 0;
            $k_ind= 0;
            $k_indi= 0;

            _tree_objetivo_prs($result_obj_sup, $k_obj, $k_ind, $k_indi, 0, 0);

            $obj_peso->close_matrix();
            ?>
        </ul>

    </div>

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$k_obj?>;
    </script>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>

<?php
function _tree_objetivo_prs($result_obj, &$k_obj, &$k_ind, &$k_indi, $level, $k_obj_sup= 0, $if_obj_sup= false, $if_pol= false) {
    global $clink;
    global $id_proceso, 
           $id_proceso_code;
    global $year;
    global $month;
    global $day;

    global $item_planning_array;
    global $Ttipo_proceso_array;
    global $Tpeso_inv_array;
    global $obj_peso;
    global $array_procesos_entity;

    $obj_signal= new Tlist_signals($clink);
    $obj_signal->SetYear($year);
    $obj_signal->SetMonth($month);
    
    $obj= new Tobjetivo($clink);  
    $obj_user= new Tusuario($clink);
    $obj_prs= new Tproceso($clink);
    
    $_k_obj= 0; 
    $_k_obj_sup= 0;

    while ($row_obj= $clink->fetch_array($result_obj)) {
        $prs= $array_procesos_entity[$row_obj['id_proceso']];
        if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
            continue;
        if (empty($prs['id_entity']) && (!$row_obj['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
            continue;
        if (empty($prs['id_entity']) && (!$row_obj['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
            continue;

        $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
        $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;
        
        ++$k_obj;
        if ($level == 0 && $if_obj_sup) 
            ++$k_obj_sup;

        $id_objetivo= $row_obj['_id'];
        $peso= $row_obj['_peso'];
        $obj->Set($id_objetivo);
        $nombre= $obj->GetNombre();

        $obj_peso->SetIdProceso($id_proceso);
        $obj_peso->set_id_proceso_code($id_proceso_code);
        /*
        $obj_peso->SetDay($day);
        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->compute_traze= true;
        $value2= $obj_peso->calcular_objetivo($id_objetivo);
        $array_register= $obj_peso->get_array_register();
        */
        /*
        $obj_signal->get_month($_month, $_year);
        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($_year, $_month);
        $obj_peso->SetDay(null);
        $value1= $obj_peso->calcular_objetivo($id_objetivo);
        */
        $id_user= $array_register['id_usuario'];
        $item_reg= $array_register['signal'];

        $responsable= null;
        $observacion= null;

        if ($id_user && $item_reg == 'OBJ') {
            $observacion= textparse($array_register['observacion'], true);

            $email_user= $obj_user->GetEmail($id_user);
            $responsable= $email_user['nombre'];
            if (!is_null($email_user['cargo'])) 
                $responsable.= ', '.textparse($email_user['cargo'], true);
            $responsable.= '  <br /><strong>corte:</strong>'.odbc2date($array_register['reg_fecha']).'   <strong>registrado:</strong>'.odbc2time_ampm($array_register['cronos']);
        }
        ?>

<?php
        $_item_sup_obj= 0;

        if (!$if_pol) {
            $_item_obj= ($level == 0 && $if_obj_sup) ? "obj_sup" : "obj";

            if ($level == 0) 
                $_item_sup_obj= 0;
            else 
                $_item_sup_obj= ($level == 1 && $if_obj_sup) ? "'obj_sup'" : "'obj'";

            $_k_obj= ($level == 0 && $if_obj_sup) ? $k_obj_sup : $k_obj;
            $_k_obj_sup= ($level == 0) ? 0 : $k_obj_sup;
        }
        else {
            $_item_sup_obj= "'pol'";
            $_item_obj= 'obj';
            $_k_obj= ($level == 0 && $if_obj_sup) ? $k_obj_sup : $k_obj;
            $_k_obj_sup= $k_obj_sup;
        }
        ?>

<input type="hidden" form="treeForm" id="id_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$id_objetivo?>" />
<input type="hidden" form="treeForm" id="observacion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$observacion?>" />
<input type="hidden" form="treeForm" id="registro_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$responsable?>" />
<input type="hidden" form="treeForm" id="descripcion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$nombre?>" />
<input type="hidden" form="treeForm" id="if_entity_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$if_entity?>" />

<input type="hidden" id="page_objetivo_<?=$_item_obj?>_<?=$_k_obj?>" name="page_objetivo_<?=$id_objetivo?>"
    value="<?=$id_objetivo?>" />

<li id="<?=$_item_obj?>_li_<?=$_k_obj?>">
    <div class=ul_pol onmouseover="this.className='ul_pol rover-obj'" onmouseout="this.className='ul_pol'">
        <div class="div_inner_ul_li" onclick="refresh_<?=$_item_obj?>('<?=$_item_obj?>_ul_<?=$_k_obj?>')">

            <div class="alarm-block">
                <i id="img_<?=$_item_obj?>_li_<?=$_k_obj?>" class="fa fa-search-plus" title="expandir"></i>

                <?php
                        $obj_signal->get_alarm($value2);
                        $obj_signal->get_flecha($value2, $value1);
                        ?>
            </div>

            <?php
                    $nombre= get_short_label($nombre);

                    $obj_prs->Set($row_obj['id_proceso']);
                    $tipo_prs= $obj_prs->GetTipo();
                    $proceso= $obj_prs->GetNombre();
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                    $numero= !empty($row_obj['numero']) ? $row_obj['numero'] : $_k_obj;
                    ?>
        </div>

        <div class="div_inner_ul_li"
            onclick="ShowContentItem('<?=$_item_obj?>', <?=$_k_obj?>, <?=$_item_sup_obj?>, <?=$_k_obj_sup?>);">
            <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>
            <span class="flag">No.<?=$numero?> </span><?=$nombre?>

            <br />
            <img class="img-rounded" src="../img/<?=img_item_planning('obj')?>"
                title="<?=$item_planning_array['obj']?>" />
            <img class="img-rounded" src="../img/<?=img_process($tipo_prs)?>"
                title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong class="strong-title"><?=$proceso?></strong>

            <strong class="strong-title">periodo: </strong><?="{$row_obj['inicio']}-{$row_obj['fin']}"?>
            <?php if (!is_null($peso)) { ?><strong class="strong-title">Ponderación: <span class="peso">
                    <?=$Tpeso_inv_array[$peso]?></span></strong><?php } ?>

        </div>
    </div>

    <?php
            $obj_peso->SetYear($year);
            $obj_peso->SetIdProceso(null);
            $iresult_obj= $obj_peso->listar_procesos_ref_objetivo($id_objetivo);
            $_cant_obj_li= $obj_peso->GetCantidad();

            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);
            $obj_peso->SetIdProceso($id_proceso);

            $result_ind= $obj_peso->listar_inductores_ref_objetivo($id_objetivo, true);
            $_cant_obj= $obj_peso->GetCantidad();

            $t_k_obj= $k_obj;
            ?>

    <input type="hidden" form="treeForm" id="_cant_<?=$_item_obj?>_ul_<?=$_k_obj?>" value=<?=$_cant_obj_li?> />

    <?php if ($_cant_obj_li > 0 || $_cant_obj > 0) { ?>
    <ul id="<?=$_item_obj?>_ul_<?=$_k_obj?>" style="display:none">
        <?php
                    if ($_cant_obj_li > 0) 
                        _tree_objetivo($iresult_obj, $k_obj, $k_ind, $k_indi, $level+1, $k_obj_sup, $if_obj_sup);

                    if ($_cant_obj > 0) {
                        $_cant_obj= $_cant_obj_li + $clink->num_rows($result_ind);
                    ?>
        <script language="javascript">
        $("#_cant_<?=$_item_obj?>_ul_<?=$_k_obj?>").val(<?=$_cant_obj?>);
        </script>
        <?php } ?>

        <?php
                    $if_top_inductor= false;
                    $id_item_sup= $t_k_obj;
                    $item_sup= 'obj';
                    include "_tree_inductor.inc.php";
                    ?>
    </ul>
    <?php } ?>
</li>
<?php
    }
}

?>