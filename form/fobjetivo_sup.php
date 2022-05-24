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
require_once "../php/class/objetivo.class.php";
require_once "../php/class/objetivo_ci.class.php";
require_once "../php/class/politica.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$signal= !empty($_GET['signal'])?  $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add')
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tobjetivo_ci($clink);
}

$id_objetivo= $obj->GetIdObjetivo();
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day= !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];

$id_proceso= $_GET['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
$obj_prs->SetIdUsuario(null);
$id_proceso_sup= $obj_prs->get_proceso_top($_SESSION['id_entity'], $_SESSION['entity_tipo']);

if (!empty($id_proceso_sup)) {
    $obj_prs->Set($id_proceso);
    $id_proceso_sup_code= $obj_prs->get_id_code();
    $tipo= $obj_prs->GetTipo();
    $nombre_sup= $obj_prs->GetNombre().' '.$Ttipo_proceso_array[(int)$tipo];
}

$numero= $_GET['numero'];
if (empty($numero)) {
    $obj->SetYear($year);
    !empty($id_proceso_sup) ? $obj->SetIdProceso($id_proceso_sup) : $obj->SetIdProceso($id_proceso);
    $numero= $obj->GetNumero();
}

$inicio= $_GET['inicio'];
if (empty($inicio)) 
    $inicio= $obj->GetInicio();
if (empty($inicio)) 
    $inicio= $year;

$fin= $_GET['fin'];
if (empty($fin)) 
    $fin= $obj->GetFin();
if (empty($fin)) 
    $fin= $year;

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();

if (!empty($id_objetivo)) {
    $obj_peso= new Tpeso($clink);
    $obj_peso->SetYear($year);
    $obj_peso->SetIdObjetivo($id_objetivo);
}

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$url_page= "../form/fobjetivo.php?signal=$signal&action=$action&menu=objetivo&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>OBJETIVOS ESTRATEGICOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        var text;

        if ($('#inicio').val() > $('#fin').val()) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio del objetivo no puede ser superior al año en que finaliza.");
            return;
        }
        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            alert(
                "No ha especificado un número para identificar este Objetivo. De este número dependerá el orden al listarlo."
            );
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el objetivo estratégico');
            return;
        }

        if (!Entrada($('#descripcion').val())) {
            $('#descripcion').focus(focusin($('#descripcion')));

            text = "No ha descrito una estrategia para alcanzar el Objetivo estratégico propuesto. ";
            text +=
                "Esto no es obligatorio, pero si necesario si desea que el objetivo sea bien entendido por los demás. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok) 
                    return;
                else {
                    if (!this_1()) 
                        return;
                }
            });
        } else {
            if (!this_1()) 
                return;
        }

        function this_1() {
            if (parseInt($('#t_cant_obji').val()) > 0 && parseInt($('#cant_obji').val()) == 0) {
                text =
                    "No ha vinculado el Objetivo estratégico con ninguno de los Objetivos de trabajo vigentes en el año. ";
                text += "¿Desea continuar?.";
                confirm(text, function(ok) {
                    if (!ok) 
                        return false;
                    else {
                        parent.app_menu_functions = false;
                        $('#_submit').hide();
                        $('#_submited').show();

                        document.forms[0].action = '../php/objetivo.interface.php';
                        document.forms[0].submit();
                    }
                });
            } else {
                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();

                document.forms[0].action = '../php/objetivo.interface.php';
                document.forms[0].submit();
            }
        }
    }


    var trId;

    function refreshp() {
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURIComponent($('#descripcion').val());

        var numero = $('#numero').val();
        var year = $('#year').val();

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        url = '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre + '&descripcion=' + descripcion + '&numero=' +
            numero;
        self.location = 'fobjetivo_sup.php?version=&action=<?=$action?>' + url;
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        function set_numero(val) {
            $('#numero').val(val);
        }

        if ($('#t_cant_obji').val() == 0) {
            $('#div-objetivos').hide();
        }

        tinymce.init({
            selector: '#descripcion',
            theme: 'modern',
            height: 300,
            language: 'es',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
                '| bullist numlist outdent indent | removeformat | help',

            content_css: '../css/content.css'
        });

        <?php if (empty($id_proceso_sup)) { ?>
        var text =
            "No se ha definido una unidad o proceso superior. Debe tener Unidad organizativa o porceso superior ";
        text += "para definir los objetivos superiores.";
        alert(text, function(ok) {
            self.location.href = '<?php prev_page() ?>';
        });

        <?php } ?>

        try {
            $('#descripcion').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>") <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">OBJETIVO ESTRATÉGICO DEL ORGANO DE DIRECCIÓN SUPERIOR</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Descripción</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Impacto de los Objetivos Estrategicos de
                                <?=$_SESSION['empresa']?></a></li>
                    </ul>
     
                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id_objetivo ?>" />
                        <input type="hidden" name="menu" value="objetivo" />

                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" id="month" name="month" value="<?=$month?>" />
                        <input type="hidden" id="day" name="day" value="<?=$day?>" />

                        <input type="hidden" id="if_control_interno" name="if_control_interno" value="0" />
                        <input type="hidden" id="if_objsup" name="if_objsup" value="1" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <?php if (!empty($id_proceso_sup)) { ?>
                            <label class="alert alert-info">
                                Organo Superior de Dirección: <strong><?= $nombre_sup ?></strong>
                            </label>
                            <?php } ?>

                            <input type="hidden" id="proceso_code_<?= $id_proceso_sup ?>"
                                name="proceso_code_<?= $id_proceso_sup ?>" value="<?= $id_proceso_sup_code ?>" />
                            <input type="hidden" id="proceso<?= $id_proceso_sup ?>" name="proceso<?= $id_proceso_sup ?>"
                                value="<?= $id_proceso_sup ?>" />
                            <input type="hidden" name="proceso" id="proceso" value="<?= $id_proceso_sup ?>" />

                            <div class="form-group row">
                                <label class="col-form-label col-1">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-1">
                                    desde:
                                </label>
                                <div class=" col-2">
                                    <select name="inicio" id="inicio" class="form-control input-sm"
                                        onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-1">
                                    hasta:
                                </label>
                                <div class=" col-2">
                                    <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 col-md-2 col-lg-2">
                                    Objetivo No.:
                                </label>
                                <div class=" col-sm-9 col-md-10 col-lg-10">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control"
                                            value="<?=$numero?>">
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
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-1">
                                    Objetivo:
                                </label>
                                <div class="col-md-11 col-lg-11">
                                    <textarea id="nombre" name="nombre" class="form-control input-sm"
                                        rows="9"><?= $nombre ?></textarea>
                                </div>
                            </div>
                        </div> <!-- generales -->


                        <!-- Descripcion -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="descripcion" id="descripcion"
                                class="form-control"><?= $descripcion ?></textarea>
                        </div>

                        <!-- objetivos estrategicos de unidades de menor jerarquia -->
                        <div class="tabcontent" id="tab3">

                            <?php
                            $array_pesos= null;
                            $_cant= 0;

                            $obj_peso= new Tpeso($clink);
                            $obj_peso->SetIdProceso($_SESSION['id_entity']);
                            $obj_peso->SetYear($year);
                            $obj_peso->SetIdObjetivo_sup($id_objetivo);

                            if (!empty($id_objetivo)) {
                                $array_pesos= $obj_peso->listar_objetivos_ref_objetivo_sup($id_objetivo, false);
                                $_cant= $obj_peso->GetCantidad();
                            }

                            require_once "inc/_objetivo_tabs.inc.ini.php";
                            ?>

                            <div id="div-objetivos">
                                <table class="table table-striped" data-toggle="table" data-height="300"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $_connect= 0;
                                        $id_list_prs= $_SESSION['id_entity'];
                                        $proceso= $_SESSION['entity_nombre'];

                                        include "inc/objetivo_obji_tabs.inc.php";
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <input type="hidden" name="cant_obji" id="cant_obji" value="<?=$i_obji?>" />
                            <input type="hidden" name="t_cant_obji" id="t_cant_obji" value="<?=$cant_obji?>" />

                            <script language="javascript">
                            if (document.getElementById('t_cant_obji').value == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Estratégicos para la Organización <?=$_SESSION['empresa'] ?>. Deberá definirlos para poder acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div><!-- objetivos estrategicos de unidades de menor jerarquia -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/09_objetivos.htm#09_11.1')">Ayuda</button>
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