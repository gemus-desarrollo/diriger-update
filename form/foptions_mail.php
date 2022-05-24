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
require_once "../php/class/config_mail.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/_config_prs.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'form';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$error= null;

global $config;
global $clink;
$config= new _Tconfig_mail($clink);

$obj_config_prs= new Tconfig_synchro($clink);
$obj_config_prs->set_conectado();

if ($signal == 'execute') {
    $config->SetLink($clink);
    $error= $config->set_servers();
    $obj_config_prs->post();
} else
    $config->get_servers();

$array= split_time_seconds($config->time_synchro);
$day_synchro= !empty($array['d']) ? $array['d'] : 0;
$hour_synchro= !empty($array['h']) ? $array['h'] : 0;
$min_synchro= !empty($array['i']) ? $array['i'] : 15;

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

$obj_prs->listar_in_order('asc_desc', false, null, false, 'desc');

$cant_prs= 0;
foreach ($obj_prs->array_procesos as $array) {
    if ($array['id'] == $_SESSION['local_proceso_id'])
        continue;
    if ($array['id'] != $_SESSION['local_proceso_id'] && (empty($array['conectado']) || $array['conectado'] == _LAN))
        continue;
    ++$cant_prs;
    $array_procesos[$array['id']]= $array;
}

$obj_config_prs->listar();

if ($signal == 'form' || !is_null($error)) {
    $url_page= "../form/foptions_mail.php?version=".$_SESSION['update_no']."&action=$action";
    add_page($url_page,$action, 'f');
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

    #div-ajax-body {
        max-height: 400px;
        overflow: scroll;
    }

    ._msg {
        font-weight: 9px;
        color: #ff4000;
        text-align: left;
    }
    </style>

    <script type="text/javascript">
    function validar_mail() {
        var text;
        var pattern = new RegExp('^[a-zA-Z0-9][a-zA-Z0-9-.]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$');

        if ($('#off_mail_server').is(':checked')) {
            return true;
        }

        if (!Entrada($('#hostname').val())) {
            $('#hostname').focus();
            alert("Especifique el nombre DNS del servidor donde está instalado el sistema Diriger");
            return false;
        } else {
            if (!pattern.test($('#hostname').val())) {
                text = "Nombre del servidor de aplicaciones, donde esta instalado el sistema Diriger, no aceptado. ";
                text += "Debe ser el nombre DNS completo, ejemplo (DIRIGER.gemus.local).";
                alert(text);
                return false;
            }
        }
        if (!Entrada($('#email_login_smtp').val())) {
            $('#email_login_smtp').focus();
            alert("Debe de definir el usuario que se conecta al buzón de correo electrónico de salida");
            return false;
        }
        if (!Entrada($('#email_password_smtp').val())) {
            $('#email_password').focus();
            alert("Debe de definir la clave de acceso al buzón de correo electrónico de salida");
            return false;
        }
        if (!Entrada($('#email_login').val())) {
            $('#email_login').focus();
            alert("Debe de definir el usuario que se conecta al buzón de correo electrónico de entrada");
            return false;
        }
        if (!Entrada($('#email_password').val())) {
            $('#email_password').focus();
            alert("Debe de definir la clave de acceso al buzón de correo electrónico de entrada");
            return false;
        }
        if (!Entrada($('#outgoing_mail_server').val()) || !Entrada($('#incoming_mail_server').val())) {
            $('#outgoing_mail_server').focus();
            alert("Debe de especificar los servidores de entrada y salida de correos.");
            return false;
        }

        return true;
    }

    function test_conexion(test) {
        if (!validar_mail())
            return;

        set_same_pop3_smtp();

        $('#outgoing_ssl').val(0);
        if ($('#_outgoing_ssl').is(':checked'))
            $('#outgoing_ssl').val(1);
        if ($('#_outgoing_tls').is(':checked'))
            $('#outgoing_ssl').val(2);
        var outgoing_ssl = $('#outgoing_ssl').val();

        $('#incoming_ssl').val(0);
        if ($('#_incoming_ssl').is(':checked'))
            $('#incoming_ssl').val(1);
        if ($('#_incoming_tls').is(':checked'))
            $('#incoming_ssl').val(2);
        var incoming_ssl = $('#incoming_ssl').val();

        var hostname = encodeURIComponent($('#hostname').val());
        var email_login = encodeURIComponent($('#email_login').val());
        var email_password = encodeURIComponent($('#email_password').val());
        var email_login_smtp = encodeURIComponent($('#email_login_smtp').val());
        var email_password_smtp = encodeURIComponent($('#email_password_smtp').val());

        var incoming_port = $('#incoming_port').val();
        var incoming_protocol = $('#incoming_protocol').val();
        var outgoing_port = $('#outgoing_port').val();

        var fullusermail = $('#fullusermail1').is(':checked') ? 1 : 0;
        var mail_method = $('#mail_method').val();
        var smtp_auth = $('#smtp_auth').is(':checked') ? 1 : 0;
        var smtp_auth_tls = $('#smtp_auth_tls').is(':checked') ? 1 : 0;

        var outgoing_no_tls = $('#outgoing_no_tls').is(':checked') ? 1 : 0;

        var outgoing_mail_server = encodeURIComponent($('#outgoing_mail_server').val());
        var incoming_mail_server = encodeURIComponent($('#incoming_mail_server').val());

        var url = '../form/ajax/test_mail.ajax.php?incoming_port=' + incoming_port + '&incoming_protocol=' +
            incoming_protocol;
        url += '&incoming_ssl=' + incoming_ssl + '&outgoing_port=' + outgoing_port + '&outgoing_ssl=' + outgoing_ssl +
            '&fullusermail=' + fullusermail;
        url += '&test=' + test + '&mail_method=' + mail_method + '&smtp_auth=' + smtp_auth + '&smtp_auth_tls=' +
            smtp_auth_tls;
        url += '&incoming_mail_server=' + incoming_mail_server + '&outgoing_mail_server=' + outgoing_mail_server;
        url += '&email_login=' + email_login + '&email_password=' + email_password + '&email_login_smtp=' +
            email_login_smtp;
        url += '&email_password_smtp=' + email_password_smtp + '&hostname=' + hostname + '&outgoing_no_tls=' +
            outgoing_no_tls;

        var capa = 'div-ajax-body';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);

        displayFloatingDiv('div-ajax-panel', "PRUEBA DE CONEXIÓN DE CORREO ELECTRÓNICO", 60, 0, 10, 10);
    }

    function refresh_chk_synchro() {
        $('#tr_synchro1').css('display', 'none');
        $('#tr_synchro2').css('display', 'none');

        if ($('#type_synchro0').is(':checked')) {
            $('#nav-tab2').css('display', 'none');
            $('#tab2').css('visibility', 'hidden');
        }
        if ($('#type_synchro1').is(':checked')) {
            $('#nav-tab2').css('display', 'inline');
            $('#tab2').css('visibility', 'visible');
            $('#manner2').css('display', 'none');

            $('#tr_synchro1').css('display', 'flex');
            $('#time_synchro').val($('#day_synchro1').val() * 86400 + $('#hour_synchro1').val() * 3600 + $(
                '#min_synchro1').val() * 60);
        }
        if ($('#type_synchro2').is(':checked')) {
            $('#nav-tab2').css('display', 'inline');
            $('#tab2').css('visibility', 'visible');
            $('#manner2').css('display', 'inline');

            $('#tr_synchro2').css('display', 'inline');
            $('#time_synchro').val($('#day_synchro2').val() * 86400 + $('#hour_synchro2').val() * 3600 + $(
                '#min_synchro2').val() * 60);
        }

        for (var i = 1; i <= $('#cant_prs').val(); i++) {
            set_manner(i);
        }
    }

    function set_manner(index) {
        if (parseInt($('#select_manner' + index).val()) == <?=_SYNCHRO_AUTOMATIC_HTTP?>) {
            $('#mcrypt' + index).prop('checked', true);
            $('#mcrypt' + index).attr("readonly", "readonly");
        } else
            $('#mcrypt' + index).attr('readonly', false);

        if ($('#type_synchro1').is(':checked')) {
            if (parseInt($('#select_manner' + index).val()) == 2)
                $('#select_manner' + index).val(1);
            $('#manner2_' + index).css('display', 'none');
            $('#manner3_' + index).css('display', 'none');
        }
        if ($('#type_synchro2').is(':checked')) {
            $('#manner2_' + index).css('display', 'inline');
            $('#manner3_' + index).css('display', 'inline');
        }
        if ($('#select_manner' + index).val() == 0) {
            $('#day_prs' + index).val(0);
            $('#hour_prs' + index).val(0);
            $('#min_prs' + index).val(0);

            return;
        }

        var day, hour, min;

        if ($('#type_synchro1').is(':checked')) {
            day = $('#day_synchro1').val();
            hour = $('#hour_synchro1').val();
            min = $('#min_synchro1').val();
        }
        if ($('#type_synchro2').is(':checked')) {
            day = parseInt($('#day_synchro2').val());
            hour = parseInt($('#hour_synchro2').val());
            min = parseInt($('#min_synchro2').val());
        }

        if (parseInt($('#day_prs' + index).val()) < day)
            $('#day_prs' + index).val(day);
        if (parseInt($('#day_prs' + index).val()) == day && parseInt($('#hour_prs' + index).val()) < hour)
            $('#hour_prs' + index).val(hour);
        if ((parseInt($('#day_prs' + index).val()) == day && parseInt($('#hour_prs' + index).val()) == hour) &&
            parseInt($('#min_prs' + index).val()) < min)
            $('#min_prs' + index).val(min);

        if (parseInt($('#day_prs' + index).val()) == 0 && parseInt($('#hour_prs' + index).val()) == 0 && parseInt($(
                '#min_prs' + index).val()) == 0) {
            $('#day_prs' + index).val(day);
            $('#hour_prs' + index).val(hour);
            $('#min_prs' + index).val(min);
        }
    }

    function set_chk_manner(index) {
        if (parseInt($('#select_manner' + index).val()) == <?=_SYNCHRO_AUTOMATIC_HTTP?>)
            $('#mcrypt' + index).prop('checked', true);
    }

    function validar() {
        var text;
        var form = document.forms[0];

        if (!validar_mail())
            return;

        set_same_pop3_smtp();

        refresh_chk_synchro();

        if (!$('#type_synchro0').is(':checked') && parseInt($('#time_synchro').val()) == 0) {
            text = "No ha especificado la frecuencia con la que se realizará la sincronización del Sistema. ";
            text += "Debe de especificar cada cuanto tiempo el sistema se sincronizará.";
            alert(text);
            return;
        }
        /*
        if (!validar_keys()) return;
        */
        $('#outgoing_ssl').val(0);
        if ($('#_outgoing_ssl').is(':checked'))
            $('#outgoing_ssl').val(1);
        if ($('#_outgoing_tls').is(':checked'))
            $('#outgoing_ssl').val(2);

        $('#incoming_ssl').val(0);
        if ($('#_incoming_ssl').is(':checked'))
            $('#incoming_ssl').val(1);
        if ($('#_incoming_tls').is(':checked'))
            $('#incoming_ssl').val(2);

        form.action = 'foptions_mail.php?version=&action=add&signal=execute';
        form.submit();
    }

    function select_port(id) {
        var form = document.forms[0];

        if (id == 0) {
            if ($('#incoming_protocol').val() == "POP3") {
                $('#110').show();
                $('#995').show();
                $('#993').hide();
                $('#143').hide();
            }
            if ($('#incoming_protocol').val() == 'IMAP') {
                $('#110').hide();
                $('#995').hide();
                $('#993').show();
                $('#143').show();
            }
        }

        if (id == 0 || id == 1) {
            if ($('#incoming_protocol').val() == "POP3")
                $('#incoming_port').val($('#incoming_ssl').is(':checked') ? 995 : 110);
            if ($('#incoming_protocol').val() == "IMAP")
                $('#incoming_port').val($('#incoming_ssl').is(':checked') ? 993 : 143);
        }
        if (id == 1) {
            if ($('#_incoming_ssl').is(':checked'))
                $('#_incoming_tls').attr('checked', false);
            $('#incoming_port').val($('#_incoming_ssl').is(':checked') ? 993 : 143);
        }
        if (id == 2) {
            if ($('#_incoming_tls').is(':checked'))
                $('#_incoming_ssl').attr('checked', false);
            $('#incoming_port').val($('#_incoming_tls').is(':checked') ? 993 : 143);
        }
        if (id == 3) {
            if ($('#_outgoing_ssl').is(':checked'))
                $('#_outgoing_tls').attr('checked', false);
            $('#outgoing_port').val($('#_outgoing_ssl').is(':checked') ? 465 : 25);
        }
        if (id == 4) {
            if ($('#_outgoing_tls').is(':checked'))
                $('#_outgoing_ssl').attr('checked', false);
            $('#outgoing_port').val($('#_outgoing_tls').is(':checked') ? 587 : 25);
        }
    }

    function active_mail_config() {
        if ($("#off_mail_server").is(':checked')) {
            $("#config_mail input").attr("disabled", "disabled");
            $("#config_mail checkbox").attr("disabled", "disabled");
            $("#config_mail select").attr("disabled", "disabled");
            $("#config_mail button").attr("disabled", "disabled");

        } else {
            $("#config_mail input").attr("disabled", false);
            $("#config_mail checkbox").attr("disabled", false);
            $("#config_mail select").attr("disabled", false);
            $("#config_mail button").attr("disabled", false);
        }
    }

    function set_same_pop3_smtp() {
        if ($('#email_user_same_pop3_smtp').is(':checked')) {
            $('#email_login').prop('readonly', true);
            $('#email_password').prop('readonly', true);

            $('#email_login').val($('#email_login_smtp').val());
            $('#email_password').val($('#email_password_smtp').val());

            $('#tr-email-pop3').hide();
        } else {
            $('#email_login').prop('readonly', false);
            $('#email_password').prop('readonly', false);

            $('#tr-email-pop3').show();
        }
    }

    /*
    function validar_keys() {
        var mcrypt_key;
        var text;

        <?php
        $cant_prs= 0;
        reset($array_procesos);
        foreach ($array_procesos as $row) {
            ++$cant_prs;
        ?>
            mcrypt_key= $('#mcrypt_key<?=$cant_prs?>').val();

            if ($('#select_manner<?=$cant_prs?>').val() != <?=_SYNCHRO_NEVER?>) {
                if (Entrada(mcrypt_key)) {
                    if (mcrypt_key.length != 32) {
                        text= "Está incorrecta la llave "+$('#prs<?=$cant_prs?>').val()+". Debe tener 32 caracteres "
                        alert(text);
                        return false;
                    }
                } else {
                    text= "Debe definir la llave para la encriptación de los datos en la sincronización";
                    alert(text);
                    return false;
                }
            }
        <?php } ?>

        return true;
    }
    */
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        InitDragDrop();

        new BootstrapSpinnerButton('spinner-day_synchro1', 0, 31);
        new BootstrapSpinnerButton('spinner-hour_synchro1', 0, 24);
        new BootstrapSpinnerButton('spinner-min_synchro1', 0, 60);

        new BootstrapSpinnerButton('spinner-day_synchro2', 0, 31);
        new BootstrapSpinnerButton('spinner-hour_synchro2', 0, 24);
        new BootstrapSpinnerButton('spinner-min_synchro2', 0, 60);

        set_same_pop3_smtp();
        $('#email_user_same_pop3_smtp').click(function() {
            set_same_pop3_smtp();
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
        refresh_chk_synchro();
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
                <div class="card-header">CONFIGURACIÓN DEL SISTEMA: CORREO ELECTRÓNICO</div>
                <div class="card-body">
        
                    <ul class="nav nav-tabs" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Configuración</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Sincronización de datos y frecuencia</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">: Destinos</a></li>
                    </ul>
        

                    <?php if ($signal == 'form' || !is_null($error)) { ?>
                    <form action='javascript:validar()' class='form-horizontal' method=post>
                        <input type="hidden" name="menu" value="options" />
                        <input type="hidden" name="time_synchro" id="time_synchro" value="<?=$config->time_synchro?>" />

                        <!-- Comunicación -->
                        <div class="tabcontent" id="tab1">

                            <div class="checkbox">
                                <label class="col-lg-12">
                                    <input type="checkbox" id="off_mail_server" name="off_mail_server" value="1"
                                        <?php if (!empty($config->off_mail_server)) echo "checked='checked'" ?>
                                        onchange="active_mail_config()" />
                                    No existe o esta desconectado el servidor de correo electrónico, a traves del cual
                                    se reciben y envian correos
                                </label>
                            </div>

                            <div id="config_mail">
                                <input type="hidden" id="incoming_ssl" name="incoming_ssl"
                                    value="<?=$config->incomingssl?>" />
                                <input type="hidden" id="outgoing_ssl" name="outgoing_ssl"
                                    value="<?=$config->outgoing_ssl?>" />

                                <div class="form-horizontal">
                                    <div class="row col-lg-12">
                                        <div class="col-lg-4">
                                            <label class="text">
                                                Nombre DNS servidor local<br />
                                                <div class="_msg">ejemplo: diriger.gemus.local</div>
                                            </label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="hostname" name="hostname"
                                                value="<?=$config->hostname?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-horizontal">
                                    <div class="row">
                                        <strong> Saliente:</strong>
                                    </div>

                                    <div class="form-group row">
                                        <table class="table table-striped">
                                            <tr>
                                                <td></td>
                                                <td><label class="text">Protocolo</label></td>
                                                <td><label class="text">Servidor de Correo</label></td>
                                                <td><label class="text">Puerto</label></td>
                                                <td><label class="text">SSL</label></td>
                                                <td><label class="text">TLS</label></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <select id="mail_method" name="mail_method" class="form-control">
                                                        <option value="smtp"
                                                            <?php if ($config->mail_method == 'smtp') echo "selected='selected'" ?>>
                                                            SMTP</option>
                                                        <option value="mail"
                                                            <?php if ($config->mail_method == 'mail') echo "selected='selected'" ?>>
                                                            MAIL</option>
                                                        <option value="sendmail"
                                                            <?php if ($config->mail_method == 'sendmail') echo "selected='selected'" ?>>
                                                            SENDMAIL</option>
                                                        <option value="qmail"
                                                            <?php if ($config->mail_method == 'qmail') echo "selected='selected'" ?>>
                                                            QMAIL</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <?php
                                                        $email_server = $version >= 5600 && !empty($_SESSION['email_server_hostname']) ? $_SESSION['email_server_hostname'] : $email_server;
                                                        $outgoing_mail_server= !empty($config->outgoing_mail_server) ? $config->outgoing_mail_server : $_SESSION['email_server'];
                                                        ?>
                                                    <input type="text" class="form-control" id="outgoing_mail_server"
                                                        name="outgoing_mail_server"
                                                        value="<?=$outgoing_mail_server?>" />
                                                </td>
                                                <td>
                                                    <select id="outgoing_port" name="outgoing_port"
                                                        class="form-control">
                                                        <option value=25
                                                            <?php if ((int) $config->outgoing_port == 25) echo "selected='selected'" ?>>
                                                            25</option>
                                                        <option value=465
                                                            <?php if ((int) $config->outgoing_port == 465) echo "selected='selected'" ?>>
                                                            465</option>
                                                        <option value=587
                                                            <?php if ((int) $config->outgoing_port == 587) echo "selected='selected'" ?>>
                                                            587</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="checkbox" id="_outgoing_ssl" value="1"
                                                        <?php if ($config->outgoing_ssl == 1) echo "checked='checked'" ?>
                                                        onclick="select_port(3)" />
                                                </td>
                                                <td>
                                                    <input type="checkbox" id="_outgoing_tls" value="2"
                                                        <?php if ($config->outgoing_ssl == 2) echo "checked='checked'" ?>
                                                        onclick="select_port(4)" />
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info"
                                                        onclick="test_conexion(0)">Probar saliente</button>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div id="tr-email-smtp" class="form-group row">
                                        <div class="col-sm-2">
                                            Usuario:
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                                $email_login_smtp = !empty($config->email_login_smtp) ? $config->email_login_smtp : ($config->email_user_same_pop3_smtp ? $_SESSION['email_user'] : null);
                                                ?>
                                            <input type="text" class="form-control" id="email_login_smtp"
                                                name="email_login_smtp" value="<?= $email_login_smtp ?>" />
                                        </div>
                                        <div class="col-sm-2">
                                            Clave:
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                                $email_password_smtp = !empty($config->email_password_smtp) ? $config->email_password_smtp : ($config->email_user_same_pop3_smtp ? $_SESSION['email_pass'] : null);
                                                ?>
                                            <input type="password" class="form-control" id="email_password_smtp"
                                                name="email_password_smtp" value="<?= $email_password_smtp ?>" />
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="checkbox col-12">
                                            <label class="text">
                                                <input type="checkbox" id="smtp_auth" name="smtp_auth" value="1"
                                                    <?php if ($config->smtp_auth) echo "checked='checked'" ?> />
                                                Autorizar la autenticación SMTP
                                            </label>
                                        </div>
                                        <div class="checkbox col-12">
                                            <label class="text">
                                                <input type="checkbox" id="smtp_auth_tls" name="smtp_auth_tls" value="1"
                                                    <?php if ($config->smtp_auth_tls) echo "checked='checked'" ?> />
                                                Habilitar encriptación TLS automáticamente si el servidor lo soporta
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <hr />

                                <div class="form-horizontal col-12">
                                    <div class="row">
                                        <strong>Entrante:</strong>
                                    </div>

                                    <div class="form-group row">
                                        <table class="table table-striped">
                                            <tr>
                                                <td></td>
                                                <td><label class="text">Protocolo</label></td>
                                                <td><label class="text">Servidor de Correo</label></td>
                                                <td><label class="text">Puerto</label></td>
                                                <td><label class="text">SSL</label></td>
                                                <td><label class="text">TLS</label></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <select id="incoming_protocol" name="incoming_protocol"
                                                        class="form-control" onchange="select_port(0)">
                                                        <option id="pop3" value="POP3"
                                                            <?php if ($config->incoming_protocol == "POP3") echo "selected" ?>>
                                                            POP3</option>
                                                        <option id="imap" value="IMAP"
                                                            <?php if ($config->incoming_protocol == "IMAP") echo "selected" ?>>
                                                            IMAP</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <?php
                                                        $_version = explode('.', PHP_VERSION);
                                                        $version= (int)($_version[0] * 1000 + $_version[1] * 100 + $_version[2]);
                                                        $email_server = $version >= 5600 && !empty($_SESSION['email_server_hostname']) ? $_SESSION['email_server_hostname'] : $_SESSION['email_server'];
                                                        $incoming_mail_server= !empty($config->incoming_mail_server) ? $config->incoming_mail_server : $email_server;
                                                        ?>
                                                    <input type="text" class="form-control" id="incoming_mail_server"
                                                        name="incoming_mail_server"
                                                        value="<?=$incoming_mail_server?>" />
                                                </td>
                                                <td>
                                                    <select id="incoming_port" name="incoming_port"
                                                        class="form-control">
                                                        <option id="110" value=110
                                                            <?php if ((int) $config->incoming_port == 110) echo "selected='selected'" ?>>
                                                            110</option>
                                                        <option id="995" value=995
                                                            <?php if ((int) $config->incoming_port == 995) echo "selected='selected'" ?>>
                                                            995</option>
                                                        <option id="143" value=143
                                                            <?php if ((int) $config->incoming_port == 143) echo "selected='selected'" ?>>
                                                            143</option>
                                                        <option id="993" value=993
                                                            <?php if ((int) $config->incoming_port == 993) echo "selected='selected'" ?>>
                                                            993</option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="_incoming_ssl" value="1"
                                                        <?php if ($config->incoming_ssl == 1) echo "checked='checked'" ?>
                                                        onclick="select_port(1)" />
                                                </td>
                                                <td>
                                                    <input type="checkbox" id="_incoming_tls" value="2"
                                                        <?php if ($config->incoming_ssl == 2) echo "checked='checked'" ?>
                                                        onclick="select_port(2)" />
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info"
                                                        onclick="test_conexion(1)">Probar entrante</button>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="col-12">
                                        <div class="checkbox">
                                            <label class="text">
                                                <input type="checkbox" id="email_user_same_pop3_smtp"
                                                    name="email_user_same_pop3_smtp"
                                                    <?php if ($config->email_user_same_pop3_smtp) echo "checked='checked'"?>
                                                    value="1" />
                                                Las credenciales del usuario de salida y el de entrada son las mismas.
                                            </label>
                                        </div>
                                    </div>

                                    <div id="tr-email-pop3" class="form-group row">
                                        <div class="col-sm-2">
                                            Usuario:
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                                 $email_login= !empty($config->email_login) ? $config->email_login : $_SESSION['email_user'];
                                                 ?>
                                            <input type="text" class="form-control" id="email_login" name="email_login"
                                                value="<?= $email_login ?>" />
                                        </div>
                                        <div class="col-sm-2">
                                            Clave:
                                        </div>
                                        <div class="col-sm-3">
                                            <?php
                                                 $email_password= !empty($config->email_password) ? $config->email_password : $_SESSION['email_pass'];
                                                 ?>
                                            <input type="password" class="form-control" id="email_password"
                                                name="email_password" value="<?= $email_password ?>" />
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="checkbox">
                                            <label class="checkbox text">
                                                <input type="checkbox" id="outgoing_no_tls" name="outgoing_no_tls"
                                                    value="1"
                                                    <?php if ($config->outgoing_no_tls) echo "checked='checked'" ?> />
                                                No realizar start-TLS para encriptar la sesión, incluso con los
                                                servidores que la soportan
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div> <!-- Comunicación -->


                        <div class="tabcontent" id="tab3">

                            <div class="form-group row">
                                <div class="col-12">
                                    <label class="text">
                                        <input type="checkbox" name="http_access" id="http_access" value="1"
                                            <?php if ($config->http_access) echo "checked" ?> />
                                        El sistema puede ser accedido por otros Diriger de la organización empresarial,
                                        mediante servicio web con protocolo http/https, para la lectura de datos
                                    </label>
                                </div>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="type_synchro" id="type_synchro0"
                                        value="<?=_SYNCHRO_NEVER?>"
                                        <?php if ($config->type_synchro == _SYNCHRO_NEVER) echo "checked" ?>
                                        onclick="refresh_chk_synchro()" />
                                    No sincroniza con otros sistemas
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="type_synchro" id="type_synchro1"
                                        value="<?=_SYNCHRO_MANUAL?>"
                                        <?php if ($config->type_synchro == _SYNCHRO_MANUAL) echo "checked" ?>
                                        onclick="refresh_chk_synchro()" />
                                    La sincronización se realizará manual. La ejecuta un usuario con nivel de
                                    SUPERUSUARIO
                                </label>
                            </div>

                            <div id="tr_synchro1" class="row">
                                <div class="col-md-2">
                                    Recordar cada
                                </div>
                                <div class="col-md-2">
                                    <div id="spinner-day_synchro1" class="input-group spinner">
                                        <input type="text" name="day_synchro1" id="day_synchro1" class="form-control"
                                            value="<?=$day_synchro?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    días
                                </div>
                                <div class="col-md-2">
                                    <div id="spinner-hour_synchro1" class="input-group spinner">
                                        <input type="text" name="hour_synchro1" id="hour_synchro1" class="form-control"
                                            value="<?=$hour_synchro?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    horas y
                                </div>
                                <div class="col-md-2">
                                    <div id="spinner-min_synchro1" class="input-group spinner">
                                        <input type="text" name="min_synchro1" id="min_synchro1" class="form-control"
                                            value="<?=$min_synchro?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    minutos
                                </div>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="type_synchro" id="type_synchro2"
                                        value="<?= _SYNCHRO_AUTOMATIC_EMAIL ?>"
                                        <?php if ($config->type_synchro == _SYNCHRO_AUTOMATIC_EMAIL  || $config->type_synchro == _SYNCHRO_AUTOMATIC_HTTP) echo "checked" ?>
                                        onclick="refresh_chk_synchro()" />
                                    La sincronización será automática, repetitiva en background. Se tiene la tarea
                                    programada en el Sistema Operativo
                                </label>
                            </div>

                            <div id="tr_synchro2">
                                <div class="form-group row">
                                    <div class="col-md-1">
                                        Cada
                                    </div>
                                    <div class="col-md-2">
                                        <div id="spinner-day_synchro2" class="input-group spinner">
                                            <input type="text" name="day_synchro2" id="day_synchro2"
                                                class="form-control" value="<?=$day_synchro?>">
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up">
                                                    <i class="fa fa-arrow-up"></i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down">
                                                    <i class="fa fa-arrow-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        días
                                    </div>
                                    <div class="col-md-2">
                                        <div id="spinner-hour_synchro2" class="input-group spinner">
                                            <input type="text" name="hour_synchro2" id="hour_synchro2"
                                                class="form-control" value="<?=$hour_synchro?>">
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up">
                                                    <i class="fa fa-arrow-up"></i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down">
                                                    <i class="fa fa-arrow-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        horas y
                                    </div>
                                    <div class="col-md-2">
                                        <div id="spinner-min_synchro2" class="input-group spinner">
                                            <input type="text" name="min_synchro2" id="min_synchro2"
                                                class="form-control" value="<?=$min_synchro?>">
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up">
                                                    <i class="fa fa-arrow-up"></i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down">
                                                    <i class="fa fa-arrow-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        minutos.
                                    </div>
                                </div>

                                <div class="form-group row margin-top-5">
                                    <label class="col-form-label col-md-4 pull-left">
                                        En la noche/madrugada despues de la hora:
                                    </label>
                                    <div class="col-md-3 pull-left">
                                        <select id="timesynchro" name="timesynchro" class="form-control input-small">
                                            <option value="0">Todo el día</option>
                                            <?php
                                                $i= 0;
                                                for ($ddtime= "1969-01-01 16:45:00"; strtotime($ddtime) < strtotime("1969-01-01 23:59:00"); ++$i) {
                                                    $ddtime= add_date($ddtime, 0, 0, 0, 0, 15);
                                                    $xtime= date('H:i:s', strtotime($ddtime));
                                                ?>
                                            <option value="<?=$xtime?>"
                                                <?php if ($xtime == $config->timesynchro) { ?>selected="selected"
                                                <?php } ?>><?=date('h:i A', strtotime($ddtime))?></option>
                                            <?php } ?>
                                            <?php
                                                for ($ddtime= "1969-01-01 00:00:00"; strtotime($ddtime) < strtotime("1969-01-01 05:45:00"); ++$i) {
                                                    $ddtime= add_date($ddtime, 0, 0, 0, 0, 15);
                                                    $xtime= date('H:i:s', strtotime($ddtime));
                                                ?>
                                            <option value="<?=$xtime?>"
                                                <?php if ($xtime == $config->timesynchro) { ?>selected="selected"
                                                <?php } ?>><?=date('h:i A', strtotime($ddtime))?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- sincronizacion -->


                        <!-- Destinos -->
                        <div class="tabcontent" id="tab2">
                            <table id="table" class="table table-striped" data-toggle="table" data-height="600"
                                data-search="true" data-row-style="rowStyle">
                                <thead>
                                    <tr>
                                        <th>DESTINO</th>
                                        <th>MODO</th>
                                        <th>FRECUENCIA</th>
                                        <th>ENCRIPTAR?</th>
                                        <!--
                                             <th>LLAVE</th>
                                             -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cant_prs= 0;
                                    reset($array_procesos);
                                    foreach ($array_procesos as $row) {
                                        ++$cant_prs;

                                        $id= $row['id'];
                                        $manner= $obj_config_prs->array_procesos[$row['id']]['manner'];
                                        $day_prs= $obj_config_prs->array_procesos[$row['id']]['d'];
                                        $hour_prs= $obj_config_prs->array_procesos[$row['id']]['h'];
                                        $min_prs= $obj_config_prs->array_procesos[$row['id']]['i'];
                                        $mcrypt= $obj_config_prs->array_procesos[$row['id']]['mcrypt'];
                                        $mcrypt_key= $obj_config_prs->array_procesos[$row['id']]['key'];
                                    ?>

                                    <tr>
                                        <td>
                                            <?= textparse($row['nombre'],", ".$Ttipo_proceso_array[$row['tipo']]) ?>
                                            <input type="hidden" id="prs<?=$cant_prs?>" name="prs<?=$cant_prs?>"
                                                value="<?= textparse($row['nombre'],", ".$Ttipo_proceso_array[$row['tipo']], true) ?>" />
                                        </td>

                                        <td>
                                            <input type="hidden" id="tab_prs<?=$cant_prs?>" name="tab_prs<?=$cant_prs?>"
                                                value="<?=$id?>">

                                            <select id="select_manner<?=$cant_prs?>" name="select_manner<?=$cant_prs?>"
                                                class="form-control" onchange="set_manner(<?=$cant_prs?>)"
                                                style="max-width: 160px">
                                                <option id="manner0_<?=$cant_prs?>" value=<?=_SYNCHRO_NEVER?>
                                                    <?php if ($manner == _SYNCHRO_NEVER) echo "selected='selected'"?>>
                                                    Nunca</option>
                                                <option id="manner1_<?=$cant_prs?>" value=<?=_SYNCHRO_MANUAL?>
                                                    <?php if ($manner == _SYNCHRO_MANUAL) echo "selected='selected'"?>>
                                                    Manual</option>
                                                <?php if ($row['conectado']) {?>
                                                <option id="manner2_<?=$cant_prs?>" value=<?=_SYNCHRO_AUTOMATIC_EMAIL?>
                                                    <?php if ($manner == _SYNCHRO_AUTOMATIC_EMAIL) echo "selected='selected'"?>>
                                                    Automática E-mail</option>
                                                <option id="manner3_<?=$cant_prs?>" value=<?=_SYNCHRO_AUTOMATIC_HTTP?>
                                                    <?php if ($manner == _SYNCHRO_AUTOMATIC_HTTP) echo "selected='selected'"?>>
                                                    Automática HTTP</option>
                                                <?php }?>
                                            </select>

                                        </td>

                                        <td>
                                            <div class="row">
                                                <div class="col-2">
                                                    <select id="day_prs<?= $cant_prs ?>" name="day_prs<?= $cant_prs ?>"
                                                        class="form-control" onchange="set_manner(<?= $cant_prs ?>)">
                                                        <?php for ($i = 0; $i < 31; ++$i) { ?>
                                                        <option value="<?= $i ?>"
                                                            <?php if ($i == $day_prs) echo "selected='selected'" ?>>
                                                            <?= $i ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-1">
                                                    días
                                                </div>
                                                <div class="col-2">
                                                    <select id="hour_prs<?= $cant_prs ?>"
                                                        name="hour_prs<?= $cant_prs ?>" class="form-control"
                                                        onchange="set_manner(<?= $cant_prs ?>)">
                                                        <?php for ($i = 0; $i < 24; ++$i) { ?>
                                                        <option value="<?= $i ?>"
                                                            <?php if ($i == $hour_prs) echo "selected='selected'" ?>>
                                                            <?= $i ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    horas y
                                                </div>
                                                <div class="col-2">
                                                    <select id="min_prs<?= $cant_prs ?>" name="min_prs<?= $cant_prs ?>"
                                                        class="form-control" onchange="set_manner(<?= $cant_prs ?>)">
                                                        <?php for ($i = 0; $i < 60; ++$i) { ?>
                                                        <option value="<?= $i ?>"
                                                            <?php if ($i == $min_prs) echo "selected='selected'" ?>>
                                                            <?= $i ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    minutos
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="form-control" id="mcrypt<?= $cant_prs ?>"
                                                name="mcrypt<?= $cant_prs ?>" onclick="set_chk_manner(<?=$cant_prs?>)"
                                                <?php if ($mcrypt) echo "checked='checked'"?> value="1" />
                                        </td>
                                        <!--
                                             <td>
                                                 <input type="password" class="form-control" id="mcrypt_key<?= $cant_prs ?>" name="mcrypt_key<?= $cant_prs ?>" value="<?=$mcrypt_key?>" />
                                             </td>
                                             -->
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <input type="hidden" id="cant_prs" name="cant_prs" value="<?=$cant_prs?>">
                        </div> <!-- Destinos -->

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

    <div id="div-ajax-panel" class="ajax-panel card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-title"
                    class="panel-title ajax-title clear col-11 m-0 win-drag"></div>

                <div class="col-1 m-0t">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="div-ajax-body" class="card-body">

        </div>
    </div>
</body>

</html>