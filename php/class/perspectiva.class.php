<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tobjetivo'))
    include_once "objetivo.class.php";

class Tperspectiva extends Tobjetivo {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tobjetivo::__construct($this->clink);

        $this->className = "Tperspectiva";
    }

    public function GetNumero() {
        if (!empty($this->numero)) 
            return $this->numero;
        else 
            return $this->find_numero('tperspectivas');
    }

    public function Set($id = null) {
        if (!empty($id)) 
            $this->id_perspectiva = $id;

        $sql = "select * from tperspectivas where id = $this->id_perspectiva";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_code = $row['id_code'];
            $this->id_perspectiva_code = $this->id_code;
            $this->numero = $row['numero'];

            $this->id_proceso = $row['id_proceso'];
            $this->id_proceso_code = $row['id_proceso_code'];

            $this->nombre = stripslashes($row['nombre']);
            $this->color = $row['color'];
            $this->descripcion = stripslashes($row['descripcion']);

            $this->peso = $row['peso'];
            $this->inicio = $row['inicio'];
            $this->fin = $row['fin'];
        }

        return $this->error;
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into tperspectivas (numero, nombre, descripcion, color, peso, inicio, fin, ";
        $sql .= "id_proceso, id_proceso_code, cronos, situs) values ($this->numero, $nombre, $descripcion, ";
        $sql .= "'$this->color', $this->peso, $this->inicio, $this->fin, ";
        $sql .= "$this->id_proceso, '$this->id_proceso_code', '$this->cronos', '$this->location')";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_perspectiva = $this->clink->inserted_id("tperspectivas");
            $this->id = $this->id_perspectiva;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tperspectivas', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_perspectiva_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "update tperspectivas set numero= $this->numero, nombre= $nombre, descripcion= $descripcion, ";
        $sql .= "color= '$this->color', id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code', ";
        $sql .= "peso= $this->peso, inicio= $this->inicio, fin= $this->fin, cronos= '$this->cronos', ";
        $sql .= "situs= '$this->location' where id = $this->id_perspectiva";

        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar($corte_prs = null, $flag= true) {
        $flag= !is_null($flag) ? $flag : true;
            
        $sql = "select distinct tperspectivas.*, tperspectivas.peso as _peso, tperspectivas.id as _id, ";
        $sql .= "tperspectivas.id_code as _id_code, tperspectivas.id_proceso as _id_proceso, ";
        $sql .= "tperspectivas.id_proceso_code as _id_proceso_code, tperspectivas.nombre as _nombre, ";
        $sql.= "tperspectivas.descripcion as _descripcion, tperspectivas.numero as _numero, ";
        $sql .= "tprocesos.nombre as proceso, tprocesos.tipo as tipo from tperspectivas, tprocesos ";
        $sql .= "where tperspectivas.id_proceso = tprocesos.id ";
        if (!empty($corte_prs)) 
            $sql .= "and tprocesos.tipo <= $corte_prs ";
        if (!empty($this->id_proceso)) 
            $sql .= "and tperspectivas.id_proceso = $this->id_proceso ";
        if (!empty($this->year)) 
            $sql .= "and (tperspectivas.inicio <= $this->year and tperspectivas.fin >= $this->year) ";
        if (!empty($this->inicio)) 
            $sql .= "and (tperspectivas.inicio <= $this->fin and tperspectivas.fin >= $this->inicio) ";
        $sql .= "order by tipo asc, tperspectivas.inicio asc, numero asc ";

        $result = $this->do_sql_show_error('listar', $sql);

        if (!$result) 
            return null;
        if ($flag) 
            return $result;
        
        if (isset($this->array_perspectivas)) unset($this->array_perspectivas);
        $this->array_perspectivas= array();
        
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['_id'], (array)$this->array_perspectivas)) 
                continue;
            ++$i;
            $array= array('id' => $row['_id'], 'numero' => $row['_numero'], 'nombre' => $row['_nombre'], 
                        'color'=>$row['color'], 'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_perspectivas[$row['_id']] = $array;
        }
        
        return $i;
    }

    public function eliminar($radio_date = null) {
        $error = null;

        $obj = new Treference($this->clink);
        $obj->SetIdProceso($this->id_proceso);
        $obj->empty_set_null_perspectiva($this->id_perspectiva, null, $this->year);

        if ($radio_date == 2 || $this->year == $this->inicio) {
            $sql = "delete from tperspectivas where id = $this->id_perspectiva ";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Esta perspectiva contiene indicadores. Para borrarla no debe tener indicadores asignados. ";
                $error .= "Vacie la perspectiva, desde la lista de perspectiva o inductores y despues intente borrar nuevamente.";
            }
        } else {
            $year = $this->year - 1;
            $sql = "update tperspectivas set fin= $year where id = $this->id_perspectiva ";
            $this->do_sql_show_error('eliminar', $sql);
        }

        return $error;
    }

    public function listar_indicadores() {
        $sql= "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code, trend, peso, ";
        $sql.= "tindicadores.id_proceso as _id_proceso, peso as _peso from tindicadores, tindicador_criterio ";
        $sql.= "where year = $this->year and tindicadores.id = tindicador_criterio.id_indicador ";
        if (!empty($this->id_perspectiva)) 
            $sql .= "and id_perspectiva = $this->id_perspectiva ";
        if (!empty($this->id_proceso)) 
            $sql .= "and tindicador_criterio.id_proceso = $this->id_proceso ";

        $result = $this->do_sql_show_error('listar_indicadores', $sql);
        return $result;
    }
}
