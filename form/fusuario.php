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
require_once "../php/class/grupo.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/code.class.php";
require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tusuario($clink);
}

$id_usuario= $obj->GetIdUsuario();
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$obj_sub= null;

if (!empty($id_usuario)) {
    $obj_sub= new Torgtarea($clink);
    $obj_sub->SetIdResponsable($id_usuario);

    $obj_sub->listar_usuarios();
    $array_usuarios= $obj_sub->array_usuarios;

    $obj_sub->listar_grupos();
    $array_grupos= $obj_sub->array_grupos;
}

$clave= '';
if ($action == 'update' || $action == 'list')
    $clave= '12345678';
if ($action == 'add')
    $clave= '';

if (!empty($_GET['id_proceso']))
    $id_proceso= $_GET['id_proceso'];
if (empty($id_proceso))
    $id_proceso= $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$usuario= !is_null($_GET['usuario']) ? urldecode($_GET['usuario']) : $obj->GetUsuario();
$nombre= !is_null($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$cargo= !is_null($_GET['cargo']) ? urldecode($_GET['cargo']) : $obj->GetCargo();
$email= !is_null($_GET['email']) ? urldecode($_GET['email']) : $obj->GetMail_address();
$noIdentidad= !is_null($_GET['noIdentidad']) ? $_GET['noIdentidad'] : $obj->GetNoIdentidad();
$nivel= !is_null($_GET['nivel']) ? $_GET['nivel'] : $obj->GetRole();

$global= !is_null($_GET['global']) ? $_GET['global'] : $obj->GetIfGlobalUser();
$acc_sys= !is_null($_GET['acc_sys']) ? $_GET['acc_sys'] : $obj->get_acc_sys();
$acc_planwork= !is_null($_GET['acc_planwork']) ? $_GET['acc_planwork'] : $obj->get_acc_planwork();
$acc_planrisk= !is_null($_GET['acc_planrisk']) ? $_GET['acc_planrisk'] : $obj->get_acc_planrisk();
$acc_planaudit= !is_null($_GET['acc_planaudit']) ? $_GET['acc_planaudit'] : $obj->get_acc_planaudit();
$acc_planheal= !is_null($_GET['acc_planheal']) ? $_GET['acc_planheal'] : $obj->get_acc_planheal();
$acc_planproject= !is_null($_GET['acc_planproject']) ? $_GET['acc_planproject'] : $obj->get_acc_planproject();
$acc_archive= !is_null($_GET['acc_archive']) ? $_GET['acc_archive'] : $obj->get_acc_archive();

$nivel_archive2= !is_null($_GET['nivel_archive2']) ? $_GET['nivel_archive2'] : $obj->get_nivel_archive2();
$nivel_archive3= !is_null($_GET['nivel_archive3']) ? $_GET['nivel_archive3'] : $obj->get_nivel_archive3();
$nivel_archive4= !is_null($_GET['nivel_archive4']) ? $_GET['nivel_archive4'] : $obj->get_nivel_archive4();

$freeassign= !is_null($_GET['freeassign']) ? $_GET['freeassign'] : $obj->get_freeassign();

$user_ldap= null;
if ($action != 'add')
    $user_ldap= !is_null($_GET['user_ldap']) ? $_GET['user_ldap'] : $obj->get_user_ldap();
if (is_null($user_ldap) && $action == 'add')
    $user_ldap= 0;

$time= new TTime();
$year= !empty($_SESSION['current_year']) ? $_SESSION['current_year'] : $time->GetYear();
$month= !empty($_SESSION['current_month']) ? $_SESSION['current_month'] : $time->GetMonth();
$day= !empty($_SESSION['current_day']) ? $_SESSION['current_day'] : $time->GetDay();

$user_date_ref= $year.'-'.$month.'-'.$day;
$id_user_restrict= !empty($id_usuario) ? $id_usuario : 0;
$restrict_prs= array(_TIPO_PROCESO_INTERNO);

$obj_prs= new Tproceso($clink);
$obj_prs->init_proceso_install();

$url_page= "../form/fusuario.php?signal=$signal&action=$action&menu=usuario&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>USUARIO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <!-- Upload files -->
    <link rel="stylesheet" href="../libs/upload/upload.css" />
    <script type="text/javascript" src="../libs/upload/upload.js"></script>

    <script type="text/javascript" src="../js/time.js?version="></script>
    
    <script type="text/javascript" src="../js/ajax_core.js?version="></script>
    
    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
        .panel-options {
            background-color: #fff;
            flex: 1;
            height: 80vh;
            overflow: auto;
            margin: 8px;
            padding: 4px;
        }
        blockquote.text {
            font-size: 1em;
            font-weight: normal;
        }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function _validar() {
            var form= document.forms[0];

            if (!Entrada($('#nombre').val())) {
                $('#nombre').focus(focusin($('#nombre')));
                alert('Introduzca el nombre');
                return false;
            }
            if ($('#proceso').val() == 0) {
                $('#proceso').focus(focusin($('#proceso')));
                alert('No ha especificado el proceso o Unidad Organizativa a la que pertenece el usuario');
                return false;
            }
            if (!Entrada($('#cargo').val())) {
                $('#cargo').focus(focusin($('#cargo')));
                alert('Introduzca el cargo que ocupa el usuario en la organización');
                return false;
            }
            if ($('#global_user').is(':checked') && !Entrada($('#no_identidad').val())) {
               $('#global_user').focus(focusin($('#global_user')));
               alert("Sí se trata de un usuario cuyos fatos migrarán, debe especificar el Número de Carnet de Identidad.");
               return false;
            }
            if ($('#freeassign').is(':checked') && !Entrada($('#no_identidad').val())) {
               $('#freeassign').focus(focusin($('#freeassign')));
               alert("Sí se trata de un usuario que puede asignat tareas libremente debe de especificar el Número de Carnet de Identidad.");
               return false;
            }
            if ((parseInt($('#role').val()) >= <?=_PLANIFICADOR?> && parseInt($('#role').val()) != <?=_ADMINISTRADOR?>)
                    && !Entrada($('#no_identidad').val())) {
                $('#no_identidad').focus(focusin($('#no_identidad')));
                alert("Al usuario PLANIFICADOR o SUPERUSUARIO se le debe de especificarle el Número de Identidad del usuario.");
                return false;
            }
            if (Entrada($('#no_identidad').val())) {
                if (!IsNumeric($('#no_identidad').val()) || $('#no_identidad').val().length < 11) {
                    $('#no_identidad').focus(focusin($('#no_identidad')));
                    alert("Error en el Número de Identidad del usuario.");
                    return false;
                }
            }
            if (Entrada($('#email').val())) {
                if (!valEmail($('#email').val())) {
                    $('#email').focus(focusin($('#email')));
                    alert('Dirección de correo electrónico incorrecta.');
                    return false;
                }
            }
            if ($('#role').val() == 0) {
                $('#role').focus(focusin($('#role')));
                alert('Selecione el nivel de acceso al sistema');
                return false;
            }

           update_plan_option('acc_planwork', 0);
           update_plan_option('acc_planrisk', 0);
           update_plan_option('acc_planaudit', 0);
           update_plan_option('acc_planheal', 0);
           update_plan_proyecto();
           update_archive();

            if ((parseInt($('#acc_planwork').val()) || parseInt($('#acc_planrisk').val()) || parseInt($('#acc_planaudit').val()) || parseInt($('#acc_planheal').val())
                || parseInt($('#acc_planproject').val()) || parseInt($('#acc_planaudit').val())) && parseInt($('#role').val()) < 3) {
               $('#role').focus(focusin($('#role')));
               alert("El usuario responsable de la planificación y la gestión de cualquiera de los Planes debe tener un nivel de accesos no menor que PLANIFICADOR. ");
               return false;
            }

            function _this() {
                if ($('#pwd').val() != '12345678' && $('#pwd').val() != $('#clave2').val()) {
                   $('#pwd').focus(focusin($('#pwd')));
                   alert('No ha repetido la clave correctamente');
                   limpiar();
                   return false;
                }

                $('#usuario').val($('#user').val());

                if ($('#pwd').val() == '12345678') {
                    $('#clave').val(null);

                } else {
                   $('#clave').val($('#pwd').val());

                   if (validar_login(form, true) == false)
                       return false;
                }

                return true;
            }

            if ($('#exect').val() == 'add' || ($('#exect').val() == 'update' || ($('#user_ldap').val() || $('#ldap_login').val()))) {
                if (!_this()) 
                    return;
            } else {
                if (Entrada($('#pwd').val()) || Entrada($('#clave2').val())) {
                    if (!_this()) 
                        return;
                }
            }

            parent.app_menu_functions= false;
            $('#_submit').hide();
            $('#_submited').show();

            form.action= '../php/user.interface.php';
            form.submit();
        }

        function validar() {
            if ($('#acc_sys').is(':checked')) {
                $('#acc_sys').focus(focusin($('#acc_sys')));
                confirm("El acceso del usuario al sistema está bloquedo, y no podra acceder. Desea continuar?", function(ok) {
                    if (!ok)
                        return false;
                    else
                        _validar();
                });
            } else {
                _validar();
            }
        }

        function limpiar() {
            $('#pwd').val('');
            $('#clave2').val('');
        }

        function update_plan_option(option, index) {
            index= parseInt(index);

            if (index != 0) {
                if ($('#'+option+index).is(':checked')) {
                    for(j= index; j > 0; j--)
                        $('#'+option+j).prop('checked', true);
                }

                if (index == 2 && !$('#'+option + 2).is(':checked'))
                    $('#'+option + 1).prop('checked', false);

                if ($('#'+option + 3).is(':checked')) {
                    $('#'+option + 2).prop('checked', true);
                    $('#freeassign').prop('checked', true);
                }

                if (!$('#'+option + 3).is(':checked') && !$('#'+option + 1).is(':checked'))
                    $('#'+option + 2).prop('checked', false);
            }

            $('#'+option).val(0);

            for(var j= 3; j > 0; j--) {
                if ($('#'+option+j).is(':checked')) {
                    $('#'+option).val(j);
                    return false;
                }
            }
        }

        function update_plan_proyecto(index) {
            if ($('#acc_planproject1').is(':checked')) {
                $('#acc_planproject').val(1);
                $('#acc_planproject1').prop('checked', true);
            }
            if ($('#acc_planproject3').is(':checked')) {
                $('#acc_planproject').val(3);
                $('#acc_planproject1').prop('checked',true);
                $('#freeassign').prop('checked', true);
            }
            if (!$('#acc_planproject1').is(':checked') && !$('#acc_planproject3').is(':checked')) {
                $('#acc_planproject').val(0);
            }
        }

        function update_archive() {
            if ($('#acc_archive3').is(':checked')) {
                $('#acc_archive').val(3);
                $('#acc_archive1').prop('checked', true);

                $('#acc_archive2').prop('checked', true);
                $('#div_nivel_archive2').show();

                $('#nivel_archive4').prop('checked', true);
            }

            if (!$('#acc_archive3').is(':checked')) {
                if ($('#acc_archive2').is(':checked')) {
                    $('#acc_archive').val(2);
                    $('#div_nivel_archive2').show();
                } else {
                    $('#nivel_archive2').val(0);
                    $('#div_nivel_archive2').hide();
                }
            }

            if (!$('#acc_archive3').is(':checked') && !$('#acc_archive2').is(':checked')) {
                if ($('#acc_archive1').is(':checked')) {
                    $('#acc_archive').val(1);
                    $('#div_nivel_archive3').show();
                } else {
                    $('#nivel_archive3').val(0);
                    $('#div_nivel_archive3').hide();
                }                
            }

            /*
            if ($('#acc_archive3').is(':checked')) {
                $('#nivel_archive2').val(3);
                $('#nivel_archive3').val(3);
            }
            */
            if ($('#nivel_archive2').val() == undefined)
                $('#nivel_archive2').val(0);
            if ($('#nivel_archive3').val() == undefined)
                $('#nivel_archive3').val(0);
        }

        function refreshp() {
            var action= $('#exect').val();

            var global= ($('#global_user').is(':checked')) ? 1 : 0;
            var acc_sys= ($('#acc_sys').checked) ? 1 : 0;
            var acc_planrisk= $('#acc_planrisk').val();
            var acc_planwork= $('#acc_planwork').val();
            var acc_planaudit= $('#acc_planaudit').val();
            var acc_planheal= $('#acc_planheal').val();
            var acc_planproject= $('#acc_planproject').val();
            var acc_archive= $('#acc_archive').val();
            var nivel_archive2= $('#nivel_archive2').val();
            var nivel_archive3= $('#nivel_archive3').val();
            var nivel_archive4= $('#nivel_archive4').val();

            var freeassign= ($('#freeassign').is(':checked')) ? 1 : 0;

            var usuario= '';
            var clave= '';

            usuario= encodeURI($('#user').val());
            <?php if ($action == 'add' || ($action == 'update' && (!$user_ldap || !$config->ldap_login))) { ?>
            clave= encodeURI($('#pwd').val());
            <?php } ?>

            var nombre= encodeURI($('#nombre').val());
            var email= encodeURI($('#email').val());
            var user_ldap= $('#user_ldap').val();
            var cargo= encodeURI($('#cargo').val());
            var role= $('#role').val();
            var noIdentidad= $('#no_identidad').val();

            var id= $('#id').val();

            var url= 'fusuario.php?version=&action='+action+'&id='+id+'&nombre='+nombre+'&cargo='+cargo;
            url+= '&global='+global+'&noIdentidad='+noIdentidad+'&id_proceso='+$('#proceso').val()+'&email='+email;
            url+= '&acc_sys='+acc_sys+'&acc_planwork='+acc_planwork+'&acc_planrisk='+acc_planrisk+'&acc_planaudit='+acc_planaudit;
            url+= '&acc_planheal='+acc_planheal+'&acc_planproject='+acc_planproject+'&freeassign='+freeassign+'&clave='+clave;
            url+= '&acc_archive='+acc_archive+'&nivel_archive2='+nivel_archive2+'&nivel_archive3='+nivel_archive3+'&nivel_archive4='+nivel_archive4;
            url+= '&nivel='+role+'&usuario='+usuario+'&user_ldap='+user_ldap;

            parent.app_menu_functions= false;
            $('#_submit').hide();
            $('#_submited').show();

            self.location.href= url;
        }

        function set_user_ldap() {
            if ($('#user_ldap').val() == 1) {
                if (Entrada($('#nombre').val()) && $('#connect_ldap').val() == 1) {
                    $('#nombre').attr('readonly', true);
                    $('#ldap-msg-name').show();
                }
                if (Entrada($('#email').val()) && ($('#connect_ldap').val() && $('#mail_use_ldap').val())) {
                    // $('#email').attr('readonly', true);
                    $('#ldap-msg-email').show();
                }

                $('#user').attr('disabled', true);
                /*
                $('#tr-clave1').hide();
                $('#tr-clave2').hide();
                */
                $('#tr-ldap').hide();
            } else {
                $('#nombre').attr('readonly', false);
                $('.ldap_msg').hide();
                $('#user').attr('disabled', false);
                /*
                $('#tr-clave1').show();
                $('#tr-clave2').show();
                */
            }
        }

        function update_tabs() {
            <?php if ($_SESSION['nivel'] == _ADMINISTRADOR) { ?>
            if ($('#role').val() == <?=_SUPERUSUARIO?> && $('#_nivel').val() != <?=_SUPERUSUARIO?>) {
                $('#role').val($('#_nivel').val());
                return;
            }
            <?php } ?>

            if ($('#role').val() >= <?=_PLANIFICADOR?>) {
                $('#nav-tab2').show();
                $('#tab2').css('visibility', 'visible');
            } else {
                $('#nav-tab2').hide();
                $('#tab2').css('visibility', 'hidden');
            }

            if ($('#role').val() >= <?=_SUPERUSUARIO?>) {
                $('#acc_planaudit1').prop('checked',true);
                $('#acc_planaudit2').prop('checked',true);
                $('#acc_planaudit3').prop('checked',true);

                $('#acc_planrisk1').prop('checked',true);
                $('#acc_planrisk2').prop('checked',true);
                $('#acc_planrisk3').prop('checked',true);

                $('#acc_planwork1').prop('checked',true);
                $('#acc_planwork2').prop('checked',true);
                $('#acc_planwork3').prop('checked',true);

                $('#acc_planheal1').prop('checked',true);
                $('#acc_planheal2').prop('checked',true);
                $('#acc_planheal3').prop('checked',true);

                $('#acc_planproject1').prop('checked',true);
                $('#acc_planproject3').prop('checked',true);

                $('#acc_archive1').prop('checked',true);
                $('#acc_archive2').prop('checked',true);
                $('#acc_archive3').prop('checked',true);
                $('#acc_archive4').prop('checked',true);
            }

            if ($('#role').val() < <?=_PLANIFICADOR?>) {
                $('#acc_planaudit1').prop('checked',false);
                $('#acc_planaudit2').prop('checked',false);
                $('#acc_planaudit3').prop('checked',false);

                $('#acc_planrisk1').prop('checked',false);
                $('#acc_planrisk2').prop('checked',false);
                $('#acc_planrisk3').prop('checked',false);

                $('#acc_planwork1').prop('checked',false);
                $('#acc_planwork2').prop('checked',false);
                $('#acc_planwork3').prop('checked',false);

                $('#acc_planheal1').prop('checked',false);
                $('#acc_planheal2').prop('checked',false);
                $('#acc_planheal3').prop('checked',false);

                $('#acc_planproject1').prop('checked',false);
                $('#acc_planproject3').prop('checked',false);

                $('#acc_archive1').prop('checked',false);
                $('#acc_archive2').prop('checked',false);
                $('#acc_archive3').prop('checked',false);
                $('#acc_archive4').prop('checked',false);
            }
        }

        function fix_ldap() {
            $('#set_ldap').val(1);
            alert("Si selecciona el boton Aceptar. Los datos de conexion del usuario al LDAP seran reiniciados.");
        }

        var array_NO_LOCAL_prs= Array();
        <?php
        $obj_prs= new Tproceso($clink);
        $array_NO_LOCALs= $obj_prs->listar_NO_LOCALs();

        foreach ($array_NO_LOCALs as $local) {
        ?>
        array_NO_LOCAL_prs[<?=$local['id']?>]= <?=$local['id']?>;
        <?php } ?>
    </script>

    <script type="text/javascript">
	$(document).ready(function() {
        $('.ldap-msg').hide();

        InitUploaderImage('firma', "<?= urlencode(_ROOT_DIRIGER_DIR)?>", 300, 200);

        update_plan_option('acc_planwork', 0);
        update_plan_option('acc_planrisk', 0);
        update_plan_option('acc_planaudit', 0);
        update_plan_option('acc_planheal', 0);
        update_plan_proyecto();

        update_tabs();
        update_archive();
        set_user_ldap();

        $.ajax({
            data:  {
                    "year" : <?=!empty($year) ? $year : date('Y')?>,
                    "user_ref_date" : '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                    "id_user_restrict" : <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                    "restrict_prs" : <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                    "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                    "array_grupos" : <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>
                },
            url:   'ajax/usuario_tabs_simple.ajax.php',
            type:  'post',
            beforeSend: function () {
                $("#ajax-tab-users").html("Procesando, espere por favor...");
            },
            success:  function (response) {
                $("#ajax-tab-users").html(response);
            }
        });

        <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n", " ", addslashes($error))?>");
        <?php } ?>
	});
    </script>

</head>

<body>
    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">USUARIOS</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Acceso a los Planes Generales</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab5">Suboordinados 1/2</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Subordinados 2/2</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Participa de Unidades Organizativas/Proceso</a></li>
                    </ul>

                    <form class="form-horizontal" action="javascript:validar()"  method="post" enctype="multipart/form-data">
                       <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                       <input type="hidden" id="id" name="id" value="<?=$id_usuario?>" />
                       <input type="hidden" name="menu" value="usuario" />

                       <input type="hidden" name="usuario" id="usuario" value="" />
                       <input type="hidden" name="clave" id="clave" value="" />
                       <input type="hidden" name="_nivel" id="_nivel" value="<?=$action == 'update' ? $obj->GetRole() : 0?>" />

                       <input type="hidden" id="eliminado" name="eliminado" value="<?=$obj->GetEliminado()?>">
                       <input type="hidden" id="user_date_ref" name="user_date_ref" value="<?=$user_date_ref?>" />

                       <input type="hidden" id="login_ldap" name="login_ldap" value="<?=$config->login_ldap ? 1 : 0?>"/>
                       <input type="hidden" id="user_ldap" name="user_ldap" value="<?=$user_ldap ? 1 : 0?>"/>
                       <input type="hidden" id="connect_ldap" name="connect_ldap" value="<?=$config->ldap_login ? 1 : 0?>" />
                       <input type="hidden" id="mail_use_ldap" name="mail_use_ldap" value="<?=$config->mail_use_ldap ? 1 : 0?>" />

                       <input type="hidden" id="set_ldap" name="set_ldap" value="0" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="checkbox">
                                 <label>
                                     <input type="checkbox" name="acc_sys" id="acc_sys" value="1" <?php if (!empty($acc_sys)) echo "checked='checked'"; ?>  />
                                     Bloqueado el acceso al Sistema
                                 </label>
                            </div>

                            <hr></hr>
                            <div class="form-group row">
                                <div class="row col-md-8">
                                    <label class="col-form-label col-md-3">
                                        Nombre y Apellidos:
                                    </label>
                                    <div class="col-md-9">
                                        <input id="nombre" name="nombre" class="form-control" value="<?=$nombre?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label id="ldap_msg_name" class="ldap-msg alert alert-danger">
                                        * Definido en el servidor de dominio LDAP
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Unidad Organizativa a la que pertenece:
                                </label>

                                <div class="col-md-9">
                                <?php
                                $top_list_option= "seleccione........";
                                $id_list_prs= null;
                                $order_list_prs= 'eq_asc_desc';
                                $reject_connected= false;
                                $in_building= false;
                                $break_exept_connected= _TIPO_GRUPO;
                                $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
                                $id_select_prs= $id_proceso;
                                require_once "inc/_select_prs.inc.php";
                                ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Cargo:
                                </label>
                                <div class="col-md-9">
                                    <input id="cargo" name="cargo" class="form-control" value="<?=$cargo?>" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    No. Identidad
                                </label>
                                <div class="col-md-5 pull-left">
                                    <input id="no_identidad" name="no_identidad" class="form-control" maxlength="11" value="<?=$noIdentidad?>" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Correo Electrónico:
                                </label>
                                <div class="col-md-5">
                                    <input id="email" name="email" class="form-control" value="<?=$email?>" />
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <label id="ldap-msg-email" class="ldap-msg alert alert-danger">
                                        * Definido en el servidor de dominio LDAP.
                                    </label>
                                </div>
                            </div>

                            <hr></hr>

                            <div class="form-group row">
                                <fieldset class="col-md-5 col-lg-5">
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-4 col-lg-4">
                                            Nivel de Acceso:
                                        </label>
                                        <div class="col-md-8 col-lg-8">
                                            <select name="role" id="role" class="form-control" onchange="update_tabs()">
                                                <option value=0>...</option>
                                                <?php
                                                for ($i = 1; $i < 7; ++$i) {
                                                    if ($i == _SUPERUSUARIO && $_SESSION['nivel'] <= _ADMINISTRADOR)
                                                        continue;
                                                    if ($i == _GLOBALUSUARIO && $_SESSION['nivel'] != _GLOBALUSUARIO)
                                                        continue;
                                                ?>
                                                    <option value=<?= $i ?> <?php if ($i == $nivel) echo "selected='selected'"; ?>><?= $roles_array[$i] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div id="tr-usuario" class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Usuario:
                                        </label>
                                        <div class="col-md-9 pull-left">
                                            <input id="user" name="user" class="form-control" autocomplete="no" value="<?= $usuario ?>" />
                                        </div>
                                    </div>
                                    <div id="tr-clave1" class="form-group row">
                                        <label class="col-form-label col-md-3">
                                            Clave:
                                        </label>
                                        <div class="col-md-9">
                                            <input type="password" id="pwd" name="pwd" class="form-control" value="<?= $clave ?>" autocomplete="no" onclick="limpiar()" />
                                        </div>
                                    </div>
                                    <div id="tr-clave2" class="form-group row">
                                        <label class="col-form-label col-md-3 col-lg-3">
                                            Repita la Clave:
                                        </label>
                                        <div class="col-md-9 col-lg-9">
                                            <input type="password" id=clave2 name=clave2 class="form-control" value="<?= $clave ?>" autocomplete="no" />
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="col-md-7 col-lg-7 pull-left">
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-3 col-lg-3">
                                            Firma (imagen)
                                        </label>

                                        <div class="col-md-7 col-lg-7 pull-left">
                                            <div id="firma" class="panel-file">
                                                <div class="img">
                                                    <?php
                                                    $firma = $obj->GetImage();
                                                    if (!is_null($firma)) {
                                                        $dim= $obj->GetDim();
                                                    ?>
                                                    <img src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&id=<?= $id_usuario ?>" <?=$dim?> />
                                                    <?php } ?>
                                                </div>

                                                <div class="title">Click para cargar imagen ...</div>
                                                <input type="file" id="firma-upload" name="firma-upload" />
                                                <div class="close img-thumbnail" onclick="closeFile('firma');">X</div>
                                            </div>

                                            <input type="hidden" id="firma-upload-init" name="firma-upload-init" value="1" />
                                        </div>

                                        <div class="col-md-2 col-lg-2">
                                            <button type="button" id="firma-btn-trash" class="btn btn-default upload-trash" title="Eliminar Imagen">
                                                <i class="fa fa-trash fa-2x"></i>
                                            </button>
                                        </div>

                                    </div>
                                </fieldset>
                            </div>

                            <label id="ldap-msg-user" class="ldap-msg alert alert-danger">
                                * El usuario y la clave del usuario deben de corresponderse con lo registrado en los servidores de Dominio o LDAP.
                            </label>

                            <hr></hr>
                        </div> <!-- generales -->

                        <?php
                        $id_entity= !empty($id_proceso) ? $array_procesos_entity[$id_proceso]['id_entity'] : $_SESSION['id_entity'];
                        $id_entity= !empty($id_entity) ? $id_entity : $id_proceso;
                        $entity_nombre= $array_procesos_entity[$id_entity]['nombre'];
                        $entity_tipo= $array_procesos_entity[$id_entity]['tipo'];
                        ?>
                        
                        <!-- nivel de asignacion de tareas -->
                        <div class="tabcontent" id="tab5">
                            <blockquote class="text">
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="freeassign" id="freeassign0" value="0" <?php if (empty($freeassign)) echo "checked='checked'"; ?>  />
                                        Puede asignar tareas y delegar responsabilidades solo a sus subordinados.
                                    </label>
                                    <label>
                                        <input type="radio" name="freeassign" id="freeassign1" value="1" <?php if ($freeassign == 1) echo "checked='checked'"; ?>  />
                                        Puede asignar tareas y delegar responsabilidades a usuarios que no son subordinados, pero que pertenecen a la misma entidad.
                                    </label>  
                                    <label>
                                        <input type="radio" name="freeassign" id="freeassign2" value="2" <?php if ($freeassign == 2) echo "checked='checked'"; ?>  />
                                        Puede asignar tareas y delegar responsabilidades a usuarios que no son subordinados, independientemente a la entidad a la que pertenecen.
                                    </label>                                                              
                                </div>                        
                            </blockquote >

                            <blockquote class="text">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="global_user" name="global_user" value="1" <?= !empty($global) ? "checked='checked'": ""; ?> />
                                        <i class="fa fa-warning" style="color: red; margin-left: 8px;"></i>
                                        Sus tareas y eventos serán accesibles desde otras estaciones o servidores, ubicados fuera del proceso o unidad organizativa empresarial.
                                    </label>
                                </div>                         
                            </blockquote>
                        </div>

                        <!-- acceso a planes generales -->
                        <div class="tabcontent" id="tab2">
                            <div class="panel-options">
                                <input type="hidden" id="acc_planwork" name="acc_planwork" value="<?= $acc_planwork ?>" />
                               <input type="hidden" id="acc_planrisk" name="acc_planrisk" value="<?= $acc_planrisk ?>" />
                               <input type="hidden" id="acc_planaudit" name="acc_planaudit" value="<?= $acc_planaudit ?>" />
                               <input type="hidden" id="acc_planheal" name="acc_planheal" value="<?= $acc_planheal ?>" />
                               <input type="hidden" id="acc_planproject" name="acc_planproject" value="<?= $acc_planproject ?>" />
                               <input type="hidden" id="acc_archive" name="acc_archive" value="<?= $acc_archive ?>" />

                               <legend>Planes de Trabajo Generales (anuales y  mensuales)</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planwork3" id="acc_planwork3" value="3" 
                                       onclick="update_plan_option('acc_planwork',3)" <?php if ($acc_planwork == 3) echo "checked='checked'"; ?>  />
                                       Gestiona  el plan de <?=$entity_nombre?>
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planwork1" id="acc_planwork1" value="1" 
                                       onclick="update_plan_option('acc_planwork',1)" <?php if ($acc_planwork >= 1) echo "checked='checked'"; ?>  />
                                       Gestiona los planes de las UEBs, direcciones funcionales y departamentos a los que  pertenece
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planwork2" id="acc_planwork2" value="2" 
                                       onclick="update_plan_option('acc_planwork',2)" <?php if ($acc_planwork >= 2) echo "checked='checked'"; ?>  />
                                       Puede agregar actividades a los planes de <?=$entity_nombre?>
                                   </label>
                               </div>


                               <legend>Planes de Riesgos y Prevención</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planrisk3" id="acc_planrisk3" value="3" 
                                       onclick="update_plan_option('acc_planrisk',3)" <?php if ($acc_planrisk == 3) echo "checked='checked'"; ?>  />
                                       Gestiona el plan de <?=$entity_nombre?>
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planrisk1" id="acc_planrisk1" value="1" 
                                       onclick="update_plan_option('acc_planrisk',1)" <?php if ($acc_planrisk >= 1) echo "checked='checked'"; ?>  />
                                       Gestiona los planes de las UEBs, direcciones funcionales y departamentos a los que  pertenece
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planrisk2" id="acc_planrisk2" value="2" 
                                       onclick="update_plan_option('acc_planrisk',2)" <?php if ($acc_planrisk >= 2) echo "checked='checked'"; ?>  />
                                       Puede agregar riesgos al plan de <?=$entity_nombre?>
                                   </label>
                               </div>

                               <legend>Planes de Auditoría y/o Acciones de Control</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planaudit3" id="acc_planaudit3" value="3" 
                                       onclick="update_plan_option('acc_planaudit',3)" <?php if ($acc_planaudit == 3) echo "checked='checked'"; ?>  />
                                       Gestiona el plan de <?=$entity_nombre?>
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planaudit1" id="acc_planaudit1" value="1" 
                                       onclick="update_plan_option('acc_planaudit',1)" <?php if ($acc_planaudit >= 1) echo "checked='checked'"; ?>  />
                                       Gestiona los planes de las UEBs, direcciones funcionales y departamentos a los que  pertenece
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planaudit2" id="acc_planaudit2" value="2" 
                                       onclick="update_plan_option('acc_planaudit',2)" <?php if ($acc_planaudit >= 2) echo "checked='checked'"; ?>  />
                                       Puede agregar auditorías o acciones de control al plan de <?=$entity_nombre?>
                                   </label>
                               </div>

                               <legend>Planes de Medidas y de Acciones correctivas / correctoras</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planheal3" id="acc_planheal3" value="3" 
                                       onclick="update_plan_option('acc_planheal',3)" <?php if ($acc_planheal == 3) echo "checked='checked'"; ?>  />
                                       Gestiona el plan de <?=$entity_nombre?>
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planheal1" id="acc_planheal1" value="1" 
                                       onclick="update_plan_option('acc_planheal',1)" <?php if ($acc_planheal >= 1) echo "checked='checked'"; ?>  />
                                       Gestiona los planes de las UEBs, direcciones funcionales y departamentos a los que  pertenece
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planheal2" id="acc_planheal2" value="2" 
                                       onclick="update_plan_option('acc_planheal',2)" <?php if ($acc_planheal >= 2) echo "checked='checked'"; ?>  />
                                       Puede agregar tareas o acciones al plan de <?=$entity_nombre?>
                                   </label>
                               </div>

                               <legend>Gestión de Proyectos</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planproject3" id="acc_planproject3" value="3" 
                                       onclick="update_plan_proyecto()" <?php if ($acc_planproject == 3) echo "checked='checked'"; ?>  />
                                       Gestiona todos los proyectos que involucran a <?=$entity_nombre?>
                                   </label>
                               </div>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_planproject1" id="acc_planproject1" value="1" 
                                       onclick="update_plan_proyecto()" <?php if ($acc_planproject >= 1) echo "checked='checked'"; ?>  />
                                       Gestiona los proyectos que involucran a las UEBs, direcciones funcionales y departamentos a los que pertenece
                                   </label>
                               </div>

                               <!-- ARCHIVOS -------------------------------------->
                               <legend>Gestión de Archivos</legend>
                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="acc_archive3" id="acc_archive3" value="3" 
                                       onclick="update_archive()" <?php if ($acc_archive == 3) echo "checked='checked'"; ?>  />
                                       Es Jefe de Despacho de <?=$entity_nombre?>. Gestiona los Documentos e Indicaciones derivadas de la Oficina de Archivos.
                                   </label>
                               </div>

                               <div class="checkbox">
                                   <label>
                                       <input type="checkbox" name="nivel_archive4" id="nivel_archive4" value="1" 
                                       onclick="update_archive()" <?php if ($acc_archive || $nivel_archive4) echo "checked='checked'"; ?>  />
                                       Se le asignan responsabilidades con las indicaciones que se originan en las Oficinas de Archivos;
                                   </label>
                               </div>

                               <div class="checkbox row">
                                   <div class="col-8">
                                        <label>
                                            <input type="checkbox" name="acc_archive2" id="acc_archive2" value="2" 
                                            onclick="update_archive()" <?php if ($acc_archive == 2 || $acc_archive == 3) echo "checked='checked'"; ?>  />
                                            Tiene acceso a la Oficina de Archivos de <?=$entity_nombre?>.
                                        </label>
                                   </div>
                                   <div id="div_nivel_archive2" class="row col-4">
                                       <label class="label-control col-6">
                                           Nivel de acceso:
                                       </label>
                                       <div class="col-6">
                                            <select id="nivel_archive2" name="nivel_archive2" class="form-control">
                                                <option <?=$nivel_archive2 == _USER_REGISTRO_ARCH ? "selected='selected'" : ""?> value="<?=_USER_REGISTRO_ARCH?>">Registro</option>
                                                <option <?=$nivel_archive2 == _USER_CONSULTOR_ARCH ? "selected='selected'" : ""?> value="<?=_USER_CONSULTOR_ARCH?>">Consultor</option>
                                            </select>
                                       </div>
                                   </div>
                               </div>

                               <div class="checkbox row">
                                   <div class="col-8">
                                        <label>
                                            <input type="checkbox" name="acc_archive1" id="acc_archive1" value="1" 
                                            onclick="update_archive()" <?php if ($acc_archive == 1) echo "checked='checked'"; ?>  />
                                            Accede a la Oficina de Archivo de las UEBs, direcciones funcionales y departamentos a los que pertenece.
                                        </label>
                                   </div>
                                   <div id="div_nivel_archive3" class="row col-4">
                                       <label class="label-control col-6">
                                           Nivel de acceso:
                                       </label>
                                       <div class="col-6 mt-3">
                                            <select id="nivel_archive3" name="nivel_archive3" class="form-control">
                                                <option <?=$nivel_archive3 == _USER_REGISTRO_ARCH ? "selected='selected'" : ""?> value="<?=_USER_REGISTRO_ARCH?>">Registro</option>
                                                <option <?=$nivel_archive3 == _USER_CONSULTOR_ARCH ? "selected='selected'" : ""?> value="<?=_USER_CONSULTOR_ARCH?>">Consultor</option>
                                            </select>
                                       </div>
                                   </div>
                               </div>
                            </div>
                        </div><!-- acceso a planes generales -->

                        <!-- asignacion de subordinados -->
                        <div class="tabcontent" id="tab3">
                            <div id="ajax-tab-users">

                            </div>
                        </div><!-- asignacion de subordinados -->


                        <!-- procesos de los que participa -->
                       <div class="tabcontent" id="tab4">
                        <?php
                        $id= $id_usuario;

                        $obj_prs= new Tproceso($clink);
                        !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                        $obj_prs->SetIdProceso($_SESSION['nivel'] != _GLOBALUSUARIO ? $id_entity : $_SESSION['local_proceso_id']);
                        $obj_prs->SetTipo($_SESSION['nivel'] != _GLOBALUSUARIO ? $entity_tipo : $_SESSION['local_proceso_tipo']);
                        $result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', true, null, false);

                        if (!empty($id_usuario)) {
                            $obj_prs->SetIdUsuario($id_usuario);
                            $array_procesos= $obj_prs->get_procesos_by_user();
                        }

                        $name_form= "fusuario";
                        $id_prs_restrict= $id_proceso;
                        $restrict_up_prs= false;
                        $restrict_prs= null;
                        
                        $create_select_input= false;
                        require "inc/proceso_tabs.inc.php";
                       ?>
                      </div><!-- procesos de los que participa -->

                        <!-- buttom -->
                        <?php $aceptar= is_null($obj->GetEliminado()) ? "Aceptar" : "Restituir"; ?>
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset" onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <?php if ($config->ldap_login) { ?>
                            <button class="btn btn-success" type="reset" onclick="fix_ldap()">Reconectar LDAP</button>
                            <?php } ?>
                            <button class="btn btn-danger" type="button" onclick="open_help_window('../help/02_usuarios.htm#02_4.2')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div>  <!-- container -->

    </div>
 </body>
</html>

