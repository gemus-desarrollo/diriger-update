<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/proceso.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

$terminado= !empty($_GET['terminado']) ? $_GET['terminado'] : 0;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
} else
    $obj= new Tproyecto($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : $obj->GetIdPrograma();
if (empty($id_programa)) 
    $id_programa= 0;

$time= new TTime();
$year= $time->GetYear();
$month= $time->GetMonth();
$lastday= $time->longmonth();

$inicio= $year - 5;
$fin= $year + 5;

$obj_user= new Tusuario($clink);

$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : "1/".$month."/".$year;
$fecha_final= !empty($_GET['fecha_final']) ? urldecode($_GET['fecha_final']) : $lastday."/".$month."/".$year;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= date("Y", strtotime(date2odbc($fecha_inicio)));

$url_page= "../form/lproyecto.php?signal=$signal&action=$action&menu=proyecto&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action&id_programa=$id_programa";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE PROYECTOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>
    
    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript" type="text/javascript">
    function refreshp() {
        var year = escape($('#year').val());
        var month = escape($('#month').val());
        var id_programa = $('#programa').val();
        var id_proceso = $('#proceso').val();

        var url = 'lproyecto.php?version=&action=<?=$action?>&&id_proceso=' + id_proceso;
        url += '&year=' + year + '&month=' + month + '&id_programa=' + id_programa;

        self.location = url;
    }

    function imprimir() {
        var year = escape($('#year').val());
        var month = escape($('#month').val());
        var id_programa = $('#programa').val();
        var id_proceso = $('#proceso').val();

        var url = '../print/lproyecto.php?version=&id_proceso=' + id_proceso;
        url += '&year=' + year + '&month=' + month + '&id_programa=' + id_programa;

        show_imprimir(url, "IMPRIMIENDO RELACIÓN DE PROYECTOS",
                        "width=850,height=500,toolbar=no,location=no, scrollbars=yes");
    }

    function displayWindow() {
        var w, h, l, t;
        l = screen.width / 4;
        t = 40;

        var title = "DEFINIR ESTADO DE EJECUCIÓN DEL PROYECTO";
        w = 550;
        h = 245;

        $('#div-ajax-panel').style.display = 'block';
        displayFloatingDiv('div-ajax-panel', title, w, h, l, t);
    }

    function mostrar(id_proyecto) {
        displayWindow();
        var url = 'ajax/fproyecto_update.ajax.php?id_proyecto=' + id_proyecto + '&action=<?=$action?>';

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);
    }

    function ejecutar() {
        var url = '../php/proyecto.interface.php?';
        var metodo = 'POST';
        var capa = 'div-ajax-panel';
        var valores = $("#fproyecto").serialize();
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
    }

    function add() {
        self.location.href = 'fproyecto.php?version=&action=add&signal=fproyecto';
    }

    function edit(id, action) {
        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/proyecto.interface.php?id=' + id;
        document.forms[0].submit();
    }

    function eliminar(id, action) {
        var text;
        var ifDelete_task = false;

        function this_2() {
            ifDelete_task = ifDelete_task ? 1 : 0;

            parent.app_menu_functions = false;
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/proyecto.interface.php?ifDelete_task=' + ifDelete_task + '&id=' + id;
            document.forms[0].submit();
        }

        function this_1() {
            var msg = ifDelete_task ? "ELIMINARAN" : "PERDERÁN";
            text = "El elemento proyecto será eliminado y con el se " + msg +
                " todas las referencias al proyecto de las ";
            text += "tareas asociadas al mismo. Desea continuar?";

            confirm(text, function(ok) {
                if (!ok)
                    return false;
                else
                    this_2();
            });

            return true;
        }

        text = "Desea todas las tareas pertenecientes al proyecto sean eliminadas?";
        confirm(text, function(ok) {
            if (!ok) {
                ifDelete_task = false;
                if (!this_1())
                    return;
            } else {
                ifDelete_task = true;
                if (!this_1())
                    return;
            }
        });
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        $('#id_proceso').val($('#proceso').val());
        refreshp();
    }

    function _dropdown_prog(id) {
        $('#programa').val(id);
        $('#id_programa').val($('#programa').val());
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

    <!-- Los proceso en los que tiene acceso el usuario ------------------------------------------------------->
    <?php
        $acc = $_SESSION['acc_planproject'];

        unset($obj_prs);
        $obj_prs = new Tproceso($clink);

        $obj_prs->SetIdResponsable(null);
        $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
        $obj_prs->SetConectado(null);
        $obj_prs->SetTipo(null);

        $corte_prs = _TIPO_ARC;

        if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
            $obj_prs->SetIdUsuario(null);
            $array_procesos = $obj_prs->listar_in_order('eq_desc', true, $corte_prs, false);
        } else {
            $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
            $array_procesos = $obj_prs->get_procesos_by_user('eq_desc', $corte_prs, false, null, $exclude_prs);
        }

        $obj_prs->SetIdUsuario(null);
        $j = 0;

        if ($acc == _ACCESO_ALTA) {
            if (!array_key_exists($_SESSION['id_entity'], $array_procesos)) {
                $array = array('id' => $_SESSION['id_entity'], 'id_code' => $_SESSION['id_entity_code'], 
                            'nombre' => $_SESSION['entity_nombre'], 'tipo' => $_SESSION['entity_tipo'], 
                            'id_responsable' => $_SESSION['entity_id_responsable'], 'conectado' => $_SESSION['entity_conectado'], 
                            'id_proceso' => $_SESSION['superior_entity_id']);

                $_array[$_SESSION['id_entity']]= $array;
                $array_procesos= $_array + $array_procesos;
            }
        }

        if (!array_key_exists($id_proceso, (array) $array_procesos))
            $id_proceso = null;

        unset($obj_prs);
        $obj_prs = new Tproceso($clink);
        $array_chief_procesos = $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

        $if_jefe= false;
        if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
            $if_jefe= true;
        if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
            $if_jefe= true;
        if ($acc >= 1 && $_SESSION['usuario_proceso_id'] == $id_proceso)
            $if_jefe= true;
        ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div> 
                <a href="#" class="navd-header">
                    PROYECTOS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navd-collapse">
                        <?php if ($if_jefe) { ?>
                        <li class="nav-item">
                            <a href="#" class="" onclick="add()" title="nuevo proyecto">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarUnidades" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-industry"></i>Unidades Organizativas<span class="caret"></span>
                                <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
                            </a>

                            <ul class="dropdown-menu" id="navbarUnidades">
                                <li class="nav-item <?php if (empty($id_proceso)) echo "active" ?>" onclick="_dropdown_prs(0)">
                                    <a href="#" title="Todos los procesos">
                                        Todos ...
                                    </a>
                                </li>

                                <?php
                                foreach ($array_procesos as $array) {
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
                                ?>
                            </ul>
                        </li>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarProgramas" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-product-hunt"></i>Programas<span class="caret"></span>
                                <input type="hidden" id="programa" name="programa" value="<?=$id_programa?>" />
                            </a>

                            <ul class="dropdown-menu" id="navbarProgramas">
                                <li class="<?php if (empty($id_programa)) echo "active" ?>" onclick="_dropdown_prog(0)">
                                    <a href="#" title="Todos los programas">
                                        Todos ...
                                    </a>
                                </li>

                                <?php
                                $obj_prog= new Tprograma($clink);
                                $obj_prog->SetYear($year);
                                $obj_prog->SetIdProceso($id_proceso);
                                $result_prog= $obj_prog->listar();

                                while ($row= $clink->fetch_array($result_prog)) {
                                ?>
                                <li class="nav-item">
                                    <a href="#" class="<?php if ($id_programa == $row['id']) echo "active" ?>"
                                        onclick="_dropdown_prog(<?=$row['id']?>)" title="Todos los programas">
                                        <?=$row['nombre']?>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>

                        <?php
                        $use_select_month= true;
                        $use_select_month_all= true;
                        $use_select_year= true;
                        require "inc/_dropdown_date.inc.php";
                        ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>
                                Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/02_usuarios.htm#02_4.1')">
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
                <?=$meses_array[(int)$month]?>, <?=$year?>
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
            <div class="row">
                <label class="badge badge-danger">
                    <?php
                    if (isset($obj_prs)) unset($obj_prs);
                    $obj_prs = new Tproceso($clink);
                    if (!empty($id_proceso)) {
                        $obj_prs->Set($id_proceso);
                    }
                    ?>
                    <?php if ($obj_prs->GetConectado() && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                        class="fa fa-wifi"></i><?php } ?>
                    <?=$obj_prs->GetNombre()?>, <?=$Ttipo_proceso_array[$obj_prs->GetTipo()]?>
                </label>
            </div>
        </li>
        
        <?php if (!empty($id_programa)) { ?>
        <li class="col-auto">
            <label class="badge badge-warning">
                <?php
                if (isset($obj_prog))
                    unset($obj_prog);
                $obj_prog = new Tprograma($clink);
                $obj_prog->Set($id_programa);
                echo $obj_prog->GetNombre(); 
                ?>
            </label>
        </li>
        <?php } ?>
    </ul>
</div>


    <form action='javascript:' method=post class="intable">
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="proyecto" />
        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
        <input type="hidden" name="id_programa" id="id_programa" value="<?=$id_programa?>" />

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-striped" data-toggle="table"  data-pagination="true"
                data-search="true" data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th></th>
                        <th>PROYECTO</th>
                        <th>PROGRAMA</th>
                        <th>RESPONSABLE</th>
                        <th>INICIO (plan)</th>
                        <th>FIN (plan)</th>
                        <th>INICIO (real)</th>
                        <th>FIN (real)</th>
                        <th>DESCRIPCIÓN</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $if_jefe= false;
                    if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
                        $if_jefe= true;
                    if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
                        $if_jefe= true;
                    if ($acc >= 1 && $_SESSION['usuario_proceso_id'] == $id_proceso)
                        $if_jefe= true;

                    $i= 0;

                    $obj->SetIdPrograma($id_programa);
                    $obj->SetIdProceso($id_proceso);
                    $obj->SetFechaInicioPlan(date2odbc($inicio));
                    $obj->SetFechaFinPlan(date2odbc($fin));
                    $result= $obj->listar();

                    while ($row= $clink->fetch_array($result)) {
                        if (!empty($terminado))
                            if (!empty($row['fecha_final_real']))
                                continue;
                    ?>

                    <tr>
                        <td>
                            <?=++$i?>
                        </td>
                        <td>
                            <?php if ($action != 'list' && ($if_jefe || $row['id_responsable'] == $_SESSION['id_usuario'])) { ?>
                            <a class="btn btn-warning btn-sm" href="#"
                                onclick="edit(<?= $row['_id'] ?>,'<?= $action ?>')">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-info btn-sm" href="#" onclick="mostrar(<?= $row['_id'] ?>)">
                                <i class="fa fa-check" title="actualizar estado"></i>Estado
                            </a>

                            <a class="btn btn-danger btn-sm" href="#" onclick="eliminar(<?= $row['_id'] ?>)">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } ?>
                        </td>
                        <td>
                            <a name="<?= $row['_id'] ?>"></a>
                            <?= $row['_nombre'] ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($row['id_programa'])) {
                                $obj_prog->SetYear($year);
                                $obj_prog->Set($row['id_programa']);
                                echo $obj_prog->GetNombre();
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $email = $obj_user->GetEmail($row['id_responsable']);
                            echo $email['nombre']?>
                            <?=!empty($email['cargo']) ? textparse($email['cargo']) : null?>
                        </td>
                        <td>
                            <?php $fecha = odbc2date($row['fecha_inicio_plan']); ?>
                            <?= $fecha ?>
                        </td>
                        <td>
                            <?php $fecha = odbc2date($row['fecha_fin_plan']); ?>
                            <?= $fecha ?>
                        </td>
                        <td>
                            <?php
                            if (empty($row['fecha_inicio_real']))
                                $fecha = '&nbsp;';
                            else
                                $fecha = odbc2date($row['fecha_inicio_real']);
                            ?>

                            <?= $fecha ?>
                        </td>
                        <td>
                            <?php
                            if (empty($row['fecha_inicio_real']))
                                $fecha = '&nbsp;';
                            else
                                $fecha = odbc2date($row['fecha_fin_real']);

                            if (empty($fecha))
                                $fecha = '&nbsp;';
                            ?>

                            <?= $fecha ?>
                        </td>
                        <td>
                            <?= textparse($row['descripcion']) ?>
                        </td>

                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>

    <div id="div-ajax-panel" class="ajax-panel">

    </div>

</body>

</html>