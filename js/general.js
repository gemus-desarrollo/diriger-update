// JavaScript Document
parent.app_menu_functions = true;

var monthValues = new Array(7);
monthValues['D'] = 1;
monthValues['S'] = 1;
monthValues['Q'] = 1;
monthValues['M'] = 1;
monthValues['T'] = 3;
monthValues['E'] = 6;
monthValues['A'] = 12;

var _PLAN_TIPO_PREVENCION = 1;
var _PLAN_TIPO_AUDITORIA = 2;
var _PLAN_TIPO_SUPERVICION = 3;
var _PLAN_TIPO_ACCION = 4;
var _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL = 5;
var _PLAN_TIPO_ACTIVIDADES_MENSUAL = 6;
var _PLAN_TIPO_ACTIVIDADES_ANUAL = 7;
var _PLAN_TIPO_MEDIDAS = 8;
var _PLAN_TIPO_MEETING = 9;
var _PLAN_TIPO_INFORMATIVO = 10;
var _PLAN_TIPO_PROYECTO = 11;

var monthNames = Array('undef', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');


function enviar(id, action) {
    function _this() {
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/interface.php?id=' + id;
        document.forms[0].submit();
    }

    var item = $('#menu').val();

    switch ($('#menu').val()) {
        case 'tipo_evento':
            item = 'clasificación de actividad';
            break;
        case 'tipo_lista':
            item = 'componente de guia de control interno';
            break;
    }

    var msg = "El elemento " + item + " será eliminado. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}


function enviarTablero(id, action) {
    function _this() {
        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/tablero.interface.php?id=' + id;
        document.forms[0].submit();
    }

    var msg = "El elemento " + $('#menu').val() + " será eliminado. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}

function enviar_indicador(id, action, signal) {
    function _this() {
        var year = $('#year').val();
        var month = $('#month').val();
        var day = $('#day').val();
        var menu = $('#menu').val();

        var url = '?id=' + id + '&action=' + action + '&year=' + year + '&month=' + month + '&day=' + day;
        url += '&menu=' + menu + '&signal=' + signal;

        parent.app_menu_functions = false;
        self.location.href = '../php/indicador.interface.php' + url;
    }

    var msg = "El el indicador será eliminado y con el se perderán todos sus registros para el actual escenario. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}

function enviar_proceso(id, action) {
    function _this() {
        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/proceso.interface.php?id=' + id;
        document.forms[0].submit();
    }

    var msg = "El el proceso será eliminado y a igual que toda referencia a su gestión y demas trazabilidad. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}

function enviar_riesgo(id, action) {
    function _this() {
        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/riesgo.interface.php?id=' + id;
        document.forms[0].submit();
    }

    var msg = "El el riesgo será eliminado y a igual que toda referencia a su gestión y demas trazabilidad. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}

function enviar_nota(id, action) {
    function _this() {
        parent.app_menu_functions = false;
        document.forms[0].exect.value = action;
        document.forms[0].action = '../php/nota.interface.php?id=' + id;
        document.forms[0].submit();
    }

    var msg = "La nota será eliminada al igual que toda referencia a su gestión y demas trazabilidad. Desea continuar?";

    if (action == 'delete') {
        confirm(msg, function(ok) {
            if (!ok)
                return;
            else
                _this();
        });
    } else {
        _this();
    }
}

function validar_login(form, val_clave) {
    var text;

    if (Entrada(form.usuario.value) == false) {
        alert("Ingrese el nombre de usuario para acceder al sistema.");
        form.usuario.focus();
        return false;
    }
    if (HaveLetter(form.usuario.value) == false) {
        alert("Nombre de usuario no permitido. Asegúrese de escribirlo correctamente.");
        form.usuario.focus();
        return false;
    }
    if (form.usuario.value.length < 2) {
        alert("La identificación del usuario debe tener al menos 4 caracteres.");
        form.usuario.focus();
        return false;
    }

    if (Entrada(form.clave.value) == false) {
        alert("Ingrese su contraseña de acceso.");
        form.clave.focus();
        return false;
    }
    if (form.clave.value.length < 5) {
        alert("Contraseña muy corta. Debe contener al menos 5 caracteres.");
        form.clave.focus();
        return false;
    }

    if (val_clave) {
        if (!valClave(form.clave.value)) {
            text = "Esta contraseña no cumple con los requisitos para la seguridad informática. ";
            text += "La contraseña deberá contener letras en mayuscula, letras en minusculas, número y-o y caracteres especiales.";
            alert(text);
            return false;
        }
    }

    return true;
}

// validacion de email 
function valEmail(valor) {
    var mailres = true;
    var cadena = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ1234567890@._-";

    var arroba = valor.indexOf("@", 0);
    if ((valor.lastIndexOf("@")) != arroba)
        arroba = -1;

    var punto = valor.lastIndexOf(".");

    for (var contador = 0; contador < valor.length; contador++) {
        if (cadena.indexOf(valor.substr(contador, 1), 0) == -1) {
            mailres = false;
            break;
        }
    }

    if ((arroba > 1) && (arroba + 1 < punto) && (punto + 1 < (valor.length)) && (mailres == true) && (valor.indexOf("..", 0) == -1))
        mailres = true;
    else
        mailres = false;

    return mailres;
}


function valClave(valor) {
    var re_A = new RegExp("[A-Z]");
    var re_a = new RegExp("[a-z]");
    var re_n = new RegExp("[0-9]");
    var re_c = new RegExp("[!@$#%\^\*()_\(-)\+=<>:.;?\/]");

    var i = 4;
    /*
        if (!re_A.test(valor)) {alert("La clave o contraseña debe contener al menos un carácter en Mayuscula."); return false;}
        if (!re_a.test(valor)) {alert("La clave o contraseña debe contener al menos un carácter en minuscula."); return false;}			
        if (!re_n.test(valor) && !re_c.test(valor)) {alert("La clave o contraseña debe contener al menos un número o carácter especial."); return false;}
    */
    if (!re_A.test(valor))
        --i;
    if (!re_a.test(valor))
        --i;
    if (!re_n.test(valor))
        --i;
    if (!re_c.test(valor))
        --i;

    if (i < 3)
        return false;
    else
        return true;
}


function open_help_window(url) {
    help_window = open(url, 'AYUDA DE Diriger', 'scrollbars=yes, width=880, height=640');
}

function box_alarm(msg) {
    var html = "<label class='alert alert-danger'><div style=''><img src='../img/Warning.png' /></div><p>";
    html += msg;
    html += "</p></label>";
    document.write(html);
}

function div_alarm(msg) {
    var html = "<table width='100%' border=0><tr><td><img src='../img/Warning.png' /></td><td><p>";
    html += msg;
    html += "</p></td></tr></table>";
    $('#div-msg').innerHTML = html;
}

function reduce_image(image, ratio) {
    var width = document.getElementById(image).style.width;
    var height = document.getElementById(image).style.height;

    document.getElementById(image).style.width = (width * ratio) + 'px';
    document.getElementById(image).style.height = (height * ratio) + 'px';
}

function show_imprimir(url, title, param) {
    var wmail;

    if (window.showModalDialog)
        wmail = window.showModalDialog(url, title, param);
    else
        wmail = document.open(url, "_blank", "dependent,modal," + param);
    //   document.open(url, "_blank", "width=600,height=300,toolbar=no,location=0, menubar=0, titlebar=0, scrollbars=yes");
    //   document.open(url, "_blank", param);

    return wmail;
}

function graficar(_item, id) {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var signal = $('#signal').val();

    var url = "";
    var _url = "";
    var formulated;
    var cumulative;
    var month;
    var day;

    try {
        formulated = $('#_radio_formulated').val();
    } catch (e) {
        formulated = 0;
    }
    try {
        cumulative = $('#_radio_cumulative').val();
    } catch (e) {
        cumulative = 0;
    }
    try {
        month = $('#month').val();
    } catch (e) {
        month = 1;
    }
    try {
        day = $('#day').val();
    } catch (e) {
        day = 0;
    }

    if (_item == "indicador") {
        _url = parseInt(formulated) == 1 ? "fgraph_formulated" : "fgraph";
        month = parseInt(formulated) == 1 ? month : 1;
        url = "../form/" + _url + ".php?id_indicador=" + id + '&year=' + year + '&month=' + month;
        url += '&periodicidad=&inicio=' + year + '&fin=' + year + '&signal=' + signal + '&radio_cumulative=' + cumulative;
        if (day)
            url += '&day=' + day;
    } else {
        url = "../form/fgraph.php?item=" + _item + '&id=' + id + '&year=' + year;
        url += '&month=1' + '&id_proceso=' + id_proceso + '&periodicidad=M&signal=' + _item + '&signal=' + signal;
    }
    document.open(url, "_blank", "width=620,height=400,toolbar=no,location=0, menubar=0, titlebar=0, scrollbars=yes");
}

function ifblock_app_menu() {
    if (!parent.app_menu_functions) {
        alert("Por favor espere, la operación que se está ejecutando puede tardar varios minutos ...................");
        return true;
    }

    return false;
}

var _SERVER_DIRIGER;

function progressbar(id, text, x) {
    $.get(_SERVER_DIRIGER + 'form/ajax/bar.ajax.php?x=' + x, function(data) {
        /* update the progress bar width */
        $('#progressbar-' + id + '.progress-block > div > .progressbar').css('width', data + '%');
        /* and display the numeric value */
        $('#progressbar-' + id + '.progress-block > div.alert').html(text + ' ....' + data + '%');

        /* test to see if the job has completed */
        //     console.log(data);
        if (parseFloat(data) > 99.999) {
            $('#progressbar-' + id + ' > div.progress').removeClass('progress-striped');
            $('#progressbar-' + id + ' > div.progress').removeClass('active');
            $('#progressbar-' + id + '.progress-block > div.alert').html(text + " Terminado");
        }
    });
}

function progressbarCSS(id, text, x) {
    document.getElementById('progressbar-' + id + '-').className = "progress progress-striped active";
    document.getElementById('progressbar-' + id + '-bar').style.width = x + '%';
    /* and display the numeric value */

    document.getElementById('progressbar-' + id + '-alert').innerHTML = text + ' ....' + x;
    if (parseFloat(x) > 99.999) {
        document.getElementById('progressbar-' + id + '-').className = "progress";
        document.getElementById('progressbar-' + id + '-alert').innerHTML = text + " Terminado";
    }
}