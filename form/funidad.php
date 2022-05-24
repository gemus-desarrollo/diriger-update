<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/unidad.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tunidad($clink);
}

$id= $obj->GetIdUnidad();
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$decimal= $action == 'add' ? null : $obj->GetDecimal();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['local_proceso_id'];

$url_page = "../form/funidad.php?signal=$signal&action=$action&menu=unidad";
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
    <title>UNIDADES DE MEDIDAS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function validar() {
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca la dimensión de la unidad de medida. Ejemplo: Km, %, Hm3');
            return;
        }
        if (!Entrada($('#decimal').val())) {
            $('#decimal').focus(focusin($('#decimal')));
            ok = confirm(
                "No ha definido la cantidad de cifras decimales a utilizar en la unidad. Diriger asumirá cero (0) cifras decimales. Desea continuar?"
                );
            if (!ok) return;
            else $('#decimal').val() = 0;
        }
        if (!Entrada($('#descripcion').val())) {
            $('#descripcion').focus(focusin($('#descripcion')));
            alert('Introduzca la descripción');
            return;
        }

        document.forms[0].action = '../php/interface.php';
        document.forms[0].submit();
    }
    </script>
    <script type="text/javascript">
    $(document).ready(function() {
        tinymce.init({
            selector: '#nombre',
            theme: 'modern',
            language: 'es',
            height: 20,
            menubar: false,
            toolbar: 'subscript superscript',
            content_css: '../css/content.css'
        });

        try {
            $('#nombre').val(<?= json_encode($obj->GetNombre())?>);
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
                <div class="card-header">UNIDADES DE MEDIDA</div>
                <div class="card-body">

                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id?>" />
                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />

                        <input type="hidden" name="menu" value="unidad" />

                        <div class="form-group row">
                            <label class="col-form-label col-md-1">
                                Unidad:
                            </label>
                            <div class="col-md-4">
                                <textarea type="text" id="nombre" name="nombre"
                                    class="form-control"><?=$obj->GetNombre()?></textarea>
                            </div>

                            <label
                                class="col-form-label col-md-1">
                                Decimales:
                            </label>
                            <div class="col-md-2">
                                <select id=decimal name=decimal class="form-control">
                                    <option value="">.....</option>

                                    <?php for ($i = 0; $i < 4; ++$i) { ?>
                                    <option value="<?php echo $i ?>"
                                        <?php if ($i === $decimal) echo "selected='selected'" ?>><?php echo $i ?>
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
                                <textarea name="descripcion" rows="5" id="descripcion"
                                    class="form-control"><?=$obj->GetDescripcion()?></textarea>
                            </div>
                        </div>

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