<?php
/**
 * Description of proceso_item
 *
 * @author mustelier
 */

define('_CLASS_Tproceso_item', 1);

if (!class_exists('Tproceso'))
    include_once "proceso.class.php";

if (!class_exists('Tproceso_item') && class_exists('Tproceso')) {
    class Tproceso_item extends Tproceso {

        public function __construct($clink= null) {
            $this->clink= $clink;
            Tproceso::__construct($clink);
            if (empty($this->cronos))
                $this->cronos= date('Y-m-d H:i:s');
        }
    /*****************************************************************
    * INDICADORES
    *****************************************************************/
        public function setIndicador($action= 'add', $peso= null, $critico= null) {
            $error= null;
            $peso= setNULL($peso);

            if ($action == 'add') {
                $sql= "insert into tproceso_indicadores (id_proceso, id_proceso_code, id_indicador, id_indicador_code, ";
                $sql.= "year, peso, cronos, situs ";
                if (!is_null($critico))
                    $sql.= ", critico ";
                $sql.= ") values ($this->id_proceso, '$this->id_proceso_code', $this->id_indicador, '$this->id_indicador_code', ";
                $sql.= "$this->year, $peso, '$this->cronos', '$this->location'";
                if (!is_null($critico))
                    $sql.= ", ".boolean2pg($critico)." ";
                $sql.= ") ";
            }
            if ($action == 'update') {
                $sql= "update tproceso_indicadores set peso= $peso, cronos= '$this->cronos' ";
                if (!is_null($critico))
                    $sql.= ", critico= ".boolean2pg($critico)." ";
                $sql.= "where id_indicador = $this->id_indicador ";
                $sql.= "and id_proceso = $this->id_proceso ";
                if (!empty($this->year))
                    $sql.= "and year = $this->year ";
            }
            if ($action == 'delete') {
                $sql= "delete from tproceso_indicadores where id_proceso = $this->id_proceso and id_indicador = $this->id_indicador ";
                if (!empty($this->year))
                    $sql.= "and year = $this->year ";
            }

            $error= $this->_set_user($sql);
            return $error;
        }

        public function expand_peso_indicadores($id_indicador= null, $id_indicador_code= null, $peso= null, $critico= null) {
            $_peso= setNULL($peso);

            $id_indicador= !is_null($id_indicador) ? $id_indicador : $this->id_indicador;
            $id_indicador_code= !is_null($id_indicador_code) ? $id_indicador_code : $this->id_indicador_code;

            $sql= "select inicio, fin from tindicadores where id = $id_indicador ";
            $result= $this->do_sql_show_error('expand_peso_indicadores', $sql);
            $row= $this->clink->fetch_array($result);

            $inicio= $row['inicio']; $fin= $row['fin'];

            for ($i= $inicio; $i <= $fin; $i++) {
                $sql= "select * from  tproceso_indicadores where id_indicador = $id_indicador and id_proceso = $this->id_proceso and year = $i ";
                $this->do_sql_show_error('expand_peso_indicadores', $sql);
                $cant= $this->cant;

                if ($cant == 0) {
                    $sql= "update tproceso_indicadores set year = $i, cronos = '$this->cronos' ";
                    if (!is_null($peso))
                        $sql.= ", peso= $_peso ";
                    if (!is_null($critico))
                        $sql.= ", critico= ".boolean2pg($critico)." ";
                    $sql.= "where id_indicador = $id_indicador and id_proceso = $this->id_proceso and year = 0";

                    $this->do_sql_show_error('expand_peso_indicadores', $sql);
                    $cant= $this->clink->affected_rows();
                }

                if ($cant == 0) {
                    $sql= "insert into tproceso_indicadores (id_proceso, id_proceso_code, id_indicador, id_indicador_code, ";
                    $sql.= "year, cronos, situs ";
                    if (!is_null($peso))
                        $sql.= ", peso";
                    if (!is_null($critico))
                        $sql.= ", critico";
                    $sql.= ") values ($this->id_proceso, '$this->id_proceso_code', $id_indicador, '$id_indicador_code', ";
                    $sql.= "$i, '$this->cronos', '$this->location'";
                    if (!is_null($peso))
                        $sql.= ", $_peso ";
                    if (!is_null($critico))
                        $sql.= ", ".boolean2pg($critico)." ";
                    $sql.= ")";

                    $this->do_sql_show_error('expand_peso_indicadores', $sql);
                }
            }
        }

        public function cleanObjetoByIndicador() {
            $sql= "delete from tproceso_indicadores where id_indicador = $this->id_indicador ";
            if (!empty($this->year))
                $sql.= "and year = $this->year ";

            return $this->_clean_object($sql);
        }

        public function GetProcesoIndicador() {
            $sql= "select distinct peso, nombre, tipo, tproceso_indicadores.id_proceso as _id_proceso, ";
            $sql.= "tproceso_indicadores.id_proceso_code as _id_proceso_code from tproceso_indicadores, tprocesos ";
            $sql.= "where tproceso_indicadores.id_proceso = tprocesos.id and id_indicador = $this->id_indicador ";
            if (!empty($this->year)) {
                $sql.= "and year = $this->year ";
                if (!empty($this->year))
                    $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";
            }
            $sql.= "order by tipo asc, nombre asc";

            $result= $this->do_sql_show_error('GetProcesoIndicador', $sql);

            if (isset($this->array_procesos)) unset($this->array_procesos);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id_proceso'], 'id_code'=>$row['_id_proceso_code'], 'nombre'=>$row['nombre'],
                            'tipo'=>$row['tipo'], 'peso'=>$row['peso']);
                $this->array_procesos[$row['_id_proceso']]= $array;
                ++$i;
            }

            $this->cant= $i;
            return $this->array_procesos;
        }

        public function listar_indicadores($flag= true) {
            $sql= "select distinct tindicadores.*, tindicadores.id as _id_indicador, tindicadores.inicio as _inicio, ";
            $sql.= "tindicadores.fin as _fin, tproceso_indicadores.peso as _peso, tproceso_indicadores.critico as _critico, ";
            $sql.= "tproceso_indicadores.year as _year, tindicadores.id_proceso as _id_proceso, ";
            $sql.= "tindicadores.id as _id, tindicadores.nombre as _nombre from tproceso_indicadores, tindicadores ";
            $sql.= "where tindicadores.id = tproceso_indicadores.id_indicador ";
            if (!empty($this->id_proceso))
                $sql.= "and tproceso_indicadores.id_proceso = $this->id_proceso ";
            if (!empty($this->year))
                $sql.= "and ((inicio <= $this->year and fin >= $this->year) and tproceso_indicadores.year = $this->year) ";

            $result= $this->do_sql_show_error('listar_indicadores', $sql);

            if (!$flag)
                return $result;
            if (empty($this->cant))
                return null;

            if (isset($this->array_indicadores)) unset($this->array_indicadores);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id_indicador'], 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'],
                        'peso'=>$row['_peso'], 'critico'=> boolean($row['_critico']), 'inicio'=>$row['_inicio'], 'fin'=>$row['_fin'],
                        'year'=>$row['_year'], 'id_proceso'=>$row['_id_proceso']);

                $this->array_indicadores[$row['_id_indicador']]= $array;
                ++$i;
            }

            $this->cant= $i;
            return $this->array_indicadores;
        }


    /*****************************************************************
    *  EVENTOS 
    *****************************************************************/
        /*
         * toshow si se le asigna -1 enla base de datos aparecera null
         * toshow= -1 => null
         * toshow= 0 => 0 plan individual
         * toshow= 1 => 1 plan mensual
         * toshow= 2 => 2  plan anual
         */
        public function setEvento($action= 'add', $year= null, $toshow= null, $className= "Tevento", 
                                                                                $multi_query= false) {
            $year= !empty($year) ? $year : $this->year;
            $toshow= !is_null($toshow) ? $toshow : $this->toshow;
            $className= !empty($className) ? $className : "Tevento";
            $multi_query= !is_null($multi_query) ? $multi_query : false;

            if ($toshow == -1)
                $toshow= null;

            if (empty($this->id_evento) && empty($this->id_auditoria) && empty($this->id_tarea))
                return null;

            $id_tarea= setNULL_empty($this->id_tarea);
            $id_tarea_code= setNULL_str($this->id_tarea_code);

            $id_auditoria= setNULL_empty($this->id_auditoria);
            $id_auditoria_code= setNULL_str($this->id_auditoria_code);

            $id_evento= setNULL_empty($this->id_evento);
            $id_evento_code= setNULL_str($this->id_evento_code);
            $toshow= setNULL($toshow);

            $cumplimiento= setNULL($this->cumplimiento);
            $aprobado= setNULL_str($this->aprobado);
            $id_responsable_aprb= setNULL($this->id_responsable_aprb);
            $observacion= setNULL_str($this->observacion);

            $rechazado= setNULL_str($this->rechazado);
            
            $sql= null;

            if ($action == 'add' || $action == 'update') {
                if ($className != "Ttarea") {
                    $id= $className == "Tevento" ? $this->id_evento : $this->id_auditoria;
                    $this->set_tidx($id, $className);
                }
                $empresarial= setNULL($this->empresarial);
                $id_tipo_evento= setNULL($this->id_tipo_evento);
                $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);
                $indice= setNULL($this->indice);
                $indice_plus= setNULL($this->indice_plus);
            }

            if ($action == 'add') {
                $sql= "insert into tproceso_eventos_$year (id_proceso, id_proceso_code, id_evento, id_evento_code, ";
                $sql.= "id_responsable, toshow, empresarial, id_tipo_evento, id_tipo_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
                $sql.= "id_auditoria_code, cumplimiento, aprobado, id_responsable_aprb, observacion, rechazado, id_usuario, indice, ";
                $sql.= "indice_plus, cronos, situs) values ($this->id_proceso, '$this->id_proceso_code', $id_evento, $id_evento_code, ";
                $sql.= "$this->id_responsable, $toshow, $empresarial, $id_tipo_evento, $id_tipo_evento_code, $id_tarea, $id_tarea_code, ";
                $sql.= "$id_auditoria, $id_auditoria_code, $cumplimiento, $aprobado, $id_responsable_aprb, $observacion, $rechazado, ";
                $sql.= "{$_SESSION['id_usuario']}, $indice, $indice_plus, '$this->cronos', '$this->location') ";
            }
            if ($action == 'update') {
                $sql= "update tproceso_eventos_$year set toshow= $toshow, empresarial= $empresarial, id_tipo_evento= $id_tipo_evento, ";
                $sql.= "id_tipo_evento_code= $id_tipo_evento_code, id_responsable= $this->id_responsable, cumplimiento= $cumplimiento, ";
                $sql.= "aprobado= $aprobado, id_responsable_aprb= $id_responsable_aprb, observacion= $observacion, rechazado= $rechazado, ";
                $sql.= "indice= $indice, indice_plus= $indice_plus, id_usuario= {$_SESSION['id_usuario']} ";
                $sql.= "where id_proceso = $this->id_proceso ";
                if (!empty($this->id_evento))
                    $sql.= "and id_evento = $this->id_evento ";
                if (!empty($this->id_tarea))
                    $sql.= "and id_tarea = $this->id_tarea ";
                if (!empty($this->id_auditoria))
                    $sql.= "and id_auditoria = $this->id_auditoria ";
            }
            if ($action == 'delete') {
                $sql= "delete from tproceso_eventos_$year where 1 ";
                if (!empty($this->id_proceso))
                    $sql.= "and id_proceso = $this->id_proceso ";
                if (!empty($this->id_evento))
                    $sql.= "and id_evento = $this->id_evento ";
                if (!empty($this->id_tarea))
                    $sql.= "and id_tarea = $this->id_tarea ";
                if (!empty($this->id_auditoria))
                    $sql.= "and id_auditoria = $this->id_auditoria ";
            }
            $sql.= "; ";
            
            if (!$multi_query && !empty($sql))
                $this->_set_user($sql);

            if ($action == 'delete' && !empty($sql))
                $sql.= $this->add_to_tdelete("tproceso_eventos", $multi_query);

            return $multi_query ? $sql : $this->error;
        }

        public function set_task_usuario($id_usuario, $action= 'add') {
            $this->listar_notas(true);
            $this->listar_riesgos(true);

            $obj=new Ttarea($this->clink);
            $obj->SetYear($this->year);

            $obj->listar_by_usuario($id_usuario, true);
            $init_array= true;

            foreach ($obj->array_tareas as $tarea) {
                $obj->get_notas($tarea['id'], null, true, $init_array);
                $obj->get_riesgos($tarea['id'], null, true, $init_array);
                $init_array= true;
            }

            $array_tareas= array();

            foreach ($obj->array_notas as $array) {
                if (array_key_exists($array['id'], (array)$array_tareas))
                    continue;

                $this->id_tarea= $array['id'];
                $this->id_tarea_code= $array['id_code'];
                $this->setEvento($action);
            }

            foreach ($obj->array_riesgos as $array) {
                if (array_key_exists($array['id'], (array)$array_tareas))
                    continue;

                $this->id_tarea= $array['id'];
                $this->id_tarea_code= $array['id_code'];
                $this->setEvento($action);
            }
        }

        public function cleanObjetoByEvento() {
            $sql= "delete from tproceso_eventos_{$this->year} where 1 ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            if (!empty($this->id_auditoria))
                $sql.= "and id_auditoria = $this->id_auditoria ";

            return $this->_clean_object($sql);
        }


        private function _get_procesos($sql, $id_responsable= null, $id_proceso_sup= null) {
            $result= $this->do_sql_show_error('_get_procesos', $sql);

            $array_procesos= null;
            if (isset($this->array_procesos)) unset($this->array_procesos);
            $this->array_procesos= null;

            $array_ids= array();
            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                if ($array_ids[$row['_id']])
                    continue;
                $array_ids[$row['_id']]= $row['_id'];

                ++$i;
                $array= array('id'=>$row['_id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                    'toshow'=>$row['toshow'], 'if_entity'=>$row['if_entity'], 'id_responsable'=>$row['_id_responsable'],
                    'aprobado'=>$row['aprobado'], 'id_responsable_aprb'=>$row['id_responsable_aprb'],
                    'observacion'=>$row['observacion'], 'cumplimiento'=>$row['cumplimiento'], 'id_entity'=>$row['id_entity'],
                    'id_tipo_evento'=>$row['id_tipo_evento'], 'id_tipo_evento_code'=>$row['id_tipo_evento_code'],
                    'empresarial'=>$row['empresarial'], 'rechazado'=>$row['rechazado'], 'id_evento'=>$row['id_evento'], 
                    'id_evento_code'=>$row['id_evento_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'], 
                    'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'], 
                    'id_responsable_prs'=>$row['_id_responsable_prs'], 'indice'=>$row['indice'], 'indice_plus'=>$row['indice_plus']);

                if (is_null($id_responsable))
                    $this->array_procesos[$row['_id']]= $array;
                $array_procesos[$row['_id']]= $array;
            }

            if (is_null($id_responsable) && empty($id_proceso_sup))
                return $this->array_procesos;

            if (isset($this->array_procesos)) unset($this->array_procesos);
            $this->array_procesos= null;

            if (!empty($id_responsable)) {
                $this->getProceso_if_jefe($id_responsable, NULL, _TIPO_DIRECCION);

                foreach ($array_procesos as $array) {
                    $id= $array['id'];
                    if (array_key_exists($id, $this->array_cascade_up) == true)
                        $this->array_procesos[$id]= $array;
                }
                return $this->array_procesos;
            }

            if (!empty($id_proceso_sup)) {
                $this->id_proceso= $id_proceso_sup;
                $obj_prs= new Tproceso($this->clink);
                $obj_prs->Set($this->id_proceso);
                $this->tipo= $obj_prs->GetTipo();

                $this->array_cascade_up= $this->listar_in_order("eq_desc", true);
                if (isset($this->array_procesos)) unset($this->array_procesos);
                $this->array_procesos= null;

                foreach ($array_procesos as $array) {
                    $id= $array['id'];
                    if (array_key_exists($id, $this->array_cascade_up) == true)
                        $this->array_procesos[$id]= $array;
                }

                return $this->array_procesos;
            }
        }

        public function GetProcesoEvento($id_evento= null, $id_proceso_sup= null) {
            $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

            $sql= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.id_code as _id_code, indice, indice_plus, ";
            $sql.= "id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "tproceso_eventos_{$this->year}.toshow as toshow, tproceso_eventos_{$this->year}.rechazado as rechazado, ";
            $sql.= "tproceso_eventos_{$this->year}.id_responsable as _id_responsable, aprobado, id_responsable_aprb, ";
            $sql.= "tprocesos.id_responsable as _id_responsable_prs, observacion, cumplimiento, empresarial, ";
            $sql.= "id_tipo_evento, id_tipo_evento_code, tproceso_eventos_{$this->year}.cronos as _cronos ";
            $sql.= "from tproceso_eventos_{$this->year}, tprocesos where id_evento = $id_evento";
            $sql.= " and tproceso_eventos_{$this->year}.id_proceso = tprocesos.id ";
            $sql.= "and tproceso_eventos_{$this->year}.toshow is not null order by tproceso_eventos_{$this->year}.cronos desc";

            return $this->_get_procesos($sql, null, $id_proceso_sup);
        }

        public function GetProcesoTarea($id_tarea= null, $id_proceso_sup= null, $year= null) {
            $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
            $year= !empty($year) ? $year : $this->year;

            $sql= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.id_code as _id_code, indice, indice_plus, ";
            $sql.= "id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "tproceso_eventos_{$year}.toshow as toshow, tproceso_eventos_{$this->year}.rechazado as rechazado, ";
            $sql.= "tproceso_eventos_{$this->year}.id_responsable as _id_responsable, aprobado, id_responsable_aprb, ";
            $sql.= "tprocesos.id_responsable as _id_responsable_prs, observacion, cumplimiento, empresarial, ";
            $sql.= "id_tipo_evento, id_tipo_evento_code, tproceso_eventos_{$this->year}.cronos as _cronos ";
            $sql.= "from tproceso_eventos_{$year}, tprocesos where id_tarea = $id_tarea ";
            $sql.= "and tproceso_eventos_{$year}.id_proceso = tprocesos.id ";
            $sql.= "and tproceso_eventos_{$year}.toshow is not null order by tproceso_eventos_{$this->year}.cronos desc";

            return $this->_get_procesos($sql, null, $id_proceso_sup);
        }

    /*****************************************************************
    *  AUDITORIAS 
    *****************************************************************/
        public function listar_auditorias() {
            $sql= "select distinct tauditorias.*, tauditorias.id as _id from tauditorias, tproceso_eventos_{$this->year} ";
            $sql.= "where tauditorias.id = tproceso_eventos_{$this->year}.id_auditoria ";
            $sql.= "and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso ";
            $sql.= "and tproceso_eventos_{$this->year}.toshow is not null ";
            $sql.= "and (YEAR(tauditorias.fecha_inicio_plan) = $this->year or YEAR(tauditorias.fecha_real_plan) = $this->year) ";
            $sql.= "order by fecha_inicio_real asc ";

            $result= $this->do_sql_show_error('listar_auditorias', $sql);
            return $result;
        }

        public function GetProcesoAuditoria($id= null, $id_responsable= null, $id_proceso_sup= null) {
            $id= !empty($id) ? $id : $this->id_auditoria;

            $sql= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.id_code as _id_code, indice, indice_plus, ";
            $sql.= "id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "tproceso_eventos_{$this->year}.toshow as toshow, tproceso_eventos_{$this->year}.rechazado as rechazado, ";
            $sql.= "tproceso_eventos_{$this->year}.id_responsable as _id_responsable, aprobado, id_responsable_aprb, ";
            $sql.= "observacion, cumplimiento,  empresarial, id_tipo_evento, id_tipo_evento_code, tproceso_eventos_{$this->year}.cronos as _cronos ";
            $sql.= "from tproceso_eventos_{$this->year}, tprocesos where id_auditoria = $id ";
            if(!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            $sql.= "and tproceso_eventos_{$this->year}.id_proceso = tprocesos.id ";
            $sql.= "and tproceso_eventos_{$this->year}.toshow is not null order by tproceso_eventos_{$this->year}.cronos desc";

            return $this->_get_procesos($sql, $id_responsable, $id_proceso_sup);
        }

    /*****************************************************************
    *  PROYECTOS
    *****************************************************************/
        public function setProyecto($action= 'add', $year= null) {
            $year= !empty($year) ? $year : $this->year;
            $error= null;

            if ($action == 'add' || $action == 'update') {
                $sql= "select id from tproceso_proyectos where year = $year and id_proceso = $this->id_proceso ";
                if (!empty($this->id_proyecto))
                    $sql.= "and id_proyecto = $this->id_proyecto ";
                if (!empty($this->id_programa) && $action == 'add')
                    $sql.= "and id_programa = $this->id_programa ";

                $this->do_sql_show_error('setProyecto{select}', $sql);
                $cant= $this->cant;
                if ($cant > 0 && $action == 'add')
                    return null;

                $id_proyecto= setNULL_empty($this->id_proyecto);
                $id_proyecto_code= setNULL_str($this->id_proyecto_code);

                $id_programa= setNULL_empty($this->id_programa);
                $id_programa_code= setNULL_str($this->id_programa_code);

                if ($action == 'update') {
                    $sql= "update tproceso_proyectos set id_programa= $id_programa, id_programa_code= $id_programa_code, ";
                    $sql.= "cronos= '$this->cronos', situs= '$this->location' ";
                    $sql.= "where id_proyecto = $this->id_proyecto and year = $year and id_proceso = $this->id_proceso ";

                    $result= $this->do_sql_show_error('setProyecto{add}', $sql);
                    if ($this->cant == 0)
                        $action= 'add';
                }
                if ($action == 'add') {
                    $sql= "insert into tproceso_proyectos (id_proceso, id_proceso_code, id_proyecto, id_proyecto_code, id_programa, ";
                    $sql.= "id_programa_code, year, cronos, situs) values ($this->id_proceso, '$this->id_proceso_code', $id_proyecto, ";
                    $sql.= "$id_proyecto_code, $id_programa, $id_programa_code, $year, '$this->cronos', '$this->location')";

                    $result= $this->do_sql_show_error('setProyecto{update}', $sql);
                }
            }
            if ($action == 'delete') {
                $sql= "delete from tproceso_proyectos where id_proceso = $this->id_proceso and year = $year ";
                if (!empty($this->id_proyecto))
                    $sql.= "and id_proyecto = $this->id_proyecto ";
                if (!empty($this->id_programa))
                    $sql.= "and id_programa = $this->id_programa ";

                $result= $this->do_sql_show_error('setProyecto{delete}', $sql);
            }

            return $this->error;
        }

        public function cleanObjetoByProyecto($year= null) {
             $sql= "delete from tproceso_proyectos where 1 ";
             if (!empty($year))
                 $sql.= "and year >= $year ";
             if (!empty($this->id_proyecto))
                 $sql.= "and id_proyecto = $this->id_proyecto ";
             if (!empty($this->id_programa))
                 $sql.= "and id_programa = $this->id_programa ";

             return $this->_clean_object($sql);
        }

        public function GetProcesosProyecto($clean_array= true) {
            $sql= "select distinct nombre, tipo, tprocesos.id as _id, tprocesos.id_code as _id_code, id_entity ";
            $sql.= "from tproceso_proyectos, tprocesos where tproceso_proyectos.id_proceso = tprocesos.id ";
            if (!empty($this->id_proyecto))
                $sql.= "and id_proyecto = $this->id_proyecto ";
            if (!empty($this->id_programa))
                $sql.= "and id_programa = $this->id_programa ";

            $result= $this->do_sql_show_error('GetProcesosProyecto', $sql);

            if (isset($this->array_procesos) && $clean_array)
                unset($this->array_procesos);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'],  
                            'tipo'=>$row['tipo'], 'id_entity'=>$row['id_entity']);
                $this->array_procesos[$row['_id']]= $array;
            }
            return $this->array_procesos;
        }

     /*****************************************************************
    *  RIESGOS 
    *****************************************************************/
        public function setRiesgo($action= 'add') {
            $error= null;

            $id_riesgo= setNULL($this->id_riesgo);
            $id_riesgo_code= setNULL_str($this->id_riesgo_code);

            $id_nota= setNULL_empty($this->id_nota);
            $id_nota_code= setNULL_str($this->id_nota_code);

            $id_requisito= setNULL_empty($this->id_requisito);
            $id_requisito_code= setNULL_str($this->id_requisito_code);

            $year= setNULL($this->year);

            if ($action == 'add') {
                $sql= "insert into tproceso_riesgos (id_proceso, id_proceso_code, id_riesgo, id_riesgo_code, id_nota, id_nota_code, ";
                $sql.= "id_requisito, id_requisito_code, year, cronos, situs) values ($this->id_proceso, '$this->id_proceso_code', ";
                $sql.= "$id_riesgo, $id_riesgo_code, $id_nota, $id_nota_code, $id_requisito, $id_requisito_code, $year, '$this->cronos', ";
                $sql.= "'$this->location') ";
            } else {
                $sql= "delete from tproceso_riesgos where id_proceso = $this->id_proceso ";
                if (!empty($this->id_riesgo))
                    $sql.= "and id_riesgo = $this->id_riesgo ";
                if (!empty($this->id_nota))
                    $sql.= "and id_nota = $this->id_nota ";
            }

            $error= $this->_set_user($sql);
            return $error;
        }

        public function cleanObjetoByRiesgo() {
            $sql= "delete from tproceso_riesgos ";
            if (!empty($this->id_riesgo))
                $sql.= "where id_riesgo = $this->id_riesgo ";
            if (!empty($this->id_nota))
                $sql.= "where id_nota = $this->id_nota ";

            return $this->_clean_object($sql);
        }

        public function GetProcesosRiesgo($clean_array= true) {
            $sql= "select distinct tprocesos.*, tprocesos.id as _id from tproceso_riesgos, tprocesos ";
            $sql.= "where tproceso_riesgos.id_proceso = tprocesos.id ";
            if (!empty($this->id_riesgo))
                $sql.= "and id_riesgo = $this->id_riesgo ";
            if (!empty($this->id_nota))
                $sql.= "and id_nota = $this->id_nota ";
            $result= $this->do_sql_show_error('GetProcesosRiesgo', $sql);

            if (isset($this->array_procesos) && $clean_array)
                unset($this->array_procesos);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                ++$i;
                $array= array('id'=>$row['_id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'], 'id_entity'=>$row['id_entity']);
                $this->array_procesos[$row['_id']]= $array;
            }
            $this->cant= $i;
            return $this->array_procesos;
        }

        public function GetProcesosNota($clean_array= true) {
            return $this->GetProcesosRiesgo($clean_array);
        }

        /**
         * lista los riesgos y las notas asociados al proceso
         * @return resource
         */
        private function listar_items($item, $flag= false) {
            if ($item == 'nota' && isset($this->array_notas))
                unset($this->array_notas);
            if ($item == 'riesgo' && isset($this->array_riesgos))
                unset($this->array_riesgos);

            $table= "t$item"."s";
            $sql= "select distinct $table.*, $table.id as _id from $table, tproceso_riesgos where $table.id = tproceso_riesgos.id_$item ";
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_proceso ";

            if (!empty($this->year)) {
                $sql.= "and tproceso_riesgos.year = $this->year ";
                $sql.= "and (YEAR(fecha_inicio_plan) = $this->year and YEAR(fecha_fin_plan) = $this->year) ";
            }
            $sql.= "order by fecha_inicio_real asc ";

            $result= $this->do_sql_show_error('listar_notas', $sql);
            if (!$flag)
                return $result;

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
                ++$i;
                if ($item == 'nota')
                    $this->array_notas[$row['id']]= $array;
                if ($item == 'riesgo')
                    $this->array_riesgos[$row['id']]= $array;
            }
            $this->cant= $i;

            if ($item == 'nota')
                return $this->array_notas;
            if ($item == 'riesgo')
                return $this->array_riesgos;
        }

        public function listar_notas($flag= false) {
            return $this->listar_items('nota', $flag);
        }
        public function listar_riesgos($flag= false) {
            return $this->listar_items('riesgo', $flag);
        }

    /*****************************************************************
    *  OBJETIVOS 
    *****************************************************************/
        public function getProcesoObjetivo() {
            $sql= "select distinct tprocesos.nombre, tprocesos.tipo, tprocesos.id as _id, tprocesos.id_code as _id_code ";
            $sql.= "from tprocesos, tproceso_objetivos where tprocesos.id = tproceso_objetivos.id_proceso ";
            if (!empty($this->id_objetivo))
                $sql.= "and tproceso_objetivos.id_objetivo = $this->id_objetivo ";
            if (!empty($this->year))
                $sql.= "and year = $this->year ";
            $sql.= "order by tipo asc, nombre asc";

            $result= $this->do_sql_show_error('getProcesoObjetivo', $sql);

            if (isset($this->array_procesos)) unset($this->array_procesos);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo']);
                $this->array_procesos[$row['_id']]= $array;
                ++$i;
            }
            $this->cant= $i;
            return $this->array_procesos;
        }

    /*****************************************************************
    *  LISTAS 
    *****************************************************************/
        public function getProcesoLista() {
            $sql= "select distinct tprocesos.nombre, tprocesos.tipo, tprocesos.id as _id, tprocesos.id_code as _id_code, ";
            $sql.= "tprocesos.id_proceso as _id_proceso, conectado from tprocesos, tproceso_listas ";
            $sql.= "where tprocesos.id = tproceso_listas.id_proceso ";
            if (!empty($this->id_lista))
                $sql.= "and tproceso_listas.id_lista = $this->id_lista ";
            if (!empty($this->year))
                $sql.= "and year = $this->year ";
            $sql.= "order by tipo asc, nombre asc";
            $result= $this->do_sql_show_error('getProcesoLista', $sql);

            if (isset($this->array_procesos)) 
                unset($this->array_procesos);

            $i= 0;
            while ($row= $this->clink->fetch_array($result)) {
                $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                            'id_proceso'=>$row['_id_proceso'], 'conectado'=>$row['conectado']);
                $this->array_procesos[$row['_id']]= $array;
                ++$i;
            }
            $this->cant= $i;
            return $this->array_procesos;
        }

        public function setLista($action= 'add') {
            if ($action == 'add') {
                $sql= "insert into tproceso_listas (id_proceso, id_proceso_code, id_lista, id_lista_code, ";
                $sql.= "year, id_usuario, cronos, situs) values ($this->id_proceso, '$this->id_proceso_code', ";
                $sql.= "$this->id_lista, '$this->id_lista_code', $this->year, {$_SESSION['id_usuario']}, '$this->cronos', ";
                $sql.= "'$this->location') ";

            } else {
                $sql= "delete from tproceso_listas where id_proceso = $this->id_proceso ";
                if (!empty($this->id_lista))
                    $sql.= "and id_lista = $this->id_lista ";
                if (!empty($this->year))
                    $sql.= "and year = $this->year ";
            }

            $error= $this->_set_user($sql);
            return $error;
        }

    /*****************************************************************
    *  REQUISITOS
    *****************************************************************/
    public function getProcesoRequisito() {
        $sql= "select distinct tprocesos.nombre, tprocesos.tipo, tprocesos.id as _id, tprocesos.id_code as _id_code, ";
        $sql.= "tprocesos.id_proceso as _id_proceso from tprocesos, tproceso_listas where tprocesos.id = tproceso_listas.id_proceso ";
        if (!empty($this->id_requisito))
            $sql.= "and tproceso_listas.id_requisito = $this->id_requisito ";            
        if (!empty($this->year))
            $sql.= "and year = $this->year ";
        $sql.= "order by tipo asc, nombre asc";

        $result= $this->do_sql_show_error('getProcesoRequisito', $sql);

        if (isset($this->array_procesos)) unset($this->array_procesos);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'], 
                        'id_proceso'=>$row['_id_proceso']);
            $this->array_procesos[$row['_id']]= $array;
            ++$i;
        }
        $this->cant= $i;
        return $this->array_procesos;
    }

    public function setRequisito($action= 'add') {
        if ($action == 'add') {
            $sql= "insert into tproceso_listas (id_proceso, id_proceso_code, id_lista, id_lista_code, ";
            $sql.= "id_requisito, id_requisito_code, year, id_usuario, cronos, situs) values ($this->id_proceso, ";
            $sql.= "'$this->id_proceso_code', $this->id_lista, '$this->id_lista_code', $this->id_requisito, ";
            $sql.= "'$this->id_requisito_code', $this->year, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location') ";

        } else {
            $sql= "delete from tproceso_listas where id_proceso = $this->id_proceso ";
            if (!empty($this->id_requisito))
                $sql.= "and id_requisito = $this->id_requisito ";
            if (!empty($this->year))
                $sql.= "and year = $this->year ";
        }

        $error= $this->_set_user($sql);
        return $error;
    }

    //////////////////////////////////////////////////////////////////////////////////////
       public function update_code() {
            $result= $this->clink->tables();

            while ($row = $this->clink->fetch_row($result)) {
                $table= $row[0];
                $sql= "update $table set id_proceso_code = '$this->id_proceso_code', cronos= '$this->cronos', ";
                $sql.= "situs= '$this->location' where id_proceso = $this->id_proceso ";
                $this->do_sql_show_error('update_code',$sql,null,false);
            }
       }
    }
}
