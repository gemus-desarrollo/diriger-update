// JavaScript Documen
function FAjax_upload(form_name, _url, capa) {
    _url += '&csfr_token=123abc';
 
    var formData = new FormData();
    var files = $('#file_doc-upload')[0].files[0];
    formData.append('file_doc-upload', files);

    formData.append('id_requisito', $('#id_requisito_doc').val());
    formData.append('id_evento', $('#id_evento_doc').val());
    formData.append('id_proceso', $('#id_proceso_doc').val());
    formData.append('id_proyecto', $('#id_proyecto_doc').val());
    formData.append('id_tarea', $('#id_tarea_doc').val());
    formData.append('id_auditoria', $('#id_auditoria_doc').val());
    formData.append('id_riesgo', $('#id_riesgo_doc').val());
    formData.append('id_nota', $('#id_nota_doc').val());
    formData.append('id_politica', $('#id_politica_doc').val());
    formData.append('id_indicador', $('#id_indicador_doc').val());

    formData.append('menu', $('#menu_doc').val());
    formData.append('usuario_doc', $('#usuario_doc').val());
    formData.append('keywords_doc', $('#keywords_doc').val());
    formData.append('descripcion_doc', $('#descripcion_doc').val());

    $.ajax({
        url: _url,
        type: 'POST',
        data: formData,

        cache: false,
        contentType: false,
        processData: false,
        evalScripts: true,

        beforeSend: function() {
            $("#" + capa).html("<div class='loading-indicator'>Procesando, espere por favor...</div>");
        },
        success: function(response) {
            $("#" + capa).html(response);
        },
        error: function(xhr, status) {
            alert('Disculpe, existió un problema en la conexión AJAX');
        }
    });
}

function FAjax(_url, capa, valores, metodo, funct) {
    _url += '&csfr_token=123abc';

    $.ajax({
        data: valores,
        url: _url,
        method: metodo,

        cache: false,
        processData: false,
        evalScripts: true,

        beforeSend: function() {
            $("#" + capa).html("<div class='loading-indicator'>Procesando, espere por favor...</div>");
        },
        /*
        success: function(response) {
            var scs= response.extractScript();   //capturamos los scripts
            $("#"+capa).html(response.stripScript());   //eliminamos los scripts... ya son innecesarios
            scs.evalScript();   //ahora si, comenzamos a interpretar todo
        },
        */
        success: function(response) {
            $("#" + capa).html(response);
            if ((funct != undefined && funct != 'undefined') && funct.length > 0)
                eval(funct);
        },
        error: function(xhr, status) {
            alert('Disculpe, existió un problema en la conexión AJAX');
        }
    });
}

function Cajax(_url, capa, metodo) {
    _url += '&csfr_token=123abc';
    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var scs = xmlhttp.responseText.extractScript();
            document.getElementById(capa).innerHTML = xmlhttp.responseText;
            scs.evalScript();
        }
    }
    xmlhttp.open(metodo, _url, true);
    xmlhttp.send();
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// INTERPRETADOR DE JAVASCRIPT CARGADOS CON AJAX
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
var tagScript = '(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)';
/**
 * Eval script fragment
 * @return String
 */
String.prototype.evalScript = function() {
    return (this.match(new RegExp(tagScript, 'img')) || []).evalScript();
};
/**
 * strip script fragment
 * @return String
 */
String.prototype.stripScript = function() {
    return this.replace(new RegExp(tagScript, 'img'), '');
};
/**
 * extract script fragment
 * @return String
 */
String.prototype.extractScript = function() {
    var matchAll = new RegExp(tagScript, 'img');
    return (this.match(matchAll) || []);
};
/**
 * Eval scripts
 * @return String
 */
/*
Array.prototype.evalScript = function(extracted) {
    var s = this.map(function(sr) {
        var sc = (sr.match(new RegExp(tagScript, 'im')) || ['', ''])[1];
        console.log(sc);
        if (window.execScript) {
            window.execScript(sc);
        } else {
            window.setTimeout(sc, 0);
        }
    });
    return true;
};
*/
/**
 * Map array elements
 * @param {Function} fun
 * @return Function
 */
/*
Array.prototype.map = function(fun) {
    if (typeof fun !== "function") { 
        return false; 
    }
    var i = 0,
        l = this.length;
    for (i = 0; i < l; i++) {
        fun(this[i]);
    }
    return true;
};
*/