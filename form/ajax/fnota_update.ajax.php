<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/nota.class.php";

$action= $_GET['action'];
if (empty($action)) 
    $action= 'list';
if ($action == 'edit') 
    $action= 'add';
if ($action == 'add'){
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$id_proceso_item= !empty($_GET['id_proceso_item']) ? $_GET['id_proceso_item'] : $id_proceso;
$reg_date= !empty($_GET['reg_date']) ? urldecode($_GET['reg_date']) : date("d/m/Y");
$id_nota= $_GET['id_nota'];

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$day= $time->longmonth();
$time->SetDay($day);

$obj= new Tnota($clink);
$obj->SetIdNota($id_nota);
$obj->Set();

$obj->SetIdProceso($id_proceso_item);
$obj->SetChkApply(true);
$row= $obj->getNota_reg();
$id_nota_code= $obj->get_id_nota_code();
$estado= $row['estado'];

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso_item);
$obj_prs->Set();
$nombre_prs= $obj_prs->GetNombre();
$nombre_prs.= ", ". $Ttipo_proceso_array[$obj_prs->GetTipo()];

$id_proceso_item= $obj_prs->GetId();
$id_proceso_item_code= $obj_prs->get_id_code();
?>


<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<script language="javascript">
function validar() {
    var form = document.forms['fnota_update'];
    var text;

    if (form.estado.value == 0) {
        text= "Por favor, especifique el estado en el que se encuentra la gestión de la nota ";
        text+= "(no-conformidad, observación o nota de mejora)."
        alert(text);
        return;
    }
    if (!Entrada(form.descripcion.value)) {
        text= "Debe introducir las observaciones para el estado actual en la gestión de esta nota ";
        text+= "(no-conformidad, observación o nota de mejora).";
        alert(text);
        return;
    }

    if (!valida_estado()) 
        return;

    ejecutar('register');
}

function valida_estado() {
    var form = document.forms['fnota_update'];

    if (!Entrada(form.fecha.value)) {
        alert('Introduzca la fecha a la que corresponde el registro.');
        return false;
    } else if (!isDate_d_m_yyyyy(form.fecha.value)) {
        alert('Fecha con formato incorrecto. (d/m/yyyy) Ejemplo: 01/01/2010');
        return false;
    }

    var diff = DiferenciaFechas("<?= date('d/m/Y') ?>", form.fecha.value, 'd');

    if (diff < 0) {
        alert("No es posible definir el estado o situación para una fecha futura");
        form.fecha.value = "<?= date('d/m/Y H:i') ?>";
        return false;
    }

    return true;
}
</script>

<script type="text/javascript">
$(document).ready(function() {
    focusin = function(_this) {
        tabId = $(_this).parents('* .tabcontent');
        $(".tabcontent").hide();
        $('#nav-' + tabId.prop('id')).addClass('active');
        tabId.show();
        $(_this).focus();
    }

    //When page loads...
    $(".tabcontent").hide(); //Hide all content
    $("ul.nav li:first a").addClass("active").show(); //Activate first tab
    $(".tabcontent:first").show(); //Show first tab content

    //On Click Event
    $("ul.nav li a").click(function() {
        $("ul.nav li a").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tabcontent").hide(); //Hide all tab content

        var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
        $("#" + activeTab).fadeIn(); //Fade in the active ID content
        //         $("#" + activeTab + " .form-control:first").focus();
        return false;
    });	

    $('#div_fecha').datepicker({
        format: 'dd/mm/yyyy'
    });


    try {
        $('#descripcion').tinymce().destroy();
    } catch (e) {
        ;
    }

    tinymce.init({
        selector: '#descripcion',
        theme: 'modern',
        height: 160,
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

    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title ajax-title col-11 win-drag">REGISTRO</div>
                <div class="col-1">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body info-panel">
            <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                <?php if ($action != 'list') { ?>
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                <?php } ?>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Registros Anteriores</a></li>
            </ul>

            <form id="fnota_update" name=fnota_update" action='javascript:validar()'>
                <input type="hidden" name="exect" value="<?= $action ?>" />
                <input type="hidden" name="id" value="<?= $id_nota ?>" />
                <input type="hidden" name="id_code" value="<?= $id_nota_code ?>" />
                <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />

                <input type="hidden" name="proceso" value="<?= $id_proceso ?>" />
                <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />
                <input type="hidden" name="id_proceso_item" value="<?= $id_proceso_item ?>" />
                <input type="hidden" name="id_proceso_item_code" value="<?= $id_proceso_item_code ?>" />

                <input type="hidden" name="year" value="<?= $year ?>" />
                <input type="hidden" name="month" value="<?= $month ?>" />
                <input type="hidden" id="reg_date" name="reg_date" value="<?= $reg_date ?>" />
                <input type="hidden" name="menu" value="nota_update" />

                <?php if ($action != 'list') { ?>
                <!-- tab1 -->
                <div class="tabcontent " id="tab1">
                    <label class="alert alert-warning" style="margin-bottom: 2px; padding: 2px;">
                        <?=textparse($nombre_prs)?>
                    </label>
                    <label class="alert alert-info" style="padding: 2px;">
                        <?=textparse($obj->GetDescripcion())?>
                    </label>

                    <div class="form-group row">
                        <div class="col-6">
                            <label class="col-form-label col-5">
                                Fecha de registro:
                            </label>
                            <div class="col-6">
                                <div class="input-group date" id="div_fecha" data-date-language="es">
                                    <input type="text" id="fecha" name="fecha" class="form-control date"
                                        value="<?= $reg_date ?>" readonly />
                                    <span class="input-group-text"><span
                                            class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="col-form-label col-3">
                                Estado:
                            </label>
                            <div class="col-8">
                                <input type="hidden" name="id_estado" value="<?= $estado ?>" />

                                <select name="estado" id="estado" class="form-control" onchange="valida_estado()">
                                    <option value="0">seleccione ...</option>
                                    <?php for ($i = 1; $i < 4; ++$i) { ?>
                                    <option value="<?= $i ?>" <?php if ($i == $estado) echo "selected" ?>>
                                        <?= $estado_hallazgo_array[$i] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="_radio_prs" name="_radio_prs" value="1" checked="checked" />
                            Aplicar este estado a todas las unidades y procesos subordinados directos.
                        </label>
                    </div>

                    <label class="col-form-label col-12">
                        Observaciones:
                    </label>
                    <div class="col-12">
                        <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
                    </div>
                </div> <!-- tab1 -->
                <?php  } ?>

                <!-- tab2 -->
                <div class="tabcontent" id="tab2">
                    <table id="table" class="table table-striped" data-toggle="table" data-height="320">
                        <thead>
                            <tr>
                                <th>ESTADO</th>
                                <th>FECHA Y HORA</th>
                                <th>RESPONSABLE</th>
                                <th>OBSERVACIÓN</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $obj->SetYear(null);
                            $obj->SetMonth(null);
                            $result = $obj->getAvance($id_nota, true);

                            while ($row = $clink->fetch_array($result)) {
                            ?>
                            <tr>
                                <td>
                                    <?= $estado_hallazgo_array[$row['estado']] ?>
                                </td>
                                <td>
                                    <?= odbc2date($row['cronos']) ?>
                                </td>
                                <td>
                                    <?= $row['responsable'] ?>
                                </td>
                                <td>
                                    <?= textparse($row['observacion']) ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div> <!-- tab2 -->

                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action != 'list') { ?> <button class="btn btn-primary" type="submit">
                        Aceptar</button><?php } ?>
                    <button class="btn btn-warning" type="reset"
                        onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                </div>

                <div id="_submited" class="submited" align="center" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos
                    minutos ........
                </div>
            </form>
            <br />
        </div>
    </div>
</div>