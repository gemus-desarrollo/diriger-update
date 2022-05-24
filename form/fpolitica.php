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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/politica.class.php";
require_once "../php/class/objetivo.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/badger.class.php";

$signal= !empty($_GET['signal'])?  $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))  
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tpolitica($clink);
}

$id_politica= $obj->GetIdPolitica();
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new Ttime();
$actual_year= $time->GetYear();

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

if (empty($inicio)) 
    $inicio= $actual_year;
if (empty($fin)) 
    $fin= $actual_year;

$grupo= !empty($_GET['grupo']) ? $_GET['grupo'] : $obj->GetGrupo();
$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : $obj->GetCapitulo();
$if_titulo= !empty($_GET['if_titulo']) ? 1 : $obj->GetIfTitulo();
$if_capitulo= !empty($_GET['if_capitulo']) ? 1 : $obj->GetIfCapitulo();

$politica= !empty($_GET['politica']) ? urldecode($_GET['politica']) : $obj->GetNombre();
$observacion= !empty($_GET['observacion']) ? urldecode($_GET['observacion']) : $obj->GetObservacion();

$year= !empty($_GET['year']) ? $_GET['year'] : $actual_year;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['current_proceso_id'];
if (empty($id_proceso)) $id_proceso= $_SESSION['id_entity'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$id_proceso_code= $obj_prs->get_id_code();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$obj->SetInicio($inicio);
$obj->SetFin($fin);
$obj->SetYear($year);
$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();

if ((int)$_fin < $_SESSION['current_year'] + 5)
    $_fin= $_SESSION['current_year'] + 5;


if (!empty($id_politica)) {
    $obj_peso= new Tpeso($clink);
    $obj_peso->SetIdPolitica($id_politica);
    $array_pesos= $obj_peso->listar_objetivos_ref_politica(null, false);
}

$url_page= "../form/fpolitica.php?signal=$signal&action=$action&menu=politica&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&chk_inner=$chk_inner&chk_sys=$chk_sys";

add_page($url_page, $action, 'f');
?>


<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>LINEAMINETO Y/O POLITICA</title>

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

    function refreshp() {
        var politica = $('#politica').val();
        var observacion = $('#politica').val();
        var numero = $('#numero').val();
        var grupo = $('#grupo').val();
        var capitulo = $('#capitulo').val();
        if_titulo = $('#if_titulo0').is(':checked') ? 0 : 1;
        if_capitulo = $('#if_capitulo0').is(':checked') ? 0 : 1;

        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var action = $('#exect').val();

        url = '&politica=' + encodeURI(politica) + '&observacion=' + encodeURI(observacion) + '&numero=' + numero +
            '&grupo=' + grupo;
        url += '&capitulo=' + capitulo + '&inicio=' + inicio + '&fin=' + fin + '&if_titulo=' + if_titulo +
            '&if_capitulo=' + if_capitulo;

        self.location.href = 'fpolitica.php?version=&action=' + action + url;
    }

    function validar() {
        var form = document.forms[0];
        var text;
        /*
        if (($('#if_titulo').val() == 0 || ($('#if_titulo').val() == 1 && $('#if_capitulo').val() == 0)) && $(
                '#capitulo').val() == 0) {
            $('#capitulo').focus(focusin($('#capitulo')));
            alert(
                'Si no es un nuevo capítulo, debe especificar el capítulo que contendrá la nueva política o lineamiento.'
                );
            return;
        }
        if ($('#if_titulo').val() == 0 && $('#grupo').val() == 0) {
            $('#grupo').focus(focusin($('#grupo')));
            ok = confirm('No ha especificado un grupo para esta política o lineamiento. Desea continua?');
            if (!ok)
                return;
        }
        */
        if ($('#inicio').val() > $('#fin').val()) {
            $('#fin').focus(focusin($('#fin')));
            alert("El año de inicio de la política no puede ser superior al año en que finaliza.");
            return;
        }
        if (parseInt($('#year').val()) < parseInt($('#inicio').val()) || parseInt($('#year').val()) > parseInt($('#fin')
                .val())) {
            $('#year').focus(focusin($('#year')));
            alert("El año de referencia tiene que estar entre los años de vigencia de la Política.");
            return;
        }

        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            text= "Debe especificar el número que identifica a esta nueva Política o Lineamiento ";
            text+= "o el que identifica al capítulo o epígrafe según sea el caso";
            alert(text);
            return;
        }
        if (!Entrada($('#politica').val())) {
            $('#politica').focus(focusin($('#politica')));
            text= "Introduzca el enuenciado de la nueva política o lineamiento de trabajo ";
            text+= "o el nombre del capítulo o el epígrafe si fuese el caso.";
            alert(text);
            return;
        }

        if (parseInt($('#cant_objt').val()) == 0 && parseInt($('#cant_obji').val()) == 0) {
            text= "No se permite Políticas o Lineamientos que no esten vinculados a al menos un Objetivos Estratégico";
            alert(text);
            return;
        }

        if ($('#if_titulo').val() == 0 && (parseInt($('#t_cant_obji').val()) > 0 && parseInt($('#cant_obji').val()) == 0)) {
            text= "La política o lineamiento no está relacionado con ningún Objetivo Estratégico. ";
            text+= "Eso significa que el resultado de este no tendrá impacto alguno sobre la ";
            text+= "Planificación Estratégica de la Organización. ¿Desea continuar?."
            confirm(text, function(ok) {
                if (!ok)
                    return; 
                else 
                    _this_1();              
            });
        } else {
            _this_1();
        }

        function _this_1() {
            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            form.action = '../php/politica.interface.php';
            form.submit();            
        }
    }

    function refreshform() {
        if ($("#if_titulo0").is(':checked')) {
            $("#capitulo").attr("disabled", false);
            $("#grupo").attr("disabled", false);

            $("#tr-capitulo").css("display", "flex");
            $("#tr-grupo").css("display", "flex");

            $("#classified").css("visibility", 'hidden');
            $("#cont-2").css("display", 'inline');

        } else {
            $("#capitulo").attr("disabled", true);
            $("#grupo").attr("disabled", true);

            $("#tr-capitulo").css("display", "none");
            $("#tr-grupo").css("display", "none");

            $("#classified").css("visibility", 'visible');
            $("#cont-2").css("display", 'none');

            if ($("#if_capitulo0").is(':checked')) {
                $("#grupo").val(0);
                $("#capitulo").attr("disabled", false);
                $("#tr-capitulo").css("display", "flex");

            } else {
                $("#capitulo").val(0);
                $("#grupo").val(0);
            }
        }
    }
    </script>
    <script type="text/javascript">
    function set_numero(val) {
        $('#numero').val(val);
    }

    $(document).ready(function() {
        refreshform();

        new BootstrapSpinnerButton('spinner-numero', 1, 255);

        if (parseInt($('#t_cant_obji').val()) == 0) {
            $('#div-objetivos').hide();
        }

        tinymce.init({
            selector: '#observacion',
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
            $('#observacion').val(<?= json_encode($observacion)?>);
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
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">POLÍTICA O LINEAMIENTODE TRABAJO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Descripción/Observación</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Objetivos Estratégicos</a></li>
                    </ul>

                    <form action='javascript:validar()' class="form-horizontal" method=post>
                        <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />
                        <input type="hidden" name="id" value="<?= $id_politica ?>" />

                        <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
                        <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

                        <input type="hidden" id="cant_objt" name="cant_objt" value="0" />
                        <input type="hidden" id="cant_obji" name="cant_obji" value="0" />

                        <input type="hidden" name="menu" value="politica" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-4">
                                        Tipo de enunciado:
                                    </label>
                                    <div class="col-lg-8">
                                        <label class="radio text col-12">
                                            <input type="radio" id="if_titulo1" name="if_titulo" value="1"
                                                <?php if (!empty($if_titulo)) echo "checked='checked'" ?>
                                                onclick="refreshform()" />
                                            Es un Título (Capítulo o Epígrafe)
                                        </label>
                                        <label class="radio text col-12">
                                            <input type="radio" id="if_titulo0" name="if_titulo" value="0"
                                                <?php if (empty($if_titulo)) echo "checked='checked'" ?>
                                                onclick="refreshform()" />
                                            Es un Lineamiento o Política
                                        </label>
                                    </div>
                                </div>

                                <div id="classified" class="col-lg-6">
                                    <label class="col-form-label col-12">
                                        Clasificación en el documento:
                                    </label>
                                    <div class="col-12">
                                        <label class="radio text col-12">
                                            <input type="radio" name="if_capitulo" id="if_capitulo1" value="1"
                                                onchange="refreshform()"
                                                <?php if (!empty($if_capitulo)) echo "checked='checked'" ?>
                                                onclick="refreshform()" />
                                            Es un nuevo Capítulo
                                        </label>
                                        <label class="radio text col-12">
                                            <input type="radio" name="if_capitulo" id="if_capitulo0" value="0"
                                                onchange="refreshform()"
                                                <?php if (empty($if_capitulo)) echo "checked='checked'" ?> />
                                            Es un nuevo Epígrafe
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="tr-capitulo" class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Capítulo:
                                </label>
                                <div class="col-md-10">
                                    <?php
                                     $tobj = new Tpolitica($clink);
                                     $tobj->SetIfTitulo(1);
                                     $result = $tobj->listar(false);
                                     ?>

                                    <select name="capitulo" id="capitulo" class="form-control" onchange="javascript:refreshp()">
                                        <option value="0"> ... </option>

                                        <?php
                                         while ($row = $clink->fetch_array($result)) {
                                             if (!empty($row['capitulo']))
                                                 continue;
                                             ?>
                                        <option value="<?= $row['id'] ?>"
                                            <?php if ($row['id'] == $capitulo) echo "selected='selected'"; ?>>
                                            <?php echo $row['nombre'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div id="tr-grupo" class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Epígrafe:
                                </label>
                                <div class="col-md-10">
                                    <?php $clink->data_seek($result); ?>

                                    <select name="grupo" id="grupo" class="form-control"
                                        onchange="javascript:refreshp()">
                                        <option value="0"> ... </option>

                                        <?php
                                         while ($row = $clink->fetch_array($result)) {
                                             if (empty($row['capitulo']))
                                                 continue;
                                             if (!empty($capitulo) && $row['capitulo'] != $capitulo)
                                                 continue;
                                             ?>
                                        <option value="<?= $row['id'] ?>"
                                            <?php if ($row['id'] == $grupo) echo "selected='selected'" ?>>
                                            <?php echo $row['nombre'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Válido:
                                </label>
                                <label class="col-form-label col-md-2">
                                    Desde:
                                </label>
                                <div class="col-md-2">
                                    <select name="inicio" id="inicio" class="form-control">
                                        <?php for ($i = 2008; $i < $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-md-2">
                                    Hasta:
                                </label>
                                <div class="col-md-2">
                                    <select name="fin" id="fin" class="form-control">
                                        <?php for ($i = 2008; $i < $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <label class="alert alert-info col-md-12">
                                Periodo en el que será gestionado la Política. Es el intervalo de años en el que esta
                                vigente y es de obligatorio cumplimiento para la organización, por lo que es calculado o
                                medible por el sistema.
                            </label>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Año de referencia:
                                </label>
                                <div class="col-md-2">
                                    <select name="year" id="year" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="alert alert-info col-md-8">
                                    Año a partir del cual son fijadas las ponderaciones del efecto de los Objetivo
                                    Estratégicos sobre esta Política o Lineamiento de trabajo.
                                </label>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Número:
                                </label>
                                <div class=" col-md-10">
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
                                    Enunciado:
                                </label>
                                <div class=" col-md-10">
                                    <textarea name="politica" rows="7" id="politica"
                                        class="form-control"><?= nl2br($politica); ?></textarea>
                                </div>
                            </div>
                        </div> <!-- generales -->

                        <!-- impacto de los objetivos estrategicos de los procesos o direcciones subordinandas -->
                        <div class="tabcontent" id="tab2">
                            <?php
                            $array_pesos= null;
                            $_cant= 0;

                            $obj_peso= new Tpeso($clink);
                            $obj_peso->SetIdProceso($_SESSION['id_entity']);
                            $obj_peso->SetYear($year);
                            $obj_peso->SetIdObjetivo_sup($id_politica);

                            if (!empty($id_politica)) {
                                $array_pesos= $obj_peso->listar_objetivos_ref_politica($id_politica, false);
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
                                        if (isset($obj_prs)) 
                                        unset($obj_prs);
                                        $obj_prs = new Tproceso($clink);
                                        $_id_proceso = !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
                                        $obj_prs->SetIdProceso($_id_proceso);
                                        $obj_prs->listar_in_order('eq_desc', false, null, false, 'asc');

                                        reset($obj_prs->array_procesos);
                                        foreach ($obj_prs->array_procesos as $prs) {
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
                            <input type="hidden" name="t_cant_obji" id="t_cant_obji" value="<?=$j_obji?>" />

                            <script language="javascript">
                            if (parseInt(document.getElementById('t_cant_obji').value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Estratégicos para las Dirección o Procesos subordinados. Deberá definirlos para poder acceder a esta funcionalidad. "
                                );
                            }
                            </script>
                        </div>
                        <!-- impacto de los objetivos estrategicos de los procesos o direcciones subordinandas -->


                        <!-- observacion -->
                        <div class="tabcontent" id="tab3">
                            <div class="form-group row">
                                <div class="col-lg-12">
                                    <textarea name="observacion" id="observacion"
                                        class="form-control input-sm"><?= $observacion ?></textarea>
                                </div>
                            </div>
                        </div> <!-- observacion -->


                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
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