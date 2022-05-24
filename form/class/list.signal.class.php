<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../../php/class/base.class.php";
include_once "../../php/class/time.class.php";

class Tlist_signals extends Tbase {
    public $if_eficaz;
    public $cumulative;

    public function __construct($clink= null) {
        $this->clink = $clink;
        Tbase::__construct($this->clink);
        
        $this->cumulative= false;
        $this->if_eficaz= true;
    }

    public function get_month(&$month, &$year) {
        if ($this->month == 1) {
            $year = $this->year - 1;
            $month = 12;
        } else {
            $month = $this->month - 1;
            $year = $this->year;
        }
    }

    public function get_alarm($value, $echo= true) {
        if (is_null($value)) $alarm= "blank";
        else {
            if ($value >= _BLUE)
                $alarm = "blue";
            if ($value >= _AQUA && $value < _BLUE)
                $alarm = "aqua";
            if ($value >= _GREEN && $value < _AQUA)
                $alarm = "green";
            if ($value >= _YELLOW && $value < _GREEN)
                $alarm = "yellow";
            if ($value >= _ORANGE && $value < _YELLOW)
                $alarm = "orange";
            if ($value < _ORANGE)
                $alarm = "red";
        }
        $img= "<div class='alarm-cicle small bg-{$alarm}' title='". tooltip_alarm($alarm)."'></div>";
        
        if ($echo) echo $img;
        return $img;    
    }
    
    
    public function get_flecha($value2, $value1) {
        $dev= null;
        if (!is_null($value1) && !is_null($value2)) 
            $dev= $value2 - $value1;
        
        if (is_null($dev)) 
            $alarm= "blank";
        else {
            if ($dev == 0) 
                $alarm= "yellow";
            if ($dev > 0) 
                $alarm= "green";
            if ($dev < 0) 
                $alarm= "red";             
        }

        echo "<div class='alarm-arrow vertical small bg-{$alarm}' title='". tooltip_arrow($alarm, $this->cumulative)."'><i class='fa ".arrow_direction($alarm)."'></i></div>";
    }

    public function set_criterio() {
        $obj= new Tproceso($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdProceso($this->id_proceso);
        $obj->get_criterio_eval();

        $this->_orange= $obj->get_orange();
        $this->_yellow= $obj->get_yellow();
        $this->_green= $obj->get_green();
        $this->_aqua= $obj->get_aqua();
        $this->_blue= $obj->get_blue();        
    }
    
    public function get_alarm_prs($value, $echo= true, $set_criterio= true) {
        $set_criterio= !is_null($set_criterio) ? $set_criterio : true;
        if ($set_criterio) 
            $this->set_criterio();
        
        if (is_null($value)) 
            $alarm= "blank";
        else {
            if ($value >= $this->_blue) 
                $alarm= "blue";
            if ($value >= $this->_aqua && $value < $this->_blue) 
                $alarm= "aqua";
            if ($value >= $this->_green && $value < $this->_aqua) 
                $alarm= "green";
            if ($value >= $this->_yellow && $value < $this->_green) 
                $alarm= "yellow";
            if ($value >= $this->_orange && $value < $this->_yellow) 
                $alarm= "orange";
            if ($value < $this->_orange) 
                $alarm= "red";
        }

        $this->if_eficaz= ($value >= $this->_green) ? true : false;

        $img= "<div class='alarm-cicle small bg-{$alarm}'></div>";
        if ($echo) 
            echo $img;
        return $img;
    }


    public function update_eficaz_prs() {
        $eficaz= setNULL($this->if_eficaz);

        $sql= "update table treg_proceso set eficaz = $eficaz, cronos= now() where id = ";
        $sql.= "(select id from treg_proceso where id_proceso = $this->id_proceso and year = $this->year and month = $this->month ";
        $sql.= "order by cronos desc limit 1)";

        $this->do_sql_show_error('update_eficaz_prs', $sql);
    }
}


function get_short_label($text) {
    $_len= 120;
    $flag= false;
    
    $len= strlen($text);
    
    if ($len > $_len) {
        $flag= true;
        $text= substr($text, 0, $_len);
        $len= strrpos($text, " ");
    }
    
    $text= substr($text, 0, $len);
    
    if ($flag) $text.= " .....";

    return $text;
}
 

?>