<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2018
 */


if (!class_exists('Tbase_lista'))
    include_once "base_lista.class.php";


class Tlista extends Tbase_lista {
    public function __construct($clink= null) {
         Tbase_lista::__construct($clink);
         $this->clink= $clink;

         $this->className= "Tlista";
     }

    public function Set($id= null) {
        $this->id_lista= !empty($id) ? $id : $this->id_lista;

        $sql= "select * from tlistas where id = $this->id_lista ";
        $result= $this->do_sql_show_error('Set', $sql);

        if (!$result) 
            return $this->error;

        $row= $this->clink->fetch_array($result);
        $this->id= $row['id'];
        $this->id_lista= $this->id;
        $this->id_code= $row['id_code'];
        $this->id_lista_code= $this->id_code;

        $this->nombre= stripslashes($row['nombre']);
        $this->descripcion= stripslashes($row['descripcion']);

        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];
        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);

        $sql = "insert into tlistas (nombre, descripcion, id_proceso, id_proceso_code, inicio, fin, cronos, ";
        $sql.= "situs) values ('$this->nombre', $descripcion, $this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "$this->inicio, $this->fin, '$this->cronos', '$this->location')";

        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tlistas");
            $this->id_lista = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tlistas', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_lista_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);

        $sql = "update tlistas set descripcion= $descripcion, nombre= '$this->nombre', inicio= $this->inicio, ";
        $sql .= "fin= $this->fin, situs= '$this->location', cronos= '$this->cronos' where id = $this->id_lista ";
        $result= $this->do_sql_show_error('update', $sql);
    }

    public function listar($include_sub_prs= false, $flag= true) {
        $flag= !is_null($flag) ? $flag : true;
        $include_sub_prs= !is_null($include_sub_prs) ? $include_sub_prs : false;

        $sql = "select distinct tlistas.*, tlistas.id as _id, id_code as _id_code, descripcion as _descripcion, ";
        $sql .= "tproceso_listas.id_proceso as _id_proceso, tproceso_listas.id_proceso_code as _id_proceso_code ";
        $sql .= "from tlistas, tproceso_listas where tlistas.id = tproceso_listas.id_lista ";
        if (!empty($this->id_proceso)) {
            if (!$include_sub_prs) {
                $sql .= "and tproceso_listas.id_proceso = $this->id_proceso ";
            } else {
                $sql.= "and tproceso_listas.id_proceso in (select id from tprocesos ";
                $sql.= "where tprocesos.id = $this->id_proceso or tprocesos.id_proceso = $this->id_proceso) ";
        }   }    
        if (!empty($this->year)) 
            $sql .= "and (tlistas.inicio <= $this->year and tlistas.fin >= $this->year) ";
        $sql .= "order by tlistas.cronos asc, tproceso_listas.cronos asc";

        $result = $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        if (isset($this->array_listas)) 
            unset($this->array_listas);

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($array_ids[$row['_id']]))
                continue;
            $array_ids[$row['_id']]= $row['_id'];

            ++$i;
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['nombre'],
                'descripcion'=>$row['_descripcion'], 'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_listas[$row['id']]= $array;
        }
        return $this->array_listas;        
    }

    public function eliminar() {
        $sql= "delete from tlistas where id = $this->id_lista";
        $this->do_sql_show_error('eliminar', $sql);
    }

    /* auditorias */
    public function get_lista_by_auditoria($id_auditoria= null, $flag= false) {
        $id_auditoria= !empty($id_auditoria) ? $id_auditoria : $this->id_auditoria;

        $sql= "select distinct tlistas.* from tlistas, tlista_auditorias where tlistas.id = tlista_auditorias.id_lista ";
        if ($id_auditoria) 
            $sql.= "and tlista_auditorias.id_auditoria = $id_auditoria ";

        $result = $this->do_sql_show_error('get_lista_by_auditoria', $sql);
        if ($flag) 
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'],
                'descripcion'=>$row['descripcion'], 'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_listas[$row['id']]= $array;
        }
        return $this->array_listas;
    }

    public function setAuditoria($action= 'add') {
         $sql= null;

         if ($action == 'add') {
             $sql= "select id from tlista_auditorias where id_lista= $this->id_lista ";
             $sql.= "and id_auditoria = $this->id_auditoria ";

             $result= $this->do_sql_show_error('addAuditoria', $sql);
             $cant= $this->cant;

             $action= empty($cant) ? 'add' : 'update';
         }

         if ($action == 'add') {
             $sql= "insert into tlista_auditorias (id_lista, id_lista_code, id_auditoria, id_auditoria_code,  cronos, situs) ";
             $sql.= "values ($this->id_lista, '$this->id_lista_code', $this->id_auditoria, '$this->id_auditoria_code', ";
             $sql.= "'$this->cronos', '$this->location') ";
         }

        if ($action == 'delete') {
             $sql= "delete from tlista_auditorias where id_lista= $this->id_lista ";
             if (!empty($this->id_auditoria)) $sql.= "and id_auditoria = $this->id_auditoria ";
         }

         $this->_set_user($sql);

         return $this->error;
    }

    public function extend_year() {        
        $sql= "update ttipo_listas set inicio= $this->inicio, fin= $this->fin ";
        $sql.= "where id_lista = $this->id_lista; ";
        $sql.= "update tlista_requisitos set inicio= $this->inicio, fin= $this->fin ";
        $sql.= "where id_lista = $this->id_lista; "; 

        $result= $this->do_multi_sql_show_error('extend_year', $sql);       
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Tlista_requisito'))
    include_once "lista_requisito.class.php";