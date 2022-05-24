<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

define('_CLASS_Tproceso', 1);

if (!class_exists('Tbase_proceso'))
    include_once "base_proceso.class.php";

class Tproceso extends Tbase_proceso {
    private $control_recursive;
    private $if_tmp_tprocesos;

    public function __construct($clink= null) {
        Tbase_proceso::__construct($clink);
        $this->init_control_recursive= true;
        $this->if_tmp_procesos= false;
        $this->clink= $clink;
    }

    private function _create_tmp_tproceos() {
        $sql= "drop table if exists _tmp_tprocesos ";
        $this->do_sql_show_error('_create_tmp_tproceos', $sql);

        $sql= "CREATE TEMPORARY TABLE _tmp_tprocesos (";
            $sql.= "id INTEGER(11), ";
            $sql.= "id_code CHAR(12), ";
            $sql.= "nombre VARCHAR(180), ";
            $sql.= "tipo SMALLINT, ";
            $sql.= "if_entity TINYINT(1), ";
            $sql.= "id_entity INTEGER(11), ";
            $sql.= "id_entity_code CHAR(12), ";
            $sql.= "codigo CHAR(2), ";
            $sql.= "id_proceso INTEGER(11), ";
            $sql.= "id_proceso_code CHAR(12), ";
            $sql.= "id_responsable INTEGER(11), ";
            $sql.= "responsable VARCHAR(180), ";
            $sql.= "cargo VARCHAR(180), ";
            $sql.= "conectado TINYINT, ";
            $sql.= "inicio MEDIUMINT, ";
            $sql.= "fin MEDIUMINT, ";
            $sql.= "local_archive VARCHAR(12) ";
        $sql.= ") ";

        $result= $this->do_sql_show_error('_create_tmp_tproceos', $sql);
    }

    private function _create_clone_tmp_table($table) {
        $new_table= $table."_clone";
        $sql= "drop table if exists $new_table ";
        $this->do_sql_show_error('_create_clone_tmp_table', $sql);

        $sql= "CREATE TEMPORARY TABLE $new_table ";
        $sql.= "select * from $table";

        $result= $this->do_sql_show_error('_create_clone_tmp_table', $sql);
        return $new_table;
    }

    private function _insert_into_tmp_tprocesos($sql_select) {
        $sql= "insert into _tmp_tprocesos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= $sql_select;

        $result= $this->do_sql_show_error('_insert_into_tmp_tprocesos', $sql);
    }

    private function _create_tmp_tproceos_block_1($corte) {
        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';

        $sql= "select distinct t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo as _tipo, ";
        $sql.= "t1.if_entity, t1.id_entity, t1.id_entity_code, t1.codigo, t1.id_proceso as _id_proceso, ";
        $sql.= "t1.id_proceso_code as _id_proceso_code, t1.id_responsable, tusuarios.nombre as responsable, ";
        $sql.= "cargo, t1.conectado as _conectado, t1.inicio as _inicio, t1.fin as _fin, local_archive ";
        $sql.= "from $tprocesos as t1, tusuarios where t1.id_responsable = tusuarios.id ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null or t1.id = $this->id_entity) ";
        if (!empty($this->id_proceso))
            $sql.= "and t1.id_proceso = $this->id_proceso ";
        if ($corte)
            $sql.= "and t1.tipo <= $corte ";
        if (!empty($this->id_responsable))
            $sql.= "and tusuarios.id = $this->id_responsable ";
        if (!empty($this->conectado))
            $sql.= "and (t1.conectado <> 1) ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
          if (!empty($this->conectado))
            $sql.= "and (t1.conectado <> 1) ";

        $this->_insert_into_tmp_tprocesos($sql);
    }

    private function _create_tmp_tproceos_block_2($corte) {
        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';

        $sql= "select distinct t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo as _tipo, ";
        $sql.= "t1.if_entity, t1.id_entity, t1.id_entity_code, t1.codigo, t1.id_proceso as _id_proceso, ";
        $sql.= "t1.id_proceso_code as _id_proceso_code, t1.id_responsable, tusuarios.nombre as responsable, cargo, ";
        $sql.= "t1.conectado as _conectado, t1.inicio as _inicio, t1.fin as _fin, t1.local_archive ";
        $sql.= "from $tprocesos as t1, tprocesos as t2, tusuarios where t1.id_responsable = tusuarios.id ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null ot t1.id = $this->id_entity) ";
        if (!empty($this->tipo) && !empty($this->id_proceso))
            $sql.= "and (t1.tipo <= $this->tipo and (t1.id = t2.id_proceso and t2.id = $this->id_proceso)) ";
        if ($corte)
            $sql.= "and t1.tipo <= $corte ";
        if (!empty($this->id_responsable))
            $sql.= "and tusuarios.id = $this->id_responsable ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        if (!empty($this->conectado))
            $sql.= "and (t1.conectado <> 1) ";

        $this->_insert_into_tmp_tprocesos($sql);
    }

    private function _create_tmp_tproceos_block_3($corte) {
        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';

        $sql= "select distinct t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo as _tipo, ";
        $sql.= "t1.if_entity, t1.id_entity, t1.id_entity_code, ";
        $sql.= "t1.codigo, t1.id_proceso as _id_proceso, t1.id_proceso_code as _id_proceso_code,  t1.id_responsable, ";
        $sql.= "tusuarios.nombre as responsable, cargo, t1.conectado as _conectado, t1.inicio as _inicio, t1.fin as _fin, ";
        $sql.= "local_archive from $tprocesos as t1, tusuarios where t1.id_responsable = tusuarios.id ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null or t1.id = $this->id_entity) ";
        if ($corte)
            $sql.= "and t1.tipo <= $corte ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        if (!empty($this->conectado))
            $sql.= "and (t1.conectado <> 1) ";

         $this->_insert_into_tmp_tprocesos($sql);
    }

    private function create_tmp_tproceos($asc, $corte= _MAX_TIPO_PROCESO) {
        $this->_create_tmp_tproceos();
        if (is_null($this->error)) {
            $this->_create_tmp_tproceos_block_1($corte);
        }
        if (is_null($this->error)) {
            $this->_create_tmp_tproceos_block_2($corte);
        }
        if (is_null($this->error) && $asc == 'eq_asc_desc') {
            $this->_create_tmp_tproceos_block_3($corte);
        }
        $this->if_tmp_tprocesos= is_null($this->error) ? true : false;
    }

    public function listar_all($asc, $flag= true, $corte= _MAX_TIPO_PROCESO, $order= 'asc', $create_tmp_procesos= true) {
        $this->if_tmp_procesos= false;

        $asc= !is_null($asc) ? $asc : "eq_asc_desc";
        $corte= !is_null($corte) ? $corte : _MAX_TIPO_PROCESO;
        $order= !is_null($order) ? $order : 'asc';
        $create_tmp_procesos= !is_null($create_tmp_procesos) ? $create_tmp_procesos : true;

        if ($create_tmp_procesos)
            $this->create_tmp_tproceos($asc, $corte);

        $tmp_procesos= $this->if_tmp_tprocesos ? "_tmp_tprocesos" : "tprocesos";
        if ($this->if_tmp_tprocesos) 
            $tmp_procesos2= $this->_create_clone_tmp_table($tmp_procesos);
        else 
            $tmp_procesos2= $tmp_procesos;

        $sql= "select distinct t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo, t1.if_entity, ";
        $sql.= "t1.id_entity, t1.id_entity as _id_entity, t1.id_entity_code, t1.id_entity_code as _id_entity_code, ";
        $sql.= "t1.codigo, t1.id_proceso as _id_proceso, t1.id_proceso_code as _id_proceso_code, ";
        if ($this->if_tmp_tprocesos)
            $sql.= "t1.responsable, t1.cargo, ";
        $sql.= "t1.id_responsable, t1.conectado as _conectado, t1.inicio, t1.fin, t1.local_archive ";
        $sql.= !empty($this->id_entity) ? "from $tmp_procesos as t1, $tmp_procesos2 as t2 " : "from $tmp_procesos as t1 ";
        $sql.= " where 1 ";
        if (!empty($this->id_entity)) {
            $sql.= "and ((t1.id_entity = $this->id_entity or t1.id = $this->id_entity or t1.id = $this->id_entity) ";
            $sql.= "or (t1.id_entity is null and (t1.id_proceso = $this->id_entity ";
            $sql.= "or (t1.id = t2.id_proceso and t2.id = $this->id_entity)"; 
            $sql.= "))) ";
        }        
        $sql.= "order by t1.tipo $order, _nombre ASC";

        $result= $this->do_sql_show_error('listar_all', $sql);
        if ($flag)
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['_nombre'], 'tipo'=>$row['tipo'],
                          'if_entity'=> boolean($row['if_entity']), 'id_entity'=>$row['id_entity'], 'id_entity_code'=>$row['id_entity_code'],
                          'conectado'=>$row['_conectado'], 'id_proceso'=>$row['_id_proceso'], 'local_archive'=> boolean($row['local_archive']),
                          'id_responsable'=>$row['id_responsable'], 'codigo'=>$row['codigo'], 'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_procesos[$row['_id']]= $array;
        }
        return $this->array_procesos;
    }

    public function listar($flag= true, $plus= null, $order= 'asc', $asc= null, $corte= null) {
        $flag= !is_null($flag) ? $flag : true;

        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';

        if ($this->if_copy_tprocesos && $this->use_copy_tprocesos)
            $tprocesos2= $this->_create_clone_tmp_table($tprocesos);
        else 
            $tprocesos2= $tprocesos;

        $t2= ((!is_null($plus) && strpos($plus, 't2.') !== false) || ($asc == 'eq_asc_desc' || $asc == 'eq_asc' || $asc == 'asc')) ? true : false;

        $sql= "select distinct t1.*, t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo as _tipo, ";
        $sql.= "tusuarios.nombre as responsable, cargo, t1.descripcion as _descripcion, t1.id_proceso as _id_proceso, ";
        $sql.= "t1.id_responsable as _id_responsable, t1.conectado as _conectado, t1.inicio as _inicio, t1.fin as _fin, ";
        $sql.= "t1.if_entity as _if_entity, t1.id_entity as _id_entity, t1.id_entity_code as _id_entity_code ";
        $sql.= "from $tprocesos as t1, tusuarios ";
        if ($t2)
            $sql.= ", $tprocesos2 as t2 ";
        $sql.= " where t1.id_responsable = tusuarios.id ";
        if (!empty($this->id_entity)) {
            $sql.= "and ((t1.id_entity = $this->id_entity or t1.id = $this->id_entity) ";
            $sql.= "or (t1.id_entity is null and (t1.id_proceso = $this->id_entity ";
            if($t2)
                $sql.= "or (t1.id = t2.id_proceso and t2.id = $this->id_entity)"; 
            $sql.= "))) ";
        }
        if (!empty($this->id_proceso) && is_null($plus))
            $sql.= "and (t1.id = $this->id_proceso or t1.id_proceso = $this->id_proceso) ";
        if (!empty($this->tipo) && is_null($plus))
            $sql.= "and t1.tipo = $this->tipo ";
        if (!empty($this->id_responsable))
            $sql.= "and tusuarios.id = $this->id_responsable ";
        if (!empty($corte))
            $sql.= "and t1.tipo <= $corte ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        if ($asc != 'eq_asc' && $asc != 'eq_desc') {
            if ($this->conectado === _LOCAL)
                $sql.= "and t1.conectado = 1 ";
            if ($this->conectado === _NO_LOCAL)
                $sql.= "and t1.conectado <> 1 ";
        } else {
            if ($this->conectado === _LOCAL)
                $sql.= "and (t1.conectado = 1 or t1.id = {$_SESSION['id_entity']}) ";
            if ($this->conectado === _NO_LOCAL)
                $sql.= "and (t1.conectado <> 1 or t1.id = {$_SESSION['id_entity']}) ";
        }
        if (!is_null($plus))
            $sql.= "and $plus ";
        $sql.= "order by t1.tipo $order, _nombre ASC ";

        $result= $this->do_sql_show_error('listar', $sql);
        if ($flag)
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['_nombre'], 'tipo'=>$row['_tipo'],
                    'descripcion'=>$row['_descripcion'], 'id_proceso'=>$row['_id_proceso'], 'conectado'=>$row['_conectado'],
                    'id_responsable'=>$row['_id_responsable'], 'codigo'=>$row['codigo'], 'local_archive'=> boolean($row['local_archive']),
                    'if_entity'=> boolean($row['_if_entity']), 'id_entity'=>$row['_id_entity'], 'id_entity_code'=>$row['_id_entity_code'],
                    'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_procesos[$row['_id']]= $array;
        }

        return $this->array_procesos;
    }

    private function _listar_side($id_proceso, $flag= false) {
        $flag= !is_null($flag) ? $flag : false;
        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';
        $this->control_recursive[$id_proceso]= $id_proceso;

        if ($this->if_copy_tprocesos && $this->use_copy_tprocesos)
            $tprocesos2= $this->_create_clone_tmp_table($tprocesos);
        else 
            $tprocesos2= $tprocesos;

        $sql= "select t1.*, t1.id as _id, t1.inicio as _inicio, t1 .fin as _fin, ";
        $sql.= "t1.if_entity as _if_entity, t1.id_entity as _id_entity, t1.id_entity_code as _id_entity_code ";
        $sql.= "from $tprocesos as t1, $tprocesos2 as t2 ";
        $sql.= "where t1.id_proceso = t2.id and t2.id = $id_proceso and t1.tipo >= t2.tipo ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null) ";
        $result= $this->do_sql_show_error('listar', $sql);
        if ($flag)
            return $result;

        $array_procesos= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['id'], $this->control_recursive))
                continue;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'conectado'=>$row['conectado'], 'descripcion'=>$row['descripcion'],
                          'tipo'=>$row['tipo'], 'id_code'=>$row['id_code'], 'id_proceso'=>$row['id_proceso'],
                          'if_entity'=> boolean($row['_if_entity']), 'id_entity'=>$row['_id_entity'], 'id_entity_code'=>$row['_id_entity_code'],
                          'inicio'=>$row['_inicio'], 'fin'=>$row['_fin']);
            $array_procesos[$row['_id']]= $array;
        }

        $_array_procesos= array();
        $_array_procesos= array_merge_overwrite($_array_procesos, (array)$array_procesos);
        foreach ($array_procesos as $prs) {
            $this->control_recursive[$prs['id']]= $prs['id'];
        }
        foreach ($_array_procesos as $prs) {
            $_array_recursive= $this->_listar_side($prs['id'], false);
            if (is_array($_array_recursive) and count($_array_recursive) > 0)
                $array_procesos= array_merge_overwrite((array)$array_procesos, $_array_recursive);
        }

        reset($array_procesos);
        return $array_procesos;
    }

    public function listar_side() {
        reset($this->array_procesos);
        foreach ($this->array_procesos as $prs) {
            if (!empty($this->id_proceso) && $prs['id'] == $this->id_proceso)
                continue;
            $array_procesos= $this->_listar_side($prs['id'], false);
            if (count($array_procesos) > 0)
                $this->array_procesos= array_merge_overwrite((array)$this->array_procesos, $array_procesos);
        }
        $this->array_procesos= arrayUniqueId($this->array_procesos);
        return $this->array_procesos;
    }

    /**
     * devuelve los procesos segun jerarquia --asc se rfiere a los de proceso de rango superior o indices menores
     *  en el arreglo de tipo de procesos y el corte indica a partir de que nivel se tomara el resultado
     *
     * @param null $asc
     * @param bool $flag
     * @param int $corte
     * @param bool $equal
     * @param string $order
     * @return null|resource
     */
    public function listar_in_order($asc= null, $show_dpto= false, $corte= _TIPO_ARC, $equal= true, $order= 'asc') {
        $show_dpto= !is_null($show_dpto) ? $show_dpto : false;
        $corte= is_null($corte) ? _TIPO_ARC : $corte;
        $equal= is_null($equal) ? true : $equal;
        $plus= null;

        if (isset($this->control_recursive)) 
            unset($this->control_recursive);
        $this->control_recursive= null;

       if ($asc == 'asc_desc' || $asc == 'eq_asc_desc') {
            $this->listar_all($asc, false, $corte);
            if ($show_dpto && $order == 'asc')
                $this->listar_side(false);

            return $this->array_procesos;

       } else {
            $xplus= "";

            if ($asc != 'eq_eq') {
                if (!empty($this->tipo)) {
                    if ($asc == 'asc')
                        $xplus.= " t1.tipo < $this->tipo ";
                    if ($asc == 'eq_asc')
                        $xplus.= " t1.tipo <= $this->tipo ";
                    if ($asc == 'desc')
                        $xplus.= " t1.tipo > $this->tipo ";
                    if ($asc == 'eq_desc')
                        $xplus.= " t1.tipo >= $this->tipo ";
                }
                if (!empty($corte)) {
                    if (!empty($this->tipo) && (strstr($asc, 'asc') || strstr($asc, 'desc')))
                        $xplus.= " and ";

                    if ($asc == 'asc')
                        $xplus.= " t1.tipo < $corte ";
                    elseif ($asc == 'eq_asc')
                        $xplus.= " t1.tipo <= $corte ";
                    elseif ($asc == 'desc')
                        $xplus.= " t1.tipo < $corte ";
                    elseif ($asc == 'eq_desc')
                        $xplus.= " t1.tipo <= $corte ";
                }
            } else {
                 $xplus.= " t1.tipo = $corte ";
            }
            if (strlen($xplus) > 0)
                $plus= "($xplus) ";
            if (!empty($this->id_proceso)) {
                if ($asc == 'asc' || $asc == 'eq_asc') {
                    if (!is_null($plus))
                        $plus.= " and ";
                    $plus.=" (";
                    if ($asc == 'asc')
                        $plus.= "(t1.id = t2.id_proceso and t2.id = $this->id_proceso) ";
                    if ($asc == 'eq_asc') {
                        $plus.= "((t1.id = t2.id_proceso or (t1.id_proceso = t2.id and t1.tipo <= $corte)) and t2.id = $this->id_proceso) ";
                        $plus.= " or t1.id = $this->id_proceso ";
                    }
                    $plus.= ") ";
                }
                if ($asc == 'desc' || $asc == 'eq_desc') {
                    if (!is_null($plus))
                        $plus.= " and (";
                    $plus.=" (t1.id_proceso = $this->id_proceso ";
                    if ($corte && empty($this->tipo)) {
                        if ($equal)
                            $plus.= "and t1.tipo = $corte";
                        else{
                           if ($asc == 'desc')
                               $plus.= "and t1.tipo < $corte ";
                           if ($asc == 'eq_desc')
                               $plus.= "and t1.tipo <= $corte ";
                        }
                    }
                    $plus.= ") ";
                    if ($asc == 'eq_desc')
                        $plus.= " or t1.id = $this->id_proceso ";
                    $plus.= ") ";
                }
            }

            $this->listar(false, $plus, $order, $asc);
            if ($show_dpto)
                $this->listar_side(false);

            return $this->array_procesos;
       }
    }

    /** listar los proceso que son locales */
    public function listar_NO_LOCALs($id_proceso= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];

        $this->get_procesos_down($id_proceso, null, null, true);

        $array_NO_LOCALs= array();
        $i= 0;

        foreach ($this->array_cascade_down as $key => $row) {
            ++$i;
            if ($row['conectado'] != _NO_LOCAL && $row['id'] != $id_proceso)
                continue;
            $array= array('id'=>$row['id'], 'conectado'=>$row['conectado'], 'nombre'=>$row['nombre']);
            $array_NO_LOCALs[$row['id']]= $array;
        }
        return $i > 0 ? $array_NO_LOCALs : null;
    }

    /**
    * arreglo de procesos en los que esta involucrado el usuario
    */
    private function _get_proceso_by_user() {
        $sql= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.inicio as _inicio, tprocesos.fin as _fin ";
        $sql.= "from tprocesos, tusuario_procesos, tusuario_grupos ";
        $sql.= "where tusuario_procesos.id_usuario is null and tusuario_procesos.id_proceso = tprocesos.id ";
        $sql.= "and tusuario_procesos.id_grupo = tusuario_grupos.id_grupo and tusuario_grupos.id_usuario = $this->id_usuario ";
        if (!empty($this->id_entity))
            $sql.= "and (tprocesos.id_entity = $this->id_entity or tprocesos.id_entity is null) ";
        if (!empty($this->year))
            $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";

        $sql.= "UNION ";
        $sql.= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.inicio as _inicio, tprocesos.fin as _fin ";
        $sql.= "from tprocesos, tusuario_procesos where id_usuario= $this->id_usuario ";
        $sql.= "and tprocesos.id = tusuario_procesos.id_proceso ";
        if (!empty($this->id_entity))
            $sql.= "and (tprocesos.id_entity = $this->id_entity or tprocesos.id_entity is null) ";
        if (!empty($this->year))
            $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";

        $sql.= "UNION ";
        $sql.= "select distinct tprocesos.*, tprocesos.id as _id, tprocesos.inicio as _inicio, tprocesos.fin as _fin ";
        $sql.= "from tprocesos, tusuarios where tusuarios.id = $this->id_usuario ";
        $sql.= "and tprocesos.id = tusuarios.id_proceso ";
        if (!empty($this->id_entity))
            $sql.= "and (tprocesos.id_entity = $this->id_entity or tprocesos.id_entity is null) ";
        if (!empty($this->year))
            $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";
        $sql.= "order by tipo asc";

        $result= $this->do_sql_show_error('_get_proceso_by_user', $sql);
        return $result;
    }

    public function get_procesos_by_user($asc= 'asc_desc', $corte= _TIPO_ARC, $equal= true, $init_array= true, $exclude_prs_type= null, $id_usuario_array= null) {
        $asc= !is_null($asc) ? $asc : 'asc_desc';
        $corte= !is_null($corte) ? $corte : _TIPO_ARC;
        $equal= !is_null($equal) ? $equal : true;
        $init_array= !is_null($init_array) ? $init_array : true;

        if ($init_array)
            if (isset($this->array_procesos)) unset($this->array_procesos);

        if (is_array($id_usuario_array)) {
            $sql= "select tusuarios.id_proceso as _id, tprocesos.* from tusuarios, tprocesos ";
            $sql.= "where tusuarios.id in (".implode(',',$id_usuario_array).") and tusuarios.id_proceso = tprocesos.id ";
            if (!empty($this->year))
                $sql.= "and (tprocesos.inicio <= $this->year and tprocesos.fin >= $this->year) ";
            if (!empty($this->id_entity))
                $sql.= "and (tprocesos.id_entity = $this->id_entity or tprocesos.id = $this->id_entity) ";
            $result= $this->do_sql_show_error('get_procesos_by_user', $sql);
        }

        if (!is_array($id_usuario_array) && !empty($this->id_usuario)) {
            $result= $this->_get_proceso_by_user();
        }

        $array_procesos= array();

        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($exclude_prs_type) && array_key_exists((int)$row['tipo'], $exclude_prs_type))
                continue;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'conectado'=>$row['conectado'], 'descripcion'=>$row['descripcion'],
                          'tipo'=>$row['tipo'], 'id_code'=>$row['id_code'], 'id_proceso'=>$row['id_proceso'],
                            'inicio'=>$row['_inicio'], 'fin'=>$row['_fin'], 'local_archive'=>$row['local_archive']);

            if ($asc == 'asc_desc' || $asc= 'eq_asc_desc')
                $this->array_procesos[$row['_id']]= $array;
            else
                $array_procesos[$row['_id']]= $array;
        }

        if ($asc == 'asc_desc' || $asc == 'eq_asc_desc')
            return $this->array_procesos;

        $this->listar_in_order($asc, true, $corte, $equal);

        $this->cant= 0;
        $id_delete= null;
        foreach ($this->array_procesos as $array) {
            $id= $array['id'];

            if (!empty($id_delete)) {
                unset($this->array_procesos[$id_delete]);
            }
            if (!empty($exclude_prs_type) && (int)$array['tipo'] == (int)$exclude_prs_type) {
                $id_delete= $id;
                continue;
            }
            if (!array_key_exists($id, (array)$array_procesos)) {
                $id_delete= $id;
                continue;
            }

            $id_delete= null;
            ++$this->cant;
        }

        if (!empty($id_delete))
            unset($this->array_procesos[$id_delete]);

        reset($this->array_procesos);
        return $this->array_procesos;
    }

    public function get_criterio_eval($year= null, $take_value= true) {
        $year= !empty($year) ? $year : $this->year;

        $sql= "select * from tproceso_criterio where id_proceso = $this->id_proceso and year = $year order by year desc limit 1";
        $result= $this->do_sql_show_error('get_criterio_eval', $sql);
        $row= $this->clink->fetch_array($result);
        $cant= $this->cant;

        if (empty($cant) || !$result)
            return false;

        if ($take_value) {
            $this->_red= $row['_red'];
            $this->_orange= $row['_orange'];
            $this->_yellow= $row['_yellow'];
            $this->_green= $row['_green'];
            $this->_aqua= $row['_aqua'];
            $this->_blue= $row['_blue'];
        }
        return true;
    }

    private function update_criterio_eval($year= null) {
        $year= !empty($year) ? $year : $this->year;
        if (empty($year)) $year= date('Y');

        $sql= "update tproceso_criterio set _orange= $this->_orange, _yellow= $this->_yellow, _green= $this->_green, ";
        $sql.= "_aqua= $this->_aqua, _blue = $this->_blue, cronos= '$this->cronos' where id_proceso = $this->id_proceso ";
        $sql.= "and year = $year ";

        $this->do_sql_show_error('set_criterio_eval', $sql, false);
        if ($this->cant > 0)
            return;

        if ($this->cant == 0) {
            $sql= "insert into tproceso_criterio (id_proceso, id_proceso_code, year, _orange, _yellow, _green, _aqua, _blue, ";
            $sql.= "cronos, situs) values ($this->id_proceso, '$this->id_proceso_code', $year, $this->_orange, $this->_yellow, ";
            $sql.= "$this->_green, $this->_aqua, $this->_blue, '$this->cronos', '$this->location')";

            $this->do_sql_show_error('update_criterio_eval', $sql);
        }
    }

    public function set_criterio_eval() {
        $inicio= $this->year - 1;
        $fin= $this->year + 3;

        for ($year= $inicio; $year < $fin; ++$year) {
            if (($year < $this->year || $year > $this->year) && $this->get_criterio_eval($year, false))
                continue;
           $this->update_criterio_eval($year);
        }
    }

    /**
     * $in_building= 0: se consideran todos los procesos
     * $in_building= 1: solo los procsesos dentro del local al origen
     * $corte= null ; el primer tipo de procesos a partir de cual se detiene al alcanzarlo
     */
    public function get_proceso_top($id_proceso= null, $corte= null, $in_building= false, $init_search= true) {
        $init_search= is_null($init_search) ? true : $init_search;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $corte= !is_null($corte) ? $corte : null;
        if (empty($id_proceso))
            return null;

        if ($init_search) {
            if (isset($this->control_recursive)) unset ($this->control_recursive);
            $this->control_recursive= array();
        }

        $sql= "select t1.*, t1.id as _id, t1.id_code as _id_code, t1.nombre as _nombre, t1.tipo as _tipo ";
        $sql.= "from tprocesos as t1, tprocesos as t2 where (t1.id = t2.id_proceso and t2.id = $id_proceso) ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or (t1.id_entity is null and (t2.id_entity = $this->id_entity or t2.id_entity is null))) ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        $result= $this->do_sql_show_error('get_proceso_up', $sql);
        $row= $this->clink->fetch_array($result);

        if (array_key_exists($row['_id'], $this->control_recursive))
            return null;
        else
            $this->control_recursive[$row['_id']]= $row['_id'];

        if (!empty($corte) && $row['_tipo'] <= $corte)
            return $row['_id'];
        
        if ($row['conectado'] == _LAN && $row['_tipo'] <= $_SESSION['entity_tipo'])
            return $_SESSION['id_entity'];

        if ($row['conectado'] != _LAN && $row['_tipo'] <= $_SESSION['entity_tipo'])
            return $in_building ? $_SESSION['id_entity'] : $row['_id'];

        elseif (empty($row['id_proceso']) && ($row['conectado'] != _LAN && $row['_id'] != $id_proceso)) {
            if ($in_building)
                return $in_building ? $row['_id'] : $id_proceso;
        }
        elseif (empty($row['id_proceso']) || (!empty($row['id_proceso']) && (!empty($corte) && $row['_tipo'] <= $corte)))
            return $row['_id'];

        else
            if (!empty($row['id_proceso']))
                return $this->get_proceso_top($row['_id'], $corte, $in_building, false);
    }
    

    /**
     * true=> el proceso que esta en la intranet o esta conectado pero es directamente subordinado a local_id
     * false=> el proceso esta en otra intranet sin subordinacion directa a local-id
     */
    public function get_if_in_building($id_proceso= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_proceso_sup= $this->get_proceso_top($id_proceso, null, true);

        if ($id_proceso_sup == $_SESSION['local_proceso_id'])
            return true;

        if (!empty($id_proceso_sup)) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($id_proceso_sup);
            $conectado= $obj_prs->GetConectado();
        }

        if (!empty($id_proceso_sup) && ($conectado != _NO_LOCAL && $id_proceso_sup != $_SESSION['local_proceso_id']))
            return false;
        return true;
    }

    private function _get_proceso_up($id_proceso, $corte= null) {
        $array= null;

        $sql= "select t1.* from tprocesos as t1, tprocesos as t2 where (t1.id = t2.id_proceso and t2.id = $id_proceso) ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null) ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        $result= $this->do_sql_show_error('get_proceso_up', $sql);
        if (empty($this->cant))
            return;
        $row= $this->clink->fetch_array($result);

        if (is_null($corte) || (!empty($corte) && $row['tipo'] <= $corte)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                'id_responsable'=>$row['id_responsable'], 'conectado'=>$row['conectado'], 'id_proceso'=>$row['id_proceso'],
                'inicio'=>$row['inicio'], 'fin'=>$row['fin'], 'if_entity'=>$row['if_entity'], 'id_entity'=>$row['id_entity']);
            $this->array_cascade_up[$row['id']]= $array;

            $this->_get_proceso_up($row['id'], $row['tipo']);
        }
    }

    /**
     * @include_NO_LOCAL_prs: true: incluye al proceso local
     * listado de procesos que se encuentran con nivel o jerarquia superior al proceso id_proceso
     * en la cadena de direccion o mando
     */
    public function get_procesos_up_cascade($id_proceso= null, $corte= null, $empty= true, $include_NO_LOCAL_prs= false) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $empty= !is_null($empty) ? $empty : true;

        if ($empty) {
            if (isset($this->array_cascade_up)) unset($this->array_cascade_up);

            if (!$include_NO_LOCAL_prs)
                $this->array_cascade_up= array();
            else {
                $this->Set($id_proceso);
                $array= array('id'=>$this->id, 'id_code'=>$this->id_code, 'nombre'=>$this->nombre, 'tipo'=>$this->tipo,
                    'id_responsable'=>$this->id_responsable, 'conectado'=>$this->conectado, 'id_proceso'=> $this->id_proceso_sup,
                    'inicio'=> $this->inicio, 'fin'=> $this->fin, 'if_entity'=> $this->if_entity, 'id_entity'=>$this->id_entity);
                $this->array_cascade_up[$this->id]= $array;
            }
        }

        $this->_get_proceso_up($id_proceso, $corte);

        $array_type= array();
        $array_name= array();

        foreach ($this->array_cascade_up as $key =>$row) {
            $array_type[$key]= $row['tipo'];
            $array_name[$key]= $row['nombre'];
        }

        array_multisort($array_type, SORT_DESC, $array_name, SORT_ASC, $this->array_cascade_up);
        reset($this->array_cascade_up);
        return $this->array_cascade_up;
    }

    private function _get_procesos_down($id_proceso= null, $corte= null) {
        $array= null;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $tprocesos= $this->if_copy_tprocesos && $this->use_copy_tprocesos ? "_ctprocesos" : "tprocesos";

        $sql= "select distinct t1.* from $tprocesos as t1, $tprocesos as t2 ";
        $sql.= "where ((t1.id_proceso = t2.id and t2.id = $id_proceso) or t1.id_proceso = $id_proceso) ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null) ";
        if (!empty($this->year))
            $sql.= "and (t1.inicio <= $this->year and t1.fin >= $this->year) ";
        $result= $this->do_sql_show_error('_get_procesos_down', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if (is_null($corte) || (!empty($corte) && $row['tipo'] <= $corte)) {
                $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                                'lugar'=>$row['lugar'], 'descripcion'=>$row['descripcion'], 'id_responsable'=>$row['id_responsable'],
                                'conectado'=>$row['conectado'], 'codigo'=>$row['codigo'], 'id_proceso'=>$row['id_proceso'],
                                'if_entity'=> boolean($row['if_entity']), 'id_entity'=>$row['id_entity'],
                                'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
                $this->array_cascade_down[$row['id']]= $array;

                $this->_get_procesos_down($row['id'], $corte);
        }   }
    }

    /**
     * listado de procesos que se encuentran con nivel o jerarquia inferior al proceso id_proceso
     */
    public function get_procesos_down($id_proceso= null, $corte= null, $empty= true, $include_prs= false) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $empty= !is_null($empty) ? $empty : true;
        $include_prs= !is_null($include_prs) ? $include_prs : false;

        if ($empty) {
            if (isset($this->array_cascade_down)) unset($this->array_cascade_down);
            $this->array_cascade_down= array();
        }

        if ($include_prs) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($id_proceso);

            $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(), 
                    'tipo'=>$obj_prs->GetTipo(), 'lugar'=>$obj_prs->GetLugar(), 'descripcion'=>$obj_prs->GetDescripcion(), 
                    'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(), 
                    'codigo'=>$obj_prs->GetCodigo(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(),
                    'if_entity'=> boolean($obj_prs->GetIfEntity()), 'id_entity'=>$obj_prs->GetIdEntity(),
                    'inicio'=> $obj_prs->GetInicio(), 'fin'=> $obj_prs->GetFin());
            $this->array_cascade_down[$id_proceso]= $array;
        }

        $this->_get_procesos_down($id_proceso, $corte);

        $array_type= array();
        $array_name= array();

        foreach ($this->array_cascade_down as $key =>$row) {
            $array_type[$key]= $row['tipo'];
            $array_name[$key]= $row['nombre'];
        }

        array_multisort($array_type, SORT_ASC, $array_name, SORT_ASC, $this->array_cascade_down);
        reset($this->array_cascade_down);

        $array_cascade_down= array();
        foreach ($this->array_cascade_down as $array)
            $array_cascade_down[$array['id']]= $array;
        unset($this->array_cascade_down);

        $this->array_cascade_down= array();
        foreach ($array_cascade_down as $array) {
            $this->array_cascade_down[$array['id']]= $array;
            $this->control_recursive[$array['id']]= $array['id'];
        }

        return $this->array_cascade_down;
    }

    public function if_proceso_sub($id_proceso) {
        reset($this->array_cascade_down);
        foreach ($this->array_cascade_down as $key => $row) {
            if ($row['id'] == $id_proceso) 
                return true;
        }
        return false;
    }

    public function init_cascade($id_usuario= null, $id_proceso= null, $corte= null) {
        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_usuario;

        $obj_prs= null;
        if (isset($this->array_cascade_down)) 
            unset($this->array_cascade_down);
        $this->array_cascade_down= array();
        if (isset($this->array_cascade_up)) 
            unset($this->array_cascade_up);
        $this->array_cascade_up= array();
        if (isset($this->control_recursive)) 
            unset($this->control_recursive);
        $this->control_recursive= array();

        if ($this->acc == 3) {
            $id_proceso= $_SESSION['id_entity'];
            $id_usuario= null;
            $this->id_usuario= null;
        }
        if ((empty($id_usuario) && empty($id_proceso)) && $this->acc != 3)
            $id_usuario= $_SESSION['id_usuario'];

        if ((empty($id_usuario) && empty($id_proceso)) || $this->acc == 3)
            $id_proceso= $_SESSION['id_entity'];

        if (!empty($id_usuario) && (!empty($this->acc) && ($this->acc < 3))) {
            $this->id_usuario= $id_usuario;
            $asc= !empty($id_proceso) ? "eq_asc" : null;

            $this->getProceso_if_jefe($id_usuario, $id_proceso, $corte);
            $this->get_procesos_by_user($asc, $corte);
            $this->array_cascade_up= array_merge_overwrite((array)$this->array_cascade_up, $this->array_procesos);
        }
        if (!empty($id_usuario) && empty($this->acc)){
            $obj_user= new Tusuario($this->clink);
            $obj_user->Set($id_usuario);
            $_id_proceso= $obj_user->GetIdProceso();

            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($_id_proceso);
        }
        if (!empty($id_proceso) && (empty($id_usuario))) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->use_copy_tprocesos= $this->use_copy_tprocesos;
            $obj_prs->Set($id_proceso);
        }
        if ($obj_prs) {
            $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(), 
                    'tipo'=>$obj_prs->GetTipo(), 'lugar'=>$obj_prs->GetLugar(), 'descripcion'=>$obj_prs->GetDescripcion(), 
                    'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(), 
                    'codigo'=>$obj_prs->GetCodigo(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(),
                    'inicio'=> $obj_prs->GetInicio(), 'fin'=> $obj_prs->GetFin());

            $this->array_cascade_up[$id_proceso]= $array;
            $this->array_cascade_down[$id_proceso]= $array;
        }

        foreach ($this->array_cascade_down as $prs) {
            $this->control_recursive[$prs['id']]= $prs['id'];
        }
        reset($this->array_cascade_up);
        reset($this->array_cascade_down);
    }

    /**
     * La cascada de procesos subordinados al usuario, tomando como procesos superiores, o sea el top
     * de cada rama a aquel proceso del cual el usuario es jefe directo
     */
    public function get_procesos_down_cascade($id_usuario= null, $id_proceso= null, $corte= null, $array_procesos= null) {
        $this->init_cascade($id_usuario, $id_proceso, $corte);

        if (count($this->array_cascade_up)) {
            foreach ($this->array_cascade_up as $array)
                $this->get_procesos_down($array['id'], $corte, false, true);
        } else {
            if ($id_proceso == -1)
                $this->get_procesos_down(null, $corte, false, true);
        }

        if ((empty($id_usuario) && empty($id_proceso)) && $array_procesos) {
            reset($array_procesos);
            foreach ($array_procesos as $array) {
                $this->get_procesos_down($array['id'], $corte, false, true);
            }
        }

        $this->listar_side_down();

        if ($this->acc == 3 && $id_proceso != $_SESSION['id_entity']) {
            $array= array('id'=>$_SESSION['id_entity'], 'id_code'=>$_SESSION['id_entity_code'], 'nombre'=>$_SESSION['entity_nombre'],
                    'tipo'=>$_SESSION['entity_tipo'], 'lugar'=>$_SESSION['entity_lugar'], 'descripcion'=>null,
                    'id_responsable'=>$_SESSION['entity_id_responsable'], 'conectado'=>$_SESSION['local_proceso_conectado'],
                    'codigo'=>$_SESSION['local_proceso_codigo'], 'id_proceso'=>$_SESSION['superior_proceso_id'],
                    'inicio'=> $_SESSION['inicio'], 'fin'=>$_SESSION['fin']);

            $this->array_cascade_down[$_SESSION['id_entity']]= $array;
            $this->control_recursive[$_SESSION['id_entity']]= $_SESSION['id_entity'];
        }

        $this->cant= count($this->array_cascade_down);
        return $this->array_cascade_down;
    }

    private function listar_side_down() {
        reset($this->array_cascade_down);
        foreach ($this->array_cascade_down as $prs) {
            $array_procesos= $this->_listar_side($prs['id']);
            if (count($array_procesos) > 0)
                $this->array_cascade_down= array_merge_overwrite((array)$this->array_cascade_down, $array_procesos);
        }
        return $this->array_cascade_down;
    }

    protected function test_if_synchronize($array= null) {
        if (count($array) == 0)
            return false;
        $chain= implode(',', $array);
        $valid= false;

        $sql= "select manner from _config_synchro where id_proceso in ($chain)";
        $result= $this->do_sql_show_error('get_sinchronization_setup', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if (!$valid) {
                $valid= !is_null($row['manner']) &&  $row['manner'] != _SYNCHRO_NEVER ? true : false;
            }
        }
        return $valid;
    }

    public function get_synchronization_setup() {
        $this->get_procesos_up_cascade($_SESSION['local_proceso_id']);
        $array= array();
        $i_up= 0;
        $if_send_up= false;
        foreach ($this->array_cascade_up as $_array) {
            ++$i_up;
            $array[]= $_array['id'];
        }
        if ($i_up) {
            $if_send_up= $this->test_if_synchronize($array) ? true : false;
        }

        $this->get_procesos_down($_SESSION['local_proceso_id']);
        $array= array();
        $i_down= 0;
        $if_send_down= false;
        foreach ($this->array_cascade_down as $_array) {
            ++$i_down;
            $array[]= $_array['id'];
        }
        if ($i_down) {
            $if_send_down= $this->test_if_synchronize($array) ? true : false;
        }

        return array('up'=>$if_send_up, 'down'=>$if_send_down);
    }

    public function get_entity_migrate_setup() {
        $if_send_down= false;
        $sql= "select * from tprocesos where id_proceso = {$_SESSION['id_entity']} and if_entity = true";
        $result= $this->do_sql_show_error('get_entity_migrate_setup', $sql);
        $if_send_down= $this->cant > 0 ? true : false;

        $if_send_up= false;
        $sql= "select t1.* from tprocesos as t1, tprocesos as t2 where t1.id = t2.id_proceso ";
        $sql.= "and t2.id = {$_SESSION['id_entity']}";
        $result= $this->do_sql_show_error('get_entity_migrate_setup', $sql);
        $if_send_up= $this->cant > 0 ? true : false;

        return array('up'=>$if_send_up, 'down'=>$if_send_down);
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Tusuario'))
    include_once "usuario.class.php";