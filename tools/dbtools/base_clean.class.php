<?php
/**
 * @author PhD. Geraudis Mustelier Portuondo
 * @copyright 2013
 */

include_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/time.class.php";
include_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";


class Tbase_clean extends Tfile {
    public $nrows;
    public $irows;
    public $nums_rows;
    public $last_purge_time;
    protected $beginscript;
    public $reg_fecha;
    public $go_exec_funct;
    public $go_exec_sql;
    public $verbose;

    protected $max_steep;
    protected $steep;
    protected $max_multi_query;

    protected $id_system;
    private $init_time_system;

    public $year_init,
            $year_end;

    public function __construct($clink) {
        Tfile::__construct($clink);
        $this->set_cronos();

        $this->clink = $clink;
        $this->go_exec_funct = true;
        $this->go_exec_sql = true;
        $this->beginscript = null;

        $this->max_multi_query = 5000;
    }

    protected function writeLog($line) {
        if ($_SESSION['output_signal'] == 'shell' || $_SESSION['execfromshell'] == 'shell') {
            echo "\n$line";
            return;
        }

        $line = nl2br($line);
        $stream = preg_split('/<br \/>/', $line);

        foreach ($stream as $line) {
            $line = trim($line);
            $line = addslashes($line);
            $line = $line . "<br />";
            usleep(100);
            ?>

            <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                <script type='text/javascript'>
            <?php } ?>
                $('#winlog').append('<?= $line ?>');
                goend();
            <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
                </script>
            <?php } ?>

            <?php
            flush();
            ob_flush();
        }
    }

    private function close_system_action($id) {
        $sql= "update tsystem set fin= '$this->cronos', observacion= 'forzado el cierre' ";
        $sql.= "where id = $id";
        $result= $this->do_sql_show_error('close_action_system',$sql);
    }

    public function if_occuped_system() {
        global $config;

        $sql= "select * from tsystem order by id desc limit 1";
        $result= $this->clink->query($sql);
        $row= $this->clink->fetch_array($result);

        $occupied= false;
        $date= date_create(date('Y-m-d H:i:s'));
        $this->action= $row['action'];
        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];

        $ellapsed= s_datediff('h', date_create($row['cronos']), $date);
        if (empty($this->inicio)) {
            if ($ellapsed < $config->maxexectime)
                $occupied= true;
            else {
                $this->close_system_action ($row['id']);
            }
        }

        if (!empty($this->inicio) && empty($this->fin)) {
            $ellapsed= s_datediff('h', date_create($this->inicio), $date);

            if ($ellapsed < $config->maxexectime)
                $occupied= true;
            else {
                if (($ellapsed >= $config->maxexectime && ($this->action != 'purge' && stripos($this->action, 'exec_functions') === false))
                    || ($ellapsed >= 5*$config->maxexectime && $this->action == 'purge')
                    || ($ellapsed >= 10*$config->maxexectime && stripos($this->action, 'exec_functions') !== false)) {

                    $this->close_system_action ($row['id']);
                }
            }
        }

        $action= strtoupper($this->action);
        $_occupied= $occupied;

        if ($occupied) {
            if ($ellapsed < $config->maxexectime) {
                $text= "Hay otra operación de $action corriendo en el sistema desde $this->inicio. ";
                $text.= "Podría esperar a que finalice esta operación o intentar de nuevamente pasadas {$config->maxexectime} horas.";
            } else {
                $text= "La operación de $action iniciada en fecha $this->inicio fue cerrada por el sistema por tiempo excedido.";
                $_occupied= false;
            }
        }
        return $occupied ? array($_occupied, $text) : null;
    }

    public function init_system() {
        $this->id_system= null;
        $this->action= null;
        $this->error= null;

        $this->fecha= null;
        $this->observacion= null;
        $this->fin= null;
        $this->kronos= null;
    }

    public function delete_system() {
        if (!empty($this->id_system)) {
            $sql= "delete from tsystem where id = $this->id_system";
            $this->clink->query($sql);
        }
        $this->init_system();
    }

    public function get_system($action) {
        $this->fecha= null;

        $sql= "select * from tsystem where action like '%$action%' ";
        if (!empty($this->id_system))
            $sql.= "and id != $this->id_system ";
        $sql.= "order by cronos desc limit 1 ";
        $result= $this->exec_sql_NO_LOCAL($sql, false, false);
        $this->cant= $this->clink->num_rows($result);

        if (!empty($this->error_system))
            return null;
        if (!$this->cant)
            return null;

        $row= $this->clink->fetch_array($result);
        $this->fecha= $row['cronos'];
        $this->observacion= $row['observacion'];
        $this->fin= $row['fin'];
        $this->kronos= $row['cronos'];

        return $row['fecha'];
    }

    public function set_system($action= null, $date= null, $error= null) {
        if (!empty($action))
            $this->action= $action;
        if (empty($date))
            $date= ($this->action == 'beginscript' || stripos($this->action, "exec_functions") !== false) ? $this->reg_fecha : _UPDATE_DATE_DIRIGER;
        if (!empty($this->error) && empty($error))
            $error= $this->error;

        if (empty($this->id_system)) {
            $now= (stripos($this->action, 'exec_functions') !== false || stripos($this->action, 'beginscript') !== false) ? "now()" : "'$this->cronos'";
            $this->init_time_system= $this->cronos;

            $sql= "insert into tsystem (action, id_usuario, fecha, cronos, inicio) ";
            $sql.= "values ('$this->action', {$_SESSION['id_usuario']}, '$date', $now, $now)";
            $this->clink->query($sql);
            $this->id_system= $this->clink->inserted_id();
        } else {
            $error= setNULL_str($error);
            $now= date('Y-m-d H:i:s');

            if ((!empty($this->init_time_system) && $this->init_time_system == $now) && stripos($this->action, "Lote") != false) {
                $sql= "delete from tsystem ";
            } else {
                $sql= "update tsystem set cronos= now(), fin= '$now' ";
                if (strtotime($date) >= strtotime('2018-09-28'))
                    $sql.= ", observacion= $error ";
            }
            $sql.= "where id = $this->id_system";

            $this->clink->query($sql);
            $this->init_system();
        }
    }

    public function if_lock_system() {
        $sql= "select * from tusuarios where acc_sys = 2 and id = "._USER_SYSTEM;
        $result= $this->exec_sql_NO_LOCAL($sql, false, false, 0);
        $row= $this->clink->fetch_array($result);
        return $this->clink->num_rows($result) > 0 ? true : false;
    }

    public function lock_system($id_usuario= null) {
        $sql= "update tusuarios set acc_sys= (acc_sys + 2) where acc_sys < 2 ";
        if (!empty($id_usuario))
            $sql.= "and id = $id_usuario";
        $this->exec_sql_NO_LOCAL($sql, false, false, 0);
    }

    public function unlock_system($id_usuario= null) {
        $verbose= !empty($this->verbose) ? true : false;

        $sql= "update tusuarios set acc_sys= 0 where acc_sys < 0 or acc_sys > 1 ";
        if (!empty($id_usuario))
            $sql.= "and id = $id_usuario";
        $this->exec_sql_NO_LOCAL($sql, null, $verbose);

        if ($_SESSION['output_signal'] != 'shell')
            bar_progressCSS(0, "Operaciones terminadas. Acceso al sistema permitido. GRACIAS!!!!", 1);
        else
            echo "\nOperaciones terminadas. Acceso al sistema permitido. GRACIAS!!!!".date('Y-m-d H:i:s')."\n";
    }

    protected function exec_sql_NO_LOCAL($sql, $avoid_error= false, $verbose= null, $divout= 'winlog', $fileout= true) {
        $avoid_error= is_null($avoid_error) ? false : $avoid_error;
        $verbose= is_null($verbose) ? $this->verbose : $verbose;
        $divout= is_null($divout) ? 'winlog' : $divout;
        $fileout= is_null($fileout) ? true : $fileout;

        $sql= str_replace('"', '\"', $sql);
        $result= @$this->clink->query($sql);

        if (!$result) {
            if (!$avoid_error)
                $this->go_exec= false;
            $error= $this->clink->error();
            if ($_SESSION['output_signal'] != 'shell') {
                $line= "<div class='alert alert-danger'>{$error}</div>\n";
                $this->writeLog($line);
            } else {
                echo "\n$error\n";
            }
        } else {
            if ($verbose == 1)
                $this->writeLog("{$sql} \n");
            if ($verbose == 2)
                $this->writeLog('<div class="alert alert-success">Ejecutado con exito</div>');
        }

        return $result;
    }

    protected function exec_multi_sql_NO_LOCAL($sql, $avoid_error= false, $verbose= null, $divout= 'winlog', $fileout= true) {
        $avoid_error= is_null($avoid_error) ? false : $avoid_error;
        $verbose= is_null($verbose) ? $this->verbose : $verbose;
        $divout= is_null($divout) ? 'winlog' : $divout;
        $fileout= is_null($fileout) ? true : $fileout;

        $sql= str_replace('"', '\"', $sql);
        $result= @$this->clink->multi_query($sql);

        if (!$result) {
            if (!$avoid_error)
                $this->go_exec= false;
            $error= $this->clink->error();
            $line= "<div class='alert alert-danger'>{$error}</div>\n";
            $this->show_error($line, $divout, $fileout);
        } else {
            if ($verbose == 1)
                $this->writeLog("$sql \n");
            if ($verbose == 2)
                $this->writeLog('<div class="alert alert-success">Ejecutado con exito</div>');
        }

        return $result;
    }
}
