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

require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/evento.class.php";

$id_redirect= 'ok';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : null;
if (isset($_SESSION['obj']))
    unset($_SESSION['obj']);

$obj= new Tevento($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['inicio']) ? date('m', strtotime(urldecode($_GET['inicio']))) : date('m');

$time= new TTime();
$time->splitTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$fecha_inicio= !empty($_GET['inicio']) ? urldecode($_GET['inicio']) : "1/".$month."/".$year;
$fecha_fin= !empty($_GET['fin']) ? urldecode($_GET['fin']) : $lastday."/".$month."/".$year;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null;
$texto= !empty($_GET['texto']) ? trim(urldecode($_GET['texto'])) : null;
$empresarial= !empty($empresarial) ? $_GET['empresarial'] : null;

$acc_planwork= !empty($_SESSION['acc_planwork']) ? $_SESSION['acc_planwork'] : 0;

$id_subordinado= !empty($_GET['id_subordinado']) ? $_GET['id_subordinado'] : null;
$id_responsable= $acc_planwork ? null : $_SESSION['id_usuario'];

$obj->SetYear($year);
(!empty($id_proceso) && $id_proceso != -1) ? $obj->SetIdProceso($id_proceso) : $obj->SetIdProceso(null);
$obj->SetFechaInicioPlan(date2odbc($fecha_inicio));
$obj->SetFechaFinPlan(date2odbc($fecha_fin));
$obj->SetCumplimiento(null);
$obj->SetIfEmpresarial($empresarial);
$obj->SetIdEscenario(null);
$obj->SetIdResponsable(null);
$obj->SetIdUsuario(null);

$result= $obj->listar();

$obj_user= new Tusuario($clink);

$obj_tipo_evento= new Ttipo_evento($clink);
$obj_tipo_evento->SetYear($year);

$url_page= "../form/levento.php?signal=$signal&action=$action&menu=evento&id_proceso=$id_proceso&cumplimiento=$cumplimiento";
$url_page.= "&year=$year&inicio=". urlencode($fecha_inicio)."&fin=".urlencode($fecha_fin)."&exect=$action";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE ACTIVIDADES O EVENTOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
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

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/string.js" charset="utf-8"></script>
    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript" type="text/javascript">
    function refreshp() {
        var empresarial = 0;

        if ($('#ifempresarial2').is(':checked'))
            empresarial = 1;

        var id_cumplimiento = $('#cumplimiento').val();
        var year = $('#year').val();
        var inicio = $('#fecha_inicio').val();
        var fin = $('#fecha_fin').val();
        var texto = $('#texto').val();
        var id_proceso = $('#proceso').val();

        if (Entrada(texto))
            texto = encodeURIComponent(trim_str(texto));

        if (DiferenciaFechas(fin, inicio, 'd') < 0) {
            alert(
                "Existe incongruencia en las fechas. La fecha de inicio del intervalo no puede ser anterior al cierre"
            );
            return false;
        }

        var url = 'levento.php?action=<?=$action?>&cumplimiento=' + id_cumplimiento + '&empresarial=' + empresarial;
        url += '&inicio=' + encodeURIComponent(inicio) + '&fin=' + encodeURIComponent(fin) + '&id_proceso=' +
            id_proceso;
        url += "&texto=" + texto + '&year=' + year;

        self.location = url;
    }

    function validar_date() {
        var year = $('#year').val();
        var text;

        var year1 = 0;
        fecha = new Fecha($('#fecha_inicio').val());
        year1 = fecha.anio;

        var year2 = 0;
        fecha = new Fecha($('#fecha_fin').val());
        year2 = fecha.anio;

        if (year1 != year2 || year1 != year || year2 != year) {
            text = "Debe escoger su intervalo de fechas dentro del año " + year +
                " selecionado. Puede seleccionar otro año para revizar.";
            alert(text);
            return false;
        }
        return true;
    }

    function filtrar_exect() {
        if (!validar_date())
            return;

        refreshp();
    }

    function filtrar() {
        validar_date();
        displayFloatingDiv('div-ajax-panel-filter', "ESTADO DE EJECUCIÓN DEL ACTIVIDAD", 50, 0, 10, 15);
    }

    function imprimir() {
        var year = $('#year').val();
        var inicio = $('#fecha_inicio').val();
        var fin = $('#fecha_fin').val();
        var texto = $('#texto').val();
        var id_proceso = $('#proceso').val();

        if (!validar_date())
            return;

        var url = '../print/levento.php?action=<?=$action?>&cumplimiento=' + id_cumplimiento + '&empresarial=' +
            empresarial;
        url += '&inicio=' + encodeURIComponent(inicio) + '&fin=' + encodeURIComponent(fin) + '&id_proceso=' +
            id_proceso;
        url += "&texto=" + texto + '&year=' + year;

        prnpage = window.open(url, "IMPRIMIENDO LISTADO DE ACTIVIDADES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function enviar_evento(id, action) {
        var _action = action;

        function _this() {
            parent.app_menu_functions = false;

            document.forms[0].exect.value = _action;
            document.forms[0].action = '../php/evento.interface.php?id=' + id + '&action=' + _action +
                '&force_delete=1';
            document.forms[0].submit();
        }

        var msg =
            "IMPORTANTE!! La actividad será eliminado y a igual que toda referencia al mismo en los calendarios de todos los participantes. Desea continuar?";

        if (_action == 'delete') {
            confirm(msg, function(ok) {
                if (!ok)
                    return;
                else
                    _this();
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
            minDate: '01/01/<?=$year?>',
            maxDate: '31/12/<?=$year?>',
            autoclose: true,
            inline: true
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$year?>',
            maxDate: '31/12/<?=$year?>',
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
            $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['id_entity']);
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
        if ($edit && (($id_proceso_sup == $_SESSION['id_entity'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL))
            $edit= true;

        $obj_prs->SetIdProceso($_SESSION['id_entity']);
        $obj_prs->SetTipo($_SESSION['entity_tipo']);
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
                    LISTADO DE EVENTOS PERIÓDICOS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a href="#" onclick="filtrar()">
                                <i class="fa fa-filter"></i>Filtrar
                            </a>
                        </li>

                        <?php
                        $id_select_prs= $id_proceso;
                        if (empty($id_select_prs)) 
                            $id_select_prs= -1;
                        $show_dpto= true;
                        $restrict_prs= array(_TIPO_DEPARTAMENTO);
                        require "inc/_dropdown_prs.inc.php";
                        ?>

                        <?php
                        $inicio= $year - 5;
                        $fin= $year + 5;
                        
                        $use_select_year= true;
                        $use_select_month= false;
                        $use_select_day= false;
                        require "../form/inc/_dropdown_date.inc.php";  
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
                                <a href="#" onclick="open_help_window('../help/manual.htm#11_13.2')">
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
                    <?=$fecha_inicio?>, <?=$fecha_fin?>
                </label>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nshow" class="badge badge-warning1"></div>
                </div>
            </li>
            <li class="col-auto">
                <div class="col-12">
                    <label class="badge badge-danger">
                        <?php if (!empty($id_proceso) && $id_proceso != -1) { ?>
                        <?php if ($_connect_prs && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?php } ?>

                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>



    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
        <input type="hidden" name="menu" id="menu" value="evento" />

        <div class="app-body container-fluid table threebar">
            <table id="table" class="table table-striped" data-toggle="table" data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Número</th>
                        <?php if ($action == 'add' || $action == 'edit') { ?><th></th><?php } ?>
                        <th>ACTIVIDAD</th>
                        <th>ESTADO</th>
                        <th>APROBADO</th>
                        <th>RESPONSABLE</th>
                        <th>INICIA</th>
                        <th>FINALIZA</th>
                        <th>LUGAR</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    $_id = array(null);

                    $obj_reg= new Tregister_planning($clink);

                    while ($row = $clink->fetch_array($result)) {
                        ++$i;
                        $array_responsable = array('id_responsable' => $row['id_responsable'], 'id_responsable_2' => $row['id_responsable_2'],
                            'responsable_2_reg_date' => $row['responsable_2_reg_date']);

                        $obj_reg->SetYear($year);
                        $array = $obj_reg->getEvento_reg($row['id'], $array_responsable);

                        //$img= $obj->getCumplidoImage();
                        $numero= !empty($row['numero']) ? $row['numero'] : null;
                        $numro.= !empty($row['numero_plus']) ? ".{$row['numero_plus']}" : null;

                        $capitulo= null;
                        if (!empty($row['id_tipo_evento'])) {
                            $obj_tipo_evento->Set($row['id_tipo_evento']);
                            $capitulo= $obj_tipo_evento->GetNumero();
                        } else {
                            if (!empty($row['empresrial']) && $row['empresarial'] >= 1)
                                $capitulo= ($row['empresarial'] - 1);
                        }

                        if (!empty($capitulo))
                            $numero= "{$capitulo}.{$numero}";

                        if (!empty($texto) && (stripos(strtolower($row['nombre']), strtolower($texto)) === false)
                                                && (stripos(strtolower($row['descripcion']), strtolower($texto)) === false))
                            continue;
                        ?>

                    <tr>
                        <td><?=++$j?></td>

                        <td><?=!empty($numero) ? $numero : $i?></td>

                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <td>
                            <a class="btn btn-warning btn-sm" href="#" title="Editar"
                                onclick="enviar_evento(<?= $row['id'] ?>, 'edit')">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm" href="#" title="Eliminar"
                                onclick="enviar_evento(<?= $row['id'] ?>, 'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                        </td>
                        <?php } ?>

                        <td>
                            <?php if (!empty($row['periodicidad'])) { ?>
                            <i class="fa fa-folder-open-o fa-2x"></i>
                            <?php } ?>

                            <?= textparse($row['nombre']) ?>
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
                        <td>
                            <?= nl2br($row['lugar']) ?>
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

    <!-- div-ajax-panel -->
    <div id="div-ajax-panel-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-md-10">FILTRADO DE LOS EVENTOS</div>
                <div class="col-1pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-ajax-panel-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="container-fluid form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-sm-2">Desde:</label>
                    <div class="col-md-4">
                        <div id="div_fecha_inicio" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control input-sm" id="fecha_inicio" name="fecha_inicio"
                                readonly value="<?=$fecha_inicio?>">
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <label class="col-form-label col-sm-2">Hasta:</label>
                    <div class="col-md-4">
                        <div id="div_fecha_fin" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control input-sm" id="fecha_fin" name="fecha_fin"
                                readonly value="<?=$fecha_fin?>">
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="checkbox text col-md-12">
                        <input type="radio" name="empresarial" id="ifempresarial1" value="0"
                            <?php if (empty($empresarial)) echo "checked='checked'" ?> />
                        Todos los eventos
                    </label>

                    <label class="checkbox text col-md-12">
                        <input type="radio" name="empresarial" id="ifempresarial2" value="1"
                            <?php if (!empty($empresarial)) echo "checked='checked'" ?> />
                        Solamente los incluidos en los Planes Generales (anual y mensual) de la Empresa
                    </label>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-md-3">
                        Estado de Cumplimiento:
                    </label>
                    <div class="col-sm-4 col-md-5 col-lg-5">
                        <select name="cumplimiento" id="cumplimiento" class="form-control"
                            onchange="javascript:refreshp()">
                            <option value="0">Todos...</option>
                            <?php for ($i = 1; $i < 7; ++$i) { ?>
                            <option value="<?= $i ?>" <?php if ($i == $cumplimiento) echo "selected='selected'" ?>>
                                <?= $eventos_cump[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="text col-md-12">
                        Contiene el texto:
                    </label>
                    <div class="col-md-12">
                        <textarea type="text" class="form-control" id="texto" name="texto"><?=$texto?></textarea>
                    </div>
                </div>

                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="btn" onclick="filtrar_exect()">Filtrar</button>
                    <button class="btn btn-warning" type="reset"
                        onclick="CloseWindow('div-ajax-panel-filter');">Cerrar</button>
                </div>
            </div>

        </div>
    </div> <!-- div-ajax-panel -->
</body>

</html>