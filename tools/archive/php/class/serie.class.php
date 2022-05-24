<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of serie
 *
 * @author mustelier
 */

include_once "../../../php/class/base.class.php";


class Tserie extends Tbase {
    private $serie;
    protected $numero;
    
    public function SetSerie($id) {
        $this->serie= $id;
    }

    public function __construct($clink = null) {
        parent::__construct($clink);
    }
    
    public function GetNumber($serie= null, $year= null) {
        if (!empty($serie)) 
            $this->serie= $serie;
        if (!empty($year)) 
            $this->year= $year;
        
        $sql= "select numero from tseries where serie = '$this->serie' and year = $this->year ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error('GetNumber', $sql);
        
        if ($result) {
            $row= $this->clink->fetch_array($result);
            $this->numero= !empty($row[0]) ? $row[0] : 0;
            ++$this->numero;

            $this->SetNumero(); 
            return $this->numero;
        } 
        
        return null;
    }
    
    public function SetNumero() {
        $sql= "update tseries set numero = $this->numero, id_usuario= {$_SESSION['id_usuario']}, situs= '$this->location', "; 
        $sql.= "cronos= '$this->cronos' where serie = '$this->serie' and year = $this->year and id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error('GetNumber', $sql);
        $cant= $this->clink->affected_rows($result);
        
        if ($result && empty($cant)) {
            $sql= "insert into tseries (serie, year, numero, id_proceso, id_proceso_code, id_usuario, cronos, situs) ";
            $sql.= "values ('$this->serie', $this->year, $this->numero, $this->id_proceso, '$this->id_proceso_code', ";
            $sql.= "{$_SESSION['id_usuario']}, '$this->cronos', '$this->location')";
            
            $result= $this->do_sql_show_error('GetNumber', $sql);
            return $result ? true : false;
        }
        
        return ($result && $this->clink->affected_rows($result)) ? true : false;
    }
}
