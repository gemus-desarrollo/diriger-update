// JavaScript Document

var _PLAN_TIPO_PREVENCION = 1;
var _PLAN_TIPO_AUDITORIA = 2;
var _PLAN_TIPO_SUPERVICION = 3;
var _PLAN_TIPO_ACCION = 4;
var _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL = 5;
var _PLAN_TIPO_ACTIVIDADES_MENSUAL = 6;
var _PLAN_TIPO_ACTIVIDADES_ANUAL = 7;
var _PLAN_TIPO_MEDIDAS = 8;
var _PLAN_TIPO_MEETING = 9;


function blinkIt() {
    if (!document.all)
        return;
    else {
        for (i = 0; i < document.all.tags('blink').length; i++) {
            s = document.all.tags('blink')[i];
            s.style.visibility = (s.style.visibility == 'visible') ? 'hidden' : 'visible';
        }
    }
}

function loadtablero(tablero) {
    var month = $('#month').val();
    var year = $('#year').val();
    var day = $('#day').val();

    self.location.href = 'tablero.php?id_tablero=' + tablero + '&day=' + day + '&month=' + month + '&year=' + year;
}

function loadresumen(tablero) {
    var month = $('#month').val();
    var year = $('#year').val();
    var day = $('#day').val();

    self.location.href = 'resumen.php?id_tablero=' + tablero + '&day=' + day + '&month=' + month + '&year=' + year;
}

function loadproceso(tablero) {
    var month = $('#month').val();
    var year = $('#year').val();
    var day = $('#day').val();

    self.location.href = 'proceso.php?id_proceso=' + tablero + '&day=' + day + '&month=' + month + '&year=' + year;
}


function loadgantt(tablero) {
    var month = $('#month').val();
    var year = $('#year').val();

    self.location.href = 'gantt_user.php?id_gantt=' + tablero + '&month=' + month + '&year=' + year;
}


function loadproyecto(id) {
    var month = $('#month').val();
    var year = $('#year').val();
    var id_programa = $('#programa').val();
    var signal = $('#signal').val();
    var name = signal.indexOf('gantt') != -1 ? "gantt" : "jkanban";
    var url = name + '.php?id_proyecto=' + id + '&month=' + month + '&year=' + year + '&id_programa=' + id_programa;

    self.location.href = url;
}

function load_resume_work(tablero) {
    var month = $('#month').val();
    var year = $('#year').val();
    var day = $('#day').val();

    if (tablero >= 0)
        self.location.href = 'resume_work.php?id_calendar=' + tablero + '&day=' + day + '&month=' + month + '&year=' + year;
    else {
        if (tablero == -1)
            self.location.href = 'resume_update_ind.php?day=' + day + '&month=' + month + '&year=' + year
        if (tablero == -2)
            self.location.href = 'resume_update_tarea.php?day=' + day + '&month=' + month + '&year=' + year;
    }
}

function loadgrafico(id, id_perspectiva) {
    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();

    self.location.href = '../form/fgraph.php?id_indicador=' + id + '&year=' + year + '&month=' + month + '&day=' + day + '&id_perspectiva=' + id_perspectiva;
}


var tabscrollwight = 150;
var step = 200;

function init() {
    var tabArea = $('#tab-area');
    var elems = tabArea.getElementsByTagName('div');

    if (tabArea.clientWidth > elems[1].clientWidth) {
        //	elems[0].style.visibility= 'hidden'; 
        //	elems[2].style.visibility= 'hidden';		
    }
}

function core_move_tabbar(side) {
    var tabArea = $('#tab-area');
    var elems = tabArea.getElementsByTagName('div');

    var x = elems[1].offsetLeft;

    if (side == 'right') {
        if ((elems[1].clientWidth + x) <= (tabArea.clientWidth - tabscrollwight))
            return;
        elems[1].style.left = (x - step) + 'px';
        return;
    }

    if (side == 'left') {
        if (x >= tabscrollwight)
            return;
        elems[1].style.left = (x + step) + 'px';
        return;
    }
}

function set_pos_tab(pos) {
    var x = ((pos - 1) * tabscrollwight);
    var y = tabscrollwight + step;

    if (x < y)
        return;

    $('#div-tab-buttons').style.left = -x + 'px';
}

var tab_proyecto_control;

function move_tabbar(side, init) {
    if (init == 1)
        tab_proyecto_control = setInterval("core_move_tabbar('" + side + "')", 100);
    else
        clearTimeout(tab_proyecto_control);
}