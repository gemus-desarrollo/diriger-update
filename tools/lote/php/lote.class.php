<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


include_once "baseLote.class.php";

class Tlote extends TbaseLote {    
    protected $table;
    protected $xml;

    protected $array_types;
    protected $array_names;
    protected $array_length;
    protected $array_values;
    protected $array_sql;

    protected $cant_tables;
    
    protected $array_prs_synchro;
    
    public function setTable($id) {
        $this->table = $id;
    }

    public function __construct($clink) {
        $this->clink= $clink;
        TbaseLote::__construct($clink);
        
        if (is_null($this->cronos)) 
            $this->cronos= date('Y-m-d H:i:s');
    }
    
    public function setCantTables($id) {
        $this->cant_tables = $id;
    }

    public function listar() {
        $sql= "select tsincronizacion.*, nombre, cargo from tsincronizacion, tusuarios ";
        $sql.= "where tsincronizacion.id_usuario = tusuarios.id ";
        if (!empty($this->action) && $this->action != 'list') 
            $sql.= " and action = '$this->action' ";
        if (!empty($this->origen_code)) 
            $sql.= "and origen = '$this->origen_code' ";
        if (!empty($this->destino_code)) 
            $sql.= "and destino = '$this->destino_code' ";
        $sql.= "order by tsincronizacion.cronos desc limit 100";

        $result= $this->do_sql_show_error('listar', $sql); 
        $this->cant= $this->clink->num_rows($result);
        if (empty($this->cant)) 
            return null;
        return $result;
    }

    public function _reg() {
        $observacion= setNULL_str($this->observacion);
        $id_usuario= !empty($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : _USER_SYSTEM;
        $fecha= !empty($this->fecha) ? $this->fecha : date('Y').'-01-01';
                
        $date_cutoff= setNULL_str($this->date_cutoff);
        $date_cutover= setNULL_str($this->date_cutover);
        $cronos_cut= setNULL_str($this->cronos_cut);
        $steep_current= setNULL($this->steep_current);
        $finalized= boolean2pg($this->finalized);
        $tb_filter= setNULL_str($this->tb_filter);
        
        $date_tdeletes= setNULL_str($this->last_time_tables['_tdeletes'][1]);
        $date_treg_tarea= setNULL_str($this->last_time_tables['_treg_tarea'][1]);
        $date_treg_evento= setNULL_str($this->last_time_tables['_treg_evento'][1]);
        $date_tproceso_eventos= setNULL_str($this->last_time_tables['_tproceso_eventos'][1]);
        $date_tusuario_eventos= setNULL_str($this->last_time_tables['_tusuario_eventos'][1]);
        
        $date_treg_objetivo= setNULL_str($this->last_time_tables['_treg_objetivo'][1]);
        $date_treg_inductor= setNULL_str($this->last_time_tables['_treg_inductor'][1]);
        $date_treg_perspectiva= setNULL_str($this->last_time_tables['_treg_perspectiva'][1]);
        $date_treg_real= setNULL_str($this->last_time_tables['_treg_real'][1]);
        $date_treg_plan= setNULL_str($this->last_time_tables['_treg_plan'][1]);        
        $date_tinductor_eventos= setNULL_str($this->last_time_tables['_tinductor_eventos'][1]);
        
        $sql= "insert into tsincronizacion (lote, id_usuario, origen, destino, action, observacion, mcrypt, ";
        $sql.= "date_cutoff, date_cutover, cronos_cut, steep_current, finalized, cronos, cronos_syn, tb_filter, ";
        $sql.= "date_tdeletes, date_treg_tarea, date_treg_evento, date_tproceso_eventos, date_tusuario_eventos, ";
        $sql.= "date_treg_objetivo, date_treg_inductor, date_treg_perspectiva, date_treg_real, date_treg_plan, ";
        $sql.= "date_tinductor_eventos) values ('$this->filename', $id_usuario, '$this->origen_code', '$this->destino_code', ";
        $sql.= "'$this->action', $observacion, ". boolean2pg($this->if_mcrypt).", $date_cutoff, $date_cutover, ";
        $sql.= "$cronos_cut, $steep_current, $finalized, '$this->cronos', '$fecha', $tb_filter, $date_tdeletes, ";
        $sql.= "$date_treg_tarea, $date_treg_evento, $date_tproceso_eventos, $date_tusuario_eventos, $date_treg_objetivo, "; 
        $sql.= "$date_treg_inductor, $date_treg_perspectiva, $date_treg_real, $date_treg_plan, $date_tinductor_eventos)";
        $result= $this->do_sql_show_error('_reg', $sql);
      
        return $this->error;
    }

    public function if_lote_loaded($filename= null) {
        $filename= is_null($filename) ? urldecode($_GET["lote"]) : $filename;
        
        $ipos= strrpos($filename,".gz");
        $ipos !== false ? $ipos : strlen($filename);
        $name= substr($filename, 0, $ipos);
        
        $sql= "select * from tsincronizacion where action = 'import' and lote = '$name'";
        $result= $this->do_sql_show_error('if_lote_loaded', $sql);
        $num= $this->clink->num_rows($result);

        return $num > 0 ? true : false;
    }
    
    public function set_chain_procesos($type_synchro= null) {
        if (is_null($this->array_prs_synchro)) 
            $this->set_array_prs_synchro();

        $id_proceso= $_SESSION['local_proceso_id'];
        $tipo= $_SESSION['local_proceso_tipo'];

        $sql= "select distinct t1.* from tprocesos as t1, tprocesos as t2 ";
        $sql.= "where (t1.id_proceso = $id_proceso and t1.tipo >= $tipo or ((t1.id = t2.id_proceso and t2.id = $id_proceso) and t1.tipo <= $tipo)) ";
        $sql.= "and (t1.conectado <> "._NO_LOCAL." and t1.conectado <> "._NO_LOCAL_WAN_NODO.") ";
        $result= $this->do_sql_show_error('set_chain_procesos', $sql);

        if (isset($this->array_procesos)) 
            unset($this->array_procesos);
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($row['conectado'] == _LAN) 
                continue;
            $manner= $this->array_prs_synchro[$row['id']]['manner'];
            $mcrypt= $this->array_prs_synchro[$row['id']]['mcrypt'];
            
            if (is_null($this->array_prs_synchro[$row['id']]) || $manner == _SYNCHRO_NEVER) 
                continue;
            if (($type_synchro == _SYNCHRO_AUTOMATIC_EMAIL) && $manner != _SYNCHRO_AUTOMATIC_EMAIL) 
                continue;
            if (($type_synchro == _SYNCHRO_AUTOMATIC_HTTP) && $manner != _SYNCHRO_AUTOMATIC_HTTP) 
                continue;

            $array= array('id'=>$row['id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'], 'tipo'=>$row['tipo'],
                        'url'=>$row['url'], 'puerto'=>$row['puerto'], 'protocolo'=>$row['protocolo'],
                        'codigo'=>$row['codigo'], 'id_responsable'=>$row['id_responsable'], 'id_proceso'=>$row['id_proceso'],
                        'id_proceso_code'=>$row['id_proceso_code'], 'manner'=>$manner, 'mcrypt'=>$mcrypt);

            $this->array_procesos[$row['id']]= $array;

            if ($i > 0) 
                $this->chain_procesos.= ",";
            $this->chain_procesos.= $row['id'];
            ++$i;
        }

        return $i;
    } 
    
    private function set_array_prs_synchro() {
        $this->array_prs_synchro= Array();

        $obj_config_prs= new Tconfig_synchro($this->clink);
        $obj_config_prs->listar();
        $this->array_prs_synchro= $obj_config_prs->array_procesos;
    }    
    
    public function set_array_procesos($type_synchro= null) {
        $type_synchro= !is_null($type_synchro) ? $type_synchro : _SYNCHRO_MANUAL;
        if (is_null($this->array_prs_synchro)) 
            $this->set_array_prs_synchro();

        $id_proceso= $_SESSION['local_proceso_id'];

        $sql= "select t1.id as _id, t1.nombre as _nombre, t1.email as _email, t1.tipo as _tipo, t1.codigo as _codigo "; 
        $sql.= "from tprocesos  as t1, tprocesos as t2 where (t1.id = t2.id_proceso and t2.id = $id_proceso) ";
        $sql.= "and (t1.conectado <> "._NO_LOCAL." and t1.conectado <> "._NO_LOCAL_WAN_NODO.") ";
        $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        $sql.= "union ";
        $sql.= "select t1.id as _id, t1.nombre as _nombre, t1.email as _email, t1.tipo as _tipo, t1.codigo as _codigo "; 
        $sql.= "from tprocesos  as t1, tprocesos as t2 where (t1.id_proceso = t2.id and t2.id = $id_proceso) ";
        $sql.= "and (t1.conectado <> "._NO_LOCAL." and t1.conectado <> "._NO_LOCAL_WAN_NODO.") ";
        $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        $sql.= "order by _tipo asc, _nombre asc ";       
        $result= Tbase::do_sql_show_error('set_array_procesos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (is_null($this->array_prs_synchro[$row['_id']]) || $this->array_prs_synchro[$row['_id']]['manner'] == _SYNCHRO_NEVER) 
                continue;
            if (($type_synchro == _SYNCHRO_AUTOMATIC_EMAIL || $type_synchro == _SYNCHRO_AUTOMATIC_HTTP) 
                    && $this->array_prs_synchro[$row['_id']]['manner'] == _SYNCHRO_MANUAL) 
                continue;
            if ($type_synchro == _SYNCHRO_AUTOMATIC_HTTP && $this->array_prs_synchro[$row['_id']]['manner'] != _SYNCHRO_AUTOMATIC_HTTP) 
                continue;
            ++$i;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['_nombre'], 'email'=>$row['_email'], 'tipo'=>$row['_tipo'], 'codigo'=>$row['_codigo']);
            $this->array_procesos[$row['_id']]= $array;
        }
        return $this->array_procesos;
    }
    
}
?>