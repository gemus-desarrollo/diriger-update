<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal = !empty($_GET['signal']) ? $_GET['signal'] : null;

if ($action == 'add' && empty($_GET['id_redirect'])) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else
    $obj = new Tgrupo($clink);

$id_grupo = $obj->GetIdGrupo();
$redirect = $obj->redirect;
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

if (!empty($id_grupo)) {
    $obj->listar_usuarios();
    $array_usuarios = $obj->array_usuarios;
}

$time = new TTime();
$year = !empty($_SESSION['current_year']) ? $_SESSION['current_year'] : $time->GetYear();
$month = !empty($_SESSION['current_month']) ? $_SESSION['current_month'] : $time->GetMonth();
$day = !empty($_SESSION['current_day']) ? $_SESSION['current_day'] : $time->GetDay();

$user_date_ref = $year . '-' . $month . '-' . $day;

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetTipo($_SESSION['entity_tipo']);

$url_page = "../form/fgrupo.php?signal=$signal&action=$action&menu=grupo&exect=$action";
$url_page .= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>GRUPO DE USUARIOS</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
        <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <script type="text/javascript">
            function validar() {
                if (!Entrada($('#nombre').val())) {
                    $('#nombre').focus(focusin($('#nombre')));
                    alert('Introduzca el nombre del Grupo de Usuarios');
                    return;
                }
                if (parseInt($('#cant_multiselect-users').val()) == 0) {
                    alert("No pueden existir grupos sin usuarios asignados");
                    return;
                }

                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();
                document.forms[0].action = '../php/grupo.interface.php?menu=grupo';
                document.forms[0].submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $.ajax({
                    data:  {
                            "year" : <?=!empty($year) ? $year : date('Y')?>,
                            "user_ref_date" : '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                            "id_user_restrict" : <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                            "restrict_prs" : <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                            "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>
                        },
                    url:   'ajax/usuario_tabs_group.ajax.php',
                    type:  'post',
                    beforeSend: function () {
                        $("#ajax-tab-users").html("Procesando, espere por favor...");
                    },
                    success:  function (response) {
                        $("#ajax-tab-users").html(response);
                    }
                });                
                
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
                     <div class="card-header">GRUPOS DE USUARIOS</div>
                     <div class="card-body">

                        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                            <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                            <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Integrantes</a></li>
                        </ul>

                         <form class="form-horizontal" action="javascript:validar()" method="POST">
                             <input type="hidden" name="exect" value="<?= $action ?>" />
                             <input type="hidden" name="id" value="<?= $id_grupo ?>" />
                             <input type="hidden" id="menu" name="menu" value="grupo" />
                             <input type="hidden" id="user_date_ref" name="user_date_ref" value="<?=$user_date_ref ?>" />

                             <!-- generales -->
                             <div class="tabcontent" id="tab1">
                                 <div class="form-group row">
                                     <label class="col-form-label col-md-2">
                                         Nombre:
                                     </label>
                                     <div class="col-md-10">
                                         <input id="nombre" name="nombre" class="form-control" value="<?=$obj->GetNombre()?>" />
                                     </div>
                                 </div>
                                 <div class="form-group row">
                                     <label class="col-form-label col-md-2">
                                         Descripción:
                                     </label>
                                     <div class="col-md-10">
                                         <textarea name="descripcion" rows="8" id="descripcion" class="form-control"><?=$obj->GetDescripcion()?></textarea>
                                     </div>
                                 </div>
                             </div><!-- generales -->


                             <!-- Participantes -->
                            <div class="tabcontent" id="tab2">
                                <div id="ajax-tab-users">

                                </div>
                            </div><!-- Participantes -->

                             <!-- buttom -->
                             <div id="_submit" class="btn-block btn-app">
                                 <?php if ($action == 'update' || $action == 'add') { ?>
                                     <button class="btn btn-primary" type="submit">Aceptar</button>
                                 <?php } ?>
                                 <button class="btn btn-warning" type="reset" onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                                 <button class="btn btn-danger" type="button" onclick="open_help_window('../help/02_usuarios.htm#02_4.3')">Ayuda</button>
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
