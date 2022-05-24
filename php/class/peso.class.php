<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

include_once "../config.inc.php";

if (!class_exists('Treference'))
    include_once "reference.class.php";

//include_once "../config.inc.php";

class Tpeso extends Treference {
    protected $array_register;

    protected $if_ci;
    protected $if_eficaz;

    protected $compute_executed;
    public $compute_traze;

    public $if_teventos;
    public $if_treg_evento;
    public $toshow;

    protected $array_cascade_entity;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Treference::__construct($clink);

        $this->periodicidad= null;
        $this->if_objsup= false;
        $this->flag_field_prs= false;
        $this->cronos= date('Y-m-d H:i:s');
        $this->compute_executed= false;
        $this->compute_traze= false;
    }

    public function SetIfControlInterno($id = 1) {
        $this->if_ci = empty($id) ? 0 : 1;
    }
    public function GetIfControlInterno() {
        return $this->if_ci;
    }
    public function get_array_register() {
        return $this->array_register;
    }
    public function get_if_eficaz() {
        return $this->if_eficaz;
    }

    /**
     * politicas
     */
    public function listar_politicas_ref_objetivo($id_objetivo= 0, $id_proceso= 0) {
        if (isset($this->array_pesos)) 
            unset($this->array_pesos);
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_objetivo= !empty($id_objetivo) ? $id_objetivo : $this->id_objetivo;

        $sql= "select distinct tpoliticas.*, tpoliticas.id as _id, peso, tpolitica_objetivos.id_proceso as _id_proceso ";
        $sql.= "from tpoliticas, tpolitica_objetivos where tpoliticas.id = tpolitica_objetivos.id_politica and peso > 0 ";
        if (!empty($id_objetivo))
            $sql.= "and id_objetivo = $id_objetivo ";
        if (!empty($id_proceso))
            $sql.= "and tpolitica_objetivos.id_proceso = $id_proceso ";
        if (!empty($this->year))
            $sql.= "and tpolitica_objetivos.year = $this->year ";
        $sql.= "order by tpoliticas.id asc ";
        $result= $this->do_sql_show_error('listar_politicas_ref_objetivo', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id']]= $row['peso'];
            ++$i;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'numero'=>$row['numero'],
                        'politica'=>$row['nombre'], 'peso'=>$row['peso'], 'id_proceso'=>$row['_id_proceso']);
            $this->array_politicas[$row['id']]= $array;
        }
        return $this->array_pesos;
    }

    public function listar_politicas_ref_inductor($id) {
        $sql= "select * from tobjetivo_inductores where id_inductor = $id and peso > 0";
        $result= $this->do_sql_show_error('listar_politicas_from_objetivo', $sql);

        while ($row= $this->clink->fetch_array($result))
            $this->listar_politicas_ref_objetivo($row['id_objetivo']);
    }

    public function listar_politicas_ref_riesgo($id) {
        $sql= "select * from tinductor_riesgos where id_riesgo = $id and peso > 0";
        $result= $this->do_sql_show_error('listar_politicas_ref_riesgo', $sql);

        while ($row= $this->clink->fetch_array($result))
            $this->listar_politicas_ref_inductor($row['id_inductor']);
    }

    public function listar_politicas_ref_evento($id) {
        $sql= "select * from tinductor_eventos where id_evento = $id and peso > 0";
        $result= $this->do_sql_show_error('listar_politicas_ref_evento', $sql);

        while ($row= $this->clink->fetch_array($result))
            $this->listar_politicas_ref_inductor($row['id_inductor']);

        $this->array_politicas= array_unique((array)$this->array_politicas);
    }

    public function listar_indicadores_ref_proceso($id, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        $this->cant= 0;

        $sql= "select tproceso_indicadores.*, tproceso_indicadores.id_indicador as _id, ";
        $sql.= "tproceso_indicadores.id_indicador_code as _id_code, ";
        $sql.= "tprocesos.nombre as _nombre from tproceso_indicadores, tprocesos ";
        $sql.= "where tproceso_indicadores.id_proceso = tprocesos.id and tprocesos.id = $id ";
        if (!empty($this->year))
            $sql.= "and tproceso_indicadores.year = $this->year ";
        $result= $this->do_sql_show_error('listar_indicadores_ref_proceso', $sql);

        if ($flag)
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['_id']]= array('peso'=>$row['peso'], 'id_proceso'=>$row['id_proceso']);
            ++$i;
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'proceso'=>$row['_nombre'],
                        'id_proceso'=>$row['id_proceso']);
            $this->_array_indicadores[$row['id']]= $array;
        }
        $this->cant= $i;
        return $this->array_pesos;
    }

    public function listar_objetivos_sup_ref_objetivo($id_objetivo= 0, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        $this->cant= 0;

        $id_objetivo= $id_objetivo ? $id_objetivo : $this->id_objetivo;

        $sql= "select distinct tobjetivos.*, tobjetivos.id as _id, tobjetivos.id_code as _id_code, peso, ";
        $sql.= "tpolitica_objetivos.id_proceso as _id_proceso from tobjetivos, tpolitica_objetivos ";
        $sql.= "where tobjetivos.id = tpolitica_objetivos.id_objetivo_sup and id_objetivo = $id_objetivo ";
        $sql.= "and id_politica is null and peso > 0 ";
        if (!empty($this->year))
            $sql.= "and tpolitica_objetivos.year = $this->year ";
        if (!empty($this->id_proceso))
            $sql.= "and tpolitica_objetivos.id_proceso = $this->id_proceso ";
        $sql.= "order by tobjetivos.numero asc, tobjetivos.id asc ";
        $result= $this->do_sql_show_error('listar_objetivos_sup_ref_objetivo', $sql);

        if ($flag)
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['_id']]= array('peso'=>$row['peso'], 'id_proceso'=>$row['_id_proceso']);
            ++$i;
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'objetivo'=>$row['objetivo'],
                        'estrategia'=>$row['estrategia'], 'id_proceso'=>$row['_id_proceso']);
            $this->_array_objetivos[$row['_id']]= $array;
        }
        $this->cant= $i;
        return $this->array_pesos;
    }

    /**
     * objetivos
     */
    public function listar_objetivos_ref_politica($id_politica= 0, $flag= true) {
        if (isset($this->array_pesos)) 
            unset($this->array_pesos);

        $id_politica= !empty($id_politica) ? $id_politica : $this->id_politica;

        $sql= "select distinct tobjetivos.*, tobjetivos.id as _id, tobjetivos.id_code as _id_code, ";
        $sql.= "peso as _peso, tobjetivos.numero as _numero from tpolitica_objetivos, tobjetivos ";
        $sql.= "where tobjetivos.id = tpolitica_objetivos.id_objetivo ";
        $sql.= "and id_politica = $id_politica and id_objetivo_sup is null and peso > 0 ";

        if (!empty($this->year))
            $sql.= "and tpolitica_objetivos.year = $this->year ";
        if (!empty($this->id_proceso)) {
            $sql.= "and (tobjetivos.id_proceso = $this->id_proceso ";
            $sql.= "or (tpolitica_objetivos.id_proceso = $this->id_proceso ";

            if ($this->id_proceso != $_SESSION['id_entity'])
                $sql.= "or tpolitica_objetivos.id_proceso = ".$_SESSION['id_entity'];
            $sql.= " )) ";
        }
        $sql.= "order by tobjetivos.numero asc, tobjetivos.id asc ";

        $result= $this->do_sql_show_error('listar_objetivos_ref_politica', $sql);

        if ($flag) 
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id_objetivo']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    public function listar_objetivos_ref_objetivo_sup($id_objetivo_sup= null, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->cant= 0;

        if (empty($id_objetivo_sup)) 
            $id_objetivo_sup= $this->id_objetivo_sup;

        $sql= "select distinct tobjetivos.*, tobjetivos.id as _id, tobjetivos.id_code as _id_code, ";
        $sql.= "peso as _peso, tobjetivos.numero as _numero, tobjetivos.objetivo as _nombre ";
        $sql.= "from tpolitica_objetivos, tobjetivos where tobjetivos.id = tpolitica_objetivos.id_objetivo ";
        $sql.= "and id_objetivo_sup = $id_objetivo_sup and id_politica is null and peso > 0 ";
        if (!empty($this->year))
            $sql.= "and tpolitica_objetivos.year = $this->year ";
        if (!empty($this->id_proceso))
            $sql.= "and tpolitica_objetivos.id_proceso = $this->id_proceso ";
        $sql.= "order by tobjetivos.numero asc, tobjetivos.id asc ";

        $result= $this->do_sql_show_error('listar_objetivos_ref_objetivo_sup', $sql);
        if ($flag) 
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
                $this->array_pesos[$row['_id']]= $row['_peso'];
        }
        return $this->array_pesos;
    }

    public function listar_objetivo_sup_ref_objetivo($id_objetivo= null, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        if (empty($id_objetivo)) 
            $id_objetivo= $this->id_objetivo;

        $sql= "select * from tpolitica_objetivos where id_objetivo = $id_objetivo ";
        $sql.= "and id_politica is null and peso > 0 ";
        if (!empty($this->year))
            $sql.= "and year = $this->year ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso";

        $result= $this->do_sql_show_error('listar_objetivo_sup_ref_objetivo', $sql);
        if ($flag) 
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id_objetivo_sup']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    public function listar_objetivos_ref_inductor($id_inductor=0) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        if (empty($id_inductor)) 
            $id_inductor= $this->id_inductor;

        $sql= "select * from tobjetivo_inductores where 1 ";
        if (!empty($id_inductor))
            $sql.= "and id_inductor = $id_inductor ";
        if (!empty($this->year))
            $sql.= "and year = $this->year ";
        $result= $this->do_sql_show_error('listar_objetivos_ref_inductor', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id_objetivo']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    /**
     * Programas
     */
    public function listar_indicadores_ref_programa($id_programa= null, $flag= true, 
                                                        $use_array_indicadores= false) {
        if (empty($id_programa)) 
            $id_programa= $this->id_programa;

        $sql= "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code, ";
        $sql.= "tref_programas.peso as _peso from tref_programas, tindicadores ";
        $sql.= "where tref_programas.id_indicador = tindicadores.id ";
        $sql.= "and  tref_programas.id_programa =  $id_programa ";
        if ($flag)
            $sql.= "and tref_programas.peso > 0 ";
        if (!empty($this->year))
            $sql.= "and tref_programas.year = $this->year ";
        if (!empty($this->inicio))
            $sql.= "and (fin >= $this->inicio and inicio <= $this->fin) ";
        $sql.= "order by nombre asc";
        $result= $this->do_sql_show_error('listar_indicadores_ref_programa', $sql);

        if ($use_array_indicadores) {
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'], 'id_proceso'=>$row['id_proceso'],
                        'peso'=>null);
                $this->array_indicadores[$row['_id']]= $array;
        } }

        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->clink->data_seek($result);

        while ($row= $this->clink->fetch_array($result)) {
            $id_indicador= $row['_id'];
            $this->array_indicadores[$id_indicador]['peso']= $row['_peso'];
            $this->array_pesos[$id_indicador]= $row['_peso'];
        }
        return $this->array_pesos;
    }

    /**
     *  inductores
     */
    public function listar_inductores_ref_indicador($id_indicador= null, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= null;

        $id_indicador= !is_null($id_indicador) ? $id_indicador: $this->id_indicador;

        $sql= "select distinct id_inductor, id_inductor code, peso, nombre, inicio, fin ";
        $sql.= "from tref_indicadores, tinductores where tref_indicadores.id_inductor = tinductores.id ";
        $sql.= "and id_indicador = $id_indicador and peso > 0 ";
        if (!empty($this->inicio)) {
            $sql.= "and ((tinductores.fin >= $this->inicio and tinductores.inicio <= $this->fin) ";
            $sql.= "and (tref_indicadores.year >= $this->inicio and tref_indicadores.year <= $this->fin)) ";
        }
        if (!empty($this->year))
            $sql.= "and tref_indicadores.year = $this->year ";
        $sql.= "order by nombre asc";

        $result= $this->do_sql_show_error('listar_inductores_ref_indicador', $sql);
        if ($flag) 
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id_inductor']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    public function listar_inductores_ref_objetivo($id_objetivo= null, $flag= true) {
        $this->cant= 0;
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        if (empty($id_objetivo)) 
            $id_objetivo= $this->id_objetivo;

        $sql= "select distinct tinductores.*, tinductores.id as _id, tinductores.id_code as _id_code, ";
        $sql.= "tobjetivo_inductores.peso as _peaso, tinductores.id as id_inductor, tinductores.numero as _numero ";
        $sql.= "from tobjetivo_inductores, tinductores where tinductores.id = tobjetivo_inductores.id_inductor ";
        if (!empty($id_objetivo))
            $sql.= "and id_objetivo = $id_objetivo ";
        if (!empty($this->year))
            $sql.= "and tobjetivo_inductores.year = $this->year ";
        if ($flag)
            $sql.= "and tobjetivo_inductores.peso > 0 order by _numero asc";
        $result= $this->do_sql_show_error('listar_inductores_ref_objetivo', $sql);
        if ($flag)
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['_id']]= $row['_peso'];
        }
        return $this->array_pesos;
    }

    public function listar_inductores_ref_perspectiva($id_perspectiva= null, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        if (empty($id_perspectiva)) 
            $id_perspectiva= $this->id_perspectiva;

        $sql= "select distinct *, id as id_inductor, id_code as id_inductor_code, id as _id, ";
        $sql.= "id_code as _id_code from tinductores where id_perspectiva = $id_perspectiva ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_inductores_ref_perspectiva', $sql);
        if ($flag)
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'],'peso'=>1,
                'inicio'=>$row['inicio'], 'fin'=>$row['fin'], 'id_proceso'=>$row['id_proceso']);
            $this->array_pesos[$row['id']]= $array;
        }
        return $this->array_pesos;
    }

    public function listar_inductores_ref_riesgo($id_riesgo= null, $flag= true) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();
        if (empty($id_riesgo)) 
            $id_riesgo= $this->id_riesgo;

        $sql= "select  distinct * from tinductor_riesgos where id_riesgo = $id_riesgo ";
        $result= $this->do_sql_show_error('listar_ref_objetivos_riesgo', $sql);
        if ($flag) 
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            /*
            $array= array('id'=>$row['id_inductor'], 'id_code'=>$row['id_inductor_code'], 'nombre'=>$row['nombre'],'peso'=>$row['peso'],
                'inicio'=>$row['_inicio'], 'fin'=>$row['_fin']);
            $this->array_pesos[$row['id_inductor']]= $array;
            */
            $this->array_pesos[$row['id_inductor']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    public function listar_inductores_ref_evento($id_evento= null, $id_tarea= null) {
        $id_evento= empty($id_evento) ? $id_evento : $this->id_evento;
        $id_tarea= empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql= "select distinct tinductor_eventos.*, tinductores.nombre, tinductores.inicio as _inicio, ";
        $sql.= "tinductores.fin as _fin from tinductor_eventos, tinductores ";
        $sql.= "where tinductor_eventos.id_inductor = tinductores.id ";
        if(!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if(!empty($id_tarea))
            $sql.= "and id_tarea = $id_tarea "; 
        $sql.= "order by cronos desc";
        $result= $this->do_sql_show_error('listar_inductores_ref_evento', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if (!is_null($this->array_pesos[$row['id_inductor']]))
                continue;
            /*
            $array= array('id'=>$row['id_inductor'], 'id_code'=>$row['id_inductor_code'], 'nombre'=>$row['nombre'],'peso'=>$row['peso'],
                'inicio'=>$row['_inicio'], 'fin'=>$row['_fin']);

            $this->array_pesos[$row['id_inductor']]= $array;
            */
            $this->array_pesos[$row['id_inductor']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    /**
     *  perspectivas
     */
    public function listar_perspectivas_ref_proceso($id_proceso= null, $flag= true) {
        if (empty($id_proceso))
            $id_proceso= $this->id_proceso;

        $sql= "select tperspectivas.*, tperspectivas.id as _id, tperspectivas.peso as _peso ";
        $sql.= "from tperspectivas where id_proceso = $id_proceso ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_perspectivas_ref_proceso', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $this->array_pesos[$id]= array('peso'=>$row['_peso'], 'id_proceso'=>$row['id_proceso']);
        }
        return $this->array_pesos;
    }

    /**
     *  riesgos
     */
    public function update_riesgo_ref_inductor($id_inductor, $id_inductor_code, $peso) {
        $sql= "select * from  tinductor_riesgos where id_inductor= $id_inductor ";
        $sql.= "and id_riesgo= $this->id_riesgo";
        $this->do_sql_show_error('update_riesgo_ref_inductor', $sql);

        if ($this->cant > 0) {
           $sql= "update tinductor_riesgos set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
           $sql.= "where id_inductor= $id_inductor and id_riesgo= $this->id_riesgo";
        } else {
            if (is_null($id_inductor_code))
                $id_inductor_code= get_code_from_table('tinductores', $id_inductor, $this->clink);

            $sql= "insert into tinductor_riesgos (id_inductor, id_inductor_code, id_riesgo, ";
            $sql.= "id_riesgo_code, peso, cronos, situs) values ($id_inductor, '$id_inductor_code', ";
            $sql.= "$this->id_riesgo, '$this->id_riesgo_code', $peso, '$this->cronos', '$this->location') ";
        }
        $this->do_sql_show_error('update_riesgo_ref_inductor', $sql);
    }

    public function delete_riesgo_ref_inductor($id_inductor) {
        $sql= "delete from tinductor_riesgos where id_inductor= $id_inductor ";
        $sql.= "and id_riesgo= $this->id_riesgo ";
        $result= $this->do_sql_show_error('delete_riesgo_ref_inductor', $sql);
    }

    public function listar_riesgos_ref_inductor($id_inductor=0) {
        if (isset($this->array_pesos)) unset($this->array_pesos);
        if (empty($id_inductor))
            $id_inductor= $this->id_inductor;

        $sql= "select * from tinductor_riesgos where id_inductor = $id_inductor ";
        $result= $this->do_sql_show_error('listar_riesgos_ref_inductor', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$row['id_riesgo']]= $row['peso'];
        }
        return $this->array_pesos;
    }

    /**
     *  indicadores
     */
    public function update_indicador($id_indicador, $peso) {
        $sql= "update tref_indicadores set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_inductor = $this->id_inductor and id_indicador = $id_indicador ";
        $this->do_sql_show_error('update_indicador', $sql);
    }

    public function listar_indicadores_ref_inductor($id_inductor= null, $flag= true, $id_programa= null, 
                                                                        $with_programa_null= _PERSPECTIVA_ALL) {
        if (empty($id_inductor))
            $id_inductor= $this->id_inductor;

        $sql= "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code,  ";
        $sql.= "tindicadores.id_proceso as _id_proceso, tref_indicadores.peso as _peso ";
        $sql.= "from tref_indicadores, tindicadores ";
        if ($with_programa_null == _PERSPECTIVA_NOT_NULL)
            $sql.= ", tref_programas ";
        $sql.= "where tref_indicadores.id_indicador = tindicadores.id ";
        if (!empty($id_inductor))
            $sql.= "and tref_indicadores.id_inductor = $id_inductor ";
        if ($with_programa_null == _PERSPECTIVA_NOT_NULL) {
            $sql.= "and (tref_indicadores.id_indicador = tref_programas.id_indicador ";
            $sql. "and tref_indicadores.year = tref_programas.year) ";
        }
            
        if ($flag)
            $sql.= "and tref_indicadores.peso > 0 ";
        if (!empty($this->year))
            $sql.= "and tref_indicadores.year = $this->year ";
        if (!empty($this->inicio))
           $sql.= "and (fin >= $this->inicio and inicio <= $this->fin) ";
        $sql.= "order by nombre asc";
        $result= $this->do_sql_show_error('listar_indicadores_ref_inductor', $sql);

        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);

        if (!empty($id_programa))
            $this->listar_indicadores_ref_programa($id_programa, true, true);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $id_indicador= $row['_id'];

            if (!empty($id_programa))
                if (!array_key_exists($id_indicador, $this->array_indicadores))
                    continue;
            $this->array_pesos[$id_indicador]= $row['_peso'];
        }
        $this->cant= $i;
        return $this->array_pesos;
    }

    public function listar_indicadores_ref_perspectiva($id_perspectiva=0, $flag= true) {
        if (empty($id_perspectiva))
            $id_perspectiva= $this->id_perspectiva;

        $sql= "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code, ";
        $sql.= "tindicador_criterio.peso as _peso, tindicador_criterio.id_proceso as _id_proceso  ";
        $sql.= "from tindicador_criterio, tindicadores where tindicador_criterio.id_indicador = tindicadores.id ";
        $sql.= "and tindicador_criterio.id_perspectiva = $id_perspectiva ";
        if ($flag)
            $sql.= "and tindicador_criterio.peso > 0 ";
        if (!empty($this->year))
            $sql.= "and tindicador_criterio.year = $this->year ";
        if (!empty($this->inicio))
            $sql.= "and (fin >= $this->inicio and inicio <= $this->fin) ";
        if (!empty($this->id_proceso))
            $sql.= "and tindicador_criterio.id_proceso = $this->id_proceso ";
        $sql.= "order by nombre asc";
        $result= $this->do_sql_show_error('listar_indicadores_ref_perspectiva', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $id_indicador= $row['_id'];
            $this->array_pesos[$id_indicador]= array('peso'=>$row['_peso'], 'id_proceso'=>$row['id_proceso']);
        }
        return $this->array_pesos;
    }

    /**
     * evento
     */
    public function update_evento_ref_inductor($id_inductor, $id_inductor_code, $peso, $action= null) {
        $cant= 0;

        if (is_null($action) || $action == 'insert') {
            $sql= "select * from tinductor_eventos where id_inductor= $id_inductor ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento= $this->id_evento ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea = $this->id_tarea ";
            $this->do_sql_show_error('update_evento_ref_inductor', $sql);
        }

        if ($this->cant > 0 || $action == 'update') {
            $sql= "update tinductor_eventos set peso= $peso, cronos= '$this->cronos', situs= '$this->location'  ";
            $sql.= "where id_evento= $this->id_evento ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento= $this->id_evento ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea = $this->id_tarea ";            
            $this->do_sql_show_error('update_evento_ref_inductor', $sql);
        }

        if ($action == 'insert' || ($action == 'update' && $this->cant == 0)) {
            if (is_null($id_inductor_code))
                $id_inductor_code= get_code_from_table('tinductores', $id_inductor, $this->clink);
            
            $id_evento= setNULL($this->id_evento);
            $id_evento_code= setNULL_str($this->id_evento_code);
            $id_tarea= setNULL($this->id_tarea);
            $id_tarea_code= setNULL_str($this->id_tarea_code);

            $sql= "insert into tinductor_eventos (id_evento, id_evento_code, id_tarea, id_tarea_code, id_inductor, ";
            $sql.= "id_inductor_code, peso, cronos, situs) values ($id_evento, $id_evento_code, $id_tarea, $id_tarea_code, ";
            $sql.= "$id_inductor, '$id_inductor_code', $peso, '$this->cronos', '$this->location') ";
            $this->do_sql_show_error('update_evento_ref_inductor', $sql);
         }

    }

    public function delete_evento_ref_inductor($id_inductor) {
        $sql= "delete from tinductor_eventos where id_inductor= $id_inductor ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento= $this->id_evento ";
        if (!empty($this->id_tarea))
            $sql.= "and id_tarea = $this->id_tarea "; 

        $result= $this->do_sql_show_error('delete_evento_ref_inductor', $sql);
    }

    public function listar_id_eventos_ref_inductor($id_inductor) {
        $sql= "select distinct teventos.*, peso from teventos, tinductor_eventos ";
        $sql.= "where teventos.id = tinductor_eventos.id_evento and id_inductor = $id_inductor and peso > 0 ";
        $sql.= "order by fecha_inicio_plan asc, numero asc";
        $result= $this->do_sql_show_error('listar_eventos_ref_inductor', $sql);
        return $result;
    }

    public function listar_eventos_ref_inductor($id_inductor= 0, $flag= true) {
        if (!$this->if_teventos) {
            $obj= new Tbase_plantrab($this->clink);
            $obj->SetYear($this->year);
            $obj->SetMonth($this->month);
            $obj->SetIdProceso($this->id_proceso);
            $obj->set_create_tmp_table_tidx(false);

            $obj->automatic_event_status($this->toshow);
            $this->if_teventos= $obj->if_teventos;
            $this->if_treg_evento= $obj->if_treg_evento;
        }

        if (!isset($obj)) {
            $obj= new Tevento($this->clink);
            $obj->SetYear($this->year);
            $obj->if_teventos= $this->if_teventos;
            $obj->if_treg_evento= $this->if_treg_evento;
        }

        if (isset($this->array_eventos)) 
            unset($this->array_eventos);
        if (isset($this->array_pesos)) 
            unset($this->array_pesos);

        $id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor;

        $sql= "select distinct _teventos.*, peso from _teventos, tinductor_eventos ";
        $sql.= "where _teventos.id = tinductor_eventos.id_evento and id_inductor = $id_inductor and peso > 0 ";
        $sql.= "order by fecha_inicio_plan asc, numero asc";
        $result= $this->do_sql_show_error('listar_eventos_ref_inductor', $sql);
        if ($flag) 
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['id'];
            if (array_key_exists($id, $this->array_pesos))
                continue;
            $this->array_pesos[$id]= $row['peso'];
            ++$i;
            $rowcmp= $obj->get_last_reg($id, $row['id_responsable']);

            $cumplimiento= !is_null($rowcmp) ? $rowcmp['cumplimiento'] : _NO_INICIADO;
            $memo= !is_null($rowcmp) ? $rowcmp['observacion'] : "No existe usuario con la tarea asignada y cumplimiento reportado. Detectedado por el Sistema ". date('d/m/Y H:s');
            $rechazado= !is_null($rowcmp) ? $rowcmp['rechazado'] : null;
            $aprobado= !is_null($rowcmp) ? $rowcmp['aprobado'] : null;

            $time= odbc2ampm(substr($row['time1'],0,5)).'-'.odbc2ampm(substr($row['time2'],0,5));

            $array= array('id'=>$id,'time'=>$time, 'evento'=>$row['nombre'], 'lugar'=>$row['lugar'], 'cumplimiento'=>$cumplimiento,
                'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'], 'memo'=>stripslashes($memo),
                'id_tarea'=>$row['_id_tarea'], 'id_evento'=> $row['_id_evento'], 'empresarial'=>$row['empresarial'],
                'descripcion'=>stripslashes($row['descripcion']), 'id_responsable'=>$row['id_responsable'],
                'id_usuario'=>$row['_id_usuario'], 'id_tipo_evento'=>$row['id_tipo_evento'], 'aprobado'=>$aprobado,
                'rechazado'=>$rechazado, 'year'=>$row['year'], 'cronos'=>$row['_cronos'],
                'id_auditoria'=> $row['_id_auditoria'], 'toshow'=>$row['_toshow'], 'id_proceso'=>$row['_id_proceso']);

            $this->array_eventos[$row['id']]= $array;
        }

        return $i;
    }

    /**
     * proceso
     */
    public function listar_objetivos_ref_proceso($id_proceso= 0, $flag= false) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso; 
        $sql= "select *, id as _id from tobjetivos where id_proceso = $id_proceso ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_objetivos_ref_proceso', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $id_objetivo= $row['_id'];
            $this->array_pesos[$id_objetivo]= 1;
        }
        return $this->array_pesos;
    }

    public function listar_procesos_ref_objetivo($id_objetivo= 0, $flag= false) {
        $id_objetivo= !empty($id_objetivo) ? $id_objetivo : $this->id_objetivo; 

        $sql= "select tprocesos.*, tprocesos.id as _id from tprocesos, tobjetivos ";
        $sql.= "where tprocesos.tipo = "._TIPO_PROCESO_INTERNO." and tobjetivos.id_proceso = tprocesos.id ";
        if (!empty($id_objetivo)) 
            $sql.= "and tobjetivos.id = $id_objetivo ";
        if (!empty($this->year))
            $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_procesos_ref_objetivo', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$id_objetivo][]= $row['_id'];
        }
        return $this->array_pesos;
    }
    
    public function listar_inductores_ref_proceso($id_proceso= 0, $flag= false) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso; 
        $sql= "select *, id as _id from tinductores where id_proceso = $id_proceso ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_inductores_ref_proceso', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $id_inductor= $row['_id'];
            $this->array_pesos[$id_inductor]= 1;
        }
        return $this->array_pesos;
    }

    public function listar_procesos_ref_inductor($id_inductor= 0, $flag= false) {
        $id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor; 
        $sql= "select tprocesos.*, tprocesos.id as _id from tprocesos, tinductores ";
        $sql.= "where tprocesos.tipo = "._TIPO_PROCESO_INTERNO." ";
        if (!empty($id_inductor))
            $sql.= "and tinductores.id = $id_inductor ";
        if (!empty($this->year))
            $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";
        $result= $this->do_sql_show_error('listar_procesos_ref_inductor', $sql);
        if ($flag)
            return $result;

        if (isset($this->array_pesos)) unset($this->array_pesos);
        $this->array_pesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_pesos[$id_inductor][]= $row['_id'];
        }
        return $this->array_pesos;
    }    
    
    /**
     *  funciones generales de calculo
     */
    public function set_observacion($table, $field, $id, $id_code, $observacion= null) {
        $row= $this->get_observacion($table, $field, $id, false);
        $id_row= !empty($row[0]) ? $row[0] : null;

        $id_usuario= $_SESSION['id_usuario'];
        $observacion= !is_null($observacion) ? $observacion : $this->observacion;
        $observacion= setNULL_str($observacion);

        $regdate= $this->year.'-'.str_pad($this->month, 2, '0' ,STR_PAD_LEFT).'-'.str_pad($this->day, 2, '0', STR_PAD_LEFT);
        $value= setNULL($this->value);

        $sql= "update $table set observacion= $observacion, valor= $value, id_usuario = $id_usuario, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where year = $this->year ";
        $sql.= "and month = $this->month and $field = $id and ".date2pg("reg_fecha")." = ".date2pg("'$regdate'")." ";
        if ($this->flag_field_prs)
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($id_row))
            $sql.= "and id = $id_row ";

        $this->do_sql_show_error('set_observacion', $sql);
        $cant= $this->clink->affected_rows();

        if (!empty($cant)) 
            return;

        $field_code= $field.'_code';

        $sql= "insert into $table ($field, $field_code, observacion, id_usuario, year, month, reg_fecha, valor, ";
        if ($this->flag_field_prs)
            $sql.= "id_proceso, id_proceso_code, ";
        $sql.= "cronos, situs) values ($id, '$id_code', $observacion, $id_usuario, $this->year, $this->month, ";
        $sql.= "'$regdate', $value, ";
        if ($this->flag_field_prs)
            $sql.= "$this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "'$this->cronos', '$this->location')";

        $this->do_sql_show_error('set_observacion', $sql);
   }

    public function get_observacion($table, $field, $id, $flag= false) {
        $sql= "select * from $table where year = $this->year and month = $this->month and $field = $id ";
        if (!empty($this->id_proceso) && $this->flag_field_prs)
            $sql.= "and id_proceso = $this->id_proceso ";
        $sql.= "order by cronos desc ";

        $result= $this->do_sql_show_error('get_observacion', $sql);
        if ($flag) 
            return $result;

        $row= $this->clink->fetch_array($result);
        return $row;
   }

    protected function get_reg_origen($table) {
        $text= null;
        $item= null;

        switch($table) {
            case 'treg_politica' :
                $text= "Lineamiento";
                $item= 'pol';
                break;
            case 'treg_objetivo' :
                $text= "Objetivo Estrategico";
                $item= 'obj';
                break;
            case 'treg_inductor' :
                $text= "Objetivo de Trabajo";
                $item= 'ind';
                break;
            case 'treg_perspectiva' :
                $text= "Perspectiva";
                $item= 'per';
                break;
            case 'treg_indicador' :
                $text= "Indicador";
                $item= 'indi';
                break;
            case 'treg_programa' :
                $text= "Programa";
                $item= 'prog';
                break;
        }

        $text= "Registrado para ".$text;
        $array= array('text'=>$text, 'signal'=>$item);
        return $array;
    }
}


/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";
if (!class_exists('Tcell'))
    include_once "cell.class.php";
if (!class_exists('Tusuario'))
    include_once "usuario.class.php";
if (!class_exists('Tproceso'))
    include_once "proceso.class.php";