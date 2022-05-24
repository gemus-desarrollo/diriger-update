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
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/plan_ci.class.php";
require_once "../php/class/nota.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_auditoria.class.php";

require_once "../form/class/nota.signal.class.php";

$_SESSION['debug'] = 'no';
$signal = 'nota';

require_once "../php/inc_escenario_init.php";

$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_proceso'];
$show_all_notes = !empty($_GET['show_all_notes']) ? 1 : 0;

if (empty($id_proceso))
    $id_proceso = $_SESSION['usuario_proceso_id'];

if (empty($month) || $month == -1 || $month == 13)
    $month = 12;

$fin = $actual_year + 2;
if ($year == $actual_year)
    $end_month = $actual_month;
else
    $end_month = 12;

$obj = new Tnota($clink);
$obj_signal = new Tnota_signals($clink);

$obj_user = new Tusuario($clink);

$obj->SetDay(null);
$obj->SetMonth($month);
$obj->SetYear($year);

$date_cut = $year . '-' . str_pad($month, '0', 2, STR_PAD_LEFT) . '-' . str_pad($day, '0', 2, STR_PAD_LEFT);

$noconf = !is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej = !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ = !is_null($_GET['observ']) ? $_GET['observ'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf = 1;
    $mej = 1;
    $observ = 1;
}

$id_auditoria = !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;

$url_page = "../html/nota.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page .= "&year=$year&month=$month&day=$day&noconf=$noconf&mej=$mej&observ=$observ&id_auditoria=$id_auditoria";
$url_page .= "&show_all_notes=$show_all_notes";

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
        $delay = (int)$config->delay * 60;
        header("Refresh: $delay; url=$url_page&csfr_token=123abc");
    }
    ?>

    <title>NOTAS DE HALLAZGOS</title>

    <?php
    $acc = $_SESSION['acc_planheal'];
    $tipo_plan = _PLAN_TIPO_ACCION;

    require_once "inc/_tablero_top_riesgo.inc.php";
    ?>


    <script type="text/javascript">
        function show_all_notes(val) {
            $('#show_all_notes').val(val);
            refreshp();
        }

        function mostrar_guia(panel) {
            var id_proceso = $('#proceso').val();
            var year = $('#year').val();
            var id_auditoria = $('#id_auditoria').val();

            var url = '../form/ajax/fadd_guia.ajax.php?signal=nota&action=add' + '&id_proceso=' + id_proceso;
            url += '&year=' + year + '&id_auditoria=' + id_auditoria + '&panel=' + panel;

            var capa = 'div-ajax-panel';
            var metodo = 'GET';
            var valores = '';
            var funct= '';

            displayFloatingDiv('div-ajax-panel', "SELECCIONE LISTA DE CHEQUEO", 80, 40, 10, 15);
            FAjax(url, capa, valores, metodo, funct);
        }

        function mostrar_resume_guia(index) {
            var year = $('#year').val();
            var panel= index == 0 ? "guia_grafica" : "guia_tabla"; 

            var url = '../form/ajax/fadd_guia.ajax.php?signal=nota&action=add&panel='+panel;
            url += '&id_proceso=-1&&year=' + year;

            var capa = 'div-ajax-panel';
            var metodo = 'GET';
            var valores = '';
            var funct= '';
            
            displayFloatingDiv('div-ajax-panel', "SELECCIONE LISTA DE CHEQUEO", 80, 40, 10, 15);
            FAjax(url, capa, valores, metodo, funct);           
        }

        function to_general_resume(panel) {
            var id_lista= $('#id_lista').val();
            var year= $('#year').val();

            var url;
            if (panel == "guia_grafica") 
                url= "../print/resume_lista_general.php";
            if (panel == "guia_tabla")
                url= "../print/resumen_lista_requisitos.php";

            url+= '?id_lista='+id_lista+'&year='+year;
            show_imprimir(url, "IMPRIMIENDO RESUMEN GENERAL DE APLICACIÓN DE GUIA", 
                        "width=1000,height=800,toolbar=no,location=no, scrollbars=yes");
            CloseWindow('div-ajax-panel');
        }

        function to_guia(panel) {
            var id_auditoria = $('#id_auditoria').val();
            var id_lista = $('#id_lista').val();
            var year = $('#year').val();
            var id_proceso = $('#proceso').val();
            var url = 'year=' + year + '&id_lista=' + id_lista + '&id_auditoria=' + id_auditoria + '&id_proceso=' + id_proceso;  

            if (panel == 'guide')
                url = '../form/flista_requisito_status.php?' + url;
            if (panel == 'resume')
                url = '../html/resume_requisito.php?' + url;

            self.location.href = url;
        }

        function grafico() {
            var noconf = 0,
                mej = 0,
                observ = 0;
            var id_proceso = $('#proceso').val();
            var year = $('#year').val();
            var show_all_notes = $('#show_all_notes').val();
            var url = "graph_nota.php?";
            var id_auditoria = $('#id_auditoria').val();

            if ($('#mej').is(':checked'))
                mej = 1;
            if ($('#observ').is(':checked'))
                observ = 1;
            if ($('#noconf').is(':checked'))
                noconf = 1;

            url += '&noconf=' + noconf + '&mej=' + mej + '&observ=' + observ + '&id_auditoria=' + $('#id_auditoria').val();
            url += '&id_proceso=' + id_proceso + '&year=' + year + '&show_all_notes=' + show_all_notes + '&id_auditoria=' + id_auditoria;
            self.location = url;
        }
    </script>

    <style>
        .prs-text {
            color: #828282;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
    require_once "../form/inc/_riesgo_top_div.inc.php";

    $obj_proceso = new Tproceso($clink);
    $obj_proceso->SetIdProceso($id_proceso);
    $obj_proceso->Set();
    $id_proceso_code = $obj_proceso->get_id_code();
    $tipo = $obj_proceso->GetTipo();
    $nombre = $obj_proceso->GetNombre() . ' (' . $Ttipo_proceso_array[$obj_proceso->GetTipo()] . ')';
    $id_responsable = $obj_proceso->GetIdResponsable();
    ?>

    <input type="hidden" id="id_lista" name="id_lista" value="<?= $id_lista ?>" />
    <input type="hidden" id="id_lista_code" name="id_lista_code" value="" />

    <input type="hidden" id="show_all_notes" name="show_all_notes" value="<?= $show_all_notes ?>" />

    <!-- app-body -->
    <div class="app-body dashboard container-fluid sixthbar">
        <?php
        $cant_show = 0;
        $cant_print_reject = 0;

        $obj_event = new Tevento($clink);

        if (!empty($id_proceso) && !is_null($array_procesos)) {
            $obj->SetTipo(null);
            $obj->SetIdProceso(null);
            $obj->SetIdEntity(null);
            $obj->SetYear($year);
            $obj->SetMonth(null);
            $obj->SetIdAuditoria($id_auditoria);
            $obj->SetChkApply(true);

            $obj_prs = new Tproceso($clink);
            $obj_prs->SetYear($year);
            $obj_prs->SetIdProceso($id_proceso);

            $obj_prs->get_procesos_down(null, null, null, true);
            $array_procesos_down = $obj_prs->array_cascade_down;

            if (isset($array)) 
                unset($array);
                
            $array = array();
            foreach ($array_procesos_down as $key => $prs)
                $array[] = $key;

            $obj->set_show_all_notes($show_all_notes);
            $result = $obj->listar($noconf, $mej, $observ, true, $array);

            if (count($array) == 1) {
                $obj->SetIdProceso($id_proceso);
                $obj->set_id_proceso_code($id_proceso_code);
            }

            $ranking = $obj->list_ranking($result, $config->automatic_note);

            // NO CONFORMIDADES -------------------------------------------------------------
            unset($obj);
            $obj = new Tnota($clink);

            $i = 0;
            reset($ranking);
            foreach ($ranking as $_array) {
                write_nota($_array, _NO_CONFORMIDAD);
            }
            // OBSERVACIONES -------------------------------------------------------------
            reset($ranking);
            foreach ($ranking as $_array) {
                write_nota($_array, _OBSERVACION);
            }
            // NOTAS DE MEJORA -------------------------------------------------------------
            reset($ranking);
            foreach ($ranking as $_array) {
                write_nota($_array, _OPORTUNIDAD);
            }
        ?>

        <?php } else { ?>
            <div class="container-fluid">
                <div class="alert alert-danger">
                    <h1>USTED NO TIENE ACCESO A NINGÚN PROCESO INTERNO. DEBE CONSULTAR AL ADMINISTRADOR DEL SISTEMA.</h1>
                </div>
            </div>
        <?php } ?>
    </div> <!-- app-body -->

    <script type="text/javascript">
        document.getElementById('nshow').innerHTML = <?= $cant_show ?>;
        document.getElementById('nhide').innerHTML = <?= $cant_print_reject ?>;
    </script>

    <!-- #win-board-signal -->
    <div id="win-board-signal" class="card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="signal-title" class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>    
            </div>
        </div>

        <div class="card-body">
            <div id="win-board-signal-project-icon" class="btn-toolbar btn-block list-group-horizontal">
                <a id="img_dox" class="btn btn-app btn-primary btn-sm d-none d-lg-inline-block" onclick="showWindow('<?= $action ?>')" title="Gestión de documentos y actas adjuntos">
                    <i class="fa fa-file-text"></i>Documentos
                </a>

                <a class="btn btn-app btn-info btn-sm d-none d-lg-inline-block" title="ver el estado de las tareas" onclick="mostrar('tareas')">
                    <i class="fa fa-check-square"></i>Estado
                </a>

                <?php if ($action == 'edit' && $permit_aprove) {  ?>
                    <a class="btn btn-app btn-primary btn-sm d-none d-lg-inline-block" title="definir estado de la gestión" onclick="mostrar('register')">
                        <i class="fa fa-check"></i>Registrar
                    </a>

                    <a class="btn btn-app btn-warning btn-sm d-none d-lg-inline-block" title="editar" onclick="_edit()">
                        <i class="fa fa-edit"></i>Editar
                    </a>

                    <a class="btn btn-app btn-danger btn-sm d-none d-lg-inline-block" title="eliminar el nota" onclick="_delete()">
                        <i class="fa fa-trash"></i>Eliminar
                    </a>
                <?php } ?>
            </div>

            <div class="win-body container-fluid">
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-3"><span style="font-weight: bold; font-size: 1.1em;">Unidad Organizativa:</span></div>
                    <div class="col-8" id="proceso-name"></div>
                </div>

                <div class="row">
                    <div class="col-4 badge">ÚLTIMO REGISTRO</div>

                    <div class="col-12">
                        <table class="descripcion-block">
                            <tr>
                                <td class="title">Estado:</td>
                                <td colspan="3">
                                    <div id="estado"></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Registrado por:</td>
                                <td colspan="3">
                                    <div id="registro"></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Fecha (Referencia):</td>
                                <td>
                                    <div id="reg_fecha"></div>
                                </td>
                                <td class="title" style="padding-left: 20px;">Registro:</td>
                                <td>
                                    <div id="registro_date"></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <div class="title">Observaciones:</div>
                                    <div id="observacion_item"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-4 badge">ESTADO INICIAL</div>
                    <div class="col-12">
                        <table class="descripcion-block">
                            <tr>
                                <td class="title">Identificado por:</td>
                                <td>
                                    <div id="usuario"></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Registro:</td>
                                <td>
                                    <div id="registro_date_init"></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Planificada:</td>
                                <td>
                                    <div id="date_interval"></div>
                                </td>
                            </tr>
                            <!--
                                <tr>
                                    <td colspan="2">
                                        <div class="title">Descripción:</div>
                                        <div class="descripcion_item" id="descripcion"></div>
                                    </td>
                                </tr>
                                -->
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- #win-board-signal -->


    <!-- Panel2 -->
    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title win-drag col-10">FILTRADO DE NOTAS</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body form">
            <div class="col-md-12">
                <label class="checkbox text">
                    <input type="checkbox" name="noconf" id="noconf" value="1" <?php if (!empty($noconf)) echo "checked='checked'" ?> />
                    Mostrar las No-conformidades
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="observ" id="observ" value="1" <?php if (!empty($observ)) echo "checked='checked'" ?> />
                    Mostrar las Observaciones
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="mej" id="mej" value="1" <?php if (!empty($mej)) echo "checked='checked'" ?> />
                    Mostrar las Notas de Mejora
                </label>

                <hr />
                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="button" onclick="refreshp()">Aceptar</button>
                    <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!--panel2 -->


    <div id="bit" class="loggedout-follow-normal" style="width: 30%"><a class="bsub" href="javascript:void(0)">
            <span id="bsub-text">Leyenda</span></a>
        <div id="bitsubscribe">
            <div class="row">
                <div class="col-6">
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-box small bg-red"></div>
                        </div>
                        <label class="text col-xs-8 col-8">
                            No conformidad
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-box small bg-orange"></div>
                        </div>
                        <label class="text col-8">
                            Observación
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-box small bg-aqua"></div>
                        </div>
                        <label class="text col-xs-8 col-8">
                            Oportunidad de Mejora
                        </label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-cicle small bg-red"></div>
                        </div>
                        <label class="text col-8">
                            Identificada
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-cicle small bg-yellow"></div>
                        </div>
                        <label class="text col-8">
                            Gestionandose
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="alarm-cicle small bg-green"></div>
                        </div>
                        <label class="text col-8">
                            Cerrada
                        </label>
                    </div>
                </div>
            </div>
        </div><!-- #bitsubscribe -->
    </div><!-- #bit -->


    <?php require_once "inc/_tablero_bottom_riesgo.inc.php"; ?>

    <div id="div-ajax-panel" data-bind="draganddrop">
    </div>
</body>

</html>


<?php
function write_nota($_array, $tipo_filter) {
    global $obj;
    global $obj_prs;
    global $obj_user;
    global $obj_signal;

    global $cant_show;
    global $estado_hallazgo_array;
    global $Ttipo_nota_array;
    global $Ttipo_proceso_array;

    global $array_proceso_conected;

    $id = $_array['id'];
    $obj->SetIdNota($id);
    $obj->Set();
    $tipo = $obj->GetTipo();

    if ($tipo != $tipo_filter)
        return;
    $array = array('id' => $id, 'tipo' => $obj->GetTipo());

    $obj_prs->Set($_array['id_proceso']);
    $proceso = $obj_prs->GetNombre();
    $proceso .= ", " . $Ttipo_proceso_array[$obj_prs->GetTipo()];

    ++$cant_show;

    $type = $obj_signal->get_type($array);

    $id_usuario = $obj->GetIdUsuario();
    $email = $obj_user->GetEmail($id_usuario);
    $usuario = $email['nombre'];
    if (!empty($email['cargo']))
        $usuario .= ", {$email['cargo']}";
    $registro_date_init = odbc2time_ampm($obj->get_kronos());

    $email = $obj_user->GetEmail($_array['id_usuario']);
    $registro = $email['nombre'];
    if (!empty($email['cargo']))
        $resgistro .= ", {$email['cargo']}";
    $registro_date = odbc2time_ampm($_array['cronos']);

    $fecha_inicio = odbc2date($obj->GetFechaInicioReal());
    $fecha_fin = odbc2date($obj->GetFechaFinPlan());

    $alarm = $obj_signal->get_alarm($_array);

    $estado = $estado_hallazgo_array[$_array['estado']];
    $estado = !empty($estado) ? $estado : "IDENTIFICADO";
?>

    <input type="hidden" id="proceso_<?= $id ?>" value="<?= $proceso ?>" />

    <input type="hidden" id="nota_<?= $id ?>" value="<?= textparse(purge_html($obj->GetNombre()), true) ?>" />
    <input type="hidden" id="tipo_<?= $id ?>" value="<?= $Ttipo_nota_array[$tipo] ?>" />
    <input type="hidden" id="estado_<?= $id ?>" value="<?= $estado ?>" />
    <input type="hidden" id="registro_date_init_<?= $id ?>" value="<?= $registro_date_init ?>" />
    <input type="hidden" id="date_interval_<?= $id ?>" value="<?= "Desde $fecha_inicio  hasta  $fecha_fin" ?>" />

    <input type="hidden" id="reg_fecha_<?= $id ?>" value="<?= odbc2date($_array['reg_fecha']) ?>" />

    <input type="hidden" id="registro_<?= $id ?>" value="<?= $registro ?>" />

    <input type="hidden" id="observacion_<?= $id ?>" value="<?= textparse(purge_html($_array['observacion']), true) ?>" />
    <input type="hidden" id="registro_date_<?= $id ?>" value="<?= $registro_date ?>" />
    <input type="hidden" id="usuario_<?= $id ?>" value="<?= $usuario ?>" />
    <input type="hidden" id="descripcion_<?= $id ?>" value="<?= textparse(purge_html($obj->GetDescripcion()), true) ?>" />


    <div class="box box-<?= $type ?> box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $estado ?> <?= odbc2date($array['reg_fecha']) ?></h3>
            
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" onclick="ShowContentNota(<?= $id ?>, <?= $_array['id_proceso'] ?>)">
                    <i class="fa fa-wrench"></i>
                </button>
            </div>
            <!-- /.box-tools -->
        </div>

        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-2">
                    <div class="alarm-cicle bg-<?= $alarm ?>"></div>
                </div>
                <div class="col-10">
                    <div class="row">
                        <div class="col-6 border-left">
                            <div class="description-block">
                                <div class="description-header">Identificado:</div>
                                <div class="description-text">
                                    <?= $fecha_inicio ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 border-left">
                            <div class="description-block">
                                <div class="description-header">Estado:</div>
                                <div class="description-text">
                                    <?= $estado ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <?php if (array_key_exists($_array['id_proceso'], $array_proceso_conected) && $_array['id_proceso'] != $_SESSION['local_proceso_id']) { ?>
                    <img class='img-rounded icon' src='<?= _SERVER_DIRIGER ?>img/transmit.ico' alt='requiere transmisión de datos' style="margin-right: 5px;" />
                <?php } ?>
                <span class="prs-text"><?= $proceso ?></span><br />
                <?= textparse(purge_html($obj->GetDescripcion())) ?>
            </div>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
<?php } ?>