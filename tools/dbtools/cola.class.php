<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2018
 */

class Tcola extends Tbase {
    protected $cronos;
    public $verbose;
    public $action;
    public $protocol;
    
    protected $id_proceso;
    public $origen;
    
    public $array_cola;
    
    private $last_time_exect;
    
    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase::__construct($clink);
        $this->verbose= true;
        $this->listar();
    }
    
    public function set_cronos(){
        $this->cronos= date('Y-m-d H:i:s');
    }

    private function write_log($text) {
        if (!$this->verbose)
            return;
        $filename= _LOG_DIRIGER_DIR."cola.log";
        $fp= fopen($filename, 'a+');
        fwrite($fp, $text);
        fclose($fp);
    }
    public function unlock_system() {
        $sql= "update tusuarios set acc_sys= 0 where acc_sys < 0 or acc_sys > 1 ";
        $this->do_sql_show_error('unlock_system',$sql);
    } 
    
    private function push() {
        $id= strtotime($this->cronos);
        $sql= "insert into tcola (cronos, action, protocol, id_proceso, origen) values ";
        $sql.= "('$this->cronos', '$this->action', '$this->protocol', $this->id_proceso, '$this->origen') "; 
        $result= $this->do_sql_show_error('push',$sql);
        
        $this->id= $this->clink->inserted_id();
        
        $text= "PUSH => CRONOS: $this->cronos | ACTION: $this->action | PROTOCOL: $this->protocol | ";
        $text.= "ORIGEN: $this->origen | ID: $this->id \n";
        $this->write_log($text);
    }
    
    private function update($id) {
        $sql= "update tcola set cronos= '$this->cronos' where id = $id";
        $this->do_sql_show_error('update',$sql);
        
        $action= $this->array_cola[$id]['action'];
        $protocol= $this->array_cola[$id]['protocol'];
        $origen= $this->array_cola[$id]['origen'];
        $text= "UPDATE => CRONOS: $this->cronos | ACTION: $action | PROTOCOL: $protocol | ";
        $text.= "ORIGEN: $origen | ID: $id \n";
        $this->write_log($text);        
    }
    
    public function delete($id) {
        $sql= "delete from tcola where id = $id";
        $result= $this->do_sql_show_error('delete',$sql);
        
        $action= $this->array_cola[$id]['action'];
        $protocol= $this->array_cola[$id]['protocol'];
        $origen= $this->array_cola[$id]['origen'];
        $cronos= $this->array_cola[$id]['cronos'];
        $text= "DELETE => CRONOS: $cronos | ACTION: $action | PROTOCOL: $protocol | ";
        $text.= "ORIGEN: $origen | ID: $id \n";
        $this->write_log($text);         
    }
    
    private function listar() {
        $sql= "select * from tcola order by cronos asc, id asc";
        $result= $this->do_sql_show_error('listar',$sql);
        
        $this->array_cola= array();
        while ($row= $this->clink->fetch_array($result)) 
            $this->array_cola[$row['id']]= $row;                
    }
    
    public function search() {
        $sql= "select * from tcola ";
        $sql.= "where action= '$this->action' and protocol = '$this->protocol' and origen = '$this->origen'";
        $result= $this->do_sql_show_error('search',$sql);
        $row= $this->clink->fetch_array($result);
        
        $text= "======================================================================================= \n";
        $text.= "SEARCH => CRONOS: $this->cronos | ACTION: $this->action | PROTOCOL: $this->protocol | ";
        $text.= "ORIGEN: $this->origen | ID: {$row['id']}\n";
        $this->write_log($text); 
        
        return $row['id'];
    }

    private function if_system_busy() {
        global $config;
        
        $sql= "select * from tsystem order by inicio desc, id desc limit 1";
        $result= $this->do_sql_show_error('if_system_busy',$sql);
        $row= $this->clink->fetch_array($result);
        
        if (empty($row['fin'])) {
            if (!empty($row['cronos']))
                $ellapsed= s_datediff('h', date_create($row['cronos']), date_create($this->cronos));
            else 
                $ellapsed= s_datediff('h', date_create($row['inicio']), date_create($this->cronos));
            
            if (($ellapsed >= $config->maxexectime && ($row['action'] != 'purge' && stripos($row['action'], 'exec_functions') === false)) 
                || ($ellapsed >= 5*$config->maxexectime && $row['action'] == 'purge') 
                || ($ellapsed >= 10*$config->maxexectime && stripos($row['action'], 'exec_functions') !== false)) {
                $this->close_system_action ($row['id']);
                $this->unlock_system();
                $this->last_time_exect= $this->cronos;

                $text= "SYSTEM FREE => CRONOS: $this->cronos \n";
                $this->write_log($text);                
                return false;
            }  
    
            $text= "SYSTEM OCCUPED => CRONOS: $this->cronos ACTION: {$row['action']} INICIO:{$row['inicio']} CRONOS: {$row['cronos']} ELLAPSED: $ellapsed \n";
            $this->write_log($text);        
            return $row['action'];
        }
        
        $this->last_time_exect= $row['fin'];
        $text= "SYSTEM FREE => CRONOS: $this->cronos LAST TIME: $this->last_time_exect \n";
        $this->write_log($text);        
        return false;
    }
    
    private function close_system_action($id) {
        $sql= "update tsystem set fin= '$this->cronos', observacion= 'forzado el cierre' ";
        $sql.= "where id = $id";
        $result= $this->do_sql_show_error('close_action_system',$sql);

        $action= $this->array_cola[$id]['action'];
        $protocol= $this->array_cola[$id]['protocol'];
        $origen= $this->array_cola[$id]['origen'];       
        $text= "CLOSE SYSTEM ACTION => CRONOS: $this->cronos | ACTION: $action | PROTOCOL: $protocol | ";
        $text.= "ORIGEN: $origen \n";
        $this->write_log($text);            
    }
    
    private function _if_justtime_synchro() {
        global $config;        
        $currtime= $this->cronos;
        
        if (!empty($config->timesynchro)) {
            $timesynchro= strtotime($config->timesynchro);
            if ($currtime > strtotime('06:00:00') && $currtime < strtotime('17:00:00')) 
                return false;
            if ((($currtime >= strtotime('17:00:00') && $currtime < strtotime('23:59:59')) 
                && ($timesynchro >= strtotime('17:00:00') && $timesynchro < strtotime('23:59:59'))) && $currtime < $timesynchro) 
                return false;
            if ((($currtime >= strtotime('00:00:00') && $currtime < strtotime('06:00:00')) 
                && ($timesynchro >= strtotime('00:00:00') && $timesynchro < strtotime('06:00:00'))) && $currtime < $timesynchro) 
                return false;
        } 
        
        return true;
    } 
    
    private function _if_justtime_backup() {
        global $config;        
        $currtime= $this->cronos;
        
        if (!empty($config->timepurge)) {
            $timepurge= strtotime($config->timepurge);
            if ($currtime > strtotime('06:00:00') && $currtime < strtotime('17:00:00')) {
                if ($currtime < $timepurge) 
                    return false;
            } else {
                if (($timepurge >= strtotime('17:00:00') && $timepurge < strtotime('23:59:59')) && $currtime < $timepurge) 
                    return false;
                if (($timepurge >= strtotime('00:00:00') && $timepurge <= strtotime('06:00:00')) && $currtime < $timepurge) 
                    return false;
        }   } 
        
        return true;
    }    

    private function _if_justtime_purge() {
        global $config;        
        $currtime= $this->cronos;
        
        if (!$this->_if_justime_backup())
            return false;
        
        $obj_sys= new Tclean($this->clink);

        $fecha_clean= $obj_sys->get_system('purge');
        $fecha_clean= $obj_sys->GetFecha();
        $month= !empty($fecha_clean) ? (int)s_datediff('m', date_create($fecha_clean), date_create($currtime)) : 3;

        if ((!is_null($fecha_clean) && $month < (int)$config->monthpurge)) 
            return false;
        
        return true;
    }
    
    private function if_justtime($action) {        
        if ($action == 'update')
            return true;
        if ($action == 'importLote' || $action == 'exportLote') 
            return $this->_if_justtime_synchro();
        if ($action == 'backup') 
            return $this->_if_justtime_backup();
        if ($action == 'purge') 
            return $this->_if_justtime_purge();        
        return true;
    }
    
    private function if_system_waiting() {
        global $config;
        $maxwaittime= $config->maxwaittime*60;
        if (empty($this->last_time_exect))
            return true;
        
        $ellapsed= s_datediff('s', date_create($this->last_time_exect), date_create($this->cronos));
        $i= 0;
        foreach ($this->array_cola as $row) {
            if (!empty($this->id) && $row['id'] == $this->id)
                break; 
            
            if ($ellapsed >= $maxwaittime || !$this->if_justtime($row['action']))
                $this->update($row['id']);
            else 
                ++$i;
        }        
        
        if ($i > 0) {
            $text= "SYSTEM WAITING => CRONOS: $this->cronos ACTION: $this->action ORIGEN: $this->origen \n";
            $this->write_log($text);              
            return true;
        } else {
            $text= "SYSTEM READY => CRONOS: $this->cronos ACTION: $this->action ORIGEN: $this->origen \n";
            $this->write_log($text);              
            return false;            
        }
    }
    
    public function if_execute() {
        $this->id= $this->search();
        $if_busy= $this->if_system_busy();
        $if_waiting= $this->if_system_waiting();
        
        if (($if_busy || $if_waiting) && empty($this->id)) 
            $this->push();
        
        if ($if_busy || $if_waiting) {
            $text= "WAIT => CRONOS: $this->cronos ACTION: $this->action ORIGEN: $this->origen \n";
            $this->write_log($text);              
            return false; 
        }

        $text= "EXECUTE=> CRONOS: $this->cronos ACTION: $this->action ORIGEN: $this->origen \n";
        $this->write_log($text);           
        return true;
    }
    
    public function if_execute_update() {
        $if_busy= $this->if_system_busy();
        
        if (!$if_busy || ($if_busy 
            && ($if_busy != 'update' && $if_busy != 'beginscript' && stripos($if_busy, 'exec_functions.') === false))) {
            $sql= "truncate table tcola";
            $this->do_sql_show_error('if_execute_update',$sql);
        
            $this->push();
        } else {
            $this->search();
        }
        
        return !$if_busy;
    }
}
