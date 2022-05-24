<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */


include_once "base_tipo.class.php";

class Ttipo_evento extends Tbase_tipo {
    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_tipo::__construct($clink);

        $this->className= "Ttipo_evento";
        $this->table= "ttipo_eventos";
    }

    public function Set($id= null) {
        if (!empty($id))
            $this->id_tipo_evento= $id;

        $sql= "select * from ttipo_eventos where id = $this->id_tipo_evento";
        $result= $this->do_sql_show_error('Set', $sql);

        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_code= $row['id_code'];
        $this->id_tipo_evento= $this->id;
        $this->id_tipo_evento_code= $this->id_code;

        $this->nombre= stripslashes($row['nombre']);
        $this->descripcion= stripslashes($row['descripcion']);
        $this->empresarial= $row['empresarial'];

        $this->numero= $row['numero'];
        $this->id_subcapitulo= $row['id_subcapitulo'];
        $this->id_subcapitulo_code= $row['id_subcapitulo_code'];

        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];

        $this->subcapitulo0= $row['subcapitulo0'];
        $this->subcapitulo1= $row['subcapitulo1'];

        $this->indice= $row['indice'];
        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];
    }

    public function add() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $numero= setNULL_str($this->numero);

        $id_subcapitulo= setNULL($this->id_subcapitulo);
        $id_subcapitulo_code= setNULL_str($this->id_subcapitulo_code);

        $subcapitulo0= setNULL($this->subcapitulo0);
        $subcapitulo1= setNULL($this->subcapitulo1);
        $inicio= setNULL($this->inicio);
        $fin= setNULL($this->fin);
        $index= ($this->empresarial*pow(10,6)) + ($this->subcapitulo0 ? $this->subcapitulo0*pow(10,3) : 0) + $this->subcapitulo1*10;

        $sql= "insert into ttipo_eventos (nombre, numero, descripcion, empresarial, subcapitulo0, id_subcapitulo, subcapitulo1, ";
        $sql.= "indice, inicio, fin, cronos, situs, id_subcapitulo_code, id_proceso, id_proceso_code) values ($nombre, $numero, ";
        $sql.= "$descripcion, $this->empresarial, $subcapitulo0, $id_subcapitulo, $subcapitulo1, $index, $inicio, $fin, ";
        $sql.= "'$this->cronos', '$this->location', $id_subcapitulo_code, $this->id_proceso, '$this->id_proceso_code')";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id= $this->clink->inserted_id("ttipo_eventos");
            $this->id_tipo_evento= $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('ttipo_eventos','id','id_code');

            $this->id_code= $this->obj_code->get_id_code();
            $this->id_tipo_evento_code= $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $numero= setNULL_str($this->numero);

        $id_subcapitulo= setNULL($this->id_subcapitulo);
        $id_subcapitulo_code= setNULL_str($this->id_subcapitulo_code);

        $subcapitulo0= setNULL($this->subcapitulo0);
        $subcapitulo1= setNULL($this->subcapitulo1);
        $inicio= setNULL($this->inicio);
        $fin= setNULL($this->fin);
        $index= ($this->empresarial*pow(10,6)) + ($this->subcapitulo0 ? $this->subcapitulo0*pow(10,3) : 0) + $this->subcapitulo1*10;

        $sql= "update ttipo_eventos set nombre= $nombre, numero= $numero, descripcion= $descripcion, empresarial= $this->empresarial, ";
        $sql.= "subcapitulo0= $subcapitulo0, id_subcapitulo= $id_subcapitulo, subcapitulo1= $subcapitulo1, inicio= $inicio, ";
        $sql.= "fin= $fin, indice= $index, cronos= '$this->cronos', situs= '$this->location', id_subcapitulo_code= $id_subcapitulo_code ";
        $sql.= "where id = $this->id_tipo_evento ";
        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar($empresarial= null, $id_subcapitulo= null) {
        $empresarial= !empty($empresarial) ? $empresarial : $this->empresarial;
        $_id_subcapitulo= setNULL_equal_sql($id_subcapitulo);

        $sql= "select * from ttipo_eventos where 1 ";
        if (!empty($empresarial))
            $sql.= "and empresarial = $empresarial ";
        if (!empty($this->id_proceso) && empty($this->id_proceso_asigna))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (empty($this->id_proceso) && !empty($this->id_proceso_asigna))
            $sql.= "and id_proceso = $this->id_proceso_asigna ";
        if (!empty($this->id_proceso) && !empty($this->id_proceso_asigna))
            $sql.= "and (id_proceso = $this->id_proceso or id_proceso = $this->id_proceso_asigna) ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        if (!empty($id_subcapitulo))
            $sql.= "and id_subcapitulo $_id_subcapitulo ";
        if (!is_null($id_subcapitulo) && empty($id_subcapitulo))
            $sql.= "and (id_subcapitulo is null or id_subcapitulo = 0) ";
        $sql.= "order by empresarial asc, subcapitulo0 asc, subcapitulo1 asc";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function listar_all() {
        unset($this->array_tipo_eventos);
        $this->array_tipo_eventos= array();

        $sql= "select * from ttipo_eventos";
        $result= $this->do_sql_show_error('listar_all', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'empresarial'=>$row['empresarial'],
                        'inicio'=>$row['inicio'], 'fin'=>$row['fin'], 'indice'=>$row['indice']);
            $this->array_tipo_eventos[$row['id']]= $array;
        }
        return $this->array_tipo_eventos;
    }

    public function if_valid_tipo_evento($id_tipo, $year= null) {
        $year= !empty($year) ? $year : $this->year;
        if (empty($id_tipo))
            return true;
        if (is_null($this->array_tipo_eventos) || !isset($this->array_tipo_eventos))
            $this->listar_all();

        if (!array_key_exists($id_tipo, $this->array_tipo_eventos))
            return false;
        if ($this->array_tipo_eventos[$id_tipo]['inicio'] > $year || $this->array_tipo_eventos[$id_tipo]['fin'] < $year)
            return false;
        return true;
    }

    public function eliminar($radio_date=null) {
        $sql= "delete from ttipo_eventos where id = $this->id_tipo_evento ";
        $this->do_sql_show_error('eliminar', $sql);

        $sql= "update teventos set id_tipo_evento = NULL where id_tipo_evento = $this->id_tipo_evento ";
        $this->do_sql_show_error('eliminar', $sql);
    }

    public function fix_numero() {
        $sql= "select numero, id_subcapitulo from ttipo_eventos where empresarial = $this->empresarial ";
        $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($this->id_subcapitulo)) {
            $sql.= "and ((id_subcapitulo is not null and id_subcapitulo= $this->id_subcapitulo) ";
            $sql.= "or id = $this->id_subcapitulo) ";
        } else
            $sql.= "and id_subcapitulo is null ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $sql.= "order by subcapitulo0 desc, subcapitulo1 desc limit 1 ";

        $result= $this->do_sql_show_error('fix_numero', $sql);
        $row= $this->clink->fetch_array($result);
        $array= array('numero'=>$row[0], 'id_subcapitulo'=>$row[1]);
        return $array;
    }

    public function get_subcapitulos($id= null) {
        $id= !empty($id) ? $id : $this->id_tipo_evento;
        $sql= "select * from ttipo_eventos where id = $id or id_subcapitulo = $id";
        $result= $this->do_sql_show_error('get_subcapitulos', $sql);
        $cant= $this->clink->num_rows($result);
        if ($cant == 0)
            return null;

        $array= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array[]= $row['id'];
        }
        return $array;
    }

    private function _build_numero($empresarial, $subcapitulo0, $subcapitulo1) {
        $numero= (int)$empresarial-1;
        $numero.= !empty($subcapitulo0) ? ".$subcapitulo0" : "";
        $numero.= !empty($subcapitulo1) ? ".$subcapitulo1" : "";
        return $numero;
    }

    private function _update_tipo($result) {
        $i= 0;
        $sql= null;
        $array_id_tipos= array();
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            list($_empresarial, $subcapitulo0, $subcapitulo1)= preg_split('/\./', $row['numero']);

            $array_id_tipos[$row['id']]= array($row['id'], $this->subcapitulo0, $subcapitulo1);

            $numero= $this->_build_numero($this->empresarial, $this->subcapitulo0, $subcapitulo1);
            $subcapitulo0= setNULL($this->subcapitulo0);

            $index= ($this->empresarial*pow(10,6)) + ($this->subcapitulo0 ? $this->subcapitulo0*pow(10,3) : 0) + $subcapitulo1*10;

            $sql.= "update ttipo_eventos set numero = '$numero', empresarial= $this->empresarial, ";
            $sql.= "subcapitulo0= $subcapitulo0, subcapitulo1= $subcapitulo1, indice= $index ";
            $sql.= "where id = {$row['id']}; ";

            if ($i > 10) {
                $this->do_multi_sql_show_error('_update_tipo', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql) {
            $this->do_multi_sql_show_error('_update_tipo', $sql);
        }

        foreach ($array_id_tipos as $id => $array) {
            $this->_update_numero_eventos($id);
        }
    }

    private function _update_numero_eventos($id) {
        global $string_procesos_down_entity;

        $sql= "select distinct teventos.id as _id, id_auditoria, id_tarea, id_tipo_evento, teventos.indice as e_indice, ";
        $sql.= "ttipo_eventos.indice as t_indice from teventos, ttipo_eventos where teventos.id_tipo_evento = ttipo_eventos.id ";
        $sql.= "and (id_tipo_evento = $id and ttipo_eventos.id = $id)";
        $result = $this->do_sql_show_error('_update_numero_eventos', $sql);

        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $indice= setNULL($row['t_indice']);

            $sql.= "update teventos set empresarial= $this->empresarial, indice= $indice ";
            $sql.= "where id = {$row['_id']}; ";
            $sql.= "update tproceso_eventos_{$this->year} set empresarial= $this->empresarial, indice= $indice ";
            $sql.= "where id_evento = {$row['_id']} ";
            if (!empty($row['id_auditoria']))
                $sql.= "or id_auditoria = {$row['id_auditoria']} ";
            if (!empty($row['id_tarea']))
                $sql.= "or id_tarea = {$row['id_tarea']} ";
            $sql.= "and id_proceso in ($string_procesos_down_entity); ";

            if ($i > 10) {
                $this->do_multi_sql_show_error('_update_numero_eventos', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql) {
            $this->do_multi_sql_show_error('_update_numero_eventos', $sql);
        }
    }

    public function update_empresarial_down() {
        $sql= "select * from ttipo_eventos where id_proceso = $this->id_proceso and id_subcapitulo = $this->id";
        $result = $this->do_sql_show_error('update_empresarial_down', $sql);

        $this->_update_tipo($result);
    }

    public function update_subcapitulo_down($id_subcapitulo) {
        $sql= "select * from ttipo_eventos where id_proceso = $this->id_proceso and id_subcapitulo = $id_subcapitulo";
        $result = $this->do_sql_show_error('update_subcapitulo_down', $sql);

        $this->_update_tipo($result);
    }

    private function _copy($result, &$array_ids, $id_target, $id_target_code) {
        while ($row= $this->clink->fetch_array($result)) {
            $this->nombre= $row['nombre'];
            $this->descripcion= $row['descripcion'];

            $this->numero= $row['numero'];
            $this->indice= $row['indice'];

            $this->empresarial= $row['empresarial'];
            $this->inicio= $row['inicio'];
            $this->fin= $row['fin'];
            $this->subcapitulo0= $row['subcapitulo0'];
            $this->subcapitulo1= $row['subcapitulo1'];

            $array= !empty($row['id_subcapitulo']) ? $array_ids[$row['id_subcapitulo']] : array(null, null);
            $this->id_subcapitulo= $array[0];
            $this->id_subcapitulo_code= $array[1];

            $this->id_proceso= $id_target;
            $this->id_proceso_code= $id_target_code;

            $this->add();
            $array_ids[$row['id']]= array($this->id, $this->id_code);
        }
    }

    public function copy($id_origen, $id_target, $id_target_code) {
        $array_ids= array();

        $sql= "select * from $this->table where id_proceso = $id_origen and id_subcapitulo is null ";
        $result = $this->do_sql_show_error("copy -- $this->table", $sql);
        $this->_copy($result, $array_ids, $id_target, $id_target_code);

        $sql= "select * from $this->table where id_proceso = $id_origen and id_subcapitulo is not null ";
        $sql.= "order by subcapitulo0 asc, subcapitulo1 asc";
        $result = $this->do_sql_show_error("copy -- $this->table", $sql);
        $this->_copy($result, $array_ids, $id_target, $id_target_code);
    }

    /*
    Encontrar el id_tipo_evento del tipo evento en otra entidad que tenga el mismo nombre
    */
    public function get_from_other_entity($id_origen, $id_entity_origen, $id_entity_target) {
        $sql= "select t2.* from ttipo_eventos as t1, ttipo_eventos as t2 ";
        $sql.= "where (lower(t1.nombre) = lower(t2.nombre) and t1.id = $id_origen) ";
        $sql.= "and (t1.id_proceso = $id_entity_origen and t2.id_proceso = $id_entity_target) ";
        if (!empty($this->year))
            $sql.= "and (t2.inicio >= $this->year and t2.fin >= $this->year) ";

        $result = $this->do_sql_show_error("get_from_other_entity", $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row['id']) ? array($row['id'], $row['id_code']) : null;
    }
}

?>