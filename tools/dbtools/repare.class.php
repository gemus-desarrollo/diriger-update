<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2020
 */
class Trepare_sys extends Tbase {
    private $sql_array;
    
    public function __construct($clink, $filename= null) {
        Tbase::__construct($clink);
        $this->set_cronos();
    }

    public function read_sql($text) {
        if (strlen($text) == 0)
            return;
        
        $eol= true;
        $lines= null;
        $nlines= 0;
        $sql_lines= array();
 
        $lines= preg_split("/\\n/", $text);

        foreach ($lines as $line) {
            if (!strlen($line)) {
                ++$nlines;
                continue;
            }            
            
            $line= trim($line)." ";

            if ($eol && strstr($line, "/*") !== false) {
                if (strstr($line, '*/')) {
                    $eol = false;
                    ++$nlines;
                    continue;
                }
            }
            if (!$eol && strstr($line, "*/") !== false) {
                $eol = true;
                ++$nlines;
                continue;
            }

            $_row= substr($line,0,4);
            if (strstr($_row, "-- ") !== false) {
                ++$nlines;
                continue;
            }
            
            if (!strlen($line)) {
                ++$nlines;
                continue;
            }
            
            $sql_lines[]= array('num'=>$nlines, 'line'=>$line);
            ++$nlines;
        }       
        
        $sql= null; 
        $eol= false;
        $this->sql_array= array();
        
        foreach ($sql_lines as $array) {
            $line= $array['line'];
            $sql.= $line;

            $pos= strrpos($line, ';');
            $len= strlen(trim($line));
            if ($pos !== false && ((int)$pos + 1) == (int)$len)
                $eol= true;

            if ($eol) {
                $this->sql_array[]= $sql;
                ++$this->nrows;
                $sql = null;
                $eol = false;
            }  
        }
    }
    
    public function exect_sql() {
        $i= 0;
        foreach ($this->sql_array as $sql) {
            $sql= $this->parseSQL($sql);
            $result= $this->do_sql_show_error('execute_sql', $sql, false, 'yes', 'winlog');

            $count= 0;
            if (stristr($node->sql,'update') || stristr($node->sql,'insert') || stristr($node->sql,'delete') || stristr($node->sql,'alter'))
                $count= $this->clink->affected_rows($result);

            if (empty($this->error_system)) {
                $this->show_error("<strong>OK (affected: $count)</strong><br/><br/>");
            }    

            $r= (float)(++$i) / $this->nrows;
            $_r= $r*100; 
            $_r= number_format($_r,1);
            bar_progressCSS(0, "Ejecutando script para actualizar BD ($_r%)....", $r);                    
        }
    }
    
    public function exec_function($function) {
        bar_progressCSS(1, "$function: Iniciando", 0);
        $error= call_user_func($function, null);
        bar_progressCSS(1, "$function: terminado", 1, null, false);  
    }    
    
    private function parseSQL($sql) {
        $sql= $_SESSION['_DB_SYSTEM'] == 'mysql' ? preg_replace('/"/', '`', $sql) : preg_replace('/"/', '\\"', $sql);
        return $sql;
    }    
}
