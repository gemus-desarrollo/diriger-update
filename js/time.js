/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

/////////////////////////////////////////////////////// MUSTELIER ////////////////////////////////
//Este script y otros muchos pueden
//descarse on-line de forma gratuita
//en El Código: www.elcodigo.net
//
//	Version 1
//	03/02/2001

/*
 * Cantidad de dias transcurridos desde la fecha2 hasta la fecha1=> fecha1 - fecha2;
 */
function DiferenciaFechas(fecha1, fecha2, flag) {
    //Obtiene dia, mes y año
    var fecha1 = new Fecha(fecha1);
    var fecha2 = new Fecha(fecha2);

    if ((fecha1.ampm == "AM" || fecha1.ampm == "M") && parseInt(fecha1.hh) == 12)
        fecha1.hh = 0;
    if (fecha1.ampm == "PM" && parseInt(fecha1.hh) != 12)
        fecha1.hh = parseInt(fecha1.hh) + 12
    if ((fecha2.ampm == "AM" || fecha2.ampm == "M") && parseInt(fecha2.hh) == 12)
        fecha2.hh = 0;
    if (fecha2.ampm == "PM" && parseInt(fecha2.hh) != 12)
        fecha2.hh = parseInt(fecha2.hh) + 12

    //Obtiene objetos Date
    var miFecha1 = new Date(fecha1.anio, fecha1.mes, fecha1.dia, fecha1.hh, fecha1.mi, fecha1.ss);
    var miFecha2 = new Date(fecha2.anio, fecha2.mes, fecha2.dia, fecha2.hh, fecha2.mi, fecha2.ss);

    //Resta fechas y redondea
    miFecha1 = miFecha1.getTime();
    miFecha2 = miFecha2.getTime();

    var diferencia = miFecha1 - miFecha2;
    var dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
    var segundos = Math.floor(diferencia / 1000);
    var minutos = Math.floor(diferencia / (1000 * 60));
    //alert ('La diferencia es de ' + dias + ' dias,\no ' + segundos + ' segundos.');

    var meses = (fecha1.anio * 12 + parseInt(fecha1.mes)) - (fecha2.anio * 12 + parseInt(fecha2.mes));
    meses = meses > 1 || meses == 0 ? meses : dias > 30 ? 1 : 0;
    if (flag == 's')
        return segundos;
    if (flag == 'd')
        return dias;
    if (flag == 'm')
        return meses;
}

function Fecha(cadena) {
    //Separador para la introduccion de las fechas
    var separador = Array("/", " ", ":");
    var iampm = -1;

    if (cadena.indexOf("-") != -1)
        _separator = "-";
    if (cadena.indexOf("/") != -1)
        _separator = "/";

    var iam = cadena.indexOf(" AM") != -1 ? cadena.indexOf(" AM") : cadena.indexOf(" am");
    var ipm = cadena.indexOf(" PM") != -1 ? cadena.indexOf(" PM") : cadena.indexOf(" pm");
    var im = cadena.indexOf(" M") != -1 ? cadena.indexOf(" M") : cadena.indexOf(" m");

    this.ampm = null;
    if (iam != -1) {
        this.ampm = "AM";
        iampm = iam;
    }
    if (ipm != -1) {
        this.ampm = "PM";
        iampm = ipm;
    }
    if (im != -1) {
        this.ampm = "M";
        iampm = im;
    }

    cadena = iampm != -1 ? cadena.substring(0, iampm) : cadena;

    //Separa por dia, mes y año
    if (cadena.indexOf(_separator) != -1) {
        var posi1 = 0;
        var posi2 = cadena.indexOf(_separator, posi1 + 1);
        var posi3 = cadena.indexOf(_separator, posi2 + 1);
        var posi4 = cadena.indexOf(separador[1], posi3 + 1);

        if (posi4 == -1)
            posi4 = cadena.length;

        if (_separator == "/") {
            this.dia = cadena.substring(posi1, posi2);
            this.mes = cadena.substring(posi2 + 1, posi3);
            this.anio = cadena.substring(posi3 + 1, posi4);
        }
        if (_separator == "-") {
            this.anio = cadena.substring(posi1, posi2);
            this.mes = cadena.substring(posi2 + 1, posi3);
            this.dia = cadena.substring(posi3 + 1, posi4);
        }
    } else {
        this.dia = 0;
        this.mes = 0;
        this.anio = 0;
    }
    if (cadena.indexOf(separador[2]) != -1) {
        var posi5 = cadena.indexOf(separador[2], posi4 + 1);
        var posi6 = cadena.indexOf(separador[2], posi5 + 1);
        this.hh = cadena.substring(posi4 + 1, posi5);

        if (posi6 == -1)
            this.mi = cadena.substring(posi5 + 1, cadena.length);
        else
            this.mi = cadena.substring(posi5 + 1, posi6);

        if (posi6 != -1)
            this.ss = cadena.substring(posi6 + 1, cadena.length);
        else
            this.ss = 0;
    } else {
        this.hh = 0;
        this.mi = 0;
        this.ss = 0;
    }
}

function ampm2time(fecha) {
    if (fecha == null || fecha == 'undefined' || fecha.length == 0)
        return;

    var date = false;
    var time = false;
    var hh = false;
    var mi = false;
    var ss = false;
    var am = false;

    var item = false;
    var items = fecha.split(' ');

    for (var i = 0; i < items.length; ++i) {
        item = new String(items[i]);

        if (item.indexOf('/') > 0 || item.indexOf('-') > 0)
            date = item;
        if (item.indexOf(':') > 0) {
            time = item;
            if (time.length < 7)
                time = time + ':00';
            time = time.split(':');

            hh = time[0];
            mi = time[1];
            ss = time[2];
        }
        if (item.indexOf('m') > 0 || item.indexOf('M') > 0)
            am = item;
    };

    var _hh = parseInt(hh);

    if (am == 'pm' || am == 'PM')
        if (_hh < 12)
            _hh += +12;
    if (am == 'am' || am == 'AM')
        if (_hh == 12)
            _hh = '00';

    if (_hh.length == 1)
        _hh = '0' + _hh;

    return date + ' ' + _hh + ':' + mi + ':' + ss;
}

function longmonth(md, year) {
    var b = 0;

    if (md == 2) {
        if ((year % 4 == 0 && year % 100 != 0) || year % 400 == 0)
            b = 29;
        else
            b = 28;
    } else if (md <= 7) {
        if (md == 0)
            b = 31;
        else if (md % 2 == 0)
            b = 30;
        else
            b = 31;
    } else if (md > 7) {
        if (md % 2 == 0)
            b = 31;
        else
            b = 30;
    }
    return b;
}

function add_to_date(fecha, day, month, year) {
    var _fecha = new Fecha(fecha);
    var d = new Date(_fecha.anio, _fecha.mes - 1, _fecha.dia, 0, 0, 0);
    var f = d;
    f.setDate(d.getDate() + day);
    f.setMonth(d.getMonth() + month);
    f.setFullYear(d.getFullYear() + year);
    var str = f.getFullYear() + '-' + (f.getMonth() + 1) + '-' + f.getDate();
    return str;
}

/*
 * Verificar si el formato es una fecha
 */
function isDate(str) {
    var patter1 = /^\d{1,2}\/\d{1,2}\/\d{2,4}$/;
    var patter2 = /^\d{2,4}-\d{1,2}-\d{1,2}$/;
    if (str != '' && (str.match(patter1) || str.match(patter2)))
        return true;
    else
        return false;
}