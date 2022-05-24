<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

require_once "../php/inc.php";
session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$not_load_config_class= true;
require_once "../php/class/config.class.php";
require_once "../php/class/config_ldap.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/_config_prs.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'form';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$error= null;

global $config;
global $clink;
$config= new _Tconfig_ldap($clink);

if ($signal == 'execute') {
    $config->set_servers();
} else {
    $config->get_servers();
}

$obj_prs= new Tproceso($clink);
$result= $obj_prs->listar();

$cant_prs= 0;
while ($row= $clink->fetch_array($result)) {
    if (!boolean($row['if_entity']) && empty($row['cronos_syn']))
        continue;
    $array= array('id'=>$row['_id'], 'nombre'=>$row['_nombre']);
    ++$cant_prs;
    $array_procesos[$row['_id']]= $array;
}

if ($signal == 'form' || !is_null($error)) {
    $url_page= "../form/foptions_ldap.php?version=".$_SESSION['update_no']."&action=$action";
    add_page($url_page, $action, 'f');
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>OPCIONES DE CONFIGURACIÓN</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/moment.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    .form-group table {
        border: none;
    }

    .form-group table td {
        padding: 4px 4px;
    }

    .win-board .panel-body.output-board {
        max-height: 400px;
        overflow-y: scroll;
    }

    .btn.btn-info {
        margin-bottom: 5px;
        margin-left: 5px;
    }
    </style>

    <script type="text/javascript">
    var ifnew;
    var oId;
    var $table;
    var row_ldap;

    var arrayIndex = new Array();
    var maxIndex = -1;
    var index = -1;

    function set_radius() {
        if ($('#use_radius_login').is(':checked')) {
            $('#servers_radius').prop('disabled', false);
            $('#secret').prop('disabled', false);
            $('#admin_radius').prop('disabled', false);
            $('#passwd_radius').prop('disabled', false);
            $('#btn-radius').show();
        } else {
            $('#servers_radius').prop('disabled', 'disabled');
            $('#secret').prop('disabled', 'disabled');
            $('#admin_radius').prop('disabled', 'disabled');
            $('#passwd_radius').prop('disabled', 'disabled');
            $('#btn-radius').hide();
        }
    }

    function set_ldap() {
        if ($('#use_ldap_not_login').is(':checked'))
            $('#btn-ldap').hide();
        else
            $('#btn-ldap').show();
    }

    function new_domain() {
        ifnew = true;
        displayFloatingDiv('div-ajax-panel-ldap', '', 70, 0, 20, 5);

        $('#servers').val('');
        $('#domain').val('');
        $('#port').val(389);
        $('#cn').val('');
        $('#proceso').val(0);
        $('#admin').val('');
        $('#passwd').val('');

        $('#ldap_ssl').prop('checked', false);
        $('#ldap_tls').prop('checked', false);
        $('#ldap_utf8').prop('checked', true);

        $('#use_radius_login').is(':checked', false);
        $('#use_ldap_not_login').is(':checked', false);
        set_radius();

        $('#servers_radius').val('');
        $('#secret').val('');
        $('#admin_radius').val('');
        $('#passwd_radius').val('');
    }

    function edit_domain(id) {
        new_domain();

        ifnew = false;
        oId = id;

        $('#servers').val($('#servers' + oId).val());
        $('#domain').val($('#domain' + oId).val());
        $('#port').val($('#port' + oId).val());
        $('#cn').val($('#cn' + oId).val());
        $('#proceso').val($('#proceso' + oId).val());
        $('#admin').val($('#admin' + oId).val());
        $('#passwd').val($('#passwd' + oId).val());

        $('#ldap_ssl').prop('checked', parseInt($('#ldap_ssl' + oId).val()) == 1 ? true : false);
        $('#ldap_tls').prop('checked', parseInt($('#ldap_tls' + oId).val()) == 1 ? true : false);
        $('#ldap_utf8').prop('checked', parseInt($('#ldap_utf8' + oId).val()) == 1 ? true : false);

        $('#servers_radius').val($('#servers_radius' + oId).val());
        $('#secret').val($('#secret' + oId).val());
        $('#admin_radius').val($('#admin_radius' + oId).val());
        $('#passwd_radius').val($('#passwd_radius' + oId).val());

        $('#use_radius_login').prop('checked', parseInt($('#use_radius_login' + oId).val()) == 1 ? true : false);
        $('#use_ldap_not_login').prop('checked', parseInt($('#use_ldap_not_login' + oId).val()) == 1 ? true : false);

        set_radius();
    }

    function add_domain() {
        if (!validar_ldap())
            return;
        if (!validar_radius())
            return;

        var id;
        var btn = '';
        var servers = $('#servers').val();
        var domain = $('#domain').val();
        var port = $('#port').val();
        var cn = $('#cn').val();
        var ssl = $('#ldap_ssl').is(':checked') ? 1 : 0;
        var tls = $('#ldap_tls').is(':checked') ? 1 : 0;
        var utf8 = $('#ldap_utf8').is(':checked') ? 1 : 0;
        var admin = $('#admin').val();
        var passwd = $('#passwd').val();
        var id_proceso = parseInt($('#proceso').val());
        var proceso = array_procesos[id_proceso];
        var btn_test = '';

        var servers_radius = $('#servers_radius').val();
        var use_radius_login = $('#use_radius_login').is(':checked') ? 1 : 0;
        var use_ldap_not_login = $('#use_ldap_not_login').is(':checked') ? 1 : 0;
        var admin_radius = $('#admin_radius').val();;
        var passwd_radius = $('#passwd_radius').val();
        var secret = $('#secret').val();

        if (ifnew) {
            ++numero;
            oId = numero;
            $('#cant_').val(numero);
        }

        id = oId;

        <?php if ($action == 'add' || $action == 'edit') { ?>
        btn = '' +
            '<a href="#" class="btn btn-warning btn-sm" title="modificar datos de servidor" onclick="edit_domain(' +
            oId + ');">' +
            '<i class="fa fa-edit"></i>Editar' +
            '</a>' +
            '<a href="#" class="btn btn-danger btn-sm" title="eliminar conexión a servidor" onclick="delete_domain(' +
            oId + ');">' +
            '<i class="fa fa-trash"></i>Eliminar' +
            '</a>' +
            '';
        <?php } ?>

        servers = servers + '<input type="hidden" id="servers' + oId + '" name="servers' + oId + '" value="' + servers +
            '" />' +
            '';
        domain = domain + '<input type="hidden" id="domain' + oId + '" name="domain' + oId + '" value="' + domain +
            '" />' +
            '';
        port = port + '<input type="hidden" id="port' + oId + '" name="port' + oId + '" value="' + port + '" />' +
            '<input type="hidden" id="ldap_ssl' + oId + '" name="ldap_ssl' + oId + '" value="' + ssl + '" />' +
            '<input type="hidden" id="ldap_tls' + oId + '" name="ldap_tls' + oId + '" value="' + tls + '" />' +
            '<input type="hidden" id="ldap_utf8' + oId + '" name="ldap_utf8' + oId + '" value="' + utf8 + '" />' +
            '<input type="hidden" id="admin' + oId + '" name="admin' + oId + '" value="' + admin + '" />' +
            '<input type="hidden" id="passwd' + oId + '" name="passwd' + oId + '" value="' + passwd + '" />' +

            '';
        cn = cn + '<input type="hidden" id="cn' + oId + '" name="cn' + oId + '" value="' + cn + '" />' +
            '';
        proceso = proceso + '<input type="hidden" id="proceso' + oId + '" name="proceso' + oId + '" value="' +
            id_proceso + '" />' +
            '';
        radius = servers_radius + '<input type="hidden" id="servers_radius' + oId + '" name="servers_radius' + oId +
            '" value="' + servers_radius + '" />' +
            '<input type="hidden" id="use_radius_login' + oId + '" name="use_radius_login' + oId + '" value="' +
            use_radius_login + '" />' +
            '<input type="hidden" id="use_ldap_not_login' + oId + '" name="use_ldap_not_login' + oId + '" value="' +
            use_ldap_not_login + '" />' +
            '<input type="hidden" id="secret' + oId + '" name="secret' + oId + '" value="' + secret + '" />' +
            '<input type="hidden" id="admin_radius' + oId + '" name="admin_radius' + oId + '" value="' + admin_radius +
            '" />' +
            '<input type="hidden" id="passwd_radius' + oId + '" name="passwd_radius' + oId + '" value="' +
            passwd_radius + '" />' +
            '';

        if (use_radius_login)
            btn_test += '<button type="button" class="btn btn-info" onclick="test_radius(' + oId + ')">RADIUS</button>';
        if (!use_ldap_not_login)
            btn_test += '<button type="button" class="btn btn-info " onclick="test_ldap(' + oId + ')">LDAP</button>';
        btn_test += '';

        if (ifnew) {
            index = ++maxIndex;
            arrayIndex['-' + oId] = index;

            $table.bootstrapTable('insertRow', {
                index: index,
                row: {
                    id: id,
                    btn: btn,
                    servers: servers,
                    domain: domain,
                    port: port,
                    cn: cn,
                    proceso: proceso,
                    radius: radius,
                    btn_test: btn_test
                }
            });
        }

        if (!ifnew) {
            index = arrayIndex['-' + oId];

            $table.bootstrapTable('updateRow', {
                index: index,
                row: {
                    id: id,
                    btn: btn,
                    servers: servers,
                    domain: domain,
                    port: port,
                    cn: cn,
                    proceso: proceso,
                    radius: radius,
                    btn_test: btn_test
                }
            });
        }

        CloseWindow('div-ajax-panel-ldap');
    }

    function validar_ldap() {
        var text;
        if (!Entrada($('#servers').val())) {
            $('#servers').focus(focusin($('#servers')));
            alert(
                "Debe especificar el número IP o nombre DNS del servidor LDAP o de al menos uno de los  Controladores de Dominio");
            return false;
        }
        if (!Entrada($('#domain').val())) {
            $('#domain').focus(focusin($('#domain')));
            alert("Debe especificar el dominio al que pertenece el Directorio Activo");
            return false;
        }
        if (!Entrada($('#cn').val()) && !Entrada($('#cn').val())) {
            $('#cn').focus(focusin($('#cn')));
            alert("Debe especificar la cadena de conexión LDAP.");
            return false;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert("Debe especificar la Unidad Organizativa al que corresponde este en el Directorio Activo.");
            return false;
        }
        if (!Entrada($('#admin').val())) {
            $('#admin').focus(focusin($('#admin')));
            alert("Debe especificar el nombre de usuario con nivel de administrador del Dominio");
            return false;
        }
        if (!Entrada($('#passwd').val())) {
            $('#passwd').focus(focusin($('#passwd')));
            alert("Debe especificar la clave de acceso del usuario administrador de dominio");
            return false;
        }

        return true;
    }

    function validar_radius() {
        if (!$('#use_radius_login').is(':checked'))
            return true;

        if (!Entrada($('#domain').val())) {
            $('#domain').focus(focusin($('#domain')));
            alert("Debe especificar el dominio al que pertenece el Directorio Activo");
            return false;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert("Debe especificar la Unidad Organizativa al que corresponde este en el Directorio Activo.");
            return false;
        }
        if (!Entrada($('#servers_radius').val())) {
            $('#servers_radius').focus(focusin($('#servers_radius')));
            alert("Debe especificar el número IP o nombre DNS del servidor para la autenticación RADIUS");
            return false;
        }
        if (!Entrada($('#secret').val()) && !Entrada($('#secret').val())) {
            $('#secret').focus(focusin($('#secret')));
            alert("Debe especificar la cadena secreta para la autenticación RADIUS.");
            return false;
        }
        if (!Entrada($('#admin_radius').val())) {
            $('#admin_radius').focus(focusin($('#admin_radius')));
            alert(
                "Debe especificar el nombre de un usuario con nivel de administrador del Dominio permitido en el servidor RADIUS");
            return false;
        }
        if (!Entrada($('#passwd_radius').val())) {
            $('#passwd_radius').focus(focusin($('#passwd_radius')));
            alert(
                "Debe especificar la clave de acceso del usuario administrador de dominio permitido en el servidor RADIUS");
            return false;
        }

        return true;
    }

    function test_ldap(index) {
        if (index == -1 && !validar_ldap())
            return;

        var servers = index == -1 ? encodeURIComponent($('#servers').val()) : encodeURIComponent($('#servers' + index)
            .val());
        var domain = index == -1 ? encodeURIComponent($('#domain').val()) : encodeURIComponent($('#domain' + index)
        .val());
        var port = index == -1 ? $('#port').val() : $('#port' + index).val();
        var cn = index == -1 ? encodeURIComponent($('#cn').val()) : encodeURIComponent($('#cn' + index).val());
        var admin = index == -1 ? encodeURIComponent($('#admin').val()) : encodeURIComponent($('#admin' + index).val());
        var passwd = index == -1 ? encodeURIComponent($('#passwd').val()) : encodeURIComponent($('#passwd' + index)
        .val());
        var ssl = index == -1 ? $('#ldap_ssl').is(':checked') ? 1 : 0 : $('#ldap_ssl' + index).val();
        var tls = index == -1 ? $('#ldap_tls').is(':checked') ? 1 : 0 : $('#ldap_tls' + index).val();
        var utf8 = index == -1 ? $('#ldap_utf8').is(':checked') ? 1 : 0 : $('#ldap_utf8' + index).val();

        var url = '../form/ajax/test_ldap.ajax.php?servers=' + servers + '&port=' + port + '&domain=' + domain +
            '&cn=' + cn;
        url += '&admin=' + admin + '&passwd=' + passwd + '&ssl=' + ssl + '&tls=' + tls + '&utf8=' + utf8;

        var capa = 'div-ajax-body';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);

        displayFloatingDiv('div-ajax-panel', '', 60, 0, 15, 10);
    }

    function test_radius(index) {
        if ((index != -1 && !parseInt($('#use_radius_login' + index).val())) || (index == -1 && !$('#use_radius_login')
                .is(':checked'))) {
            alert("La seguridad RADIUS no esta actividada. Edite la configuración y active la seguridad RADIUS.");
            return;
        }
        if (index == -1 && !validar_radius())
            return;

        var servers = index == -1 ? encodeURIComponent($('#servers_radius').val()) : encodeURIComponent($(
            '#servers_radius' + index).val());
        var domain = index == -1 ? encodeURIComponent($('#domain').val()) : encodeURIComponent($('#domain' + index)
        .val());
        var secret = index == -1 ? encodeURIComponent($('#secret').val()) : encodeURIComponent($('#secret' + index)
        .val());
        var admin = index == -1 ? encodeURIComponent($('#admin_radius').val()) : encodeURIComponent($('#admin_radius' +
            index).val());
        var passwd = index == -1 ? encodeURIComponent($('#passwd_radius').val()) : encodeURIComponent($(
            '#passwd_radius' + index).val());

        var url = '../form/ajax/test_radius.ajax.php?servers=' + servers + '&secret=' + secret;
        url += '&admin=' + admin + '&passwd=' + passwd + '&domain=' + domain;

        var capa = 'div-ajax-body';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);

        displayFloatingDiv('div-ajax-panel', '', 60, 0, 15, 10);
    }

    function delete_domain(id) {
        function _this() {
            var ids = new Array();
            ids.push(id);

            $table.bootstrapTable('remove', {
                field: 'id',
                values: ids
            });

            for (i = id; i <= $('#cant_').val(); ++i) {
                if (arrayIndex['-' + i] == 'undefined')
                    continue;
                arrayIndex['-' + i] = arrayIndex['-' + i] ? arrayIndex['-' + i] - 1 : 0;
                maxIndex = arrayIndex['-' + i];
            }
            arrayIndex['-' + id] = 'undefined';
        }

        confirm('Realmente desea eliminar a este Directorio Activo?', function(ok) {
            if (!ok)
                return false;
            else
                _this();
        });
    }

    function validar() {
        var form = document.forms[0];

        form.action = 'foptions_ldap.php?version=&action=add&signal=execute';
        form.submit();
    }

    function test_mail_ldap() {
        if ($('#ldap_login').is(':checked'))
            $('#mail_use_ldap').attr('disabled', false);
        else {
            $('#mail_use_ldap').attr('checked', false);
            $('#mail_use_ldap').attr('disabled', true);
        }
    }

    function active_mail_config() {
        if ($('#off_mail_server').is(':checked'))
            $('#config_mail').attr('disabled', true);
        else
            $('#config_mail').attr('disabled', false);
    }
    </script>

    <script type="text/javascript">
    var array_procesos = Array();
    <?php
    reset($array_procesos);;
    foreach ($array_procesos as $row) {
    ?>
    array_procesos[<?=$row['id']?>] = '<?=$row['nombre']?>';
    <?php } ?>
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        InitDragDrop();

        $table = $("#table");
        $table.bootstrapTable('append', row_ldap);

        $('#ldap_ssl').click(function() {
            if ($('#ldap_ssl').is(':checked')) {
                if ($('#ldap_tls').is(':checked'))
                    $('#ldap_tls').prop('checked', false);
            }
        });
        $('#ldap_tls').click(function() {
            if ($('#ldap_tls').is(':checked')) {
                if ($('#ldap_ssl').is(':checked'))
                    $('#ldap_ssl').prop('checked', false);
            }
        });
        $('#block_no_ldap_login').click(function() {
            if ($('#block_no_ldap_login').is(':checked'))
                $('#ldap_login').prop('checked', true);
        });

        <?php
                if ($signal == 'execute') {
                    if (is_null($error)) {
                ?>
        text =
            "Para que algunos cambios se hagan efectivos deberá salir y entrar nuevamente al sistema Diriger. ¿Desea salir ahora del sistema?";
        confirm(text, function(ok) {
            if (!ok)
                self.location.href = '../html/background.php?csfr_token=<?=$_SESSION['csfr_toke']?>&';
            else
                parent.location.href = '../php/exit.php';
        });

        <?php } else { ?>
        alert("<?=$error?>");
        <?php } ?>

        <?php } else { ?>
        test_mail_ldap();
        active_mail_config();
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">CONFIGURACIÓN DEL SISTEMA: AUTENTICACIÓN LDAP / RADIUS</div>
                <div class="card-body">

                    <form action='javascript:validar()' class='form-horizontal' method=post>
                        <input type="hidden" name="menu" value="options" />

                        <?php if ($signal == 'form' || !is_null($error)) { ?>
                        <div class="checkbox label label-danger">
                            <label onclick="test_mail_ldap()">
                                <input type="checkbox" id="block_no_ldap_login" name="block_no_ldap_login" value="1"
                                    <?php if ($config->block_no_ldap_login) echo "checked" ?> />
                                <strong>Bloquear el acceso a Diriger</strong> cuando no exista Controlador de Dominio
                                disponible.
                            </label>
                        </div>

                        <div class="checkbox">
                            <label onclick="test_mail_ldap()">
                                <input type="checkbox" id="ldap_login" name="ldap_login" value="1"
                                    <?php if ($config->ldap_login) echo "checked" ?> />
                                Conectarse a servidores LDAP o RADIUS de una estructura de Directorios Activos.
                                Los usuarios primero seran autenticados por el Directorio Activo.
                                Se mantendra la unicidad en los nombres completos y de nombre de usuarios.
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="mail_use_ldap" name="mail_use_ldap" value="1"
                                    <?php if ($config->mail_use_ldap) echo "checked" ?> />
                                El servidor de correo esta integrado con el Directorio Activo. Coinciden usuario y
                                dominio.
                            </label>
                        </div>

                        <div id="toolbar">
                            <div class="btn-toolbar" role="form">
                                <?php if ($action == 'add') { ?>
                                <button type="button" class="btn btn-primary" onclick="new_domain();">
                                    <i class="fa fa-plus"></i>Nuevo Dominio
                                </button>
                                <?php } ?>
                            </div>
                        </div>

                        <script type="text/javascript">
                        var row_ldap = [
                            <?php
                                    $i = 0;
                                    foreach ($config->array_ldap_servers as $server) {
                                        if (empty($server['proceso']) || !strlen($server['proceso']) || !boolean($server['proceso']))
                                            continue;
                                        ++$i;
                                       if ($i > 1)
                                           echo ", ";
                                        ?> {
                                id: <?=$i?>,

                                <?php if ($action == 'add' || $action == 'edit') { ?>
                                btn: '' +
                                    '<a href="#" class="btn btn-warning btn-sm" title="modificar datos de servidor" onclick="edit_domain(<?=$i?>);">' +
                                    '<i class="fa fa-edit"></i>Editar' +
                                    '</a>' +
                                    '<a href="#" class="btn btn-danger btn-sm" title="eliminar conexión a servidor" onclick="delete_domain(<?=$i?>);">' +
                                    '<i class="fa fa-trash"></i>Eliminar' +
                                    '</a>' +
                                    '',
                                <?php } ?>

                                servers: '<?=$server['servers']?>' +
                                    '<input type="hidden" id="servers<?=$i?>" name="servers<?=$i?>" value="<?=$server['servers']?>" />' +
                                    '',
                                domain: '<?=$server['domain']?>' +
                                    '<input type="hidden" id="domain<?=$i?>" name="domain<?=$i?>" value="<?=$server['domain']?>" />' +
                                    '',
                                port: '<?=$server['port']?>' +
                                    '<input type="hidden" id="port<?=$i?>" name="port<?=$i?>" value="<?=$server['port']?>" />' +
                                    '<input type="hidden" id="ldap_utf8<?=$i?>" name="ldap_utf8<?=$i?>" value="<?= boolean($server['utf8']) ? 1 : 0?>"  />' +
                                    '<input type="hidden" id="ldap_ssl<?=$i?>" name="ldap_ssl<?=$i?>" value="<?= boolean($server['ssl']) ? 1 : 0?>"  />' +
                                    '<input type="hidden" id="ldap_tls<?=$i?>" name="ldap_tls<?=$i?>" value="<?= boolean($server['tls']) ? 1 : 0 ?>"  />' +
                                    '<input type="hidden" id="admin<?=$i?>" name="admin<?=$i?>" value="<?=$server['admin']?>" />' +
                                    '<input type="hidden" id="passwd<?=$i?>" name="passwd<?=$i?>" value="<?=$server['passwd']?>" />' +
                                    '',
                                cn: '<?=$server['cn']?>' +
                                    '<input type="hidden" id="cn<?=$i?>" name="cn<?=$i?>" value="<?=$server['cn']?>" />' +
                                    '',
                                proceso: '<?=$array_procesos[$server['proceso']]['nombre']?>' +
                                    '<input type="hidden" id="proceso<?=$i?>" name="proceso<?=$i?>" value="<?=$server['proceso']?>" />' +
                                    '',
                                radius: '<?=$server['servers_radius']?>' +
                                    '<input type="hidden" id="servers_radius<?=$i?>" name="servers_radius<?=$i?>" value="<?=$server['servers_radius']?>" />' +
                                    '<input type="hidden" id="use_radius_login<?=$i?>" name="use_radius_login<?=$i?>" value="<?=$server['use_radius_login']?>" />' +
                                    '<input type="hidden" id="use_ldap_not_login<?=$i?>" name="use_ldap_not_login<?=$i?>" value="<?=$server['use_ldap_not_login']?>" />' +
                                    '<input type="hidden" id="secret<?=$i?>" name="secret<?=$i?>" value="<?=$server['secret']?>" />' +
                                    '<input type="hidden" id="admin_radius<?=$i?>" name="admin_radius<?=$i?>" value="<?=$server['admin_radius']?>" />' +
                                    '<input type="hidden" id="passwd_radius<?=$i?>" name="passwd_radius<?=$i?>" value="<?=$server['passwd_radius']?>" />' +
                                    '',
                                btn_test: '' +
                                    <?php if ($server['use_radius_login']) { ?> '<button type="button" class="btn btn-info" onclick="test_radius(<?=$i?>)">RADIUS</button>' +
                                    <?php } ?>
                                <?php if (!$server['use_ldap_not_login']) { ?> '<button type="button" class="btn btn-info" onclick="test_ldap(<?=$i?>)">LDAP</button>' +
                                <?php } ?> ''

                            }
                            <?php } ?>
                        ];
                        </script>


                        <table id="table" class="table table-striped" data-toggle="table" data-height="400"
                            data-toolbar="#toolbar" data-search="true" data-row-style="rowStyle">
                            <thead>
                                <tr>
                                    <th data-field="id">No.</th>
                                    <th data-field="btn"></th>
                                    <th data-field="servers">SERVIDOR<br />LDAP</th>
                                    <th data-field="domain">DOMINIO</th>
                                    <th data-field="port">PUERTO<br />LDAP</th>
                                    <th data-field="cn">CADENA</th>
                                    <th data-field="proceso">UNIDAD ORGANIZATIVA</th>
                                    <th data-field="radius">SERVIDOR<br />RADIUS</th>
                                    <th data-field="btn_test">PROBAR</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>

                        <script type="text/javascript">
                        maxIndex = <?= $i-1 ?>;
                        numero = <?=$i?>;

                        <?php
                        $k= 0;
                        for ($j= 1; $j <= $i; ++$j) {
                        ?>
                        arrayIndex['-' + <?=$j?>] = <?=$k++?>;
                        <?php } ?>
                        </script>

                        <input type="hidden" id="cant_" name="cant_" value="<?=$i?>" />

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.php')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                    <?php } ?>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

    <!-- Conexión LDAP -->
    <div id="div-ajax-panel-ldap" class="ajax-panel card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag">CONEXIÓN LDAP</div>

                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-ajax-panel-ldap');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs" role="tablist">
                <li id="nav-tab1 class="nav-item""><a class="nav-link" href="tab1">Unidad Organizativa</a></li>
                <li id="nav-tab1 class="nav-item""><a class="nav-link" href="tab2">Autenticación LDAP</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab3">Autenticación RADIUS</a></li>
            </ul>

            <!-- tab 1 Unidad Organizativa -->
            <div class="tabcontent" id="tab1">
                <div class="form-horizontal">
                    <div class="form-group row">

                        <div class="checkbox col-12" style="margin-top: 20px;">
                            <label>
                                <input type="checkbox" id="use_radius_login" name="use_radius_login"
                                    onclick="set_radius()" value="1"
                                    <?=$config->use_radius_login ? "checked='checked'" : ""?> />
                                Utilizar primero autenticación basada en servidores RADIUS
                            </label>
                        </div>

                        <div class="checkbox col-12">
                            <label>
                                <input type="checkbox" id="use_ldap_not_login" name="use_ldap_not_login"
                                    onclick="set_ldap()" value="1"
                                    <?=$config->use_ldap_not_login ? "checked='checked'" : ""?> />
                                Se cierra el acceso al servidor LDAP. Solo se hace la autenticación RADIUS
                            </label>
                        </div>

                    </div>

                    <div class="form-group row col-12">
                        <div class="row col-12">
                            <label class="col-form-label col-2">
                                Dominio:
                            </label>
                            <div class="col-10">
                                <input type="text" class="form-control" id="domain" name="domain" value="" />
                            </div>
                        </div>
                        <div class="col-12">
                            <span class="text text-info">Ejemplo: Sí el servidor es DIRIGER.gemus.cu. Entonces el
                                dominio es <strong>gemus.cu</strong></span>
                        </div>
                    </div>

                    <div class="form-group row col-12">
                        <label class="col-form-label col-3">
                            Unidad Organizativa:
                        </label>
                        <div class="col-9">
                            <?php
                                $obj_prs= new Tproceso($clink);
                                $result= $obj_prs->listar(true, null, 'asc');
                                ?>
                            <select id="proceso" name="proceso" class="form-control input-sm" onchange="refreshp(1)">
                                <option value="0" <?php if (empty($id_select_prs)) echo "selected='selected'";?>>...
                                </option>
                                <?php 
                                $i= 0;
                                while ($row= $clink->fetch_array($result)) { 
                                    if (!boolean($row['_if_entity']) || !empty($row['cronos_syn']))
                                        continue;
                                    ++$i;
                                    $proceso= $row['_nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['_tipo']]."</span>";
                                ?>
                                <option value="<?=$row['_id']?>"
                                    <?php if ($row['_id'] == $id_select_prs) echo "selected='selected'"; ?>>
                                    <?=$proceso?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="n_entities" name="n_entities" value="<?=$i?>" />
            </div> <!-- tab 1 Unidad Organizativa -->

            <!-- tab 2 LDAP -->
            <div class="tabcontent" id="tab2">

                <fieldset class="fieldset">
                    <legend>Servidores LDAP</legend>

                    <div class="form-group row">
                        <div class="row col-12">
                            <label class="col-form-label col-2">
                                Servidor:
                            </label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="servers" name="servers" maxlength="51"
                                    value="" />
                            </div>
                            <label class="col-form-label col-1">Puerto:</label>
                            <div class="col-1">
                                <input type="text" class="form-control" id="port" name="port" value="" />
                            </div>
                        </div>

                        <div class="col-12">
                            <span class="text text-info">Se pueden utilizar varios números separados por espacios.
                                Ejemplo 192.168.0.1 192.168.1.2</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Cadena de conexión:
                        </label>
                        <div class="col-10">
                            <input type="text" class="form-control" id="cn" name="cn" value="" />
                            <label class="text">
                                <span class="text text-info">ou=gemus,dc=gemus,dc=cu ó cn=Users,dc=gemus,dc=cu</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="checkbox col-12">
                            <label>
                                <input type="checkbox" id="ldap_utf8" name="ldap_utf8" value="1" />
                                Aplicar codificación UTF8 a la respuesta del servidor
                            </label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <legend>Usuario (Administrador de Dominio)</legend>

                    <div class="form-group row">
                        <div class="checkbox col-12">
                            <label>
                                <input type="checkbox" id="ldap_ssl" name="ldap_ssl" value="1" />
                                Conexión segura a servidor LDAP. Encriptación <strong>SSL</strong>.
                            </label>
                        </div>
                        <div class="checkbox col-12">
                            <label>
                                <input type="checkbox" id="ldap_tls" name="ldap_tls" value="1" />
                                Conexión segura a servidor LDAP. Encriptación <strong>TLS</strong>.
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="row col-12">
                            <label class="col-form-label col-2">
                                Usuario:
                            </label>
                            <div class="col-4">
                                <input class="form-control" class="form-control" id="admin" name="admin" value=""
                                    autocomplete="no" />
                            </div>

                            <label class="col-form-label col-1">
                                Clave:
                            </label>
                            <div class="col-5">
                                <input type="password" class="form-control" id="passwd" name="passwd" value=""
                                    autocomplete="no" />
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="submit col-md-offset-1" style="margin: 10px 20px;">
                    <button type="button" id="btn-ldap" class="btn btn-info" onclick="test_ldap(-1)">Probar
                        LDAP</button>
                </div>

            </div> <!-- tab 2 LDAP -->


            <!-- tab 3 RADIUS -->
            <div class="tabcontent form-horizontal" id="tab3">
                <legend>Servidores RADIUS</legend>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Servidor(es):
                    </label>
                    <div class="col-9">
                        <input type="text" class="form-control" id="servers_radius" name="servers_radius" maxlength="51"
                            value="" />
                    </div>

                    <div class="col-12">
                        <span class="text text-info">Se puden utilizar varios números separados por espacios. Ejemplo
                            192.168.0.1 192.168.1.2</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Secreto:
                    </label>
                    <div class="col-10">
                        <input type="password" class="form-control" id="secret" name="secret" value="" />
                    </div>
                </div>

                <div class="col-12">
                    <fieldset class="fieldset">
                        <legend>Usuario (Acceso a RADIUS)</legend>

                        <div class="form-horizontal col-12">
                            <div class="form-group row col-12">
                                <label class="col-form-label col-2">
                                    Usuario:
                                </label>
                                <div class="col-4">
                                    <input class="form-control" class="form-control" id="admin_radius"
                                        name="admin_radius" value="" autocomplete="no" />
                                </div>

                                <label class="col-form-label col-1">
                                    Clave:
                                </label>
                                <div class="col-sm-5 col-md-5">
                                    <input type="password" class="form-control" id="passwd_radius" name="passwd_radius"
                                        value="" autocomplete="no" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="submit col-md-offset-1" style="margin: 10px 20px;">
                    <button id="btn-radius" type="button" class="btn btn-info" onclick="test_radius(-1)">Probar
                        RADIUS</button>
                </div>
            </div> <!-- tab 3 RADIUS -->


            <div id="_submit" class="btn-block btn-app">
                <?php if ($action == 'add') { ?>
                <button class="btn btn-primary" type="button" onclick="add_domain()">Aceptar</button>
                <?php } ?>
                <button class="btn btn-warning" type="button"
                    onclick="CloseWindow('div-ajax-panel-ldap')">Cerrar</button>
                <button class="btn btn-danger" type="button"
                    onclick="open_help_window('../help/manual.php')">Ayuda</button>
            </div>

        </div> <!-- Conexión LDAP -->
    </div>


    <div id="div-ajax-panel" class="ajax-panel card card-primary win-board" data-bind="draganddrop">
        <div class="card-header row">
            <div id="win-title" class="panel-title ajax-title clear col-11 m-0  win-drag">
                CONEXIÓN A SERVIDOR LDAP
            </div>
            <div class="col-1 m-0">
                <div class="close">
                    <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
        <div id="div-ajax-body" class="card-body output-board">

        </div>
    </div>

</body>

</html>