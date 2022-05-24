<?php
include_once "../../../php/class/base.class.php";

class TSend extends Tbase {
    protected $id;
    protected $fecha;
    public $obj; 
    
    public $observacion;
    public $sendmail;
    public $uplink;
    
    public $obj_lote;
    public $file_lote;
    public $email;
    public $signal;
    public $data_origen_chief_prs;
    public $id_origen_chief_prs;
    public $tb_filter;
    public $last_time_tables;
    
    public $id_destino;
    
    public $array_eventos;
    public $finalized;

    public $obj_sys;
    
    
    public function __construct($uplink) {
        Tbase::__construct($uplink);
        
        $this->uplink= $uplink; 
        $this->_Init();

        $this->finalized= true;
        $this->array_eventos= null;        
    }
    
    public function _Init() {
        global $file_lote;
        global $email;
        global $signal;
        global $data_origen_chief_prs;
        global $id_origen_chief_prs;
        global $tb_filter;        
        global $obj_lote;
        global $obj_sys;
        
        global $observacion;
        
        $this->file_lote= $file_lote;
        $this->email= $email;
        $this->observacion= $observacion;
        $this->signal= $signal;
        $this->data_origen_chief_prs= $data_origen_chief_prs;
        $this->id_origen_chief_prs= $id_origen_chief_prs;
        $this->tb_filter= $tb_filter;
        
        $this->obj_lote= $obj_lote;
        $this->obj_sys= $obj_sys;  
    }
    
    private function _Setting() {
        global $config;
        global $array_dbtable;
        
        $this->_Init();
        
        $year= !empty($this->fecha) ? date('Y', strtotime($this->fecha)) : date('Y');

        $cant_tables= count($array_dbtable);
        $this->uplink->RefreshLink();
        $this->obj= new Texport($this->uplink);  
        $this->obj->SetYear($year);

        if ($_SESSION['output_signal'] != 'shell') 
            $this->obj->divout= 'win-log';
        $this->obj->action= 'export';
        $this->obj->signal= $this->signal;
        
        $this->obj->SetFecha($this->fecha);
        $this->obj->SetObservacion($this->observacion);
        $this->obj->setCantTables($cant_tables);

        $this->obj->id_origen= $config->local_proceso_id;
        $this->obj->id_origen_code= $config->local_proceso_id_code;
        $this->obj->origen_code= $config->location;
        $this->obj->id_origen_chief_prs= $this->id_origen_chief_prs;
        $this->obj->data_origen_chief_prs= $this->data_origen_chief_prs;
        $this->obj->id_origen_escenario_code= $_SESSION['id_escenario_code'];

        $this->obj->id_destino= $this->obj_lote->array_procesos[$this->id_destino]['id'];
        $this->obj->id_destino_chief_prs= $this->obj_lote->array_procesos[$this->id_destino]['id_responsable'];
        $this->obj->destino_code= $this->obj_lote->array_procesos[$this->id_destino]['codigo'];
        $this->obj->destino_type= $this->obj_lote->array_procesos[$this->id_destino]['tipo'];

        $this->obj->tb_filter= $this->tb_filter;

        $obj_esc= new Tescenario($this->uplink);
        $obj_esc->SetEscenario(date('Y'), $this->obj->id_destino, _LOCAL);
        $this->obj->id_destino_escenario= $obj_esc->GetIdEscenario();
        $this->obj->id_destino_escenario_code= $obj_esc->get_id_escenario_code();

        $this->obj->if_origen_up= $_SESSION['local_proceso_id'] == $this->obj_lote->array_procesos[$this->id_destino]['id_proceso'] ? true : false;
        $this->obj->if_origen_down= $_SESSION['superior_proceso_id'] == $this->obj->id_destino ? true : false;

        $this->email= $this->obj_lote->array_procesos[$this->id_destino]['email'];

        $last_export= $this->obj->get_last_date_synchronization('export');
        $this->last_time_tables= $this->obj->last_time_tables;
        
        if ($this->obj->finalized) {
            $date_cutoff= !empty($this->fecha) ? $this->fecha : $this->obj->date_cutover;
            $cronos_cut= !empty($this->fecha) ? null : $last_export;
        } else {
            $date_cutoff= $this->obj->date_cutover;
            $cronos_cut= !empty($this->obj->cronos_cut) ? $this->obj->cronos_cut : null;
        }
        $this->obj->date_cutoff= !empty($date_cutoff) ? $date_cutoff : date('Y')."-01-01";
        $this->obj->cronos_cut= $cronos_cut;

        if (!empty($date_cutoff)) {
            $year= date('Y', strtotime($date_cutoff));
            $this->obj->SetYear($year);
        }

        $this->obj->finalized_init= $this->finalized;
        $this->obj->array_tmp_eventos= $this->array_eventos;
        
        $obj_config= new Tconfig_synchro($this->uplink);
        $obj_config->Set($this->id_destino);

        if ($this->signal != 'form') {
            $diff= s_datediff('s', date_create($last_export), date_create(date('Y-m-d H:i').'-00'));
            if ($diff < (int)$obj_config->time_synchro && $this->obj->finalized) {
                if ($this->obj_sys)
                    $this->obj_sys->delete_system();
                return false;
        }   }    

        if ($this->signal == 'shell') {
            $msgshell= "\r\nProcesos ".$this->obj_lote->array_procesos[$this->id_destino]['nombre'];
            $msgshell.= "\r\n Inicio:".date('Y-m-d H:i:');
            echo $msgshell;
        }

        return true;    
    }  
    
    private function init_export() {
        global $array_dbtable;
        
        if (isset($this->obj)) unset($this->obj);
        $this->obj= null;
        
        if ($this->_Setting() == false) 
            return null;

        $text= "Verificando permisos de escritura... ";
        bar_progressCSS(1, $text, 0.1);
        $error= $this->obj->create_file_lote();
        if ($this->delete_lote($text, $error)) 
            return;

        $text= "Preparando tablas temporales para la creación de un lote....";
        bar_progressCSS(1, $text, 0.2);
        $error= $this->obj->export_db(); // -> export_db()
        if ($this->delete_lote($text, $error)) 
            return false;

        $i= 0;
        reset($array_dbtable);
        $text= "Procesando los registros de las tablas ... ";
        bar_progressCSS(1, $text, 0.3); 
        
        return true;
    }
    
    public function export($id, $fecha= null) {
        global $config;
        global $array_dbtable;
        
        $this->id_destino= $id;
        $this->fecha= $fecha;
        $cant_tables= count($array_dbtable);
        
        $result= $this->init_export();
        if (!$result)
            return;
        
        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);
        $this->obj->set_output_header();   
        
        $i= 0;
        reset($array_dbtable);
        while (list($key,$table)= each($array_dbtable)) {
            ++$i;
            $perc= (float)$i/$cant_tables;
            bar_progressCSS(1, "Procesando tabla: ".$key." ... ", $perc);

            if (!$table['export'] && ($key != "tusuarios" && $key != "tprocesos")) 
                continue;
            if ($key == 'ttipo_eventos' && !$this->obj->if_origen_down) 
                continue;

            $this->obj->setTable($key);
            $this->obj->use_year= $table['use_year'] && $key != "tdeletes" ? $table['use_year'] : false;
            $error= $this->obj->export_table_data();

            bar_progressCSS(1, "Tabla $key procesada ", $perc);
            if (!is_null($error)) 
                return $error;
        }

        bar_progressCSS(2, "Terminadas la exportación de todas las tablas ...", 1);
        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);
         
        $this->obj->set_output_bottom();
        
        $text= "Iniciando la escritura de los datos ... ";
        bar_progressCSS(1, $text, 0.6);
        $error= $this->obj->lote_export();
        if ($this->delete_lote($text, $error)) 
            return;

        $this->Send();
    
        $this->obj->delete_tmp_tdocumentos();

        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);

        bar_progressCSS(1, "Exportacion finalizada. Registrando las operaciones ....", 0.9);
        bar_progressCSS(2, "Exportacion finalizada. Registrando las operaciones ....", 1);

        if (is_null($error)) 
            $this->obj->_reg();  

        if (is_null($error)) {
           if ($this->obj->finalized) 
               bar_progressCSS(1, "Terminado con exito. Nada más que hacer ", 1);
           else 
               bar_progressCSS(1, "Terminado con exito un bloque. Por el tamaño del paquete se dividio en varios pedazos. ", 1);

           $file_lote= $this->obj->getFileURL();
           $file_lote.= $this->obj->if_mcrypt ? ".gz.mcrypt" : ".gz";
           $file_lote= str_replace("\\", "/", $file_lote);    
        }

        if (!$this->obj->finalized && $this->signal != 'webservice') {
            $this->finalized= false;
            $this->array_eventos= array_merge_overwrite($this->array_eventos, $this->obj->array_tmp_eventos);

            unset($this->obj);
            return $this->export($id, $this->obj->date_cutover);
        } else {
            if ($this->signal == 'shell') {
                $msgshell= "\r\nProcesos ".$this->obj_lote->array_procesos[$id]['nombre'];
                if (is_null($error)) 
                    echo "\r\nLote creado:".$file_lote;
                $msgshell.= "\r\n Fin:".date('Y-m-d H:i:');
                $msgshell.= "========================\r\n";
                echo $msgshell;
            }

            $this->finalized= true;
            $this->array_eventos= null;
        }    

        return $error;
    } // TERMINA LA FUNCION DE EXPORTACION
     
    function delete_lote($text, $error) {
        if (is_null($error)) 
            return false;
        $this->obj->delete_file_lote();
        fix_error($error, $text, $this->obj->divout);
        return true;
    }    
    
    private function Send() {
        global $config;
        
        bar_progressCSS(1, "Compactando fichero lote ...... ", 0.7);
        $this->obj->compress();

        $this->obj->if_mcrypt= $this->obj_lote->array_procesos[$this->id_destino]['mcrypt'];
        if ($this->obj->if_mcrypt) {
            bar_progressCSS(1, "Encriptando fichero lote ...... ", 0.9);
            $this->obj->encrypt_file();
        } 
        
        if ($this->signal != 'webservice') 
            $this->SendMail();
        if ($this->signal == 'webservice')
           $this->echoWeb();
    }

    private function SendMail() {
        global $config; 
    
        $this->sendmail = 0;
        $error_mail= null;
        
        if ($config->off_mail_server) 
            return null;
 
        if (!empty($this->email) && $this->signal != 'webservice') {
            bar_progressCSS(1, "Enviando actualización por correo electrónico ...... ", 0.8);

            $this->obj->OpenSMTP();
            $error_mail= $this->obj->send_mail($this->email);
            if (is_null($error_mail)) {
                $this->sendmail= 1;
                $this->obj->send_documentos($this->email);
            }    
            $this->obj->CloseSMTP();
        }
        if (!empty($error_mail) && !empty($this->email)) {
            $_observacion= "NO SE HA PODIDO ENVIAR EL LOTE ADJUNTO POR CORREO ELÉCTRONICO {$this->obj->filename}. <br/>";
            $_observacion.= $this->obj->GetObservacion();
            $this->obj->SetObservacion("<div class='alert alert-danger'>$_observacion</div>");
            fix_error($_observacion, "CORREO ELECTRONICO", $this->obj->divout);
        }
    }
    
    private function echoWeb() {
        $url= $this->obj->url;
        $url.= $this->obj->if_mcrypt ? ".gz.mcrypt" : ".gz"; 
        if (!file_exists($url)) {
            $error= "No existe el fichero $url";
            $error.= $this->signal == 'webservice' ? "ERROR_WEB5" : null;
            fix_error($error, "NO EXISTE EL LOTE REQUERIDO", $this->obj->divout);
        }    
        $output= file_get_contents($url);  
        echo $output;  
    } 
}
