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
require_once "../php/class/grupo.class.php";
require_once "../php/class/tablero.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'form';

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttablero($clink);
}

$id_tablero= $obj->GetIdTablero();
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$array_indicadores= null;
$array_usuarios= null;
$array_grupos= null;

if (!empty($id_tablero)) {
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;

    $obj->listar_indicadores(false, null);
    $array_indicadores= $obj->array_indicadores;
}

$time= new TTime();
$year= !empty($_SESSION['current_year']) ? $_SESSION['current_year'] : $time->GetYear();
$month= !empty($_SESSION['current_month']) ? $_SESSION['current_month'] : $time->GetMonth();
$day= !empty($_SESSION['current_day']) ? $_SESSION['current_day'] : $time->GetDay();

$user_date_ref= $year.'-'.$month.'-'.$day;
$id_user_restrict= 0;
$restrict_prs= array(_TIPO_PROCESO_INTERNO);

$obj_prs= new Tproceso($clink);

$url_page= "../form/ftablero.php?signal=$signal&action=$action&menu=tablero&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>TABLERO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>


    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        var text;
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre que le dará al tablero');
            return;
        }
        if (parseInt($('#t_cant_tab_ind').val()) > 0 && parseInt($('#cant_tab_ind').val()) == 0) {
            alert(
                "Los tableros deben contener al menos un indicador. Seleccione los indicadores a mostrar en el tablero.");
            return;
        }
        if (parseInt($('#t_cant_tab_user').val()) > 0 && parseInt($('#cant_tab_user').val()) == 0) {
            text = "Los tableros deben tener al menos un usuario que le acceda. ";
            text += "Por favor seleccione los usuarios o grupos de usuarios con accesos.";
            alert(text);
            return;
        }

        parent.app_menu_functions = false;
        $('#_submit').hide;
        $('#_submited').show();

        document.forms[0].action = '../php/tablero.interface.php';
        document.forms[0].submit();
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        if ($('#t_cant_indi').val() == 0) {
            $('#div-indicadores').hide();
        }

        $.ajax({
            data: {
                "year": <?=!empty($year) ? $year : date('Y')?>,
                "user_ref_date": '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                "id_user_restrict": <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                "restrict_prs": <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                "array_usuarios": <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                "array_grupos": <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>
            },
            url: 'ajax/usuario_tabs_simple.ajax.php',
            type: 'post',
            beforeSend: function() {
                $("#ajax-tab-users").html("Procesando, espere por favor...");
            },
            success: function(response) {
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
                <div class="card-header">TABLEROS DE CONTROL</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item" title=""><a class="nav-link" href="tab2">Indicadores</a></li>
                        <li id="nav-tab3" class="nav-item" title=""><a class="nav-link" href="tab3">Acceso de Usuarios</a></li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id_tablero?>" />
                        <input type="hidden" name="menu" value="tablero" />

                        <input type="hidden" id="user_date_ref" name="user_date_ref" value="<?=$user_date_ref?>" />


                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Nombre:
                                </label>
                                <div class="col-md-10">
                                    <input id="nombre" name="nombre" class="form-control" maxlength="30"
                                        value="<?=$obj->GetNombre()?>">
                                </div>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="use_perspectiva" id="use_perspectiva" value=1
                                        <?php if ($obj->use_perspectiva) echo "checked=checked'" ?> />
                                    En el tablero de control se mostraran los indicadores dentro de las PERSPECTIVAS
                                    definidas para el Cuadro de Mando Integral.
                                </label>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Propósito:
                                </label>
                                <div class="col-md-10">
                                    <textarea name="descripcion" rows="6" id="descripcion"
                                        class="form-control"><?=$obj->GetDescripcion()?></textarea>
                                </div>
                            </div>
                        </div> <!-- generales -->


                        <!-- usuarios con acceso al tablero -->
                        <div class="tabcontent" id="tab3">
                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- usuarios con acceso al tablero -->


                        <!-- indicadors -->
                        <div class="tabcontent" id="tab2">
                            <div id="div-indicadores">
                                <?php
                                $year= null; // para que salgan todos o indicadores sin considerr el año
                                $create_select_input= false;
                                require "inc/indicador.inc.php";
                                ?>
                            </div>

                            <script language="javascript">
                            if (document.getElementById('t_cant_indi').value == 0) {
                                box_alarm(
                                    "No existen indicadores definidos en el sistema. Por favor, deberá definir los indicadores y luego acceder a esta funcionalidad."
                                    );
                            }
                            </script>
                        </div> <!-- indicadors -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/11_indicadores.htm#11_16.1')">Ayuda</button>
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