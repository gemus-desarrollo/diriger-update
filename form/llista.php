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
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/lista.class.php";

require_once "../php/class/badger.class.php";

$signal= "lista";
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if ($action == 'add') 
    $action= 'edit';

if ($action == 'list' || $action == 'edit') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tlista($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$componente= !empty($_GET['componente']) ? $_GET['componente'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['usuario_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$month= $year < date('Y') ? 12 : date('m');
$day= $year < date('Y') ? 12 : date('d');

$inicio= $year -3;
$fin= $year + 3;

$result= $obj->listar();

$_id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$acc= !is_null($_SESSION['acc_planaudit']) ? $_SESSION['acc_planaudit'] : 0;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_planaudit();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdResponsable(null);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetConectado(null);
$obj_prs->SetTipo(null);

//    $corte_prs= $signal == 'anual_plan_audit' ? _TIPO_GRUPO : null;
if ($signal == 'anual_plan_audit') {
    $exclude_prs= null;
} else {
    $exclude_prs= array();
    $exclude_prs[_TIPO_ARC]= 1;
}

if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
    $obj_prs->SetIdUsuario(null);
    if ($acc == _ACCESO_ALTA && $_SESSION['nivel'] < _SUPERUSUARIO)
        $array_procesos= $obj_prs->listar_in_order('eq_desc', true,  $corte_prs, false);
    else
        $array_procesos= $obj_prs->get_procesos_down($_SESSION['id_entity'], $corte_prs, null, true);
} else {
    $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
    $array_procesos= $obj_prs->get_procesos_by_user('eq_desc', $corte_prs, false, null, $exclude_prs);
}

$obj_prs->SetIdUsuario(null);
$j= 0;

if ($acc == _ACCESO_ALTA) {
    if (!array_key_exists($_SESSION['id_entity'], $array_procesos)) {
        $obj_prs->Set($_SESSION['id_entity']);

        $array= array('id'=>$_SESSION['id_entity'], 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
            'tipo'=>$obj_prs->GetTipo(), 'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(),
            'id_proceso'=>$obj_prs->GetIdProceso_sup());

            $_array[$_SESSION['id_entity']]= $array;
            $array_procesos= array_merge_overwrite($array_procesos, $_array);
    }
}

if (!array_key_exists($id_proceso, (array)$array_procesos))
    $id_proceso= null;

unset($obj_prs);
$obj_prs= new Tproceso($clink);
$array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

$if_jefe= false;
if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
    $if_jefe= true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe= true;
if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['id_entity']))
    $if_jefe= true;
//      if ($acc == _ACCESO_MEDIA && ($id_proceso == $_SESSION['local_proceso_id'])) $if_jefe= true;

// asignat los permisos de aprobacion o modificacion  del plan
if ($if_jefe && ($year == $actual_year || ($year == ($actual_year - 1) && $actual_month <= 3))) {
    if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['usuario_proceso_id'] != $id_proceso)
        $permit_eval= true;
    $permit_change= true;
}

if ($if_jefe && $year >= $actual_year) {
    $permit_change= true;
}

if ($if_jefe && $year <= $actual_year)
    $permit_repro= true;

$url_page= "../form/llista.php?signal=$signal&action=$action&menu=lista&componente=$componente&year=$year";
$url_page.= "&exect=$action&id_proceso=$id_proceso";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE LISTAS DE CHEQUEO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <link rel="stylesheet" href="../css/menu.css">

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function refreshp() {
        var url = 'llista.php?action=<?=$action?>&year=' + $('#year').val();
        url += '&componente=' + $('#componente').val() + '&id_proceso=' + $('#proceso').val();
        self.location.href = url;
    }

    function mostrar_auditorias() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();
        var day = $('#day').val();

        var fecha_inicio = encodeURIComponent(year + '-01-01');
        var fecha_fin = encodeURIComponent(year + '-' + month + '-' + day);

        var url = '../form/ajax/fadd_auditorias.ajax.php?signal=lista&action=add' + '&id_proceso=' + id_proceso +
            '&year=' + year;
        url += '&fecha-inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin;

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        displayFloatingDiv('div-ajax-panel', "SELECCIONE LA AUDITORÍA O ACCIÓN DE CONTROL", 80, 40, 10, 15);
        FAjax(url, capa, valores, metodo, funct);
    }

    function mostrar() {
        var title = "CRITERIO DE CUMPLIMIENTO"
        displayFloatingDiv('div-ajax-panel-register', title, 60, 0, 10, 20);
    }

    function validar() {
        if (!Entrada($('#fecha').val())) {
            alert('Introduzca la fecha en la que emitio esta evaluación');
            $('#fecha').focus(focusin($('#fecha')));
            return false;
        }

        if (!parseInt($('#criterio').val()) == -1) {
            $('#criterio').focus(focusin($('#criterio')));
            alert('Debe definir un crterio o evaluacion sobre el cumplimiento del aspecto');
            return;
        }

        if (!Entrada($('#observacion').val())) {
            $('#observacion').focus(focusin($('#observacion')));
            alert('Introduzca sus observaciones sobre el cumplimiento del aspecto que esta evaluando.');
            return false;
        }

        return true;
    }

    function eliminar(id) {
        function _this() {
            parent.app_menu_functions = false;
            document.forms[0].exect.value = 'delete';
            document.forms[0].action = '../php/lista.interface.php?menu=lista&id=' + id;
            document.forms[0].submit();
        }

        var msg = "La lista de chequeo será eliminada, es una operación ireversible. Desea continuar?";

        confirm(msg, function(ok) {
            if (!ok) 
                return;
            else 
                _this()
        });
    }

    function editar(id) {
        var year = $('#year').val();
        var url = '../php/lista.interface.php?action=edit&menu=lista&id=' + id + '&year=' + year;
        url += '&signal=lista';

        parent.app_menu_functions = false;
        document.forms[0].exect.value = 'edit';
        document.forms[0].action = url;
        document.forms[0].submit();
    }

    function add() {
        var year = $('#year').val();
        var id_proceso = $('#proceso').val();

        var url = 'flista.php?action=add&signal=add&id_proceso=' + id_proceso + '&year=' + year;
        url += '&signal=lista';
        self.location.href = url;
    }

    function view_requisitos(id, action) {
        var year = $("#year").val();
        var id_proceso = $('#proceso').val();
        var url = 'llista_requisito.php?action=' + action + '&id_lista=' + id + '&year=' + year;
        url += '&id_proceso=' + id_proceso + '&signal=lista';

        self.location.href = url;
    }

    function view_estructura(id) {
        var year = $("#year").val();
        var id_proceso = $('#proceso').val();
        var action = $('#exect').val();

        var url = 'ltipo_lista.php?action=' + action + '&id_lista=' + id + '&year=' + year;
        url += '&id_proceso=' + id_proceso + '&id_lista=' + id + '&signal=lista';

        self.location.href = url;
    }

    function imprimir() {
        var year = $("#year").val();
        var id_proceso = $('#proceso').val();
        var url = '../print/llista.php?year=' + year + '&id_proceso=' + id_proceso;

        show_imprimir(url, "IMPRIMIENDO GUIA DE CONTROL INTERNO",
            "width=750,height=500,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }

    $(document).ready(function() {
        InitDragDrop();

        $('#div_fecha').datepicker({
            format: 'dd/mm/yyyy'
        });

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>
                <a href="#" class="navd-header">
                    LISTADO DE LISTAS DE CHEQUEO
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navd-collapse">
                        <?php if (($action == 'add' || $action == 'edit') && $permit_change) { ?>
                            <li class="nav-item d-none d-md-block">
                                <a href="#" class="" onclick="add()" title="nueva Lista de Chequeo">
                                    <i class="fa fa-plus"></i>Agregar
                                </a>
                            </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-cogs"></i>Opciones <span class="caret"></span>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <li class="nav-item">
                                    <a class="" title="Definir una Auditoría o Supervición como origen" href="#"
                                        onclick="mostrar_auditorias()">
                                        <i class="fa fa-fire"></i>Definir Acción de Control
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarUnidades" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-industry"></i>Unidades Organizativas<span class="caret"></span>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarUnidades">
                                <?php if (!is_null($array_procesos)) { ?>
                                    <?php
                                    if (!array_key_exists($id_proceso, (array)$array_procesos))
                                        $id_proceso= null;

                                    foreach ($array_procesos as  $array) {
                                        if (empty($array['id']))
                                            continue;
                                        if (empty($id_proceso))
                                            $id_proceso= $array['id'];

                                        if ((empty($acc) || $acc < 3) && ($_SESSION['local_proceso_id'] == $array['id'] || $array['tipo'] <= $_SESSION['local_proceso_tipo'])) {
                                            if (!array_key_exists($array['id'], $array_chief_procesos)) {
                                                if ($id_proceso == $array['id'])
                                                    $id_proceso= null;
                                                continue;
                                            }
                                        }

                                        require "../form/inc/_tablero_tabs_proceso.inc.php";
                                    }

                                    $_SESSION['id_proceso']= $id_proceso;
                                }

                                if (!empty($id_proceso)) {
                                    $obj_prs = new Tproceso($clink);
                                    $obj_prs->SetIdProceso($id_proceso);
                                    $obj_prs->Set();
                                    $nombre_prs = $obj_prs->GetNombre() . ', ' . $Ttipo_proceso_array[$obj_prs->GetTipo()];
                                    $conectado = $obj_prs->GetConectado();
                                    $tipo = $obj_prs->GetTipo();
                                }
                                ?>
                            </ul>
                        </li>

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
                                <a href="#" onclick="open_help_window('../help/manual.htm')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$year?>
                </label>
            </li>
            <li class="col-auto">
                <label class="badge badge-warning">
                    <?=!empty($componente) ? $Tambiente_control_array[$componente] : "Todos los componentes"?>
                </label>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nshow" class="badge badge-warning">0</div>
                </div>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultas:</label>
                    <div id="nhide" class="badge badge-warning">0</div>
                </div>
            </li>
            <li class="col-auto">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                            <i class="fa fa-wifi"></i>
                        <?php } ?>
                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>

    <div class="app-body container-fluid table foubar">
        <form action='javascript:' method=post>
            <input type="hidden" name="exect" id="exect" value='<?=$action?>' />
            <input type="hidden" name="menu" id="menu" value="lista" />
            <input type="hidden" name="signal" id="signal" value="lista" />

            <input type="hidden" name="day" id="day" value="<?= $day ?>" />
            <input type="hidden" name="month" id="month" value="<?= $month ?>" />

            <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
            <input type="hidden" id="componente" name="componente" value="<?=$componente?>" />

            <div class="alert alert-info">
                <div class="row">
                    <div class="col-2">
                        <strong>Accion de Control:</strong>
                    </div>
                    <div class="col-10 pull-left">
                        <?= textparse($auditoria)?>
                    </div>
                </div>
            </div>


            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <?php if ($action != 'list') { ?>
                        <th></th>
                        <?php } ?>
                        <th>TÍTULO</th>
                        <th>REQUISITOS</th>
                        <th>UNIDAD ORGANIZATIVA</th>
                        <th>PERIODO</th>
                        <th>RESULTADO</th>
                        <th>FECHA</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $obj= new Tlista($clink);
                    $obj->SetIdProceso(!empty($id_proceso) && $id_proceso > 0 ? $id_proceso : null);
                    $obj->SetYear($year);

                    $result= $obj->listar(true);

                    $i = 0;
                    $array_ids= array();
                    while ($row = $clink->fetch_array($result)) {
                        if ($array_ids[$row['_id']])
                            continue;
                        $array_ids[$row['_id']]= $row['_id'];
                    ?>
                    <tr>
                        <td>
                            <?=++$i?>
                        </td>

                        <?php if ($action != 'list') { ?>
                        <td>
                            <a class="btn btn-warning btn-sm" href="javascript:editar(<?= $row['id'] ?>);">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm" href="javascript:eliminar(<?= $row['id'] ?>);">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>

                            <a class="btn btn-success btn-sm" href="javascript:view_estructura(<?= $row['id'] ?>);">
                                <i class="fa fa-list-ol"></i>Estructura
                            </a>
                        </td>
                        <?php } ?>

                        <td>
                            <?= textparse($row['nombre']) ?>
                        </td>
                        <td>
                            <a class="btn btn-info btn-sm"
                                href="javascript:view_requisitos(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Requisitos
                            </a>
                        </td>
                        <td>
                            <?php
                            $obj_prs->Set($row['_id_proceso']);
                            $_in_building= ($row['_id_proceso'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($row['_id_proceso']) : true;

                            $conectado= $obj_prs->GetConectado();
                            $img_conectdo= ($conectado != _NO_LOCAL && ($row['_id_proceso'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src=\'"._SERVER_DIRIGER."img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;
                            $nombre= $obj_prs->GetNombre();
                            $tipo= $obj_prs->GetTipo();

                            $proceso= "$nombre, <span class='tooltip_em'>{$Ttipo_proceso_array[$tipo]}</span>";
                            $proceso.= "<br />{$Ttipo_conexion_array[$conectado]}";

                            echo $proceso;
                            ?>
                        </td>
                        <td>
                            <?="{$row['inicio']} - {$row['fin']}"?>
                        </td>

                        <td>

                        </td>
                        <td>

                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </form>
    </div>
    
    <div id="div-ajax-panel" class="ajax-panel">

    </div>

    <div id='div-ajax-panel-register' class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title ajax-title col-md-11 win-drag"></div>
                <div class="col-1">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel-register');">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id='div-ajax-register' class='panel-body'>

            <form class="form-horizontal" id="fregister" name="fregister" action="javascript:validar()" method="post">
                <div class="form-group row">
                    <label class="col-form-label col-sm-3 col-md-2 col-lg-1">
                        Fecha:
                    </label>
                    <div class=" col-4">
                        <div class='input-group date' id='div_fecha' data-date-language="es">
                            <input type='text' id="fecha" name="fecha" class="form-control" readonly="readonly" value="<?=$date?>" />
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-2 col-md-1 col-lg-1">
                        Criterio
                    </label>
                    <div class=" col-sm-10 col-md-11 col-lg-11">
                        <select name="criterio" id="criterio" class="form-control">
                            <?php
                            $i= 0;
                            foreach ($Tcriterio_array as $array) {
                                ++$i;
                            ?>
                            <?php if (is_null($array)) { ?>
                            <option value="-1"> ... </option>
                            <?php
                                continue;
                            }
                            ?>
                            <option value="<?= $array[1] ?>"><?= $array[2]?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-2 col-md-1 col-lg-1">
                        Evidencia
                    </label>
                    <div class=" col-sm-10 col-md-11 col-lg-11">
                        <textarea id="observacion" name="observacion" class="form-control" rows="4"></textarea>
                    </div>
                </div>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <button class="btn btn-warning" type="reset"
                        onclick="CloseWindow('div-ajax-panel-register');">Cancelar</button>
                </div>

                <div id="_submited" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                </div>
            </form>

        </div>
    </div>

</body>

</html>