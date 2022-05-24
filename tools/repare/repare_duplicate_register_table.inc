<?php
/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier
 * Date: 25/10/2021
 * Time: 02:16
 */

class Trepare_index extends Tbase {
    private $table_name;
    private $index_name;

    private $array_columns,
            $array_selected_columns;

    private $column_basic,   // campo que sera modificado
            $plus_text;     // texto que se le agrega al campo para modificarlo

    public $array_registers;        

    public function SetTableName($id) {
        $this->table_name= $id;
    }
    public function SetIndexName($id) {
        $this->index_name= $id;
    }
    public function SetPlusText($id) {
        $this->plus_text= $id;
    }    

    public function __construct($clink) {
        Tbase::__construct($clink);
    }

    public function get_columns() {
        $sql= "SHOW COLUMNS FROM $this->table_name";
        $result= $this->do_sql_show_error('get_columns', $sql);

        $this->array_columns= array();
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_columns[$row['Field']]= array('name'=>$row['Field'], 'type'=>$row['Type']);
        }
        return $this->array_columns;
    }

    public function list_selected() {
        $this->array_selected_colums= array();

        $cant_columns= $_POST['cant_columns'];
        $j= 0;
        for ($i= 1; $i <= $cant_columns;  $i++) {
            if (empty($_POST["icolumn_chk-$i"])) 
                continue;
            ++$j;  
            $column= $_POST["icolumn_chk-$i"];
            $this->array_selected_columns[$column]= array('name'=>$column, "length"=>$_POST["icolumn_length-$i"]);
        }

        if ($_POST["icolumn_select"]) {
            $this->column_basic= $_POST["icolumn_select"];
        } 

        return $this->array_selected_columns;
    }

    public function list_duplicate() {
        $this->list_selected();

        $sql= "SELECT id ";
        foreach ($this->array_selected_columns as $col) {
            $sql.= ", {$col['name']}";
        }
        $sql.= ", count(*) as _cant FROM $this->table_name ";
        $sql.= "GROUP BY ";
        $i= 0;
        reset($this->array_selected_columns);
        foreach ($this->array_selected_columns as $col) {
            ++$i;
            if ($i > 1)
                $sql.= ", ";
            $sql.= "{$col['name']}";
        }
        $sql.= " HAVING _cant > 1"; 
        
        $result= $this->do_sql_show_error('list_duplicate', $sql);

        $this->array_registers= array();
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_registers[$row['id']]= $row;
        }

        reset($this->array_selected_columns);

        return $this->array_registers;
    }

    public function modify_duplicate() {
        $nrecords= count($this->array_registers);
        $this->plus_text= $_POST['plus_text'];

        $i= 0;
        $sql= null;
        foreach ($this->array_registers as $row) {
            ++$i;
            $sql= "update $this->table_name set $this->column_basic = CONCAT($this->column_basic, '$this->plus_text') ";
            $sql.= "where id != {$row['id']} and (";
            $j= 0;
            reset($this->array_selected_columns);
            foreach ($this->array_selected_columns as $col) {
                ++$j;
                if ($j > 1)
                    $sql.= "and ";
                $is_string= isStringType($this->array_columns[$col['name']]['type']);
                $value= setNULL_empty_equal_sql($row[$col['name']], $is_string);
                $sql.= "{$col['name']} $value ";
            }
            $sql.= "); ";

            $result= $this->do_sql_show_error('modify_duplicate', $sql);
            if (!$result) 
                return $this->error;

            $r= (float)$i/$nrecords;
            $_r= number_format($r*100, 3);               
            bar_progressCSS(0, "Procesando registros ... $_r%", $r);                  
        }

        return null;
    }

    public function create_unique_index() {
        reset($this->array_selected_columns);

        $sql= "CREATE UNIQUE INDEX $this->index_name ON $this->table_name (";
        $i= 0;
        foreach ($this->array_selected_columns as $col) {
            ++$i;
            if ($i > 1)
                $sql.= ", ";
            $sql.= "{$col['name']}";
            $sql.= !empty($col['length']) ? "({$col['length']})" : null;    
        }
        $sql.= ")";

        $result= $this->do_sql_show_error('create_unique_index', $sql);
        return $this->error;
    } 
}