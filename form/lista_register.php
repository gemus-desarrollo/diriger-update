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

require_once "../php/class/proceso.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista_requisito.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'lista';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add' || $action == 'update') $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$obj_prs= new Tproceso($clink);

$id_proceso= empty($_GET['id_proceso']) || $_GET['id_proceso'] == -1 ? $_SESSION['id_entity'] : $_GET['id_proceso'];

if (!empty($id_proceso)) {
   $obj_prs->SetIdProceso($id_proceso);
   $obj_prs->Set();
   $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
   $conectado= $obj_prs->GetConectado();
   $tipo= $obj_prs->GetTipo();
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
}
else {
    $obj= new Tlista_requisito($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();
if (empty($id_lista)) $id_lista= 0;

$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : null;
$componente= !empty($_GET['componente']) ? $_GET['componente'] : null;
$id_capitulo= !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : null;
$id_subcapitulo= !empty($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : null;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;

$obj_user= new Tusuario($clink);

$year= date('Y');
$inicio= $year - 5;
$fin= $year + 5;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$obj->SetYear($year);

$obj_lista= new Tlista($clink);
$obj_lista->SetIdLista($id_lista);
$obj_lista->Set();
$nombre_lista= $obj_lista->GetNombre();
$_inicio= $obj_lista->GetInicio();
$_fin= $obj_lista->GetFin();

// determinar si el usuario es jefe
unset($obj_prs);
$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
$array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

$if_jefe= false;
$acc= $_SESSION['acc_planaudit'];
if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos)) 
    $if_jefe= true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO) 
    $if_jefe= true;
if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['local_proceso_id'])) 
    $if_jefe= true;
// if ($acc == _ACCESO_MEDIA && ($id_proceso == $_SESSION['local_proceso_id'])) $if_jefe= true;

$url_page= "../form/llista_requisito.php?signal=$signal&action=$action&menu=tipo_lista&year=$year";
$url_page.= "&id_proceso=$id_proceso&id_lista=$id_lista&if_jefe=$if_jefe";

add_page($url_page,$action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE REQUISITOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/lista.css" />
    <script type="text/javascript" src="../js/lista.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function set_url() {
        var year = $('#year').val();
        var id_lista = $('#id_lista').val();
        var id_proceso = $('#id_proceso').val();
        var componente = $('#id_componente').val();
        var id_capitulo = $('#id_capitulo').val();
        var id_subcapitulo = $('#id_subcapitulo').val();
        var id_tipo_lista = id_subcapitulo ? id_subcapitulo : 0;

        var url = '?action=<?=$action?>&signal=list&if_jefe=<?=$if_jefe?>&componente=' + componente + '&year=' + year;
        url += '&id_tipo_lista=' + id_tipo_lista + '&id_lista=' + id_lista + '&id_proceso=' + id_proceso;
        url += '&id_capitulo=' + id_capitulo + '&id_subcapitulo=' + id_subcapitulo +
            '&_inicio=<?=$_inicio?>&_fin=<?=$_fin?>';

        return url
    }

    function filtrar() {
        $('#id_componente').val($('#componente').val());
        $('#id_capitulo').val($('#capitulo').val());
        $('#id_subcapitulo').val($('#subcapitulo').val());

        var url = set_url();
        self.location.href = 'lista_register.php' + url;
    }

    function ejecutar(id) {
        if ($('#cumplimiento').val() == -1) {
            alert("Debe asignar un estado al requisito.");
            return;
        }
        if (!Entrada($('#reg_fecha').val())) {
            alert("Especifique la fecha en la que se define el estado del requisito.");
            return;
        }

        if (!Entrada($('#observacion').val())) {
            alert("No ha especificado las razones para la asignacion de este estado al requisito a revizar.");
            return;
        }

        $('#div_reg_fecha_' + id).html($('#reg_fecha').val());
        $('#div_cumplimiento_' + id).html($('#cumplimiento').val());
        $('#div_observacion_' + id).html($('#observacion').val());

        $('#reg_fecha_' + id).val($('#reg_fecha').val());
        $('#cumplimiento_' + id).val($('#cumplimiento').val());
        $('#observacion_' + id).val($('#observacion').val());
    }

    function mostrar(id) {
        displayFloatingDiv('div-panel-register', "ACTUALIZAR LISTA DE CHEQUEO", 70, 0, 10, 10);

        var url = 'ajax/flista_requisito.ajax.php?id=' + id;
        var capa = 'div-panel-register';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
    }

    function imprimir() {
        var url = '../print/lista_register.php' + set_url();
        prnpage = window.open(url, "IMPRIMIENDO ESTADO DE LA LISTA",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function form_filter() {
        var title = "FILTRADO DE REQUISITOS";
        displayFloatingDiv('div-panel-filter', title, 60, 0, 10, 20);

        refresh_ajax_select('', <?= !empty($id_capitulo) ? $id_capitulo : 0 ?>, <?= $numero ?>);
        refresh_ajax_select_capitulo('', <?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>, <?= $numero ?>);
    }

    function closep() {
        var url = 'llista.php?action=<?=$action?>&id_proceso=<?=$id_proceso?>&year=<?=$year?>';
        self.location.href = url;
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        InitDragDrop();

        refresh_ajax_select('', <?= !empty($id_capitulo) ? $id_capitulo : 0 ?>, <?= $numero ?>);
        refresh_ajax_select_capitulo('', <?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>, <?= $numero ?>);

        $('#componente').on('change', function() {
            refresh_ajax_select('', 0, 0);
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
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">LISTADO DE REQUISITOS</div>
                <div class="card-body">

                    <div class="alert alert-info" style="margin: 1px 5px 4px 5px;">
                        <div class="row">
                            <div class="col-md-2" style="font-weight: bold">Título de la Lista:</div>
                            <div class="col-md-10 pull-left"><?= textparse($nombre_lista) ?></div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-3">Mostradas:</div>
                        <div id="nshow" class="msg-circle nshow"></div>
                        <div class="col-md-3">Ocultas:</div>
                        <div id="nhide" class="msg-circle nhide"></div>
                    </div>

                    <form action='javascript:' method=post class="form-horizontal">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" name="menu" id="menu" value="tipo_lista" />
                        <input type="hidden" name="year" id="year" value="<?=$year?>" />

                        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" name="id_lista" id="id_lista" value="<?= $id_lista ?>" />

                        <input type="hidden" name="id_tipo_lista" id="id_tipo_lista"
                            value="<?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>" />
                        <input type="hidden" name="id_componente" id="id_componente"
                            value="<?= !empty($componente) ? $componente : 0 ?>" />
                        <input type="hidden" name="id_capitulo" id="id_capitulo"
                            value="<?= !empty(id_capitulo) ? $id_capitulo : 0 ?>" />
                        <input type="hidden" name="id_subapitulo" id="id_subcapitulo"
                            value="<?= !empty($id_subcapitulo) ? $id_subcapitulo : 0 ?>" />

                        <input type="hidden" name="numero" id="numero" value="<?= $numero ?>" />

                        <div id="toolbar" class="btn-btn-group btn-app">
                            <button id="btn-print" class="btn btn-success d-none d-lg-block" type="submit" onclick="imprimir(1);">
                                <i class="fa fa-print"></i>Imprimir
                            </button>

                            <button id="btn-print" class="btn btn-info" type="button" onclick="form_filter();">
                                <i class="fa fa-filter"></i>Filtrar
                            </button>
                        </div>

                        <?php
                            $obj_item= new Tlista_requisito($clink);
                            $obj_item->SetYear($year);
                            $obj_item->SetComponente($componente);
                            $obj_item->SetIdCapitulo($id_capitulo);
                            $obj_item->SetIdTipo_lista($id_tipo_lista);

                            $result= $obj_item->listar();

                            if (isset($obj_tipo)) unset($obj_tipo);
                            $obj_tipo= new Ttipo_lista($clink);
                            ?>

                        <table class="table table-hover table-striped" data-toggle="table" data-toolbar="#toolbar"
                             data-search="true" data-show-columns="true">
                            <thead>
                                <tr>
                                    <th data-field="id">No.</th>
                                    <?php if ($if_jefe) { ?>
                                    <th data-field="icons"></th>
                                    <?php } ?>
                                    <th>Peso</th>
                                    <th>Requisitos a Evaluar</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th>Registro</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($row= $clink->fetch_array($result)) { ?>
                                <tr>
                                    <td>
                                        <?php
                                           $numero= $row['componente'];
                                           if (!empty($row['id_tipo_lista'])) {
                                               $obj_tipo->Set($row['id_tipo_lista']);
                                               $capitulo= $obj_tipo->GetCapitulo();
                                               $subcapitulo= $obj_tipo->GetSubcapitulo();
                                           }
                                           if (!empty($capitulo)) $numero.= ".$capitulo";
                                           if (!empty($subcapitulo)) $numero.= ".$subcapitulo";

                                           $numero.= ") {$row['numero']}";

                                           echo $numero;
                                           ?>
                                    </td>

                                    <?php if ($if_jefe) { ?>
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                            href="javascript:mostrar(<?= $row['_id'] ?>);">
                                            <i class="fa fa-check"></i>Registrar
                                        </a>
                                    </td>
                                    <?php } ?>

                                    <td>
                                        <?= $Tpeso_inv_array[$row['peso']] ?>
                                    </td>

                                    <td>
                                        <?= textparse($row['nombre'])?>
                                    </td>
                                    <td>
                                        <input type="hidden" id="reg_fecha_<?=$row['_id']?>"
                                            name="reg_fecha_<?=$row['_id']?>" value="<?=$row['reg_fecha']?>" />
                                        <input type="hidden" id="cumplimiento_<?=$row['_id']?>"
                                            name="cumplimiento_<?=$row['_id']?>" value="<?=$row['cumplimiento']?>" />

                                        <div id="div_cumplimiento_<?=$row['_id']?>">
                                            <?=$Tcriterio_array[$row['cumplimiento']+1]?>
                                        </div>
                                        <div id="div_reg_fecha_<?=$row['_id']?>">
                                            <?= odbc2date($row['reg_fecha'], true)?>
                                        </div>

                                    </td>
                                    <td>
                                        <input type="hidden" id="observacion_<?=$row['_id']?>"
                                            name="observacion_<?=$row['_id']?>"
                                            value="<?= textparse($row['observacion'], true)?>" <div
                                            id="div-observacion_<?=$row['_id']?>">
                                        <?= textparse($row['observacion'])?>
                </div>

                </td>
                <td>
                    <div id="div_registro_<?=$row['_id']?>">
                        <?php
                                                $email= $obj_user->GetEmail($row['id_usuario']);
                                                echo "{$email['nombre']}, {$email['cargo']}";
                                                ?>
                        <br />
                        <?= odbc2time_ampm($row['cronos'])?>
                    </div>
                </td>
                </tr>
                <?php } ?>
                </tbody>
                </table>

                <input type="hidden" id="cant" name="cant" value="<?= $i ?>">

                <script type="text/javascript" language="JavaScript">
                document.getElementById('nshow').innerHTML = '<?=$nshow?>';
                document.getElementById('nhide').innerHTML = '<?=$nhide?>';
                </script>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action == 'update' || $action == 'add') { ?>
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <?php } ?>
                    <button class="btn btn-warning" type="reset"
                        onclick="self.location.href = '<?php prev_page() ?>'">Cerrar</button>
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

    <!-- panel-register -->
    <div id="div-panel-register" data-bind="draganddrop">

    </div>

    <!-- panel-requisito -->
    <div id="div-panel-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-title" class="panel-title win-drag col-10">FILTRADO</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-panel-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax-filter" class="card-body">
            <div class="form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-sm-4">
                        Componente:
                    </label>
                    <div class=" col-sm-8">
                        <select id="componente" name="componente" class="form-control"
                            onchange="refresh_ajax_select('', 0, 0);">
                            <option value="0">... </option>
                            <?php for ($i = 1; $i < _MAX_COMPONENTES_CI; ++$i) { ?>
                            <option value="<?= $i ?>" <?php if ($i == $componente) echo "selected='selected'" ?>>
                                <?= $Tambiente_control_array[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">
                        Capítulo:
                    </label>
                    <div id="ajax-capitulo" class="col-sm-8">
                        <select id="capitulo" name="capitulo" class="form-control">
                            <option value="0"> ... </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">
                        Epigrafe:
                    </label>
                    <div id="ajax-subcapitulo" class="col-sm-8">
                        <select id="subcapitulo" name="subcapitulo" class="form-control">
                            <option value="0"> ... </option>
                        </select>
                    </div>
                </div>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action == 'update' || $action == 'add') { ?>
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <?php } ?>
                    <button class="btn btn-warning" type="reset" onclick="filtrar()">Filtrar</button>
                    <button class="btn btn-danger" type="button"
                        onclick="CloseWindow('div-panel-filter');">Cerrar</button>
                </div>
            </div>

        </div>
    </div><!-- panel-requisito -->


</body>

</html>