<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../../../php/class/time.class.php";
include_once "../../../php/class/_config_prs.class.php";

include_once "lote.class.php";
include_once "db.class.php";


class Texport extends Tlote { 
    protected $db;
    protected $_table;
    private $cronos_cut_init;
    public $finalized_init;

    public function GetDB() {
        return $this->dblink;
    }

    public function __construct($clink) {   
        global $config;
        
        $this->clink= $clink;
        Tlote::__construct($this->clink);
        
        $this->db= new Tdb();    
        $this->dblink= $this->db->GetDB(); 
        $this->db->action= 'export';

        $this->id_origen= $config->local_proceso_id;
        $this->id_origen_code= $config->local_proceso_id_code;
        $this->origen_code= $config->location;
        $this->origen_type= $config->local_proceso_tipo;
        $this->id_origen_escenario_code= $_SESSION['id_escenario'];

        $this->db->id_origen= $this->id_origen;
        $this->db->id_origen_code= $this->id_origen_code;
        $this->db->id_origen_escenario_code= $this->id_origen_escenario_code;
        $this->db->origen_code= $this->origen_code;
        $this->db->origen_type= $this->origen_type;

        $this->finalized= true;
        $this->db->finalized= $this->finalized;
        
        $this->className= "Texport";
    }
    
    private function _Init() {
        $this->db->SetYear($this->year);
        $this->db->SetFecha($this->fecha);
        $this->db->date_cutoff= $this->date_cutoff;
        $this->db->date_cutover= $this->date_cutover;
        $this->db->cronos_cut= $this->cronos_cut;
        $this->db->steep_max= $this->steep_max;
        
        $this->db->cronos_cut_init= $this->cronos_cut;
        $this->db->finalized_init= $this->finalized_init; 
        $this->db->array_tmp_eventos= $this->array_tmp_eventos;

        $this->db->array_eventos= $this->array_eventos;
        $this->db->array_auditorias= $this->array_auditorias;
        $this->db->array_tareas= $this->array_tareas;

        $this->db->id_origen= $this->id_origen;
        $this->db->id_origen_code= $this->id_origen_code;
        $this->db->id_origen_chief_prs= $this->id_origen_chief_prs;
        $this->db->data_origen_chief_prs= $this->data_origen_chief_prs;
        
        $this->db->id_destino= $this->id_destino;
        $this->db->id_destino_code= $this->id_destino_code;
        $this->db->destino_type= $this->destino_type;
        $this->db->id_destino_chief_prs= $this->id_destino_chief_prs;
        
        $this->db->if_origen_up= $this->if_origen_up;
        $this->db->if_origen_down= $this->if_origen_down;
        
        $this->db->divout= $this->divout;  
        $this->db->tb_filter= $this->tb_filter;
        $this->db->last_time_tables= $this->last_time_tables;
    }
    
    private function _end() {
        $this->date_cutoff= $this->db->date_cutoff;
        $this->date_cutover= $this->db->date_cutover;
        $this->cronos_cut= $this->db->cronos_cut;
        $this->steep_current= $this->db->steep_current; 
        
        $this->finalized= $this->db->finalized;
        $this->array_tmp_eventos= $this->db->array_tmp_eventos;

        $this->array_eventos= $this->db->array_eventos;
        $this->array_auditorias= $this->db->array_auditorias;
        $this->array_tareas= $this->db->array_tareas;

        $this->cant_used_tables= $this->db->cant_used_tables;
    }

    public function export_db() {
        $this->_Init();
        $error= $this->db->export_db();
        $this->_end();
        
        return $error;
    }
    
    public function set_output_header() {
        global $config;
        global $array_dbtable;
        $xmldata= null;
        
        $xmlstr= "<?xml version='1.0' encoding='UTF-8' standalone='yes' ?><lote></lote>";
        $this->xml= new SimpleXMLElement($xmlstr);
        
        $this->xml->addChild('host', $_SESSION['ip_app']);
        $this->xml->addChild('database', $_SESSION['db_name']);
        $this->xml->addChild('motor', $_SESSION['_DB_SYSTEM']);
        $this->xml->addChild('cronos_syn', !empty($this->fecha) ? $this->fecha : date('Y').'-01-01');
        $this->xml->addChild('cronos', $this->cronos);
        $this->xml->addChild('cronos_cut', $this->cronos_cut);
        $this->xml->addChild('date_cutoff', $this->date_cutoff);
        $this->xml->addChild('date_cutover', $this->date_cutover);
        $this->xml->addChild('finalized', $this->finalized ? 1 : 0);
        $this->xml->addChild('escenario', $_SESSION['id_escenario_code']);
        $this->xml->addChild('origen', $config->location);
        $this->xml->addChild('destino', $this->destino_code);
        $this->xml->addChild('usuario', $_SESSION['nombre']);
        $this->xml->addChild('observacion', $this->observacion);        

        $xmldata= $this->xml->addChild('cant_tables',$this->cant_used_tables);
        reset($array_dbtable);
        while (list($key, $dbtable)= each($array_dbtable)) {
            if (!$dbtable['export']) 
                continue;
            $xmltable= $xmldata->addChild('tabla', $key);
            $xmltable->addAttribute('size', $dbtable['size']);
        }
    }
    
    public function set_output_bottom() {
        $fecha= !empty($this->fecha) ? $this->fecha : date('Y').'-01-01';
        
        $this->xml->addChild('fecha', $fecha);
        $this->xml->addChild('steep_current', $this->steep_current);
        $this->xml->addChild('tb_filter', $this->tb_filter);
        $this->xml->addChild('date_tdeletes', $this->last_time_tables['_tdeletes'][1]);
        $this->xml->addChild('date_treg_tarea', $this->last_time_tables['_treg_tarea'][1]);
        $this->xml->addChild('date_treg_evento', $this->last_time_tables['_treg_evento'][1]);
        $this->xml->addChild('date_tproceso_eventos', $this->last_time_tables['_tproceso_eventos'][1]);
        $this->xml->addChild('date_tusuario_eventos', $this->last_time_tables['_tusuario_eventos'][1]);
        $this->xml->addChild('date_treg_objetivo', $this->last_time_tables['_treg_objetivo'][1]);
        $this->xml->addChild('date_treg_inductor', $this->last_time_tables['_treg_inductor'][1]);
        $this->xml->addChild('date_treg_perspectiva', $this->last_time_tables['_treg_perspectiva'][1]);
        $this->xml->addChild('date_treg_real', $this->last_time_tables['_treg_real'][1]);
        $this->xml->addChild('date_treg_plan', $this->last_time_tables['_treg_plan'][1]);
        $this->xml->addChild('date_tinductor_eventos', $this->last_time_tables['_tinductor_eventos'][1]);
    }
    
    public function create_file_lote() {
        $error= "No ha sido posible crear el fichero para escritura.";
        $this->url= _EXPORT_DIRIGER_DIR;
        
        if (empty($this->filename)) {
            $file= null;
            $date= date('Y-m-d-H-i', strtotime($this->cronos));
            $file= "lote_{$_SESSION['empresa']}_{$this->origen_code}_{$this->destino_code}_{$date}.xml";
            $file= str_replace(":","_",$file);
            $file= str_replace("-","_",$file);
            $file= str_replace(" ","_",$file);
            $this->filename= $file;
        }
        
        $this->url.= $this->filename;
        $this->pfile= fopen($this->url,'w+');
        if (empty($this->pfile)) 
            return $error;
        
        fclose($this->pfile);
        return NULL;
    }

    public function delete_file_lote() {
        unlink($this->url);
    }

    public function setTable($id) {
        $this->table= $id;
        if ($id == "tusuario_eventos") 
            $_table= "tusuario_eventos_{$this->year}";
        if ($id == "tproceso_eventos") 
            $_table= "tproceso_eventos_{$this->year}";
        if ($id == "treg_evento") 
            $_table= "treg_evento_{$this->year}";

        $this->_table= "_$id";
    }
 
    public function export_table_data() {
        global $last_time_tables;
        
        $msg= "Exportando tabla $this->table ....";
        $name= array();
        $type= array();
        $len= array();
        
        $i= 0;
        $this->year= $this->db->GetYear();
        $table= $this->use_year ? "{$this->table}_{$this->year}" : $this->table;
        $fields= $this->db->dblink->fields($table, false);
        foreach ($fields as $row) {
            $name[$i]= $row['Field'];
            $type[$i]= strtoupper($row['Type']);
            $len[$i]= $row['char_length'];
            ++$i;
        } 
        
        $array_tables_exempt= array("_teventos", "_ttareas", "_tauditorias", "_ttematicas", "_tdebates");
        $array_tables_fix= array("_tusuarios", "_tprocesos");
        $array_tables_only_origen= array("_triesgos", "_tnotas", "_tindicadores", "_tobjetivos", "_tinductores",
                                    "_tperspectivas");
        
        $this->cronos_under= $this->db->cronos_under;
        if (!array_key_exists($this->_table, $this->last_time_tables))
            $cronos_cut= $this->cronos_cut_init;
        else {
            $cronos_cut= $last_time_tables[$this->_table];
            if (empty($cronos_cut))
                $cronos_cut= !empty($this->cronos_under) ? $this->cronos_under : $this->cronos_cut;
        }
        if (!$this->finalized_init && array_search($this->_table, $array_tables_exempt) !== false)
            $cronos_cut= null;
        if (array_search($this->_table, $array_tables_fix) !== false) 
            $cronos_cut= null;
        $sql= "select distinct * from $this->_table where 1 ";
        if (array_search($this->_table, $array_tables_fix) !== false) 
            $sql.= "and $this->_table.situs != '$this->destino_code' ";
        if (!empty($cronos_cut) && !$array_dbtable[$this->table]['basic_data']) {
            $sql.= "and ($this->_table.cronos >= '$cronos_cut' ";
            $sql.= "or ($this->_table.cronos_syn is not null and $this->_table.cronos_syn >= '$cronos_cut')) ";          
        }
        
        if (array_search($this->_table, $array_tables_only_origen) !== false) 
            $sql.= "and $this->_table.id_proceso != $this->id_destino ";
        $sql.= "order by cronos asc";

        $result= $this->db->db_sql_show_error("export_table_data($this->_table)", $sql);
        $cant_fields = $this->db->dblink->num_fields($result);
        $cant_rows= $this->db->dblink->num_rows($result);

        if (!is_null($this->error)) 
            return $this->error;
        if (empty($cant_rows)) {
            $this->delete_tmp_table();
            return null;    
        } 
        
        $this->_export_table_data($result, $name, $type, $len, $cant_fields, $cant_rows);
    }
    
    private function _export_table_data($result, $name, $type, $len, $cant_fields, $cant_rows) {
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes;
        global $array_dbtable;
        /*
        $arraychr= array('À'=>'&Agrave;', 'à'=>'&agrave;', 'Á'=>'&Aacute;', 'á'=>'&aacute;', 'Â'=>'&Acirc;', 'â'=>'&acirc;', 'Ã'=>'&Atilde;',
            'ã'=>'&atilde;', 'Ä'=>'&Auml;', 'ä'=>'&auml;', 'Å'=>'&Aring;', 'å'=>'&aring;', 'Æ'=>'&AElig;', 'æ'=>'&aelig;', 'Ç'=>'&Ccedil;',
            'ç'=>'&ccedil;', 'Ð'=>'&ETH;', 'ð'=>'&eth;', 'È'=>'&Egrave;', 'è'=>'&egrave;', 'É'=>'&Eacute;', 'é'=>'&eacute;', 'Ê'=>'&Ecirc;',
            'ê'=>'&ecirc;', 'Ë'=>'&Euml;', 'ë'=>'&euml;', 'Ì'=>'&Igrave;', 'ì'=>'&igrave;', 'Í'=>'&Iacute;', 'í'=>'&iacute;', 'Î'=>'&Icirc;',
            'î'=>'&icirc;', 'Ï'=>'&Iuml;', 'ï'=>'&iuml;', 'Ñ'=>'&Ntilde;', 'ñ'=>'&ntilde;', 'Ò'=>'&Ograve;', 'ò'=>'&ograve;', 'Ó'=>'&Oacute;',
            'ó'=>'&oacute;', 'Ô'=>'&Ocirc;', 'ô'=>'&ocirc;', 'Õ'=>'&Otilde;', 'õ'=>'&otilde;', 'Ö'=>'&Ouml;', 'ö'=>'&ouml;', 'Ø'=>'&Oslash;',
            'ø'=>'&oslash;', 'Œ'=>'&OElig;', 'œ'=>'&oelig;', 'ß'=>'&szlig;', 'Þ'=>'&THORN;', 'þ'=>'&thorn;', 'Ù'=>'&Ugrave;', 'ù'=>'&ugrave;',
            'Ú'=>'&Uacute;', 'ú'=>'&uacute;', 'Û'=>'&Ucirc;', 'û'=>'&ucirc;', 'Ü'=>'&Uuml;', 'ü'=>'&uuml;', 'Ý'=>'&Yacute;', 'ý'=>'&yacute;',
            'Ÿ'=>'&Yuml;', 'ÿ'=>'&yuml;');
        */
        $arraychr= array('&Agrave;'=>'À', '&agrave;'=>'à', '&Aacute;'=>'Á', '&aacute;'=>'á', '&Acirc;'=>'Â', '&acirc;'=>'â', '&Atilde;'=>'Ã',
            '&atilde;'=>'ã', '&Auml;'=>'Ä', '&auml;'=>'ä', '&Aring;'=>'Å', '&aring;'=>'å', '&AElig;'=>'Æ', '&aelig;'=>'æ', '&Ccedil;'=>'Ç',
            '&ccedil;'=>'ç', '&ETH;'=>'Ð', '&eth;'=>'ð', '&Egrave;'=>'È', '&egrave;'=>'è', '&Eacute;'=>'É', '&eacute;'=>'é', '&Ecirc;'=>'Ê',
            '&ecirc;'=>'ê', '&Euml;'=>'Ë', '&euml;'=>'ë', '&Igrave;'=>'Ì', '&igrave;'=>'ì', '&Iacute;'=>'Í', '&iacute;'=>'í', '&Icirc;'=>'Î',
            '&icirc;'=>'î', '&Iuml;'=>'Ï', '&iuml;'=>'ï', '&Ntilde;'=>'Ñ', '&ntilde;'=>'ñ', '&Ograve;'=>'Ò', '&ograve;'=>'ò', '&Oacute;'=>'Ó',
            '&oacute;'=>'ó', '&Ocirc;'=>'Ô', '&ocirc;'=>'ô', '&Otilde;'=>'Õ', '&otilde;'=>'õ', '&Ouml;'=>'Ö', '&ouml;'=>'ö', '&Oslash;'=>'Ø',
            '&oslash;'=>'ø', '&OElig;'=>'Œ', '&oelig;'=>'œ', '&szlig;'=>'ß', '&THORN;'=>'Þ', '&thorn;'=>'þ', '&Ugrave;'=>'Ù', '&ugrave;'=>'ù',
            '&Uacute;'=>'Ú', '&uacute;'=>'ú', '&Ucirc;'=>'Û', '&ucirc;'=>'û', '&Uuml;'=>'Ü', '&uuml;'=>'ü', '&Yacute;'=>'Ý', '&yacute;'=>'ý',
            '&Yuml;'=>'Ÿ', '&yuml;'=>'ÿ', '&ndash;'=> "", '& '=> "", ' & '=> "y", '&amp;'=> 'y', '&sup'=>'^', '&sub'=>'~', 
            '&bull;'=>'º', '&middot;'=>'º', '&ndash;'=>'-', '&mdash;'=>'-', '&lt;'=>'<', '&gt;'=>'>', '&le;'=>'<=', '&ge;'=>'>=', 
            '&rdquo;'=>'\"', '&ldquo;'=>'\"', '&rsquo;'=>'\'', '&quot;'=> '\"', '&iexcl;'=> '¡', '&cent;'=> '¢', '&pound;'=> '£',  
            '&curren;'=> '¤', '&yen;'=> '¥', '&brvbar;'=> '¦', '&sect;'=> '§', '&uml;'=> '¨', '&copy;'=> '©', '&ordf;'=> 'ª', '&not;'=> '¬',  
            '&shy;'=> '', '&reg;'=> '®', '&macr;'=> '¯', '&deg;'=> '°', '&plusmn;'=> '±', '&acute;'=> '´' ,'&micro;'=> 'µ', '&para;'=> '¶', 
            '&ordm;'=> 'º', '&frac14;'=> '¼', '&frac12;'=> '½', '&frac34;'=> '¾', "&nbsp;"=> "");

        $cronos_cut= null;
        $nums_row= 0;
        $icronos= null;
        
        $i= 0;
        $xmltable= $this->xml->addChild('tabla');
        $xmltable->addAttribute('name',$this->table);
        
        for ($i= 0; $i < $cant_fields; $i++) {
            if ($name[$i] == 'cronos')
                $icronos= $i;
            $fields= $xmltable->addChild('campo');
            $fields->addAttribute('nombre', $name[$i]);
            $fields->addAttribute('tipo', $type[$i]);
            $fields->addAttribute('len', $len[$i]); 
        }

        $j= 0;
        $this->dblink->data_seek($result);  
        while ($row= $this->dblink->fetch_array($result, MYSQLI_NUM)) {
            $cronos_cut= $row[$icronos];

            $treg= $xmltable->addChild('registro');
            $treg->addAttribute('id', $j);

            $i= 0;
            foreach ($row as $value) {
               $_value= is_null($value) ? " " : $value;

                if (!is_null($value)) {
                    if (array_search(strtoupper($type[$i]), $SQL_blobtypes) !== false) {
                        if (strlen($_value) > 0) {
                            $_value= bin2hex($_value); 
                            $_value= "\n$_value\n";
                        }
                    } elseif (array_search(strtoupper($type[$i]), $SQL_texttypes) !== false) {
                        // REPARANDO ERRORES DE CODIFICACION -----------------------
                        reset($arraychr);
                        while (list($key, $char)= each($arraychr)) 
                            $_value= str_replace($key, $char, $_value);
                        //-------------------------------------------------------
                        $_value= purge_html($_value);
                        $_value= CleanNonACIIchar($_value);
                        $_value= strip_tags($_value);
                        $_value= utf8_encode($_value);
                        $_value= addslashes($_value);
                        $_value= "\n$_value\n";
                    }
                }

                $treg->addChild($name[$i], $_value);
                ++$i;         
            }
            
            ++$j;
            $perc= (float)$j/$cant_rows;
            bar_progressCSS(2, "Exportando registro de la tabla $this->table .... ", $perc);            
        }
        
        if (array_key_exists($this->_table, $this->last_time_tables)) {
            $this->last_time_tables[$this->_table][1]= $cronos_cut;
            $this->last_time_tables[$this->_table][2]= $j;
        } 

        if ($this->table != "tdocumentos") 
            $this->delete_tmp_table();
        
        bar_progressCSS(2, "Terminada, no hay más registro que exportar en tabla: $this->table ...", 1);
        return NULL;
    }

    private function delete_tmp_table($table= null) {
        $table= !is_null($table) ? $table : $this->_table;
        $sql= "DROP TABLE IF EXISTS $table";      
        $result= $this->db->db_sql_show_error("export_table_data($this->_table)", $sql);
    }
    
    public function delete_tmp_tdocumentos() {
        $this->delete_tmp_table("_tmp_tref_documentos");
        $this->delete_tmp_table("_tdocumentos");
    }
    
    public function send_documentos($mail_address) {
        if (!$this->db->if_tdocumentos) 
            return;
        
        $sql= "select distinct * from _tdocumentos where situs != '$this->destino_code' ";
        if (!empty($this->date_cutoff)) 
            $sql.= "and (cronos >= '$this->date_cutoff' or (cronos_syn is not null and cronos_syn >= '$this->date_cutoff')) ";       
        $result= $this->db->db_sql_show_error("send_documentos", $sql);
        $cant= $this->db->cant;
        if (empty($cant)) 
            return;
        
        $i= 0;
        while ($row= $this->db->dblink->fetch_array($result)) {
            $url= _UPLOAD_DIRIGER_DIR.$row['url'];
            $this->send_attachment($mail_address, $url, $row['url']);

            ++$i;
            $perc= (float)$i/$cant;
            bar_progressCSS(2, "Enviado documento {$row['nombre']} por correo electrónica ....", $perc);
        }
    }

    public function lote_export() {
        $this->pfile= fopen($this->url,'w+');
        $error= null;
      
        if ($this->pfile) 
            fprintf($this->pfile, "%s", $this->xml->asXML());
        else 
            $error= "Se ha producido un error intentando escribir los datos al ficheros.";
        
        unset($this->xml);
        fclose($this->pfile);
        return $error;            
    }
    
    private function get_chain_names() {
        $chain= "";
        foreach ($this->array_procesos as $array) 
            $chain.= "_".$array['codigo'];
        return $chain;
    }   
}
?>