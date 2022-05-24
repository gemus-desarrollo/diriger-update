<?php
/**
 * Created by Visual Studio Code.
 * User: PhD. Geraudis Mustelier
 * Date: 11/07/2020
 * Time: 7:16
 */

class Torigen_db {
    private $error;
    public $clink;
    public $db_origen;
    public $num_tables;
    public $array_tables;
    public $array_tables_list;
    
    public function __construct($db_origen) {
        if (!empty($db_origen)) {
            $this->db_origen= $db_origen;
            $this->clink= new Tconnect($_SESSION['db_ip'], $db_origen, true);
            $this->clink= empty($this->clink->error) ? $this->clink : false;
        }    
    }
    
    public function listar_dbs() {
        global $clink_target;

        $clink_target= new Tconnect($_SESSION['db_ip'], null);
        $clink_target= empty($clink_target->error) ? $clink_target : false;

        $sql= "show databases";
        $result= $clink_target->query($sql);
        return $result;
    }    
    
    private function get_tables() {
        global $array_exept_tables;
        
        $sql= "SET FOREIGN_KEY_CHECKS=0";
        $result= $this->clink->query($sql);

        $sql= "show tables";
        $result= $this->clink->query($sql);

        $this->array_tables= array();
        $this->num_tables= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (array_search($row[0], $array_exept_tables) !== false)
                continue;
            if (stripos($row[0], "view_") !== false)
                continue;
            if (stripos($row[0], "_copy") !== false)
                continue;
            $this->array_tables_list[]= $row[0];
            $this->array_tables[$row[0]]= array('table'=>$row[0], 'name'=>null, 'type'=>null, 
                                                'len'=>null, 'if_proceso'=>false);
            ++$this->num_tables;
        }          
    }
    
    private function copy() {
        $i= 0;
        reset($this->array_tables);
        foreach ($this->array_tables as $table) {
            $sql= "drop table if exists {$table['table']}_copy";
            $result= $this->clink->query($sql);
            
            $sql= "create table {$table['table']}_copy like {$table['table']};";
            $result= $this->clink->query($sql);
            if ($result) {
                $sql= "insert into {$table['table']}_copy select * from {$table['table']}";
                $result= $this->clink->query($sql);
                
                ++$i;
                $r= (float)$i/$this->num_tables;
                $_r= number_format($r*100, 3);
                bar_progressCSS(1, "Copiando tablas {$table['table']} .... $_r%", $r);
            }    
            if (!$result) {
                $this->error= $this->clink->error();
                return false;
            }    
        }
        return true;
    }
    
    private function delete() {
        $i= 0;
        reset($this->array_tables);
        foreach ($this->array_tables as $table) {
            $sql= "drop table {$table['table']};"; 
            $result= $this->clink->query($sql);
            
            ++$i;
            $r= (float)$i/$this->num_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Eliminando tablas {$table['table']} .... $_r%", $r);
                
            if (!$result)
                return false;
        }
        return true;
    }
    
    private function get_structure() {
        $i= 0;
        reset($this->array_tables);
        
        $i= 0;
        $j= 0;
        foreach ($this->array_tables as $index => $table) {
            $name= array();
            $type= array();
            $len= array();
            
            $i= 0;
            $if_proceso= false;
            $fields= $this->clink->fields($table['table'], false);
            foreach ($fields as $row) {
                if (!$if_proceso && $row['Field'] == "id_proceso")
                    $if_proceso= true;
                
                $name[$i]= $row['Field'];
                $type[$i]= strtoupper($row['Type']);
                $len[$i]= $row['char_length'];
                ++$i;
            }
            
            $this->array_tables[$index]['if_proceso']= $if_proceso;
            $this->array_tables[$index]['name']= $name;
            $this->array_tables[$index]['type']= $type;
            $this->array_tables[$index]['len']= $len;
            
            ++$j;
            $r= (float)$j/$this->num_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Leyendo estructuras de tablas {$table['table']} .... $_r%", $r);            
        }   
    }
    
    private function delete_copies() {
        $i= 0;
        $nums_tables= count($this->array_tables);
        reset($this->array_tables);
        foreach ($this->array_tables as $table) {
            $sql= "drop table {$table['table']}_copy;"; 
            $result= $this->clink->query($sql);
            
            ++$i;
            $r= (float)$i/$nums_tables;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "Eliminando tablas {$table['table']}_copy .... $_r%", $r);
                
            if (!$result)
                return false;
        }
        return true;
    }    
    
    public function prepare() {
        $result= false;
        
        bar_progressCSS(0, "Leyendo las tablas de la db .... 5%", 0.05); 
        $this->get_tables();
        
        bar_progressCSS(0, "Leyendo estructuras de las tablas de la db .... 10%", 0.1);
        $this->get_structure();

        bar_progressCSS(0, "Copiando las tablas de la db .... 20%", 0.2);
        $result= $this->copy();

        if ($result) {
            bar_progressCSS(0, "Eliminando las tablas originales de la db .... 30%", 0.3);
            $result= $this->delete();
        }

        return $result;
    }
    
    public function finish() {
        bar_progressCSS(0, "Eliminando las tablas originales de la db .... 100%", 1);
        $result= $this->delete_copies();    
    }
}
