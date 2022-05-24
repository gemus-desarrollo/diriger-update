/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function validar_causa() {
    var if_nota = $('#id_nota').val() == 'nota' ? true : false;
    var text;

    $('#causa').val(tinymce.get('causa').getContent());

    if (!Entrada($('#causa').val())) {
        $('#causa').focus(focusin($('#causa')));
        if (if_nota) {
            text = "Debe describir la causa de la No Conformidad u Observación, o la mejora de ";
            text += "tratarce de una Nota de Mejora .";
            alert(text);
        } else
            alert("Debe describir las condiciones que podrían generar la manifestación del riesgo");
        return false;
    }
    /*
    if (!Entrada($('#fecha_reg').val())) {
        $('#fecha_reg').focus(focusin($('#fecha_reg')));
        if (if_nota)
            text= "Debe especificar la fecha en la que se manifestó/manifestará la causa/mejora.";
        else { 
            text= "Debe especificar la fecha a partir de la cual se observarón las condiciones para ";
            text+= "la posible manifestación del riesgo.";
        }
        alert(text);
        $('#fecha_reg').empty();
        return false;
    }
    */

    if (if_nota && Entrada($('#fecha_reg').val())) {
        if (($('#tipo').val() == 1 || $('#tipo').val() == 2) &&
            DiferenciaFechas($('#fecha_reg').val(), $('#fecha_inicio').val(), 's') > 0) {
            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            text = "Existe incongruencia en las fechas. La fecha de manifestación de la causa no puede ser ";
            text += "superior a la fecha de detección de la No Conformidad u Observación.";
            alert(text);
            $('#fecha_reg').empty();
            return false;
        }

        if (($('#tipo').val() == 3) && DiferenciaFechas($('#fecha_reg').val(), $('#fecha_inicio').val(), 's') < 0) {
            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            text = "Existe incongruencia en las fechas. La fecha de manifestación de la mejora no puede ser ";
            text += "inferior a la fecha de detección de la Nota de Mejora.";
            alert(text);
            $('#fecha_reg').empty();
            return false;
        }
    }

    if (parseInt($('#id_causa').val()) == 0)
        ejecutar_causa(0, 'add');
    else
        ejecutar_causa($('#id_causa').val(), 'update');
}

function add_causa() {
    $('#id_causa').val(0);
    displayFloatingDiv('div-ajax-panel-causa-form', false, 70, 0, 5, 10);

    try {
        $('#causa').tinymce().destroy();
    } catch (e) {; }

    try {
        tinymce.init({
            selector: '#causa',
            theme: 'modern',
            height: 150,
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
    } catch (e) {; }

    $('#causa').empty();
    $('#fecha_reg').empty();
}

function edit_causa(id) {
    add_causa();
    $('#id_causa').val(id);

    tinymce.get('causa').setContent($('#causa_' + id).val());
    $('#fecha_reg').val($('#fecha_reg_' + id).val());
}

function delete_causa(id) {
    var text;
    var if_nota = $('#id_nota').val() == 'nota' ? true : false;

    if (if_nota)
        text = 'Realmente desea eliminar esta causa/mejora?';
    else
        text = 'Realmente desea eliminar esta condición para la posible manifestación del riesgo?';

    confirm(text, function(ok) {
        if (ok) {
            ejecutar_causa(id, 'delete');
        } else {
            return false;
        }
    });
}

function ejecutar_causa(id, action) {
    $('#_button').hide();
    $('#_button_active').show();

    var id_nota = $('#id_nota').val();
    var id_riesgo = $('#id_riesgo').val();
    var causa = encodeURIComponent($('#causa').val());
    var fecha_reg = Entrada($('#fecha_reg').val()) ? encodeURI($('#fecha_reg').val()) : '';

    var url = '../php/causa_register.interface.php?action=' + action + '&id_nota=' + id_nota;
    url += '&id_riesgo=' + id_riesgo + '&descripcion=' + causa + '&fecha_reg=' + fecha_reg;
    if (action == 'delete' || action == 'update')
        url += '&id_causa=' + id;

    FAjax(url, 'div-ajax-panel-causa-table', '', 'GET');
}

function refresh_ajax_causas() {
    var action = $('#exect').val();
    var id_nota = $('#id_nota').val();
    var id_riesgo = $('#id_riesgo').val();
    var fecha_final_real = id_nota ? $('#fecha_final_real').val() : $('#fecha_fin').val();
    var signal = $('#signal').val();

    var url = 'ajax/causa.ajax.php?signal=' + signal + '&id_nota=' + id_nota + '&id_riesgo=' + id_riesgo;
    url += '&fecha_final_real=' + encodeURI(fecha_final_real) + '&action=' + action;

    limpiar();
    HideContent('div-ajax-panel-causa-form');
    FAjax(url, 'div-ajax-panel-causa-table', '', 'GET');
}

function limpiar() {
    $('#id_causa').val(0);
    $('#causa').val('');
    $('#fecha_reg').val('');
    $('#btn_editar').hide();
    $('#btn_agregar').show();

    $('#_button').show();
    $('#_button_active').hide();
}