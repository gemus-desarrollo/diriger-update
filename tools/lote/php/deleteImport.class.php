<?php
/**
 * Description of deleteImport
 *
 * @author mustelier
 */
include_once "../../common/file.class.php";
include_once "base.class.php";
include_once "baseBond.class.php";
include_once "../../../php/class/time.class.php";
include_once "../../../php/class/code.class.php";


class TdeleteImport extends TbaseBond {
    public $table;

    public function __construct($dblink) {
        TbaseBond::__construct($dblink);
        $this->dblink= $dblink;
        
        $this->table= "tdeletes";
    }    

    private function _insert_row($row) {
        $campo1= setNULL_str($row['campo1']);
        $valor1= setNULL_str($row['valor1']);
        $campo2= setNULL_str($row['campo2']);
        $valor2= setNULL_str($row['valor2']);
        $campo3= setNULL_str($row['campo3']);
        $valor3= setNULL_str($row['valor3']);
        
        $id_usuario= setNULL($row['id_usuario']);
        $origen_data= setNULL_str($row['origen_data']);
        $observacion= setNULL_str($row['observacion']);
                
        $sql= "insert into tdeletes (tabla, campo1, valor1, campo2, valor2, campo3, valor3, id_usuario, origen_data, ";
        $sql.= "observacion, cronos, cronos_syn, situs) values ('{$row['tabla']}', $campo1, $valor1, $campo2, $valor2, ";
        $sql.= "$campo3, $valor3, $id_usuario, $origen_data, $observacion, '{$row['cronos']}', now(), '{$row['situs']}'); ";
        return $sql;
    }    

    private function field_tdeletes($campo, $value) {
        if (empty($campo)) 
            return null;
        
        $array= array("id_usuario", "id_responsable");
        if (array_search($campo, $array) === false) 
            return array($campo, $value, true);
        
        $sql= "select id from tusuarios where noIdentidad = '$value' and noIdentidad is not null";
        $result= $this->db_sql_show_error('field_tdeletes', $sql);
        $row= $this->dblink->fetch_array($result);
        return !empty($row['id']) ? array($campo, $row['id'], false) : null;
    }
        
    private function _tabla_id($row) {
        $tabla_ref= null;
        $id_ref= null;
        switch ($row['tabla']) {
            case "teventos":
                $tabla_ref= "tproceso_eventos";
                $id_ref= "id_evento_code";
                break;
            case "tauditorias":
                $tabla_ref= "tproceso_eventos";
                $id_ref= "id_auditoria_code";
                break;
            case "ttareas":
                $tabla_ref= "tproceso_eventos";
                $id_ref= "id_tarea_code";
                break;
            case "tnotas":
                $tabla_ref= "tproceso_riesgos";
                $id_ref= "id_nota_code";
                break;
            case "triesgos":
                $tabla_ref= "tproceso_riesgos";
                $id_ref= "id_riesgo_code";
                break;
            case "tproyectos":
                $tabla_ref= "tproceso_proyectos";
                $id_ref= "id_proyecto_code";
                break;
            case "tindicadores":
                $tabla_ref= "tproceso_indicadores";
                $id_ref= "id_indicador_code";
                break;
            case "tprogramas":
                $tabla_ref= "tproceso_proyectos";
                $id_ref= "id_programa_code";
                break;
            case "tobjetivos":
                $tabla_ref= "tproceso_objetivos";
                $id_ref= "id_objetivo_code";
                break;
            default:
                break;
        }
        
        return array($tabla_ref, $id_ref);
    }    
    
    private function permit_delete($row) {
        $array_tables= array("tproyectos", "teventos", "ttareas", "tauditorias",  
                            "triesgos", "tnotas", "tobjetivos", "tindicadores", "tprogramas");
        $array_table_fixed= array("tperspectivas", "tinductores", "ttematicas");
        
        if (array_search($row['tabla'], $array_tables) == false)
            return null;
        if ($row['tabla'] == "tusuarios")
            return null;
        if (!is_null($row['campo2']) || !is_null($row['campo3']))
            return null;
        if (empty($row['campo1']) || empty($row['valor1']))
            return null;

        $_sql= "select id_proceso from {$row['tabla']} where id_code = '{$row['valor1']}'";
        $result= $this->db_sql_show_error('permit_delete', $_sql);
        if (!$result)
            return null;
        $_row= $this->dblink->fetch_array($result);
        if (empty($_row['id_proceso']))
            return null;
        
        $sql= null;
        $array_ref= $this->_tabla_id($row);
        
        if (array_search($row['tabla'], $array_tables) != false) {
            if ($_row['id_proceso'] != $this->id_origen) {
                if ($this->if_origen_down) {
                    $sql= "update _tdeletes set tabla= '{$array_ref[0]}', campo1= '{$array_ref[1]}', valor1= '{$row['valor1']}', ";
                    $sql.= "campo2= 'id_proceso_code', valor2= '{$_SESSION['local_proceso_id_code']}' where id = {$row['id']}";
                }
                if (!$this->if_origen_down) 
                    $sql= "delete from {$row['tabla']} where id_code = '{$row['valor1']}'; ";
            }
        }
        
        if ($_row['id_proceso'] == $this->id_origen) 
            $sql= "delete from {$row['tabla']} where id_code = '{$row['valor1']}'; ";

        return $sql;
    }    
    
    private function _delete_row(&$row) {
        if (!empty($row['campo1']) && is_null($row['valor1']))
            return null;
        if (!empty($row['campo2']) && is_null($row['valor2']))
            return null;
        if (!empty($row['campo3']) && is_null($row['valor3']))
            return null;

        $sql_piece= $this->permit_delete($row);
        if (!is_null($sql_piece)) {
            if ($sql_piece != false) 
                return $sql_piece;
            else 
                return null;
        }
        
        $array_tables= array("treg_evento", "tusuario_eventos", "tproceso_eventos");
        
        if ($row['tabla'] == "usuario_eventos") 
            $row['tabla']= "tusuario_eventos"; 
        $table= $row['tabla'];
        
        $j= 0;
        $sql_temp= null;
        
        $array= $this->field_tdeletes($row['campo1'], $row['valor1']);
        if (!is_null($array)) {
            ++$j;
            $sql_temp.= "and {$array[0]} = ";
            $sql_temp.= $array[2] ? "'{$array[1]}'" : "{$array[1]}";
            $row['valor1']= $array[1];
        } else
            if (!empty($row['campo1'])) 
                return null;
            
        $array= $this->field_tdeletes($row['campo2'], $row['valor2']);
        if (!is_null($array)) {
            ++$j;
            $sql_temp.= " and {$array[0]} = ";
            $sql_temp.= $array[2] ? "'{$array[1]}'" : "{$array[1]}";
            $row['valor2']= $array[1];
        } else 
            if (!empty($row['campo2'])) 
                return null;

        $array= $this->field_tdeletes($row['campo3'], $row['valor3']);
        if (!is_null($array)) {
            ++$j;
            $sql_temp.= " and {$array[0]} = ";
            $sql_temp.= $array[2] ? "'{$array[1]}'" : "{$array[1]}";
            $row['valor3']= $array[1];
        } else 
            if (!empty($row['campo3'])) 
                return null;
        
        if (empty($j)) 
            return null;
        
        $sql= null;
        if ($table == "tusuarios") {
            $sql.= "update tusuarios set eliminado= '{$row['cronos']}' where noIdentidad = '{$row['valor1']}' ";
            $sql.= "and tusuarios.id_proceso_code = '$this->id_origen_code'; ";
            
            $sql.= "update tusuario_grupos, tusuarios set tusuario_grupos.eliminado= '{$row['cronos']}' ";
            $sql.= "where (tusuario_grupos.id_usuario = tusuarios.id and tusuarios.noIdentidad = '{$row['valor1']}') ";
            $sql.= "and tusuarios.id_proceso_code = '$this->id_origen_code'; ";
        } else {
            if (array_search($table, $array_tables) === false)
                $sql.= "delete from $table where 1 $sql_temp and {$table}.cronos < '{$row['cronos']}'; ";
            else 
                for ($year= $this->year_init; $year <= $this->year_end; $year++)
                    $sql.= "delete from {$table}_{$year} where 1 $sql_temp and {$table}_{$year}.cronos < '{$row['cronos']}'; ";
        }    
        return $sql;        
    }

    public function _tdeletes() {
        $this->table= "tdeletes";

        $sql= "select * from _tdeletes";
        $result= $this->db_sql_show_error('_tdeletes', $sql);
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $sql_piece= $this->_delete_row($row);
            if (!empty($sql_piece)) {
                $sql.= $sql_piece;
                $sql.= $this->_insert_row($row);
                $i+=2;
            }
            ++$i;
            
            $this->db_multi_sql_show_error("_tdeletes", $sql, false);
            $sql= null;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $i= 0;
                $r= (float)($j) / $nums;
                $_r= $r*100; $_r= number_format($_r,1); 
                bar_progressCSS(2, "_tdeletes ..... ", $r);                 
            }
        }
    }
    
}
