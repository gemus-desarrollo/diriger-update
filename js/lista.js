/*
 * Lista de chequeo
 */

function set_url() {
    var year;
    try {
        year = $('#_year').val();
    } catch (e) {
        year = $('#year').val();
    }

    if (year == 'undefined' || year == undefined)
        year = $('#year').val();

    var action = $('#exect').val();
    var id_lista = $('#id_lista').val();
    var id_proceso = $('#id_proceso').val();

    var componente = $('#id_componente').val();
    var id_capitulo = $('#id_capitulo').val();
    var id_subcapitulo = $('#id_subcapitulo').val();
    var id_tipo_lista = id_subcapitulo ? id_subcapitulo : 0;

    var id_auditoria;
    try {
        id_auditoria = $('#id_auditoria').val();
    } catch (e) {
        id_auditoria = 0;
    }

    var inicio = $('#inicio').val();
    var fin = $('#fin').val();
    var if_jefe = $('#if_jefe').val();

    var url = '?action=' + action + '&signal=list&if_jefe=' + if_jefe + '&componente=' + componente + '&year=' + year;
    url += '&id_tipo_lista=' + id_tipo_lista + '&id_lista=' + id_lista + '&id_proceso=' + id_proceso;
    url += '&id_capitulo=' + id_capitulo + '&id_subcapitulo=' + id_subcapitulo +
        '&_inicio=' + inicio + '&_fin=' + fin + '&id_auditoria=' + id_auditoria;

    return url
}

function refresh_ajax_select(panel, id, numero) {
    var componente = $('#componente' + panel).val();
    var id_lista = $('#id_lista').val();
    var year = $('#year' + panel).val();
    var id_capitulo = $('#capitulo' + panel).val();
    var id_tipo_lista = $('#id_tipo_lista').val();

    if (id_capitulo == undefined || id_capitulo == 'undefined')
        id_capitulol = 0;

    var _url = '../form/ajax/select_tipo_lista.ajax.php?componente=' + componente + '&id_lista=' + id_lista;
    _url += '&id=' + id + '&numero=' + numero + '&year=' + year + '&panel=' + panel + '&id_capuitulo = 0';
    _url += '&id_tipo_lista=' + id_tipo_lista;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        async: false,
        beforeSend: function() {
            $("#ajax-capitulo").html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-capitulo" + panel).html(response);
            if (id_capitulo > 0)
                refresh_ajax_select_capitulo(panel, 0, numero);
            else {
                refresh_ajax_select_capitulo(panel, -1, 0);
            }
        }
    });
}

function refresh_ajax_select_capitulo(panel, id, numero) {
    var componente = $('#componente' + panel).val();
    var id_lista = $('#id_lista').val();
    var year = $('#year' + panel).val();
    var id_capitulo = $('#capitulo' + panel).val();
    var id_tipo_lista = $('#id_tipo_lista').val();

    if (id_capitulo == undefined || id_capitulo == 'undefined')
        id_capitulol = -1;
    if (id_capitulo == 0 || id_capitulo == -1) {
        id = -1;
        id_capitulo = -1;
    }

    var _url = '../form/ajax/select_tipo_lista.ajax.php?componente=' + componente + '&id_lista=' + id_lista;
    _url += '&id_capitulo=' + id_capitulo + '&numero=' + numero + '&year=' + year + '&panel=' + panel + '&id=' + id;
    _url += '&tipo=cap' + '&id_tipo_lista=' + id_tipo_lista;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        async: false,
        beforeSend: function() {
            $("#ajax-subcapitulo" + panel).html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-subcapitulo" + panel).html(response);
        }
    });
}

function refresh_ajax_select_subcapitulo(panel, id, numero) {
    var componente = $('#componente' + panel).val();
    var id_lista = $('#id_lista').val();
    var year = $('#year' + panel).val();
    var id_capitulo = $('#capitulo' + panel).val();
    var id_subcapitulo = $('#subcapitulo').val();
    var id_tipo_lista = $('#id_tipo_lista').val();

    if ((id_capitulo == undefined || id_capitulo == 'undefined') || (id_capitulo == 0 || id_capitulo == -1)) {
        id = -1;
        id_capitulo = -1;
    }
    if (id_subcapitulo >= 0) {
        id = id_subcapitulo;
    }

    var _url = '../form/ajax/select_tipo_lista.ajax.php?componente=' + componente + '&id_lista=' + id_lista;
    _url += '&id_capitulo=' + id_capitulo + '&numero=' + numero + '&year=' + year + '&panel=' + panel + '&id=' + id;
    _url += '&tipo=epi' + '&id_tipo_lista=' + id_tipo_lista;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        async: false,
        beforeSend: function() {
            $("#ajax-numero" + panel).html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-numero" + panel).html(response);
        }
    });
}

function setNumero(n) {
    $('#numero').val(n);
}

function form_requisito(action, id) {
    displayFloatingDiv('div-panel-requisito', '', 80, 0, 5, 5);

    var inicio = $("#inicio").val();
    var fin = $("#fin").val();
    var id_lista = $("#id_lista").val();

    var url = "../form/ajax/flista_requisito.ajax.php?id=" + id + '&action=' + action + '&inicio=' + inicio + '&fin=' + fin;
    url += '&id_lista=' + id_lista;

    $.ajax({
        //   data:  parametros,
        url: url,
        type: 'get',
        beforeSend: function() {
            $("#ajax-requisito").html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-requisito").html(response);
        }
    });
}

function ejecutar(action, id) {
    var url = '../php/lista_requisito.interface.php?action=' + action + '&id=' + id;
    var capa = 'ajax-requisito';
    var metodo = 'POST';
    var valores = $("#formListaRequisito").serialize();
    var funct= '';

    FAjax(url, capa, valores, metodo, funct);
}