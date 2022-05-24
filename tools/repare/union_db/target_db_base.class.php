<?php
/**
 * Created by Visual Studio Code.
 * User: PhD. Geraudis Mustelier
 * Date: 11/07/2020
 * Time: 7:16
 */

class Tdb_target_base {
    public $error;
    public $clink_origen;
    public $clink;
    protected $table;
    public $array_tables;
    public $array_tables_list;
    public $num_tables;
    protected $array_ids_fixed;

    public $db_origen,
            $db_target;
    public $code_origen,
            $code_target;
    public $id_proceso_origen,
            $id_proceso_target;
    public $id_proceso_origen_code,
            $id_proceso_target_code;

    protected $array_id_code;
    protected $num_rows_table;

    public function set_id_procesos() {
        $sql= "select id, id_code from tprocesos where codigo = '$this->code_origen'";
        $result= $this->clink_origen->query($sql);
        if (!$result) {
            $this->error= $this->clink_origen->error();
            die("\nset_id_procesos => \nSQL=>$sql ==== ERROR:$this->error \n");
        }
        $row= $this->clink_origen->fetch_array($result);
        $this->id_proceso_origen= $row[0];
        $this->id_proceso_origen_code= $row[1];

        $sql= "select id, id_code from tprocesos where codigo = '$this->code_target'";
        $result= $this->clink->query($sql);
        if (!$result) {
            $this->error= $this->clink->error();
            die("\nset_id_procesos => \nSQL=>$sql ==== ERROR:$this->error \n");
        }
        $row= $this->clink->fetch_array($result);
        $this->id_proceso_target= $row[0];
        $this->id_proceso_target_code= $row[1];

        if (empty($this->id_proceso_origen) || empty($this->id_proceso_target))
            exit("No se han identificado los ID de los procesos. id_origen= $this->id_proceso_origen  id_target= $this->id_proceso_target \n");
    }

    protected function prepare_array_id_code() {
        if (isset($this->array_id_code)) {
            unset($this->array_id_code);
            $this->array_id_code= array();
            $this->num_rows_table= 0;
        }

        $sql= "select * from $this->table";
        $result= $this->clink->query($sql);
        $this->num_rows_table= $this->clink->num_rows($result);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_id_code[$row['id']]= $row['id_code'];
        }
    }

    protected function if_exist_id_code($id_origen, $id_code, $row_origen) {
        $id_target= array_search($id_code, $this->array_id_code);

        if ($id_target !== false) {
            $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>$id_code, 'id_target'=>$id_target,
                                                            'id_proceso_code'=>$row_origen['id_proceso_code']);
        } else {
            $id_target= $this->insert_target($row_origen);
            if (!empty($id_target)) {
                $id_target= is_array($id_target) ? $id_target[0] : $id_target;
                $id_code= is_array($id_target) ? $id_target[1] : $id_code;
                $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>$id_code, 'id_target'=>$id_target,
                                                                'id_proceso_code'=>$row_origen['id_proceso_code']);
            }
        }

        if (!empty($id_target))
            $this->update_id_code($id_origen, $id_target, $id_code);
    }

    protected function update_id_code($id_origen, $id_target, $id_code) {
        $sql= "update $this->table set id_code = '$id_code' where id = $id_target ";
        $result= $this->clink->query($sql);

        $sql= "update {$this->table}_copy set id_code = '$id_code' where id = $id_origen ";
        $result= $this->clink_origen->query($sql);
    }

    protected function if_exist_usuario($id_origen, $row_origen) {
        if (empty($row_origen['noIdentidad']) && empty($row_origen['nombre']))
            return null;

        $noIdentidad= $row_origen['noIdentidad'];
        $usuario= strtoupper($row_origen['usuario']);
        $nombre= strtoupper($row_origen['nombre']);

        $sql= "select * from $this->table ";
        if (!empty($noIdentidad)) {
            $sql.= "where noIdentidad = '$noIdentidad'";
        } else {
            $sql.= "where (upper(usuario) = '$usuario' and upper(nombre) = '$nombre') ";
        }
        $result= $this->clink->query($sql);
        $cant= $this->clink->num_rows($result);

        if (empty($cant)) {
            $id_target= $this->insert_target($row_origen);
            if (!empty($id_target)) {
                $id_target= is_array($id_target) ? $id_target[0] : $id_target;
                $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>null, 'id_target'=>$id_target,
                                                               'id_proceso_code'=>$row_origen['id_proceso_code']);
            }
        } else {
            $row= $this->clink->fetch_array($result);
            $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>null, 'id_target'=>$row['id'],
                                                            'id_proceso_code'=>$row['id_proceso_code']);
        }
    }

    protected function insert_target($row, $exect_sql= true) {
        $sql= "insert into $this->table (";
        $i= 0;
        $_clave= null;
        $_sql= null;
        foreach ($this->array_tables[$this->table]['name'] as $name) {
            if ($name == 'id') {
                ++$i;
                continue;
            }
            if ($this->table == "tusuarios" && $name == '_clave') {
                $_clave= $i;
                continue;
            }
            if ($i > 1 || !empty($_sql)) {
                $_sql.= ",";
            }
            $_sql.= $name;

            ++$i;
        }
        $sql.= "$_sql) values (";
        $i= 0;
        $_sql= null;
        foreach ($this->array_tables[$this->table]['type'] as $type) {
            if ($this->array_tables[$this->table]['name'][$i] == "id") {
                ++$i;
                continue;
            }
            if ($this->table == "tusuarios" && $i == $_clave) {
                ++$i;
                continue;
            }
            if ($i > 1 || !empty($_sql)) {
                $_sql.= ", ";
            }
            $value= $row[$this->array_tables[$this->table]['name'][$i]];
            $item= $this->fix_type($value, $type, $i);
            $_sql.= $item;
            ++$i;
        }
        $sql.= "$_sql); ";

        if ($exect_sql) {
            $result= $this->clink->query($sql);
            if (!$result) {
                $this->error= $this->clink->error();
                if (stristr($this->error, "duplicate") !== false) {
                    if ($this->table == "teventos") {
                        return $this->find_id_evento($row['nombre'], $row['lugar'], $row['id_proceso_code'],
                                                    $row['situs'], $row['fecha_inicio_plan'], $row['fecha_fin_plan']);
                    }
                    elseif ($this->table == "tusuarios") {
                        return $this->insert_usuario($row);
                    }
                    elseif ($this->table == "tasistencias") {
                        return $this->find_id_asistencia($row['id_evento_code'], $row['id_proceso_code'],
                                                        $row['id_usuario'], $row['nombre']);
                    } else {
                        return null;
                    }
                }
                die("\ninsert_target => \nSQL=>$sql ==== ERROR:$this->error \n");
            }
            return $this->clink->inserted_id($this->table);
        } else {
            return $sql;
        }
    }

    private function find_id_evento($nombre, $lugar, $id_proceso_code, $situs, $fecha_inicio_plan, $fecha_fin_plan) {
        $sql= "select * from $this->table where convert(nombre using utf8) = convert('$nombre' using utf8) ";
        $sql.= "and (id_proceso_code = '$id_proceso_code' or situs = '$situs') ";
        if (!empty($lugar))
            $sql.= "and convert(lugar using utf8) = convert('$lugar' using utf8) ";
        if (!empty($fecha_inicio_plan))
            $sql.= "and fecha_inicio_plan = '$fecha_inicio_plan' ";
        if (!empty($fecha_fin_plan))
            $sql.= "and fecha_fin_plan = '$fecha_fin_plan' ";

        $result= $this->clink->query($sql);
        if (!$result) {
            $this->error= $this->clink->error();
            die("\nfind_id_evento => \nSQL=>$sql ==== ERROR:$this->error \n");
        }

        $row= $this->clink->fetch_array($result);
        if (empty($row['id']) || empty($row['id_code'])) {
            die("\nfind_id_evento => \nSQL=>$sql ==== ERROR:No se encuentra el id_evento \n");
        }

        return array($row['id'], $row['id_code']);
    }

    private function insert_usuario($row) {
        $row['usuario']= "{$row['usuario']}.xxx";
        $row['nombre']= "{$row['nombre']}.xxx";

        $sql= "update tusuarios_copy set nombre= '{$row['nombre']}', usuario= '{$row['usuario']}' where id = {$row['id']}";
        $this->clink_origen->query($sql);

        return $this->insert_target($row);
    }

    private function find_id_asistencia($id_evento_code, $id_proceso_code, $id_usuario= null, $nombre= null) {
        $sql= "select * from tasistencias where id_evento_code = '$id_evento_code' and id_proceso_code = '$id_proceso_code' ";
        if (!empty($nombre))
            $sql.= "and nombre = '$nombre' ";
        if (!empty($id_usuario))
            $sql.= "and id_usuario = $id_usuario";
        $result= $this->clink->query($sql);
        if (!$result) {
            $this->error= $this->clink->error();
            die("\nfind_id_asistencia => \nSQL=>$sql ==== ERROR:$this->error \n");
        }

        $row= $this->clink->fetch_array($result);
        return array($row['id'], $row['id_code']);
    }

    protected function update_target_list() {
        global $array_primary_tables;
        global $array_secondary_tables;
        $array= array("tusuarios", "tgrupos", "ttableros");
        $array_tables_list= array_merge_overwrite($array_primary_tables, $array_secondary_tables, $array);

        foreach ($array_tables_list as $table) {
            if ($table == "tusuarios")
                continue;
            $this->table= $table;
            $this->update_target();
        }
    }

    private function update_target() {
        $i= 0;
        $j= 0;
        $sql= null;
        $num_rows= count($this->array_ids_fixed[$this->table]);

        reset($this->array_ids_fixed[$this->table]);
        foreach ($this->array_ids_fixed[$this->table] as $row) {
            $sql_origen= "select * from {$this->table}_copy ";
            if (!empty($row['id_code'])) {
                $sql_origen.= "where id_code = '{$row['id_code']}' ";
            } else {
                $sql_origen.= "where id = {$row['id']} ";
            }
            $result= $this->clink_origen->query($sql_origen);
            $row_origen= $this->clink_origen->fetch_array($result);

            ++$i;
            if (!empty($row_origen['id_code']) && substr($row_origen['id_code'], 0, 2) != $this->code_origen)
                continue;
            if (!empty($row_origen['id_proceso_code']) && $row_origen['id_proceso_code'] == $this->id_proceso_target_code)
                continue;
            if (!empty($row_origen['id_entity_code']) && $row_origen['id_entity_code'] != $this->id_proceso_origen_code)
                continue;
            ++$j;
            $sql.= $this->_update_target($row_origen, $row['id_target']);

            if ($j > _NUM_ROWS_INSERT) {
                $result= $this->clink->multi_query($sql);
                if (!$result) {
                    $this->error= $this->clink->error();
                    die("\nupdate_target => \nSQL=>$sql ==== ERROR:$this->error \n");
                }

                $sql= null;
                $j= 0;
                $r= (float)$i/$num_rows;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Actualizando en {$this->db_target} {$this->table} ... $_r%", $r);
            }
        }

        if ($sql) {
            $result= $this->clink->multi_query($sql);
            if (!$result) {
                $this->error= $this->clink->error();
                die("\nupdate_target => \nSQL=>$sql ==== ERROR:$this->error \n");
            }
        }

        unset($this->array_ids_fixed[$this->table]);

        bar_progressCSS(1, "Actualizando en {$this->db_target} {$this->table} ... 100%", 1);
    }

    private function _update_target($row, $id_target) {
        $array= array("usuario", "nombre", "noIdentidad", "nivel", "user_ldap");
        $i= 0;
        $sql= "update $this->table set ";
        foreach ($this->array_tables[$this->table]['name'] as $name) {
            if ($name == 'id') {
                ++$i;
                continue;
            }
            if ($name == "_clave") {
                ++$i;
                continue;
            }
            if ($this->table == "tusuarios" && array_search($name, $array) !== false) {
                ++$i;
                continue;
            }
            if ($i > 1)
                $sql.= ", ";
            $sql.= "$name= ".$this->fix_type($row[$name], $this->array_tables[$this->table]['type'][$i], $i);
            ++$i;
        }
        if (!empty($row['id_code'])) {
            $sql.= " where id_code = '{$row['id_code']}'; ";
        } else {
            $sql.= " where id = $id_target; ";
        }
        return $sql;
    }

    protected function fix_type($item, $type, $index) {
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;

        if (array_search($type, $SQL_booltypes) !== false && $this->array_tables[$this->table]['len'][$index] == 1) {
            $item= setNULL($item);
        }
        if (array_search($type, $SQL_timetypes) !== false) {
            $item= setNULL_str($item, false);
        }
        elseif (array_search($type, $SQL_texttypes) !== false) {
            if (!empty($item)) {
                $item= stripslashes($item);
            }
            $item= setNULL_str($item);
        }
        elseif (array_search($type, $SQL_numtypes) !== false) {
            if (is_numeric($item) && $item == 0)
                $item= setZero($item);
            else
                $item= setNULL($item);
        }
        elseif (array_search($type, $SQL_blobtypes) !== false) {
            if (is_null($item) || strlen($item) == 0)
                $item= 'NULL';
            else {
                $item= hex2bin($item);
                $item= addslashes($item);
                $item= "'$item'";
            }
        } else {
            $item= setNULL($item);
        }

        return $item;
    }
}
