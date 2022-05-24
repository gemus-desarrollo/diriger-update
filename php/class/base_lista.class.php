<?php
/**
 *
 * @author mustelier
 */

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";

class Tbase_lista extends Tbase_planning {
    protected $id_tipo_lista;
    protected $id_tipo_lista_code;

    protected $componente;
    protected $id_lista;
    protected $id_lista_code;

    protected $id_requisito;
    protected $id_requisito_code;

    protected $evidencia;
    protected $indicacion;

    protected $calcular;

    public $array_requisitos;
    public $array_listas;

    public $if_tlista_requisitos;

    public function SetEvidencia($id) {
        $this->evidencia= $id;
    }
    public function GetEvidencia() {
        return $this->evidencia;
    }
    public function SetIndicacion($id) {
        $this->indicacion= $id;
    }
    public function GetIndicacion() {
        return $this->indicacion;
    }
    public function SetIdLista($id) {
        $this->id_lista= $id;
    }
    public function GetIdLista() {
        return $this->id_lista;
    }
    public function get_id_lista_code() {
        return $this->id_tipo_lista_code;
    }
    public function set_id_lista_code($id) {
        $this->id_lista_code= $id;
    }
    public function GetIdTipo_lista() {
        return $this->id_tipo_lista;
    }
    public function SetIdTipo_lista($id) {
        $this->id_tipo_lista= $id;
    }
    public function get_id_tipo_lista_code() {
        return $this->id_tipo_lista_code;
    }
    public function set_id_tipo_lista_code($id) {
        $this->id_tipo_lista_code= $id;
    }
    public function GetComponente() {
        return $this->componente;
    }
    public function SetComponente($id) {
        $this->componente= $id;
    }
    public function SetCalcular($id) {
        $this->calcular= !empty($id) ? true : false;
    }

    protected $id_capitulo;
    protected $id_capitulo_code;
    protected $capitulo;
    protected $subcapitulo;

    public function GetIdCapitulo() {
        return $this->id_capitulo;
    }
    public function get_id_capitulo_code() {
        return $this->id_capitulo_code;
    }
    public function SetIdCapitulo($id) {
        $this->id_capitulo = $id;
    }
    public function set_id_capitulo_code($id) {
        $this->id_capitulo_code = $id;
    }
    public function GetCapitulo() {
        return $this->capitulo;
    }
    public function SetCapitulo($id) {
        $this->capitulo = $id;
    }
    public function GetSubcapitulo() {
        return $this->subcapitulo;
    }
    public function SetSubcapitulo($id) {
        $this->subcapitulo = $id;
    }

    protected $estado;
    public  $if_tnotas;

    public function setEstado($id) {
        $this->estado = $id;
    }
    public function getEstado() {
        return $this->estado;
    }

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_planning::__construct($clink);
    }

    public function getLista_reg() {
        $sql= "select * from treg_nota where id_lista = $this->id_lista ";
        if (!empty($this->id_requisito))
            $sql.= "and id_requisito = $this->id_requisito ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->reg_fecha))
            $sql.= "and date(reg_date) <= '$this->reg_fecha'";
        $sql.= "order by cronos desc limit 1";

        $result= $this->do_sql_show_error('getLista_reg', $sql);
        if ($result) {
            $row= $this->clink->fetch_array($result);
            return $row;
        } else {
            return $this->error;
        }
    }

    public function setLista_reg() {
        $observacion= setNULL_str($this->observacion);
        $id_auditoria= setNULL($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);
        $calcular= boolean2pg($this->calcular);

        $sql= "insert into treg_nota (id_lista, id_lista_code, id_requisito, id_requisito_code, id_proceso, ";
        $sql.= "id_proceso_code, id_auditoria, id_auditoria_code, cumplimiento, calcular, observacion, reg_fecha, ";
        $sql.= "estado, cronos, situs) values ($this->id_lista, '$this->id_lista_code', $this->id_requisito, ";
        $sql.= "'$this->id_requisito_code', $this->id_proceso, '$this->id_proceso_code', $id_auditoria, ";
        $sql.= "$id_auditoria_code, $this->cumplimiento, $calcular, $observacion, '$this->reg_fecha', ";
        $sql.= "$this->estado, '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('setLista_reg', $sql);
        return $this->error;
    }

    protected function get_array_reg_procesos($id_proceso=null, $tipo= null, $radio_prs= null) {
        $item= null;
        if (isset($this->array_procesos)) unset($this->array_procesos);
        $this->array_procesos= array();

        switch($this->className) {
            case('Triesgo'):
                $item= 'riesgos';
                break;
            default:
                $item= 'riesgos';
        }

        $array_procesos= array();
        $id_proceso_code= null;
        $obj_prs= new Tproceso($this->clink);

        $radio_prs= !empty($radio_prs) ? (int)$radio_prs : 0;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];

        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $id_proceso_code= $obj_prs->get_id_proceso_code();
        if (empty($tipo))
            $tipo= $obj_prs->GetTipo();
        $array_procesos[$id_proceso]= array('id'=>$id_proceso, 'id_code'=>$id_proceso_code);

        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdUsuario(null);
        $obj_prs->SetIdResponsable(null);

        if ($radio_prs == 2) {
            $obj_prs->SetConectado(null);
            $obj_prs->SetIdEntity(null);
            $array_procesos= $obj_prs->listar_in_order('eq_desc', true);
        }

        $sql= "select distinct tproceso_riesgos.id_proceso as _id_proceso, tproceso_riesgos.id_proceso_code as _id_proceso_code, ";
        $sql.= "tipo, conectado from tproceso_riesgos, tprocesos where tproceso_riesgos.id_proceso = tprocesos.id ";
        if ($item == 'riesgos')
            $sql.= "and id_riesgo = $this->id_riesgo ";
        $result= $this->do_sql_show_error('get_array_reg_procesos', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if ($radio_prs == 0 && (int)$id_proceso != (int)$row['_id_proceso'])
                continue;
            $this->array_procesos[$row['_id_proceso']]= array('id'=>$row['_id_proceso'], 'id_code'=>$row['_id_proceso_code'], 'toshow'=>null);
        }
        return $this->array_procesos;
    }

    protected function get_array_reg_objs($year= null) {
        $year= !empty($year) ? $year : $this->year + 1;

        $sql= "select distinct id_inductor, id_inductor_code, peso from tinductor_riesgos, tinductores where peso > 0 ";
        $sql.= "and tinductor_riesgos.id_inductor = tinductores.id and (inicio <= $year and fin >= $year) ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        $result= $this->do_sql_show_error('get_array_reg_objs', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_inductores[]= array('id'=>$row['id_inductor'], 'id_code'=>$row['id_inductor_code'], 'peso'=>$row['peso']);
        }    
        return $this->array_inductores;
    }

    protected function _create_tlista_requisitos() {
        $sql= "drop table if exists _tlista_requisitos";
        $result= $this->do_sql_show_error("_create_tlista_requisitos", $sql);
// 
        $sql= "CREATE TEMPORARY TABLE _tlista_requisitos ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";

        $sql.= " numero ".field2pg("MEDIUMINT(4)").", ";
        $sql.= " componente ".field2pg("TINYINT(4)").", ";
        $sql.= " nombre ".field2pg("TEXT").", ";

        $sql.= " id_lista ".field2pg("INTEGER(11)").", ";
        $sql.= " id_lista_code ".field2pg("CHAR(12)").", ";

        $sql.= " id_tipo_lista ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tipo_lista_code ".field2pg("CHAR(12)").", ";

        $sql.= " peso ".field2pg("TINYINT(4)").", ";
        $sql.= " inicio ".field2pg("MEDIUMINT(9)").", ";
        $sql.= " fin ".field2pg("MEDIUMINT(9)").", ";

        $sql.= " evidencia ".field2pg("LONGTEXT").", ";
        $sql.= " indicacion ".field2pg("TEXT").", ";
        $sql.= " indice ".field2pg("INTEGER(11)").", ";

        $sql.= " id_usuario ".field2pg("INTEGER(11)").", ";
        $sql.= " cronos ".field2pg("DATETIME").", ";
        $sql.= " cronos_syn ".field2pg("DATETIME").", ";
        $sql.= " situs ".field2pg("CHAR(2)")." ";
        $sql.= ") ";

        if (empty($this->error))
            $this->do_sql_show_error('_create_tlista_requisitos', $sql);
        if (is_null($this->error))
            $this->if_tlista_requisitos= true;
    }  
    
    protected function create_tlista_requisitos($result) {
        $this->_create_tlista_requisitos();

        $sql= null;
        $i= 0;
        $j= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['_id'];

            $sql.= "insert into _tlista_requisitos ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "select * from tlista_requisitos where id = {$row['_id']}; ";

            ++$j;
            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('create_tlista_requisitos', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql)
            $this->do_multi_sql_show_error('create_tlista_requisitos', $sql);
            
        $this->cant= $j;   
        return $j;
    }
}
