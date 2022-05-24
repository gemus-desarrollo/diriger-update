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
require_once "../../php/class/indicador.class.php";

$_SESSION['debug']= null;

$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : null;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$day= !empty($_GET['day']) ? $_GET['day'] : date('d');

$fecha= $day.'/'.$month.'/'.$year;	

$obj= new Tindicador($clink);
$obj->SetYear($year);
$obj->Set($id_indicador);

$cumulative= $obj->GetIfCumulative();
$formulated= $obj->GetIfFormulated();

?>

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    var form = document.forms['ftipo_graph'];

    $('#_radio_cumulative').val($('#radio_cumulative1').is(':checked') ? 1 : 0);
    $('#_radio_formulated').val($('#radio_formulated2').is(':checked') ? 1 : 0);

    _grafico_indicador();
    CloseWindow('div-ajax-graph-select-panel');
}
</script>

<script type="text/javascript">
$(document).ready(function() {
    <?php if (!$cumulative && !$formulated) { ?>
    validar();
    <?php } ?>

    <?php if (!is_null($error)) { ?>
    alert("<?= str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>


<form id="ftipo_graph" name="ftipo_graph" action="javascript:validar()" method="post">
    <input type="hidden" id="_radio_cumulative" name="_radio_cumulative" value="1" />
    <input type="hidden" id="_radio_formulated" name="_radio_formulated" value="1" />

    <div class="form-group row col-12">
        <?php if ($cumulative) { ?>
        <div class="checkbox col-12">
            <label>
                <input type="radio" name="radio_cumulative" id="radio_cumulative1" value="1" checked="checked" />
                Se muestrán los valores acumulados hasta el cierre de cada mes.
            </label>
        </div>
        <div class="checkbox col-12">
            <label>
                <input type="radio" name="radio_cumulative" id="radio_cumulative2" value="0" />
                Se muestrán los valores de cierre de cada mes. El día 1 de cada mes el indicador se inicializá en 0.
            </label>
        </div>

        <?php } else { ?>

        <input type="hidden" id="radio_cumulative1" value="1" />
        <?php } ?>

        <?php if ($formulated) { ?>
        <div class="checkbox col-12">
            <label>
                <input type="radio" name="radio_formulated" id="radio_formulated1" value="1" checked="checked" />
                Se grafíca solo el indicador seleccionado, todo el año escogido.
            </label>
        </div>
        <div class="checkbox col-12">
            <label>
                <input type="radio" name="radio_formulated" id="radio_formulated2" value="0" />
                Se mustrán cada uno los indicadores que lo componen. Solo se graficán los valores correspondientes al
                cierre del mes escogido.
            </label>
        </div>

        <?php } else { ?>

        <input type="hidden" id="radio_formulated1" value="1" />
        <?php } ?>

    </div>

    <!-- buttom -->
    <div id="_submit" class="btn-block btn-app">
        <button class="btn btn-primary" type="submit">Aceptar</button>
        <button class="btn btn-warning" type="reset"
            onclick="CloseWindow('div-ajax-graph-select-panel');">Cancelar</button>
    </div>

    <div id="_submited" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
    </div>
</form>