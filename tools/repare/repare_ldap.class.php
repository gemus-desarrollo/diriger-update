<?php
/**
 * User: Geraudis Mustelier
 * Date: 12/01/2021
 * Time: 13:25
 */

require_once '../../php/class/base.class.php';

class Trepare_ldap extends Tbase {
    public $id_user_fix;
    public $id_user_delete;
    private $total_tables;
    public $mix_cargo; 
    protected $if_secure_mix_cargo;

    protected $nombre_fix,
            $nombre_del;

    public function __construct($clink) {
        global $config;
        $this->total_tables= 69;
        Tbase::__construct($clink);
    }

    public function lista() {        
        $sql= "select * from tusuarios order by nombre asc, id asc; ";
        $result= $this->clink->query($sql);
        
        return $result;
    }

    public function repare($show_error= false) { 
        $this->exect_transfer();    
        $error= $this->update_users($show_error);

        if (is_null($error) && $this->mix_cargo)
            $this->mix_cargo();

        $this->delete($error, $show_error);
    }

    private function delete($error, $show_error= false) {
        if (is_null($error)) {
            $this->obj_user_fix->set_eliminado(false);
            $error= $this->obj_user_del->eliminar(true, true);
            $this->set_ldap_uid();

            if (is_null($error) && $this->mix_cargo)
                $this->_mix_cargo();

            if (!is_null($error)) {
                $error.= " No se ha podido eliminar al usuario nombre: $this->nombre_del ID:$this->id_user_delete ";
                $error.= "<strong>SE MANTINE:</strong> $this->nombre_fix ID: $this->id_user_fix";
            }    
        } else
            $error.= " No fue posible actualizar al usuario: $this->nombre_fix ";
        
        if ($show_error) 
            show_error(null, $error);  
    }

    private function update_users($show_error= false) {
        $error= null;
    
        if (empty($this->id_user_fix) || empty($this->id_user_delete)) 
            return "Debe especificar a los dos usuarios";
            
        $this->obj_user_del= new Tusuario($this->clink);
        $this->obj_user_del->SetIdUsuario($this->id_user_delete);
        $this->obj_user_del->Set();
        
        $usuario_del= $this->obj_user_del->GetUsuario();
        $this->nombre_del= $this->obj_user_del->GetNombre();
        $user_ldap_del= $this->obj_user_del->get_user_ldap();
        
        $this->obj_user_del->set_user_ldap("$user_ldap_del-xxx", false);
        $this->obj_user_del->SetUsuario("$usuario_del-xxx");
        $this->obj_user_del->SetNombre("$this->nombre_del-xxx", false);
        
        $this->obj_user_del->update();
        
        // ************* obj_user_fix *************
        $this->obj_user_fix= new Tusuario($this->clink);
        $this->obj_user_fix->SetIdUsuario($this->id_user_fix);
        $this->obj_user_fix->Set();
        $this->nombre_fix= $this->obj_user_fix->GetNombre();    
        
        $this->obj_user_fix->SetUsuario($usuario_del);
        $this->obj_user_fix->SetNombre($this->nombre_del, false);
        $this->obj_user_fix->set_user_ldap($user_ldap_del, false);
        $this->obj_user_fix->SetClave($this->obj_user_del->GetClave());
        $this->obj_user_fix->SetMail_address($this->obj_user_del->GetMail_address());
        
        $error= $this->obj_user_fix->update();
        return $error;
    }
    
    private function transfer_user($table, $id_field= "id_usuario", $itable) {
        if (!$this->clink->if_table_exist($table))
            return null;
        
        $r= (float)$itable/$this->total_tables;
        $_r= number_format($r*100, 3);               
        bar_progressCSS(0, "Tablas procesadas ... $_r%", $r);    
        
        $sql= "select * from $table where $id_field = $this->id_user_delete";
        $result= $this->clink->query($sql);
        $nums= $this->clink->num_rows($result);
        
        $i= 0;
        $j= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $sql= "update $table set $id_field = $this->id_user_fix where $id_field = $this->id_user_delete ";
            $sql.= "and id = {$row['id']}";
            $this->clink->query($sql);
            
            ++$i;
            ++$j;
            if ($j >= 200) {
                $j= 0;
                $r= (float)$i/$nums;
                $_r= number_format($r*100, 3);               
                bar_progressCSS(1, "Procesando registros ... $_r%", $r);
            }        
            
            $error= $this->clink->error();
            if (!empty($error) && strstr($error, "Duplicate entry")) {
                $error= null;
                $sql= "delete from $table where id = {$row['id']}";
                $this->clink->query($sql);
                
                if ($this->clink->error())
                    show_error($sql, $this->clink->error);         
            }
            if ($error) 
                show_error($sql, $error);      
        }
        
        if ($j && !empty($nums)) {
            $r= (float)$i/$nums;
            $_r= number_format($r*100, 3);               
            bar_progressCSS(1, "Procesando registros ... $_r%", $r);        
        }
    }
    
    public function exect_transfer() {
        $init_year= 2017;
        $end_year= (int)date('Y')+1;

        $cant_tables= 0;

        $this->transfer_user("tasistencias", "id_usuario", ++$cant_tables);
        $this->transfer_user("tarchivo_personas", "id_usuario", ++$cant_tables); 

        $this->transfer_user("tauditorias", "id_usuario", ++$cant_tables);
        $this->transfer_user("tauditorias", "id_responsable", ++$cant_tables);
        $this->transfer_user("tauditorias", "id_responsable_2", ++$cant_tables);

        $this->transfer_user("teventos", "id_usuario", ++$cant_tables);
        $this->transfer_user("teventos", "id_responsable", ++$cant_tables);
        $this->transfer_user("teventos", "id_responsable_2", ++$cant_tables); 

        $this->transfer_user("ttareas", "id_usuario", ++$cant_tables);
        $this->transfer_user("ttareas", "id_responsable", ++$cant_tables);
        $this->transfer_user("ttareas", "id_responsable_2", ++$cant_tables);

        $this->transfer_user("tdebates", "id_usuario", ++$cant_tables);
        $this->transfer_user("tdebates", "id_responsable", ++$cant_tables);

        $this->transfer_user("tdocumentos", "id_usuario", ++$cant_tables); 
        $this->transfer_user("tdocumentos", "id_responsable", ++$cant_tables);

        $this->transfer_user("tindicadores", "id_usuario_real", ++$cant_tables); 
        $this->transfer_user("tindicadores", "id_usuario_plan", ++$cant_tables); 

        $this->transfer_user("tkanban_column_tareas", "id_usuario", ++$cant_tables); 
        $this->transfer_user("tkanban_columns", "id_responsable", ++$cant_tables);

        $this->transfer_user("tpersonas", "id_usuario", ++$cant_tables);
        $this->transfer_user("tpersonas", "id_responsable", ++$cant_tables);

        $this->transfer_user("ttematicas", "id_responsable", ++$cant_tables);
        $this->transfer_user("ttematicas", "id_responsable_eval", ++$cant_tables);

        for($year= $init_year; $year <= $end_year; $year++) {
            $this->transfer_user("treg_evento_$year", "id_usuario", ++$cant_tables);
            $this->transfer_user("treg_evento_$year", "id_responsable", ++$cant_tables);

            $this->transfer_user("tusuario_eventos_$year", "id_usuario", ++$cant_tables);

            $this->transfer_user("tproceso_eventos_$year", "id_responsable", ++$cant_tables);
            $this->transfer_user("tproceso_eventos_$year", "id_responsable_aprb", ++$cant_tables);
            $this->transfer_user("tproceso_eventos_$year", "id_usuario", ++$cant_tables);            
        }

        $this->transfer_user("ttematicas", "id_responsable", ++$cant_tables);

        $this->transfer_user("tnotas", "id_usuario", ++$cant_tables);
        $this->transfer_user("tnotas", "id_responsable", ++$cant_tables);

        $this->transfer_user("tnota_causas", "id_usuario", ++$cant_tables);

        $this->transfer_user("tplanes", "id_usuario", ++$cant_tables);
        $this->transfer_user("tplanes", "id_responsable", ++$cant_tables);
        $this->transfer_user("tplanes", "id_responsable_aprb", ++$cant_tables);
        $this->transfer_user("tplanes", "id_responsable_auto_eval", ++$cant_tables);
        $this->transfer_user("tplanes", "id_responsable_eval", ++$cant_tables);

        $this->transfer_user("treg_nota", "id_usuario", ++$cant_tables);

        $this->transfer_user("treg_plantrab", "id_usuario", ++$cant_tables);

        $this->transfer_user("triesgos", "id_usuario", ++$cant_tables);
        $this->transfer_user("treg_riesgo", "id_usuario", ++$cant_tables);

        $this->transfer_user("treg_tarea", "id_usuario", ++$cant_tables);

        $this->transfer_user("tregistro", "id_usuario_real", ++$cant_tables);
        $this->transfer_user("tregistro", "id_usuario_plan", ++$cant_tables);
        $this->transfer_user("tregistro", "id_usuario", ++$cant_tables);

        $this->transfer_user("tindicadores", "id_usuario_real", ++$cant_tables);
        $this->transfer_user("tindicadores", "id_usuario_plan", ++$cant_tables);
        $this->transfer_user("treg_plan", "id_usuario", ++$cant_tables);
        $this->transfer_user("treg_real", "id_usuario", ++$cant_tables);
    
        $this->transfer_user("tperspectivas", "id_usuario", ++$cant_tables);
        $this->transfer_user("treg_perspectiva", "id_usuario", ++$cant_tables);
        
        $this->transfer_user("tinductores", "id_usuario", ++$cant_tables);
        $this->transfer_user("treg_inductor", "id_usuario", ++$cant_tables);
        
        $this->transfer_user("treg_politica", "id_usuario", ++$cant_tables); 

        $this->transfer_user("ttrazas", "id_usuario", ++$cant_tables); 
        $this->transfer_user("tprocesos", "id_responsable", ++$cant_tables); 
        
        return $cant_tables;
    }

    public  function set_eliminado() {        
        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_user_delete);
        $obj_user->set_eliminado(false);
    }

    public function mix_cargo() {
        if (empty($this->obj_user_fix)) {
            $this->obj_user_fix= new Tusuario($this->clink);
            $this->obj_user_fix->SetIdUsuario($this->id_user_fix);
            $this->obj_user_fix->Set();            
        }

        if (empty($this->obj_user_del)) {
            $this->obj_user_del= new Tusuario($this->clink);
            $this->obj_user_del->SetIdUsuario($this->id_user_delete);
            $this->obj_user_del->Set();            
        }

        $this->obj_user_fix->SetCargo($this->obj_user_del->GetCargo());

        $this->obj_user_fix->SetIdProceso($this->obj_user_del->GetIdProceso());
        $this->obj_user_fix->set_id_proceso_code($this->obj_user_del->get_id_proceso_code());

        $this->obj_user_fix->set_acc_archive($this->obj_user_del->get_acc_archive());
        $this->obj_user_fix->set_acc_planaudit($this->obj_user_del->get_acc_planaudit());
        $this->obj_user_fix->set_acc_planheal($this->obj_user_del->get_acc_planheal());
        $this->obj_user_fix->set_acc_planproject($this->obj_user_del->get_acc_planproject());
        $this->obj_user_fix->set_acc_planrisk($this->obj_user_del->get_acc_planrisk());
        $this->obj_user_fix->set_acc_planwork($this->obj_user_del->get_acc_planwork());
    }

    private function _mix_cargo() {
        if ($this->if_secure_mix_cargo) {
            $this->obj_user_fix->update();
            $this->_transfer_grupo();
        }

        unset($this->obj_user_del);
        unset($this->obj_user_fix);
    }

    private function _transfer_grupo() {
        $sql= "update tusuario_grupos set id_usuario = $this->id_user_fix where id_usuario = $this->id_user_delete";
        $this->clink->query($sql);

        $sql= "update tproceso_usuarios set id_usuario = $this->id_user_fix where id_usuario = $this->id_user_delete";
        $this->clink->query($sql);        
    }

    public function delete_user() {        
        $sql= "delete from tusuarios where id = $this->id_user_delete";
        $this->clink->query($sql);
        
        if ($this->clink->error()) 
            show_error($sql, $this->clink->error());  
    } 

    public function set_ldap_uid() {
        $sql= "update tusuarios set user_ldap= null where id = $this->id_user_fix";
        $this->clink->query($sql);
    }
}