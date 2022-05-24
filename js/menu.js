function isnArray() {
    var argnr = isnArray.arguments.length
    for (var i = 0; i < argnr; i++) {
        this[i + 1] = isnArray.arguments[i];
    }
}

var isnMonths = new isnArray("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

function muestraReloj(phpDate) {
    // Compruebo si se puede ejecutar el script en el navegador del usuario
    if (!document.layers && !document.all && !document.getElementById)
        return;
    // Obtengo la hora actual y la divido en sus partes
    var fechacompleta = new Date();
    if (phpDate != undefined)
        fechacompleta = phpDate.length > 0 ? new Date(phpDate) : new Date();

    var day = fechacompleta.getDay();
    var dday = fechacompleta.getDate();
    var month = fechacompleta.getMonth() + 1;
    var year = fechacompleta.getYear() + 1900;

    var horas = fechacompleta.getHours();
    var minutos = fechacompleta.getMinutes();
    var segundos = fechacompleta.getSeconds();
    var mt = "AM";
    // Pongo el formato 12 horas
    if (horas > 12) {
        mt = "PM";
        horas = horas - 12;
    }
    if (horas == 0) horas = 12;
    // Pongo minutos y segundos con dos dígitos
    if (minutos <= 9)
        minutos = "0" + minutos;
    if (segundos <= 9)
        segundos = "0" + segundos;
    // En la variable 'cadenareloj' puedes cambiar los colores y el tipo de fuente
    cadenareloj = isnMonths[month] + " " + dday + ", " + year + "  " + horas + ":" + minutos + ":" + segundos + " " + mt;
    // Escribo el reloj de una manera u otra, según el navegador del usuario
    if (document.layers) {
        document.layers.spanreloj.document.write(cadenareloj);
        document.layers.spanreloj.document.close();
    } else if (document.all)
        spanreloj.innerHTML = cadenareloj;
    else {
        if (document.getElementById)
            document.getElementById("spanreloj").innerHTML = cadenareloj;
    }
    // Ejecuto la función con un intervalo de un segundo
    setTimeout("muestraReloj(undefined)", 1000);
}
// Fin del script -->

function setTopMenuIcon() {
    if (isMobile() && window.parent) {
        window.parent.$('#btn-navbarSecondary').css('display', 'block');
        window.parent.$('#btn-navbarSecondary').addClass('d-inline-block');
    }

    if (window.parent) {
        if (isMobile()) {
            setMobilMenu();
        } else {
            setPcMenu();
        }
    }
}

function crea_escenario() {
    alert("No existe un escenario definido para trabajar.");
    initmenu();

    if ($('#nivel').val() > 3) {
        setTopMenuIcon();
        mainWinApp.src = '../form/fescenario.php?action=add';
    } else
        parent.location.href = '../index.php';
}

function OnClickLoad(submenu) {
    var year = $('#year').val();
    var month = $('#month').val();
    var _action = $('#exect').val();

    var page = 'background.php?csfr_token=123abc';

    if (submenu == 'exit')
        parent.location = '../php/exit.php';

    page = '../html/background.php?csfr_token=123abc&?action=' + _action;

    if ($('#id_escenario').val() > 0) {
        /*
        if (submenu == 5) 
            TableroLoad();
        else 
            parent.mainWinApp.src= page;
        */
    } else {
        setTopMenuIcon();
        crea_escenario();
    }
}

function sendpage(page) {
    var year = $('#year').val();
    var month = $('#month').val();
    page += '&year=' + year + '&month=' + month + '&csfr_token=123abc';

    setTopMenuIcon();
    try {
        if ($('#id_escenario').val() > 0)
            self.location.href = page;
        else
            crea_escenario();
    } catch (e) {
        self.location.href = page;
    }
}

function load_url(action) {
    var year = $('#year').val();
    var month = $('#month').val();
    action += '&year=' + year + '&month=' + month + '&csfr_token=123abc';

    if (typeof mainWinApp === 'undefined') {
        window.parent.location.href = action;
    }
    setTopMenuIcon();
    try {
        if ($('#id_escenario').val() > 0) {
            mainWinApp.src = action;
        } else
            crea_escenario();
    } catch (e) {
        mainWinApp.src = action;
    }
}

function TableroLoad() {
    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();

    if (month == -1) {
        mainWinApp.src = 'background.php?csfr_token=123abc&';
        alert('No ha especificado el mes. Por favor, seleccione el mes a mostrar');
        return;
    }
    if (day == -1 || day == 0) {
        mainWinApp.src = 'background.php?csfr_token=123abc&';
        alert('No ha especificado el día. Por favor, seleccione el día a mostrar');
        return;
    }

    setTopMenuIcon();
    if ($('#id_escenario').val() > 0)
        mainWinApp.src = 'tablero.php?tablero=0&year=' + year + '&month=' + month + '&day=' + day;
    else
        crea_escenario();
}

function update_sys() {
    setTopMenuIcon();
    mainWinApp.src = '../tools/dbtools/update.interface.php';
}

function upgrade_sys() {
    setTopMenuIcon();
    mainWinApp.src = '../tools/dbtools/update_by_mail.interface.php';
}

function backup_sys() {
    setTopMenuIcon();
    var src = '../tools/dbtools/gen_backup.interface.php?action=export&verbose=0&save=0';
    mainWinApp.src = src;
}

function clean_sys() {
    setTopMenuIcon();
    mainWinApp.src = '../tools/dbtools/clean.interface.php';
}

function export_sys() {
    mainWinApp.src = '../tools/lote/php/export.interface.php?signal=home&action=export';
}