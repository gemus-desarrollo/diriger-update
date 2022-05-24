<?php
/**
 * @author muste
 * @copyright 2018
 */

include_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/escenario.class.php";


class _Tescenario extends Tescenario {
    
    public function __construct($clink= null) {
        Tescenario::__construct($clink);
        $this->clink= $clink;
    }  
    
    public function add() {
        $mapa_strat= $_SESSION["_DB_SYSTEM"] == "mysql" ? setNULL_str($this->mapa_strat, true, false) : setNULL_blob($this->mapa_strat);
        $mapa_org= $_SESSION["_DB_SYSTEM"] == "mysql" ? setNULL_str($this->mapa_org, true, false) : setNULL_blob($this->mapa_org);
        $mapa_proc= $_SESSION["_DB_SYSTEM"] == "mysql" ? setNULL_str($this->mapa_proc, true, false) : setNULL_blob($this->mapa_proc);
        
        $param_strat = $this->param_strat['name'] . ':' . $this->param_strat['type'] . ':' . $this->param_strat['size'] . ':' . $this->param_strat['dim'];
        $param_org = $this->param_org['name'] . ':' . $this->param_org['type'] . ':' . $this->param_org['size'] . ':' . $this->param_org['dim'];
        $param_proc = $this->param_proc['name'] . ':' . $this->param_proc['type'] . ':' . $this->param_proc['size'] . ':' . $this->param_proc['dim'];

        $observacion_org = setNULL_str($this->observacion_org);
        $observacion_proc = setNULL_str($this->observacion_proc);
        $descripcion = setNULL_str($this->descripcion);
        $mision = setNULL_str($this->mision);
        $vision = setNULL_str($this->vision);

        $sql = "insert into _tescenarios (id, id_code, id_proceso, id_proceso_code, descripcion, inicio, fin, mapa, mapa_param, org_mapa, ";
        $sql .= "org_param, proc_mapa, proc_param, mision, vision, cronos, situs, org_observaciones, proc_observaciones) ";
        $sql .= "values ($this->id, '$this->id_code', $this->id_proceso, '$this->id_proceso_code', $descripcion, $this->inicio, $this->fin, ";
        
        if (!empty($this->mapa_strat)) $sql.= "$mapa_strat, '$param_strat', ";
        else $sql.= "null, null, ";
                
        if (!empty($this->mapa_org)) $sql.= "$mapa_org, '$param_org', ";
        else $sql.= "null, null, ";
            
        if ($this->mapa_proc) $sql.= "$mapa_proc, '$param_proc', ";
        else $sql.= "null, null, ";
   
        $sql.= "$mision, $vision, '$this->cronos', '$this->location', $observacion_org, $observacion_proc) ";

        $result = $this->do_sql_show_error('add', $sql);
        return $this->error;        
    }    
}
