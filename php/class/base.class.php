<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

require_once _ROOT_DIRIGER_DIR."php/class/base_base.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

if (!defined('_LIBRARIES_DIRIGER')) {
    include_once _ROOT_DIRIGER_DIR."php/class/library.php";
    include_once _ROOT_DIRIGER_DIR."php/class/library_string.php";
    include_once _ROOT_DIRIGER_DIR."php/class/library_style.php";
}

require_once _ROOT_DIRIGER_DIR."php/class/errorLibrary.inc.php";

if (defined(_VERSION_DIRIGER))
    $_SESSION['version']= _VERSION_DIRIGER;

class Tbase extends Tbase_core {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_core::__construct($this->clink);
    }

    protected function _this_copy() {
        $class= get_class($this);
        $class= "new $class;";

        $obj= eval($class);
        $tmp= serialize($this);
        $obj= unserialize($tmp);

        return $obj;
    }

    public function GetOrigenData($item= null, $origen_data= null) {
        $origen_data= !is_null($origen_data) ? $origen_data : $this->origen_data;
        if (is_null($item))
            return $origen_data;

        $array= explode("||~", $origen_data);

        foreach ($array as $data) {
            $origen_data= stristr($data, '^'.$item.':');
            if ($origen_data != false) {
                $len= strlen($item) + 2;
                $origen_data= trim(substr($data, $len));
                return $origen_data;
        }   }

        return null;
    }

    public function set_use_copy_tusuarios($id= false) {
        $this->use_copy_tusuarios= $id;
        if ($this->use_copy_tusuarios && !$this->if_copy_tusuarios) 
            $this->get_if_copy_tusuarios_exist();
    }

    protected function get_if_copy_tusuarios_exist() {
        $result= $this->clink ? $this->clink->if_table_exist("_ctusuarios") : null;
        $this->if_copy_tusuarios= $result ? true : false;

        $this->error= null;
        return $this->if_copy_tusuarios;
    }

    public function set_use_copy_tprocesos($id= false) {
        $this->use_copy_tprocesos= $id;
        if ($this->use_copy_tprocesos && !$this->if_copy_tprocesos)
            $this->get_if_copy_tprocesos_exist();

        return $this->if_copy_tprocesos;
    }

    protected function get_if_copy_tprocesos_exist() {
        $result= $this->clink ? $this->clink->if_table_exist("_ctprocesos") : null;
        $this->if_copy_tprocesos= $result ? true : false;
        $this->error= null;
        return $this->if_copy_tprocesos;
    }

    protected $copyto;

    public function get_ifcopyto($year, $copyto= null) {
        $copyto= !empty($copyto) ? $copyto : $this->copyto;
        $year= "$year(";
        $pos= strpos($copyto, (string)$year);

        if ($pos === false)
            return null;
        return substr($copyto, $pos+5, 12);
    }

    public $className;
    public $pfile;
    public $filename;
    public $nfileline;

    public $divout;
    public $fileout;

    public function show_error($html, $divout= null, $fileout= null, $write= true) {
        $divout= !empty($divout) ? $divout : $this->divout;
        $fileout= !empty($fileout) ? $fileout : $this->fileout;
        $write= !is_null($write) ? $write : true;

        ++$this->nfileline;

        if ($write) {
            if ($divout) {
                usleep(100);
            ?>
            <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                <script type='text/javascript'>
            <?php } ?>
                   writeLog('<?=date('Y-m-d H:i:s')?>', "<?=$html?>", "<?=$divout?>");
            <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                </script>
            <?php } ?>
            <?php
                flush();
                ob_flush();
                ob_end_flush();
                ob_start();
            } else {
                if ($_SESSION['debug'] == 'yes' && is_null($divout))
                    echo $html;
            }
        }

        if ($fileout) {
            $html= "<a name=$this->nfileline></a>".$html."<br/>\t\n";
            fwrite($this->pfile, $html);
        }
    }

    private function _bloc_validate() {
        if (is_null($_SESSION['output_signal']) || (!is_null($_SESSION['output_signal']) && $_SESSION['output_signal'] != 'shell')) {
            if (!isset($_SESSION['_ctime']) || empty($_SESSION['_ctime'])) { ?>
                <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                    <script type='text/javascript'>
                <?php } ?>
                        parent.location.href= '<?=_SERVER_DIRIGER?>index.php';
                <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                    </script>
                <?php } ?>
        <?php
        }   }
    }

    public function do_sql_show_error($function, $sql, $stop_by_error= true, $debug= null, $divout= null, $fileout= null, $write= true) {
        global $stop_by_die;
        
        if ($stop_by_die)
            return null;

        $divout= !empty($divout) ? $divout : $this->divout;
        $fileout= !empty($fileout) ? $fileout : $this->fileout;
        if (is_null($debug))
            $debug= $_SESSION['debug'];

        $this->_bloc_validate();

        $this->error= null;
        $this->cant= 0;
        $this->error_system= null;
        if (is_null($this->className))
            $this->className= get_class($this);

  //    $this->clink->free_result();
        $result= @$this->clink->query($sql);

        if (!$result) {
            $date= date('Y-m-d H:i:s');
            $this->error= $this->clink->error();

            $this->error_system= "CLASS: $this->className; DATE: $date FUNCTION: $function <br/> SQL: ".htmlspecialchars($sql);
            $this->error_system.= "<br/>NO: ".$this->clink->errno()."  ERROR: ".htmlspecialchars($this->error)."<br/>";
        //    $this->error= $stop_by_error ? $this->error : null;
            $this->error= errorLibrary($this->error, $function, $this->className);

            if ((!empty($debug) && $debug !== 'no') && ($divout || $fileout))
                $this->show_error("<div class='alert alert-danger'>{$this->error_system}</div", $divout, $fileout);
            if ((!empty($debug) && $debug !== 'no') && $stop_by_error) {
                if ($debug !== 'update')
                    $stop_by_die= true;
            }

        } else {
            $_sql= substr(strtolower($sql),0,7);
            $this->cant= stripos($_sql, 'select') !== false ? $this->clink->num_rows($result) : $this->clink->affected_rows($result);
            /* TEST */
            if ($debug === 'yes' && (!empty($divout) || !empty($fileout))) {
                $this->show_error("CLASS: $this->className ==>> DATE: $date  FUNCTION: $function <br> SQL: ".htmlspecialchars($sql), $divout, $fileout, $write);
            }
        }

        return $result;
    }

    public function do_multi_sql_show_error($function, $sql, $stop_by_error= true, $debug= null, $divout= null, $fileout= null, $write= true) {
        global $stop_by_die;

        if ($stop_by_die)
            return null;
        if (empty($sql))
            return null;

        $divout= !empty($divout) ? $divout : $this->divout;
        $fileout= !empty($fileout) ? $fileout : $this->fileout;
        $write= !is_null($write) ? $write : true;
        if (is_null($debug))
            $debug= $_SESSION['debug'];

        $this->_bloc_validate();

        $this->error= null;
        $this->cant= 0;
        $this->error_system= null;
        if (is_null($this->className))
            $this->className= get_class($this);

        // $this->clink->free_result();
        $result= @$this->clink->multi_query($sql);

        if (!$result) {
            $date= date('Y-m-d H:i:s');
            $this->error= $this->clink->error;
            $this->error_system= "CLASS: $this->className; DATE: $date FUNCTION: $function <br/> SQL: ".htmlspecialchars($sql);
            $this->error_system.= "<br/> ERROR: ".htmlspecialchars($this->error)."<br/>";

            $this->error= $stop_by_error ? $this->error : null;
            $this->error= errorLibrary($this->error, $function, $this->className);

            if (!empty($debug) && $debug !== 'no') {
                $this->show_error("<div class='alert alert-danger'>{$this->error_system}</div>", $divout, $fileout);
                if ($stop_by_error) {
                    $stop_by_die= true;
                }
            }

        } else {
            /* TEST */
            if (!empty($debug) && $debug !== 'no')
                $this->show_error("CLASS: $this->className ==>> DATE: $date  FUNCTION: $function<br/> SQL: ".htmlspecialchars($sql), $divout, $fileout, $write);
        }

        return $result;
    }

    public function debug_time($item) {
        if (is_null($_SESSION['trace_time']) || $_SESSION['trace_time'] == 'no')
            return;
        $time= date('Y-m-d H:i:s');
        $ellapsed= null;

        if (!is_null($this->time_hit[$item])) {
            $result= date_diff(date_create($this->time_hit[$item]), date_create($time));
            $ellapsed= $result->format('%i m : %s sec');
        } else {
            if (is_null($this->time_hit[$item]))
                $this->time_hit[$item]= $time;
        }

        $this->show_error("<br><br> $item ===> ----- tiempo registrado: $time --- tiempo trancurrido : $ellapsed -------<br>");
    }

    protected function _set_user($sql) {
        $this->cant= 0;
        $result= $this->do_sql_show_error('_set_user', $sql, false);
        if (!$result) {
            if (stristr($this->error, "Duplicate")) {
                $this->error= null;
                $this->cant= 0;
            }
        } else
            $this->cant= $this->clink->affected_rows();
        return $this->error;
    }

    protected function _set_id_code($table, $situs= null) {
        $sql= "select id, situs from $table where id_code is null";
        $result= $this->do_sql_show_error('set_id_code', $sql);

        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $_situs= !is_null($situs) ? $situs : $row['situs'];
            $id_code= build_code($row['id'], $_situs);
            $sql.= "update $table set id_code = '$id_code' where id = {$row['id']}; ";
        } 

        if (!empty($sql))
            $this->do_multi_sql_show_error('set_id_code', $sql);                
    } 
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";

?>