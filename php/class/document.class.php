<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 8/6/15
 * Time: 7:53 a.m.
 */

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";


class Tdocumento extends Tbase_planning {
    public $url;
    public  $size, $type;
    public $array_files,
            $cant_files,
            $header;

    protected $keywords;
    protected $if_tdocumentos;
    protected $sendmail;

    public function get_id_documento_code() {
        return $this->id_documento_code;
    }
    public function set_id_documento_code($id) {
        $this->id_documento_code = $id;
    }
    public function SetKeywords($id) {
        $this->keywords = $id;
    }
    public function GetKeywords() {
        return $this->keywords;
    }
    public function SetSendMail($id = 1) {
        $this->sendmail = $id;
    }
    public function GetSendMail() {
        return $this->sendmail;
    }

    public function __construct($clink= null) {
        global $config;

        Tbase_planning::__construct($clink);

        $this->cant_files= 0;
        $this->clink= $clink;
        $this->if_tdocumentos= false;

        if (ini_set("upload_max_filesize") < $config->maxfilesize)
            $result= ini_set("upload_max_filesize", $config->maxfilesize."M");
        if (ini_set("post_max_size") < $config->maxfilesize)
            $result= ini_set("post_max_size", $config->maxfilesize."M");       
    }

    public function Set($id= null) {
        $this->id_documento= !empty($id) ? $id : $this->id_documento;
        $this->id= $this->id_documento;

        $sql= "select * from tdocumentos where id = $this->id_documento ";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_code = $row['id_code'];
            $this->id_documento_code= $this->id_code;

            $this->filename= stripslashes($row['nombre']);
            $this->url= $row['url'];
            $this->descripcion= stripslashes($row['descripcion']);
            $this->keywords= stripslashes($row['keywords']);

            $this->id_responsable= $row['id_responsable'];
            $this->id_usuario= $row['id_usuario'];

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->id_archivo= $row['id_archivo'];
            $this->id_archivo_code= $row['id_archivo_code'];

            $this->kronos= $row['cronos'];
        }
    }

    function get_url() {
        echo _UPLOAD_DIRIGER_DIR.$this->url;
    }

    public function add() {
        $this->id_documento= null;

        $id_proceso= setNULL($this->id_proceso);
        $id_proceso_code= setNULL_str($this->id_proceso_code);

        $descripcion= setNULL_str($this->descripcion);
        $keywords= setNULL_str($this->keywords);
        $nombre= setNULL_str($this->filename);
        $url= setNULL_str($this->url);

        $id_archivo= setNULL($this->id_archivo);
        $id_archivo_code= setNULL_str($this->id_archivo_code);

        $year= setNULL_empty($this->year);
        $month= setNULL_empty($this->month);

        $sql= "insert into tdocumentos (nombre, url, descripcion, keywords, id_responsable, id_usuario, ";
        $sql.= "id_proceso, id_proceso_code, cronos, situs, id_archivo, id_archivo_code, year, month) values ";
        $sql.= "($nombre, $url, $descripcion, $keywords, $this->id_responsable, $this->id_usuario, $id_proceso, ";
        $sql.= "$id_proceso_code, '$this->cronos', '$this->location', $id_archivo, $id_archivo_code, $year, $month) ";
        $result= $this->do_sql_show_error('add', $sql, false);

        if ($result) {
            $this->id= $this->clink->inserted_id("tdocumentos");
            $this->id_documento= $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tdocumentos','id','id_code');
            $this->id_code= $this->obj_code->get_id_code();
            $this->id_documento_code= $this->id_code;

        } else {
            $sql= "select id, id_code from tdocumentos where url = '$this->url' ";
            $result= $this->do_sql_show_error('add', $sql, false);

            $row= $this->clink->fetch_array($result);
            $this->id_documento= $row['id'];
            $this->id_documento_code= $row['id_code'];

            $this->id= $this->id_documento;
            $this->id_code= $this->id_documento_code;

            if (!empty($this->id_documento))
                Tdocumento::update();
        }

        return $this->error;
    }

    public function add_ref() {
        $id_evento= setNULL($this->id_evento);
        $id_evento_code= setNULL_str($this->id_evento_code);

        $id_tarea= setNULL($this->id_tarea);
        $id_tarea_code= setNULL_str($this->id_tarea_code);

        $id_auditoria= setNULL($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);

        $id_proyecto= setNULL($this->id_proyecto);
        $id_proyecto_code= setNULL_str($this->id_proyecto_code);

        $id_riesgo= setNULL($this->id_riesgo);
        $id_riesgo_code= setNULL_str($this->id_riesgo_code);

        $id_nota= setNULL($this->id_nota);
        $id_nota_code= setNULL_str($this->id_nota_code);

        $id_requisito= setNULL($this->id_requisito);
        $id_requisito_code= setNULL_str($this->id_requisito_code);

        $id_indicador= setNULL($this->id_indicador);
        $id_indicador_code= setNULL_str($this->id_indicador_code);

        $sql= "insert into tref_documentos (id_documento, id_documento_code, id_evento, id_evento_code, id_tarea, ";
        $sql.= "id_tarea_code, id_auditoria, id_auditoria_code, id_proyecto, id_proyecto_code, id_riesgo, id_riesgo_code, ";
        $sql.= "id_nota, id_nota_code, id_requisito, id_requisito_code, id_indicador, id_indicador_code, cronos, situs) ";
        $sql.= "values ($this->id, '$this->id_code', $id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, ";
        $sql.= "$id_auditoria_code, $id_proyecto, $id_proyecto_code, $id_riesgo, $id_riesgo_code, $id_nota, $id_nota_code, ";
        $sql.= "$id_requisito, $id_requisito_code, $id_indicador, $id_indicador_code, '$this->cronos', ";
        $sql.= "'$this->location') ";

        $result= $this->do_sql_show_error('add_ref', $sql, false);
        if(!$result) {
            if (stripos($this->error, 'duplicate') !== false || stripos($this->error, 'duplicada') !== false)
                $this->error= null;
        }
        return $this->error;
    }

    public function update() {
        $id_proceso= setNULL($this->id_proceso);
        $id_proceso_code= setNULL_str($this->id_proceso_code);

        $descripcion= setNULL_str($this->descripcion);
        $keywords= setNULL_str($this->keywords);

        $sql= "update tdocumentos set descripcion= $descripcion, keywords= $keywords, id_responsable= $this->id_responsable, ";
        $sql.= "id_usuario= $this->id_usuario, id_responsable= $this->id_responsable, cronos= '$this->cronos', situs= '$this->location'  ";
        if (!empty($this->id_proceso))
            $sql.= ", id_proceso= $id_proceso, id_proceso_code= $id_proceso_code ";
        $sql.= "where id= $this->id_documento ";

        $this->do_sql_show_error('add', $sql);
        return $this->error;
    }

    protected function _create_tmp_tdocumentos() {
        $sql= "drop table if exists ".stringSQL("_tdocumentos");
        $this->do_sql_show_error('create_tmp_tdocumentos', $sql);

        $fields= $this->clink->fields("tdocumentos");
        $nums_fields= count($fields);

        $sql= "CREATE TEMPORARY TABLE _tdocumentos (";
        $i= 0;
        foreach ($fields as $field) {
            $sql.= showFieldSQL($field);
            ++$i;
            $sql.= $i < $nums_fields ? ", \r\n" : "";
        }
        $sql.= "); ";

        $this->do_sql_show_error('_create_tmp_tdocumentos', $sql);
        return $this->error;
    }

    public function listar_by_usuarios($flag= true, $keywords= null) {
        $this->_create_tmp_tdocumentos();

        if (empty($this->id_usuario))
            return null;

        $sql= "insert into _tdocumentos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : " ";
        $sql.= "select distinct tdocumentos.* from tdocumentos, tusuario_documentos where ";
        $sql.= "(tdocumentos.id_responsable = $this->id_usuario or tdocumentos.id_usuario = $this->id_usuario) ";
        $sql.= "or (tdocumentos.id = tusuario_documentos.id_documento and (tusuario_documentos.id_usuario = $this->id_usuario ";
        $sql.= "or tusuario_documentos.id_grupo in (select id_grupo from tusuario_grupos where id_usuario = $this->id_usuario))) ";

        $result= $this->do_sql_show_error('listar_by_usuarios', $sql);
        $this->if_tdocumentos= true;

        if ($flag)
            return $result;

        return $this->_create_array($result, $keywords);
    }

    public function listar($flag= true, $keywords= null) {
        $tdocumentos= $this->if_tdocumentos ? "_tdocumentos" : "tdocumentos";

        $sql= "select distinct $tdocumentos.*, id_evento, id_auditoria, id_proyecto, id_riesgo, id_nota, id_indicador, ";
        $sql.= "id_requisito from $tdocumentos, tref_documentos where $tdocumentos.id = tref_documentos.id_documento ";
        $id_item= false;
        if (!empty($this->id_evento) && empty($this->id_auditoria)) {
            $sql .= "and id_evento= $this->id_evento ";
            $id_item = true;
        }
        if (!empty($this->id_auditoria)) {
            $sql .= "and id_auditoria= $this->id_auditoria ";
            $id_item = true;
        }
        if (!empty($this->id_tarea)) {
            $sql .= "and id_tarea= $this->id_tarea ";
            $id_item = true;
        }        
        if (!empty($this->id_proyecto)) {
            $sql .= "and id_proyecto= $this->id_proyecto ";
            $id_item = true;
        }
        if (!empty($this->id_riesgo)) {
            $sql .= "and id_riesgo= $this->id_riesgo ";
            $id_item = true;
        }
        if (!empty($this->id_nota)) {
            $sql .= "and id_nota= $this->id_nota ";
            $id_item = true;
        }
        if (!empty($this->id_requisito)) {
            $sql .= "and id_requisito= $this->id_requisito ";
            $id_item = true;
        }
        if (!empty($this->id_indicador)) {
            $sql .= "and id_indicador= $this->id_indicador ";
            $id_item = true;
        }
        if (!empty($this->year)) {
            $sql .= "and (year is null or year= $this->year) ";
            $id_item = true;
        }
        if (!empty($this->month)) {
            $sql .= "and (month is null or month= $this->month) ";
            $id_item = true;
        }
        if ((!empty($this->year) || !empty($this->month)) && !$id_item) {
            $month= !empty($this->month) ? $this->month : 1;
            $day= !empty($this->day) ? $this->day : 1;;
            $date= $this->year."-".str_pad($month, 2, '0', STR_PAD_LEFT)."-".str_pad($day, 2, '0', STR_PAD_LEFT)." 00:00:00";

            $sql.= "and $tdocumentos.cronos >= '$date'";
        }
        $sql.= "order by nombre asc, $tdocumentos.cronos desc";

        $result= $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        return $this->_create_array($result, $keywords);
    }

    private function _create_array($result, $keywords= null) {
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($keywords) && is_string($keywords)) {
                $fix= $this->search_keywords($keywords, $row['nombre'], $row['keywords'], $row['descripcion']);
                if (empty($fix))
                    continue;
            }

            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'url'=>stripslashes($row['url']), 'nombre'=>stripslashes($row['nombre']),
                    'descripcion'=>stripslashes($row['descripcion']), 'keywords'=> stripslashes($row['keywords']), 
                    'id_evento'=> $row['id_evento'], 'id_tarea'=>$row['id_tarea'], 
                    'id_proyecto'=>$row['id_proyecto'], 'id_auditoria'=> $row['id_auditoria'], 'id_riesgo'=> $row['id_riesgo'],
                    'id_nota'=> $row['id_nota'], 'id_proceso'=>$row['id_proceso'], 'id_responsable'=>$row['id_responsable'],
                    'id_requisito'=>$row['id_requisito'], 'id_indicador'=> $row['id_indicador'], 'id_usuario'=>$row['id_usuario'], 
                    'origen_data'=>$row['origen_data'], 'cronos'=>$row['cronos'], 'fix'=>$fix);

            $this->array_files[$row['id']]= $array;
        }

        $array_cronos= null;

        if (!empty($keywords) && is_string($keywords)) {
            foreach ($this->array_files as $array) {
                $array_fix[]= $array['fix'];
                $array_nombre[]= $array['nombre'];
                $array_cronos[]= $array['cronos'];
            }

            reset($this->array_files);
            array_multisort($array_fix, SORT_DESC, $array_nombre, SORT_ASC, $array_cronos, SORT_DESC, (Array)$this->array_files);
        }

        reset($this->array_files);
        return $this->array_files;
    }

    public function get_documentos($flag= true) {
        $flag= !is_null($flag) ? $flag : true;

        $sql= "select distinct tdocumentos.*, tref_documentos.id as _id, tdocumentos.id as _id_documento ";
        $sql.= "from tdocumentos, tref_documentos where tdocumentos.id = tref_documentos.id_documento ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        if (!empty($this->id_tarea))
            $sql.= "and id_tarea = $this->id_tarea ";            
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        if (!empty($this->id_nota))
            $sql.= "and id_nota = $this->id_nota ";
        if (!empty($this->id_proyecto))
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if (!empty($this->id_requisito))
            $sql.= "and id_requisito = $this->id_requisito ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        $sql.= "order by tdocumentos.cronos desc";

        $result= $this->do_sql_show_error('get_documentos', $sql);
        if ($flag)
            return $result;
        if (!$result || $this->cant == 0)
            return null;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_documentos[$i++]= array('id'=>$row['_id'], 'id_documento'=>$row['id'], 'id_documento_code'=>$row['id_code'],
                            'id_usuario'=>$row['id_usuario'], 'nombre'=>$row['nombre'], 'url'=>$row['url'], 'descripcion'=>$row['descripcion'],
                            'keywords'=>$row['keywords'], 'id_proceso'=>$row['id_proceso'], 'id_responsable'=>$row['id_responsable'],
                            'id_evento'=>$row['id_evento'],'cronos'=>$row['cronos']);
        }
        $this->cant= $i;
        return $this->array_documentos;
    }

    private function _get_documento_ref($id) {
        $sql= "select * from tref_documentos where id = $id";
        $result= $this->do_sql_show_error('_get_documento_ref', $sql);

        return $result && $this->cant > 0 ? $this->clink->fetch_array($result) : null;
    }

    private function _add_ref($row) {
        $id_evento= !empty($this->id_evento) ? $this->id_evento : $row['id_evento'];
        if (!empty($this->id_evento)) {
            if (empty($this->id_evento_code))
                $this->id_evento_code= get_code_from_table ('teventos', $this->id_evento);
            $id_evento_code= $this->id_evento_code;
        } else
            $id_evento_code= $row['id_evento_code'];

        $id_tarea= !empty($this->id_tarea) ? $this->id_tarea : $row['id_tarea'];
        if (!empty($this->id_tarea)) {
            if (empty($this->id_tarea_code))
                $this->id_tarea_code= get_code_from_table ('ttareas', $this->id_tarea);
            $id_tarea_code= $this->id_tarea_code;
        } else
            $id_tarea_code= $row['id_tarea_code'];
                        
        $id_auditoria= !empty($this->id_auditoria) ? $this->id_auditoria : $row['id_auditoria'];
        if (!empty($this->id_auditoria)) {
            if (empty($this->id_auditoria_code))
                $this->id_auditoria_code= get_code_from_table ('tauditorias', $this->id_auditoria);
            $id_auditoria_code= $this->id_auditoria_code;
        } else
            $id_auditoria_code= $row['id_auditoria_code'];

        $id_proyecto= !empty($this->id_proyecto) ? $this->id_proyecto : $row['id_proyecto'];
        if (!empty($this->id_proyecto)) {
            if (empty($this->id_proyecto_code))
                $this->id_proyecto_code= get_code_from_table ('tproyectos', $this->id_proyecto);
            $id_proyecto_code= $this->id_proyecto_code;
        } else
            $id_proyecto_code= $row['id_proyecto_code'];

        $id_riesgo= !empty($this->id_riesgo) ? $this->id_riesgo : $row['id_riesgo'];
        if (!empty($this->id_riesgo)) {
            if (empty($this->id_riesgo_code))
                $this->id_riesgo_code= get_code_from_table ('triesgos', $this->id_riesgo);
            $id_riesgo_code= $this->id_riesgo_code;
        } else
            $id_riesgo_code= $row['id_riesgo_code'];

        $id_nota= !empty($this->id_nota) ? $this->id_nota : $row['id_nota'];
        if (!empty($this->id_nota)) {
            if (empty($this->id_nota_code))
                $this->id_nota_code= get_code_from_table ('tnotas', $this->id_nota);
            $id_nota_code= $this->id_nota_code;
        } else
            $id_nota_code= $row['id_nota_code'];

        $id_requisito= !empty($this->id_requisito) ? $this->id_requisito : $row['id_requisito'];
        if (!empty($this->id_requisito)) {
            if (empty($this->id_requisito_code))
                $this->id_requisito_code= get_code_from_table ('tlista_requisitos', $this->id_requisito);
            $id_requisito_code= $this->id_requisito_code;
        } else
            $id_requisito_code= $row['id_requisito_code'];

        $id_indicador= !empty($this->id_indicador) ? $this->id_indicador : $row['id_indicador'];
        if (!empty($this->id_indicador)) {
            if (empty($this->id_indicador_code))
                $this->id_requisito_code= get_code_from_table ('tindicadores', $this->id_indicador);
            $id_indicador_code= $this->id_indicador_code;
        } else
            $id_indicador_code= $row['id_indicador_code'];

        $id_evento= setNULL($id_evento);
        $id_evento_code= setNULL_str($id_evento_code);
        
        $id_tarea= setNULL($id_tarea);
        $id_tarea_code= setNULL_str($id_tarea_code);

        $id_auditoria= setNULL($id_auditoria);
        $id_auditoria_code= setNULL_str($id_auditoria_code);

        $id_proyecto= setNULL($id_proyecto);
        $id_proyecto_code= setNULL_str($id_proyecto_code);

        $id_riesgo= setNULL($id_riesgo);
        $id_riesgo_code= setNULL_str($id_riesgo_code);

        $id_nota= setNULL($id_nota);
        $id_nota_code= setNULL_str($id_nota_code);

        $id_requisito= setNULL($id_requisito);
        $id_requisito_code= setNULL_str($id_requisito_code);

        $id_indicador= setNULL($id_indicador);
        $id_indicador_code= setNULL_str($id_indicador_code);

        $sql= "insert into tref_documentos (id_documento, id_documento_code, id_evento, id_evento_code, id_tarea, ";
        $sql.= "id_tarea_code, id_auditoria, id_auditoria_code, id_proyecto, id_proyecto_code, id_riesgo, id_riesgo_code, ";
        $sql.= "id_nota, id_nota_code, id_requisito, id_requisito_code, id_indicador, id_indicador_code, cronos, situs) ";
        $sql.= "values ({$row['id_documento']}, '{$row['id_documento_code']}', ";
        $sql.= "$id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $id_proyecto, ";
        $sql.= "$id_proyecto_code, $id_riesgo, $id_riesgo_code, $id_nota, $id_nota_code, $id_requisito, $id_requisito_code, ";
        $sql.= "$id_indicador, $id_indicador_code, '$this->cronos', '$this->location') ";

        $result= $this->do_sql_show_error('_add_ref', $sql, false);
    }

    public function copy_documentos($array_documentos= null) {
        $array_documentos= is_null($array_documentos) ? $this->array_documentos : $array_documentos;
        reset($array_documentos);

        $_array_documentos= array();
        $i= 0;
        foreach ($array_documentos as $array) {
            ++$i;
            $row= $this->_get_documento_ref($array['id']);
            if (is_null($row))
                continue;
            $this->_add_ref($row);

            $_array_documentos[$array['id']]= $array;
        }
        return $_array_documentos;
    }

    public function move_all_by_evento_to($id_evento= null) {
        $sql= "select * from tref_documentos where id_evento= $id_evento";
        $result_list= $this->do_sql_show_error('move_all_by_evento_to', $sql);
        if (empty($this->cant)) 
            return null;

        $fecha_inicio_plan= setNULL_str($this->fecha_inicio_plan);

        $array_documentos= array();
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result_list)) {
            ++$i;
            $sql= "update tref_documentos set id_evento= $this->id_evento, id_evento_code= '$this->id_evento_code', ";
            $sql.= "cronos= '$this->cronos', situs= '$this->location' ";
            $sql.= "where id_evento = $id_evento and id_documento = {$row['id_documento']}";
            $result= $this->do_sql_show_error('move_all_by_evento_to', $sql);

            if ($result)
                $array_documentos[$row['id_documento']]= array('id'=>$row['id_documento'], 'id_code'=>$row['id_documento_code']);
            else
                return false;
        }

        return $array_documentos;
    }

    private function search_keywords($keywords, $nombre= null, $keywords_doc= null, $descripcion= null) {
        if (empty($keywords) || !is_string($keywords)) 
            return null;

        $needle_r= explode(";", stripslashes($keywords));

        $descripcion= is_null($descripcion) ? $this->descripcion : stripslashes($descripcion);
        $descripcion= strtolower($descripcion);

        $keywords_doc= is_null($keywords_doc) ? $this->keywords : stripslashes($keywords_doc);
        $keywords_doc= strtolower($keywords_doc);

        $nombre= is_null($nombre) ? $this->nombre : stripslashes($nombre);
        $nombre= strtolower($nombre);

        $i= 0;
        foreach ($needle_r as $key => $needle) {
            $needle= strtolower(stripslashes(trim($needle)));

            $found= strpos($nombre, $needle);
            if ($found !== false)
                ++$i;
            $found= strpos($keywords_doc, $needle);
            if ($found !== false)
                ++$i;
            $found= strpos($descripcion, $needle);
            if ($found !== false)
                ++$i;
        }
        return $i;
    }

    /**
     * @param $dir
     * directorio destino, termiona en /
     */
    public function upload() {
        $this->filename= $_FILES["file_doc-upload"]["name"];
        if ($this->filename) {
            $_url= $_FILES["file_doc-upload"]["tmp_name"];
            $this->size= ($_FILES["file_doc-upload"]["size"] / 1024);
            $this->type= $_FILES["file_doc-upload"]["type"];
            $this->error= $_FILES["file_doc-upload"]["error"];

            if (!empty($this->error))
                return "No ha sido posible cargar el archivo. ERROR:$this->error";

            $ipos= strrpos($this->filename, '.');
            $name= $ipos > 0 ? substr($this->filename, 0, $ipos) : $this->filename;
            $ext= $ipos > 0 ? substr($this->filename, $ipos) : null;
            $this->url= md5("{$name}.{$ext}").$_SESSION['id_usuario'];

            $result= move_uploaded_file($_url, _UPLOAD_DIRIGER_DIR.$this->url);

            if (!$result)
                return "No ha sido posible cargar el archivo";
        }

        return null;
    }

    public function eliminar() {
        $sql= "delete from tref_documentos where id_documento = $this->id_documento ";
        if (!empty($this->id_evento))
            $sql .= "and id_evento= $this->id_evento ";
        if (!empty($this->id_tarea))
            $sql .= "and id_tarea= $this->id_tarea ";            
        if (!empty($this->id_auditoria))
            $sql .= "and id_auditoria= $this->id_auditoria ";
        if (!empty($this->id_proyecto))
            $sql .= "and id_proyecto= $this->id_proyecto ";
        if (!empty($this->id_riesgo))
            $sql .= "and id_riesgo= $this->id_riesgo ";
        if (!empty($this->id_nota))
            $sql .= "and id_nota= $this->id_nota ";
        if (!empty($this->id_politica))
            $sql .= "and id_politica= $this->id_politica ";
        if (!empty($this->id_requisito))
            $sql .= "and id_requisito= $this->id_requisito ";
        if (!empty($this->id_indicador))
            $sql .= "and id_indicador= $this->id_indicador ";

        $result= $this->do_sql_show_error('eliminar', $sql);
        if (!is_null($this->error))
            return $this->error;

        $sql= "select * from tref_documentos where id_documento = $this->id_documento";
        $result= $this->do_sql_show_error('eliminar', $sql);
        $nums= $this->cant;

        if (empty($nums))
            $this->_eliminar();
        return null;
    }

    private function _eliminar ($_url= null) {
        $result= !is_null($_url) ? unlink($_url) : !unlink(_UPLOAD_DIRIGER_DIR.$this->url);
        if (!$result)
            return "Error en el borrado del archivo";

        $sql= "delete from tdocumentos where id = $this->id_documento ";
        $this->do_sql_show_error('_eliminar', $sql);
        return null;
    }

    public function eliminar_by_id($id) {
        $this->Set($id);
        return $this->_eliminar();
    }

    public function uncompress($_url) {
        $error= null;

        // descomprimiendo el fichero compacto y leyendolo
        $fp = fopen($_url.$this->filename, 'rb');

        if (!$fp) {$error= "Error en intento de acceso al fichero GZ --->".$_url.$this->filename; return $error;}

        fseek($fp, -4, SEEK_END);
        $bsize = fread($fp, 4);
        $isize = end(unpack("V", $bsize));
        fclose($fp);

        $fp = @gzopen($_url.$this->filename, "rb");
        if (!$fp) {
            $error= "Error de descompreción del fichero GZ --->".$_url.$this->filename;
            return $error;
        }

        $data = gzread($fp, $isize);
        gzclose($fp);

        $this->filename = substr($this->filename, 0, strrpos($this->filename, ".gz"));
        $fp = fopen($_url.$this->filename, "wb");
        if (!$fp) {
            $error= "Error en intento de escritura del fichero XML --->".$this->filename;
            return $error;
        }

        fwrite($fp, $data);
        fclose($fp);

        return $error;
    }

    public function compress() {
        $fp = fopen($this->url, 'rb');
        $data = fread($fp, filesize($this->url));
        fclose($fp);

        $fd = fopen($this->url.'.gz', 'wb');
        $gzdata = gzencode($data,9);
        fwrite($fd, $gzdata);
        fclose($fd);

        unlink($this->url);
    }

    public function create_backup() {
        rename(_UPLOAD_DIRIGER_DIR.$this->filename.'.gz', _UPLOAD_DIRIGER_DIR."~".$this->filename.'.gz');
        unlink(_UPLOAD_DIRIGER_DIR.$this->filename);
    }

    public function send_mail($mail_address) {
        if (is_null($mail_address))
            return null;
        $this->error= null;

        $mail= new Tmail; //New instance, with exceptions enabled

        $mail->AddAttachment($this->url.".gz");         // add attachments

        $body= "TipoDirigerMsg:LOTE&".$_SESSION['empresa']."&".$_SESSION['location']."&".$this->cronos;
        $mail->Subject  = $body;
        $mail->FromName= $body;
        $mail->MsgHTML($body);

        $mail->AddAddress($mail_address);

        try {
            $mail->Send();
        } catch(Exception $e) {
            $this->error= "Se ha produccido un error en el intento de conexci?n al servidor de correo electr?nico. ";
        }

        $mail->SmtpClose();
        return $this->error;
    }

    protected function existAttachment($part, $inbox, $email_number, $date= null) {
        if (isset($part->parts)) {
            foreach ($part->parts as $partOfPart) {
                $this->existAttachment($partOfPart, $inbox, $email_number, $date= null);
            }
        }
        else {
            if (isset($part->disposition)) {
                if (fullUpper($part->disposition) == 'ATTACHMENT') {
                    $this->save_attach($inbox, $part->dparameters[0]->value, $email_number, $date);
                }
            }
        }
    }

    public function setGrupo($action= 'add') {
        $sql= null;
        if ($action == 'add') {
            $sql= "insert into tusuario_documentos (id_documento, id_documento_code, id_grupo, cronos, situs) ";
            $sql.= "values ($this->id_documento, '$this->id_documento_code', $this->id_grupo, '$this->cronos', '$this->location') ";
        }
        if ($action == 'delete') {
            $sql= "delete from tusuario_documentos where id_grupo = $this->id_grupo and id_usuario is null ";
            $sql.= "and id_documento = $this->id_documento ";
        }

        $this->_set_group($sql);
        return $this->error;
    }

    public function setUsuario($action= 'add', $indirect= false) {
        $sql= null;
        if ($action == 'add') {
            $sql= "insert into tusuario_documentos (id_documento, id_documento_code, id_usuario, cronos, situs) ";
            $sql.= "values ($this->id_documento, '$this->id_documento_code', $this->id_usuario, '$this->cronos', '$this->location') ";
        }
        if ($action == 'delete') {
            $sql= "delete from tusuario_documentos where id_usuario = $this->id_usuario and id_grupo is null ";
            $sql.= "and id_documento = $this->id_documento ";
        }

        $this->_set_user($sql);
        return $this->error;
    }

    public function listar_usuarios($use_id_user= true) {
        $sql= "select distinct tusuarios.*, tusuarios.id as _id, nombre, email, cargo, tusuario_documentos.cronos as _cronos ";
        $sql.= "from tusuarios, tusuario_documentos where tusuarios.id = tusuario_documentos.id_usuario ";
        if (!empty($this->id_documento))
            $sql.= "and tusuario_documentos.id_documento = $this->id_documento ";
        $sql.= "order by nombre asc ";

        return $this->_list_user($sql,$use_id_user);
    }

    public function listar_grupos() {
        $sql= "select distinct id_grupo as _id, nombre from tgrupos, tusuario_documentos ";
        $sql.= "where tgrupos.id = tusuario_documentos.id_grupo ";
        if (!empty($this->id_documento))
            $sql.= "and tusuario_documentos.id_documento = $this->id_documento ";

        return $this->_list_group($sql);
    }

    function read_associeted_to($array) {
        global $Ttipo_auditoria_array;
        global $Ttipo_nota_origen_array;
        global $Ttipo_nota_array;

        $text= null;
        if (!empty($array['id_tarea'])) {
            $sql= "select * from ttareas where id = {$array['id_tarea']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Tarea:</strong> {$row['nombre']} Fecha/Hora:".odbc2time_ampm($row['fecha_inicio_plan'])."<br/>";
        }        
        if (!empty($array['id_evento'])) {
            $sql= "select * from teventos where id = {$array['id_evento']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Actividad:</strong> {$row['nombre']} Fecha/Hora:".odbc2time_ampm($row['fecha_inicio_plan'])."<br/>";
        }
        if (!empty($array['id_auditoria'])) {
            $sql= "select * from tauditorias where id = {$array['id_auditoria']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Acción de Control:</strong> {$row['nombre']} Tipo:{$Ttipo_auditoria_array[(int)$row['tipo']]} Origen:{$Ttipo_nota_origen_array[(int)$row['origen']]} Fecha/Hora:".odbc2time_ampm($row['fecha_inicio_plan'])."<br/>";
        }
        if (!empty($array['id_nota'])) {
            $sql= "select * from tnotas where id = {$array['id_nota']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Nota:</strong> Tipo:{$Ttipo_nota_array[(int)$row['tipo']]} Origen:{$Ttipo_nota_origen_array[(int)$row['origen']]} Fecha/Hora:".odbc2time_ampm($row['fecha_inicio_real'])."<br/>";
        }
        if (!empty($array['id_riesgo'])) {
            $sql= "select * from triesgos where id = {$array['id_riesgo']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Riesgo:</strong> {$row['nombre']} Fecha:".odbc2date($row['fecha_inicio_plan'])."<br/>";
        }
        if (!empty($array['id_proyecto'])) {
            $sql= "select * from tproyectos where id = {$array['id_proyecto']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Proyecto:</strong> {$row['nombre']} Fecha:".odbc2date($row['fecha_inicio_plan'])."<br/>";
        }
        if (!empty($array['id_requisito'])) {
            $sql= "select * from tlista_requisitos where id = {$array['id_requisito']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Lista de Chequeo (requisto):</strong> {$row['nombre']} <br/>";
        }    
        if (!empty($array['id_indicador'])) {
            $sql= "select * from tindicadores where id = {$array['id_indicador']} ";
            $result= $this->do_sql_show_error('read_associeted_to', $sql);
            $row= $this->clink->fetch_array($result);

            $text.= "<strong>Indicador:</strong> {$row['nombre']} <br/>";
        }        
        return $text;
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";
if (!class_exists('Tmail'))
    include_once "mail.class.php";


function get_file_type($filename) {
    $ext= array(
        'comprimido'=> array('rar', 'zip', 'gz', 'gzip', 'tar'),
        'video'=> array('avi', 'mpg', 'mp4', 'mkv', 'vob', 'wmv'),
        'audio'=> array('mp3', 'waf', 'wma'),
        'imagen'=> array('png', 'jpeg', 'jpg', 'avi', 'psd', 'gif', 'bmp'),
        'word'=> array('docx', 'doc', 'rft'),
        'pdf'=> array('pdf'),
        'power point'=> array('pptx', 'ppt', 'ppsx'),
        'excel'=> array('xlsx', 'xls')
    );

    $class= array(
        'comprimido'=> 'winrar.ico',
        'video'=> 'avi.ico',
        'audio'=> 'audio.ico',
        'imagen'=> 'jpeg.ico',
        'word'=> 'docx-win.ico',
        'pdf'=> 'pdf.ico',
        'power point'=> 'pptx_win.ico',
        'excel'=> 'xlsx-win.ico'
    );

    $array= preg_split('/\./', $filename);
    $count= count($array);

    $key= false;
    $found= false;

    for ($i= $count-1; $i > 0; $i--) {
        reset($ext);
        $_key= null;

        foreach ($ext as $_key => $_ext) {
            $found= array_search(strtolower($array[$i]), $_ext);
            if ($found !== false) {
                $key= $_key;
                $found= $_ext[$found];
                break;
            }
        }

        if ($found !== false)
            break;
    }

    if ($found !== false) $result= array('type'=>$key, 'img'=>$class[$key], 'ext'=>$found);
    return $result;
}

function mime_type($ext) {
    $mime= null;

    switch ($ext) {
        case 'doc' :
            $mime = 'application/msword';
            break;
        case 'pdf' :
            $mime = 'application/pdf';
            break;
        case 'ppt' :
            $mime = 'application/vnd.ms-powerpoint';
            break;
        case 'ppsx' :
            $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            break;
        case 'docx' :
            $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
        case 'xlsx' :
            $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        case 'xls' :
            $mime = 'application/vnd.ms-excel';
            break;
        case 'accdb' :
            $mime = 'application/msaccess';
            break;

        case 'gzip' :
            $mime = 'application/gzip';
            break;
        case 'gz' :
            $mime = 'application/gzip';
            break;
        case 'rar' :
            $mime = 'application/gzip';
            break;
        case 'zip' :
            $mime = 'application/gzip';
            break;

        case 'txt' :
            $mime = 'text/plain';
            break;
        case 'html' :
            $mime = 'text/html';
            break;
        case 'msg' :
            $mime = 'message/rfc822';
            break;

        case 'exe' :
            $mime = 'application/octet-stream';
            break;

        case 'fla' :
            $mime = 'application/x-shockwave-flash';
            break;

        case 'mp4' :
            $mime = 'video/mp4';
            break;
        case 'avi' :
            $mime = 'video/avi';
            break;
        case 'vob' :
            $mime = 'video/avi';
            break;
        case 'flv' :
            $mime = 'video/x-flv';
            break;
        case '3gpp' :
            $mime = 'video/3gpp';
            break;
        case 'mkv' :
            $mime = 'video/x-ms-wmv';
            break;
        case 'mpg' :
            $mime = 'video/mpeg';
            break;

        case 'mpeg' :
            $mime = 'audio/mpeg';
            break;

        case 'gif' :
            $mime = 'image/gif';
            break;
        case 'jpeg' :
            $mime = 'image/jpeg';
            break;
        case 'pjpeg' :
            $mime = 'image/pjpeg';
            break;
        case 'jpg' :
            $mime = 'image/jpg';
            break;
        case 'bmp' :
            $mime = 'image/bmp';
            break;
        case 'png' :
            $mime = 'image/png';
            break;
        case 'tiff' :
            $mime = 'image/tiff';
            break;
    }

    return $mime;
}

