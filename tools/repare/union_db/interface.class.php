<?php
/**
 * Created by Visual Studio Code.
 * User: PhD. Geraudis Mustelier
 * Date: 11/07/2020
 * Time: 7:16
 */

include_once "../../../php/config.inc.php";
include_once "origen_db.class.php";
include_once "target_db_base.class.php";
include_once "target_db.class.php";


$array_exept_tables= array("_config", "_config_synchro", "talerts", "tcola", "tsystem",
                            "tsincronizacion", "texcel", "texcel_celdas", "texcel_plantillas");

$array_secondary_tables= array("torganismos", "tpoliticas", "tunidades", "tlistas",
                                "tlista_requisitos");

$array_critical_tables= array("tusuarios", "tdeletes", "tseries", "tgrupos", "ttableros");

$array_primary_tables= array("tprocesos", "tarchivos", "tasistencias", "tauditorias",
        "tdebates", "tdocumentos", "tescenarios", "teventos", "tindicadores", "tinductores",
        "tnotas",  "tobjetivos", "tpersonas", "tperspectivas", "tplanes", "tprogramas",
        "tproyectos", "triesgos", "ttareas", "ttematicas", "ttipo_auditorias", "ttipo_eventos",
        "ttipo_listas", "ttipo_reuniones", "tnotas_causas");



class Tinterface_target extends Tdb_target {

    public function __construct($db_target) {
        $this->db_target= $db_target;

        $this->clink= new Tconnect($_SESSION['db_ip'], $db_target, true);
        $this->clink= empty($this->clink->error) ? $this->clink : false;

        $sql= "SET FOREIGN_KEY_CHECKS=0";
        $result= $this->clink->query($sql);

        $this->array_ids_fixed= array();
    }

    public function __destruct () {
        $sql= "SET FOREIGN_KEY_CHECKS=1";
        $result= $this->clink->query($sql);
    }

    public function do_list_table() {
        global $array_primary_tables;
        global $array_critical_tables;
        global $array_secondary_tables;

        $this->num_tables= count($this->array_tables_list);

        memory_usage("do_list_table");

        $i= $this->_do_list_table_primary();

        memory_usage("_do_list_table_primary");

        $i= $this->_do_list_table_secondary($i);

        memory_usage("_do_list_table_secondary");

        $i= $this->_do_list_table_critical($i);
        bar_progressCSS(0, "Insertando registros tablas principales.... 80%", 0.8);

        $this->update_target_list();

        memory_usage("update_target_list");

        reset($this->array_tables);
        foreach ($this->array_tables as $table) {
            $this->table= $table['table'];

            if (array_search($table['table'], $array_primary_tables) !== false
                || array_search($table['table'], $array_critical_tables) !== false
                || array_search($table['table'], $array_secondary_tables) !== false)
                continue;
            $this->do_relations_tables();
        }

        bar_progressCSS(0, "Insertando registros tablas principales.... 100%", 1);

        $this->fix_config_synchro_prs();
    }

    private function _do_list_table_primary() {
        global $array_primary_tables;

        $i= 0;
        foreach ($array_primary_tables as $table) {
            $this->table= $table;

            ++$i;
            $r= (float)$i/$this->num_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(0, "Tablas procesadas {$this->table} ... $_r%", $r);

            $this->do_primay_table();

            memory_usage("_do_list_table_primary ($this->table)");
        }
        bar_progressCSS(0, "Insertando registros tablas primarias.... 60%", 0.6);
        return $i;
    }

    private function do_primay_table() {
        $sql= "select * from {$this->table}_copy";
        $result= $this->clink_origen->query($sql);
        $num_rows= $this->clink_origen->num_rows($result);
        if (empty($num_rows))
            return null;

        $this->prepare_array_id_code();

        $i= 0;
        $j= 0;
        while ($row= $this->clink_origen->fetch_array($result)) {
            $this->if_exist_id_code($row['id'], $row['id_code'], $row);

            ++$i;
            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $r= (float)$i/$num_rows;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Insertando en $this->db_target {$this->table} .... $_r%", $r);
            }
        }
        bar_progressCSS(1, "Insertando en $this->db_target {$this->table} .... 100%", 1);

        $num_rows= count($this->array_ids_fixed[$this->table]);
        $i= 0;
        $j= 0;
        foreach ($this->array_ids_fixed[$this->table] as $array) {
            $this->update_db_origen ($array['id_target'], $array['id_code'], $array['id']);

            ++$i;
            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $r= (float)$i/$num_rows;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Actualizando en $this->db_origen {$this->table} .... $_r%", $r);
            }
        }
        bar_progressCSS(1, "Actualizando en $this->db_origen {$this->table} .... 100%", 1);
    }


    private function _do_list_table_secondary($i) {
        global $array_secondary_tables;

        foreach ($array_secondary_tables as $table) {
            $this->table= $table;

            ++$i;
            $r= (float)$i/$this->num_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(0, "Tablas procesadas {$this->table} ... $_r%", $r);

            $this->do_secondary_tables();

            memory_usage("_do_list_table_secondary ($this->table) ");
        }
        bar_progressCSS(0, "Insertando registros tablas secundarias.... 70%", 0.7);
        return $i;
    }

    private function do_secondary_tables() {
        $sql= "select * from {$this->table}_copy";
        $result= $this->clink_origen->query($sql);
        $num_rows= $this->clink_origen->num_rows($result);
        if (empty($num_rows))
            return null;

        $i= 0;
        $j= 0;
        while ($row= $this->clink_origen->fetch_array($result)) {
            $this->if_exist_nombre($row['id'], strtolower($row['nombre']), $row['id_proceso_code'], $row);
            ++$i;
            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $r= (float)$i/$num_rows;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Insertando en $this->db_target {$this->table} .... $_r%", $r);
            }
        }
    }


    private function _do_list_table_critical($i) {
        global $array_critical_tables;

        $array_tables= array("tgrupos", "ttableros");
        foreach ($array_critical_tables as $table) {
            $this->table= $table;

            ++$i;
            $r= (float)$i/$this->num_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(0, "Tablas procesadas {$this->table} ... $_r%", $r);

            if (array_search($table, $array_tables) !== false) {
                $this->do_critical_tables();
            }
            if ($table == "tusuarios") {
                $this->do_tusuarios_table();
            }
            if ($table == "tseries") {
                $this->do_tseries_table();
            }
            if ($table == "tdeletes") {
                $this->do_tdeletes_table();
            }

            memory_usage("_do_list_table_critical ($this->table) ");
        }
    }

    private function do_critical_tables() {
        $sql= "select * from {$this->table}_copy";
        $result= $this->clink_origen->query($sql);
        $nums_rows= $this->clink_origen->num_rows($result);

        $i= 0;
        while ($row= $this->clink_origen->fetch_array($result)) {
            $id_target= $this->insert_target($row);
            if (!empty($id_target)) {
                $id_target= is_array($id_target) ? $id_target[0] : $id_target;
                $id_code= is_array($id_target) ? $id_target[1] : $row['id_code'];
                $this->array_ids_fixed[$this->table][]= array('id'=>$row['id'], 'id_code'=>$id_code, 'id_target'=>$id_target,
                                                            'id_proceso_code'=>null);
            }
            ++$i;
            $r= (float)$i/$num_rows;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Registros procesados {$this->table} ... $_r%", $r);
        }

        foreach ($this->array_ids_fixed[$this->table] as $array) {
            $this->update_db_origen ($array['id_target'], $array['id_code'], $array['id']);
        }
    }

    private function do_tusuarios_table() {
        $sql= "select * from tusuarios_copy";
        $result= $this->clink_origen->query($sql);
        $nums_rows= $this->clink_origen->num_rows($result);

        $i= 0;
        while ($row= $this->clink_origen->fetch_array($result)) {
            if ($row['id'] == _USER_SYSTEM)
                continue;
            $this->if_exist_usuario($row['id'], $row);

            ++$i;
            $r= (float)$i/$num_rows;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Registros procesados {$this->table} ... $_r%", $r);
        }

       foreach ($this->array_ids_fixed[$this->table] as $array) {
            $this->update_db_origen($array['id_target'], null, $array['id']);
        }
    }

    private function do_tdeletes_table() {
        $j= 0;
        $sql= null;
        foreach ($this->array_ids_fixed["tusuarios"] as $array) {
            $sql= "update tdeletes set valor1= {$array['id_target']} ";
            $sql.= "where campo1 = 'id_usuario' and valor1= {$array['id']}; ";
            $sql.= "update tdeletes set valor2= {$array['id_target']} ";
            $sql.= "where campo2 = 'id_usuario' and valor2= {$array['id']}; ";

            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $this->clink_origen->multi_query($sql);
                $sql= null;
                $j= 0;
            }
        }
        if ($sql)
            $this->clink_origen->multi_query($sql);

        $sql= "select * from tdeletes_copy";
        $result= $this->clink_origen->query($sql);
        $nums_rows= $this->clink_origen->num_rows($result);

        $i= 0;
        while ($row= $this->clink_origen->fetch_array($result)) {
            ++$i;
            if (empty($row['tabla']))
                continue;
            if (empty($row['valor1']))
                continue;
            $this->insert_target($row);

            $r= (float)$i/$num_rows;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Registros procesados {$this->table} ... $_r%", $r);
        }
    }

    private function do_tseries_table() {
        $sql= "select * from {$this->table}_copy";
        $result= $this->clink_origen->query($sql);

        while ($row= $this->clink_origen->fetch_array($result)) {
            $this->insert_target($row);
        }
    }


    private function do_relations_tables() {
        $sql= "select * from {$this->table}_copy";
        $result= $this->clink_origen->query($sql);
        $nums_rows= $this->clink_origen->num_rows($result);

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink_origen->fetch_array($result)) {
            if ($row['situs'] == $this->code_target)
                continue;
            $this->insert_target($row);

            ++$i;
            ++$j;
            if ($j > _NUM_ROWS_INSERT) {
                $j= 0;
                $r= (float)$i/$num_rows;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Registros procesados {$this->table} ... $_r%", $r);
            }
        }
        bar_progressCSS(1, "Registros procesados {$this->table} ... 100%", 1);
    }

}

