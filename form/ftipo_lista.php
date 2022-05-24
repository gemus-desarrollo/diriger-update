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
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";

$signal = !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action = !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj = new Ttipo_lista($clink);
}

$year = !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year = date('Y');

$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_lista = !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();

$obj_lista = new Tlista($clink);
$obj_lista->SetIdLista($id_lista);
$obj_lista->Set();
$nombre_lista = $obj_lista->GetNombre();

$_inicio= !empty($_GET['min_year']) ? $_GET['min_year'] : $obj_lista->Getinicio();
$_fin= !empty($_GET['max_year']) ? $_GET['max_year'] : $obj_lista->GetFin();

$componente = !empty($_GET['componente']) ? $_GET['componente'] : $obj->GetComponente();
if (empty($componente))
    $componente = 1;
$id_capitulo = !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : $obj->GetIdCapitulo();

$if_change= false;
if ($_GET['componente'] != $obj->GetComponente() || $_GET['id_capitulo'] != $obj->GetIdCapitulo())
    $if_change= true;

$nombre = !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion = !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$if_jefe = !is_null($_GET['if_jefe']) ? $_GET['if_jefe'] : false;

$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso = $_SESSION['id_entity'];

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

if ($action == 'add' && !empty($id_capitulo)) {
    $obj->Set($id_capitulo);
    $max_year = $obj->GetYear();
    $nombre = null;
    $descripcion = null;

    $inicio= $obj->GetInicio();
    $fin= $obj->GetFin();
}
if (empty($id_capitulo))
    $id_capitulo = $obj->GetIdCapitulo();

$obj->SetIdLista($id_lista);
$obj->SetYear($year);

if ($action != 'add' && (!empty($id_capitulo) && $id_capitulo > 0)) {
    $obj_temp = new Ttipo_lista($clink);
    $obj_temp->Set($id_capitulo);
    $max_year = $obj_temp->GetYear();
}

$id = $action == 'update' ? $obj->GetIdTipo_lista() : 0;

if ($action != 'add' && !$if_change) {
    $numero= $obj->GetNumero();
    $_fin = $max_year ? $max_year : $_fin;
} else {
    $obj->SetComponente($componente);
    $obj->SetIdCapitulo($id_capitulo);
    $numero= $obj->fix_numero()['numero'];
}
if (!empty($numero))
    list($componente, $capitulo, $subcapitulo) = preg_split('/\./', $numero);
if ($action == 'add' || ($action == 'update' && $if_change)) {
    if (empty($id_capitulo))
        ++$capitulo;
    else
        ++$subcapitulo;
}

$show_capitulo = is_null($id_capitulo) ? false : true;

$url_page = "../form/ftipo_lista.php?signal=$signal&action=$action&menu=tipo_lista&exect=$action";
$url_page .= "&id_lista=$id_lista&id_proceso=$id_proceso&year=$year&inicio=$inicio&fin=$fin";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT"/>
    <title>TIPO DE REQUISITOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../libs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet"/>
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
        .point {
            margin: 3px;
            font-size: 1.3em;
            font-weight: bold;
            color: black;
        }
        #ajax-numero {
            border: none;
        }
    </style>

    <script language="javascript">
        function set_capitulo(val) {

        }

        function set_capitulo(val) {
            $('#capitulo').val(val);
        }

        function set_subcapitulo(val) {
            $('#subcapitulo').val(val);
        }

        function set_numero() {

        }

        function validar() {
            if (!Entrada($('#nombre').val())) {
                $('#nombre').focus(focusin($('#nombre')));
                alert('Introduzca el nombre o título que tendra la nueva clasificación de requisitos a definir');
                return;
            }

            var numero = $('#componente').val() + '.' + $('#capitulo').val();

            <?php if (!empty($subcapitulo)) { ?>
            if ($('#subcapitulo').val() > 0)
                numero += '.' + $('#subcapitulo').val();
            <?php } ?>

            $('#numero').val(numero);

            document.forms[0].action = '../php/interface.php';
            document.forms[0].submit();
        }

        function refreshp(index) {
            var nombre = encodeURI($('#nombre').val());
            var descripcion = encodeURI($('#descripcion').val());
            var action = $('#exect').val();
            var id_lista = $("#id_lista").val();
            var signal = $("#signal").val();
            var componente = $('#componente').val();
            var id_capitulo= 0;

            if (index == 0)
                id_capitulo= 0;
            else
                id_capitulo = parseInt($('#id_capitulo').val()) ? $('#id_capitulo').val() : 0;

            var inicio= $('#inicio').val();
            var fin= $('#year').val();

            var url = 'ftipo_lista.php?action=' + action + '&componente=' + componente + '&id_capitulo=' + id_capitulo;
            url += '&descripcion=' + descripcion + '&nombre=' + nombre + '&id_lista=' + id_lista + '&signal=' + signal;
            url+= '&inicio='+inicio+'&fin='+fin+'&min_year=<?=$min_year?>&max_year=<?=$max_year?>';

            self.location.href = url;
        }
    </script>

    <script type="text/javascript">
        var focusin;

        $(document).ready(function () {
            new BootstrapSpinnerButton('spinner-componente', 1, 5);
            new BootstrapSpinnerButton('spinner-capitulo', 1, 255);
            new BootstrapSpinnerButton('spinner-subcapitulo', 1, 255);

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n", " ", addslashes($error))?>");
            <?php } ?>
        });
    </script>

</head>

<body>
<script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

<div class="app-body form">
    <div class="container">
        <div class="card card-primary">
            <div class="card-header">TIPO DE REQUISITOS</div>
            <div class="card-body">

                <form class="form-horizontal" action='javascript:validar()' method=post>
                    <input type="hidden" name="menu" value="tipo_lista"/>
                    <input type="hidden" id="exect" name="exect" value="<?= $action ?>"/>
                    <input type="hidden" id="signal" name="signal" value="<?= $signal ?>"/>

                    <input type="hidden" name="id" value="<?= $id ?>"/>
                    <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>"/>
                    <input type="hidden" name="id_lista" id="id_lista" value="<?= $id_lista ?>"/>
                    <input type="hidden" id="numero" name="numero" value="<?= $numero ?>"/>

                    <input type="hidden" id="year" name="year" value="<?= $year ?>"/>

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-3" style="font-weight: bold">
                                Título de la Lista:
                            </div>
                            <div class="col-md-9 pull-left">
                                <?= textparse($nombre_lista) ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-md-2">
                            Nombre:
                        </label>
                        <div class=" col-md-10">
                            <textarea id="nombre" name="nombre" class="form-control" rows="2"><?= $nombre ?></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2">
                            Componente:
                        </label>
                        <div class=" col-md-6">
                            <select id="componente" name="componente" class="form-control" onchange="refreshp(0)">
                                <?php for ($i = 1; $i < _MAX_COMPONENTES_CI; ++$i) { ?>
                                    <option value="<?= $i ?>" <?php if ($i == $componente) echo "selected='selected'" ?>>
                                        <?= $Tambiente_control_array[$i] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($show_capitulo) { ?>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">
                                Capítulo:
                            </label>
                            <div class="col-md-10">
                                <?php
                                $obj = new Ttipo_lista($clink);
                                $result = $obj->listar($componente);
                                ?>
                                <select id="id_capitulo" name="id_capitulo" class="form-control" onchange="refreshp(1)">
                                    <?php while ($row = $clink->fetch_array($result)) { ?>
                                        <option value="<?= $row['id'] ?>" <?php if ($row['id'] == $id_capitulo) echo "selected='selected'" ?>>
                                            <?= $row['nombre'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Número:
                        </label>

                        <div class="col-6">
                            <div class="row form-inline">
                                <div class="col-3 input-group">
                                    <div id="spinner-componente" class="input-group spinner">
                                        <input type="text" name="componente" id="componente" class="form-control"
                                               value="<?= $componente ?>" readonly>
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up" disabled>
                                                <i class="fa">
                                                    <span class="fa fa-caret-up"></span></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down" disabled>
                                                <i class="fa">
                                                    <span class="fa fa-caret-down"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group">.</div>

                                <div class="col-3 input-group">
                                    <div id="spinner-capitulo" class="input-group spinner">
                                        <input type="text" name="capitulo" id="capitulo" class="form-control"
                                               value="<?= $capitulo ?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa">
                                                    <span class="fa fa-caret-up"></span>
                                                </i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa">
                                                    <span class="fa fa-caret-down"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($subcapitulo)) { ?>
                                    <div class="input-group">.</div>

                                    <div class="col-3 input-group">
                                        <div id="spinner-subcapitulo" class="input-group spinner">
                                            <input type="text" name="subcapitulo" id="subcapitulo" class="form-control"
                                                   value="<?= $subcapitulo ?>">
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up">
                                                    <i class="fa">
                                                        <span class="fa fa-caret-up"></span>
                                                    </i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down">
                                                    <i class="fa">
                                                        <span class="fa fa-caret-down"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Válido desde:
                        </label>
                        <div class="col-2">
                            <select name="inicio" id="inicio" class="form-control input-sm" onchange="refreshpage()">
                                <?php for ($i= $_inicio; $i <= $_fin; ++$i) { ?>
                                    <option value="<?=$i?>" <?php if ($i == $inicio) echo "selected='selected'"; ?>>
                                        <?=$i?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <label class="col-form-label col-1">
                            hasta:
                        </label>
                        <div class="col-2">
                            <select name="fin" id="fin" class="form-control input-sm" onchange="refreshpage()">
                                <?php for ($i= $_inicio; $i <= $_fin; ++$i) { ?>
                                    <option value="<?=$i?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                        <?=$i?>
                                    </option>
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
                                      class="form-control"><?= $descripcion ?></textarea>
                        </div>
                    </div>

                    <!-- buttom -->
                    <div class="btn-block btn-app">
                        <?php if ($action == 'update' || $action == 'add') { ?>
                            <button type="submit" class="btn btn-primary">Aceptar</button>
                        <?php } ?>
                        <button type="reset" class="btn btn btn-warning"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar
                        </button>
                        <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda
                        </button>
                    </div>
                </form>

            </div><!-- panel-body -->
        </div><!-- panel -->
    </div>  <!-- container -->

</div>

</body>
</html>
