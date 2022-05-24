<?php
/**
 * Description of deleteExport
 *
 * @author mustelier
 */
include_once "baseLote.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/DBServer.class.php";

include_once "escenario.ext.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/usuario.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/evento.class.php";

include_once "baseExport.class.php";

class TdeleteExport extends TbaseExport {
    public function __construct($clink= null) {
        TbaseLote::__construct($clink);
        $this->dblink= $clink;
    }    
    
    public function _tdeletes() {  
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_tdeletes']) ? $last_time_tables['_tdeletes'] : null;         
        
        $restrict_tables= "('tsubordinados', 'tgrupos', 'tusuario_grupos', 'tarchivos', 'tarchivo_personas', ";
        $restrict_tables.= "'tpersonas', 'tindicador_tableros', 'torganismos', 'tprocesos', 'tproceso_usuarios', ";
        $restrict_tables.= "'ttableros', 'tusuario_tableros', 'tunidades', 'tdocumentos', 'tdebates', 'tlistas')";
        
        $sql.= "select distinct tdeletes.* from tdeletes where 1 ";
        if (!empty($cronos_cut)) 
            $sql.= "and cronos >= '$cronos_cut' ";
        elseif (!empty($this->date_cutoff)) 
            $sql.= "and cronos >= '$this->date_cutoff' ";
        $sql.= "and situs = '$this->origen_code' and tabla not in $restrict_tables ";
        $sql.= "order by cronos asc ";
        
        $result= $this->db_sql_show_error('export_tdeletes', $sql);
        $nums= $this->db_cant;
       
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            if ((!empty($row['campo1']) && is_null($row['valor1'])) || $row['campo1'] == 'id_grupo')
                continue;
            if ((!empty($row['campo2']) && is_null($row['valor2'])) || $row['campo2'] == 'id_grupo')
                continue;
            if ((!empty($row['campo3']) && is_null($row['valor3'])) || $row['campo3'] == 'id_grupo')
                continue; 
                
            $sql.= "insert into _tdeletes ";
            $sql.= $this->if_mysql ? "() " : ""; 
            $sql.= "select * from tdeletes where id = {$row['id']}; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("_tdeletes", $sql);
                $i= 0;
                $sql= null;
             
                $r= (float)($j) / $nums;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "_tdeletes ..... ", $r);                 
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("_tdeletes", $sql); 

        $this->update_id_usuario("_tdeletes", 'id_responsable');
        $this->fix_export();
        
        return $j;
    }
    
    private function fix_field($i, $row, $array_usuarios) {
        $array_field= array("id_usuario", "id_responsable");
        $ref= null;
        $id= null;

        $campo= "campo{$i}";
        $valor= "valor{$i}";
        $id= $row[$valor];

        $ref= array_search($row[$campo], $array_field);
        $ref= $ref !== false ? $array_field[$ref] : null;
        
        if (is_null($ref)) 
            return null; 
        
        $noIdentidad= !empty($array_usuarios[$id]) ? $array_usuarios[$id] : null;
        if (is_null($noIdentidad))
            return null;
        $noIdentidad= setNULL_str($noIdentidad);
  
        $sql= "update _tdeletes set $valor = $noIdentidad where $valor = convert($id, char) ";
        $sql.= "and $campo = '$ref' and id = {$row['id']}; ";
        return $sql;
    }
    
    private function fix_export() {
        $max_register_block_db_input= $_SESSION["_max_register_block_db_input"]/3;
        
        $sql= "select * from tusuarios";
        $result= $this->db_sql_show_error('export_tdeletes', $sql);
        while ($row= $this->dblink->fetch_array($result))
            $array_usuarios[$row['id']]= $row['noIdentidad'];

        $sql= "select * from _tdeletes ";
        $result= $this->db_sql_show_error('fix_export', $sql);
        $nums= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            if (empty($row['valor1'])) 
                continue;
            
            ++$j;
            if (!empty($row['campo1'])) {
                ++$i;
                $sql.= $this->fix_field(1, $row, $array_usuarios);
            }    
            if (!empty($row['campo2'])) {
                ++$i;
                $sql.= $this->fix_field(2, $row, $array_usuarios);
            }    
            if (!empty($row['campo3'])) {
                ++$i;
                $sql.= $this->fix_field(3, $row, $array_usuarios);
            }
            
            if ($i >= $max_register_block_db_input) {
                $this->db_multi_sql_show_error("fix_export", $sql);
                $i= 0;
                $sql= null;
             
                $r= (float)($j) / $nums;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "fix_export ..... ", $r);                 
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("fix_export", $sql);
        
        bar_progressCSS(2, "fix_export ..... ", 1);  
    }
    
}
