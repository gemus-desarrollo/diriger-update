<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/politica.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

require_once "../php/class/badger.class.php";

$signal= 'politica';
$restrict_prs= array(_TIPO_DIRECCION, _TIPO_PROCESO_INTERNO, _TIPO_GRUPO, _TIPO_DEPARTAMENTO);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add')
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : 0;
$grupo= !empty($_GET['grupo']) ? $_GET['grupo'] : 0;
$chk_sys= !is_null($_GET['chk_sys']) ? $_GET['chk_sys'] : 1;
$chk_inner= !is_null($_GET['chk_inner']) ? $_GET['chk_inner'] : 0;
$chk_title= !is_null($_GET['chk_title']) ? $_GET['chk_title'] : 0;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tpolitica($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

require_once "../php/inc_escenario_init.php";


$obj->SetIdProceso($id_proceso);
$obj->SetYear($year);

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_inductor= new Tinductor($clink);
$obj_indicador= new Tindicador($clink);
$obj_objetivo= new Tobjetivo($clink);
$obj_peso= new Tpeso($clink);

$url_page= "../form/lpolitica.php?signal=$signal&action=$action&menu=politica&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&exect=$action&chk_inner=$chk_inner&chk_sys=$chk_sys&chk_title=$chk_title";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <meta http-equiv="refresh" content="<?= (int)$config->delay*60?>" />

    <title>LISTADO DE POLÍTICAS O LINEAMIENTOS DE TRABAJO</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>

    <style type="text/css">
    .title_pol {
        width: 60%;
        color: #000000;
        font-size: 1.1em;
        padding: 3px;
        margin-top: 8px;
        margin-bottom: 4px;
    }

    .capitulo {
        background-color: lightblue;
        font-weight: bold;
    }

    .grupo {
        background-color: aquamarine;
        font-weight: bold;
    }

    #div-filter {
        width: 60%;
    }
    </style>

    <script type="text/javascript">
    function _refreshp(flag) {
        var capitulo = $('#capitulo').val();
        var grupo = $('#grupo').val();
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();

        var chk_inner = $('#chk_inner').is(':checked') ? 1 : 0;
        var chk_sys = $('#chk_sys').is(':checked') ? 1 : 0;
        var chk_title = $('#chk_title').is(':checked') ? 1 : 0;

        var url = 'lpolitica.php?version=&action=<?=$action?>&capitulo=' + capitulo +
            '&grupo=' + grupo;
        url += '&id_proceso=' + id_proceso + '&year=' + year + '&month=' + month + '&chk_sys=' + chk_sys +
            '&chk_inner=' + chk_inner;
        url += '&chk_title=' + chk_title;

        parent.app_menu_functions = false;

        self.location = url;
    }

    function _set_grupo() {
        $('#grupo').val(0);
    }
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <div class="app-body container-fluid twobar">

    <?php
        $string_procesos_down_entity= $id_proceso != $_SESSION['id_entity'] ? null : $string_procesos_down_entity;
        
        $k_pol= 0;
        $k_pol_total= 0;
        $array_capitulo= array();
        $array_grupo= array();
        $ipol= 0;

        $array_politicas= null;

        if (isset($obj)) 
            unset($obj);
        $obj= new Tpolitica($clink);
        $obj->SetYear($year);
        $obj->SetIfTitulo(false);
        $result_pol= $obj->listar();
        $k_pol_total= $obj->GetCantidad();

        create_array_valid_politicas($result_pol);

        $clink->free_result($result_pol);

        unset($obj);
        $obj= new Tpolitica($clink);
        $obj->SetCapitulo($capitulo);
        $obj->SetGrupo($grupo);
        $obj->SetYear($year);

        $chk_sys= empty($chk_sys) ? false : true;
        $result_pol= $obj->listar($chk_sys);

        while ($row_pol= $clink->fetch_array($result_pol)) {
            ++$ipol;

            if ($chk_inner && !boolean($row_pol['if_inner']))
                continue;

            if (empty($row_pol['capitulo']))
                if (empty($array_capitulo[$row_pol['id']]['name'])) 
                    $array_capitulo[$row_pol['id']]['name']= $row_pol['nombre'];

            if (boolean($row_pol['titulo']) && (!empty($row_pol['capitulo']) && empty($row_pol['grupo'])))
                if (empty($array_grupo[$row_pol['id']]['name'])) 
                    $array_grupo[$row_pol['id']]['name']= $row_pol['nombre'];

            if (!boolean($row_pol['titulo'])) {
                $array_capitulo[$row_pol['capitulo']]['n']= empty($array_capitulo[$row_pol['capitulo']]['n']) ? 1 : ++$array_capitulo[$row_pol['capitulo']]['n'];

                if (!empty($row_pol['grupo']))
                    $array_grupo[$row_pol['grupo']]['n']= empty($array_grupo[$row_pol['grupo']]['n']) ? 1 : ++$array_grupo[$row_pol['grupo']]['n'];
            }
        }
        ?>

        <ul class=ul_top style="margin-top:0">
            <?php
            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetIdProceso($id_proceso);
            $obj_peso->set_id_proceso_code($id_proceso_code);

            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);
            $obj_peso->set_matrix();

            /**
             * Los que estan definidos dentro de un titulo o capitulo
             */
            unset($obj);
            $obj= new Tpolitica($clink);
            $obj->SetIfTitulo(false);
            $obj->SetIfCapitulo(false);
            $obj->SetYear($year);

            $k_pol_show= 0;
            $k_pol= 0;

            _tree_politica($k_pol, 0, false, $k_pol_show, $k_obj, $k_ind, $k_indi);

            $obj->SetIfTitulo(true);
            $obj->SetIfCapitulo(true);

            $result_pol_cap= $obj->listar(false, $string_procesos_down_entity);

            while ($row_pol_cap= $clink->fetch_array($result_pol_cap)) {
                if ($chk_inner && !boolean($row_pol_cap['if_inner']))
                    continue;
                if (!empty($capitulo) && $capitulo != $row_pol_cap['id'])
                    continue;

                ++$k_pol;
                $nombre= $row_pol_cap['nombre'];
                $numero= $row_pol_cap['numero'];
                $if_titulo= boolean($row_pol_cap['titulo']);
                $if_inner= boolean($row_pol_cap['if_inner']);
                ?>

                <input type="hidden" id="if_titulo_pol_<?=$k_pol?>" value=<?=$if_titulo?> />
                <input type="hidden" id="if_inner_pol_<?=$k_pol?>" value=<?=$if_inner?> />

                <input type="hidden" id="id_pol_<?=$k_pol?>" value="<?=$row_pol_cap['_id']?>" />
                <input type="hidden" id="descripcion_pol_<?=$k_pol?>" value="<?=$nombre?>" />

                <input type="hidden" id="registro_pol_<?=$k_pol?>" value="" />
                <input type="hidden" id="observacion_pol_<?=$k_pol?>" value="" />

                <?php
                if ((!empty($array_capitulo[$row_pol_cap['id']]['n']) || $chk_title) && !empty($array_capitulo[$row_pol_cap['id']]['name'])) {
                ?>
                    <li id="pol_li_<?=$k_pol?>">
                        <div class="ul_pol title_pol capitulo" onclick="ShowContentItem('pol', <?=$k_pol?>, 0, 0);">
                            <?=$array_capitulo[$row_pol_cap['id']]['name']?>
                            <?php
                            if (boolean($row_pol_cap['if_inner'])) {
                                unset($obj_prs);
                                $obj_prs= new Tproceso($clink);
                                $obj_prs->Set($row_pol_cap['id_proceso']);
                                $tipo_prs= $obj_prs->GetTipo();
                                $proceso= $obj_prs->GetNombre();
                                $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                            ?>

                            <br />
                            <img src="../img/<?=img_item_planning('pol')?>" title="<?=$item_planning_array['pol']?>" />
                            <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                                class="strong-title"><?=$proceso?></strong>
                            <strong class="strong-title">periodo: </strong><?="{$row_pol_cap['inicio']}-{$row_pol_cap['fin']}"?>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>

                <?php
                unset($obj);
                $obj= new Tpolitica($clink);

                $obj->SetYear($year);
                $obj->SetIfTitulo(true);
                $obj->SetIfGrupo(true);
                $obj->SetCapitulo($row_pol_cap['id']);

                $result_pol_grupo= $obj->listar(false, $string_procesos_down_entity);
                $cant= $obj->GetCantidad();

                _tree_politica($k_pol, $row_pol_cap['id'], false, $k_pol_show, $k_obj, $k_ind, $k_indi);

                while ($row_pol_grupo= $clink->fetch_array($result_pol_grupo)) {
                    if ($chk_inner && !boolean($row_pol_grupo['if_inner']))
                        continue;
                    if (!empty($grupo) && $grupo != $row_pol_grupo['id'])
                        continue;

                    ++$k_pol;
                    $nombre= $row_pol_grupo['nombre'];
                    $numero= $row_pol_grupo['numero'];
                    $if_titulo= boolean($row_pol_grupo['titulo']);
                    $if_inner= boolean($row_pol_grupo['if_inner']);
                    ?>

                    <input type="hidden" id="if_titulo_pol_<?=$k_pol?>" value=<?=$if_titulo?> />
                    <input type="hidden" id="if_inner_pol_<?=$k_pol?>" value=<?=$if_inner?> />

                    <input type="hidden" id="id_pol_<?=$k_pol?>" value="<?=$row_pol_grupo['_id']?>" />
                    <input type="hidden" id="descripcion_pol_<?=$k_pol?>" value="<?=$nombre?>" />

                    <input type="hidden" id="registro_pol_<?=$k_pol?>" value="" />
                    <input type="hidden" id="observacion_pol_<?=$k_pol?>" value="" />

                    <?php
                    if ((!empty($array_grupo[$row_pol_grupo['id']]['n']) || $chk_title) && !empty($array_grupo[$row_pol_grupo['id']]['name'])) {
                    ?>
                        <li id="pol_li_<?=$k_pol?>">
                            <div class="ul_pol title_pol grupo" onclick="ShowContentItem('pol', <?=$k_pol?>, 0, 0);">
                                <?=$array_grupo[$row_pol_grupo['id']]['name']?>
                                <?php
                                if (boolean($row_pol_grupo['if_inner'])) {
                                    unset($obj_prs);
                                    $obj_prs= new Tproceso($clink);
                                    $obj_prs->Set($row_pol_grupo['id_proceso']);
                                    $tipo_prs= $obj_prs->GetTipo();
                                    $proceso= $obj_prs->GetNombre();
                                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                                ?>

                                <br />
                                <img src="../img/<?=img_item_planning('pol')?>" title="<?=$item_planning_array['pol']?>" />
                                <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                                    class="strong-title"><?=$proceso?></strong>
                                <strong class="strong-title">periodo:
                                </strong><?="{$row_pol_grupo['inicio']}-{$row_pol_grupo['fin']}"?>
                                <?php } ?>
                            </div>
                        </li>
                    <?php
                    }

                    _tree_politica($k_pol, $row_pol_cap['id'], $row_pol_grupo['id'], $k_pol_show, $k_obj, $k_ind, $k_indi);
                }
            }

            $obj_peso->close_matrix();
            ?>
        </ul>

    </div>

    <?php require "inc/_tree_js_div.inc.php" ?>

    <?php
        if ($signal == 'politica') {
            $cant_print_reject= (int)$k_pol_total - (int)$k_pol_show;
        ?>
    <script type="text/javascript" language="JavaScript">
    $('#nshow').html('<?=(int)$k_pol_show?>');
    $('#nhide').html('<?=$cant_print_reject?>');
    </script>
    <?php } ?>

    <!-- div-filter -->
    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title win-drag col-11 m-0">FILTRADO DE POLÍTICAS O LINEAMIENTOS DE TRABAJO</div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-1">
                        Capitulo:
                    </label>
                    <div class="col-11">
                        <?php
                        $obj->SetIfTitulo(1);
                        $obj->SetYear($year);
                        $result_pol = $obj->listar();
                        ?>

                        <select name="capitulo" id="capitulo" class="form-control"
                            onchange="javascript:_set_grupo(); _refreshp();">
                            <option style="margin-bottom:4px" value="0"> ... </option>

                            <?php
                            while ($row_pol = $clink->fetch_array($result_pol)) {
                                if (!empty($row_pol['capitulo']))
                                    continue;
                                ?>
                            <option value="<?= $row_pol['id'] ?>"
                                <?php if ($row_pol['id'] == $capitulo) echo "selected='selected'"; ?>>
                                <?= $row_pol['nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-1">
                        Epigrafe:
                    </label>
                    <div class="col-11">
                        <?php $clink->data_seek($result_pol); ?>

                        <select name="grupo" id="grupo" class="form-control">
                            <option value="0"> ... </option>
                            <?php
                            while ($row_pol = $clink->fetch_array($result_pol)) {
                                if (empty($row_pol['capitulo']))
                                    continue;
                                ?>
                            <option value="<?= $row_pol['id'] ?>"
                                <?php if ($row_pol['id'] == $grupo) echo "selected='selected'" ?>>
                                <?= $row_pol['nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="chk_sys" name="chk_sys" value="1"
                            <?php if (!empty($chk_sys)) echo "checked='checked'" ?> />
                        Mostrar solo las Políticas o Lineamientos que estan vinculados a Objetivos Estratégico de la
                        Organización< </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="chk_inner" name="chk_inner" value="1"
                            <?php if (!empty($chk_inner)) echo "checked='checked'" ?> />
                        Mostrar solo las Políticas o Lineamientos emitidas por el Organismo Superior
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="chk_title" name="chk_title" value="1"
                            <?php if (!empty($chk_title)) echo "checked='checked'" ?> />
                        Mostrar los Capitulos y Epigrafes aunque no contengan Políticas o Lineamientos definidos
                    </label>
                </div>
            </div>

            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="refreshp()">Refrescar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
            </div>
        </div>
    </div> <!-- div-filter -->

</body>
</html>


<?php
function _tree_politica(&$k_pol, $capitulo, $grupo, &$k_pol_show, &$k_obj, &$k_ind, &$k_indi) {
    global $clink;
    global $year,
            $month,
            $day;
    global $obj_signal;
    global $obj_user;
    global $id_proceso,
            $id_proceso_code;
    global $chk_sys, $chk_inner;
    global $item_planning_array;
    global $Ttipo_proceso_array;

    global $string_procesos_down_entity;

    global $obj_peso;

    global $array_politicas;

    $obj= new Tpolitica($clink);
    $obj->SetYear($year);
    $obj->SetIdProceso($id_proceso);

    $obj->SetIfTitulo(false);
    
    if ($grupo === false)
        $obj->SetIfGrupo(false);

    $obj->SetIfCapitulo(false);
    $obj->SetCapitulo($capitulo);

    if (!empty($grupo))
        $obj->SetGrupo($grupo);

    $chk_sys= empty($chk_sys) ? false : true;
    $result_pol= $obj->listar($chk_sys, $string_procesos_down_entity);

    while ($row_pol= $clink->fetch_array($result_pol)) {
        $id_politica= $row_pol['_id'];

        if (empty($array_politicas[$row_pol['_id']]))
            continue;
        if ($chk_inner && !boolean($row_pol['if_inner']))
            continue;

        ++$k_pol;
        ++$k_pol_show;
        $nombre= $row_pol['nombre'];
        $numero= $row_pol['numero'];

        $obj_peso->SetYear($year);
        $obj_peso->SetMonth($month);
        $obj_peso->SetDay($day);

        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->compute_traze= true;
        $value2= $obj_peso->calcular_politica($id_politica);
        $array_register= $obj_peso->get_array_register();

        $obj_signal->get_month($_month, $_year);
        $obj_peso->SetYear($_year);
        $obj_peso->SetMonth($_month);

        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($_year, $_month);
        $obj_peso->SetDay(null);
        $value1= $obj_peso->calcular_politica($id_politica);

        $id_user= $array_register['id_usuario'];
        $item_reg= $array_register['signal'];

        $responsable= null;
        $observacion= null;

        if ($id_user && $item_reg == 'POL') {
            $observacion= textparse($array_register['observacion'], true);

            $email_user= $obj_user->GetEmail($id_user);
            $responsable= $email_user['nombre'];
            if (!is_null($email_user['cargo']))
                $responsable.= ', '.textparse($email_user['cargo'], true);
            $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
        }

        $if_titulo= boolean($row_pol['titulo']);
        $if_inner= boolean($row_pol['if_inner']);
        ?>

        <input type="hidden" id="if_titulo_pol_<?=$k_pol?>" value=<?=$if_titulo?> />
        <input type="hidden" id="if_inner_pol_<?=$k_pol?>" value=<?=$if_inner?> />

        <input type="hidden" id="id_pol_<?=$k_pol?>" value="<?=$id_politica?>" />
        <input type="hidden" id="descripcion_pol_<?=$k_pol?>" value="<?=$nombre?>" />
        <input type="hidden" id="registro_pol_<?=$k_pol?>" value="<?=$responsable?>" />
        <input type="hidden" id="observacion_pol_<?=$k_pol?>" value="<?=$observacion?>" />

        <input type="hidden" id="page_politica_<?=$k_pol?>" name="page_politica_<?=$id_politica?>" value="<?=$id_politica?>" />

        <li id="pol_li_<?=$k_pol?>">
            <div class="ul_pol" onmouseover="this.className='ul_pol rover'" onmouseout="this.className='ul_pol'">

                <div class="div_inner_ul_li" onclick="refresh_pol('pol_ul_<?=$k_pol?>')">
                    <div class="alarm-block">
                        <i id="img_pol_li_<?=$k_pol?>" class="fa fa-search-plus" title="expandir"></i>

                        <div class="alarm-block">
                        <?php
                        $obj_signal->get_alarm($value2);
                        $obj_signal->get_flecha($value2, $value1);
                        ?>
                        </div>
                    </div>

                    <?php $nombre= get_short_label($nombre); ?>
                </div>

                <div class="div_inner_ul_li" onclick="ShowContentItem('pol', <?=$k_pol?>, 0, 0);">
                    <span
                        class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>&nbsp;
                    <span class="flag">No.<?=$numero?>&nbsp;</span><?=$nombre?>
                    <?php
                    if (boolean($row_pol['if_inner'])) {
                        unset($obj_prs);
                        $obj_prs= new Tproceso($clink);
                        $obj_prs->Set($row_pol['id_proceso']);
                        $tipo_prs= $obj_prs->GetTipo();
                        $proceso= $obj_prs->GetNombre();
                        $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                    ?>

                    <br />
                    <img src="../img/<?=img_item_planning('pol')?>" title="<?=$item_planning_array['pol']?>" />
                    <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                        class="strong-title"><?=$proceso?></strong>
                    <strong class="strong-title">periodo: </strong><?="{$row_pol['inicio']}-{$row_pol['fin']}"?>
                    <?php } ?>
                </div>
            </div>

            <ul id="pol_ul_<?=$k_pol?>" style="display:none">
                <?php
                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $result_obj= $obj_peso->listar_objetivos_ref_politica($id_politica);
                $_cant_pol= $result_obj ? $clink->num_rows($result_obj) : 0;
                ?>
                <input type="hidden" id="_cant_pol_ul_<?=$k_pol?>" value="<?=$_cant_pol?>" />

                <?php
                _tree_objetivo($result_obj, $k_obj, $k_ind, $k_indi, 0, $k_pol, false, true);
                ?>
            </ul>
        </li>
        <?php
    }
}
?>