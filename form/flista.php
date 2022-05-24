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
require_once "../php/class/lista.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/badger.class.php";

require_once "../php/class/code.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$signal= !empty($_GET['signal']) ?  $_GET['signal']: 'flista';

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_planaudit();

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tlista($clink);
}

$id_lista= $obj->GetIdLista();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['local_proceso_id'];

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year= date('Y');

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();
$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$componente= !is_null($_GET['componente']) ? $_GET['componente'] : $obj->GetComponente();

$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : $obj->GetIdTipo_lista();
if (empty($id_tipo_lista)) 
    $id_tipo_lista= 0;

$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();

$_inicio= $year - 5;
$_fin= $year + 5;

if (empty($inicio)) 
    $inicio= $year;
if (empty($fin)) 
    $fin= $_fin;

$obj->SetYear($year);

$obj_prs= new Tproceso($clink);

$url_page= "../form/flista.php?signal=$signal&action=$action&menu=flista&exect=$action&descripcion=$descripcion";
$url_page.= "&year=$year&nombre=$nombre&componente=$componente&id_tipo_lista=$id_tipo_lista";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>GUIA DE CONTROL INTERNO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/custom.css">

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/lista.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        if (parseInt($('#inicio').val()) > parseInt($('#fin').val())) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la aplicación del Requisito no puede ser superior al año en que finaliza.");
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre de la lguia.');
            return;
        }
        if ($("#cant_multiselect-prs").val() == 0) {
            alert("No ha seleccionado los procesos que utilizan esta Guia de Control o GLista de Chequeo.");
            return;
        }

        document.forms['formLista'].action = '../php/lista.interface.php';
        document.forms['formLista'].submit();
    }

    function cerrar(error) {
        table_ajax($('#exect').val());
        CloseWindow('div-panel-requisito');

        if (error.length > 0) {
            alert(error);
        }
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#nav-tab3 .nav-link').click(function() {
            var _url = '?action=<?=$action?>&id_lista=' + $('#id_lista').val() + '&id_proceso=';
                _url += $('#id_proceso').val() + '&signal=flista' + '&min_year=' + $('#inicio').val();
                _url+= '&max_year=' + $('#fin').val();
            self.location.href = 'ltipo_lista.php' + _url;
        });
        $('#nav-tab4 .nav-link').click(function() {
            var _url = '?action=<?=$action?>&id_lista=' + $('#id_lista').val() + '&id_proceso=';
                _url +=  $('#id_proceso').val() + '&signal=flista' + '&min_year=' + $('#inicio').val();;
                _url+= '&max_year=' + $('#fin').val();
            self.location.href = 'llista_requisito.php' + _url;
        });

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

        try {
            $('#descripcion').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">GUIA DE CONTROL INTERNO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item" title="Descripcion"><a class="nav-link" href="tab2">Descripción</a></li>
                        <?php if (!empty($id_lista)) { ?>
                        <li id="nav-tab3" class="nav-item" title="Estructura de la lista"><a class="nav-link" href="tab3">Estructura</a></li>
                        <li id="nav-tab4" class="nav-item" title="Requisitos a chequear"><a class="nav-link" href="tab4">Requisitos</a></li>
                        <?php } ?>
                    </ul>

                    <form id="formLista" class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" name="menu" value="flista" />
                        <input type="hidden" id="signal" name=signal value="<?=$signal?>" />

                        <input type="hidden" id="id" name="id" value="<?=$id_lista?>" />
                        <input type="hidden" id="id_lista" name="id_lista" value="<?=$id_lista?>" />
                        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" name="year" id="year-panel" value="<?=$year?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-1">
                                    Años:
                                </label>
                                <label class="col-form-label col-lg-1">
                                    Inicia
                                </label>
                                <div class=" col-lg-2">
                                    <select name="inicio" id="inicio" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $inicio) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <label class="col-form-label col-lg-1">
                                    Termina
                                </label>
                                <div class=" col-lg-2">
                                    <select name="fin" id="fin" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $fin) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-1">
                                    Título:
                                </label>
                                <div class="col-lg-11">
                                    <textarea type="text" id="nombre" name="nombre" rows="2"
                                        class="form-control"><?=$nombre?></textarea>
                                </div>
                            </div>

                            <?php
                            unset($obj_prs);
                            unset($array_procesos);
                            $array_procesos= null;

                            $obj_prs= new Tproceso_item($clink);

                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3)
                                $obj_prs->set_use_copy_tprocesos(false);
                            else
                                $obj_prs->set_use_copy_tprocesos(true);

                            $obj_prs->SetIdProceso($id_proceso);
                            $obj_prs->set_acc($acc);
                            $obj_prs->SetConectado(null);
                            $obj_prs->SetIdResponsable(null);

                            if (!$config->show_group_dpto_risk) {
                                $result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', true, _TIPO_PROCESO_INTERNO, false);
                            } else {
                                $result_prs_array = $obj_prs->get_procesos_down_cascade(null, $id_proceso, _TIPO_PROCESO_INTERNO);
                            }
                            $cant_prs = $obj_prs->GetCantidad();

                            if (!empty($id_lista)) {
                                $obj_prs->SetIdLista($id_lista);
                                $obj_prs->SetYear($year);
                                $array_procesos= $obj_prs->GetProcesoLista();
                            }

                            $name_form= "flista";
                            $restrict_prs = null;
                            $restrict_up_prs = true;
                            $filter_by_toshow = true;
                            $create_select_input= false;

                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab1 Procesos-->


                        <!-- alacance -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="descripcion" id="descripcion"
                                class="form-control"><?=$descripcion?></textarea>
                        </div><!-- alacance -->

                        <div class="tabcontent" id="tab3">

                        </div>

                        <div class="tabcontent" id="tab4">

                        </div>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>
                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->
    </div>


    <!-- panel-requisito -->
    <div id="div-panel-requisito" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-title" class="panel-title win-drag col-10">REQUISITO A
                    VERIFICAR</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-panel-requisito');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax-requisito" class="card-body">

        </div>
    </div><!-- panel-requisito -->

</body>

</html>

<?php $_SESSION['obj']= serialize($obj); ?>