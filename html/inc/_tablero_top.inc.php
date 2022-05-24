<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/21/15
 * Time: 4:27 p.m.
 */

?>

<?php require '../form/inc/_page_init.inc.php'; ?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="../css/general.css?version=" />
<link rel="stylesheet" type="text/css" media="screen" href="../css/table.css?version=" />

<link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
<script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

<link rel="stylesheet" href="../libs/windowmove/windowmove.css?version=" />
<script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

<script type="text/javascript" src="../js/windowcontent.js?version="></script>

<script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
<script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

<link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css?version="/>
<script type="text/javascript" src="../js/widget.js?version="></script>

<script type="text/javascript" src="../js/ajax_core.js?version=" charset="utf-8"></script>

<script type="text/javascript" src="../js/form.js?version="></script>

<link rel="stylesheet" type="text/css" media="screen" href="../css/custom.css?version=" />
<link rel="stylesheet" type="text/css" media="screen" href="../css/tablero.css?version=" />
<script type="text/javascript" src="../js/tablero.js?version=" charset="utf-8"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

<link rel="stylesheet" href="../css/scheduler.css?version=" type="text/css" />

<style type="text/css">
    body {
        background: #F7F7F7;
        background-color: #F7F7F7;
        color: #73879C;
        padding: 0px;
        margin: 0px;
        height: 100vh;
        overflow: auto;
    }
    .twobar {
        margin-top: 10px!important;
        margin-right: 6px;
    }
    .div-ajax-graph-select-panel {
        filter:alpha(opacity=80);
        -moz-opacity:0.9;
        opacity: 0.9;
    }
</style>

<script language="javascript" type="text/javascript">
    function refreshp(flag) {
        var id_tablero= $('#tablero').val();
        var action= $('#exect').val();

        var year= $('#year').val();
        var month= $('#month').val();
        var day= $('#day').val();
        var recompute= $('#recompute').val();

        switch (flag) {
            case 0:
                year= 0;
                month= 0;
                day= 0;
                break;
            case 1:
                month= 0;
                day= 0;
                break;
            case 2:
                day= 0;
                break;
        }

        var url;
        url= '<?=$signal?>.php?id_tablero='+id_tablero+'&year='+year;
        url+= '&month='+month+'&day='+day+'&action='+action+'&recompute='+recompute;
        self.location.href= url;
    }

    function recompute() {
        $('#recompute').val(1);
        refreshp(-1);
    }

    function imprimir() {
        var text;

        var month= $('#month').val();
        var year= $('#year').val();
        var day= $('#day').val();
        var id_tablero= $('#tablero').val();
        var id_proceso= $('#proceso').val();
        var url;

        <?php $_signal= ($signal == 'resumen') ? 'tablero' : $signal; ?>

        text= "Para ver\\imprimir el resumen correspondiente al mes <?=$meses_array[(int)$month]?> del año <?=$year?> ";
        text+= "seleccione \"Si\". Para ver\\imprimir la tabla resumen correspondiente a todo el año <?=$year?>, seleccione \"No\"";
        confirm(text, function(ok) {
            if (ok) {
                url= '../print/<?=$_signal?>_mensual.php';
                this_1();
            } else {
                url='../print/<?=$_signal?>.php';
                this_1();
            }
        });

        function this_1() {
            url+= '?id_tablero='+id_tablero+'&month='+month+'&year='+year+'&day='+day;
            url+= '&id_proceso='+id_proceso;
            show_imprimir(url,"IMPRIMIENDO RESUMEN DEL ESTADO DE LOS INDICADORES","width=900,height=600,toolbar=no,location=no, scrollbars=yes");
        }
    }

    function showWindow() {
        var action= $('#exect').val();
        showOpenWindow('docs', action);
    }

</script>

 <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }
     function _dropdown_tablero(id) {
        $('#tablero').val(id);
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
    function _dropdown_day(day) {
        $('#day').val(day);
        refreshp();
    }
    $(document).ready( function () {
        InitDragDrop();

        <?php if (!is_null($error)) { ?>
            alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
</script>