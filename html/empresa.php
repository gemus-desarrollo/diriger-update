<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/politica.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";
require_once "../php/class/cell.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

$signal= 'empresa';

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : 1;

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);

$url_page= "../html/empresa.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day";

set_page($url_page);
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

    <title>RESUMEN EMPRESARIAL</title>

    <?php require '../form/inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css?version=" />

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css?version=" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/windowcontent.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../css/custom.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/resumen.css?" />

    <script type="text/javascript" src="../js/ajax_core.js?version=" charset="utf-8"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/tablero.css?version=" />
    <script type="text/javascript" src="../js/tablero.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <link rel="stylesheet" href="../css/scheduler.css?version=" type="text/css" />

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    .ul-container {
        margin: 20px 10px 5px 30px;
        clear: left;
    }

    .div_inner_ul_li.div-alarm {
        float: left;
        clear: left;
        margin-right: 10px;
        max-width: 100px;
    }

    .div_inner_ul_li .div-inner {
        float: left;
        clear: left;
        margin-right: 10px;
        max-width: 800px;
    }

    .title-panel {
        border-radius: 4px;
        -moz-border-radius: 4px;
        box-shadow: #7BF 4px 4px;

        max-width: 600px;
        margin: 10px 5px 20px 20px;
        padding: 5px;

        float: left;
        clear: both;

        text-align: left;
        background: -moz-linear-gradient(left, #000066, #7BF);
        font-weight: bolder;
        color: white;
    }

    .title-head {
        max-width: 200px;
        text-align: center;

        background: -moz-linear-gradient(left, #953100, #FFB591);
        box-shadow: #FFB591 4px 4px;
    }
    </style>

    <script type="text/javascript">
    function refreshp(flag) {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();

        self.location.href = 'empresa.php?id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
    }

    function recompute() {
        var month = $('#month').val();
        var year = $('#year').val();
        var id_proceso = $('#proceso').val();

        var url = '../php/recompute.interface.php?id_proceso=' + id_proceso + '&month=' + month;
        url += '&year=' + year + '&item_recompute=empresa';

        self.location.href = url;

        parent.app_menu_functions = false;

        self.location.href = url;
    }

    function imprimir() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();

        url = '../print/empresa.php?id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
        prnpage = window.open(url, "IMPRIMIENDO RESUMEN DE LA UNIDAD",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }

    function _dropdown_tablero(id) {
        $('#tablero').val(id);
        refreshp();
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }

    function _dropdown_month(month) {
        $('#month').val(month);
        refreshp();
    }

    function _dropdown_day(day) {
        $('#day').val(day);
        refreshp();
    }

    $(document).ready(function() {
        InitDragDrop();

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->

    <div id="navbar-secondary" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <a href="#" class="navd-header">
                RESUMEN GENERAL
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">
                    <li>
                        <a href="#" class="" onclick="recompute()">
                            <img class="img-rounded ico" src="../img/recompute.ico" />
                            Recalcular
                        </a>
                    </li>

                    <?php
                    if ($signal != 'objetivo_sup' && $signal != 'empresa') {
                        $top_list_option= "seleccione........";
                        $id_list_prs= null;
                        $order_list_prs= 'eq_desc';
                        $reject_connected= false;
                        $id_select_prs= $id_proceso;
                        $in_building= false;

                        $restrict_prs= array(_TIPO_GRUPO, _TIPO_DEPARTAMENTO);
                        include "../form/inc/_dropdown_prs.inc.php";
                    }
                    ?>

                    <?php
                        $use_select_year= true;
                        $use_select_month= true;
                        require "../form/inc/_dropdown_date.inc.php";
                        ?>

                    <li class="d-none d-lg-block">
                        <a href="#" class="" onclick="imprimir()">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <a href="#" onclick="open_help_window('../help/11_indicadores.htm#11_16.2')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <?php
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $nombre= $obj_prs->GetNombre();
    $_connect= $obj_prs->GetConectado();
    $tipo= $obj_prs->GetTipo();

    $nombre.= ", ".$Ttipo_proceso_array[(int)$tipo];
    unset($obj_prs);
    ?>

    <div id="navbar-third" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">		
                <li class="col">
                    <label class="badge badge-success">
                        <?=(int)$day?> de <?=$meses_array[(int)$month]?>, <?=$year?>
                    </label>
                </li>
                <li class="col">
                    <div class="col-12">
                        <label class="badge badge-danger">
                            <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                                class="fa fa-wifi"></i><?php } ?>
                            <?= $nombre ?>
                        </label>
                    </div>
                </li>
            </ul>
        </nav>
    </div>


    <!-- app-body -->
    <div class="app-body table container-fluid twobar">
        <?php
        $obj_peso= new Tpeso_calculo($clink);
        $obj_peso->SetIdProceso($id_proceso);
        $obj_peso->set_id_proceso_code($id_proceso_code);

        $obj_peso->SetYear($year);
        $obj_peso->set_matrix();

        $obj_peso->SetMonth($month);
        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->compute_traze= true;
        $value2= $obj_peso->calcular_empresa();

        $obj_signal->get_month($_month, $_year);

        $obj_peso->SetYear($_year);
        $obj_peso->SetMonth($_month);

        $obj_peso->init_calcular();
        $obj_peso->SetIdProceso($id_proceso);
        $obj_peso->SetYearMonth($_year, $_month);
        $value1= $obj_peso->calcular_empresa();
        ?>                            

        <div class="card border-secondary m-3" style="max-width: 18rem;">
            <div class="card-header"><?=$nombre?></div>
            <div class="card-body">
                <div class="row">
                    <div class="alarm-block">
                    <?php 
                    $obj_signal->get_alarm($value2);
                    $obj_signal->get_flecha($value2, $value1);
                    ?> 
                    </div>

                    <?php if (!empty($value2)) { ?>
                        <div style="font-size:1.3em; font-weight:bold;">(<?= number_format($value2, 1,'.','').'%' ?>)</div>
                    <?php } ?>
                </div>

                <div style="text-align:right; float:right;">
                    <img class="img-thumbnail icon" src="../img/chart-bar.ico" title="graficar"
                        onclick="graficar('empresa',<?= $id_proceso ?>)" />
                </div>
            </div>
        </div>

        <!-- OBJETIVOS DE TRABAJO -->
        <div class="card border-light m-3" style="width: 98%;">
            <div class="card-header">OBJETIVOS DE TRABAJO DEL AÑO</div>

            <div class="card-body">           
                <?php
                $k_ind= 0;

                $obj= new Tinductor($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);

                $result_ind= $obj->listar(_PERSPECTIVA_ALL);

                while ($row= $clink->fetch_array($result_ind)) {
                    ++$k_ind;

                    $id_inductor= $row['_id'];

                    $nombre= $row['nombre'];
                    $descripcion= $if_top_inductor ? $row['descripcion'] : null;
                    $inicio= $row['inicio'];
                    $fin= $row['fin'];
                    $peso= $row['_peso'];

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_inductor($row['id_inductor'], $row['id_inductor_code']);

                    $array_register= $obj_peso->get_array_register();
                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_inductor($row['id_inductor'], $row['id_inductor_code']);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'IND') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.textparse($email_user['cargo']);
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }
                ?>

                <input type="hidden" id="id_ind_<?=$k_ind?>" value="<?=$id_inductor?>" />
                <input type="hidden" id="observacion_ind_<?=$k_ind?>" value="<?=$observacion?>" />
                <input type="hidden" id="registro_ind_<?=$k_ind?>" value="<?=$responsable?>" />
                <input type="hidden" id="descripcion_ind_<?=$k_ind?>" value="<?=$nombre?>" />

                <li id=ind_li_<?php echo $k_ind?>>
                    <div class="ul_ind ul-container" onmouseover="this.className='ul_ind rover-ind ul-container'"
                        onmouseout="this.className='ul_ind ul-container'">
                        <div class="div_inner_ul_li div-alarm">
                            <div class="alarm-block">
                            <?php
                            $obj_signal->get_alarm($value2);
                            $obj_signal->get_flecha($value2, $value1);
                            ?>
                            </div>

                            <?php
                            if (isset($obj_prs)) 
                                unset($obj_prs);
                            $obj_prs= new Tproceso($clink);

                            $obj_prs->Set($row['id_proceso']);
                            $tipo_prs= $obj_prs->GetTipo();
                            $proceso= $obj_prs->GetNombre();
                            $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                            $numero= !empty($row['numero']) ? $row['numero'] : $k_ind;
                            ?>
                        </div>

                        <div class="div_inner_ul_li div-inner" onclick="ShowContentItem('ind', <?=$k_ind?>, <?= $item_sup ? "'$item_sup'" : '0' ?>, <?= $id_item_sup ? $id_item_sup : '0'?>);">
                            <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>
                            <span class="flag">No.<?=$numero?></span>&nbsp;<?=$nombre?>

                            <br />
                            <img src="../img/<?= img_item_planning('ind')?>" title="<?=$item_planning_array['ind']?>" />
                            <img src="../img/<?= img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" />
                            <strong class="strong-title"><?=$proceso?></strong>
                            <strong class="strong-title">periodo: </strong><?php echo $row['inicio'].'-'.$row['fin'] ?>
                            <?php if (!is_null($peso)) { ?>
                                <strong class="strong-title">Ponderación: <span class="peso"><?=$Tpeso_inv_array[$peso]?></span></strong>
                            <?php } ?>
                        </div>
                    </div>
                </li>
                <?php }  ?>

            </div>
        </div>
        <!-- OBJETIVOS DE TRABAJO -->

        <!-- OBJETIVOS DE ESTRATEGICOS -->
        <div class="card border-light m-3" style="width: 98%;">
            <div class="card-header">OBJETIVOS ESTRATÉGICOS</div>
            <div class="card-body">
                <?php
                $obj= new Tobjetivo($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);
                $obj->SetIfControlInterno(false);

                $result_obj= $obj->listar();

                $k_obj= 0;
                while ($row_obj= $clink->fetch_array($result_obj)) {
                    ++$k_obj;

                    $id_objetivo= $row_obj['_id'];
                    $peso= $row_obj['_peso'];
                    $obj->Set($id_objetivo);
                    $nombre= $obj->GetNombre();

                    $obj_peso->SetIdProceso($id_proceso);
                    $obj_peso->set_id_proceso_code($id_proceso_code);
                    
                    $obj_peso->SetDay($day);
                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_objetivo($id_objetivo);
                    $array_register= $obj_peso->get_array_register();
            
                    $obj_signal->get_month($_month, $_year);
                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $obj_peso->SetDay(null);
                    $value1= $obj_peso->calcular_objetivo($id_objetivo);
            
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
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }
                    ?>               

                    <input type="hidden" id="id_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$id_objetivo?>" />
                    <input type="hidden" id="observacion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$observacion?>" />
                    <input type="hidden" id="registro_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$responsable?>" />
                    <input type="hidden" id="descripcion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$nombre?>" />

                    <li id="<?=$_item_obj?>_li_<?php echo $_k_obj?>">
                        <div class="ul_pol ul-container" onmouseover="this.className='ul_pol rover-obj ul-container'"
                            onmouseout="this.className='ul_pol ul-container'">
                            <div class="div_inner_ul_li div-alarm">
                                <div class="alarm-block">
                                <?php
                                $obj_signal->get_alarm($value2);
                                $obj_signal->get_flecha($value2, $value1);
                                ?>
                                </div>

                                <?php
                                $obj_prs->Set($row_obj['id_proceso']);
                                $tipo_prs= $obj_prs->GetTipo();
                                $proceso= $obj_prs->GetNombre();
                                $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                                $numero= !empty($row_obj['numero']) ? $row_obj['numero'] : $_k_obj;
                                ?>
                            </div>

                            <div class="div_inner_ul_li div-inner"
                                onclick="ShowContentItem('<?php echo $_item_obj ?>', <?=$_k_obj?>, <?=$_item_sup_obj?>, <?=$_k_obj_sup?>);">
                                <span
                                    class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>
                                <span class="flag">No.<?php echo $numero?> </span><?=$nombre?>

                                <br />
                                <img src="../img/<?=img_item_planning('obj')?>" title="<?=$item_planning_array['obj']?>" />
                                <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                                    class="strong-title"><?=$proceso?></strong>
                                <strong class="strong-title">periodo:
                                </strong><?php echo $row_obj['inicio'].'-'.$row_obj['fin'] ?>
                                <?php if (!is_null($peso)) { ?><strong class="strong-title">Ponderación: <span class="peso">
                                        <?=$Tpeso_inv_array[$peso]?></span></strong><?php } ?>
                                <strong class="strong-title">lineamientos:</strong>

                                <?php
                                $obj->SetIdProceso(null);
                                $array= $obj->get_politicas($row_obj['_id']);

                                $j= 0;
                                foreach ($array as $cell) {
                                    if ($j > 0) echo ", ";
                                    echo ' L'.$cell['numero'].' ';
                                    ++$j;
                                }
                                ?>
                            </div>
                        </div>
                    </li>  
                <?php } ?>              
            </div>
        </div>
        <!-- OBJETIVOS ESTRATEGICOS -->

        <!-- PERSPECTIVAS -->
        <div class="card border-light m-3" style="width: 98%;">
            <div class="card-header">PERSPECTIVAS (CUADRO DE MANDO INTEGRAL)</div>
            <div class="card-body">
                <?php
                $k_per= 0;
                $obj= new Tperspectiva($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);
                $result_persp= $obj->listar();

                while ($row_persp= $clink->fetch_array($result_persp)) {
                    $_prs= $array_procesos_entity[$row_persp['_id_proceso']];
                    $id_entity= !empty($_prs['id_entity']) ? $_prs['id_entity'] : $_prs['id'];
                    $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;  
                                        
                    ++$k_per;

                    $id_persp= $row_persp['_id'];
                    $peso= $row_persp['peso'];
                    $nombre= $row_persp['_nombre'];

                    $obj_peso->SetIdProceso($id_proceso);
                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->init_calcular();
                    $obj_peso->compute_traze= true;

                    $value2= $obj_peso->calcular_perspectiva($id_persp, $array_procesos_down_entity);
                    $array_register= $obj_peso->get_array_register();

                    $obj_signal->get_month($_month, $_year);
                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->SetYearMonth($_year, $_month);
                    $obj_peso->SetDay(null);
                    $obj_peso->init_calcular();
                    $value1= $obj_peso->calcular_perspectiva($id_persp, $array_procesos_down_entity);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'PER') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.textparse($email_user['cargo']);
                        $responsable.= '  <br /><strong>corte:</strong>'.odbc2date($array_register['reg_fecha']).'   <strong>registrado:</strong>'.odbc2time_ampm($array_register['cronos']);
                    }
                    ?>

                    <input type="hidden" id="id_per_<?=$k_per?>" value="<?=$id_persp?>" />
                    <input type="hidden" id="observacion_per_<?=$k_per?>" value="<?=$observacion?>" />
                    <input type="hidden" id="registro_per_<?=$k_per?>" value="<?=$responsable?>" />
                    <input type="hidden" id="descripcion_per_<?=$k_per?>" value="<?=$nombre?>" />

                    <li id="per_li_<?=$k_per?>">
                        <div class="ul_pol paneltableblock ul-container" style="background:#<?=$row_persp['color']?>">
                            <div class="div_inner_ul_li div-alarm">
                                <div class="alarm-block">
                                <?php
                                $obj_signal->get_alarm($value2);
                                $obj_signal->get_flecha($value2, $value1);
                                ?>
                                </div>
                                <?php
                                $nombre= get_short_label($nombre);
                                $tipo_prs= $row_persp['tipo'];
                                $proceso= $row_persp['proceso'];
                                $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                                ?>
                            </div>

                            <div class="div_inner_ul_li div-inner" onclick="ShowContentItem('per', <?=$k_per?>, 0, 0);">
                                <span
                                    class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>

                                <span class="flag">No.<?=$row_persp['numero']?></span>&nbsp;<?=$nombre?>

                                <br />
                                <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                                    class="strong-title"><?=$proceso?></strong>
                                <strong class="strong-title">periodo:
                                </strong><?php echo $row_persp['inicio'].'-'.$row_persp['fin'] ?>
                                <?php if (!is_null($peso)) { ?><strong class="strong-title">Ponderación: <span class="peso">
                                        <?=$Tpeso_inv_array[$peso]?></span></strong><?php } ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </div>
        </div>
        <!-- PERSPECTIVAS -->

        <!-- LINEAMIENTOS -->
        <div class="card border-light m-3" style="width: 98%;">
            <div class="card-header">LINEAMIENTOS</div>
            <div class="card-body">
                <?php
                $k_pol= 0;
                $obj= new Tpolitica($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);

                $result_pol= $obj->listar(1);

                while ($row_pol= $clink->fetch_array($result_pol)) {
                    $id_politica= $row_pol['_id'];

                    ++$k_pol;
                    $nombre= $row_pol['nombre'];
                    $numero= $row_pol['numero'];

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);
                    $obj_peso->SetDay($day);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_politica($id_politica, $row_pol['_id_code']);
                    $array_register= $obj_peso->get_array_register();
                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_politica($id_politica, $row_pol['_id_code']);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'POL') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.textparse($email_user['cargo']);
                        $responsable.= '  <br /><strong>corte:</strong>'.odbc2date($array_register['reg_fecha']).'   <strong>registrado:</strong>'.odbc2time_ampm($array_register['cronos']);
                    }

                    $if_titulo= setZero($row_pol['titulo']);
                    $if_inner= setZero(boolean($row_pol['if_inner']));
                    ?>

                    <input type="hidden" id="if_titulo_pol_<?=$k_pol?>" value=<?=$if_titulo?> />
                    <input type="hidden" id="if_inner_pol_<?=$k_pol?>" value=<?=$if_inner?> />

                    <input type="hidden" id="id_pol_<?=$k_pol?>" value="<?=$id_politica?>" />
                    <input type="hidden" id="descripcion_pol_<?=$k_pol?>" value="<?=$nombre?>" />
                    <input type="hidden" id="registro_pol_<?=$k_pol?>" value="<?=$responsable?>" />
                    <input type="hidden" id="observacion_pol_<?=$k_pol?>" value="<?=$observacion?>" />

                    <li id="pol_li_<?=$k_pol?>">
                        <div class="ul_pol ul-container" onmouseover="this.className='ul_pol rover ul-container'"
                            onmouseout="this.className='ul_pol ul-container'">
                            <div class="alarm-block">
                                <?php
                                $obj_signal->get_alarm($value2);
                                $obj_signal->get_flecha($value2, $value1);
                                ?>
                            </div>

                            <div class="div_inner_ul_li div-inner" onclick="ShowContentItem('pol', <?=$k_pol?>, 0, 0);">
                                <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>;
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
                                <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" />
                                <strong class="strong-title"><?=$proceso?></strong>
                                <strong class="strong-title">periodo: </strong><?= $row_pol['inicio'].'-'.$row_pol['fin'] ?>
                                <?php } ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>    
            </div>
        </div>
        <!-- LINEAMIENTOS -->


    </div> <!-- app-body -->

    <div id="bit" class="loggedout-follow-normal" style="width: 60%;">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>
        <div id="bitsubscribe">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src="../img/alarm-dark.ico">
                            Sobrecumplido al 110% o m&aacute;s
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-blue.ico">
                            Sobrecumplido al 105% o m&aacute;s, menor que el 110% de Sobrecumplimiento
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-green.ico">
                            Éxito. Estado de cumplimiento igual o mayor que 95% y menor que el 105% de
                            Sobrecumplimiento
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-yellow.ico">
                            Cumplimiento igual o mayor que 90% y menor que el 95%
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-orange.ico">
                            Estado de cumplimiento mayor o igual al 85% y menor que 90%
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-red.ico">
                            Fracaso. Cumplimiento menor 85%
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src="../img/arrow-green.ico">
                            Mejora referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-yellow.ico">
                            Sin Cambios referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-red.ico">
                            Empeora referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-blank.ico">
                            No hay datos en periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-blank.ico">
                            No existen datos
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-null.ico">
                            No hay valor del Plan o Criterio de Éxito
                        </li>
                    </ul>
                </div>
            </div>

            <label class="text">
                <sup class="font-size:1.2em!important">*</sup>Los valores que aparecen en esta leyenda son los
                que se utilizan por defecto. cada indicador puede tener sus valores de escala especificos.
            </label>

        </div> <!-- bitsubscribe -->
    </div> <!-- bit -->

</body>

</html>