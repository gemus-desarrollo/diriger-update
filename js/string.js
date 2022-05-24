// JavaScript Document
function text_parse(text) {
    text = text.replace('"', "");
    return text;
}

//Chequear cuando la entrada est� vac�a
function EstaVacio(s) {
    empty = false;
    if ((s == null) || (s.length == 0))
        empty = true;
    return empty;
}

function HayEspacio(s) {
    var espacio = "\t\n\r ";

    var i;
    if (EstaVacio(s))
        return true;

    // buscar a traves de lo caracteres hasta encontrar un espacio no blanco.
    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (espacio.indexOf(c) == -1)
            return false;
    }
    return true;
}

function Entrada(val) {
    var strInput = new String(val);

    if (HayEspacio(strInput))
        return false;
    else
        return true;
}

//validar que todos los caracteres sean letras 
function IsLetters(str) {
    var especiales = "\t\n\r. áéíóúñÁÉÍÓÚÑ";

    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str + "" == "null" || str + "" == "")
        return false;

    var isValid = true;

    // convert to a string for performing string comparisons.
    str += "";

    // Loop through length of string and test for any letters characters
    for (i = 0; i < str.length; i++) {
        // Letters must be between "A"-"Z", or "a"-"z"
        if (!(((str.charAt(i) >= "a" && str.charAt(i) <= "z") || (str.charAt(i) >= "A" && str.charAt(i) <= "Z")) ||
                especiales.indexOf(str.charAt(i)) != -1)) {
            isValid = false;
            break;
        }
    } // END for

    return isValid;
} // end Letters

//validar que todos los caracteres sean numeros
function IsNumeric(str) {
    var espacio = "\t\n\r-. ";

    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str + "" == "null" || str + "" == "")
        return false;
    var isValid = true;

    array = str.split(".");
    if (array.length > 2)
        return false;

    // convert to a string for performing string comparisons.
    str += "";
    // Loop through length of string and test for any numeric characters
    for (i = 0; i < str.length; i++) {
        // Alphanumeric must be between "0"-"9"
        if (!((str.charAt(i) >= "0" && str.charAt(i) <= "9") || espacio.indexOf(str.charAt(i)) != -1)) {
            isValid = false;
            break;
        }
    } // END for

    return isValid;
} // end IsAlphaNum

//validar que solo sean letras OR numeros o ambos a la vez
function IsAlphaNumeric(str) {
    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str + "" == "null" || str + "" == "")
        return false;

    var espacio = "\t\n\r\\/# ";
    var especiales = "\t\n\r\\,. áéíóúñÁÉÍÓÚÑ";

    var isValid = true;

    // convert to a string for performing string comparisons.
    str += "";
    // Loop through length of string and test for any numeric characters
    for (i = 0; i < str.length; i++) {
        // Alphanumeric must be between "0"-"9" "A"-"Z"
        if (!((str.charAt(i) >= "0" && str.charAt(i) <= "9") || (str.charAt(i) >= "A" && str.charAt(i) <= "Z") ||
                (str.charAt(i) >= "a" && str.charAt(i) <= "z") ||
                espacio.indexOf(str.charAt(i)) != -1 || especiales.indexOf(str.charAt(i)) != -1)) {
            isValid = false;
            break;
        }
    } // END for

    return isValid;
} // end IsAlphaNum

//validar que la cadena contenga al menos una letra 
function HaveLetter(str) {
    var espacio = "\t\n\r ";
    var especiales = "-.,áéíóúñÁÉÍÓÚÑ";

    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str + "" == "null" || str + "" == "")
        return false;
    var isValid = false;

    // convert to a string for performing string comparisons.
    str += "";
    // Loop through length of string and test for any characters
    for (i = 0; i < str.length; i++) {
        // Alphanumeric must be between "A"-"Z"
        if ((str.charAt(i) >= "A" && str.charAt(i) <= "Z") || (str.charAt(i) >= "a" && str.charAt(i) <= "z") ||
            especiales.indexOf(str.charAt(i) != -1)) {
            isValid = true;
            break;
        }
    } // END for

    return isValid;
} // end IsAlphaNum

function HayEspacioInterior(s) {
    var i;
    if (EstaVacio(s))
        return true;

    var espacio = "\t\n\r\\/# ";

    // buscar a traves de lo caracteres hasta encontrar un espacio no blanco.
    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (espacio.indexOf(c) != -1)
            return true;
    }

    return false;
}

//Remueve los espacios de la izquierda
function TrimLeft(str) {
    var resultStr = "";
    var len = 0;
    var i = 0;
    var j = 0;

    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str == null)
        return null;
    // Make sure the argument is a string
    str += "";
    if (str.length == 0)
        resultStr = "";
    else {
        // Loop through string starting at the beginning as long as there are spaces.
        // len = str.length - 1;
        len = str.length;

        while (parseInt(j) <= parseInt(len)) {
            ++j;
            if (str.charAt(i) == " ")
                i++;
            if (len - i >= 6 && str.substr(i, 6) == '&nbsp;')
                i = i + 6;
        }

        // When the loop is done, we're sitting at the first non-space char, so return that 
        // char plus the remaining chars of the string.
        resultStr = i < len ? str.substring(i, len) : '';
    }

    return resultStr;
}

//Remueve los espacios de la derecha
function TrimRight(str) {
    var resultStr = "";
    var i = 0,
        j = 0;

    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str == null)
        return null;
    // Make sure the argument is a string
    str += "";

    if (str.length == 0)
        resultStr = "";
    else {
        // Loop through string starting at the end as long as there are spaces.
        i = str.length;
        j = str.length;

        while (parseInt(j) > 0) {
            --j;
            if (str.charAt(i) == " ")
                i--;
            if (i >= 6 && str.substr(i - 6, 6) == '&nbsp;')
                i = i - 6;
        }

        // When the loop is done, we're sitting at the last non-space char, so return that char plus
        // all previous chars of the string.
        resultStr = str.substring(0, i);
    }

    return resultStr;
}

// elimina los espacion en blanco a los lados de la cadena
function trim_str(str) {
    var resultStr = "";

    resultStr = TrimLeft(str);
    resultStr = TrimRight(resultStr);

    return resultStr;
}

//validar que solo sean letras OR numeros o ambos a la vez, sin permitir ningun otro caracter
function AlphaNumeric_abs(str) {
    // Return immediately if an invalid value was passed in
    if (str + "" == "undefined" || str + "" == "null" || str + "" == "")
        return false;
    var isValid = true;

    // convert to a string for performing string comparisons.
    str += "";
    // Loop through length of string and test for any numeric characters
    for (i = 0; i < str.length; i++) {
        // Alphanumeric must be between "0"-"9" "A"-"Z"
        if (!((str.charAt(i) >= "0" && str.charAt(i) <= "9") || (str.charAt(i) >= "A" && str.charAt(i) <= "Z") ||
                (str.charAt(i) >= "a" && str.charAt(i) <= "z"))) {
            isValid = false;
            break;
        }
    } // END for

    return isValid;
} // end IsAlphaNum

//comprueba si se trata de una fecha en el formato d/m/yyyyy
function isDate_d_m_yyyyy(date) {
    if (!Entrada(date)) return false;

    if ((i = date.indexOf("/")) == -1)
        return false;
    d = date.substring(0, i);
    if (!IsNumeric(d))
        return false;

    str = date.substring(++i, date.length);

    if ((i = str.indexOf("/")) == -1)
        return false;
    m = str.substring(0, i);

    if (!IsNumeric(m))
        return false;

    str = str.substring(++i, str.length);
    y = str.substring(0, 4);

    if (!IsNumeric(y))
        return false;

    return true;
} // end isDate_d_m_yyyyy