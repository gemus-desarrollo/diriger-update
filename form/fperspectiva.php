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
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";

require_once '../php/class/badger.class.php';

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tperspectiva($clink);
}

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day= !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['current_proceso_id'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['local_proceso_id'];

$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$color= !empty($_GET['color']) ? urldecode($_GET['color']) : $obj->GetColor();
$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();
$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();

if (empty($fin)) 
    $fin= $year;
if (empty($inicio)) 
    $inicio= $year;

$id= $obj->GetIdPerspectiva();
$id_perspectiva= $id;
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$obj_peso= new Tpeso($clink);
$obj_peso->SetYear($year);

if (!empty($id_inductor)) {
    $obj_peso->SetInicio($inicio);
    $obj_peso->SetFin($fin);
    $obj_peso->SetIdPerspectiva($id);
}

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$_nombre= $obj_prs->GetNombre();
$_tipo= $obj_prs->GetTipo();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$url_page= "../form/fperspectiva.php?signal=$signal&action=$action&menu=perspectiva&exect=";
$url_page.= "$action&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>PERSPECTIVA</title>

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

    <script type="text/javascript" charset="utf-8" src="../js/jscolor.js?version="></script>

    <script language="javascript">
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());
        var color = encodeURI($('#color').val());
        var year = $('#year').val();
        var numero = $('#numero').val();

        var url = '&id_proceso=' + id_proceso + '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre;
        url += '&year=' + year + '&numero=' + numero + '&descripcion=' + descripcion + '&color=' + color;

        self.location.href = 'fperspectiva.php?version=&action=<?=$action?>' + url;
    }

    function _validar() {
        if (parseInt($('#t_cant_objt').val()) > 0 && parseInt($('#cant_objt').val()) == 0) {
            var text = "No ha vinculado la perspectiva con ninguno de los Objetivos de trabajo vigentes en el año, " +
                "en las Unidades Organizativas o procesos que le estan subordinados. ¿Desea continuar?.";
            confirm(text, function(ok) {
                if (!ok)
                    return;
                else {
                    parent.app_menu_functions = false;
                    $('#_submit').hide();
                    $('#_submited').show();

                    document.forms[0].action = '../php/perspectiva.interface.php';
                    document.forms[0].submit();
                }
            });
        } else {
            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            document.forms[0].action = '../php/perspectiva.interface.php';
            document.forms[0].submit();
        }
    }

    function validar() {
        if ($('#inicio').val() > $('#fin').val()) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la vigencia de la perspectiva no puede ser superior al año en que finaliza.");
            return;
        }
        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            alert(
                "No ha especificado un número para identificar la Perspectiva. De este número dependerá el orden al listarla.");
            return;
        }
        if (!Entrada($('#color').val()) || $('#color').val() == 'FFFFFF') {
            $('#color').focus(focusin($('#color')));
            alert("No ha especificadoel color para identificar la Perspectiva.");
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre de la perspectiva');
            return;
        }
        if (!Entrada($('#color').val()) || $('#color').val() == 'FFFFFF') {
            $('#color').focus(focusin($('#color')));
            alert('Debe seleccionar un color para la perspectiva');
            return;
        }
        if (!Entrada($('#descripcion').val())) {
            $('#descripcion').focus(focusin($('#descripcion')));
            confirm('No ha introducido una descripción para la perspectiva. ¿Desea continuar?', function(ok) {
                if (!ok)
                    return;
                else {
                    if (!_this())
                        return;
                }
            });
        } else {
            if (!_this())
                return;
        }

        function _this() {
            if (parseInt($('#t_cant_multiselect-inds').val()) > 0 && parseInt($('#cant_multiselect-inds').val()) == 0) {
                var text =
                    "La Perspectiva no está relacionado con ningún indicador. Debería relacionar la perspectiva " +
                    "al menos con un indicador para que el sistema pueda evaluar su cumplimiento. ¿Desea continuar?";
                confirm(text, function(ok) {
                    if (ok)
                        _validar();
                    else
                        return false;
                });
            } else
                _validar();
        }
    }
    </script>

    <script type="text/javascript">
    function set_numero(val) {
        $('#numero').val(val);
    }

    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        if (parseInt($('#t_cant_objt').val()) == 0) {
            $('#div-inductores').hide();
        }
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
                <div class="card-header">PERSPECTIVAS</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"
                            title="Ponderación del impacto o efecto de los Indicadores sobre el resultado de la Perspectiva">
                            <a class="nav-link" href="tab2">Efecto de los Indicadores</a></li>
                        <li id="nav-tab3" class="nav-item"
                            title="Ponderación del impacto de sus Objetivos de Trabajo y los de las Direcciones y procesos que le estan directamente subordinados">
                            <a class="nav-link" href="tab3">Objetivos de Trabajo</a></li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id?>" />
                        <input type="hidden" name="menu" value="perspectiva" />

                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" id="month" name="month" value="<?=$month?>" />
                        <input type="hidden" id="day" name="day" value="<?=$day?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-md-2">
                                    Desde:
                                </label>
                                <div class=" col-md-3">
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
                                <div class=" col-md-3">
                                    <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <label class="alert alert-info">
                                Periodo en el que será gestionado el Objetivo de Estrategico. Es el intervalo de años en
                                el que esta vigente y constituye una meta para la organización, por lo que es calculado
                                o medible por el sistema.
                            </label>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Año de referencia:
                                </label>
                                <div class=" col-md-3">
                                    <select name="year" id="year" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class=" col-md-7">
                                    <label class="alert alert-info">
                                        Año a partir del cual son fijadas las ponderaciones del efecto de los
                                        indicadores sobre el resultado de la Perspectiva.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 col-md-2 col-lg-2">
                                    Perspectiva No.:
                                </label>
                                <div class="col-sm-4 col-md-2 col-lg-4">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control"
                                            value="<?=$numero?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa">
                                                    <span class="fa fa-caret-up"></span></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa">
                                                    <span class="fa fa-caret-down"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <label class="col-form-label col-sm-5 col-md-5 col-lg-2">
                                    Color (Click aqui):
                                </label>
                                <div class="col-3">
                                    <input type="text" name="color" id="color" class="color form-control"
                                        value="<?=$color?>" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 col-md-3 col-lg-2">
                                    Unidad Organizativa:
                                </label>
                                <div class="col-sm-9 col-md-9 col-lg-10">
                                    <?php
                                    $top_list_option= "seleccione........";
                                    $id_list_prs= null;
                                    $order_list_prs= $id_proceso == $_SESSION['local_proceso_id'] ? 'eq_desc' : 'eq_asc_desc';
                                    $reject_connected= false;
                                    $in_building= ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs= ($action == 'add') ? true : false;

                                    $id_select_prs= $id_proceso;
                                    $restrict_prs = !$config->dpto_with_objetive ? array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO) : array(_TIPO_ARC, _TIPO_PROCESO_INTERNO) ;
                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Nombre:
                                </label>
                                <div class="col-sm-10 col-md-10">
                                    <input id="nombre" name="nombre" class="form-control input-sm"
                                        value="<?= $nombre ?>" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-5">
                                    Impacto sobre la evaluación de <?="{$_nombre}, ".$Ttipo_proceso_array[$_tipo]?>:
                                </label>
                                <div class=" col-4">
                                    <select name="peso" class="form-control">
                                        <?php for ($k= 2; $k < 8; ++$k) { ?>
                                        <option value="<?=$k?>" <?php if ($k == $peso) echo "selected='selected'"?>>
                                            <?=$Tpeso_inv_array[$k]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Descripción:
                                </label>
                                <div class="col-md-10">
                                    <textarea name="descripcion" rows="4" id="descripcion"
                                        class="form-control input-sm"><?=$descripcion?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->

                        <!-- indicadors -->
                        <div class="tabcontent" id="tab2">
                            <legend>
                                Ponderación del Impacto de los indicadores sobre esta Perspectiva
                            </legend>

                            <div id="div-indicadores">
                                <?php
                                $obj_peso->SetIdPerspectiva($id_perspectiva);
                                $obj_peso->SetYear($year);
                                $obj_peso->SetIdProceso($id_proceso);

                                $array_indicadores= null;
                                if (!empty($id))
                                    $array_indicadores= $obj_peso->listar_indicadores_ref_perspectiva($id_perspectiva, false);

                                $id_list_prs= $prs['id'];
                                $create_select_input= true;
                                require "inc/indicador.inc.php";
                                ?>
                            </div>

                            <script language="javascript">
                            if (document.getElementById('t_cant_multiselect-inds').value == 0) {
                                box_alarm(
                                    "No existen indicadores definidos en el sistema. Por favor, deberá definir los indicadores y luego acceder a esta funcionalidad."
                                    );
                            }
                            </script>
                        </div> <!-- indicadors -->


                        <!-- impacto de sus inductores o el de los procesos subordinados -->
                        <div class="tabcontent" id="tab3">
                            <legend>
                                Relación de Objetivos de Trabajo pertenecientes a la perspectiva
                            </legend>

                            <div id="div-inductores">
                                <table class="table table-striped" data-toggle="table" data-height="330"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Pertenece ?</th>
                                        <th>Objetivo</th>
                                    </thead>

                                    <?php
                                    $cant_objt= 0;
                                    $array_pesos = null;
                                    if (!empty($id_perspectiva))
                                        $array_pesos= $obj_peso->listar_inductores_ref_perspectiva($id_perspectiva, false);

                                    if (isset($obj_prs)) unset($obj_prs);
                                    $obj_prs = new Tproceso($clink);
                                    !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                                    $_id_proceso = !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
                                    $obj_prs->SetIdProceso($_id_proceso);
                                    $obj_prs->listar_in_order('eq_desc', false, null, false, 'asc');

                                    $use_weight_select= false;
                                    foreach ($obj_prs->array_procesos as $prs) {
                                        $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                        $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                        if ($prs['id'] != $_SESSION['local_proceso_id'])
                                            $_connect = ($_connect != 1) ? 1 : 0;
                                        else
                                            $_connect = 0;

                                        $id_list_prs = $prs['id'];
                                        $with_null_perspectiva = _PERSPECTIVA_ALL;
                                        $restrict_list_to_entity= false;
                                        include "inc/inductor_tabs.inc.php";
                                    }
                                    ?>
                                </table>
                            </div>

                            <input type="hidden" name="cant_objt" id="cant_objt" value="<?=$i_objt?>" />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value="<?=$cant_objt?>" />

                            <script language="javascript">
                            if (parseInt(document.getElementById('t_cant_objt').value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Trabajo en el sistema a este nivel de Dirección o Procesos o para sus niveles subordinados. Deberá definirlos para poder acceder a esta funcionalidad."
                                    );
                            }
                            </script>
                        </div><!-- impacto de sus inductores o el de los procesos subordinados -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/08_perspectivas.htm#08_10.2')">Ayuda</button>
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