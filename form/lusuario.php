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
require_once "../php/class/proceso.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;
if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else
    $obj= new Tusuario($clink);

$_id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

require_once "../php/inc_escenario_init.php";

$obj->SetYear($year);
$obj->update_all_id_proceso_jefe();

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$user_show_reject= !empty($_GET['user_show_reject']) ? $_GET['user_show_reject'] : 0;
$user_date_ref= $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($day, 2, "0", STR_PAD_LEFT);

$url_page= "../form/lusuario.php?signal=$signal&action=$action&menu=usuario&id_proceso=$_id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTA DE USUARIOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>
    
    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    .panel-multiselect {
        min-height: 300px;
        max-height: 350px;
    }
    .div-ajax-ldap {
        overflow: scroll;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function refreshp() {
        var action = $('#exect').val();
        var id_proceso = $('#proceso').val();
        var month = $('#month').val();
        var year = $('#year').val();
        var user_show_reject = $('#user_show_reject').val();

        var url = 'lusuario.php?version=&action=' + action + '&id_proceso=' + id_proceso;
        url += '&month=' + month + '&year=' + year + '&user_show_reject=' + user_show_reject;
        self.location.href = url;
    }

    function _mostrar() {
        if ($('#user_show_reject').val() == 1)
            $('#user_show_reject').val(0);
        else
            $('#user_show_reject').val(1);

        refreshp();
    }

    function enviar_user(id, action) {
        if (id == 1 && action == 'delete') {
            alert("No esta permitido eliminar al administrador del sistema.");
            return;
        }

        function _this() {
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/user.interface.php?id=' + id;
            document.forms[0].submit();
        }

        if (action == 'delete') {
            var msg = "A partir de esta fecha el usuario será eliminado y no se le podrá ser gestionado ";
            msg += "por las funcionalidades del sistema. Desea continuar?";
            confirm(msg, function(ok) {
                if (!ok)
                    return;
                else
                    _this();
            });
        } else
            _this();
    }

    function displayWindow(panel) {
        var w, h, l, t;

        if (panel != 'ldap') {
            w = 70;
            h = 0;
            l = 10;
            t = 5;
        } else {
            w = 60;
            h = 0;
            l = 10;
            t = 10;
        }

        var title;

        if (panel == 'tc')
            title = "ASIGNACIÓN DE ACCESO A TABLEROS DE CONTROL";
        if (panel == 'pr')
            title = "ASIGNACIÓN DE ACCESO A LOS PROYECTOS";
        if (panel == 'prs')
            title = "ASIGNACIÓN DE PARTICIPACIÓN Y ACCESO A PROCESOS";
        if (panel == 'su')
            title = "ASIGNACIÓN DE SUBORDINADOS";
        if (panel == 'dp')
            title = "ASIGNACIÓN DE A GRUPOS DE USUARIOS";
        if (panel == 'ldap')
            title = "ACTUALIZAR USUARIOS DESDE EL DIRECTORIO ACTIVO";

        displayModalDiv('div-ajax-panel', title, w, h, l, t);

        if (panel != 'ldap') {
            $('#div-ajax').addClass('div-ajax-ldap');
        }
    }

    function mostrar(id, tc) {
        var month = $('#month').val();
        var year = $('#year').val();

        var url = '../form/ajax/fusuario_' + tc + '.ajax.php?id_usuario=' + id + '&action=<?=$action?>&year=' + year +
            '&month=' + month;
        var capa = 'div-ajax';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        parent.app_menu_functions = true;

        FAjax(url, capa, valores, metodo, funct);
        displayWindow(tc);
    }

    function ejecutar(tc) {
        var url = '../php/user.interface.php?';
        var metodo = 'POST';
        var capa = 'div-ajax';
        var valores = $("#frm_" + tc).serialize();
        var funct= '';

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        FAjax(url, capa, valores, metodo, funct);
    }

    function ejecutar_grupo() {
        var url = '../php/grupo.interface.php?menu=user_grupo';
        var metodo = 'POST';
        var capa = 'div-ajax';
        var valores = $("#frm_grp").serialize();
        var funct= '';

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        FAjax(url, capa, valores, metodo, funct);
    }

    function update_ldap() {
        displayWindow("ldap");

        var url = '../ldap.php?execfromshell=0';
        var metodo = 'GET';
        var capa = 'div-ajax';
        var valores = '';
        var funct= '';
        
        parent.app_menu_functions = false;
        FAjax(url, capa, valores, metodo, funct);
    }

    function cerrar() {
        CloseWindow('div-ajax-panel');
        $('#div-ajax').removeClass('div-ajax-ldap');
    }
    </script>

    <script language="javascript">
    function add() {
        var id_proceso = $('#proceso').val();
        var url = 'fusuario.php?version=&action=add&signal=list&id_proceso=' + id_proceso;
        url += '&user_ldap=0';
        self.location.href = url;
    }

    function imprimir() {
        var id_proceso = $('#proceso').val();
        var user_show_reject = $('#user_show_reject').val();
        var year = $('#year').val();
        var month = $('#month').val();

        var url = '../print/lusuario.php?id_proceso=' + id_proceso + '&user_date_ref=<?= $user_date_ref?>' +
            '&user_show_reject=' + user_show_reject;
        url += '&year=' + year + '&month=' + month;

        show_imprimir(url, "IMPRIMIENDO RELACIÓN DE USUARIOS",
            "width=600,height=300,toolbar=no,location=no, scrollbars=yes");
    }

    function enviar_user(id, action) {
        function _this() {
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/user.interface.php?id=' + id;
            document.forms[0].submit();
        }

        if (action == 'delete') {
            var msg = "Esta seguro de querer eliminar este usuario?. A partir de este momento el usuario ";
            msg += "no sera accesible. Desea continuar?";
            confirm(msg, function(ok) {
                if (!ok)
                    return;
                else
                    _this();
            });
        } else
            _this();
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

    <?php
    $obj_prs= new Tproceso($clink);

    if (!empty($_id_proceso) && $_id_proceso != -1) {
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
        $conectado= $obj_prs->GetConectado();
        $tipo= $obj_prs->GetTipo();
    }

    if ($_id_proceso == -1)
        $nombre_prs= "Todos los usuarios del sistema...";

    $_connect= is_null($conectado) ? 1 : $conectado;

    if ($_id_proceso != $_SESSION['local_proceso_id'])
        $_connect= ($_connect != 1) ? 1 : 0;
    else
        $_connect= 0;

    if ($_id_proceso == -1)
        $_connect= 0;

    $use_select_proceso= true;
    $use_select_year= true;
    $use_select_month= true;
    $use_select_day= false;

    $reject_connected= false;
    $in_building= false;
    $break_exept_connected= _TIPO_DIRECCION;
    $id_select_prs= !empty($_id_proceso) ? $_id_proceso : 0;
    ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>              
                <a href="#" class="navd-header">
                    USUARIOS DEL SISTEMA
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo usuario">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-industry"></i>Opciones<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <?php if (!$user_show_reject) { ?>
                                <li class="nav-item">
                                    <a href="#" class="" title="mostrar los usuarios eliminados" onclick="_mostrar()">
                                        <i class="fa fa-eye"></i>Mostrar
                                    </a>
                                </li>
                                <?php } else { ?>
                                <li class="nav-item">
                                    <a href="#" class="" title="ocultar los usuarios eliminados" onclick="_mostrar()">
                                        <i class="fa fa-eye-slash"></i>Ocultar
                                    </a>
                                </li>
                                <?php } ?>

                                <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO && $config->ldap_login) { ?>
                                <li class="nav-item">
                                    <a href="#" class="" onclick="update_ldap()"
                                        title="Actualizar la lista de Usuarios a partir del Directorio Activo">
                                        <i class="fa fa-windows"></i>Sincronizar LDAP
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>

                        <?php if (!$user_show_reject) { ?>
                        <li class="d-none d-lg-block">
                            <a href="#" class="" title="mostrar los usuarios eliminados" onclick="_mostrar()">
                                <i class="fa fa-eye"></i>Mostrar
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="d-none d-lg-block">
                            <a href="#" class="" title="ocultar los usuarios eliminados" onclick="_mostrar()">
                                <i class="fa fa-eye-slash"></i>Ocultar
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO && $config->ldap_login) { ?>
                        <li class="d-none d-lg-block">
                            <a href="#" class="" onclick="update_ldap()"
                                title="Actualizar la lista de Usuarios a partir del Directorio Activo">
                                <i class="fa fa-windows"></i>Sincronizar LDAP
                            </a>
                        </li>
                        <?php } ?>

                        <?php
                        $show_dpto= true; 
                        $restrict_prs= array(_TIPO_DEPARTAMENTO, _TIPO_ARC, _TIPO_PROCESO_INTERNO);
                        require "inc/_dropdown_prs.inc.php"; 
                        ?>

                        <?php require "inc/_dropdown_date.inc.php"; ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
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
                    <div id="nshow" class="badge badge-warning"></div>                    
                </div>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultas:</label>
                    <div id="nhide" class="badge badge-warning"></div>                    
                </div>
            </li>
            <li class="col-auto">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>

    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="usuario" />

        <input type="hidden" id="user_show_reject" value="<?=$user_show_reject?>" />

        <?php 
        $clink->data_seek($result_prs);
        while ($row= $clink->fetch_array($result_prs)) { 
        ?>
        <input type="hidden" id="proceso_code_<?=$row['_id']?>" name="proceso_code_<?=$row['_id']?>"
            value="<?=$row['_id_code'] ?>" />
        <?php } ?>

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-hover table-striped" 
                data-toggle="table" 
                data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <?php 
                        $id_entity= $id_proceso > 0 ? $array_procesos_entity[$id_proceso]['id_entity'] : $_SESSION['id_entity'];
                        $action= 'list';
                        if ($_SESSION['nivel'] == _GLOBALUSUARIO 
                            || ($_SESSION['nivel'] >= _ADMINISTRADOR && ($_SESSION['id_entity'] == $id_entity || $_SESSION['id_entity'] == $id_proceso)))
                            $action= 'edit';

                        if ($action != 'list') { 
                        ?>
                        <th></th>
                        <?php } ?>
                        <th>NOMBRE Y APELLIDOS</th>
                        <th>CARGO</th>
                        <th>USUARIO</th>
                        <th>NIVEL</th>
                        <th>E-correo</th>
                        <th>C.I.</th>
                        <th>CONFIGURACIÓN</th>
                        <th>FIRMA</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    unset($obj_prs);
                    $obj_prs = new Tproceso($clink);
                    !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                    $cant_print_reject = 0;
                    $cant_show = 0;

                    if (empty($_id_proceso) || $_id_proceso == -1)
                        $_id_proceso= null;
                    $obj->SetIdProceso($_id_proceso);

                    $obj->set_user_date_ref(null, true);
                    $result = empty($_id_proceso) && $_SESSION['id_entity'] == $_SESSION['local_proceso_id'] ? $obj->listar_all(null, null, 1) : $obj->listar(null, null, _LOCAL, 1);
                    $cant = $obj->GetCantidad();

                    if ($cant > 0) {
                        $i = 0;
                        while ($row = $clink->fetch_array($result)) {
                            $prs= $array_procesos_entity[$row['id_proceso']];
                            $id_entity= $prs['id_entity'] ? $prs['id_entity'] : $row['id_proceso'];
                            
                            if ($id_proceso == -1 && (($_SESSION['nivel'] == _GLOBALUSUARIO 
                                                        && $_SESSION['local_proceso_id'] != $_SESSION['id_entity'] && $_SESSION['id_entity'] != $id_entity) 
                                || ($_SESSION['nivel'] != _GLOBALUSUARIO && $_SESSION['id_entity'] != $id_entity)))
                                continue;
                            if (!$user_show_reject && !empty($row['eliminado'])) {
                                ++$cant_print_reject;
                                continue;
                            }

                            if (empty($row['nombre']))
                                continue;
                            ++$cant_show;
                    ?>

                    <tr valign="top">
                        <td>
                            <?= ++$i ?>
                        </td>

                        <?php 
                        $id_entity= $id_proceso > 0 ? $array_procesos_entity[$id_proceso]['id_entity'] : $_SESSION['id_entity'];
                        $action= 'list';
                        if ($_SESSION['nivel'] == _GLOBALUSUARIO 
                            || ($_SESSION['nivel'] >= _ADMINISTRADOR && ($_SESSION['id_entity'] == $id_entity || $_SESSION['id_entity'] == $id_proceso)))
                            $action= 'edit';
                        
                        if ($action != 'list') { 
                        ?>
                        <td>
                            <a href="#" class="btn btn-warning btn-sm"
                                onclick="enviar_user(<?= $row['id'] ?>,'<?= $action ?>')">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <?php if (($row['_id'] > 1 && $row['_id'] != $_SESSION['id_usuario']) && is_null($row['eliminado'])) { ?>
                            <?php if ($_id_proceso == $row['id_proceso'] || empty($_id_proceso)) { ?>
                            <a href="#" class="btn btn-danger btn-sm"
                                onclick="enviar_user(<?= $row['_id'] ?>,'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } else { ?>
                            <a href="#" class="btn btn-success btn-sm" onclick="mostrar(<?= $row['_id'] ?>,'prs')"
                                title="asignar procesos">
                                <i class="fa fa-home"></i>
                            </a>
                            <?php } ?>
                            <?php } ?>
                        </td>
                        <?php } ?>

                        <td>
                            <!-- <a name="<?= $row['id'] ?>"></a> -->

                            <?php if (boolean($row['user_ldap'])) { ?>
                            <i class="fa fa-windows"></i>
                            <?php } ?>

                            <?php
                            $eliminado = empty($row['eliminado']) ? '' : odbc2date($row['eliminado']);
                            echo textparse($row['nombre']);
                            if (!empty($row['eliminado']))
                                echo "<br/>(eliminado en fecha $eliminado)";
                            ?>
                            <br />

                            <?php if (!empty($row['eliminado'])) { ?>
                            <i class="fa fa-trash" title="eliminado desde <?= $eliminado ?>"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_sys'])) { ?>
                            <i class="fa fa-lock" title="acceso bloqueado>"></i>
                            <?php } ?>

                            <!--
                            <?php if (boolean($row['conectado'])) { ?> 
                                <img class="img-thumbnail"  src="../img/online.gif" alt="conectado" title="conectado" /> 
                            <?php } ?>
                            <?php if (!boolean($row['conectado'])) { ?> &nbsp; <?php } ?>
                            -->

                            <?php if (boolean($row['acc_planwork']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-calendar" title="gestiona Planes de Trabajo Generales"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_planrisk']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-shield" title="gestiona los Planes de Prevención"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_planaudit']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-fire" title="gestiona Planes de medidas"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_planheal']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-magnet" title="gestiona los Planes y programas de auditorias"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_planproject']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-tasks"
                                title="gestiona y da seguimiento a la ejecución de los Proyectos"></i>
                            <?php } ?>

                            <?php if (boolean($row['acc_archive']) || $row['nivel'] == _SUPERUSUARIO) { ?>
                            <i class="fa fa-folder" title="gestiona los archivos (documentos impresos)"></i>
                            <?php } ?>
                        </td>

                        <td>
                            <?php
                            echo(!empty($row['cargo'])) ? $row['cargo'] . "<br />" : '&nbsp;';
                            $obj_prs->SetIdProceso($row['id_proceso']);
                            $obj_prs->Set();
                            echo '(' . $obj_prs->GetNombre() . ')';

                            if (!is_null($row['origen_data'])) {
                                $origen_data = $obj->GetOrigenData('process', $row['origen_data']);
                                echo "<br />" . merge_origen_data_process($origen_data, true);
                            }
                            ?>
                        </td>

                        <td><?= textparse($row['usuario']) ?></td>

                        <td>
                            <?php if (boolean($row['global_user'])) { ?>
                            <i class="fa fa-wifi" title="usuario_global"></i>
                            <?php } ?>
                            <?= $roles_array[$row['nivel']] ?>

                        </td>
                        <td>
                            <?= $row['email'] ?>
                        </td>
                        <td>
                            <?= $row['noIdentidad'] ?>
                        </td>

                        <?php $xaction = ($action != 'list') ? 'ver/modificar asignación de ' : 'ver asignación de '; ?>

                        <td>
                            <a class="btn btn-info btn-sm" href="javascript:mostrar(<?= $row['_id'] ?>,'prs')"
                                title="<?= $xaction ?> procesos">
                                <i class="fa fa-industry"></i>
                            </a>
                            <a class="btn btn-info btn-sm" href="javascript:mostrar(<?= $row['_id'] ?>,'su')"
                                title="<?= $xaction ?> subordinados">
                                <i class="fa fa-child"></i>
                            </a>
                            <a class="btn btn-info btn-sm" href="javascript:mostrar(<?= $row['_id'] ?>,'dp')"
                                title="<?= $xaction ?> grupos">
                                <i class="fa fa-users"></i>
                            </a>
                            <!--&nbsp;&nbsp;<a href="javascript:mostrar(<?= $row['_id'] ?>,'tc')"><img class="img-thumbnail"  src="../img/tablero.ico" title="<?= $xaction ?> tableros" /></a>&nbsp;-->
                        </td>

                        <td width="80px">
                            <?php if (!is_null($row['firma'])) { ?>
                            <img class="img-thumbnail"
                                src="../php/image.interface.php?menu=usuario&id=<?= $row['id'] ?>"
                                <?= $obj->GetDim($row['firma_param']) ?> />
                            <?php } ?>
                        </td>
                    </tr>

                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </form>

    <script type="text/javascript" language="JavaScript">
    document.getElementById('nshow').innerHTML = '<?=$cant_show?>';
    document.getElementById('nhide').innerHTML = '<?=$cant_print_reject?>';
    </script>

    <div id='div-ajax-panel' class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id='div-ajax' class='card-body'>
        </div>
    </div>

    <div id="bit" class="loggedout-follow-normal">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>
        <div id="bitsubscribe">
            <div class="row">
                <ul class="list-group-item item">
                    <li class="list-group-item item">
                        <i class="fa fa-calendar" title='Planes de Trabajo'></i>Gestiona Planes Generales de
                        Actividades
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-shield" title='Planes de prevencion'></i>Gestiona Planes de Riesgos y
                        Prevención
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-fire" alt="Planes de auditoria"></i>Gestiona Planes de Auditorias o de Acciones
                        de Control
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-magnet" title='Planes de medidas'></i>Gestiona Planes de Medidas o de Acciones
                        Correctivas/Correctoras
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-tasks" title='Gestion de Proyectos'></i>Gestiona y da seguimiento a la
                        ejecución de los Proyectos
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-folder" title='Gestion de Archivos'></i>Controla los documentos impresos.
                        Control de archivos
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-wifi" title="auditoria"></i>Es un usuario global. Su información se transmite
                        durante la sincronización
                    </li>
                    <li class="list-group-item item">
                        <i class="fa fa-windows" title="LDAP"></i>Es un usuario que se autoentica en el Directorio
                        Activo
                    </li>
                </ul>
            </div>
        </div><!-- #bitsubscribe -->
    </div><!-- #bit -->

</body>

</html>