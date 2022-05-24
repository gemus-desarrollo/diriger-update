<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 8/6/15
 * Time: 7:53 a.m.
 */

if (!class_exists('Tproyecto'))
    include_once "proyecto.class.php";

class Tkanban extends Tproyecto {
    /*
    protected $id_kanban;
    protected $id_kanban_code;
    */

    private $active;
    private $class;
    private $fixed;

    public $array_kanban_columns;
    public $array_kanban_tareas;

    protected $numero_column;
    protected $numero_task;

    public function SetColumnClass($id) {
        $this->class= $id;
    }
    public function GetColumnClass() {
        return $this->class;
    }
    
    public function set_if_fixed($id= true) {
        $this->fixed= $id;
    }
    public function get_if_fixed() {
        return $this->fixed;
    }
    public function set_if_active($id= true) {
        $this->active= $id;
    }
    public function get_if_active() {
        return $this->active;
    }
    public function GetNumeroColumn() {
        return $this->numero_column;
    }
    public function SetNumeroColumn($id) {
        $this->numero_column= $id;
    }
    public function GetNumeroTask() {
        return $this->numero_task;
    }
    public function SetNumeroTask($id) {
        $this->numero_task= $id;
    }

    public function __construct($clink= null) {
        Tproyecto::__construct($clink);
        $this->clink= $clink;

        $this->array_kanban_columns= array();
    }    

    public function Set($id = null) {
        if (!empty($id)) 
            $this->id_kanban_column = $id;

        $sql = "select * from tkanban_columns where id = $this->id_kanban_column";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_code = $row['id_code'];
            $this->id_kanban_column_code = $this->id_code;
            $this->numero_column = $row['numero'];

            $this->id_proyecto = $row['id_proyecto'];
            $this->id_proyecto_code = $row['id_proyecto_code'];
            $this->id_responsable = $row['id_responsable'];

            $this->nombre = stripslashes($row['nombre']);
            $this->class = $row['class'];
            $this->descripcion = stripslashes($row['descripcion']);

            $this->fixed= $row['fixed'];
            $this->active= boolean($row['active']);

            $this->kronos= $row['cronos'];
        }

        return $this->error;
    }

    public function Set_task() {
        $sql= "select * from tkanban_column_tareas where id_kanban_column = $this->id_kanban_column ";
        $sql.= "and id_tarea = $this->id_tarea and active = true oder by cronos desc limit 1";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->numero_task= $row['numero'];
            $this->cronos= $row['cronos'];
            $this->observacion= stripslashes($row['observacion']);
        }            
    }

    public function get_max_numero_column() {
        $sql= "select max(numero) from tkanban_columns where active > 0 ";
        if (!empty($this->id_proyecto) && empty($this->id_responsable)) 
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if(empty($this->id_proyecto) && !empty($this->id_responsable))
            $sql.= "and id_responsable = $this->id_responsable ";

        $result= $this->do_sql_show_error('get_max_numero_column', $sql);
        $row= $this->clink->fetch_array($result);
        return empty($row[0]) ? 1 : $row[0]+1; 
    }

    private function _set_new($array) {
        $array['numero']= 1;
        $array['fixed']= 1;
        $array['nombre'] = 'POR HACER';
        $array['descripcion']= 'Tareas no iniciadas';
        $array['class']= 'bg-info,good'; 
        $array['active']= 1;
        $this->_add($array);

        $array['numero']= 2;
        $array['fixed']= 2;
        $array['nombre'] = 'EN PROCESO';
        $array['descripcion']= 'Tareas iniciadas';;
        $array['class']= 'bg-warning'; 
        $array['active']= 2;
        $this->_add($array);

        $array['numero']= 3;
        $array['fixed']= 3;
        $array['nombre'] = 'TERMINADAS';
        $array['descripcion']= 'Tareas iniciadas';;
        $array['class']= 'bg-success'; 
        $array['active']= 3;
        $this->_add($array);
    }

    private function _add($array) {
        $numero= $array['numero'];
        $nombre = setNULL_str($array['nombre']);
        $descripcion= setNULL_str($array['descripcion']);
        $id_proyecto= setNULL($array['id_proyecto']);
        $id_proyecto_code= setNULL_str($array['id_proyecto_code']);

        $id_responsable= setNULL($array['id_responsable']);

        $class= setNULL_str($array['class']); 
        $fixed= setNULL($array['fixed']);  
        $active= $array['active'];

        $sql= "insert into tkanban_columns (fixed, nombre, numero, class, descripcion, id_proyecto, id_proyecto_code, ";
        $sql.= "id_responsable, active, cronos, situs) values ($fixed, $nombre, $numero, $class, $descripcion, ";
        $sql.= "$id_proyecto, $id_proyecto_code, $id_responsable, $active, '$this->cronos', '$this->location')";
        $result = $this->do_sql_show_error('_add', $sql); 
        
        if ($result) {
            $id = $this->clink->inserted_id("tkanban_columns");
            $this->obj_code->SetId($id);
            $this->obj_code->set_code('tkanban_columns', 'id', 'id_code');
            $id_code = $this->obj_code->get_id_code();

            return array('id'=>$id, 'id_code'=>$id_code);
        } else 
            return false;
    }

    public function set_new_proyecto() {
        $array= array();
        $array['id_proyecto']= $this->id_proyecto;
        $array['id_proyecto_code']= $this->id_proyecto_code;
        $array['id_responsable']= null;

        $this->_set_new($array);
    }

    public function set_new_responsable() {
        $array= array();
        $array['id_proyecto']= null;
        $array['id_proyecto_code']= null;
        $array['id_responsable']= $this->id_responsable;

        $this->_set_new($array);
    }

    public function add($fixed= null) {
        $array= array();

        $array['numero']= $this->get_max_numero_column();

        $array['fixed']= $fixed ? "true" : "null";
        $array['nombre'] = $this->nombre;
        $array['descripcion']= $this->descripcion;
        $array['id_proyecto']= $this->id_proyecto;
        $array['id_proyecto_code']= $this->id_proyecto_code;
        $array['id_responsable']= $this->id_responsable;
        $array['class']= $this->class; 
        $array['fixed']= $fixed;
        $array['active']= 4;

        $result= $this->_add($array);

        if ($result) {
            $this->id_kanban_column = $result['id'];
            $this->id = $this->id_kanban_column;
            $this->id_code = $result['id_code'];
            $this->id_kanban_column_code = $this->id_code;
        }
        return $this->error;        
    }

    public function update($only_number= false) {
        $only_number= !is_null($only_number) ? $only_number : false;

        $nombre = setNULL_str($this->nombre);
        $descripcion= setNULL_str($this->descripcion);
        $class= setNULL($this->class); 

        $sql= "update tkanban_columns set numero= $this->numero, ";
        if (!$only_number)
            $sql.= "nombre = $nombre, descripcion= $descripcion, class= $class, cronos= '$this->cronos' ";
        $sql.= "where id = $this->id_kanban ";
        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function delete() {
        $sql= "delete from tkanban_columns where id = $this->id_kanban_column";
        $this->do_sql_show_error('delete', $sql);
        return $this->error;        
    }

    public function listar($flag = true, $fixed= null) {
        $flag= !empty($flag) ? true : false;

        $sql= "select *, id as _id, id_code as _id_code, nombre as _nombre, numero as _numero ";
        $sql.= "from tkanban_columns where active > 0 ";
        if (!empty($this->id_proyecto) && empty($this->id_responsable)) 
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if(empty($this->id_proyecto) && !empty($this->id_responsable))
            $sql.= "and id_responsable = $this->id_responsable ";
        if (!empty($fixed))
            $sql.= "and fixed = true ";
        $sql.= "order by numero asc ";
        $result = $this->do_sql_show_error('listar', $sql);

        if (!$result) 
            return null;
        if ($flag) 
            return $result;
        
        $this->array_kanban_columns= array();
        
        $i= 0;
        $first= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['_id'], (array)$this->array_kanban_columns)) 
                continue;
            ++$i;
            $array= array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'numero' => $row['_numero'], 
                        'nombre' => $row['_nombre'], 'class'=>$row['class'], 'id_proyecto'=>$row['id_proyecto']);
            $this->array_kanban_columns[$row['_id']] = $array;
            if (is_null($first))
                $first= $array;
        }

        return $first;
    }

    /**************************************************************************************************************** 
     * comienza la asignacion de las tareas a las columnas
    *****************************************************************************************************************/
    public function get_max_numero_tarea($id_kanban_column= null) {
        $id_kanban_column= !empty($id_kanban_column) ? $id_kanban_column : $this->id_kanban_column;
        $sql= "select max(numero) from tkanban_column_tareas where id_kanban_column = $id_kanban_column ";
        $result= $this->do_sql_show_error('get_max_numero_tarea', $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? 1 : $row[0]+1; 
    }

    public function add_tarea($numero= null, $id_kanban_column= null) {
        if (!empty($id_kanban_column)) {
            $id_kanban_column_code= get_code_from_table("tkanban_columns", $id_kanban_column);
        } else {
            $id_kanban_column= $this->id_kanban_column;
            $id_kanban_column_code= $this->id_kanban_column_code;
        }

        if (empty($numero)) 
            $numero= $this->get_max_numero_tarea($id_kanban_column);
        
        $sql= "insert into tkanban_column_tareas (id_kanban_column, id_kanban_column_code, id_tarea, id_tarea_code, numero, ";
        $sql.= "active, id_usuario, cronos, situs) values ($id_kanban_column, '$id_kanban_column_code', $this->id_tarea, ";
        $sql.= "'$this->id_tarea_code', $numero, true, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location')";
        $this->do_sql_show_error('add_tarea', $sql);
    }

    public function update_tarea($id_kanban_column_origen, $id_kanban_column_target, $numero= null) {
        if ($id_kanban_column_origen != $id_kanban_column_target) {
            $sql= "update tkanban_column_tareas set active= false where id_kanban_column = $id_kanban_column_origen ";
            $sql.= "and id_tarea = $this->id_tarea";
            $result= $this->do_sql_show_error('update_tarea', $sql);

            if ($result)
                $this->add_tarea($numero, $id_kanban_column_target);
        } 
        
        if ($id_kanban_column_origen == $id_kanban_column_target && !empty($numero)) {
            $sql= "select * from tkanban_column_tareas where id_kanban_column = $id_kanban_column_origen ";
            $sql.= "and id_tarea = $this->id_tarea order by cronos desc limit 1";
            $result= $this->do_sql_show_error('update_tarea', $sql);
            $row= $this->clink->fetch_array($result);
            
            $sql= "update tkanban_column_tareas set numero= $numero where id = {$row['id']}";
            $this->do_sql_show_error('update_tarea', $sql);
        }
    }

    public function listar_tareas_from_column($flag= true, $id_kanban_column= null, $active= true) {
        $flag= !empty($flag) ? true : false;
        $id_kanban_column= !empty($id_kanban_column) ? $id_kanban_column : $this->id_kanban_column;
        $active= !empty($active) ? "true" : "false";

        $sql= "select ttareas.*, ttareas.id as _id, tkanban_column_tareas.numero as _numero, ttareas.nombre as _nombre ";
        $sql.= "from ttareas, tkanban_column_tareas where tkanban_column_tareas.active= $active ";
        $sql.= "and ttareas.id = tkanban_column_tareas.id_tarea and tkanban_column_tareas.id_kanban_column = $id_kanban_column ";
        $sql.= "order by tkanban_column_tareas.numero asc, cronos desc ";
        $result = $this->do_sql_show_error('listar_tareas_from_column', $sql);

        if (!$result) 
            return null;
        if ($flag) 
            return $result;
        
        $this->array_kanban_tareas= array();
        
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['_id'], (array)$this->array_kanban_tareas)) 
                continue;
            ++$i;
            $array= array('id' => $row['_id'], 'numero' => $row['_numero'], 'nombre' => $row['_nombre'], 'class'=>$row['class'], 
                        'id_proyecto'=>$row['id_proyecto']);
            $this->array_kanban_tareas[$row['_id']] = $array;
        }
        
        return $i;
    }

    public function list_reg() {
        $sql= "select distinct nombre, class, tkanban_column_tareas.* from tkanbon_column_tareas, ";
        $sql.= "tkanban_columns where tkanban_columns_tareas.id_kanban_column = tkanban_columns.id ";
        $sql.= "and tkanban_columns_tareas.id_tarea = $this->id_tarea ";
        if (!empty($this->id_responsable)) 
            $sql.= "and tkanban_columns.id_responsable = $this->id_responsable ";
        else 
            $sql.= "and tkanban_columns.id_proyecto = $this->id_proyecto ";
        $sql= "order by tkanban_column_tareas order desc";

        $result = $this->do_sql_show_error('listar_tareas_from_column', $sql);
        return $result;
    }
}