<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../php/config.inc.php";
require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";

require_once "../php/class/proceso.class.php";

$obj = new Tusuario($clink);

$obj->Set($_SESSION['id_usuario']);
$cant= $obj->if_unique_username();

$id_usuario_array = array();
$array_procesos = array();

if (empty($cant))
    $id_usuario_array[] = $_SESSION['id_entity'];
else
    foreach ($obj->array_procesos as $prs)
        $id_usuario_array[] = $prs['id_usuario'];

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear(date('Y'));
$obj_prs->get_procesos_by_user(null, null, null, null, null, $id_usuario_array);

foreach ($obj_prs->array_procesos as $prs)
    $array_procesos[$prs['id']] = $prs['id'];

$action = !empty($_GET['action']) ? $_GET['action'] : 'add';
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$year = !empty($_GET['year']) ? urldecode($_GET['year']) : date('Y');

$id_user_source= !empty($_POST['responsable-source']) ? $_POST['responsable-source'] : null;
$id_proceso_source= !empty($_POST['proceso-source']) ? $_POST['proceso-source'] : null;

$id_user_target= !empty($_POST['responsable-target']) ? $_POST['responsable-target'] : null;
$id_proceso_target= !empty($_POST['proceso-target']) ? $_POST['proceso-target'] : null;

$fecha= !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
$copy_user= !empty($_POST['copy_user']) ? $_POST['copy_user'] : null;

if ($action == 'execute') {
    $obj_user= new Tusuario($clink);

    $email= $obj_user->GetEmail($id_user_source);
    $nombre_user_source= $email['nombre'];
    if (!empty($email['cargo'])) 
        $nombre_user_source.= ', '.$email['cargo'];

    $email= $obj_user->GetEmail($id_user_target);
    $nombre_user_target= $email['nombre'];
    if (!empty($email['cargo'])) 
        $nombre_user_target.= ', '.$email['cargo'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>TRANSFERENCIA DE FUNCIONES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">

    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function refresh_ajax_users(index) {
        var id_proceso = $('#proceso-' + index).val();
        var year = $('#year').val();

        if (id_proceso == 0)
            return;

        var user_date_ref = $('#fecha').val();
        if (index == 'source')
            user_date_ref = 0;

        var _url = '../form/ajax/select_users.ajax.php?id_proceso=' + id_proceso + '&year=' + year;
        _url += '&plus_name=' + '-' + index + '&user_date_ref=' + encodeURIComponent(user_date_ref);

        $.ajax({
            //   data:  parametros,
            url: _url,
            type: 'get',
            beforeSend: function() {
                $('#ajax-users-' + index).html('Procesando, espere por favor...');
            },
            success: function(response) {
                $('#ajax-users-' + index).html(response);
            }
        });
    }

    function validar() {
        var form = document.forms[0];

        if ($('#responsable-source').val() == 0) {
            alert("Debe de identificar al usuario fuente");
            return;
        }
        if ($('#responsable-target').val() == 0) {
            alert("Debe de identificar al usuario destino de las tareas y responsabilidades");
            return;
        }
        if ($('#responsable-source').val() == $('#responsable-target').val()) {
            alert("El usuario fuente no puede ser el mismo que el destinatario");
            return;
        }
        if ($('#responsable-source').val() == <?=$_SESSION['id_usuario']?> || $('#responsable-target').val() ==
            <?=$_SESSION['id_usuario']?>) {
            alert("Usted no puede ser el origen ni el destino de traspaso de tareas y responsabilidades");
            return;
        }
        if (!Entrada($('#fecha').val())) {
            $('#fecha').focus(focusin($('#fecha')));
            alert('Introduzca la fecha a partir de la cual se inicia el traspaso.');
            return;
        } else if (!isDate_d_m_yyyyy($('#fecha').val())) {
            $('#fecha').focus(focusin($('#fecha')));
            alert('Fecha con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            return;
        }
        if (!$('#copy_user0').is(':checked') && !$('#copy_user1').is(':checked')) {
            alert(
                "Debe de especificar sí se transfieren las responsabilidaes o solo se copian las tareas y actividades.");
            return;
        }

        form.action = 'fusuario_usuario.php?action=execute';
        parent.app_menu_functions = false;
        form.submit();
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#div_fecha').datepicker({
            format: 'dd/mm/yyyy'
        });

        <?php if ($action != 'execute') { ?>
        refresh_ajax_users('source');
        refresh_ajax_users('target');
        <?php } ?>

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">TRANSFEERENCIA DE FUNCIONES</div>
                <div class="card-body">

                    <form class="form-horizontal" action="javascript:validar()" method="post">
                        <input type="hidden" name="exect" value="add" />
                        <input type="hidden" name="menu" value="fusuario_usuario" />
                        <input type="hidden" name="year" id="year" value="<?=$year?>" />

                        <div class="row col-12">
                            <div class="col-6">
                                <?php if ($action == 'execute') { ?>
                                <div class="alert alert-danger text"><strong>De:</strong> <?=$nombre_user_source?></div>

                                <?php } else { ?>

                                <fieldset>
                                    <legend>De: </legend>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Unidad Organizativa:
                                        </label>
                                        <div class="col-md-9">
                                            <?php
                                            $obj_prs= new Tproceso($clink);
                                            $obj_prs->SetIdEntity($_SESSION['id_entity']);
                                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                                            $array_procesos= $obj_prs->listar_in_order('eq_desc', true);

                                            foreach ($array_procesos as $row) {
                                            ?>
                                                <input type="hidden" id="proceso_code_<?=$row['id']?>"
                                                    name="proceso_code_<?=$row['id']?>" value="<?=$row['id_code'] ?>" />
                                                <input type="hidden" id="proceso_conectado_<?=$row['id']?>"
                                                    name="proceso_conectado_<?=$row['id']?>"
                                                    value="<?=$row['conectado']?>" />
                                            <?php } ?>

                                            <?php reset($array_procesos); ?>

                                            <select id="proceso-source" name="proceso-source"
                                                class="form-control input-sm" onchange="refresh_ajax_users('source')">
                                                <option value="0">... </option>

                                                <?php
                                                foreach ($array_procesos as $row) {
                                                    if ($row['tipo'] < $_SESSION['entity_tipo']) 
                                                        continue;

                                                    $_in_building= ($row['id'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($row['id']) : true;

                                                    $img_conectdo= ($row['conectado'] != _NO_LOCAL && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src=\'../img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;
                                                    $img_tipo= "<img src=\'../img/".img_process($row['tipo'])."\' title=\'".$Ttipo_proceso_array[$row['tipo']]."\' />" ;
                                                    $tips_title= $row['nombre'];

                                                    if ($row['conectado'] != _LAN && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) 
                                                        continue;

                                                    if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
                                                    $obj_prs_tmp= new Tproceso($clink);

                                                    if (!empty($row['id_proceso'])) 
                                                        $obj_prs_tmp->Set($row['id_proceso']);
                                                    $proceso_sup= $img_tipo."&nbsp;".$img_conectdo."<br />";
                                                    $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$row['tipo']].'<br />';
                                                    if (!empty($row['id_proceso'])) 
                                                        $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs_tmp= $obj_prs_tmp->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs_tmp->GetTipo()]."</em>";
                                                    $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$row['conectado']];
                                                    $proceso= $row['nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['tipo']]."</span>";
                                                ?>
                                                <option value="<?=$row['id']?>"
                                                    <?php if ($row['id'] == $id_select_prs) echo "selected='selected'"; ?>>
                                                    <?=$proceso?></option>
                                                <?php } ?>
                                            </select>

                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Usuario:
                                        </label>
                                        <div class="col-md-9">
                                            <div class="ajax-select" id="ajax-users-source">
                                                <select name="responsable-source" id="responsable-source"
                                                    class="form-control">
                                                    <option value=0
                                                        <?php if (empty($id_responsable)) echo "selected='selected'" ?>>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </fieldset>
                                <?php } ?>
                            </div>

                            <div class="col-6">
                                <?php if ($action == 'execute') { ?>
                                <div class="alert alert-info text"><strong>A:</strong> <?=$nombre_user_target?></div>

                                <?php } else { ?>

                                <fieldset>
                                    <legend>A: </legend>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Unidad Organizativa:
                                        </label>
                                        <div class="col-md-9">
                                            <?php reset($array_procesos); ?>

                                            <select id="proceso-target" name="proceso-target"
                                                class="form-control input-sm" onchange="refresh_ajax_users('target')">
                                                <option value="0">... </option>

                                                <?php
                                                foreach ($array_procesos as $row) {
                                                    if ($row['tipo'] < $_SESSION['entity_tipo']) 
                                                        continue;

                                                    $_in_building= ($row['id'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($row['id']) : true;

                                                    $img_conectdo= ($row['conectado'] != _NO_LOCAL && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src=\'../img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;
                                                    $img_tipo= "<img src=\'../img/".img_process($row['tipo'])."\' title=\'".$Ttipo_proceso_array[$row['tipo']]."\' />" ;
                                                    $tips_title= $row['nombre'];

                                                    if ($row['conectado'] != _LAN && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) 
                                                        continue;

                                                    if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
                                                    $obj_prs_tmp= new Tproceso($clink);

                                                    if (!empty($row['id_proceso'])) $obj_prs_tmp->Set($row['id_proceso']);
                                                    $proceso_sup= $img_tipo."&nbsp;".$img_conectdo."<br />";
                                                    $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$row['tipo']].'<br />';
                                                    if (!empty($row['id_proceso'])) 
                                                        $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs_tmp= $obj_prs_tmp->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs_tmp->GetTipo()]."</em>";
                                                    $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$row['conectado']];
                                                    $proceso= $row['nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['tipo']]."</span>";
                                                    ?>
                                                <option value="<?=$row['id']?>"
                                                    <?php if ($row['id'] == $id_select_prs) echo "selected='selected'"; ?>>
                                                    <?=$proceso?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Usuario:
                                        </label>
                                        <div class="col-md-9">
                                            <div class="ajax-select" id="ajax-users-target">
                                                <select name="responsable-target" id="responsable-target"
                                                    class="form-control">
                                                    <option value=0
                                                        <?php if (empty($id_responsable)) echo "selected='selected'" ?>>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-md-6 col-lg-6">
                                Fecha a partir de la cual se realizará la traspaso de tareas y responsabilidades:
                            </label>
                            <div class="col-md-2 col-lg-2">
                                <div id="div_fecha" class="input-group date" data-date-language="es">
                                    <input type="datetime" class="form-control input-sm" id="fecha" name="fecha"
                                        readonly value="<?=odbc2date($fecha)?>">
                                    <span class="input-group-text"><span
                                            class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="checkbox col-md-11 col-lg-11">
                                <label>
                                    <input type="radio" name="copy_user" id="copy_user1"
                                        <?php if ($copy_user) {?>checked="yes" <?php } ?> value="1" />
                                    Las tareas y responsabilidades del primer usuario le serán
                                    <strong>ELIMINADAS</strong> y transferidas al segundo usuario.
                                    <span class="note">
                                        El primer usuario no tendrá estas tareas ni responsabilidades en el sistema a
                                        partir de la fecha elegida.
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="checkbox col-md-11 col-lg-11">
                                <label>
                                    <input type="radio" name="copy_user" id="copy_user0"
                                        <?php if (!is_null($copy_user) && !$copy_user) {?>checked="yes" <?php } ?>
                                        value="0" />
                                    Las tareas del primer usuario le serán <strong>COPIADAS</strong> al segundo.
                                    <span class="note">
                                        El primer usuario seguirá con sus tareas y responsabilidades.
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="container-fluid">

                            <div id="progressbar-0" class="progress-block">
                                <div id="progressbar-0-alert" class="alert alert-success">

                                </div>
                                <div id="progressbar-0-" class="progress progress-striped active">
                                    <div id="progressbar-0-bar" class="progress-bar bg-success"
                                        role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span class="sr-only"></span>
                                    </div>
                                </div>
                            </div>

                            <?php
                                   if ($action == 'execute')
                                       include "../php/user_user.interface.php";
                                ?>
                        </div>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app" style="margin-top:10px;">
                            <?php if ($action != 'execute') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">
                                <?=$action == 'execute' ? "Cerrar" : "Cancelar"?>
                            </button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

</body>

</html>