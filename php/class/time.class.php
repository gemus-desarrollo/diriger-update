<?php

/*
 * Nombre: Geraudis Mustelier
 * Email: geraudism@gmail.com
 * Agosto 28, 2007
 *
 * Copyright(c).
 */

class TTime {
    private $strtime;
    private $array_month;
    private $hh, $mi, $ss;
    private $yy, $md, $dd;
    private $format;
    private $ampm, $pm;

    public function __construct($strtime= null) {
    	date_default_timezone_set('America/Havana');
        $this->array_month= array('Undef','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');

    	if (!empty($strtime))
            $this->strtime= $strtime;
    	else {
    	  $this->strtime= date('Y-m-d H:i:s');
          $this->SetFormat();
    	}

    	$this->splitTime();
    }

    public function GetHour() {
        return $this->hh;
    }
    public function GetMinute() {
        return $this->mi;
    }
    public function GetSecond() {
        return $this->ss;
    }
    public function GetYear() {
        return $this->yy;
    }
    public function GetMonth() {
        return $this->md;
    }
    public function GetDay() {
        return $this->dd;
    }
    public function GetPM() {
        return $this->pm;
    }
    public function SetDay($dd) {
        $this->dd = $dd;
    }
    public function SetYear($yy) {
        $this->yy = $yy;
    }
    public function SetMonth($md) {
        $this->md = $md;
    }
    public function SetHour($id) {
        $this->hh = $id;
    }
    public function SetMinute($id) {
        $this->mi = $id;
    }

    public function setNull() {
        $this->yy = null;
        $this->md = null;
        $this->dd = null;
        $this->hh = null;
        $this->mi = null;
        $this->ss = null;
        $this->strtime = null;
    }

    public function SetFormat($format = NULL) {
        $this->format = is_null($format) ? 'Y-m-d' : $format;
    }

    public function GetStrDate($format= NULL) {
        if (empty($format)) $format= $this->format;

        $char= strpos($format,'-') ? "-" : "/";
        $month= str_pad($this->md, 2, "0", STR_PAD_LEFT);
        $day= str_pad($this->dd, 2, "0", STR_PAD_LEFT);

        if ($format == 'd-m-Y' || $format == 'd/m/Y')
          $str= $day.$char.$month.$char.$this->yy;
        if ($format == 'Y-m-d' || $format == 'Y/m/d')
          $str= $this->yy.$char.$month.$char.$day;

        return trim($str);
    }

    public function GetStrTime($format = NULL) {
        if (empty($format))
            $format = $this->format;
        $char = strpos($format, '-') ? "-" : "/";

        if ($this->ampm) {
            if ($this->pm && ($this->hh > 0 && $this->hh < 12))
                $this->hh += 12;
            if (!$this->pm && $this->hh == 12)
                $this->hh = 0;
        }    
        $month = str_pad($this->md, 2, "0", STR_PAD_LEFT);
        $day = str_pad($this->dd, 2, "0", STR_PAD_LEFT);
        $hh = str_pad($this->hh, 2, "0", STR_PAD_LEFT);
        $mi = str_pad($this->mi, 2, "0", STR_PAD_LEFT);

        if ($format == 'd-m-Y' || $format == 'd/m/Y')
            $str = $day . $char . $month . $char . $this->yy . ' ' . $hh . ':' . $mi;
        if ($format == 'Y-m-d' || $format == 'Y/m/d')
            $str = $this->yy . $char . $month . $char . $day . ' ' . $hh . ':' . $mi;

        return trim($str);
    }

    public function splitTime($strtime= NULL, $format= NULL)  {
        if (is_null($format))
            $format= $this->format;
        if ($format == 'Y-m-d' || $format == 'Y/m/d'|| empty($format))
            $this->splitTime_yyyy_md_dd($strtime, $format);
        if ($format == 'd-m-Y' || $format == 'd/m/Y')
            $this->splitTime_dd_md_yyyy($strtime, $format);
    }

    private function splitTime_yyyy_md_dd($strtime = NULL) {
        $this->ampm = false;
        if (empty($strtime))
            $strtime = $this->strtime;

        list($this->yy, $this->md, $this->dd) = preg_split('[\/|-]', $strtime);

        $tmp = $this->dd;
        $this->dd = substr($tmp, 0, 2);

        $tmp = substr($tmp, 3);

        $this->pm = false;
        if (strstr(strtoupper($tmp), "AM") !== false) {
            $this->pm = 'AM';
            $this->ampm = true;
            str_replace("AM", "", $tmp);
        } elseif (strstr(strtoupper ($tmp), "PM") !== false) {
            $this->pm = 'PM';
            $this->ampm = true;
            $tmp = str_replace("PM", "", $tmp);
        } else {
            $this->pm = 'm';
            $this->ampm = true;
            str_replace("M", "", $tmp);
        }

        list($this->hh, $this->mi, $this->ss) = preg_split("[:]", $tmp, null, PREG_SPLIT_NO_EMPTY);

        if (strlen($this->mi) > 2)
            $this->mi = substr($this->mi, 0, 2);
        if (strlen($this->ss) > 2)
            $this->ss = substr($this->mi, 0, 2);

        if (empty($this->ss))
            $this->ss = 0;
    }

    private function splitTime_dd_md_yyyy($strtime = NULL) {
        $this->ampm = false;
        if (empty($strtime)) 
            $strtime = $this->strtime;

        list($this->dd, $this->md, $this->yy) = preg_split('[\/|-]', $strtime);
        $tmp = $this->yy;
        $this->yy = substr($tmp, 0, 4);

        $tmp = substr($tmp, 5);
        $this->pm = false;

        if (stristr(strtoupper($tmp), "AM") !== false) {
            $this->pm = 'AM';
            $this->ampm = true;
            $tmp = str_replace("AM", "", $tmp);
        } elseif (stristr(strtoupper ($tmp), "PM") !== false) {
            $this->pm = 'PM';
            $this->ampm = true;
            $tmp = str_replace("PM", " ", $tmp);
        } else {
            $this->pm = 'M';
            $this->ampm = true;
            $tmp = str_replace("M", " ", $tmp);
        }

        list($this->hh, $this->mi, $this->ss) = preg_split("[:]", $tmp, null, PREG_SPLIT_NO_EMPTY);

        if (strlen($this->mi) > 2)
            $this->mi = substr($this->mi, 0, 2);
        if (strlen($this->ss) > 2)
            $this->ss = substr($this->mi, 0, 2);
        if (empty($this->ss))
            $this->ss = 0;
    }

//   cantidad de dia que tiene un mes : enero - 1  diciembre - 12
    public function longmonth($md = null, $year = null) {
        if (empty($md))
            $md = $this->md;
        if (empty($year))
            $year = $this->yy;

        $month = str_pad($md, 2, "0", STR_PAD_LEFT);
        return (int) date('t', strtotime("$year-$month-01"));
    }

// verifica si la fecha es el ultimo dia del periodo indicado
   public function ifDayPeriodo($periodo, $fix_work_day= false) {
        $dayweek= $this->weekDay();
        $lastday= $this->longmonth();

        if ($periodo == 'D' && (($fix_work_day && $dayweek <= 5) || !$fix_work_day))
            return true;
        if ($periodo == 'S' && $dayweek == 5)
            return true;
        if ($periodo == 'Q'
            && ($this->testDay($this->dd, 15, $fix_work_day)
                || ($this->testDay($this->dd, $lastday, $fix_work_day))))
            return true;
        if ($periodo == 'M' && $this->testDay($this->dd, $lastday, $fix_work_day))
            return true;
        if ($periodo == 'T' && $this->testDay($this->dd, $lastday, $fix_work_day) && ($this->md > 1 && (int)$this->md % 3 == 0))
            return true;
        if ($periodo == 'E' && $this->testDay($this->dd, $lastday, $fix_work_day) && ($this->md > 1 && (int)$this->md % 6 == 0))
            return true;
        if ($periodo == 'A' && $this->testDay($this->dd, $lastday, $fix_work_day) && ($this->md > 1 && (int)$this->md == 12))
            return true;
        return false;
    }

    private function testDay($day, $cut, $fix_work_day) {
        $dayweek= $this->weekDay($day);
        $x= (int)$cut-(int)$day;

        if (!$fix_work_day) {
            if ($day == $cut)
                return true;
        } else {
            if (($dayweek == 5 && ($x > 0 && $x <= 3) && $this->weekDay($cut) > 5) || ($dayweek <= 5 && (int)$day == (int)$cut))
                return true;
        }

        return false;
    }

// calcula la cantidad de semanas transcurridas desde el inicio del anno hasta la fecha en curso
   public function weekNumber() {
        $day = str_pad($this->dd, 2, "0", STR_PAD_LEFT);
        $month = str_pad($this->md, 2, "0", STR_PAD_LEFT);
        return date('W', strtotime("$this->yy-$month-$day"));
    }

// calcula el dia nominal de la semana al que corresponde la fecha Domingo 7, Lunes 1, Martes 2 ..... Sabado 6;
   public function weekDay($dd= null){
       $dd= !empty($dd) ? $dd : $this->dd;

       $day= str_pad($dd, 2, "0", STR_PAD_LEFT);
       $month= str_pad($this->md, 2, "0", STR_PAD_LEFT);
       return date('N', strtotime("{$this->yy}-{$month}-{$day}"));
    }

    public function GetMonthText() {
        $month = (int) $this->md;
        return $this->array_month[$month];
    }

}

// finaliza la clase time
/*
  * A mathematical decimal difference between two informed dates
  *
  * Author: Sergio Abreu
  * Website: http://sites.sitesbr.net
  *
  * Features:
  * Automatic conversion on dates informed as string.
  * Possibility of absolute values (always +) or relative (-/+)
 */

function s_datediff($str_interval, $dt_menor, $dt_maior, $relative=false){
    if ( is_string( $dt_menor))
        $dt_menor = date_create( $dt_menor);
    if ( is_string( $dt_maior))
        $dt_maior = date_create( $dt_maior);

    $diff = date_diff( $dt_menor, $dt_maior, !$relative);

    switch( $str_interval) {
        case "y":
            $total = $diff->y + $diff->m / 12 + $diff->d / 365.25; break;
        case "m":
            $total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
            break;
        case "d":
            $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
            break;
        case "h":
            $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
            break;
        case "i":
            $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
            break;
        case "s":
            $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
            break;
    }

    if ( $diff->invert)
        return -1 * $total;
    else
        return $total;
}

function DateTime_object($time) {
    $time= strtotime($time);
    $array= array('y'=>date('Y',$time), 'm'=>date('m',$time), 'd'=>date('d',$time),
                'h'=>date('H',$time), 'i'=>date('i',$time), 's'=>date('s',$time), 'n'=>date('N', $time));
    return $array;
}

/* *
/* Halla el tiempo transcurrido desde strtime1 hasta strtime2-> strtime2 - strtime1
*/
function diffDate($strtime1, $strtime2) {
    $datetime1= date_create($strtime1);
    $datetime2= date_create($strtime2);
    $intervalo= date_diff($datetime1, $datetime2);

    $y= (int)$intervalo->format('%r%y');
    $m= (int)$intervalo->format('%r%m');
    $d= (int)$intervalo->format('%r%d');
    $h= (int)$intervalo->format('%r%h');
    $i= (int)$intervalo->format('%r%i');
    $s= (int)$intervalo->format('%r%s');

    $diff= array('y'=>$y, 'm'=>$m, 'd'=>$d, 'h'=>$h, 'i'=>$i, 's'=>$s);
    return $diff;
}   // termina diffTime

// Halla el tiempo transcurrido desde time1 hasta time2-> time1 - time2
function diffTime($time1, $time2) {
    list($hh1, $mi1, $ss1)= preg_split("[:]", $time1, null, PREG_SPLIT_NO_EMPTY);
    list($hh2, $mi2, $ss2)= preg_split("[:]", $time2, null, PREG_SPLIT_NO_EMPTY);

    if (is_null($ss1)) 
        $ss1= 0;
    if (is_null($ss2)) 
        $ss2= 0;

    $t1= ($hh1 * 60 + $mi1) * 60 + $ss1;
    $t2= ($hh2 * 60 + $mi2) * 60 + $ss2;

    $tt= $t1-$t2;
    $h= floor(((float)$tt / 3600));

    $_m= $tt - ($h * 3600);
    $m= (float)$_m / 60;
    $m= floor($m);

    $s= $_m - ($m * 60);

    $diff['h']= $h; $diff['i']= $m; $diff['s']= $s;
    return $diff;
}

function time2ampm($time) {
    return date('h:i:s A', strtotime($time));
}

function time2odbc($date) {
    if (empty($date))
        return null;

    if (stristr($date, '-'))
        list($d, $m, $rest)= explode('-', $date);
    if (stristr($date, '/'))
        list($d, $m, $rest)= explode('/', $date);
    list($y, $time, $ampm)= explode(" ", $rest);

    if ($d > $y) {
        $t = $d;
        $d = $y;
        $y = $t;
    }

    $date= trim("$y-$m-$d $time $ampm");
    return date('Y-m-d H:i:s', strtotime($date));
}

function date2odbc($date) {
    $date= time2odbc($date);
    if (empty($date))
        return NULL;
    return date('Y-m-d', strtotime($date));
}

function odbc2date($date, $withyear= true) {
    if (empty($date))
        return null;

    $str= strtotime($date);
    $str= date('d/m/Y', $str);

    if (!$withyear) {
        $len= strlen($str) - 5;
        $str= substr($str, 0, $len);
    }
    return $str;
}

function odbc2time($date) {
    if (empty($date))
        return null;

    $str = strtotime($date);
    $str = date('d/m/Y H:i:s', $str);
    return $str;
}

function odbc2time_ampm($date, $hoursoldier= true, $str_pad= false) {
    global $config;
    
    if (empty($date))
        return null;
    
    $hoursoldier= !is_null($hoursoldier) ? $hoursoldier : $config->hoursoldier;

    $time= odbc2date($date);
    if (!stripos($date, ':'))
        return $time;
    $time.= " ".odbc2ampm($date, $hoursoldier, $str_pad);
    return $time;
}

function ampm2odbc($time) {
    if (empty($time))
        return null;
    return date('H:i:s', strtotime($time));
}

function odbc2ampm($time, $hoursoldier= true, $str_pad= false) {
    global $config;
    
    if (empty($time))
        return null;
    $hoursoldier= !is_null($hoursoldier) ? $hoursoldier : $config->hoursoldier;

    $str_pad= !is_null($str_pad) ? $str_pad : false;

    $strtime= (strlen($time) > 11) ? substr($time, 11, 5) : substr($time, 0, 5);
    if ($config->hoursoldier && $hoursoldier)
        return $strtime;

    $hh= substr($strtime, 0, 2);
    $mi= substr($strtime, 3, 4);
    $time= substr($strtime, 2, 7);
    $am= null;
    $h= (int)$hh;

    if ($h == 12 && (int)$mi == 0)
        $am= 'M';
    elseif ($h == 12 && (int)$mi > 0)
        $am= 'PM';
    elseif ($h > 12) {
        $am= 'PM';
        $hh= $h - 12;
    }
    elseif ($h == 0  || ($h > 0 && $h < 12))
        $am= 'AM';
    if ($h == 0 && $am == 'AM')
        $hh= 12;

    $hh= !$str_pad ? (int)$hh : str_pad($hh, 2, "0", STR_PAD_LEFT);
    return $hh.$time.' '.$am;
}

function add_date($givendate, $day=0, $mth=0, $yr=0, $hh= 0,$min= 0, $sec= 0) {
    $cd = strtotime($givendate);
    $newdate = date('Y-m-d H:i:s', mktime(date('H', $cd)+$hh, date('i', $cd)+$min, date('s', $cd)+$sec, date('n', $cd)+$mth, date('j', $cd)+$day, date('Y', $cd)+$yr));
    return $newdate;
 }

 function getDateInterval($date, $period, $all_month= true) {
    $all_month= !is_null($all_month) ? $all_month : true;
    $inicio= $date;
    $end= $date;
    
    $day= date('d', strtotime($date));
    $month= date('m', strtotime($date));
    $year= date('Y', strtotime($date));

    $time= new TTime;
    $time->SetYear($year);
    $time->SetMonth($month);
    
    if ($period == 'D') {
        $inicio= date('Y-m-d', strtotime($date));
        $end= date('Y-m-d', strtotime($date));
    }
    if ($period == 'N') {
        $inicio= date('Y-m-d', strtotime($date));
        $end= $year.'-12-31';
    }
    if ($period == 'M' || $period == 'U') {
        $inicio= $year.'-'.$month. ($all_month ? '-01' : "-$day");
        $lastday= $time->longmonth();
        $end= $year.'-'.$month.'-'.$lastday;
    }
    if ($period == 'Y') {
        $inicio= $year.'-01-01';
        $end= $year.'-12-31';
    }
    if ($period == 'S') {
        $dayweek= date('N', strtotime($date));
        $end= add_date($date, (7-$dayweek));
        $inicio= add_date($date, -($dayweek-1));
        $end= date('Y-m-d', strtotime($end));
        $inicio= date('Y-m-d', strtotime($inicio));
    }

    $array= array('inicio'=>$inicio, 'fin'=>$end);
    return $array;
}

 /**
 * encuentra la fecha del dia de la semana en el mes segun la frecuencia que se elija
 * ejemplo el primer lunes del mes;
 */
 function get_date_day($day, $freq, $month, $year) {
    $time= new TTime();
    $lastday= $time->longmonth($month, $year);
    $lastdate= $lastday;

    $j= 0;
    for ($i= 1; $i <= $lastday; ++$i) {
        $date= $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($i, 2, "0", STR_PAD_LEFT);
        if ($day == date('N', strtotime($date))) {
            ++$j;
            $lastdate= $date;

            if ($j == $freq && ($freq && $freq < 5))
                return $date;
            if (($j > 4 || ($j == 4 && $lastday - $i < 7)) && $freq == 5)
                return $lastdate;
        }
    }

    return ($freq <= $j) ? $lastdate : false;
 }

function split_time_seconds($second) {
    if (empty($second))
        return null;

    $d= $second/86400;
    $day= (int)floor($d);
    $second= $second - ($day*86400);

    $d= $second/3600;
    $hour= (int)floor($d);
    $second= $second - ($hour*3600);

    $d= $second/60;
    $min=(int)floor($d);

    $sec= $second - ($min*60);

    $array= array('d'=>$day, 'h'=>$hour, 'i'=>$min, 's'=>$sec);
    return $array;
}

// PARA PROBAR FUNCIONES
/*
$date= '2012-11-23';
echo $date .' ===> ';
$array= getDateInterval($date, 'S');
print_r($array);
*/
?>