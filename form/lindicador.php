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
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/unidad.class.php";

require_once "../php/class/code.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add')
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : 0;
$id_inductor= !empty($_GET['id_inductor']) ? $_GET['id_inductor'] : 0;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tindicador($clink);
}

$search_process= _NO_LOCAL;
require_once "../php/inc_escenario_init.php";

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$obj_um= new Tunidad($clink);

$url_page= "../form/lindicador.php?signal=$signal&action=$action&menu=indicador&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&exect=$action&id_perspectiva=$id_perspectiva&id_inductor=$id_inductor";

set_page($url_page);

$signal= 'indicador';
$restrict_prs= array(_TIPO_DIRECCION);
$nhide= 0;
$nshow= 0;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE INDICADORES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />
    <link rel="stylesheet" type="text/css" href="../css/custom.css">

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/windowcontent.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style>
    .signal {
        height: 30px;
        width: 30px;
        border-radius: 20%;
    }
    </style>
    <script language="javascript" type="text/javascript">
    parent.app_menu_functions = true;

    function refreshp(index) {
        var id_perspectiva = $('#perspectiva').val();
        var id_inductor = $('#inductor').val();
        var action = $('#exect').val();
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        if (index == 1) {
            id_perspectiva = 0;
            id_inductor = 0;
        }
        if (index == 2) {
            id_inductor = 0;
        }

        parent.app_menu_functions = false;

        var url = 'lindicador.php?version=&action=' + action + '&id_perspectiva=' +
            id_perspectiva;
        url += '&id_inductor=' + id_inductor + '&id_proceso=' + id_proceso + '&year=' + year;

        self.location = url;
    }

    function imprimir() {
        var url;

        var _url = '&id_perspectiva=' + $('#perspectiva').val() + '&id_inductor=' + $('#inductor').val();
        _url += '&year=' + $('#year').val() + '&id_proceso=' + $('#proceso').val() + '&year=' + $('#year').val();;

        function _this() {
            url += _url;
            show_imprimir(url, "IMPRIMIENDO LISTADO DE INDICADORES",
                "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
        }

        var text =
            'Desea imprimir los nomnbres y cargos de los responsables de la actualización y planificación de cada uno de los indicadores';
        confirm(text, function(ok) {
            if (ok) {
                url = '../print/lindicador.php?version=&action=user';
                _this();
            } else {
                url = '../print/lindicador.php?version=&action=detail';
                _this();
            }
        });
    }

    function add() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        self.location.href = 'findicador.php?action=add&signal=indicador&id_proceso=' + id_proceso + '&year=' + year;
    }

    function _delete(id) {
        var year = $('#year').val();
        var id_proceso = $('#proceso').val();

        var url = '?_item=indi&action=delete&signal=indicador&id=' + id + '&year=' + year;
        url += '&id_proceso=' + id_proceso;
        url = '../form/ajax/fdelete.ajax.php' + url;

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);

        displayFloatingDiv('div-ajax-panel', "ELIMINAR INDICADOR", 50, 0, 15, 25);
    }

    function ejecutar(form) {
        var signal = document.forms[form].signal.value;
        var id = document.forms[form].id.value;
        var action = document.forms[form].exect.value;

        var metodo = 'POST';
        var capa = 'div-ajax-panel';
        var valores = $("#" + form).serialize();
        var url = '../php/indicador.interface.php?id=' + id + '&action=' + action;
        var funct= '';
        
        $('#_submit').hide();
        $('#_submited').show();
        
        FAjax(url, capa, valores, metodo, funct);

        parent.app_menu_functions = false;
    }

    function showWindow(action, id) {
        $('#id_indicador').val(id);
        showOpenWindow('docs', action);
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp(0);
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp(0);
    }

    function _dropdown_persp(id) {
        $('#perspectiva').val(id);
        refreshp(2);
    }

    function _dropdown_objt(id) {
        $('#inductor').val(id);
        refreshp(0);
    }

    $(document).ready(function() {
        var text = "Existen indicadores ocultos, por que no estan defiidos en el año selecionado. ";
        text += "Para verlos selecione otros años.";
        InitDragDrop();

        if (parseInt($('#_nhide').val()) > 0)
            alert(text);

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        $obj_prs= new Tproceso($clink);
        $obj_prs->SetYear($year);

        if (!empty($id_proceso) && $id_proceso != -1) {
            $obj_prs->SetIdProceso($id_proceso);
            $obj_prs->Set();
            $id_proceso_code= $obj_prs->get_id_proceso_code();
            $id_proceso_sup= $obj_prs->GetIdProceso_sup();
            $conectado= $obj_prs->GetConectado();
            $type= $obj_prs->GetTipo();

            $nombre_prs_title= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
        }

        $edit= ($action == 'edit') ? true : false;
        if ($edit && ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _NO_LOCAL))
            $edit= false;
        if ($edit && ($_SESSION['nivel'] < _SUPERUSUARIO && $_SESSION['id_usuario'] != $obj_prs->GetIdResponsable()))
            $edit= false;
        if ($edit && (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL))
            $edit= true;

        $obj_prs->SetIdProceso($_SESSION['id_entity']);
        $obj_prs->SetTipo($_SESSION['entity_tipo']);
        $obj_prs->SetConectado(null);
        $obj_prs->SetIdUsuario(null);
        $obj_prs->SetIdResponsable(null);

        $result_prs= $obj_prs->listar_in_order('eq_asc_desc', true, _TIPO_DIRECCION);
        ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>               
                <a href="#" class="navd-header">
                    INDICADORES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if (($action == 'edit' || $action == 'add') && $edit) { ?>
                        <li class="nav-item">
                            <a href="#" class="" onclick="add()" title="nuevo indicador">
                                <i class="fa fa-plus"></i>
                                Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-filter"></i>Filtrado<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <li class="dropdown-submenu">
                                    <a class="tooltip-viewport-left" title="" href="#">
                                        <i class="fa fa-cubes"></i>Perspectiva
                                    </a>
                                    <input type="hidden" id="perspectiva" name="perspectiva" value="<?=$id_perspectiva?>" />

                                    <ul class="navd-dropdown-menu" id="navbarOpciones">
                                        <li class="nav-item">
                                            <a href="#"
                                                class="tooltip-viewport-left <?php if ($id_proceso == 0) echo "active" ?>"
                                                onclick="_dropdown_persp(0)" title="Todos">
                                                Todas ...
                                            </a>
                                        </li>

                                        <?php
                                        unset($obj_prs);
                                        $obj_prs= new Tproceso($clink);

                                        $persp_id_proceso= !empty($persp_id_proceso) ? $persp_id_proceso : $_SESSION['local_proceso_id'];
                                        $obj_prs->get_procesos_up_cascade($persp_id_proceso, null, null, true);

                                        reset($obj_prs->array_cascade_up);
                                        foreach ($obj_prs->array_cascade_up as $row_prs) {
                                            if ($row_prs['tipo'] < $_SESSION['entity_tipo'])
                                                continue;

                                            $nombre_prs= $row_prs['nombre'].',  '.$Ttipo_proceso_array[$row_prs['tipo']];

                                            if (isset($obj_persp)) unset($obj_persp);
                                            $obj_persp= new Tperspectiva($clink);
                                            $obj_persp->SetIdProceso($row_prs['id']);

                                            $obj_persp->SetYear($year_persp);
                                            $obj_persp->SetInicio($persp_inicio);
                                            $obj_persp->SetFin($persp_fin);

                                            $result_persp= $obj_persp->listar();

                                            while ($row_persp= $clink->fetch_array($result_persp)) {
                                            ?>
                                                <li style="background-color:#<?=$row_persp['color']?>">
                                                    <a href="#"
                                                        class="tooltip-viewport-left <?php if ($row_persp['_id'] == $id_perspectiva) echo "active"?>"
                                                        onclick="_dropdown_persp(<?=$row['_id']?>)" title="">
                                                        No.<?="{$row_persp['numero']}  {$row_persp['_nombre']} ({$row_persp['inicio']}-{$row_persp['fin']}  / {$nombre_prs}"?>
                                                    </a>
                                                </li>
                                        <?php } } ?>
                                    </ul>
                                </li>

                                <li class="navd-dropdown">
                                    <a class="dropdown-toggle" href="#navbarInductor" data-toggle="collapse" aria-expanded="false">
                                        <img src="../img/empresa.ico" class="icon" />Objetivo de Trabajo
                                    </a>
                                    <input type="hidden" id="inductor" name="inductor" value="<?=$id_inductor?>" />

                                    <ul class="navd-dropdown-menu" id="navbarInductor">
                                        <li class="nav-item">
                                            <a href="#" class="tooltip-viewport-left <?php if ($tipo == 0) echo "active" ?>"
                                                onclick="_dropdown_objt(0)" title="Todos">
                                                Todos ...
                                            </a>
                                        </li>

                                        <?php
                                        $obj_inductor= new Tinductor($clink);
                                        $obj_inductor->SetYear($year);
                                        if (!empty($id_perspectiva))
                                            $obj_inductor->SetIdPerspectiva($id_perspectiva);
                                        $with_null_perspectiva= !empty($id_perspectiva) ? _PERSPECTIVA_NOT_NULL : _PERSPECTIVA_ALL;

                                        $result_inductor= $obj_inductor->listar($with_null_perspectiva);

                                        while ($row= $clink->fetch_array($result_inductor)) {
                                            $obj_prs->Set($row['id_proceso']);
                                            $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
                                            ?>

                                        <li class="nav-item">
                                            <a href="#"
                                                class="tooltip-viewport-left <?php if ($row['_id'] == $id_inductor) echo "active"; ?>"
                                                onclick="_dropdown_objt(<?=$row['_id']?>)">
                                                No.<?="{$row['_numero']} {$row['_nombre']}, {$row['inicio']}-{$row['fin']}, {$nombre_prs}"?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>

                            </ul>
                        </li>

                        <?php
                        $top_list_option= "seleccione........";
                        $id_list_prs= null;
                        $order_list_prs= 'eq_asc_desc';
                        $reject_connected= true;
                        $id_select_prs= $id_proceso;
                        $in_building= false;

                        $restrict_prs= array(_TIPO_DIRECCION, _TIPO_ARC, _TIPO_GRUPO, _TIPO_DEPARTAMENTO);
                        require "inc/_dropdown_prs.inc.php";
                        ?>

                        <?php
                        $use_select_year= true;
                        $use_select_month= false;
                        require "inc/_dropdown_date.inc.php";
                        ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/11_indicadores.htm#11_13.2')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>    
        </nav>
    </div>

    <div id="navbar-third" class="row app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$dayNames[$iday]?> <?=$day?>, <?=$meses_array[(int)$month]?> <?=$year?>
                </label>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nshow" class="badge badge-warning"></div>
                </div>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultos:</label>
                    <div id="nhide" class="badge badge-warning"></div>
                </div>
            </li>

            <li class="col-auto">
                <label class="badge badge-danger">
                    <?php if (!empty($id_proceso) && $id_proceso != -1) {
                        $_connect= ($conectado != _LAN && $id_proceso != $_SESSION['local_proceso_id']) ? _NO_LOCAL : _LOCAL;
                    ?>
                    <?php if ($_connect) { ?><i class="fa fa-wifi"></i><?php } ?>
                    <?= $nombre_prs_title ?>
                    <?php } ?>
                </label>
            </li>
        </ul>
    </div>

    <form action='javascript:' method=post class="intable">
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="indicador" />

        <input type="hidden" id="user_show_reject" value="<?=$user_show_reject?>" />

        <input type="hidden" id="_nhide" value="0" />

        <input type="hidden" id="id" name="id" value="0" />
        <input type="hidden" id="id_evento" name="id_evento" value="0" />
        <input type="hidden" id="id_auditoria" name="id_auditoria" value="0" />
        <input type="hidden" id="id_proyecto" name="id_proyecto" value="0" />
        <input type="hidden" id="ifmeeting" name="ifmeeting" value="0" />
        <input type="hidden" id="id_indicador" name="id_indicador" value="0" />

        <input type="hidden" name="month" id="month" value="0" />

        <?php  reset($result_prs); ?>
        <?php foreach ($result_prs as $row) { ?>
        <input type="hidden" id="proceso_code_<?=$row['id']?>" name="proceso_code_<?=$row['id']?>"
            value="<?=$row['id_code'] ?>" />
        <?php } ?>

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true" data-row-style="rowStyle">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col"></th>
                        <th scope="col">INDICADOR</th>
                        <th scope="col">CÁLCULO</th>
                        <th scope="col">DESCRIPCIÓN</th>
                        <th scope="col">PERIODICIDAD</th>
                        <th scope="col">CARGA</th>
                        <th scope="col">ESCALA (Real/Plan)</th>
                        <th scope="col">PROYECTO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $obj_indi = new Tindicador($clink);

                        $id_proceso= (!empty($id_proceso) && $id_proceso != -1) ? $id_proceso : null;
                        $obj_indi->SetIdProceso($id_proceso);
                        $obj_indi->SetYear($year);

                        if (!empty($id_perspectiva))
                            $obj_indi->SetIdPerspectiva($id_perspectiva);
                        if (!empty($id_inductor))
                            $obj_indi->SetIdInductor($id_inductor);

                        if (empty($id_perspectiva)) {
                            $perspectiva= null;
                            $color= null;

                            $result= $obj_indi->listar(null, _PERSPECTIVA_NULL);
                            write_table($result);
                        }

                        $i = 0;
                        unset($obj_persp);
                        $obj_persp = new Tperspectiva($clink);
                        // $obj_persp->SetYear($year);
                        $result_persp = $obj_persp->listar();

                        while ($row_persp = $clink->fetch_array($result_persp)) {
                            if (!empty($id_perspectiva)) {
                                if ($id_perspectiva != $row_persp['_id'])
                                    continue;
                            }
                            ++$i;

                            $color= null;
                            $perspectiva= null;

                            if ($row['inicio'] >= $year && $year <= $row['fin']) {
                                $color = "#{$row_persp['color']}";
                                $perspectiva = "{$row_persp['_nombre']} {$row_persp['inicio']}-{$row_persp['fin']}";
                            }

                            $obj_indi->SetIdPerspectiva($row_persp['_id']);
                            $obj_indi->SetIdInductor($id_inductor);
                            $obj_indi->SetIdProceso($id_proceso);
                            $obj_indi->SetYear($year);

                            $result = $obj_indi->listar(null, _PERSPECTIVA_NOT_NULL);
                            write_table($result);
                        }
                        ?>

                </tbody>
            </table>
        </div>
    </form>

    <script type="text/javascript" language="JavaScript">
    document.getElementById('nshow').innerHTML = '<?=$nshow?>';
    document.getElementById('nhide').innerHTML = '<?=$nhide?>';
    document.getElementById('_nhide').value = <?=empty($nhide) ? 0 : $nhide?>;
    </script>

    <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

    </div>

    <div id="bit" class="loggedout-follow-normal  d-none d-lg-block ">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>

        <div id="bitsubscribe">
            <div class="row">
                <div class="col-md-4">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/00cecm.ico" alt="CECM" />
                            CECM
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/01oac.ico" alt="OACE" />
                            OACE
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/07team.ico" alt="Grupo / Brigada" />

                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/02osde.ico" alt="OSDE" />
                            OSDE
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/03gae.ico" alt="GAE" />
                            GAE
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/04firm.ico" alt="Empresa" />
                            Empresa
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/05ueb.ico" alt="UEB" />
                            UEB
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/06arc.ico" alt="ARC" />
                            Area de Regulación y Control
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/07team.ico" alt="Grupo / Brigada" />
                            Grupo o Brigada
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/08office.ico" alt="departamento" />
                            Departamento
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/09process.ico" alt="Proceso Interno" />
                            Proceso Interno
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/10arc.ico" alt="Area de Resutados de Resultados" />
                            Area de Resultados Claves
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/calculator-self.ico" alt="indicador acumulativo" />
                            Indicador Acumulativo
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/calculator-add.ico" alt="calculado por el sistema" />
                            Valor calculado por el sistema
                        </li>
                        <li class="list-group-item item">
                            <img class="img-rounded" src="../img/transmit.ico" alt="sincronizacion" />
                            Origen de datos remoto
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--bitsubscribe -->
    </div>
    <!--bit-->

</body>

</html>

<?php
function write_table($result) {
    global $clink;
    global $edit;
    global $obj_prs;
    global $id_proceso;
    global $Ttipo_proceso_array;
    global $obj_um;
    global $color;
    global $perspectiva;
    global $obj_indi;
    global $year;
    global $periodo_inv;
    global $action;
    global $i;

    global $nshow;
    global $nhide;

    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        if ($array_ids[$row['_id']])
            continue;
        $array_ids[$row['_id']]= 1;

        if ($row['id_proceso'] != $id_proceso)
            continue;
        if ($row['id_proceso'] != $_SESSION['id_entity']) {
            if (!$obj_indi->test_if_in_proceso($_SESSION['id_entity'], $row['_id']))
                continue;
        }

        $obj_indi->SetIdProceso(null);
        $row_c= $obj_indi->get_criterio($year, $row['_id']);

        if ($year >= $row['_inicio'] && $year <= $row['_fin'])
            ++$nshow;
        else {
            ++$nhide;
            continue;
        }

        $obj_prs->Set($row['_id_proceso']);
        $conectado = $obj_prs->GetConectado();
        $tipo_prs = $obj_prs->GetTipo();

        $_conectado = ($conectado != _NO_LOCAL && $row['_id_proceso'] != $_SESSION['id_entity']) ? 1 : 0;
        $nombre_prs = $obj_prs->GetNombre() . ',  ' . $Ttipo_proceso_array[$obj_prs->GetTipo()];
        ?>

        <tr>
            <td>
                <a name="<?=$row['_id']?>"></a>
                <div class="col-12">
                    <div class="row">
                        <label class="col-6">
                            <?= !empty($row['_numero']) ? $row['_numero'] : $nshow ?>
                        </label>
                        <?php if ($perspectiva) { ?>
                        <div class="signal col-5 col-offset-1" title="<?=$perspectiva?>" style="background: <?= $color ?>">
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </td>
            <td>
                <?php if ($edit && $row['_id_proceso'] == $_SESSION['id_entity']) { ?>
                <a class="btn btn-warning btn-sm" href="#" onclick="enviar_indicador(<?=$row['_id']?>,'<?=$action?>')">
                    <i class="fa fa-edit"></i>Editar
                </a>

                <a class="btn btn-danger btn-sm" href="#" onclick="_delete(<?=$row['_id']?>)">
                    <i class="fa fa-trash"></i>Eliminar
                </a>
                <?php } ?>

                <a class="btn btn-info btn-sm" onclick="showWindow('<?=$edit?>', <?=$row['_id']?>)">
                    <i class="fa fa-file-text"></i>Documentos
                </a>
            </td>

            <td>
                <?php
                $unidad= $obj_um->GetUnidadById($row['id_unidad']);
                $unidad = preg_replace("#<p>(.*)<\/p>#", '$1', $unidad);
                ?>
                <?="{$row['_nombre']} ($unidad) "?>
                <br style="margin-bottom:8px" />

                <?php if (boolean($row['cumulative'])) { ?>
                <img class="img-rounded" src="../img/calculator-self.ico" title="Indicador acumulativo" />
                <?php } ?>

                <?php if (boolean($row['formulated'])) { ?>
                <img class="img-rounded" src="../img/calculator-add.ico" title="Calculado por el sistema" />
                <?php } ?>

                <img class="img-rounded" src="../img/<?= img_process($tipo_prs) ?>" 
                    title="Origen de datos: <?= $nombre_prs ?>" />

                <?php if ($_conectado) { ?>
                <img class="img-rounded" src="../img/transmit.ico" title="los datos son de origen remoto" />
                <?php } ?>
            </td>

            <td>
                <?=$obj_indi->replace_formulate($row['calculo'])?>
            </td>
            <td>
                Periodo del Indicador: <?= "{$row['_inicio']} - {$row['_fin']}"?>
                <p>
                    Origen de datos: <?=$nombre_prs?>
                </p>
                <p>
                    <?= textparse($row['_descripcion'])?>
                </p>
            </td>
            <td>
                <?=$periodo_inv[$row['periodicidad']] ?>
            </td>
            <td>
                <?=$periodo_inv[$row['carga']] ?>
            </td>
            <td>
                <div class="col-sm-12">
                    <?php if ($row_c['trend'] == 1) { ?>
                    <div class="row">
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-red"></div>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-orange"></div>
                            <?=$row_c['_orange']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-yellow"></div>
                            <?=$row_c['_yellow']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-green"></div>
                            <?=$row_c['_green']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-aqua"></div>
                            <?=$row_c['_aqua']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-blue"></div>
                            <?=$row_c['_blue']?>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($row_c['trend'] == 2) {?>
                    <div class="row">
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-blue"></div>
                            <?=(200-$row_c['_blue'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-aqua"></div>
                            <?=(200-$row_c['_aqua'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-green"></div>
                            <?=(200-$row_c['_green'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-yellow"></div>
                            <?=(200-$row_c['_yellow'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-orange"></div>
                            <?=(200-$row_c['_orange'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-red"></div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($row_c['trend'] == 3) {?>
                    <div class="row">
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-red"></div>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-orange"></div>
                            <?=$row_c['_orange']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-yellow"></div>
                            <?=$row_c['_yellow']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-green"></div>
                            <?=$row_c['_green']?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-yellow"></div>
                            <?=(200-$row_c['_yellow_cot'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-orange"></div>
                            <?=(200-$row_c['_orange_cot'])?>
                        </div>
                        <div class="col-xs-2 col-sm-2">
                            <div class="alarm-cicle small bg-red"></div>
                        </div>
                        <?php } ?>
                    </div>
            </td>

            <td>
                <?php
                if (!empty($row['id_proyecto'])) {
                    if (isset($obj_proj)) unset ($obj_proj);
                    $obj_proj= new Tproyecto($clink);
                    $obj_proj->Set($row['id_proyecto']);
                    $proyecto= $obj_proj->GetNombre();

                    $proj_inicio= date('m/Y', strtotime($obj_proj->GetFechaInicioPlan()));
                    $proj_fin= date('m/Y', strtotime($obj_proj->GetFechaFinPlan()));

                    echo "$proyecto <br/>  $proj_inicio - $proj_fin";
                }
                ?>
            </td>
        </tr>
<?php } } ?>