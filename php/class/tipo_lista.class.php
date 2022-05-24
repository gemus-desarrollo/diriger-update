<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */


include_once "base_tipo.class.php";

class Ttipo_lista extends Tbase_tipo {
    protected $componente;

    public function GetComponente() {
        return $this->componente;
    }
    public function SetComponente($id) {
        return $this->componente= $id;
    }

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_tipo::__construct($clink);

        $this->className= "Ttipo_lista";
        $this->table= "ttipo_listas";
    }

    public function Set($id= null) {
        $this->id_tipo_lista= !empty($id) ? $id : $this->id_tipo_lista;

        $sql= "select * from ttipo_listas where id = $this->id_tipo_lista ";
        $result= $this->do_sql_show_error('Set', $sql);

        if (!$result)
            return $this->error;

        $row= $this->clink->fetch_array($result);

        $this->id= $this->id_tipo_lista;
        $this->id_code= $row['id_code'];
        $this->id_tipo_lista_code= $this->id_code;

        $this->numero= $row['numero'];
        $this->nombre= stripslashes($row['nombre']);
        $this->descripcion= stripslashes($row['descripcion']);

        $this->componente= $row['componente'];
        $this->id_capitulo= $row['id_capitulo'];
        $this->id_capitulo_code= $row['id_capitulo_code'];

        $this->capitulo= $row['capitulo'];
        $this->subcapitulo= $row['subcapitulo'];

        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];

        $this->id_lista= $row['id_lista'];
        $this->id_lista_code= $row['id_lista_code'];
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $numero= setNULL_str($this->numero);
        $id_capitulo= setNULL($this->id_capitulo);
        $id_capitulo_code= setNULL_str($this->id_capitulo_code);

        $capitulo= setNULL($this->capitulo);
        $subcapitulo= setNULL($this->subcapitulo);
        $inicio= setNULL($this->inicio);
        $fin= setNULL($this->fin);
        $index= ($this->componente*pow(10,6)) + ($this->capitulo ? $this->capitulo*pow(10,3) : 0) + $this->subcapitulo*10;

        $sql= "insert into ttipo_listas (nombre, numero, descripcion, componente, capitulo, id_capitulo, subcapitulo, ";
        $sql.= "indice, inicio, fin, cronos, situs, id_capitulo_code, id_proceso, id_proceso_code, id_lista, id_lista_code) ";
        $sql.= "values ($nombre, $numero, $descripcion, $this->componente, $capitulo, $id_capitulo, $subcapitulo, ";
        $sql.= "$index, $inicio, $fin, '$this->cronos', '$this->location', $id_capitulo_code, $this->id_proceso, ";
        $sql.= "'$this->id_proceso_code', $this->id_lista, '$this->id_lista_code')";

        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tlistas");
            $this->id_tipo_lista = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('ttipo_listas', 'id', 'id_code');

            $this->id_code= $this->obj_code->get_id_code();
            $this->id_tipo_lista_code= $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $numero= setNULL_str($this->numero);

        $id_capitulo= setNULL($this->id_capitulo);
        $id_capitulo_code= setNULL_str($this->id_capitulo_code);

        $capitulo= setNULL($this->capitulo);
        $subcapitulo= setNULL($this->subcapitulo);
        $inicio= setNULL($this->inicio);
        $fin= setNULL($this->fin);
        $index= ($this->componente*pow(10,6)) + ($this->capitulo ? $this->capitulo*pow(10,3) : 0) + $this->subcapitulo*10;

        $sql = "update ttipo_listas set nombre= $nombre, numero= $numero, descripcion= $descripcion, ";
        $sql.= "componente= $this->componente, capitulo= $capitulo, id_capitulo= $id_capitulo, ";
        $sql.= "subcapitulo= $subcapitulo, indice= $index, inicio= $inicio, fin= $fin, situs= '$this->location', ";
        $sql.= "cronos= '$this->cronos' where id = $this->id_tipo_lista ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function eliminar() {
        $sql= "delete from ttipo_listas where id = $this->id_tipo_lista";
        $result= $this->do_sql_show_error('update', $sql);
    }

    public function listar($componente= null, $id_capitulo= null) {
        $componente= !empty($componente) ? $componente : $this->componente;
        $id_capitulo= !is_null($id_capitulo) ? $id_capitulo : $this->id_capitulo;

        $sql= "select distinct * from ttipo_listas where 1 ";
        if (!empty($componente))
            $sql.= "and componente = $componente ";
        if (!empty($this->id_lista))
            $sql.= "and id_lista = $this->id_lista ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($id_capitulo))
            $sql.= "and id_capitulo = $id_capitulo ";
        else {
            if (!is_null($id_capitulo) && empty($id_capitulo))
                $sql.= "and (id_capitulo is null or id_capitulo = 0) ";
        }
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and $this->year <= fin) ";
        $sql.= "order by componente asc, capitulo asc, subcapitulo asc";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function fix_numero() {
        $sql= "select numero, id_capitulo from ttipo_listas where componente = $this->componente ";
        $sql.= "and id_lista = $this->id_lista ";

        if (!empty($this->id_capitulo)) {
            $sql.= "and ((id_capitulo is not null and id_capitulo= $this->id_capitulo) ";
            $sql.= "or id = $this->id_capitulo) ";
        } else
            $sql.= "and id_capitulo is null ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $sql.= "order by capitulo desc, subcapitulo desc limit 1 ";

        $result= $this->do_sql_show_error('fix_numero', $sql);
        $row= $this->clink->fetch_array($result);
        $array= array('numero'=>$row[0], 'id_capitulo'=>$row[1]);

        return $array;
    }

    private function _build_numero($componente, $capitulo, $subcapitulo) {
        $numero= $componente;
        $numero.= !empty($capitulo) ? ".$capitulo" : "";
        $numero.= !empty($subcapitulo) ? ".$subcapitulo" : "";
        return $numero;
    }

    private function _update_tipo($result) {
        $i= 0;
        $sql= null;
        $array_id_tipos= array();
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            list($_componente, $_capitulo, $_subcapitulo)= preg_split('/\./', $row['numero']);

            $array_id_tipos[$row['id']]= array($row['id'], $this->capitulo);

            $numero= $this->_build_numero($this->componente, $this->capitulo, $_subcapitulo);
            $capitulo= setNULL($this->capitulo);

            $sql.= "update ttipo_listas set numero = '$numero', componente= $this->componente, capitulo= $capitulo ";
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
            $this->_update_numero_requsitos($id);
        }
    }

    public function update_componente_down() {
        $sql= "select * from ttipo_listas where id_lista = $this->id_lista and id_capitulo = $this->id ";
        $result = $this->do_sql_show_error('update_componente_down', $sql);

        $this->_update_tipo($result);
    }

    public function update_capitulo_down($id_capitulo) {
        $sql= "select * from ttipo_listas where id_lista = $this->id_lista and id_capitulo = $id_capitulo ";
        $result = $this->do_sql_show_error('update_capitulo_down', $sql);

        $this->_update_tipo($result);
    }

    private function _update_numero_requsitos($id) {
        $sql= "select distinct tlista_requsitos.id as _id, id_tipo_lista, tlista_requsitos.numero as r_numero, ";
        $sql.= "ttipo_listas.numnero as t_numero, ttipo_listas.capitulo as _capitulo ";
        $sql.= "from tlista_requsitos, ttipo_listas where tlista_requsitos.id_tipo_lista = ttipo_listas.id ";
        $sql.= "where (id_tipo_lista = $id and ttipo_listas.id = $id)";
        $result = $this->do_sql_show_error('_update_numero_requsitos', $sql);

        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $ipos= strripos('.', $row['r_numero']);
            $r_number= substr($row['r_numero'], $ipos);
            $numero= $row['t_numero'].$r_number;
            $capitulo= setNULL($row['_capitulo']);

            $sql= "update tlista_requistos set numero= '$numero', capitulo= $capitulo ";
            $sql.= "where id = {$row['_id']}; ";

            if ($i > 10) {
                $this->do_multi_sql_show_error('_update_numero_requsitos', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql) {
            $this->do_multi_sql_show_error('_update_numero_requsitos', $sql);
        }
    }


    private function _copy($result, &$array_ids, $id_target, $id_target_code) {
        while ($row= $this->clink->fetch_array($result)) {
            $this->nombre= $row['nombre'];
            $this->descripcion= $row['descripcion'];

            $this->numero= $row['numero'];
            $this->indice= $row['indice'];

            $this->componente= $row['componente'];
            $this->year= $row['year'];
            $this->capitulo= $row['capitulo'];
            $this->subcapitulo= $row['subcapitulo'];

            $this->id_lista= $row['id_lista'];
            $this->id_lista_code= $row['id_lista_code'];

            $array= !empty($row['id_capitulo']) ? $array_ids[$row['id_capitulo']] : array(null, null);
            $this->id_capitulo= $array[0];
            $this->id_capitulo_code= $array[1];

            $this->id_proceso= $id_target;
            $this->id_proceso_code= $id_target_code;

            $this->add();
            $array_ids[$row['id']]= array($this->id, $this->id_code);
        }
    }

    public function copy($id_origen, $id_target, $id_target_code) {
        $array_ids= array();

        $sql= "select * from $this->table where id_proceso = $id_origen and id_capitulo is null ";
        $result = $this->do_sql_show_error("copy -- $this->table", $sql);
        $this->_copy($result, $array_ids, $id_target, $id_target_code);

        $sql= "select * from $this->table where id_proceso = $id_origen and id_capitulo is not null ";
        $sql.= "order by capitulo asc, subcapitulo asc";
        $result = $this->do_sql_show_error("copy -- $this->table", $sql);
        $this->_copy($result, $array_ids, $id_target, $id_target_code);
    }

    public function get_array_tipo_lista($id_tipo_lista= null) {
        $id_tipo_lista= !empty($id_tipo_lista) ? $id_tipo_lista : $this->id_tipo_lista;
        if (empty($id_tipo_lista))
            return null;

        $sql= "select * from ttipo_listas where id = $id_tipo_lista or ttipo_listas.id_capitulo = $id_tipo_lista";
        $result = $this->do_sql_show_error('get_array_tipo_lista', $sql);

        $array_tipo_lista= null;
        while ($row= $this->clink->fetch_array($result)) {
            $array_tipo_lista[$row['id']]= $row['id'];
        }
        return $array_tipo_lista;
    }
}
