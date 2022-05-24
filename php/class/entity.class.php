<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2020
*/

include_once "base.class.php";

class Tentity extends Tbase {
    private $n_entity;
    private $n_users;
    
    public function  __construct($clink) {
        $this->clink= $clink;
        Tbase::__construct($clink);
        
        $this->read_licence();
        $this->error= "Ha exedido el número de entidades o usuarios permitidos por la actual licencia del sistema. ";
        $this->error.= "Por favor, debe contactar a su proveedor de la licencia de uso.";
    }    
    
    private function read_licence() {
        $sql= "select * from _config order by chronos desc limit 1";
        $result= $this->do_sql_show_error('read_licence', $sql);
        $row= $this->clink->fetch_array($result);
        $this->n_entity= $row['n_entity'];
        $this->n_users= $row['n_usuarios'];
    }
    
    public function block_entity() {
        $sql= "select count(*) from tprocesos where if_entity = true";
        $result= $this->do_sql_show_error('block_entity', $sql);
        $row= $this->clink->fetch_array($result);
        if (!empty($this->n_entity) && $row[0] >= ($this->n_entity + 1))
            return true;
        return false;
    } 
    
    public function block_users() {
        $sql= "select count(*) from tusuarios where eliminado is null";
        $result= $this->do_sql_show_error('block_users', $sql);
        $row= $this->clink->fetch_array($result);
        if (!empty($this->n_users) && $row[0] >= ($this->n_users + 1))
            return true;
        return false;
    } 
}
