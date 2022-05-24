<?php
/**
 * Created by Visual Studio Code.
 * User: PhD. Geraudis Mustelier
 * Date: 11/07/2020
 * Time: 7:16
 */

include_once "../../../php/class/code.class.php";
include_once "target_db_base.class.php";

class Tdb_target extends Tdb_target_base {

    public function if_exist_nombre($id_origen, $nombre, $id_proceso_code, $row_origen) {
        if (($this->table == "tpoliticas" && ($row_origen['id'] >= 1 && $row_origen['id'] <= 409))
            || ($this->table == "torganismos" && ($row_origen['id'] >= 1 && $row_origen['id'] <= 25))
            || ($this->table == "tunidades" && ($row_origen['id'] >= 1 && $row_origen['id'] <= 23))
            || ($this->table == "tlistas" && ($row_origen['id'] >= 1 && $row_origen['id'] <= 3)) )
            {
            $sql= "select * from $this->table where id = {$row_origen['id']} ";
            $result= $this->clink->query($sql);

        } else {

            $sql= "select * from $this->table where convert(lower(nombre) using utf8) = convert('$nombre' using utf8) ";
            $result= $this->clink->query($sql);
            if (!$result) {
                $this->error= $this->clink->error();
                die("\nupdate_table_origen => \nSQL=>$sql ==== ERROR:$this->error \n");
            }
        }

        $num_rows= $this->clink->num_rows($result);
        if ($num_rows) {
            $row= $this->clink->fetch_array($result);
            $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>$row['id_code'], 'id_target'=>$row['id'],
                                                            'id_proceso'=>$row['id_proceso']);
            $id_target= $row['id'];
            $id_code= $row['id_code'];

        } else {

            $id_target= $this->insert_target($row_origen, true);
            if (!empty($id_target)) {
                $id_target= is_array($id_target) ? $id_target[0] : $id_target;
                $id_code= build_code($id_target, $this->code_target);
                $this->array_ids_fixed[$this->table][]= array('id'=>$id_origen, 'id_code'=>$id_code, 'id_target'=>$id_target,
                                                                'id_proceso_code'=>$id_proceso_code);
            }
        }

        if (!empty($id_target)) {
            $this->update_id_code($id_origen, $id_target, $id_code);
            $this->update_table_origen_secondary($id_origen, $id_target, $id_code);
        }
        return array($id_target, $id_code);
    }

    private function update_table_origen_secondary($id_origen, $id, $id_code) {
        $array_tabla_keys["torganismos"]= array("id_organismo");
        $array_tabla_keys["tpoliticas"]= array("id_politica");
        $array_tabla_keys["tunidades"]= array("id_unidad");
        $array_tabla_keys["tlistas"]= array("id_lista");
        $array_tabla_keys["tlista_requisitos"]= array("id_requisito");

        $sql= null;
        $i= 0;
        $j= 0;
        $field= $array_tabla_keys[$this->table][0];
        reset($this->array_tables);
        foreach ($this->array_tables as $table => $array) {
            if (array_search($field, $array['name']) === false)
                continue;

            $sql.= "update {$table}_copy set $field= $id, {$field}_code = '$id_code' where $field = $id_origen; ";
            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $result= $this->clink_origen->multi_query($sql);
                if (!$result) {
                    $this->error= $this->clink_origen->error();
                    die("\nupdate_table_origen_secondary => \nSQL=>$sql ==== ERROR:$this->error \n");
                }
            }
        }
        if ($sql) {
            $result= $this->clink_origen->multi_query($sql);
            if (!$result) {
                $this->error= $this->clink_origen->error();
                die("\nupdate_table_origen_secondary => \nSQL=>$sql ==== ERROR:$this->error \n");
            }
        }
    }

    protected function update_db_origen($id_target, $id_code, $id_origen) {
        $array_tabla_keys["tarchivos"]= array("id_archivo");
        $array_tabla_keys["tasistencias"]= array("id_asistencia");
        $array_tabla_keys["tauditorias"]= array("id_auditoria");
        $array_tabla_keys["tdebates"]= array("id_debate");
        $array_tabla_keys["tdocumentos"]= array("id_documento");
        $array_tabla_keys["tescenario"]= array("id_escenario");
        $array_tabla_keys["teventos"]= array("id_evento", "id_evento_accords");
        $array_tabla_keys["tindicadores"]= array("id_indicador");
        $array_tabla_keys["tinductores"]= array("id_inductor");
        $array_tabla_keys["tlista_requisitos"]= array("id_requisito");
        $array_tabla_keys["tlistas"]= array("id_lista");
        $array_tabla_keys["tnotas_causas"]= array("id_causa");
        $array_tabla_keys["tnotas"]= array("id_nota");
        $array_tabla_keys["tobjetivos"]= array("id_objetivo");
        $array_tabla_keys["tperspectivas"]= array("id_perspectiva");
        $array_tabla_keys["tpersonas"]= array("id_persona");
        $array_tabla_keys["tplanes"]= array("id_plan");
        $array_tabla_keys["tprocesos"]= array("id_proceso", "id_proceso_jefe", "id_entity");
        $array_tabla_keys["tprogramas"]= array("id_programa");
        $array_tabla_keys["tproyectos"]= array("id_proyecto");
        $array_tabla_keys["triesgos"]= array("id_riesgo");
        $array_tabla_keys["ttareas"]= array("id_tarea", "id_depend");
        $array_tabla_keys["ttematicas"]= array("id_tematica");

        $array_tabla_keys["ttipo_auditorias"]= array("id_tipo_auditoria");
        $array_tabla_keys["ttipo_eventos"]= array("id_tipo_evento", "id_subcapitulo");
        $array_tabla_keys["ttipo_listas"]= array("id_tipo_lista");
        $array_tabla_keys["ttipo_reuniones"]= array("id_tipo_reunion");
        $array_tabla_keys["tunidades"]= array("id_unidad");

        $array_tabla_keys["tusuarios"]= array("id_usuario", "id_responsable", "id_usuario_plan", "id_usuario_real", "id_responsable_2",
                                            "id_responsable_eval", "id_responsable_aprb", "id_responsable_eval", "id_responsable_auto_eval");
        $array_tabla_keys["tunidades"]= array("id_unidad");
        $array_tabla_keys["torganismos"]= array("id_organismo");
        $array_tabla_keys["tgrupos"]= array("id_grupo");
        $array_tabla_keys["ttableros"]= array("id_tablero");
        $array_tabla_keys["tpoliticas"]= array("id_politica");

        reset($array_tabla_keys[$this->table]);
        foreach ($array_tabla_keys[$this->table] as $field) {
            if (!empty($field)) {
                $this->update_table_origen ($id_target, $id_code, $id_origen, $field);
            }
        }
    }

    private function update_table_origen($id_target, $id_code, $id_origen, $field) {
        $use_code= true;
        $array_tables= array("tusuarios", "tgrupos", "tableros");
        $array_fields= array("id_grupo", "id_tablero", "id_usuario");
        if (array_search($this->table, $array_tables) !== false || array_search($field, $array_fields) !== false) {
            $use_code= false;
        }

        $sql= null;
        $j= 0;
        reset($this->array_tables);
        foreach ($this->array_tables as $table) {
            if (array_search($field, $table['name']) !== false) {
                ++$j;
                $this->_update_table_origen($table['table'], $id_target, $id_code, $id_origen, $field, $use_code);
            }
        }
    }

    private function _update_table_origen($table, $id_target, $id_code, $id_origen, $field,
                                        $use_code= true) {
        $id_field_code= $use_code ? "{$field}_code" : $field;

        $sql= "update {$table}_copy set $field = $id_target ";
        if (!empty($id_code) && $use_code) {
            $sql.= ", $id_field_code = '$id_code' ";
        }
        $sql.= "where $field = $id_origen; ";

        $result= $this->clink_origen->query($sql);
        if (!$result) {
            $this->error= $this->clink_origen->error();
            if (stristr($this->error, "duplicate") !== false)
                return;
            die("\_update_table_origen => \nSQL=>$sql ==== ERROR:$this->error \n");
        }
    }

    protected function fix_config_synchro_prs() {
        $sql= "delete from _config_synchro where id_proceso_code = '$this->id_proceso_origen_code'; ";
        $result= $this->clink->query($sql);

        $sql= "update tprocesos set conectado = "._LAN.", if_entity= true, id_entity= null, ";
        $sql.= "id_entity_code= null, cronos_syn= null where id_code = '$this->id_proceso_origen_code'";
        $result= $this->clink->query($sql);
    }
}
