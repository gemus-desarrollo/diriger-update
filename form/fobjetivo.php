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
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo_ci.class.php";
require_once "../php/class/politica.class.php";
require_once "../php/class/inductor.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug'] = 'no';

$signal = !empty($_GET['signal']) ? $_GET['signal'] : null;
$action = !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj = new Tobjetivo_ci($clink);
}

$id_objetivo = $obj->GetIdObjetivo();
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year = !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month = !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day = !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];
if (empty($year)) 
    $year= date('Y');

$id_proceso = $_GET['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso = $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso = $_SESSION['current_proceso_id'];
if (empty($id_proceso)) 
    $id_proceso = $_SESSION['id_entity'];

if ($action == 'add') {
    $obj->SetYear($year);
    $obj->SetIdProceso($id_proceso);
}

$numero = !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$if_send_up = !empty($_GET['if_send_up']) ? $_GET['if_send_up'] : $obj->GetIfSend_up();
$if_send_down = !empty($_GET['if_send_down']) ? $_GET['if_send_down'] : $obj->GetIfSend_down();

$inicio = !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
if (empty($inicio)) 
    $inicio = $year;
$fin = !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();
if (empty($fin))
    $fin = $year;

$nombre = urldecode($_GET['nombre']);
if (empty($nombre)) 
    $nombre = $obj->GetNombre();

$descripcion = urldecode($_GET['descripcion']);
if (empty($descripcion)) 
    $descripcion = $obj->GetDescripcion();

$obj_peso = new Tpeso($clink);
$obj_peso->SetYear($year);

if (!empty($id_objetivo))
    $obj_peso->SetIdObjetivo($id_objetivo);

$obj_prs = new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo = $obj_prs->GetTipo();
$conectado = $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$url_page = "../form/fobjetivo.php?signal=$signal&action=$action&menu=objetivo";
$url_page .= "&exect=$action&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

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

    <style type="text/css">
    .title_pol {
        border-bottom: 1px;
        color: #000000;
        font-weight: bold;
        padding: 2px;
        padding-left: 5px;

        text-align: center;
        background-color: #42C1FF;
    }

    .title_pol span {
        margin-left: 10px;
    }

    .grupo {
        text-align: left;
        background-color: #D8D8D8;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        var text;

        if ($('#inicio').val() > $('#fin').val()) {
            $('#fin').focus(focusin($('#fin')));
            alert("El año de inicio del objetivo no puede ser superior al año en que finaliza.");
            return;
        }
        if (parseInt($('#year').val()) < parseInt($('#inicio').val()) || parseInt($('#year').val()) > parseInt($('#fin')
                .val())) {
            $('#year').focus(focusin($('#year')));
            alert("El año de referencia tiene que estar entre los años de vigencia del Objetivo.");
            return;
        }
        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            alert(
                "No ha especificado un número para identificar este Objetivo. De este número dependerá el orden al listarlo."
                );
            return;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert("No ha especificado la Unidad Organizativa o el Proceso al que pertenece el Objetivo Estratégico");
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el objetivo estratégico');
            return;
        }

        var conectado = $('#proceso_conectado_' + $('#proceso').val()).val();
        conectado = conectado != <?=_NO_LOCAL?> ? true : false;

        <?php if ($_SESSION['if_send_up'] || $_SESSION['if_send_down']) { ?>
        if (conectado && (!$('#if_send_up').is(':checked') && !$('#if_send_down').is(':checked'))) {
            $('#if_send_up').focus(focusin($('#if_send_up')));

            text =
                "No ha especificado la dirección en que migrará la información relativa a este Objetivo Estratégico. ";
            text += "Las direcciones superiores o subordinadas no recibirán información relativa a este Objetivo. ";
            text += "¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok)
                    return;
                else
                if (!this_1())
                    return;
            });
        } else {
            if (!this_1())
                return;
        }
        <?php } else { ?>
        if (!this_1())
            return;
        <?php } ?>

        function this_1() {
            if (!Entrada($('#descripcion').val())) {
                $('#descripcion').focus(focusin($('#descripcion')));

                text = "No ha descrito una estrategia para alcanzar el Objetivo Estratégico propuesto.  ";
                text += "Esto no es obligatorio, pero si necesario si desea que el objetivo sea bien ";
                text += "entendido por los demás. ¿Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return false;
                    else {
                        if (!this_2())
                            return false;
                        return this_3();
                    }
                });
            } else {
                if (!this_2())
                    return false;
                return this_3();
            }
        }

        function this_2() {
            if (parseInt($('#t_cant_objt').val()) > 0 && parseInt($('#cant_objt').val()) == 0) {
                text = "No ha vinculado el Objetivo estratégico con ninguno de los Objetivos de Trabajo ";
                text += "vigentes en el año. ¿Desea continuar?.";
                confirm(text, function(ok) {
                    if (!ok)
                        return false;
                    else {
                        if (!this_3())
                            return false;
                        return this_4();
                    }
                });
            } else {
                if (!this_3())
                    return false;
                return this_4();
            }
        }

        function this_3() {
            if (parseInt($('#t_cant_obji').val()) > 0 && parseInt($('#cant_obji').val()) == 0) {
                text = "No ha vinculado el Objetivo estratégico con ninguno de los Objetivos Estratégicos vigentes ";
                text +=
                    "en el año, en las Unidades Organizativas o procesos que le estan subordinados. ¿Desea continuar?.";
                confirm(text, function(ok) {
                    if (!ok)
                        return false;
                    else
                        this_4();
                });
            } else {
                this_4();
            }
        }

        function this_4() {
            parent.app_menu_functions = false;
            $('#_submit').hide()
            $('#_submited').show()

            document.forms[0].action = '../php/objetivo.interface.php';
            document.forms[0].submit();
        }
    }

    var trId;

    function refreshp() {
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var id_proceso = $('#proceso').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());

        var numero = $('#numero').val();
        var year = $('#year').val();
        if_send_up = $('#if_send_up').is(':checked') ? 1 : 0;
        if_send_down = $('#if_send_down').is(':checked') ? 1 : 0;

        parent.app_menu_functions = false;
        $('#_submit').hide()
        $('#_submited').show()

        var url = '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre + '&descripcion=' + descripcion;
        url += '&id_proceso=' + id_proceso + '&numero=' + numero;
        url += '&year=' + year + '&if_send_up=' + if_send_up + '&if_send_down=' + if_send_down;
        self.location = 'fobjetivo.php?version=&action=<?= $action ?>' + url;
    }
    </script>

    <script type="text/javascript">
    function set_numero(val) {
        $('#numero').val(val);
    }

    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        if (parseInt($('#t_cant_obji').val()) == 0) {
            $('#div-objetivos').hide();
        }
        if (parseInt($('#t_cant_objt').val()) == 0) {
            $('#div-inductores').hide();
        }
        if ($('#t_cant_objs').val() == 0) {
            $('#div-politicas').hide();
        }

        <?php if (!$_SESSION['if_send_up']) { ?>
        $('#if_send_up').attr("disabled", "disabled");
        <?php } ?>
        <?php if (!$_SESSION['if_send_down']) { ?>
        $('#if_send_down').attr("disabled", "disabled");
        <?php } ?>

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

        if ($('#t_cant_pol').val() == 0) {
            $('#div-politicas').hide();
            $('#div-politicas>bootstrap-table').hide();
            $('#div-politicas>fixed-table-body').hide();
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
                <div class="card-header">OBJETIVO ESTRATÉGICO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li class="nav-item" id="nav-tab1" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li class="nav-item" id="nav-tab2"
                            title="Descripción del Objetivo y la estrategia a seguir para su consecución">
                            <a class="nav-link" href="tab2">Descripción</a>
                        </li>
                        <li class="nav-item" id="nav-tab3"
                            title="Ponderación del impacto sobre el cumplimiento de los Lineamientos en la Dirección o proceso">
                            <a class="nav-link" href="tab3">Politicas y/o Lineamientos</a>
                        </li>
                        <li class="nav-item" id="nav-tab4"
                            title="Ponderación del impacto de sus Objetivos de Trabajo y los de las Direcciones y procesos que le estan directamente subordinados">
                            <a class="nav-link" href="tab4">Objetivos de Trabajo</a>
                        </li>
                        <li class="nav-item" id="nav-tab5"
                            title="Ponderación del impacto o efecto de los Objetivos Estratégicos de las Direcciones y procesos que le estan directamente subordinados">
                            <a class="nav-link" href="tab5">Objetivos Subordinados</a>
                        </li>
                        <li class="nav-item" id="nav-tab6"
                            title="Ponderación del impacto o efecto sobre los Objetivos Estratégicos de la Dirección u Organismo de Control Superior al que este se le subordina directamente">
                            <a class="nav-link" href="tab6">Objetivos Superiores</a>
                        </li>
                    </ul>


                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
                        <input type="hidden" name="id" value="<?= $id_objetivo ?>" />
                        <input type="hidden" name="menu" value="objetivo" />

                        <input type="hidden" id="month" name="month" value="<?= $month ?>" />
                        <input type="hidden" id="day" name="day" value="<?= $day ?>" />

                        <input type="hidden" id="if_control_interno" name="if_control_interno" value="0" />
                        <input type="hidden" id="if_objsup" name="if_objsup" value="0" />


                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
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
                                Periodo en el que será gestionado el Objetivo de Estratégico. Es el intervalo de años en
                                el que esta vigente y constituye una meta para la organización, por lo que es calculado
                                o medible por el sistema.
                            </label>
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
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
                                <div class=" col-md-6">
                                    <label class="alert alert-info">
                                        Año a partir del cual son fijadas las ponderaciones del efecto del Objetivo
                                        Estratégicos sobre los Objetivos de Trabajo y viceversa.
                                    </label>
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
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Unidad Organizativa:
                                </label>
                                <div class="col-lg-10">
                                    <?php
                                    $top_list_option = "seleccione........";
                                    $id_list_prs = null;
                                    $order_list_prs = 'eq_desc';
                                    $reject_connected = false;
                                    $in_building = ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs = ($action == 'add') ? true : false;

                                    $restrict_prs = !$config->dpto_with_objetive ? array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO) : null ;
                                    $id_select_prs = $id_proceso;
                                    include_once "inc/_select_prs_down.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-sm-2 col-md-1 col-lg-1">
                                    Objetivo:
                                </label>
                                <div class="col-sm-10 col-md-11 col-lg-11">
                                    <textarea id="nombre" name="nombre" class="form-control input-sm"
                                        rows="4"><?= $nombre ?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->


                        <!-- Descripcion -->
                        <div class="tabcontent" id="tab2">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_up" name="if_send_up" value="1"
                                        <?php if (!empty($if_send_up)) echo "checked='checked'" ?> />
                                    Transmitir este objetivo estratégico a la Dirección Empresarial o Proceso superior.
                                    Será transmitido su estado de cumplimiento periódicamente.
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_down" name="if_send_down" value="1"
                                        <?php if (!empty($if_send_down)) echo "checked='checked'" ?> />
                                    Transmitir este objetivo estratégico a las Direcciones Empresariales o Procesos
                                    subordinados. No será transmitido su estado de cumplimiento.
                                </label>
                            </div>


                            <hr>
                            </hr>
                            <div class="form-group row">
                                <div class="col-lg-12">
                                    <textarea name="descripcion" rows=15 id="descripcion"
                                        class="form-control input-sm"><?= $descripcion ?></textarea>
                                </div>
                            </div>
                        </div><!-- Descripcion -->


                        <!-- Ponderación del Impacto sobre las politicas o lineamientos -->
                        <div class="tabcontent" id="tab3">
                            <?php
                            $array_capitulo = array();
                            $array_grupo = array();

                            $obj_pol = new Tpolitica($clink);
                            $result_pol = $obj_pol->listar();

                            while ($row_pol = $clink->fetch_array($result_pol)) {
                                if (empty($row_pol['capitulo']))
                                    if (empty($array_capitulo[$row_pol['id']]['name']))
                                        $array_capitulo[$row_pol['id']]['name'] = $row_pol['nombre'];

                                if (!empty($row_pol['titulo']) && (!empty($row_pol['capitulo']) && empty($row_pol['grupo'])))
                                    if (empty($array_grupo[$row_pol['id']]['name']))
                                        $array_grupo[$row_pol['id']]['name'] = $row_pol['nombre'];

                                if (empty($row_pol['titulo'])) {
                                    $array_capitulo[$row_pol['capitulo']]['n'] = empty($array_capitulo[$row_pol['capitulo']]['n']) ? 1 : ++$array_capitulo[$row_pol['capitulo']]['n'];

                                    if (!empty($row_pol['grupo']))
                                        $array_grupo[$row_pol['grupo']]['n'] = empty($array_grupo[$row_pol['grupo']]['n']) ? 1 : ++$array_grupo[$row_pol['grupo']]['n'];
                                }
                            }

                            $array_pesos = null;

                            if (!empty($id_objetivo)) {
                                $obj_peso->SetIdProceso($_SESSION['id_entity']);
                                $array_pesos = $obj_peso->listar_politicas_ref_objetivo();
                            }
                            ?>
                            <div id="div-politicas">
                                <table id="table-politicas" class="table table-striped" 
                                    data-toggle="table" 
                                    data-height="420"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Ponderación</th>
                                            <th>Política</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <?php
                                        unset($obj_pol);
                                        $obj_pol = new Tpolitica($clink);
                                        $obj_pol->SetIfTitulo(true);
                                        $obj_pol->SetIfCapitulo(true);
                                        $result_pol_cap = $obj_pol->listar(false);

                                        $i_pol = 0;
                                        $j_pol = 0;
                                        while ($row_pol_cap = $clink->fetch_array($result_pol_cap)) {
                                            if (!empty($row_pol_cap['capitulo']))
                                                continue;

                                            if ((!empty($array_capitulo[$row_pol_cap['id']]['n']) || $chk_title) && !empty($array_capitulo[$row_pol_cap['id']]['name'])) {
                                                ?>
                                                <tr>
                                                    <td colspan="3" class="title_pol">
                                                        <?=$row_pol_cap['numero']?><span><?=$row_pol_cap['nombre'] ?></span>
                                                    </td>
                                                </tr>
                                        <?php
                                            }

                                            unset($obj);
                                            $obj = new Tpolitica($clink);

                                            $obj->SetIfTitulo(true);
                                            $obj->SetIfGrupo(true);
                                            $obj->SetCapitulo($row_pol_cap['id']);

                                            $result_pol_grupo = $obj->listar(false);
                                            $cant = $obj->GetCantidad();

                                            $i_pol = 0;
                                            $j_pol = 0;
                                            
                                            _list_politica($i_pol, $j_pol, 0, 0);

                                            _list_politica($i_pol, $j_pol, $row_pol_cap['id'], false);

                                            while ($row_pol_grupo = $clink->fetch_array($result_pol_grupo)) {
                                                if (!empty($row_pol_grupo['grupo']))
                                                    continue;

                                                if ((!empty($array_grupo[$row_pol_grupo['id']]['n']) || $chk_title) && !empty($array_grupo[$row_pol_grupo['id']]['name'])) {
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="title_pol grupo">
                                                        <?=$row_pol_grupo['numero']?><span><?=$row_pol_grupo['nombre'] ?></span>
                                                    </td>
                                                </tr>
                                        <?php
                                                }

                                                _list_politica($i_pol, $j_pol, $row_pol_cap['id'], $row_pol_grupo['id']);
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>            

                            <input type="hidden" name="cant_pol" id="cant_pol" value="<?=$i_pol ?>" />
                            <input type="hidden" name="t_cant_pol" id="t_cant_pol" value="<?=$j_pol ?>" />

                            <script language="javascript">
                            if (document.getElementById('t_cant_pol').value == 0) {
                                box_alarm(
                                    "En el sistema no existen Lineamientos o Políticas de Trabajo definidas para el escenario en el que está trabajando."
                                );                        
                            }


                            function set_cant_pol(id) {
                                var nvalue = parseInt($('#cant_pol').val());

                                if (parseInt($('#select_pol' + id).val()) > 0 && parseInt($('#init_pol' + id).val()) ==
                                    0)
                                    ++nvalue;
                                if (parseInt($('#select_pol' + id).val()) == 0 && parseInt($('#init_pol' + id).val()) >
                                    0)
                                    --nvalue;
                                document.getElementByid('#cant_pol').value = nvalue;
                            }
                            </script>
                        </div><!-- Ponderación del Impacto sobre las politicas o lineamientos -->


                        <!-- impacto de sus inductores o el de los procesos subordinados -->
                        <div class="tabcontent" id="tab4">
                            <legend>
                                Ponderación del Impacto de sus Objetivos de Trabajo y el de las otras Direcciones o
                                Procesos directamente subordinados
                            </legend>

                            <?php require "inc/_objetivot_tabs.inc.ini.php";?>

                            <div id="div-inductores">
                                <table class="table table-striped" data-toggle="table" data-height="330"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                             $cant_objt= 0;
                                             $array_pesos = null;
                                             if (!empty($id_objetivo))
                                                 $array_pesos = $obj_peso->listar_inductores_ref_objetivo($id_objetivo, false);

                                             if (isset($obj_prs)) unset($obj_prs);
                                             $obj_prs = new Tproceso($clink);
                                             $_id_proceso = !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
                                             $obj_prs->SetIdProceso($_id_proceso);
                                             $obj_prs->listar_in_order('eq_desc', true, null, false, 'asc');

                                             foreach ($obj_prs->array_procesos as $prs) {
                                                 $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                                 $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                                 if ($prs['id'] != $_SESSION['local_proceso_id'])
                                                     $_connect = ($_connect != 1) ? 1 : 0;
                                                 else
                                                     $_connect = 0;

                                                 $id_list_prs = $prs['id'];
                                                 $with_null_perspectiva = _PERSPECTIVA_ALL;
                                                 include "inc/inductor_tabs.inc.php";
                                             }
                                             ?>
                                    </tbody>
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


                        <!-- impacto de los objetivos estrategicos de los procesos o direcciones subordinandas -->
                        <div class="tabcontent" id="tab5">
                            <?php
                                 $array_pesos = null;
                                 $_cant = 0;

                                 if (!empty($id_objetivo)) {
                                     $obj_peso->SetIdProceso($_SESSION['local_proceso_id']);
                                     $array_pesos = $obj_peso->listar_objetivos_ref_objetivo_sup($id_objetivo, false);
                                     $_cant = $obj_peso->GetCantidad();
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
                                        reset($obj_prs->array_procesos);
                                        foreach ($obj_prs->array_procesos as $prs) {
                                            if ($prs['id'] == $id_proceso)
                                                continue;

                                            $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                            $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                            if ($prs['_id'] != $_SESSION['local_proceso_id'])
                                                $_connect = ($_connect != 1) ? 1 : 0;
                                            else
                                                $_connect = 0;

                                            $id_list_prs = $prs['id'];
                                            include "inc/objetivo_obji_tabs.inc.php";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <input type="hidden" name="cant_obji" id="cant_obji" value="<?=$i_obji?>" />
                            <input type="hidden" name="t_cant_obji" id="t_cant_obji" value="<?=$cant_obji?>" />

                            <script language="javascript">
                            if (parseInt(document.getElementById('t_cant_obji').value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Estratégicos para las Dirección o Procesos subordinados. Deberá definirlos para poder acceder a esta funcionalidad. "
                                );
                            }
                            </script>
                        </div>
                        <!-- impacto de los objetivos estrategicos de los procesos o direcciones subordinandas -->


                        <!-- Ponderación del Impacto sobre los Objetivos Estratégico de la Dirección Superior -->
                        <div class="tabcontent" id="tab6">
                            <legend>
                                Ponderación del Impacto sobre los Objetivos Estratégico de la Dirección Superior
                            </legend>

                            <div id="div-politicas">
                                <table class="table table-striped" data-toggle="table" data-height="300"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No.</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo Estratégicos</th>
                                    </thead>

                                    <tbody>
                                        <?php
                                         $id_proceso_sup = null;
                                         $i_objs = 0;
                                         $j_objs = 0;
                                         $array_pesos = null;

                                         if (!empty($id_objetivo)) {
                                             $obj_peso->SetIdProceso($_SESSION['local_proceso_id']);
                                             $array_pesos = $obj_peso->listar_objetivo_sup_ref_objetivo($id_objetivo, false);
                                         }

                                         if (isset($obj_prs)) unset($obj_prs);
                                         $obj_prs = new Tproceso($clink);
                                         !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                                         $obj_prs->get_procesos_up_cascade($id_proceso, null, null, false);

                                         foreach ($obj_prs->array_cascade_up as $prs) {
                                             $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                             if ($prs['id'] != $_SESSION['local_proceso_id'])
                                                 $_connect = ($_connect != 1) ? 1 : 0;
                                             else
                                                 $_connect = 0;

                                             $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                             ?>
                                        <tr>
                                            <td colspan="3" class="row_prs">
                                                <?php if ($_connect) { ?><img src="../img/transmit.ico"
                                                    class="row_prs_connect" /><?php } ?><?= $proceso ?>
                                            </td>
                                        </tr>

                                        <?php
                                             $obj_obj = new Tobjetivo($clink);
                                             $obj_obj->SetIdProceso($prs['id']);
                                             $obj_obj->SetInicio($inicio);
                                             $obj_obj->SetFin($fin);

                                             $result = $obj_obj->listar();

                                             $i = 0;
                                             while ($row = @$clink->fetch_array($result)) {
                                                $_prs= $array_procesos_entity[$row['id_proceso']];
                                                if (!empty($_prs['id_entity']) 
                                                    && ($_prs['id'] !=  $_SESSION['superior_proceso_id'] && $_prs['id_entity'] != $_SESSION['id_entity']))
                                                    continue;
                                                if (empty($_prs['id_entity']) && (!$row['if_send_down'] && $_prs['tipo'] < $_SESSION['entity_tipo']))
                                                    continue;
                                                if (empty($_prs['id_entity']) && (!$row['if_send_up'] && $_prs['tipo'] > $_SESSION['entity_tipo']))
                                                    continue;                                                   
                                                 
                                                 $class = ($j_objs % 2 == 0) ? 'roweven' : '';

                                                 $value = $array_pesos[$row['_id']];
                                                 $value = setZero($value);
                                                 if (!empty($value))
                                                     ++$i_objs;
                                                 ?>

                                        <tr id="id_objs<?= $row['_id'] ?>">
                                            <td width="30"><?=++$j_objs?></td>

                                            <td width="120px;">

                                                <select id="select_objs<?= $row['_id'] ?>"
                                                    name="select_objs<?= $row['_id'] ?>" class="form-control input-sm"
                                                    onchange="set_cant_objs(<?= $row['_id'] ?>)">
                                                    <?php for ($k = 0; $k < 8; ++$k) { ?>
                                                    <option value="<?= $k ?>"
                                                        <?php if ($k == $value) echo "selected" ?>><?= $Tpeso_inv_array[$k] ?>
                                                    </option>
                                                    <?php } ?>
                                                </select>

                                                <input type="hidden" id="init_objs<?= $row['_id'] ?>"
                                                    name="init_objs<?= $row['_id'] ?>" value="<?= $value ?>" />
                                                <input type="hidden" name="id_objs_code<?= $row['_id'] ?>"
                                                    id="id_objs_code<?= $row['_id'] ?>"
                                                    value="<?= $row['_id_code'] ?>" />

                                            </td>

                                            <td>
                                                <strong
                                                    style="font-weight:bold; padding:2px 4px;">No.<?= $row['numero'] ?></strong>
                                                <?="{$row['_nombre']}<br /><strong style='margin-left:35px'>periodo: </strong>{$row['inicio']}- {$row['fin']}" ?>
                                            </td>
                                        </tr>
                                        <?php }
                                         }
                                         ?>
                                    </tbody>
                                </table>
                            </div>

                            <input type="hidden" name="cant_objs" id="cant_objs" value="<?= $i_objs ?>" />
                            <input type="hidden" name="t_cant_objs" id="t_cant_objs" value="<?= $j_objs ?>" />

                            <script language="javascript">
                            if ($('#t_cant_objs').val() == 0) {
                                box_alarm(
                                    "Aún no se ha creado el Organo o Proceso Superior de Dirección o este está creado pero no se le han asignado objetivos estratégicos."
                                );
                            }

                            function set_cant_objs(id) {
                                var nvalue = parseInt(document.getElementById('cant_objs').value);

                                if (parseInt($('#select_objs' + id).val()) > 0 && parseInt($('#init_objs' + id)
                                        .val()) == 0) ++nvalue;
                                if (parseInt($('#select_objs' + id).val()) == 0 && parseInt($('#init_objs' + id)
                                        .val()) > 0) --nvalue;
                                $('#cant_objs').val(nvalue);
                            }
                            </script>
                        </div><!-- Ponderación del Impacto sobre los Objetivos Estratégico de la Dirección Superior -->


                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/09_objetivos.htm#09_11.2')">Ayuda</button>
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


<?php
function _list_politica(&$i_pol, &$j_pol, $capitulo, $grupo = null) {
    global $clink;
    global $array_pesos;
    global $Tpeso_inv_array;

    $obj = new Tpolitica($clink);

    $obj->SetIfTitulo(false);
    
    if ($grupo === false) 
        $obj->SetIfGrupo(false);
    
    $obj->SetIfCapitulo(false);
    $obj->SetCapitulo($capitulo);
    $obj->SetGrupo($grupo);

    $result = $obj->listar(false);

    while ($row = @$clink->fetch_array($result)) {
        ++$j_pol;

        $value = $array_pesos[$row['_id']];
        $value = setZero($value);
        if (!empty($value))
            ++$i_pol;
        ?>

        <tr id="id_<?= $row['_id'] ?>">
            <td>
                <?=$row['numero'] ?>
            </td>
            <td>
                <div style="width:170px">
                    <select id="select_pol<?= $row['_id'] ?>" name="select_pol<?= $row['_id'] ?>" class="form-control input-sm"
                        onchange="set_cant_pol(<?= $row['_id'] ?>)">
                        <?php for ($k = 0; $k < 8; ++$k) { ?>
                        <option value="<?= $k ?>" <?php if ($k == $array_pesos[$row['_id']]) echo "selected='selected'" ?>>
                            <?= $Tpeso_inv_array[$k] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <input type="hidden" id="init_pol<?= $row['_id'] ?>" name="init_pol<?= $row['_id'] ?>" value="<?=$value ?>" />
                <input type="hidden" name="id_pol_code<?= $row['_id'] ?>" id="id_pol_code<?= $row['_id'] ?>" value="<?=$row['_id_code'] ?>" />
            </td>
            <td>
                <?=$row['nombre']?>
            </td>
        </tr>
<?php
    }
}
?>