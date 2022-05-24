<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/6/15
 * Time: 8:25 a.m.
 */

?>

<?php require 'inc/_page_init.inc.php'; ?>
<!-- Bootstrap core JavaScript
================================================== -->

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         

<link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
<script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>  

<link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
<script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>    

<link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">  
<script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>    

<script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
<script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

<link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
<script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

<link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
<script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script> 

<link href="../libs/combobox/jquery-combobox.css" rel="stylesheet">
<script type="text/javascript" src="../libs/combobox/jquery-combobox.js"></script>

<script type="text/javascript" src="../js/time.js"></script>
<script type="text/javascript" src="../js/form.js?version="></script> 

<script type="text/javascript" src="../js/ajax_core.js?version="></script>

<style type="text/css">
    .ajax-task-table {
        min-height: 400px;
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<script language="javascript" type="text/javascript">
    function displayWindow(flag) {
        var title;
        if (flag == 1) 
            title= "LISTADO DE TAREAS NO ASIGNADAS";
        if (flag == 0) 
            title= "LISTADO DE AUDITORIAS O CONTROLES REALIZADOS EN EL PERIODO";

        displayFloatingDiv('div-ajax-panel', title, 80,0,10,15);
    }

    function tarea_table_ajax(action) {
        var year = $('#year').val();
        var menu= $('#menu').val();
        var id_nota= $('#id_nota').val();
        var id_riesgo= $('#id_riesgo').val();
        var id_proyecto= $('#id_proyecto').val();
        
        var url= 'ajax/tarea_tabs.ajax.php?action='+action+'&signal=<?=$signal?>&menu='+menu+'&year='+year;
        url+= '&id_nota='+id_nota+'&id_proyecto='+id_proyecto+'&id_riesgo='+id_riesgo;

        var capa= 'ajax-task-table';
        var metodo= 'GET';
        var valores= '';
        FAjax(url,capa,valores,metodo);               
    }
    
    function cerrar() {
        tarea_table_ajax($('#exect').val());
        CloseWindow('div-ajax-panel');
    }
    
    function mostrar(flag) {
        var id_auditoria= $('#id_auditoria').val();
        var id_nota= $('#id_nota').val();
        var id_riesgo= $('#id_riesgo').val();
        var id_proyecto= $('#id_proyecto').val();

        var id_proceso= $('#proceso').val();
        var origen= $('#origen').val();
        var fecha_inicio= encodeURI($('#fecha_inicio').val());
        var fecha_fin= encodeURI($('#fecha_fin').val());
        var year= $('#year').val();
        var menu= $('#menu').val();
        
        var text;
        
        if (flag == 0) {
            if (id_proceso == 0) {
                $('#proceso').focus(focusin($('#proceso')));
                text= "Deberá especificar la Unidad Organizativa o proceso al que se le está dictando esta nota de hallazgo (no conformidad, ";
                text+= "observación u nota de mejora)";
                alert(text); 
                return;
            }
            if (origen == 0) {
                $('#origen').focus(focusin($('#origen')));
                alert("Deberá especificar si se trata de una auditoría o supervisión, y si es una acción de control interna o externa"); 
                return;
            }

            if (!valida_date()) 
                return;
        }

        var _url= '&action=<?= $action ?>&fecha_inicio='+fecha_inicio+'&fecha_fin='+fecha_fin+'&id_proceso='+id_proceso;
        _url+= '&origen='+origen+'&id_auditoria='+id_auditoria+'&year='+year+'&id_nota='+id_nota+'&id_riesgo='+id_riesgo;
        _url+= '&id_proyecto='+id_proyecto+'&menu='+menu;
        
        var url= null;
        if (flag == 1) 
            url= '../form/ajax/fadd_tareas.ajax.php?signal=<?= $signal ?>' + _url;
        if (flag == 0) 
            url= '../form/ajax/fadd_auditorias.ajax.php?signal=fnota' +_url;

        var capa= 'div-ajax-panel';
        var metodo= 'GET';
        var valores= '';
        FAjax(url,capa,valores,metodo);
        
        displayWindow(flag);
    }

    function validar_main() {
        $("#control_page_origen").val(0);

        if (!validar()) {
            $("#id_tarea").val(0);
            return;
        }     
    }
    
    function add_tarea() {
        $("#control_page_origen").val("add");

        if (!validar()) {      
            return;
        }
    }
    
    function editar_tarea(id) {
        $("#control_page_origen").val("edit");
        $("#id_tarea").val(id);

        if (!validar()) {
            return;
        } 
    }
    
    function insert_task() {
        if ($('#cant_task').val() == 0) {
            confirm("No ha seleccionado las tareas. ¿Desea continuar?", function(ok) {
                if (!ok) 
                    return;
                else {
                    closeFloatingDiv('div-ajax-panel');
                    return;                            
                }
            });
        } else {
            _this();
        }

        function _this() {
            var url= '../php/<?= $signal ?>.interface.php?signal=f<?= $signal ?>&ajax_win=1';
            var metodo= 'POST';
            var capa= 'ajax-task-table';
            var valores= $("#fadd_tarea").serialize();

            FAjax(url,capa,valores, metodo);
            parent.app_menu_functions = false;                    
        }
    }

    function eliminar_tarea(id, flag) {
        var url = null;

        var id_riesgo = $('#id_riesgo').val();
        var id_nota = $('#id_nota').val();
        var id_proyecto = $('#id_proyecto').val();
        
        if (!flag) {
            url = '../php/<?= $signal ?>.interface.php';
            url += '?action=delete&signal=<?= $signal ?>&menu=tarea&id_tarea=' + id;
            <?php if ($signal == 'fproyecto' || $signal == 'proyecto') { ?>
                url += '&id='+id_proyecto+'&id_proyecto=' + id_proyecto;
            <?php } ?>
            <?php if ($signal == 'friesgo' || $signal == 'riesgo') { ?>
                url += '&id='+id_riesgo+'&id_riesgo=' + id_riesgo;
            <?php } ?>
            <?php if ($signal == 'fnota' || $signal == 'nota') { ?>
                url += '&id='+id_nota+'&id_nota=' + id_nota;
            <?php } ?>                       
        } else {
            url = '../php/tarea.interface.php?action=delete&signal=<?= $signal ?>&menu=tarea&id_tarea=' + id + '&id=' + id;
            <?php if ($signal == 'fproyecto' || $signal == 'proyecto') { ?>
                url += '&id_proyecto=' + id;
            <?php } ?>
            <?php if ($signal == 'friesgo' || $signal == 'riesgo') { ?>
                url += '&id_riesgo=' + id;
            <?php } ?>
            <?php if ($signal == 'fnota' || $signal == 'nota') { ?>
                url += '&id_nota=' + id;
            <?php } ?>                    
        }

        var metodo = 'GET';
        var capa = 'ajax-task-table';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);  
    }

    function build_date() {
        var year = $('#year').val();

        var fecha_inicio = year + '-' + $('#month_inicio').val() + '-' + $('#day_inicio').val();
        var fecha_fin = year + '-' + $('#month_fin').val() + '-' + $('#day_fin').val();

        $('#fecha_inicio').val(fecha_inicio);
        $('#fecha_fin').val(fecha_fin);

        if (parseInt($('#month_inicio').val()) > parseInt($('#month_fin').val())) {
            $('#month_inicio').focus(focusin($('#month_inicio')));
            alert("El mes de inicio de la gestión del riesgo no puede ser posterior al mes de inicio.");
            return false;
        } else {
            return true;
        }
    }
</script>
