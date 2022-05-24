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
require_once "../php/class/proceso.class.php";

require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/auditoria.class.php";

$id_redirect= 'ok';
$action= !empty($_GET['action']) ? $_GET['action'] :'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : null;

if (isset($_SESSION['obj'])) 
    unset($_SESSION['obj']);

$obj= new Tauditoria($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$acc_planwork= !empty($_SESSION['acc_planaudit']) ? $_SESSION['acc_planaudit'] : 0;

$obj->SetYear($year);
(!empty($id_proceso) && $id_proceso != -1) ? $obj->SetIdProceso($id_proceso) : $obj->SetIdProceso(null);

$result= $obj->listar();

$obj_user= new Tusuario($clink);

$actual_year= date('Y');
$inicio= $actual_year - 5;
$fin= $actual_year + 3;

$url_page= "../form/lauditoria.php?signal=$signal&action=$action&menu=evento&id_proceso=$id_proceso";
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

    <title>LISTADO DE AUDITORIAS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link rel="stylesheet" type="text/css" media="screen" href="../css/alarm.css?">

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/moment.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.es.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

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
        var year = $('#year').val();
        var id_proceso = $('#proceso').val();

        var url = 'lauditoria.php?action=<?=$action?>&year=' + year + '&id_proceso=' + id_proceso;
        self.location = url;
    }

    function filtrar() {
        displayFloatingDiv('div-ajax-panel-filter', "ESTADO DE EJECUCIÓN DE LA AUDITORIA", 60, 0, 10, 15);
    }

    function imprimir() {
        var year = $('#year').val();
        var id_proceso = $('#proceso').val();

        var url = '../print/lauditoria.php?year=' + year + '&id_proceso=' + id_proceso;
        prnpage = window.open(url, "IMPRIMIENDO LISTADO DE ACTIVIDADES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function enviar_auditoria(id, action) {
        var _action = action;

        function _this() {
            var year = $('#year').val();

            parent.app_menu_functions = false;
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/auditoria.interface.php?id=' + id + '&year=' + year + '&action=' +
                _action;
            document.forms[0].submit();
        }

        var msg =
            "IMPORTANTE!! La auditoria será eliminada y a igual que toda referencia a su gestión y demas trazabilidad. Desea continuar?";

        if (action == 'delete') {
            confirm(msg, function(ok) {
                if (!ok) return;
                else _this();
            });
        } else {
            _this();
        }
    }

    function cerrar() {
        CloseWindow('div-ajax-panel');
        refreshp();
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

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$init_year?>',
            maxDate: '31/12/<?=$end_year?>',
            autoclose: true,
            inline: true
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$init_year?>',
            maxDate: '31/12/<?=$end_year?>',
            autoclose: true,
            inline: true
        });

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
    $obj_prs= new Tproceso($clink);

    if ($id_proceso != -1) {
        $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['local_proceso_id']);
        $obj_prs->Set();
        $id_proceso_code= $obj_prs->get_id_proceso_code();
        $id_proceso_sup= $obj_prs->GetIdProceso_sup();
        $conectado_prs= $obj_prs->GetConectado();
        $type= $obj_prs->GetTipo();

        $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
    } else {
        $nombre_prs= "Todos los procesos";
    }

    $edit= ($action == 'edit' || $action == 'add') ? true : false;
    if ($edit && ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _NO_LOCAL)) 
        $edit= false;
    if ($edit && ($_SESSION['nivel'] < _SUPERUSUARIO && $_SESSION['id_usuario'] != $obj_prs->GetIdResponsable())) 
        $edit= false;
    if ($edit && (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL)) 
        $edit= true;

    $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
    $obj_prs->SetTipo($_SESSION['local_proceso_tipo']);
    $obj_prs->SetConectado(null);
    $obj_prs->SetIdUsuario(null);
    $obj_prs->SetIdResponsable(null);
    ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>              
                <a href="#" class="navd-header">
                    LISTADO DE AUDITORIAS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php
                        $id_select_prs= $id_proceso;
                        $restrict_prs= null;
                        $show_dpto= true;
                        require "inc/_dropdown_prs.inc.php";
                        ?>

                        <?php
                        $use_select_year= true;
                        $use_select_month= false;
                        $use_select_day= false;
                        $use_select_day= false;
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

            <li class="nav-item">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if (!empty($id_proceso) && $id_proceso != -1) { ?>
                        <?php if ($_connect_prs && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                            <i class="fa fa-wifi"></i>
                        <?php } } ?>

                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
        <input type="hidden" name="menu" id="menu" value="auditoria" />

        <div class="app-body container-fluid table threebar">
            <table id="table" class="table table-striped" data-toggle="table" data-pagination="true" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <?php if ($action == 'add' || $action == 'edit') { ?><th></th><?php } ?>
                        <th>AUDITORIA</th>
                        <th>ESTADO</th>
                        <th>APROBADO</th>
                        <th>RESPONSABLE</th>
                        <th>INICIA</th>
                        <th>FINALIZA</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                            $i = 0;
                            $_id = array(null);

                            $obj_reg= new Tregister_planning($clink);

                            while ($row = $clink->fetch_array($result)) {
                                $array_responsable = array('id_responsable' => $row['id_responsable'], 'id_responsable_2' => $row['id_responsable_2'],
                                    'responsable_2_reg_date' => $row['responsable_2_reg_date']);

                                $obj_reg->SetYear($year);
                                $obj_reg->SetIdAuditoria($row['id']);
                                $array = $obj_reg->getEvento_reg(null, $array_responsable);
                                if (is_null($array))
                                    continue;
                                ?>

                    <tr>
                        <td><?=++$i?></td>

                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <td>
                            <a class="btn btn-warning btn-sm" href="#" title="Editar"
                                onclick="enviar_auditoria(<?= $row['id'] ?>, 'edit')">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm" href="#" title="Eliminar"
                                onclick="enviar_auditoria(<?= $row['id'] ?>, 'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                        </td>
                        <?php } ?>

                        <td>
                            <?= $Ttipo_nota_origen_array[$row['origen']] ?>
                            <br />
                            <?= $Ttipo_auditoria_array[$row['tipo']] ?>
                        </td>
                        <td>
                            <label class="text alarm <?=$eventos_cump_class[$array['cumplimiento']]?>"
                                onclick="mostrar(<?= $row['id'] ?>)">
                                <?=$eventos_cump[$array['cumplimiento']]?>
                            </label>
                            <br />
                            <p>
                                <?php
                                        $email= $obj_user->GetEmail($array['id_responsable']);
                                        echo $email['nombre'];
                                        if (!empty($email['cargo'])) 
                                            echo ", ".textparse($email['cargo']);
                                        echo "<br />". odbc2time_ampm($array['cronos']);
                                        ?>
                            </p>
                        </td>
                        <td>
                            <?php
                                        if (!empty($array['aprobado'])) {
                                            echo odbc2time_ampm($array['aprobado']);
                                        }
                                        ?>
                        </td>
                        <td>
                            <?php
                                        $email= $obj_user->GetEmail($row['id_responsable']);
                                        echo $email['nombre'];
                                        if (!empty($email['cargo'])) 
                                            echo ", ".textparse($email['cargo']);
                                        ?>
                        </td>
                        <td>
                            <?= odbc2time_ampm($row['fecha_inicio_plan']) ?>
                        </td>
                        <td>
                            <?= odbc2time_ampm($row['fecha_fin_plan']) ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$i?>;
    </script>

</body>

</html>