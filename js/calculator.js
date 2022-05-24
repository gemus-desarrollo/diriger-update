// JaxScript Document


var PI = Math.PI;
var E = Math.E;

function SIN(x) {
    var c = Math.sin(x);
    return c;
}

function COS(x) {
    var c = Math.cos(x);
    return c;
}

function TAN(x) {
    var c = Math.tan(x);
    return c;
}

function ASIN(x) {
    var c = Math.asin(x);
    return c;
}

function ACOS(x) {
    var c = Math.acos(x);
    return c;
}

function ATAN(x) {
    var c = Math.atan(x);
    return c;
}

function SQRT(x, y) {
    var c = Math.pow(x, 1 / y);
    return c;
}

function SQRT2(x) {
    var c = Math.sqrt(x);
    return c;
}

function POT2(x) {
    var c = Math.pow(x, 2);
    return c;
}

function POT(x, y) {
    var c = Math.pow(x, y);
    return c;
}

function EXP(x) {
    var c = Math.exp(x);
    return c;
}

function LOG(x) {
    var c = Math.log(x) / Math.log(10);
    return c;
}

function LN(x) {
    var c = Math.log(x);
    return c;
}

function ALOG(x) {
    var c = Math.pow(10, x);
    return c;
}

function ALN(x) {
    var c = Math.pow(E, x);
    return c;
}

function FACTORIAL(x) {
    if (x == 0) return 1;
    var c = FACTORIAL(x - 1);
    return c;
}

function INV(x) {
    var c = 1 / x;
    return c;
}

function SIGNO(x) {
    var c = (-1) * x;
    return c;
}

function PERCENT(x, y) {
    var c = (x * y) / 100;
    return c;
}

function cMAX() {

}

function cMIN() {

}

var array_indicadores = new Array();
var selected_id_code = null;

function teclaPulsada(tecla) {
    var key = $('#editor').val();
    key += tecla;
    $('#editor').val(key);

    tecla = selected_indi ? "_" + selected_id_code : tecla;
    var calculo = $('#calculo').val();
    calculo += tecla;
    $('#calculo').val(calculo);
}

var selected_indi = false;
var using_indicadores = false;

var array_id_code = new Array();
var array_nombre = new Array();

function select_indi() {
    var i = $("#indicador").val();
    if (i == 0) {
        selected_indi = false;
        return;
    }
    selected_indi = true;
    selected_indi = "'" + array_nombre[i] + "'";
    selected_id_code = array_id_code[i];
}

function add_indicador() {
    if (!selected_indi) {
        $('#indicador').focus(focusin($('#indicador')));
        alert("Debe selecionar un Indicador para insertar en la Formula");
        return;
    }

    using_indicadores = true;
    teclaPulsada(selected_indi);
    array_indicadores.push(selected_id_code);

    selected_indi = false;
    selected_id_code = null;
    CloseWindow('dimmingdivGrey');
}

function purgehtml(html) {
    var v = html
        //alert(v);
    v = v.replace(/<span class="apple-style-span">(.*)<\/span>/gi, '$1');
    v = v.replace(/ class="apple-style-span"/gi, '');
    v = v.replace(/<span style="">/gi, '');
    v = v.replace(/<br>/gi, '<br />');
    v = v.replace(/<br ?\/?>$/gi, '');
    v = v.replace(/^<br ?\/?>/gi, '');
    v = v.replace(/<br ?\/?>/gi, '');
    v = v.replace(/(<img [^>]+[^\/])>/gi, '$1 />');
    v = v.replace(/<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '$1');
    v = v.replace(/<i\b[^>]*>(.*?)<\/i[^>]*>/gi, '$1');
    v = v.replace(/<u\b[^>]*>(.*?)<\/u[^>]*>/gi, '$1');
    v = v.replace(/<(b|strong|em|i|u) style="font-weight: normal;?">(.*)<\/(b|strong|em|i|u)>/gi, '$2');
    v = v.replace(/<(b|strong|em|i|u) style="(.*)">(.*)<\/(b|strong|em|i|u)>/gi, '$2');
    v = v.replace(/<span style="font-weight: normal;?">(.*)<\/span>/gi, '$1');
    v = v.replace(/<span style="font-weight: bold;?">(.*)<\/span>/gi, '$1');
    v = v.replace(/<span style="font-style: italic;?">(.*)<\/span>/gi, '$1');
    v = v.replace(/<span style="font-weight: bold;?">(.*)<\/span>|<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '$1')
        //alert(v);	
    return v;
}

function calcular() { //Al preionar el btn =
    if (array_indicadores.length <= 0 || array_indicadores == 'undefined') {
        var text = "Esta fórmula contienen indicadores, los cuales solo pueden ser calculados desde las funcionalidades del Sistema";
        text += " que lo requieran. Diriger asociará esta fórmula al indicador que está configurando. ¿Desea continuar?";
        confirm(text, ok);
        if (ok)
            add_formula();
        else
            return;
    }

    try {
        var formula = $('#editor').val();
        $('#editor').val(formula);
        formula = purgehtml(formula);

        var value = eval(formula.toUpperCase());
        $('#result').html(value);
    } catch (ex) {
        $('#result').html(ex.message);
    }
}

function cls() {
    using_indicadores = false;
    array_indicadores = [];

    $('#calculo').val(null);
    $('#editor').val(null);
    $('#tinyeditor').val(null);
    $('#result').html(null);
}

function backspace() {
    var str = $('#editor').val();
    var len = str.length;
    str = len > 0 ? str.substring(0, len - 1) : '';

    $('#editor').val(str);
    $('#calculo').val(null);
    $('#tinyeditor').val(null);
}

function init() {
    /*
    $('#editor').val(null);
    $('#calculo').val(null);
    $('#result').html(null);
    */
    using_indicadores = array_indicadores.length ? true : false;
}