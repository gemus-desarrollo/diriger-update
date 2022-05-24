function refresh_ajax_select(id, numero, id_subcapitulo) {
    var flag = '';
    try {
        flag = $('#tipo_actividad_flag').val();
    } catch (e) {
        flag = '';
    }

    var empresarial = $('#tipo_actividad' + flag + '1').val();
    var year = $('#year').val();
    var id_proceso = $('#id_proceso').val();
    var action= $('#exect').val();

    try {
        if (id == 0) {
            id = $('#tipo_actividad' + flag + '2').val();
            refresh_ajax_select_subcapitulo(0, 0);
        }
    } catch (e) {}

    var _url = '../form/ajax/select_tipo_evento.ajax.php?empresarial=' + empresarial + '&id=' + id;
    _url += '&numero=' + numero + '&year=' + year + '&id_proceso=' + id_proceso + '&tipo_actividad_flag=' + flag;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        beforeSend: function() {
            $("#ajax-tipo-evento" + flag).html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-tipo-evento" + flag).html(response);
            if (id_subcapitulo > 0)
                refresh_ajax_select_subcapitulo(id_subcapitulo, numero);
            else {
                refresh_ajax_select_subcapitulo(0, action == 'add' ? 0 : numero);
            }
        }
    });
}

function refresh_ajax_select_subcapitulo(id, numero) {
    var flag = '';
    try {
        flag = $('#tipo_actividad_flag').val();
    } catch (e) {
        flag = '';
    }

    var empresarial = $('#tipo_actividad' + flag + '1').val();
    var year = $('#year').val();
    var id_proceso = $('#id_proceso').val();
    var id_subcapitulo = -1;

    try {
        id_subcapitulo = $('#tipo_actividad' + flag + '2').val();
        if (id_subcapitulo == 0)
            id_subcapitulo = -1;
    } catch (e) {
        id_subcapitulo = -1;
    }

    try {
        if (id == 0)
            id = $('#tipo_actividad' + flag + '3').val();
    } catch (e) {}

    var _url = '../form/ajax/select_tipo_evento.ajax.php?empresarial=' + empresarial + '&id_subcapitulo=' + id_subcapitulo;
    _url += '&id=' + id + '&numero=' + numero + '&year=' + year + '&id_proceso=' + id_proceso + '&tipo_actividad_flag=' + flag;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        beforeSend: function() {
            $("#ajax-subtipo-evento" + flag).html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-subtipo-evento" + flag).html(response);
        }
    });
}

function setNumero(n) {
    $("#numero").val(n);
}

function validate_numero_plus(numero_plus) {
    var strNum= "0123456789.";
    var isValid= true;
    var ipunto= 0;

    for (var i = 0; i < numero_plus.length; i++) {
        if (numero_plus.charAt(i) == '.')
            ++ipunto;
        if (ipunto > 3) {
            alert("Solo se permiten dos niveles en el campo Ampliar número.");
            isValid= false;
            break;
        }    
        if (strNum.indexOf(numero_plus.charAt(i)) == -1) {
            alert("El campo para Ampliar número solo admite digitos y puntos. Carácter no valido.");
            isValid = false;
            break;
        }
    }
    return isValid;
}
