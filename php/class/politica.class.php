<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "escenario.class.php";

class Tpolitica extends Tescenario { 
    private $capitulo;
    private $grupo;
    private $if_titulo;
    private $if_capitulo;
    private $if_grupo;
    protected $if_inner;
    
    
    public function __construct($clink= null) {
        $this->clink= $clink;
        Tescenario::__construct($clink);

        $this->className= "Tpolitica";
    }

    public function GetCapitulo(){
        return $this->capitulo;
    }
    public function GetGrupo(){
        return $this->grupo;
    }
    public function GetIfTitulo(){
        return $this->if_titulo;
    }
    public function GetIfCapitulo(){
        return $this->if_capitulo;
    }
    public function GetIfGrupo(){
        return $this->if_grupo;
    }
    public function SetCapitulo($id){
        $this->capitulo = $id;
    }
    public function SetGrupo($id){
        $this->grupo = $id;
    }
    public function SetIfTitulo($id){
        $this->if_titulo = $id;
    }
    public function SetIfCapitulo($id){
        $this->if_capitulo = $id;
    }
    public function SetIfGrupo($id){
        $this->if_grupo = $id;
    }

    public function Set($id= null) {
        if (!empty($id)) 
            $this->id_politica= $id;

        $sql= "select * from tpoliticas where id = $this->id_politica ";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id_code= $row['id_code'];
            $this->id_politica_code= $this->id_code;

            $this->capitulo= $row['capitulo'];
            $this->grupo= $row['grupo'];
            $this->numero= $row['numero'];
            $this->if_titulo= $row['titulo'];
            $this->if_capitulo= ($this->if_titulo && !$this->capitulo) ? true : false;

            $this->nombre= stripslashes($row['nombre']);
            $this->observacion= stripslashes($row['observacion']);

            $this->inicio= $row['inicio'];
            $this->fin= $row['fin'];

            $this->if_inner= $row['if_inner'];

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];
        }

        return $this->error;
    }	 
    
    public function GetNumero() {
        if (!empty($this->numero)) 
            return $this->numero;
        else 
            return $this->find_numero();
    }
    
    protected function find_numero() {
        $sql= "select max(numero) from tpoliticas where inicio <= $this->year and fin >= $this->year ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty(!empty($this->capitulo)))
            $sql.= "and capitulo = $this->capitulo ";
        if (!empty($this->grupo))
            $sq.= "and grupo = $this->grupo ";
        $result= $this->do_sql_show_error("find_numero(tpoliticas)", $sql);
        if (!$result)
            return null;
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? ++$row[0] : 1;
    }    
	 
    public function add($if_inner= 1) {
        $observacion= setNULL_str($this->observacion);
        $nombre= setNULL_str($this->nombre);

        $if_titulo= boolean2pg($this->if_titulo);
        $grupo= setNULL($this->grupo);
        $capitulo= setNULL($this->capitulo);
        $if_inner= boolean2pg($if_inner);

        $sql= "insert tpoliticas (titulo, numero, capitulo, grupo, nombre, observacion, inicio, fin, if_inner, ";
        $sql.= "id_proceso, id_proceso_code, cronos, situs) values ($if_titulo, $this->numero, $capitulo, $grupo, ";
        $sql.= "$nombre, $observacion, $this->inicio, $this->fin, $if_inner, {$_SESSION['id_entity']}, ";
        $sql.= "'{$_SESSION['id_entity_code']}', '$this->cronos', '$this->location')";
        
        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id= $this->clink->inserted_id("tpoliticas");
            $this->id_politica= $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tpoliticas','id','id_code');
            $this->id_code= $this->obj_code->get_id_code();
            $this->id_politica_code= $this->id_code;
        }	 
        return $this->error;
    }
    
    
    public function update() {
        $observacion= setNULL_str($this->observacion);
        $nombre= setNULL_str($this->nombre);

        $if_titulo= boolean2pg($this->if_titulo);
        $this->grupo= setNULL($this->grupo);
        $this->capitulo= setNULL($this->capitulo);

        $sql= "update tpoliticas set nombre= $nombre, observacion= $observacion, numero= $this->numero, ";
        $sql.= "capitulo= $this->capitulo, grupo= $this->grupo, titulo= $if_titulo, inicio= $this->inicio, ";
        $sql.= "fin= $this->fin, cronos= '$this->cronos', situs= '$this->location' where id = $this->id_politica ";
        $this->do_sql_show_error('update', $sql);

        return $this->error; 
    }

    public function eliminar($radio_date= null) {
        $error= null;
        $year= ($radio_date == 2) ? $this->inicio : $this->year;

        $obj= new Treference($this->clink);
        $obj->empty_tpolitica_objetivos($this->id_politica, null, $this->year);

        if ($radio_date == 2 || $year == $this->inicio) {
            $obj->empty_tpolitica_objetivos();

            $sql= "delete from tpoliticas where id = $this->id_politica";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Este política o lineamiento contiene objetivos estrategicos asociados. Para borrarla, este no debe tener objetivos relacionados. ";
                $error .= "Vacie la política o lineamiento, desde la lista de objetivos estrategicos y despues intente borrarla nuevamente.";
            }
        } else {
            $year= $this->year-1;
            $sql= "update tpoliticas set fin= $year where id = $this->id_politica ";
            $this->do_sql_show_error('eliminar', $sql);
        }

        return $error;
    }
     	
    public function listar($flag= false, $string_procesos_down_entity= null) {
        $flag= !is_null($flag) ? $flag : false;

        if (!is_null($this->if_titulo)) 
            $this->if_titulo= $this->if_titulo ? 1 : 0;
        $if_titulo= boolean2pg($this->if_titulo);

        if ($flag) {
            $sql= "select distinct tpoliticas.*, tpoliticas.nombre as _nombre, tpoliticas.id as _id, tpoliticas.id_code as _id_code ";
            $sql.= "from tpoliticas, tobjetivos, tpolitica_objetivos where (tpoliticas.id = tpolitica_objetivos.id_politica ";
            $sql.= "and tpolitica_objetivos.id_objetivo = tobjetivos.id and peso > 0) ";
            if (!empty($this->id_proceso) && empty($string_procesos_down_entity)) 
                $sql.= "and tobjetivos.id_proceso = $this->id_proceso ";
            if (!empty($string_procesos_down_entity))
                $sql.= "and tobjetivos.id_proceso in ($string_procesos_down_entity) ";
        } else 
            $sql= "select distinct *, id as _id, id_code as _id_code from tpoliticas where 1 ";
        
        if (!empty($this->year)) 
            $sql.= "and (tpoliticas.inicio <= $this->year and tpoliticas.fin >= $this->year) ";
        if (!is_null($this->if_titulo)) 
            $sql.= "and titulo = $if_titulo ";
        if ($this->if_capitulo) 
            $sql.= "and capitulo is null ";
        if ($this->if_grupo) 
            $sql.= "and ((titulo = true and capitulo is not null) and grupo is null) ";
        if (!is_null($this->if_grupo) && !$this->if_grupo) 
            $sql.= "and (titulo = false and grupo is null) ";
        if (!empty($this->capitulo)) 
            $sql.= "and capitulo = $this->capitulo ";
        if (!is_null($this->capitulo) && empty($this->capitulo))
            $sql.= "and (capitulo is null or capitulo = 0) ";    
        if (!empty($this->grupo)) 
            $sql.= "and grupo = $this->grupo ";
        if (!is_null($this->grupo) && empty($this->grupo))
            $sql.= "and (grupo is null or grupo = 0) ";
        
        if (empty($string_procesos_down_entity))
            $sql.= "and (tpoliticas.id_proceso is null or tpoliticas.id_proceso = {$_SESSION['id_entity']}) ";
        if (!empty($string_procesos_down_entity))
            $sql.= "and (tpoliticas.id_proceso is null or tpoliticas.id_proceso in ($string_procesos_down_entity)) ";
        $sql.= "order by numero asc ";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function listar_by_reg() {
        $sql= "select distinct tpoliticas.*, tpoliticas.id as _id from tpoliticas, treg_politica ";
        $sql.= "where tpoliticas.id = treg_politica.id_politica and treg_politica.id_proceso = $this->id_proceso ";
        $sql.= "and year = $this->year ";

        if (!is_null($this->titulo)) 
            $sql.= "and titulo = $this->titulo ";
        if (!empty($this->capitulo)) 
            $sql.= "and capitulo = $this->capitulo ";
        if (!empty($this->grupo)) 
            $sql.= "and grupo = $this->grupo ";
        $sql.= "order by _id asc, numero asc ";

        $result= $this->do_sql_show_error('listar_by_reg', $sql);
        return $result;
    }
}

/*
* Funciones para el trabajo con politicas
*/
function create_array_valid_politicas($result) {
    global $year;
    global $array_politicas;
    global $clink;

    $obj= new Tpeso($clink);
    $obj->SetYear($year);

    $i= 0;
    while($row= $clink->fetch_array($result)) {
        ++$i;
        $obj->listar_objetivos_ref_politica($row['_id']);
        $cant= $obj->GetCantidad();
        if (empty($cant))
            continue;

        $array_politicas[$row['_id']]= $cant;
    }
    return $i;
}
