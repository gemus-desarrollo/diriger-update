<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/21/15
 * Time: 2:28 p.m.
 */

?>

<?php require '../form/inc/_page_init.inc.php'; ?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<link rel="stylesheet" type="text/css" href="../css/general.css?version=">

<link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

<link rel="stylesheet" href="../libs/windowmove/windowmove.css?version=" />
<script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

<script type="text/javascript" src="../js/windowcontent.js?version="></script>

<script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
<script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

<link rel="stylesheet" type="text/css" href="../css/widget.css?version=">
<script type="text/javascript" src="../js/widget.js?version="></script>

<script type="text/javascript" src="../js/time.js?version=" charset="utf-8"></script>
<script type="text/javascript" src="../js/ajax_core.js?version=" charset="utf-8"></script>
<script type="text/javascript" src="../js/form.js?version="></script>

<link rel="stylesheet" type="text/css" href="../css/tablero.css?version=" />
<link rel="stylesheet" type="text/css" href="../css/custom.css?version=" />
<script type="text/javascript" src="../js/tablero.js?version=" charset="utf-8"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

<script type="text/javascript" src="../js/menu.js?version="></script>

<link rel="stylesheet" href="../css/scheduler.css?version=" type="text/css" />

<style type="text/css">
body {
    background: #F7F7F7;
    background-color: #F7F7F7;
    color: #73879C;
}
.win-board {
    width: 30%;
}
.none-border {
    border: none;
    background-color: #824100;
    color: white;
    font-weight: bolder;
}
.box {
    min-width: 300px;
    max-width: 24%;
    margin: 6px;

    display: inline-block;
}
.box-tools i.fa {
    font-size: 1.8em;
}
.box .box-body {
    max-height: 160px;
    overflow-wrap: break-word;
    overflow-x: hidden;
}
</style>

<?php
if ($signal == 'riesgo') {
    $title = 'riesgo';
    $title_plan = "prevención";
}
if ($signal == 'nota') {
    $title = 'hallazgo';
    $title_plan = "medidas";
}
?>

<script language="javascript" type="text/javascript">
<?php
$array_id_show= null;
$nshow= 0;
$nhide= 0;
?>

function mostrar(action) {
    var title, url;
    var w, h;
    var minH= 0;

    var id_riesgo = $('#id_riesgo').val();
    var id_nota = $('#id_nota').val();
    // var month= $('#month').val();
    var year = $('#year').val();
    var if_jefe = $('#if_jefe').val();
    var id_proceso = $('#proceso').val();
    var id_proceso_item = $('#id_proceso_item').val();
    var id_plan = $('#id_plan').val();
    var id_plan_code = $('#id_plan_code').val();
    var tipo_plan = $('#tipo_plan').val();
    var id_auditoria = $('#id_auditoria').val();

    var id = 0;
    if (tipo_plan == <?=_PLAN_TIPO_ACCION?> || tipo_plan == <?=_PLAN_TIPO_MEDIDAS?>)
        id = id_nota;
    if (tipo_plan == <?=_PLAN_TIPO_PREVENCION?>)
        id = id_riesgo;

    if (action == 'register') {
        reg_date = $('#reg_fecha_' + id).val();
        title = "DEFINIR EL ESTADO EN LA GESTIÓN DEL <?=strtoupper($title)?>";
        w = 70;
        h = 0;
        url = '../form/ajax/f<?=$signal?>_update.ajax.php?reg_date=' + encodeURI(reg_date);
    }
    if (action == 'tareas') {
        title = "TAREAS ASOCIADAS AL <?=strtoupper($title)?>";
        w = 60;
        h = 0;
        minH= 340;
        url = '../form/ajax/ftarea_status.ajax.php?';
    }
    if (action == 'repro') {
        url = '../form/ajax/fcopy.ajax.php?copy_all=0&';
        title = "COPIAR EL <?=strtoupper($title)?> Y LAS TAREAS ASOCIADAS PARA EL PRÓXIMO AÑO";
        w = 50;
        h = 0;
    }
    if (action == 'copy') {
        url = '../form/ajax/fcopy.ajax.php?copy_all=1&';
        title = "COPIAR TODO EL TABLERO Y SUS TAREAS ASOCIADAS PARA EL PRÓXIMO AÑO";
        w = 50;
        h = 0;

        var nums_id_show = $('#nums_id_show').val();
        var array_id_show = $('#array_id_show').val();
        url += '&nums_id_show=' + nums_id_show + '&array_id_show=' + array_id_show + '&';
    }
    if (action == 'aprove') {
        url = '../form/ajax/fplan_ap.ajax.php?';
        w = 70;
        h = 0;
        title = "APROBAR PLAN DE <?=strtoupper($title_plan)?>";
    }
    if (action == 'aprove')
        month = null;

    url += '&signal=<?=$signal?>&id_riesgo=' + id_riesgo + '&id_nota=' + id_nota + '&action=' + action;
    // url+= '&month=' + month;
    url += '&year=' + year + '&if_jefe=' + if_jefe + '&id_proceso=' + id_proceso + '&tipo=<?=$tipo_plan?>';
    url += '&id_plan=' + id_plan + '&id_plan_code=' + id_plan_code + '&tipo_plan=' + tipo_plan;
    url += '&id_proceso_item=' + id_proceso_item + '&id_auditoria=' + id_auditoria;

    var capa = 'div-ajax-panel';
    var metodo = 'GET';
    var valores = '';
    var funct= "ajaxPanelScrollY('div-ajax-panel', 70, "+minH+");";

    displayFloatingDiv('div-ajax-panel', title, w, h, 10, 20);
    FAjax(url, capa, valores, metodo, funct);    
}

function ejecutar(action) {
    var url;
    var valores;

    if (action == 'aprove') {
        url = '../php/plan_ci.interface.php?signal=<?=$signal?>&action=aprove&';
        valores = $("#frm_ap").serialize();
    }
    if (action == 'register') {
        url = '../php/<?=$signal?>.interface.php?';
        valores = $("#f<?=$signal?>_update").serialize();
    }
    if (action == 'repro' || action == 'copy') {
        url = '../php/<?=$signal?>.interface.php?version=&action=' + action;
        valores = $("#fcopy").serialize();
    }

    var metodo = 'POST';
    var capa = 'div-ajax-panel';
    var funct= '';

    parent.app_menu_functions = false;
    $('#_submit').hide();
    $('#_submited').show();

    FAjax(url, capa, valores, metodo, funct);
}

function _delete() {
    if (ifblock_app_menu())
        return;

    function _this() {
        var id_riesgo = $('#id_riesgo').val();
        var id_nota = $('#id_nota').val();
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        var url = '../form/ajax/fdelete_riesgo.ajax.php?signal=<?=$signal?>&action=add' + '&id_proceso=' + id_proceso +
            '&year=' + year;
        url += '&id_nota=' + id_nota + '&id_riesgo=' + id_riesgo;

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        displayFloatingDiv('div-ajax-panel', "ELIMINAR <?= strtoupper($title)?>", 60, 0, 10, 15);
        FAjax(url, capa, valores, metodo, funct);
    }

    var text =
        "Esta seguro de querer eliminar el <?=$title?>. El <?=$title?> será eliminado del Plan de <?=$title_plan?>. Desea continuar?";
    confirm(text, function(ok) {
        if (!ok)
            return;
        else
            _this();
    });
}

function _edit() {
    if (ifblock_app_menu())
        return;

    var id_riesgo = $('#id_riesgo').val();
    var id_nota = $('#id_nota').val();
    var year = $('#year').val();

    var url = '../php/<?=$signal?>.interface.php?version=&action=edit&signal=<?=$signal?>';
    url += '&id_nota=' + id_nota + '&menu=tablero&id_riesgo=' + id_riesgo + '&id=' + id_<?=$signal?> + '&year=' + year;

    self.location.href = url;
}

function _add() {
    var id_proceso = $('#proceso').val();
    var month = $('#month').val();
    var year = $('#year').val();

    url = '../form/f<?=$signal?>.php?version=&action=add&signal=<?=$signal?>';
    url += '&id_proceso=' + id_proceso + '&year=' + year;

    self.location.href = url;
}

function cerrar(error) {
    parent.app_menu_functions = false;
    try {
        if (error && error != 'undefined')
            alert(error);
    } catch (e) {
        error = 'undefined';
    }

    CloseWindow('div-ajax-panel');

    if (!Entrada(error) || error == 'undefined' || !error) {
        refreshp();
    } else {
        parent.app_menu_functions = true;
    }
}

function resume(flag) {
    var id_proceso = $("#proceso").val();
    var id_auditoria = $('#id_auditoria').val();
    var year = $('#year').val();
    var action = $('#exect').val();
    var tipo_plan = $('#tipo_plan').val();
    <?php if ($signal == 'nota') {?>
    var show_all_notes = $('#show_all_notes').val();
    <?php } ?>
    var url;

    if (flag == 0)
        url = '../form/l<?=$signal?>.php';
    else
        url = '../print/lriesgo_resume.php';

    url += '?version=&action=' + action + '&year=' + year + '&id_proceso=' + id_proceso;

    <?php if ($signal == 'nota') {?>
    var observ = $("#observ").attr("checked") ? 1 : 0;
    var mej = $("#mej").attr("checked") ? 1 : 0;
    var noconf = $("#noconf").attr("checked") ? 1 : 0;

    url += '&noconf=' + noconf + '&mej=' + mej + '&observ=' + observ + '&show_all_notes=' + show_all_notes +
        '&id_auditoria=' + id_auditoria;
    <?php } ?>

    <?php if ($signal == 'riesgo') { ?>
    var estrategico = 0,
        sst = 0,
        ma = 0,
        origen = 0,
        econ = 0,
        reg = 0,
        info = 0,
        calidad = 0;

    if ($('#ifestrategico').is(':checked'))
        estrategico = 1;
    if ($('#econ').is(':checked'))
        econ = 1;
    if ($('#ma').is(':checked'))
        ma = 1;
    if ($('#sst').is(':checked'))
        sst = 1;
    if ($('#origen').is(':checked'))
        origen = 1;
    if ($('#reg').is(':checked'))
        reg = 1;
    if ($('#info').is(':checked'))
        info = 1;
    if ($('#calidad').is(':checked'))
        calidad = 1;

    url += '&ma=' + ma + '&sst=' + sst + '&econ=' + econ + '&origen=' + origen + '&reg=' + reg + '&info=' + info +
        '&estrategico=' + estrategico;
    url += '&calidad=' + calidad + '&tipo_plan=' + tipo_plan;;
    <?php } ?>

    if (flag == 0)
        self.location.href = url;
    else
        show_imprimir(url, 'LEVANTAMIENTO DE RIESGOS', "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
}
</script>

<script language="javascript" type="text/javascript">
function refreshp() {
    var id_proceso = $('#proceso').val();
    // var month= $('#month').val();
    var year = $('#year').val();
    var action = $('#exect').val();
    <?php if ($signal == 'nota') {?>
    var show_all_notes = $('#show_all_notes').val();
    <?php } ?>

    var url = '<?=$signal?>.php?version=&action=' + action;
    url += '&year=' + year + '&id_proceso=' + id_proceso;

    <?php if ($signal == 'riesgo' || $signal == 'lriesgo') { ?>
    var estrategico = 0,
        sst = 0,
        ma = 0,
        origen = 0,
        econ = 0,
        reg = 0,
        info = 0,
        calidad = 0;

    if ($('#ifestrategico').is(':checked'))
        estrategico = 1;
    if ($('#econ').is(':checked'))
        econ = 1;
    if ($('#ma').is(':checked'))
        ma = 1;
    if ($('#sst').is(':checked'))
        sst = 1;
    if ($('#origen').is(':checked'))
        origen = 1;
    if ($('#reg').is(':checked'))
        reg = 1;
    if ($('#info').is(':checked'))
        info = 1;
    if ($('#calidad').is(':checked'))
        calidad = 1;

    url += '&ma=' + ma + '&sst=' + sst + '&econ=' + econ + '&origen=' + origen + '&reg=' + reg + '&info=' + info +
        '&estrategico=' + estrategico;
    url += '&calidad=' + calidad;
    <?php } ?>

    <?php if ($signal == 'nota' || $signal == 'lnota') { ?>
    var noconf = 0,
        mej = 0,
        observ = 0;

    if ($('#mej').is(':checked'))
        mej = 1;
    if ($('#observ').is(':checked'))
        observ = 1;
    if ($('#noconf').is(':checked'))
        noconf = 1;

    url += '&noconf=' + noconf + '&mej=' + mej + '&observ=' + observ + '&id_auditoria=' + $('#id_auditoria').val();
    url += '&show_all_notes=' + show_all_notes;
    <?php } ?>

    self.location = url;
}

function loadpage(id) {
    var year = $('#year').val();
    var tipo_plan = $('#tipo_plan').val();

    url = '<?=$signal?>.php?signal=<?=$signal?>&tipo_plan=' + tipo_plan + '&year=' + year;
    url += '&id_proceso=' + id;

    self.location.href = url;
}

function imprimir(flag) {
    var url;
    var estrategico = 0,
        sst = 0,
        ma = 0,
        origen = 0,
        econ = 0,
        reg = 0;
    var observ = 0,
        mej = 0,
        noconf = 0;

    var year = $('#year').val();
    var id_proceso = $('#proceso').val();
    var id_nota = $('#id_nota').val();
    var id_riesgo = $('#id_riesgo').val();
    var tipo_plan = $('#tipo_plan').val();
    var print_all = 0;
    <?php if ($signal == 'nota' || $signal == 'lnota') {?>
    var show_all_notes = $('#show_all_notes').val();
    <?php } ?>

    if (flag == 1) {
        <?php if ($signal == 'riesgo' || $signal == 'nota') { ?>
        text = "Desea imprimir en el <?=$signal == 'riesgo' ? "Plan de Prevención" : "Plan de Medidas"?> solo los ";
        text += "<?=$signal == 'riesgo' ? "riesgos" : "notas" ?> que tiene tareas definidas para su gestión?";
        confirm(text, function(ok) {
            if (ok) {
                print_all = 0;
                _this();
            } else {
                print_all = 1;
                _this();
            }
        });
        <?php } else { ?>
        _this();
        <?php } ?>
    }

    function _this() {
        <?php if ($signal == 'riesgo') {?> 
            url = '../print/riesgos_plan.php';
        <?php } ?>
        <?php if ($signal == 'lriesgo') {?> 
            url = '../print/lriesgo.php';
        <?php } ?>
        <?php if ($signal == 'nota') {?> 
            url = '../print/acciones_plan.php';
        <?php } ?>
        <?php if ($signal == 'lnota') {?> 
            url = '../print/lnota.php';
        <?php } ?>
        <?php if ($signal == 'graph_nota') {?> 
            url = '../print/graph_nota.php';
        <?php } ?>
        url += '?year=' + year + '&id_proceso=' + id_proceso + '&id_riesgo=' + id_riesgo + '&id_nota=' + id_nota;
        url += '&tipo_plan=' + tipo_plan;
        // url+= '?month=' + month;

        <?php if ($signal == 'riesgo' || $signal == 'lriesgo')  { ?>
        if ($('#ifestrategico').is(':checked'))
            estrategico = 1;
        if ($('#ma').is(':checked'))
            ma = 1;
        if ($('#sst').is(':checked'))
            sst = 1;
        if ($('#econ').is(':checked'))
            econ = 1;
        if ($('#origen').is(':checked'))
            origen = 1;
        if ($('#reg').is(':checked'))
            reg = 1;
        if ($('#info').is(':checked'))
            info = 1;
        if ($('#calidad').is(':checked'))
            calidad = 1;

        url += '&estrategico=' + estrategico + '&ma=' + ma + '&econ=' + econ + '&sst=' + sst + '&origen=' + origen;
        url += '&reg=' + reg + '&info=' + info + '&calidad=' + calidad;
        <?php } ?>

        <?php if ($signal == 'nota' || $signal == 'lnota')  { ?>
        if ($('#noconf').is(':checked'))
            noconf = 1;
        if ($('#observ').is(':checked'))
            observ = 1;
        if ($('#mej').is(':checked'))
            mej = 1;

        url += '&noconf=' + noconf + '&mej=' + mej + '&observ=' + observ + '&id_auditoria=' + $('#id_auditoria').val();
        url += '&show_all_notes=' + show_all_notes;
        <?php } ?>

        url += '&print_all=' + print_all;

        <?php
        if ($signal == 'riesgo')
            $title= "PLAN DE PREVENCIÓN DE RIESGOS";
        if ($signal == 'lriesgo')
            $title= "RESUMEN DE RIESGOS";
        if ($signal == 'nota')
            $title= "RESUMEN DE DE ACCIONES PREVENTIVAS/CORRECTIVAS Y DE MEJORAS";
        ?>

        show_imprimir(url, "IMPRIMIENDO <?=$title?>", "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    if (flag == 2) {
        var url = '../print/<?= $config->summaryextend ? 'resumen_plan_extend' : 'resumen_plan' ?>.php?id_proceso=' +
            id_proceso + '&year=' + year;
        url += '&tipo_plan=<?=$tipo_plan?>' + '&id_auditoria=' + $('#id_auditoria').val();
        show_imprimir(url, "IMPRIMIENDO RESUMEN DEL CUMPLIMIENTO DE LAS TAREAS",
            "width=780,height=400,toolbar=no,location=no, scrollbars=yes");
    }
    if (flag == 3) {
        var url = '../print/status_tareas_<?=$signal?>.php?id_proceso=' + id_proceso + '&year=' + year +
            '&id_auditoria=' + $('#id_auditoria').val();
        show_imprimir(url, "IMPRIMIENDO RESUMEN DEL CUMPLIMIENTO DE LAS TAREAS",
            "width=780,height=400,toolbar=no,location=no, scrollbars=yes");
    }
}

function show_filter() {
    var w = 70;
    <?php if ($signal == 'nota') {?>
        w = 30;
    <?php } ?>
    displayFloatingDiv('div-filter', false, w, 0, 10, 15);
}

function mostrar_auditorias(panel) {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var id_auditoria= $('#id_auditoria').val(); 
    var url = '../form/ajax/fadd_auditorias.ajax.php?signal=nota&action=add' + '&id_proceso=' + id_proceso;
        url+= '&id_auditoria=' + id_auditoria + '&year=' + year + '&panel=' + panel;

    var capa = 'div-ajax-panel';
    var metodo = 'GET';
    var valores = '';
    var funct= "ajaxPanelScrollY('div-ajax-panel', 70, 0);";

    displayFloatingDiv('div-ajax-panel', "SELECCIONE LA AUDITORÍA O ACCIÓN DE CONTROL", 80, 40, 10, 15);
    FAjax(url, capa, valores, metodo, funct);  
}

function closep() {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();

    <?php if ($signal == 'lriesgo')  { ?>
    var url = '../html/riesgo.php?version=&id_proceso=' + id_proceso + '&year=' + year;

    if ($('#ifestrategico').is(':checked'))
        estrategico = 1;
    if ($('#ma').is(':checked'))
        ma = 1;
    if ($('#sst').is(':checked'))
        sst = 1;
    if ($('#econ').is(':checked'))
        econ = 1;
    if ($('#origen').is(':checked'))
        origen = 1;
    if ($('#reg').is(':checked'))
        reg = 1;
    if ($('#info').is(':checked'))
        info = 1;
    if ($('#calidad').is(':checked'))
        calidad = 1;

    url += '&estrategico=' + estrategico + '&ma=' + ma + '&econ=' + econ + '&sst=' + sst + '&origen=' + origen;
    url += '&reg=' + reg + '&info=' + info + '&calidad=' + calidad;
    <?php } ?>

    <?php if ($signal == 'lnota' || $signal == 'graph_nota')  { ?>
    var noconf = 0,
        mej = 0,
        observ = 0;

    if ($('#mej').is(':checked'))
        mej = 1;
    if ($('#observ').is(':checked'))
        observ = 1;
    if ($('#noconf').is(':checked'))
        noconf = 1;

    var url = '../html/nota.php?version=&id_proceso=' + id_proceso + '&year=' + year;
    url += '&noconf=' + noconf + '&mej=' + mej + '&observ=' + observ;
    <?php } ?>
    self.location.href = url;
}

function showInfoPanel() {
    displayFloatingDiv('info-panel-plan', '', 50, 0, 5, 15);
    ajaxPanelScrollY('info-panel-plan', 50, 0);
}

function showWindow(action) {
    showOpenWindow('docs', action);
}
</script>

<script type="text/javascript" charset="utf-8">
function _dropdown_prs(id) {
    $('#proceso').val(id);
    refreshp();
}
function _dropdown_year(year) {
    $('#year').val(year);
    refreshp();
}
function _dropdown_month(month) {
    $('#month').val(month);
    refreshp();
}

$(document).ready(function() {
    InitDragDrop();

    <?php if (!is_null($error)) { ?>
    alert("<?= str_replace("\n", " ", $error) ?>");
    <?php } ?>
});
</script>