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
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/indicador.class.php";


$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

if (isset($_SESSION['obj'])) 
    unset($_SESSION['obj']);

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$obj_indi= new Tindicador($clink);
$obj_indi->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_user->SetIdProceso(null);
$fecha_origen= date('Y-m-d H:i:s');
$obj_user->set_user_date_ref($fecha_origen);
$result_user= $obj_user->listar(null, null, _LOCAL);

$obj_persp= new Tperspectiva($clink);

$inicio= $year - 5;
$fin= $year + 5;

$obj_indi= new Tindicador($clink);
$obj_indi->SetIdProceso($id_proceso);
$array_perspectivas = $obj_indi->listar_perspectivas($year);
$cant_persp = $obj_indi->GetCantidad();

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdEntity(null);
$array_procesos_entity= $obj_prs->listar(false);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>PERMISOS ACCESO A INDICADORES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script language="javascript" type="text/javascript" src="../js/tablero.js?version="></script>

    <style type="text/css">
    body {
        padding: 0px;
        margin: 0px;
        background: rgb(68, 85, 102) none repeat scroll 0% 0%;
    }

    h4.panel-title {
        font-size: 0.95em;
        font-weight: bold;
        text-decoration: none;
    }

    h4.panel-title a:hover,
    h4.panel-title a:active,
    h4.panel-title a:visited {
        cursor: pointer;
        font-style: normal;
        text-decoration: none !important;
    }

    .fa-search-plus.icon {
        color: black !important;
        margin-left: 5px !important;
        margin-right: 30px !important;
    }
    </style>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function refreshp(index) {
        var id_perspectiva = $('#perspectiva').val();
        var action = $('#exect').val();
        var id_proceso = $('#id_proceso').val();
        var year = $('#year').val();

        if (index == 1)
            id_perspectiva = 0;

        var url = 'findicador_usuarios.php?action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;
        url += '&id_perspectiva=' + id_perspectiva;
        self.location = url;
    }

    var changed = 0;

    function set_changed() {
        changed = 1;
    }

    function selectPersp(id) {
        $(".collapse").collapse('hide');
        $(id).collapse('toggle');
    }

    function ejecutar() {
        var form = document.forms[0];

        var id_proceso = $('#id_proceso').val();
        var year = $('#year').val();
        var action = $('#exect').val();

        var url = '&action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;

        form.action = '../php/indicador_user.interface.php?' + url;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        form.submit();
    }

    function imprimir() {
        var url = '../print/lindicador.php?version=&action=user';
        url += '&id_proceso=<?=$_SESSION['id_entity']?>';
        prnpage = show_imprimir(url, "IMPRIMIENDO LISTADO DE INDICADORES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp(1);
    }

    $(document).ready(function() {
        $(".accordion-toggle").click(function() {
            selectPersp($(this).attr('href'));
        });
        $(".collapse").collapse('hide');
        $('#persp-<?=empty($id_perspectiva) ? 0 : $id_perspectiva?>').collapse('show');

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <a href="#" class="navd-header">ACCESOS A INDICADORES</a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">

                    <?php
                    $use_select_year = true;
                    $use_select_month = false;
                    $use_select_day = false;
                    require "inc/_dropdown_date.inc.php";
                    ?>

                    <li>
                        <a href="#" class="d-none d-lg-block" onclick="imprimir()">
                            <i class="fa fa-print"></i>
                            Imprimir
                        </a>
                    </li>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <a href="#" onclick="open_help_window('../help/02_usuarios.htm#02_4.1')">
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
    $nombre= $obj_prs->GetNombre().' ('.$Ttipo_proceso_array[$obj_prs->GetTipo()].')';
    $conectado= $obj_prs->GetConectado();
    $tipo= $obj_prs->GetTipo();
    ?>

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$meses_array[(int)$month]?>, <?=$year?>
                </label>
            </li>

            <li class="col">
                <div class="col-12">
                    <label class="badge badge-danger">
                        <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?=$nombre?>
                    </label>
                </div>
            </li>
        </ul>
    </div>


    <!-- app-body -->
    <div class="app-body container-fluid threebar">
        <div class="card card-primary" style="margin-top: 10px;">
            <div class="card-header">
                CONFIGURACIÓN DE ACCESOS A INDICADORES
            </div>

            <div class="card-body">
                <form action="javascript:ejecutar()" class="form-horizontal" method=post>
                    <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
                    <input type="hidden" id="fecha_origen" name="fecha_origen" value="<?= $fecha_origen ?>" />
                    <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso?>" />

                    <!-- panel-group -->
                    <div class="panel-group" id="accordion">

                        <?php $perspectiva= "NO DEFINIDOS EN PERSPECTIVA"; ?>
                        <div class="card card-default">
                            <?php if ($cant_persp) { ?>
                            <div class="card-header">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                                        href="#persp-0">
                                        <i class="fa fa-search-plus fa-2x icon"></i><?=$perspectiva?>
                                    </a>
                                </h4>
                            </div>
                            <?php } ?>

                            <div id="persp-0" class="panel-collapse collapse<?= $cant_persp ? ' in' : ''?>">
                                <table class="table table-hover table-striped" data-toggle="table"
                                    data-search="true">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>INDICADOR</th>
                                            <th>CON PERMISO PARA ACTUALIZAR</th>
                                            <th>CON PERMISO PARA PLANIFICAR</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        unset($obj_indi);
                                        $obj_indi= new Tindicador($clink);
                                        $obj_indi->SetIdProceso($id_proceso);
                                        $obj_indi->SetYear($year);

                                        $k= 0;
                                        $j = 0;
                                        $disabled= ($action == 'list') ? "disabled" : "";
                                        $with_null_perspectiva= _PERSPECTIVA_NULL;
                                        $result_indi= $obj_indi->listar(null, $with_null_perspectiva, false);

                                        foreach ($result_indi as $row) {
                                            ++$j;
                                            $indicador = $row['nombre'];
                                            $id_indicador = $row['id'];
                                            $id_usuario_real = $row['id_usuario_real'];
                                            $id_usuario_plan = $row['id_usuario_plan'];
                                            ?>

                                        <tr>
                                            <td>
                                                <?= !empty($row['numero']) ? $row['numero'] : $j ?>
                                            </td>
                                            <td>
                                                <div class=""
                                                    title="Periodicidad: <?= $periodo_inv[(int) $row['periodicidad']] ?>">
                                                    <?= $indicador ?></div>
                                            </td>

                                            <td>
                                                <input type="hidden" name="id_<?= ++$k ?>"
                                                    value="<?= $id_indicador ?>" />

                                                <?php if ($action == 'edit') { ?>
                                                <select name="usuario_real_<?= $k ?>" id="usuario_real_<?= $k ?>"
                                                    class="form-control" <?= $disabled ?> onchange="set_changed()">
                                                    <option value="0"> ... </option>

                                                    <?php
                                                    $clink->data_seek($result_user);
                                                    while ($row_user = $clink->fetch_array($result_user)) {
                                                        $prs= $array_procesos_entity[$row['id_proceso']];  
                                                        if ((!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity']) 
                                                                || (empty($prs['id_entity']) && $prs['id'] != $_SESSION['id_entity']))
                                                            continue;
                                                        if (empty($row_user['nombre']))
                                                            continue;
                                                        $name= textparse($row_user['nombre']);
                                                        if (!empty($row_user['cargo']))
                                                            $name.= ", ". textparse($row_user['cargo']);

                                                        $id_usuario = $row_user['id'];
                                                        $id_role = $row_user['nivel'];

                                                        if ($id_role >= _REGISTRO) {
                                                        ?>
                                                            <option value="<?= $id_usuario ?>"
                                                                <?php if ($id_usuario == $id_usuario_real) echo "selected" ?>>
                                                                <?= $name ?> </option>
                                                            <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>

                                                            <?php
                                                            } else {
                                                                $obj_user->SetIdUsuario($id_usuario_real);
                                                                $obj_user->Set();

                                                                $str = null;
                                                                $str = $obj_user->GetNombre();
                                                                $cargo= $obj_user->GetCargo();
                                                                if (!empty($str) && !empty($cargo))
                                                                    $str .= ", $cargo";
                                                                echo $str;
                                                            }
                                                            ?>

                                            </td>

                                            <td>
                                                <?php if ($action == 'edit') { ?>

                                                <select name="usuario_plan_<?= $k ?>" id="usuario_plan_<?= $k ?>"
                                                    class="form-control" <?= $disabled ?> onchange="set_changed()">
                                                    <option value="0">... </option>

                                                    <?php
                                                    $clink->data_seek($result_user);
                                                    while ($row_user = $clink->fetch_array($result_user)) {
                                                        $prs= $array_procesos_entity[$row['id_proceso']];  
                                                        if ((!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity']) 
                                                                || (empty($prs['id_entity']) && $prs['id'] != $_SESSION['id_entity']))
                                                            continue;                                                                        
                                                        if (empty($row_user['nombre']))
                                                            continue;
                                                        $name= textparse($row_user['nombre']);
                                                        if (!empty($row_user['cargo']))
                                                            $name.= ", ". textparse($row_user['cargo']);

                                                        $id_usuario = $row_user['id'];
                                                        $id_role = $row_user['nivel'];

                                                        if ($id_role > _REGISTRO) {
                                                        ?>
                                                            <option value="<?= $id_usuario ?>"
                                                                <?php if ($id_usuario == $id_usuario_plan) echo "selected" ?>>
                                                                <?= $name ?> </option>
                                                            <?php
                                                                    }
                                                                }
                                                                ?>
                                                        </select>

                                                        <?php
                                                        } else {
                                                            $obj_user->SetIdUsuario($id_usuario_plan);
                                                            $obj_user->Set();

                                                            $str = null;
                                                            $str = $obj_user->GetNombre();
                                                            $cargo= $obj_user->GetCargo();
                                                            if (!empty($str) && !empty($cargo))
                                                                $str .= ", $cargo";
                                                            echo $str;
                                                        }
                                                        ?>

                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php
                            $_color = null;
                            $id_perspectiva= null;
                            $obj_prs= new Tproceso($clink);

                            $obj_indi->SetIdProceso($id_proceso);
                            $array_perspectivas = $obj_indi->listar_perspectivas($year);
                            $cant_persp = $obj_indi->GetCantidad();

                            foreach ($array_perspectivas as $row) {
                               $id= $row['id'];
                               if (is_null($id_perspectiva))
                                   $id_perspectiva= $id;

                               $obj_persp->Set($id);

                               $perspectiva= $row['nombre'];
                               $color= '#'.$obj_persp->GetColor();

                               $obj_prs->Set($row['id_proceso']);
                               $nombre_prs = '(' .$row['inicio'].'-'.$row['fin'].')  '.$obj_prs->GetNombre().', '.$Ttipo_proceso_array[$row['tipo']];
                            ?>

                        <div class="card card-default">
                            <div class="card-header" style="background-color: <?=$color?>">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                                        href="#persp-<?=$id?>">
                                        <i class="fa fa-search-plus fa-2x icon"></i><?=$perspectiva?>
                                    </a>
                                </h4>
                            </div>

                            <div id="persp-<?=$id?>" class="panel-collapse collapse in">
                                <table class="table table-hover table-striped" data-toggle="table" data-height="400"
                                    data-search="true">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>INDICADOR</th>
                                            <th>CON PERMISO PARA ACTUALIZAR</th>
                                            <th>CON PERMISO PARA PLANIFICAR</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            $disabled= ($action == 'list') ? "disabled" : "";
                                            $with_null_perspectiva= $id_perspectiva ? _PERSPECTIVA_NOT_NULL : _PERSPECTIVA_NULL;
                                            $obj_indi->SetIdPerspectiva($id);
                                            $result_indi= $obj_indi->listar(null, $with_null_perspectiva, false);

                                            foreach ($result_indi as $row) {
                                                ++$j;
                                                $indicador = $row['nombre'];
                                                $id_indicador = $row['id'];
                                                $id_usuario_real = $row['id_usuario_real'];
                                                $id_usuario_plan = $row['id_usuario_plan'];
                                                ?>

                                        <tr>
                                            <td>
                                                <?= !empty($row['numero']) ? $row['numero'] : $j ?>
                                            </td>
                                            <td>
                                                <div class=""
                                                    title="Periodicidad: <?= $periodo_inv[(int) $row['periodicidad']] ?>">
                                                    <?= $indicador ?></div>
                                            </td>

                                            <td>
                                                <input type="hidden" name="id_<?= ++$k ?>"
                                                    value="<?= $id_indicador ?>" />

                                                <?php if ($action == 'edit') { ?>
                                                <select name="usuario_real_<?= $k ?>" id="usuario_real_<?= $k ?>"
                                                    class="form-control input-sm" <?= $disabled ?>
                                                    onchange="set_changed()">
                                                    <option value="0">... </option>

                                                    <?php
                                                    $clink->data_seek($result_user);

                                                    while ($row_user = $clink->fetch_array($result_user)) {
                                                        if (empty($row_user['nombre']))
                                                            continue;
                                                        $name= textparse($row_user['nombre']);
                                                        if (!empty($row_user['cargo']))
                                                            $name.= ", ". textparse($row_user['cargo']);

                                                        $id_usuario = $row_user['id'];
                                                        $id_role = $row_user['nivel'];

                                                        if ($id_role >= _REGISTRO) {
                                                            ?>
                                                            <option value="<?= $id_usuario ?>"
                                                                <?php if ($id_usuario == $id_usuario_real) echo "selected" ?>>
                                                                <?= $name?> </option>
                                                            <?php
                                                                    }
                                                                }
                                                                ?>
                                                </select>

                                                <?php
                                                } else {
                                                    $obj_user->SetIdUsuario($id_usuario_real);
                                                    $obj_user->Set();

                                                    $str = null;
                                                    $str = $obj_user->GetNombre();
                                                    $cargo= $obj_user->GetCargo();
                                                    if (!empty($str) && !empty($cargo))
                                                        $str .= ", $cargo";
                                                    echo $str;
                                                }
                                                ?>

                                            </td>

                                            <td>
                                                <?php if ($action == 'edit') { ?>

                                                <select name="usuario_plan_<?= $k ?>" id="usuario_plan_<?= $k ?>"
                                                    class="form-control input-sm" <?= $disabled ?>
                                                    onchange="set_changed()">
                                                    <option value="0">... </option>

                                                    <?php
                                                    $clink->data_seek($result_user);

                                                    while ($row_user = $clink->fetch_array($result_user)) {
                                                        $nombre = $row_user['nombre'];
                                                        $cargo = $row_user['cargo'];
                                                        $id_usuario = $row_user['id'];
                                                        $id_role = $row_user['nivel'];

                                                        if ($id_role > _REGISTRO) {
                                                            ?>
                                                            <option value="<?= $id_usuario ?>"
                                                                <?php if ($id_usuario == $id_usuario_plan) echo "selected" ?>>
                                                                <?= $nombre ?> (<?= $cargo ?>) </option>
                                                            <?php
                                                                    }
                                                                }
                                                                ?>
                                                </select>

                                                <?php
                                                } else {
                                                    $obj_user->SetIdUsuario($id_usuario_plan);
                                                    $obj_user->Set();

                                                    $str = null;
                                                    $str = $obj_user->GetNombre();
                                                    $cargo= $obj_user->GetCargo();
                                                    if (!empty($str) && !empty($cargo))
                                                        $str .= ", $cargo";
                                                    echo $str;
                                                }
                                                ?>

                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php } ?>
                    </div> <!-- panel-group -->

                    <input type="hidden" name="count" value="<?=$k?>" />
                    <hr />
                    <!-- buttom -->
                    <div id="_submit" class="btn-block btn-app">
                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <button class="btn btn-primary" type="submit">Aceptar</button>
                        <?php } ?>
                        <button class="btn btn-warning" type="reset"
                            onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                        <button class="btn btn-danger" type="button"
                            onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                    </div>

                    <div id="_submited" style="display:none">
                        <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                    </div>

                </form>
            </div> <!-- panel-body -->
        </div>
    </div>

</body>

</html