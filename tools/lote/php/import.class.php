<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "lote.class.php";
include_once "db.class.php";
include_once "upload.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";

include_once "../../../php/class/code.class.php";
include_once "../../../php/class/tabla_anno.class.php";

include_once "../../../php/class/pop3/pop3.class.php";


class Timport extends Tlote {
    protected $db;
    protected $_table;
    private $obj_upload;
    private $array_dbstruct;
    private $array_size_tables;
    public $origen_motor_db;

    public $pop3;

    public function __construct($clink) {
        global $array_dbtable;

        $this->clink= $clink;
        Tlote::__construct($this->clink);

        $this->db= new Tdb($clink);
        $this->db->action= 'import';
        $dblink= $this->db->GetDB();

        $this->obj_upload= new Tupload($clink);
        $this->obj_upload->SetDB($dblink);
        $this->SetDB($dblink);

        $this->pop3= new POP3();
        
        reset($array_dbtable);
        while (list($key, $dbtable)= each($array_dbtable)) {
            $this->array_dbtable[(string)$key]= array('export'=>0, 'use_year'=>null);
            $this->array_dbtable[(string)$key]['use_year']= $dbtable['use_year'];
        }    
    }
    
    public function OpenPOP3() {
        $error= null;
        $this->cant_files= 0;
        if (isset($this->array_files)) unset($this->array_files);
        $this->array_files= array();

        if (is_null($this->pop3->mbox)) {
            $this->pop3->Connect();
        }
        if (!$this->pop3->mbox) {
            $error= "Ha fallado la conexión al servidor de correos. Servidor reporta: " . imap_last_error()."\n";
            return $error;
        }
        if (empty($this->pop3->emails_quan)) {
            $error= "El buzón de correo esta vacío. No hay lotes que cargar.";
            return $error;
        }

        $this->pop3->list_inbox();
    }

    public function get_lotes() {
        if (empty($this->pop3->emails_quan)) 
            return null;
        
        reset($this->pop3->array_emails);
        foreach ($this->pop3->array_emails as $email) {
            if (stripos($email['subject'], "TipoDirigerLote") === false) 
                continue;
            
            $this->pop3->current_email_index= $email['uid'];
            $this->pop3->current_attach_index= 2;
            $this->pop3->FetchMail($email['uid']);

            if ($this->pop3->HasAttachment()) {
                if ($this->pop3->FetchAttachment()) {
                    $from= $this->get_attachment_lote_ref();	
                    if (is_null($from)) 
                        continue;					
                    if ($this->validate_proceso($from[3]) == 0) 
                        continue;

                    $last_date= $this->get_last_date_import($from[2]);
                    $use_lote= !is_null($last_date) && (strtotime($last_date) > strtotime($from[4])) ? false : true;
                    $with_lote= is_null($this->pop3->SaveAttachment(_IMPORT_DIRIGER_DIR, $from[5])) ? false : true;
                    $use_lote= $use_lote && $with_lote;
                    
                    $array= array('name'=>$from[1], 'id_code'=>$from[2], 'file'=>$from[5], 'date'=>$from[4], 'header'=>$this->header,
                            'timestamp'=>strtotime($from[4]), 'email_number'=>$email['uid'], 'use_lote'=>$use_lote);
                    $this->array_files[]= $array;

                    ++$this->cant_files;
            }   }

            $perc= (float)$this->cant_files/$this->pop3->emails_quan;
            bar_progressCSS(2, "Email # $this->cant_files: ".$this->header, $perc, 0);
        }

        if ($this->cant_files == 0) {
            $error= "En el buzón de correos no existen nuevos lotes a importar. Nada nuevo que importar.";
            return $error;
        }

        foreach ($this->array_files as $key => $row) 
            $array_timestamp[$key]= $row['timestamp'];
        array_multisort($array_timestamp, SORT_NUMERIC, SORT_ASC, $this->array_files);
        reset($this->array_files);
    }
    
    public function get_documentos() {
        if (!$this->pop3->mbox) 
            return null;
        if (empty($this->pop3->emails_quan)) 
            return null;        
        
        $i= 0;
        reset($this->pop3->array_emails);
        foreach ($this->pop3->array_emails as $email) {
            if (stripos($email['subject'], "TipoDirigerDocAttach") === false) 
                continue;
            
            ++$i;
            $this->pop3->current_email_index= $email['uid'];
            $this->pop3->current_attach_index= 2;
            $this->pop3->FetchMail($email['uid']);

            if ($this->pop3->HasAttachment()) {
                if ($this->pop3->FetchAttachment()) {
                    if (is_null($this->pop3->SaveAttachment(_UPLOAD_DIRIGER_DIR, $this->pop3->attachment_filename))) 
                        continue;
                    $this->DeleteMail($email['uid']);
            }   }

            $perc= (float)$i/$this->pop3->emails_quan;
            bar_progressCSS(2, "Email # $i: ".$this->pop3->attachment_filename, $perc, 0);
        }
    }
  
    protected function get_attachment_lote_ref() {
        $file= $this->pop3->GetAttachmentFilename();	
        $filename= substr($file, 0, strrpos($file, ".xml.gz"));
        $ref= explode("_", $filename);
        $size= count($ref);
        if (empty($size)) 
            return null;
        $ipos= strrpos(strtoupper($filename), "LOTE_");
        if ($ipos === false) 
            return null;
		
        $len= (int)strlen($filename);
        $date= substr($filename, $len-16);
        $date= substr_replace($date, '-', 4,1);
        $date= substr_replace($date, '-', 7,1);
        $date= substr_replace($date, ' ', 10,1);
        $date= substr_replace($date, ':', 13,1);

        $code_destino= substr($filename, $len-19, 2);
        $code_origen= substr($filename, $len-22, 2);
        $name= substr($filename, $ipos+5, $len-23-($ipos+5));
 
        $file= substr($file, $ipos, strlen($file)); 
        $array= array("lote", $name, $code_origen, $code_destino, $date, $file);	
        return $array;
    }

    public function DeleteMail($index) {
        $this->pop3->current_email_index= $index;
        $this->pop3->DeleteMail();
    }
    
    function ClosePOP3() {
        $this->pop3->Close();
    }
    
    protected function get_last_date_import($id_code) {
        $sql= "select * from tsincronizacion where action = 'import' and ".convert_to("origen", "utf8")." = ".convert_to("'$id_code'", "utf8")." ";
        $sql.= "order by cronos desc limit 1";
        $result= Tbase::do_sql_show_error('get_last_date_import', $sql);
        $cant= $this->clink->num_rows($result);
        if (empty($cant)) 
            return null;

        $row= $this->clink->fetch_array($result);
        return $row['cronos'];
    }

    protected function validate_proceso($id_code) {
        $sql= "select * from tprocesos where ".convert_to("codigo", "utf8")." = ".convert_to("'$id_code'", "utf8")." ";
        $result= Tbase::do_sql_show_error('validate_proceso', $sql);
        $cant= $this->clink->num_rows($result);
        return $cant;
    }

    private function display_xml_error($error, $xml) {
        $return  = $xml[$error->line - 1] . "<br/>";
        $return .= str_repeat('-', $error->column) . "^<br/>";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
             case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
                   "<br/>  Line: $error->line" .
                   "<br/>  Column: $error->column";

        if ($error->file) {
            $return .= "<br/>  File: $error->file";
        }

        return "$return\n\n--------------------------------------------\n\n";
    }
    /**
     * Corregir errores de caracteres que no se previeron el el export
     */
    function parse_xml($xmlstring) {
        if (strlen($xmlstring) == 0) 
            return null;
        
        $array_char= array('&rdquo'=>'\"', '&ldquo'=>'\"', '&rsquo'=>'\'');
        foreach ($array_char as $code => $char) 
            $xmlstring= str_replace($code, $char, $xmlstring);
        return $xmlstring;
    }
    
    public function set_import() {
        $error= null;
        $this->url= _IMPORT_DIRIGER_DIR.$this->filename;
        $this->db->action= "import";
        
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xmlstr= file_get_contents($this->url);
        $xmlstr= $this->parse_xml($xmlstr);
        // $xmlstr = preg_replace('/&[^; ]{0,6}.?/e', "((substr('\\0',-1) == ';') ? '\\0' : '&amp;'.substr('\\0',1))", $xmlstr);
        $this->xml= simplexml_load_string($xmlstr);
        
        if (!$this->xml) {
            $error= "No ha sido posible la lectura de los datos del archivo lote ($this->filename)<br/><br/>.";
            $xml = explode("\n", $xmlstr);
          
            foreach (libxml_get_errors() as $xml_error)                
                $error.= "<br/>".$this->display_xml_error($xml_error, $xml);
            
            libxml_clear_errors();
        } 
        
        $this->db->SetYear($this->year);
        $this->db->action= $this->action;
        $this->db->divout= $this->divout;
        $this->obj_upload->divout= $this->divout;
        if (is_null($error)) 
            $error= $this->db->build_db();

        return $error;
    }
    /*
     *  crea y carga las tablas temporales    
     */
    public function read_lote() {
        global $config;
        global $array_dbtable;
        $nums_tb= count($array_dbtable);
        $obj_code= new Tcode($this->clink);
        
        if (isset($this->last_time_tables)) unset ($this->last_time_tables);
        $this->last_time_tables= array();        
        
        $i= 0;
        foreach ($this->xml->children() as $key=>$child) {
            $name= $child->getName();

            switch ($name) {
                case('observacion') :
                    $this->observacion= !empty($child[0]) ? (String)$child[0] : null;
                    break;
                case('cronos_syn') :
                    $this->fecha= !empty($child[0]) ? $child[0] : null;
                    break;
                case('cronos') :
                    $this->cronos_cut= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_cutoff') :
                    $this->date_cutoff= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_cutover') :
                    $this->date_cutover= !empty($child[0]) ? $child[0] : null;
                    break;
                case('escenario') :
                    $this->id_origen_escenario_code= !empty($child[0]) ? $child[0] : null;
                    break;
                case('motor') :
                    $this->origen_motor_db= !empty($child[0]) ? (String)$child[0] : null;
                    break;
                case('origen') :
                    $this->origen_code= !empty($child[0]) ? (String)$child[0] : null;                   
                    $prs= $obj_code->get_proceso_by_code($this->origen_code);
                    if (is_null($prs)) {
                        $this->error= "El servidor de origen no esta registrado en este sistema. ";
                        $this->error.= "Por favor, consulte a su administrador y al personal de GEMUS.";
                        return $this->error;
                    }

                    $this->year_init= min(date('Y', strtotime($this->cronos_cut)), date('Y', strtotime($this->date_cutoff)));
                    $this->year_end= date('Y', strtotime($this->date_cutover));
                    
                    $this->obj_upload->year_init= $this->year_init;
                    $this->obj_upload->year_end= $this->year_end;
                    
                    $this->origen_name= $prs['nombre'];
                    $this->id_origen= $prs['id'];
                    $this->id_origen_code= $prs['id_code'];
                    $this->origen_type= $prs['tipo'];

                    $this->if_origen_up= $_SESSION['superior_proceso_id'] == $prs['id'] ? true : false;
                    $this->if_origen_down= $_SESSION['local_proceso_id'] == $prs['id_proceso'] ? true : false;                    
                    $this->obj_upload->if_origen_down= $this->if_origen_down;
                    $this->obj_upload->if_origen_up= $this->if_origen_up;
                    
                    $this->obj_upload->SetYear($this->year);
                    $this->obj_upload->origen_code= $this->origen_code;
                    $this->obj_upload->origen_name= $this->origen_name;
                    $this->obj_upload->id_origen= $this->id_origen;
                    $this->obj_upload->id_origen_code= $this->id_origen_code;

                    $this->obj_upload->id_origen_escenario_code= $this->id_origen_escenario_code;
                    $this->id_origen_escenario= get_id_from_id_code($this->id_origen_escenario_code, 'tescenarios', $this->clink);
                    $this->obj_upload->id_origen_escenario= $this->id_origen_escenario;
                    $this->obj_upload->origen_type= $this->origen_type;
                    break;
                case('destino') :
                    $this->destino_code= $child;
                    $prs= $obj_code->get_proceso_by_code($this->destino_code);
                    $this->destino_name= $prs['nombre'];
                    $this->id_destino= $prs['id'];

                    if ($this->destino_code != $config->location) {
                        $this->error= "Este lote no esta dirigido ha este sistema. ";
                        $this->error.= "Por favor, consulte a su administrador y al personal de GEMUS.";
                        return $this->error;
                    }

                    $this->obj_upload->destino_name= $this->destino_name;
                    $this->obj_upload->id_destino= $this->id_destino;
                    break;
                case('steep_current') :
                    $this->steep_current= !empty($child[0]) ? $child[0] : null;
                    break;
                case('tb_filter') :
                    $this->tb_filter= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_tdeletes') :
                    $this->last_time_tables['_tdeletes'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_tarea') :
                    $this->last_time_tables['_treg_tarea'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_evento') :
                    $this->last_time_tables['_treg_evento'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_tproceso_eventos') :
                    $this->last_time_tables['_tproceso_eventos'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_tusuario_eventos') :
                    $this->last_time_tables['_tusuario_eventos'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_objetivo') :
                    $this->last_time_tables['_treg_objetivo'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_inductor') :
                    $this->last_time_tables['_treg_inductor'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_perspectiva') :
                    $this->last_time_tables['_treg_perspectiva'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_real') :
                    $this->last_time_tables['_treg_real'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_treg_plan') :
                    $this->last_time_tables['_treg_plan'][1]= !empty($child[0]) ? $child[0] : null;
                    break;
                case('date_tinductor_eventos') :
                    $this->last_time_tables['_tinductor_eventos'][1]= !empty($child[0]) ? $child[0] : null;
                    break;                    
                case('tabla') :
                    $this->origen_motor_db= $this->origen_motor_db ? $this->origen_motor_db : "mysql";
                    $this->obj_upload->origen_motor_db= $this->origen_motor_db;

                    $row= $child->attributes();
                    $this->table= (string)$row['name'];
                    $this->array_dbtable[$this->table]['export']= 1;

                    $r= (float)(++$i) / $nums_tb;
                    $_r= $r*100; $_r= number_format($_r,1);
                    bar_progressCSS(1, "Procesando tabla $this->table ($_r%).....", $r);                                 
                    
                    $this->read_tabla_xml($child);
                    $this->fill_table();
                    unset($this->xml->children()[$key]);
                    break;
            }
        }
        
        bar_progressCSS(1, "Cargadas todas las tablas. Por favor espere.....", 1);
        unset($this->xml);
        return $this->error;
    }
    /*
     *  lee la estructura de la tabla a partir de la informacion en el XML
     */
    private function read_tabla_xml($child) {
        if (isset($this->array_names)) 
            unset($this->array_names);
        if (isset($this->array_types)) 
            unset($this->array_types);
        if (isset($this->array_length)) 
            unset($this->array_length);
        if (isset($this->array_values)) 
            unset($this->array_values);

        $i= 0; 
        $j= 0;
        foreach ($child as $fields) {
            $name= $fields->getName();
            if ($name == 'campo') {
                foreach ($fields->attributes() as $item) {
                    $itemname= $item->getName();
                    switch($itemname) {
                        case('nombre'):
                            $this->array_names[$i]= trim((string)$item);
                            break;
                        case('tipo'):
                            $this->array_types[$i]= trim((string)$item);
                            break;
                        case('len'):
                            $this->array_length[$i]= trim((string)$item);
                            break;
                    }
                }
                ++$i;
            }

            if ($name == 'registro') {
               $this->array_values[$j]= array();
               $this->read_registro($fields, $this->array_values[$j]);
               ++$j;
            }
        }

       for ($k= 0; $k < $i; ++$k) {
            $this->array_dbstruct[(string)$this->table][(int)$k]['name']= $this->array_names[$k];
            $this->array_dbstruct[(string)$this->table][(int)$k]['type']= $this->array_types[$k];
            $this->array_dbstruct[(string)$this->table][(int)$k]['length']= $this->array_length[$k];
        }
    }
    /*
     *  lee cada uno de los registro de la tabla
     */
    private function read_registro($rows, &$array_values) {
        $i= 0;
        foreach ($rows->children() as $cell) {
            $array_values[$i]= (string)trim($cell);
            ++$i;
        }
        return $i;
    }
    /*
     *  Llenado de las tablas temporales
     */
    private function fill_table() {
        global $config;
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;

        $cronos= date('Y-m-d H:i:s');
        $i= 0; 
        $j= 0; 
        $k= 0; 
        $t= 0;
        $array_id= array();
        $count= count($this->array_names);
        $_count= count($this->array_values);

        $sql= "insert into _".$this->table." (_idx";
        foreach ($this->array_names as $fields) {
            if ((strstr($fields,'id') && strlen($fields) == 2) /*|| (strstr($fields,'id_') && !strstr($fields, '_code'))*/)
                $array_id[]= $i;

            if ($i < $count) 
                $sql.= ", ";
            $sql.= stringSQL($fields);
            ++$i;
        }
        $sql.= ") values ";

        $k= 0; 
        $j= 0;
        foreach ($this->array_values as $row) {
            $isql= null;
            $insert_row= true;

            if ($j > 0 && $j < $_count) 
                $isql.= ", ";
            $isql.= "(";

            $_idx= null;

            for ($k= 0; $k < $count; ++$k) {
                $cell= current($row);

                if ($k == 0 && is_null($_idx)) {
                    $isql.= (string)$cell; 
                    $_idx= (string)$cell; 
                    --$k; 
                    continue;
                }
                if ($k < $count) 
                    $isql.= ", ";
                $item= (is_null($cell) || strlen($cell) == 0) ? NULL : (string)$cell;
                
                $cell= next($row);

                if (($this->origen_motor_db == "mysql" && !$this->if_mysql) && (array_search($this->array_types[$k], $SQL_booltypes) !== false && $this->array_length[$k] == 1)) {
                    $item= boolean2pg($item);
                }
                if (($this->origen_motor_db == "postgres" && $this->if_mysql) && (array_search($this->array_types[$k], $SQL_booltypes) !== false)) {
                    $item= boolean($item);
                }
                if (array_search($this->array_types[$k], $SQL_timetypes) !== false) {
                    $item= setNULL_str($item, false);
                }
                elseif (array_search($this->array_types[$k], $SQL_texttypes) !== false) {
                    if (!empty($item)) {
                        $item= stripslashes($item);
                        $item= utf8_decode($item);                
                    }
                    $item= setNULL_str($item);
                }
                elseif (array_search($this->array_types[$k], $SQL_numtypes) !== false) {
                    if (is_numeric($item) && $item == 0)
                        $item= setZero($item);
                    else 
                        $item= setNULL($item);
                }
                elseif (array_search($this->array_types[$k], $SQL_blobtypes) !== false) {
                    if (is_null($item) || strlen($item) == 0) 
                        $item= 'NULL';
                    else {
                       if ($this->if_mysql) {
                           $item= hex2bin($item);
                           if ($this->origen_motor_db == "mysql") 
                               $item= addslashes($item);
                           $item= "'$item'";
                       } else {
                           $item= "decode('$item','hex')";
                       }
                    }
                } else {
                    $item= setNULL($item);
                }
                if ($this->array_names[$k] == 'cronos_syn') 
                    $item= "'$cronos'";
                if ($this->array_names[$k] == 'situs' && $item == $config->location) 
                    $insert_row= false;
                $isql.= $item;
            }
            $isql.= ")";

            if ($insert_row) {
                $sql .= $isql;
                ++$j;
            }
        }

        if ($j > 0) {
            $this->array_size_tables[$this->table]= $j;
            $this->db->db_sql_show_error("fill_table(_{$this->table})", $sql);
        }      
    }

    protected function set_array_years() {
        $sql= "select * from teventos";
        $result= $this->db_sql_show_error('set_array_years', $sql);
        $this->create_array_years($result);
        
        $this->obj_upload->year= $this->year;
        $this->obj_upload->year_init= $this->year_init;
        $this->obj_upload->year_end= $this->year_end;
    }

    private function _upload_data() {
        $this->use_year= false;
        $this->obj_upload->use_year= $this->use_year;

        reset($this->array_dbtable);
        $nums_tb= count($this->array_dbtable);
        $i= 0;
        $k= 0;

        while (list($key, $dbtable)= each($this->array_dbtable)) {
            ++$i;
            if ($key == "tdeletes") 
                continue;
            if (!$dbtable['export']) 
                continue;
            if ($dbtable['use_year']) 
                continue;

            $this->obj_upload->set_table_struct($this->array_dbstruct[$key]);

            ++$k;
            $r = (float) ($k) / (2*$nums_tb);
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(1, "Subiendo lo registros xml al servidor: " . $key . " ($_r%)..... ", $r);

            $r = (float) ($i) / $nums_tb;
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(2, "Actualizando tabla: " . $key . " ($_r%)..... ", $r);

            $this->debug_time("obj_upload->_$key");
            $function= "\$this->obj_upload->_".$key."();";
            eval($function);
            $this->debug_time("obj_upload->_$key");
        }

        return $k;
    }

    private function _upload_data_year($k) {
        $this->use_year= true;
        $this->obj_upload->use_year= $this->use_year;
        $this->obj_upload->array_years= $this->array_years;

        reset($this->array_dbtable);
        $nums_tb= count($this->array_dbtable);
        $i= 0;
        while (list($key, $dbtable)= each($this->array_dbtable)) {
            ++$i;     
            if ($key == "tdeletes") 
                continue;            
            if (!$dbtable['export']) 
                continue;
            if (!$dbtable['use_year']) 
                continue;

            $this->obj_upload->set_table_struct($this->array_dbstruct[$key]);

            ++$k;
            $r = (float) ($k) / (2*$nums_tb);
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(1, "Subiendo los datos al servidor: " . $key . " ($_r%)..... ", $r);

            $r = (float) ($i) / $nums_tb;
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(2, "Actualizando tabla: " . $key . " ($_r%)..... ", $r);

            $this->debug_time("obj_upload->_$key");
            $function= "\$this->obj_upload->_".$key."();";
            eval($function);
            $this->debug_time("obj_upload->_$key");
        }

        $this->use_year= false;
        $this->obj_upload->use_year= $this->use_year;
    }

    private function _repare_upload_data($k) {
        $this->use_year= false;
        $this->obj_upload->use_year= $this->use_year;

        reset($this->array_dbtable);
        $nums_tb= count($this->array_dbtable);
        $i= 0;
        while (list($key, $dbtable)= each($this->array_dbtable)) {
            ++$i;
            if ($key == "tdeletes") 
                continue;            
            if (!$dbtable['export']) 
                continue;
            if ($dbtable['use_year']) 
                continue;

            ++$k;
            $r = (float) ($k) / (2*$nums_tb);
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(1, "Subiendo los datos al servidor: " . $key . " ($_r%)..... ", $r);

            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(2, "Garantizando integridad de la informacion tabla: $key ($_r%)... ", $r);

            $this->debug_time("obj_upload->_i_$key");
            $function= "\$this->obj_upload->_i_".$key."();";
            eval($function);
            $this->debug_time("obj_upload->_i_$key");
        }

        return $k;
    }

    private function _repare_upload_data_year($k) {
        $this->use_year= true;
        $this->obj_upload->use_year= $this->use_year;
        $this->obj_upload->array_years= $this->array_years;
        
        reset($this->array_dbtable);
        $nums_tb= count($this->array_dbtable);
        $i= 0;
        while (list($key, $dbtable)= each($this->array_dbtable)) {
            ++$i;      
            if ($key == "tdeletes") 
                continue;            
            if (!$dbtable['export']) 
                continue;
            if (!$dbtable['use_year']) 
                continue;

            ++$k;
            $r = (float) ($k) / (2*$nums_tb);
            $_r = $r * 100; $_r = number_format($_r, 1);
            bar_progressCSS(1, "Subiendo los datos al servidor: " . $key . " ($_r%)..... ", $r);

            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(2, "Garantizando integridad de la informacion tabla: $key ($_r%)... ", $r);

            $this->debug_time("obj_upload->_i_$key");
            $function= "\$this->obj_upload->_i_".$key."();";
            eval($function);
            $this->debug_time("obj_upload->_i_$key");
        }

        $this->use_year= false;
        $this->obj_upload->use_year= $this->use_year;
    }

    public function upload_data() {
        $k= 0;
        reset($this->array_size_tables);
        foreach ($this->array_size_tables as $table => $size) 
            $this->obj_upload->array_size_tables[$table]= $size;

        $this->obj_upload->create_idx();
        $this->obj_upload->set_foreign_key_check(0);

        bar_progressCSS(1, "Subiendo los datos al servidor..... ", 0);

        $this->test_tables();
        $k= $this->_upload_data();
        $k= $this->_repare_upload_data($k);       

        $this->set_array_years();
        $this->_upload_data_year($k);       
        $this->_repare_upload_data_year($k);

        $this->obj_upload->set_foreign_key_check(1);

        bar_progressCSS(2, "Ejecutando borrado de registros 0%", 0);
        $this->obj_upload->_tdeletes();
        
        bar_progressCSS(2, "Garantizando integridad de la informacion 100%", 1);
        return $this->error;
    }
   
   /*
    * Crear las tablas correspondiente a los annos de las tareas que estan llegando
    */
    private function test_tables() {
        $sql= "select min(year(fecha_inicio_plan)), max(year(fecha_inicio_plan)) from _teventos ";
        $sql.= "where year(fecha_inicio_plan) >= 2017";
        $result= $this->db_sql_show_error('test_tables', $sql);
        $row= $this->dblink->fetch_array($result);
        if (empty($this->db_cant)) 
            return;

        $obj= new Ttabla_anno($this->dblink, null);
        $init= !empty($row[0]) ? (int)$row[0] : 2017;
        $end= !empty($row[1]) ? (int)$row[1] : (int)date('Y') + 1;

        for ($year=$init; $year <= $end; $year++) 
            $obj->Set($year);
    }
}

?>