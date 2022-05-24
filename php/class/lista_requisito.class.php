<?php
/**
 * Description of lista
 *
 * @author mustelier
 */

if (!class_exists('Ttipo_lista'))
    include_once "tipo_lista.class.php";
if (!class_exists('Tlista'))
    include_once "lista.class.php";

class Tlista_requisito extends Tlista {
    private $array_requisitos_prs;
    public $init_row_temporary;
    public $max_num_pages;
    public $max_row_in_page;

    public function __construct($clink= null) {
        Tlista::__construct($clink);
        $this->clink= $clink;
        
        $this->max_num_pages= 1;
        $this->max_row_in_page= _MAX_ROW_IN_PAGE_CHECKLIST;
        $this->init_row_temporary= 0;

        $this->className= "Tlista_requisito";
     }

    public function Set($id= null) {
        if (!empty($id))
            $this->id_requisito= $id;

        $sql= "select * from tlista_requisitos where id = $this->id_requisito";
        $result= $this->do_sql_show_error('Set', $sql);
        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_requisito= $this->id;

        $this->id_tipo_lista= $row['id_tipo_lista'];
        $this->id_tipo_lista_code= $row['id_tipo_lista_code'];
        
        if (!empty($this->id_tipo_lista)) {
            $obj_tipo= new Ttipo_lista($this->clink);
            $obj_tipo->SetIdTipo_lista($this->id_tipo_lista);
            $obj_tipo->Set();
            
            $this->componente= $obj_tipo->GetComponente();
            $this->id_capitulo= $obj_tipo->GetIdCapitulo();
            $this->id_capitulo_code= $obj_tipo->get_id_capitulo_code();

            $this->capitulo= $obj_tipo->GetCapitulo();
            $this->subcapitulo= $obj_tipo->GetSubcapitulo();            
        }
        
        $this->id= $this->id_requisito;
        $this->id_code= $row['id_code'];
        $this->id_requisito_code= $this->id_code;

        $this->numero= $row['numero'];
        $this->componente= $row['componente'];

        $this->nombre= stripslashes($row['nombre']);
        $this->evidencia= stripslashes($row['evidencia']);
        $this->indicacion= stripslashes($row['indicacion']);
        $this->peso= $row['peso'];
        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];

        $this->id_lista= $row['id_lista'];
        $this->id_lista_code= $row['id_lista_code'];

        if (!empty($this->id_auditoria)) {
            $obj= new Tnota($this->clink);
            $obj->SetIdAuditoria($this->id_auditoria);
            $obj->SetIdRequisito($this->id_requisito);

            $obj->Set();

            $this->id_nota= $obj->GetIdNota();
            $this->id_nota_code= $obj->get_id_nota_code();
        }
    }

    protected function set_tidx() {
        $this->indice= null;
        $this->indice_plus= null;

        if (empty($this->id_tipo_lista))
            $this->indice= !empty($this->componente) ? $this->componente*pow(10,6) : null;
        else {
            $obj= new Ttipo_lista($this->clink);
            $obj->Set($this->id_tipo_lista);
            $this->indice= $obj->indice;
        }
    }

    public function add() {
        $this->set_tidx();

        $nombre= setNULL_str($this->nombre);
        $componente= setNULL($this->componente);

        $id_tipo_lista= setNULL($this->id_tipo_lista);
        $id_tipo_lista_code= setNULL_str($this->id_tipo_lista_code);

        $evidencia= setNULL_str($this->evidencia);
        $indicacion= setNULL_str($this->indicacion);

        $indice= setNULL($this->indice);

        $sql= "insert into tlista_requisitos (numero, nombre, componente, id_lista, id_lista_code, id_tipo_lista, ";
        $sql.= "id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, indice, id_usuario, cronos, situs) ";
        $sql.= " values ($this->numero, $nombre, $componente, $this->id_lista, '$this->id_lista_code', ";
        $sql.= "$id_tipo_lista, $id_tipo_lista_code, $this->peso, $this->inicio, $this->fin, $evidencia, ";
        $sql.= "$indicacion, $indice, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tlista_requisitos");
            $this->id_requisito = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tlista_requisitos', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_requisito_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $this->set_tidx();

        $nombre= setNULL_str($this->nombre);
        $componente= setNULL($this->componente);

        $id_tipo_lista= setNULL($this->id_tipo_lista);
        $id_tipo_lista_code= setNULL_str($this->id_tipo_lista_code);

        $evidencia= setNULL_str($this->evidencia);
        $indicacion= setNULL_str($this->indicacion);

        $indice= setNULL($this->indice);

        $sql= "update tlista_requisitos set nombre= $nombre, numero= $this->numero, indicacion= $indicacion, ";
        $sql.= "evidencia= $evidencia, peso= $this->peso, id_lista= $this->id_lista, id_lista_code= '$this->id_lista_code', ";
        $sql.= "componente= $componente, id_tipo_lista= $id_tipo_lista, id_tipo_lista_code=$id_tipo_lista_code, ";
        $sql.= "inicio= $this->inicio, fin= $this->fin, cronos= '$this->cronos', situs= '$this->location', ";
        $sql.= "indice= $indice where id = $this->id_requisito ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    private function _get_requistos_by_procesos() {
        $sql= "select * from tproceso_listas where id_lista = $this->id_lista and id_proceso = $this->id_proceso";
        $result= $this->do_sql_show_error('_get_requistos_by_procesos', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id_requisito'], 'numero'=>$row['numero'], 'numero_plus'=>null, 
                'nombre'=>$row['nombre'], 'evidencia'=>$row['evidencia'], 'indicacion'=>$row['indicacion'], 
                'inicio'=>$row['inicio'], 'fin'=>$row['fin'], 'indice'=>$row['indice']);
            $this->array_requisitos_prs[$row['id_requisito']]= $array;
        }        
    }

    private function _listar($result, $componente= null, $id_capitulo= null) {
        if (!empty($this->id_proceso) && is_null($this->array_requisitos_prs)) {
            $this->_get_requistos_by_procesos();
        }

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['_id'];
            
            if (!empty($this->id_proceso) && !empty($this->array_requisitos_prs)) {
                if (!array_key_exists($row['id'], $this->array_requisitos_prs))
                    continue;
            }

            ++$i;
            if ($this->id_tipo_lista || $id_capitulo)
                $numero= "{$row['_numero']}.";
            else
                $numero= !empty($componente) ? "$componente.0." : "";
            $numero.= "{$row['numero_plus']}";

            $array= array('id'=>$row['id'], 'numero'=>$row['numero'], 'numero_plus'=>$numero, 'nombre'=>$row['nombre'],
                'evidencia'=>$row['evidencia'], 'indicacion'=>$row['indicacion'], 'inicio'=>$row['inicio'],
                'fin'=>$row['fin'], 'indice'=>$row['indice']);
            $this->array_requisitos[$row['id']]= $array;
        }
        $this->cant= $i;
        $this->clink->data_seek($result);
        return $result;
    }

    private function sql_listar($table, $include_sub_prs) {
        $sql= null;
        if (empty($this->id_proceso)) 
            $sql= " where 1 ";
        else {
            $sql= ", tproceso_listas where $table.id = tproceso_listas.id_requisito ";
            if (!$include_sub_prs) {
                $sql.= "and tproceso_listas.id_proceso = $this->id_proceso ";
            } else {
                $sql.= "and tproceso_listas.id_proceso in ";
                $sql.= "(select id from tprocesos where id = $this->id_proceso or tprocesos.id_proceso = $this->id_proceso) ";
            }    
        }    
        if (!empty($this->year))
            $sql.= "and ($table.inicio <= $this->year and $table.fin >= $this->year) ";
        if (!empty($this->id_lista))
            $sql.= "and $table.id_lista = $this->id_lista ";
        if (!empty($this->id_tipo_lista))
            $sql.= "and $table.id_tipo_lista = $this->id_tipo_lista ";
        if (!is_null($this->id_tipo_lista) && empty($this->id_tipo_lista))
            $sql.= "and (id_tipo_lista is null or id_tipo_lista = 0) ";     
        if (!empty($this->componente))
            $sql.= "and $table.componente = $this->componente "; 
            
        return $sql;
    }

    public function listar($include_sub_prs= false, $flag= true) {
        $include_sub_prs= !is_null($include_sub_prs) ? $include_sub_prs : false;
        $flag= !is_null($flag) ? $flag : true;
        
        $table= !$this->limited && $this->if_tlista_requisitos ? "_tlista_requisitos" : "tlista_requisitos";

        $sql= "select distinct $table.*, $table.id as _id, $table.id_code as _id_code, ";
        $sql.= "numero as numero_plus, null as _numero from $table ";
        $sql.= $this->sql_listar($table, $include_sub_prs);
        $sql.= "order by indice asc ";

        $result= $this->do_sql_show_error('listar', $sql);
        $max_nums_rows= $this->clink->num_rows($result);

        if ($this->limited && !empty($max_nums_rows)) {
            $init_row= $this->init_row_temporary*$this->max_row_in_page;

            if (empty($max_nums_rows) || $max_nums_rows == -1) {
                $this->max_num_pages= 1;
                return;
            } else {
                $this->max_num_pages= (int)ceil((float)$max_nums_rows/$this->max_row_in_page);

                if ($_SESSION["_DB_SYSTEM"] == "mysql") {
                    $sql.= "limit $init_row, $this->max_row_in_page ";
                } else {
                    $sql.= "LIMIT $this->max_row_in_page OFFSET $init_row ";
                }
            }
            
            $result= $this->do_sql_show_error('listar', $sql);

            $this->cant= $this->create_tlista_requisitos($result);
        }

        if ($flag && !empty($max_nums_rows)) {
            $this->clink->data_seek($result);
            return $this->_listar($result);
        }  
        else 
            return $result;    
    }

    public function listar_all($componente= null, $id_capitulo= null, $flag= true) {
        $table= !$this->limited && $this->if_tlista_requisitos ? "_tlista_requisitos" : "tlista_requisitos";
        $componente= !is_null($componente) ? $componente : $this->componente;
        $id_capitulo= !is_null($id_capitulo) ? $id_capitulo : $this->id_capitulo;

        if (!empty($id_capitulo)) {
            $obj_tipo= new Ttipo_lista($this->clink);
            $array_tipo_lista= $obj_tipo->get_array_tipo_lista($id_capitulo);
            $string_id_tipo_lista= implode($array_tipo_lista);
        }

        $sql= "select distinct $table.*, $table.id as _id, $table.id_code as _id_code, ";
        $sql.= "numero as numero_plus, indice as _indice from $table ";
        if (!empty($this->id_proceso)) {
            $sql.= ", tproceso_listas where $table.id = tproceso_listas.id_requisito ";
            $sql.= "and tproceso_listas.id_proceso = $this->id_proceso "; 
        } else {
            $sql.= "where 1 ";
        }

        if (!empty($this->year))
            $sql.= "and ($table.inicio <= $this->year and $table.fin >= $this->year) ";
        if (!empty($this->id_lista))
            $sql.= "and $table.id_lista = $this->id_lista ";

        $sql.= !empty($this->id_tipo_lista) ? "(id_tipo_lista is null or id_tipo_lista = $this->id_tipo_lista) " : " ";

        if (!is_null($componente) && empty($componente))
            $sql.= "and ($table.componente is null or $table.componente = 0) ";
        if (!empty($componente) && empty($this->id_tipo_lista))
            $sql.= "and $table.componente = $componente ";

        if (!is_null($this->id_tipo_lista) && empty($this->id_tipo_lista)) {
            if (!empty($id_capitulo))
                $sql.= "and id_tipo_lista = $id_capitulo ";
            if (!is_null($id_capitulo) && empty($id_capitulo))
                $sql.= "and (id_tipo_lista is null or id_tipo_lista = 0) ";
        }

        if (!empty($id_capitulo)) {
            $sql.= "and id_tipo_lista in ($string_id_tipo_lista) ";
        }
        $sql.= "order by componente asc, $table.indice asc ";

        $result= $this->do_sql_show_error('listar_all', $sql);   
        $max_nums_rows= $this->clink->num_rows($result);

        if ($flag)
            return $result;

        return $this->_listar($result, $componente, $id_capitulo);
    }

    public function eliminar($radio_date=null) {
        $sql= "delete from tlista_requisitos where id = $this->id_requisito ";
        $this->do_sql_show_error('eliminar', $sql);
    }

    public function find_numero($componente, $year= null, $id_tipo_lista= null) {
        $year= !empty($year) ? $year : $this->year;
        $id_tipo_lista= !empty($id_tipo_lista) ? $id_tipo_lista : $this->id_tipo_lista;
        if ($id_tipo_lista == -1 || $this->id_capitulo == $id_tipo_lista)
            $id_tipo_lista= 0;

        $sql= "select max(tlista_requisitos.numero) as _numero from tlista_requisitos ";
        if ($this->id_capitulo > 0 
            && ((!is_null($id_tipo_lista) && $id_tipo_lista == 0) || is_null($id_tipo_lista)))
            $sql.= ", ttipo_listas where tlista_requisitos.id_tipo_lista = ttipo_listas.id ";
        else
            $sql.= "where 1 ";
        $sql.= "and (tlista_requisitos.inicio <= $year and tlista_requisitos.fin >= $year) ";
        if (!empty($this->id_lista))
            $sql.= "and tlista_requisitos.id_lista = $this->id_lista ";
        if (!empty($componente))
            $sql.= "and tlista_requisitos.componente = $componente ";
        if (!is_null($this->id_capitulo) && $this->id_capitulo == 0)
            $sql.= "and (tlista_requisitos.id_tipo_lista is null or tlista_requisitos.id_tipo_lista = 0) ";

        if ($this->id_capitulo > 0 && is_null($id_tipo_lista)) {
            $sql.= "and (ttipo_listas.componente = $componente ";
            $sql.= "and (ttipo_listas.id = $this->id_capitulo or ttipo_listas.id_capitulo = $this->id_capitulo)) ";
        }  
        if (!empty($id_tipo_lista) && $id_tipo_lista > 0)
            $sql.= "and tlista_requisitos.id_tipo_lista = $id_tipo_lista ";
        if ($this->id_capitulo > 0 && (!is_null($id_tipo_lista) && $id_tipo_lista == 0)) {
            $sql.= "and (ttipo_listas.componente = $componente and ttipo_listas.id = $this->id_capitulo) ";            
        }

        $result= $this->do_sql_show_error('find_numero', $sql);
        $row= $this->clink->fetch_array($result);

        $numero= !empty($row['_numero']) ? (int)$row['_numero'] : 0;
        return ++$numero;
    }

    public function get_requsitos_array() {
        $sql= "select treg_nota.*, tlista_requisitos.nombre from treg_nota, tlista_requisitos ";
        $sql.= "where (treg_nota.id_requisito is not null and treg_nota.id_requisito = tlista_requisitos.id) ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        $sql.= "order by reg_fecha desc, cronos desc, id desc"; 
        
        $result= $this->do_sql_show_error('get_requsitos_array', $sql);
        if (empty($this->cant))
            return null;

        if ($this->array_requisitos)
            unset($this->array_requisitos);
        $this->array_requisitos= null;

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            $id_proceso= !empty($row['id_proceso']) ? $row['id_proceso'] : 0;
            $id_requisito= !empty($row['id_requisito']) ? $row['id_requisito'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria']: 0;

            if (!empty($array_ids[$id_requisito][$id_auditoria][$id_proceso]))
                continue;
            $array_ids[$id_requisito][$id_auditoria][$id_proceso]= $row['id'];    
            
            ++$i;
            $this->array_requisitos[$row['id_requisito']]= $row;
        }
        return $i;
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Tnota'))
    include_once "nota.class.php";