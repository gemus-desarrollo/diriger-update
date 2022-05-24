<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/programa.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/peso.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tprograma($clink);
}

$id= $obj->GetIdPrograma();
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day= !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];

$time= new TTime;
if(empty($year))
    $year= $time->GetYear();
if(empty($month))
    $month= $time->GetMonth();
if(empty($day))
    $day= $time->GetDay();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['current_proceso_id'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
if (empty($inicio)) 
    $inicio= $year;

$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();
if (empty($fin)) 
    $fin= $year;

require_once "inc/escenario.ini.inc.php";

if ($year > $_fin) 
    $year= $_fin;
if ($year < $_inicio) 
    $year= $_inicio;

$_inicio-= 10;
$_fin+= 10;

$obj_peso= new Tpeso($clink);

if (!empty($id)) {
    $obj_peso->SetInicio($inicio);
    $obj_peso->SetFin($fin);
    $obj_peso->SetIdPrograma($id);
}

$id_proceso_sup= $id_proceso;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$nombre_prs= $obj_prs->GetNombre();
$conectado= $obj_prs->GetConectado();

$url_page= "../form/fprograma.php?signal=$signal&action=$action&menu=programa&exect=$action&year=$year";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>PROGRAMA</title>

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

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());
        var year = $('#year').val();

        if (parseInt(inicio) > parseInt(fin)) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio del Programa no puede ser superior al año de en el que termina");
            return;
        }

        var url = '&id_proceso=' + id_proceso + '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre +
            '&descripcion=' + descripcion;
        url += '&year=' + year;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location.href = 'fprograma.php?version=&action=<?php echo $action?>' + url;
    }


    function validar() {
        if (parseInt($('#year').val()) < parseInt($('#inicio').val()) || parseInt($('#year').val()) > parseInt($('#fin')
                .val())) {
            $('#year').focus(focusin($('#year')));
            alert("El año de referencia tiene que estar entre los años de vigencia del Programa.");
            return;
        }

        if (parseInt($('#inicio').val()) > parseInt($('#fin').val())) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio del programa no puede ser superior al año en que finaliza.");
            return;
        }

        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre');
            return;
        }

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        document.forms[0].action = '../php/programa.interface.php';
        document.forms[0].submit();
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        if ($('#t_cant_indi').val() == 0) {
            $('#div-indicadores').hide();
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
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">PROGRAMAS</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1 class="nav-item"" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"
                            title="Indicadores asociados al seguimiento del Programa. Ponderación de su impacto sobre los resultados de este Programa">
                            <a class="nav-link" href="tab2">Efecto de los Indicadores</a>
                        </li>
                        <li id="nav-tab3" class="nav-item"
                            title="Entidades, Direcciones o Procesos involucradas en el Programa, las cuales se mantendrán informadas sobre su evolución o desempeño">
                            <a class="nav-link" href="tab3">Procesos / Unidades Organizativas Involucradas</a>
                        </li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id?>" />
                        <input type="hidden" name="menu" value="programa" />


                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-md-2">
                                    Desde:
                                </label>
                                <div class=" col-md-2">
                                    <select name="inicio" id="inicio" class="form-control input-sm"
                                        onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-md-2">
                                    Hasta:
                                </label>
                                <div class=" col-md-2">
                                    <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Año de referencia:
                                </label>
                                <div class=" col-md-2">
                                    <select name="year" id="year" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class=" col-md-8">
                                    <label class="alert alert-info">
                                        Año para el cual son fijadas las ponderaciones de los efectos de los indicadores
                                        sobre los resultados del Programa.
                                    </label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Unidad Organizativa o Proceso:
                                </label>
                                <div class="col-md-9">
                                    <?php
                                    $top_list_option= "seleccione........";
                                    $id_list_prs= null;
                                    $order_list_prs= 'eq_asc_desc';
                                    $reject_connected= false;
                                    $in_building= ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs= ($action == 'add') ? true : false;

                                    $id_select_prs= $id_proceso;
                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Nombre:
                                </label>
                                <div class="col-md-10">
                                    <input type="text" id="nombre" name="nombre" class="form-control input-sm"
                                        value="<?= $nombre ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Descripción:
                                </label>
                                <div class=" col-md-10">
                                    <textarea id="descripcion" name="descripcion" class="form-control input-sm"
                                        rows="4"><?= $descripcion ?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->

                        <!-- relacion de los procesos a los que pertenece -->
                        <div class="tabcontent" id="tab3">
                            <?php
                            unset($obj_prs);
                            $obj_prs= new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                            $obj_prs->set_use_copy_tprocesos(false);

                            $result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', false, _TIPO_ARC, false, 'asc');
                            $cant_prs= $obj_prs->GetCantidad();

                            if ($cant_prs > 0) $clink->data_seek($result_prs);

                            if (!empty($id)) {
                                $obj_prs->SetIdProyecto(null);
                                $obj_prs->SetIdPrograma($id);
                                $array_procesos= $obj_prs->GetProcesosProyecto();
                            }

                            $restrict_up_prs= false;
                            $id_restrict_prs= $id_proceso;
                            $filter_by_toshow = true;
                            $create_select_input= false;

                            $restrict_prs= array(_TIPO_GRUPO, _TIPO_DEPARTAMENTO, _TIPO_ARC, _TIPO_PROCESO_INTERNO);
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- relacion de los procesos a los que pertenece -->


                        <!-- indicadors del programa -->
                        <div class="tabcontent" id="tab2">
                            <legend>
                                Ponderación del Impacto de los indicadores sobre el avance del Programa
                            </legend>

                            <div id="div-indicadores">
                                <?php
                                $obj_peso->SetIdPrograma($id);
                                $obj_peso->SetYear($year);

                                $array_indicadores= null;
                                if (!empty($id)) {
                                    $obj_peso->listar_indicadores_ref_programa($id, false, true);
                                    $array_indicadores= $obj_peso->array_indicadores;
                                }

                                $id_list_prs= $prs['id'];
                                $create_select_input= true;
                                require "inc/indicador.inc.php";
                                ?>
                            </div>

                            <script type="text/javascript">
                            if (document.getElementById('t_cant_indi').value == 0) {
                                box_alarm(
                                    "No existen indicadores definidos en el sistema. Por favor, deberá definir los indicadores y luego acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div> <!-- indicadors del programa -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/13_programas.htm#13_24.1')">Ayuda</button>
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