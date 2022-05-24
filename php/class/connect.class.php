<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


include_once "DBServer.class.php";
include_once "base.class.php";
include_once "page_control.class.php";

class Tconnect extends DBServer {
    private $os;
    private $ellapse;
    private $db_name;
    private $ip_app;
    private $expire;
    private $situs;

    private $n_entity;
    private $n_usuarios;
    private $filename;

    public function __construct($host, $database, $ifnewlink= false) {
        DBServer::__construct($host, $database, $_SESSION['db_user'], $_SESSION['db_pass'], $ifnewlink);

        $this->os= strtolower(php_uname('s'));
        if (stripos($this->os,'win') !== false)
            $this->os= 'windows';
        elseif (stripos($this->os,'FreeBSD') !== false)
            $this->os= 'FreeBSD';
        else
            $this->os= 'linux';

        if ($this->clink && !empty($database)) {
            $this->_config();
            $this->_test_key();
        }
    }
    
    private function _config() {
        if ($_SESSION['_ctime'] > 0)
            return;
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes;
        $stop= false;
        $this->filename= null;
        
        $max_config_rows= $_SESSION["_DB_SYSTEM"] == "mysql" ? 18 : 6;

        $sql= null; $nrow= 0;
        $array_exeption= array('id', 'ip_app', 'MAC', 'os', 'ip_db', 'email_server', 'email_app', 'ellapse',
                                'expire', 'reg_fecha', 'n_entity', 'n_usuario', 'chronos', 'situs');
        $file= _PHP_DIRIGER_DIR."license.txt";

        if (is_null($_SESSION['origen']) || (strlen($_SESSION['origen']) > 2 || strlen($_SESSION['origen']) == 0))
            $_SESSION['origen']= $_SESSION['location'];
        $cronos= date('Y-m-d H:i:s');

        $this->getDatabaseName();

        if (file_exists($file))  {
            $this->filename= $file;
            $result= @$this->query("select * from _config order by chronos desc limit 1");
            if (!$result)
                $result= $this->query("select * from _config limit 1");
            if ($result)
                $nrow= $this->fetch_array($result);

            $fp= file_get_contents($file);
            $sql= explode("@&@&", $fp);
            for ($i= 0; $i < $max_config_rows; ++$i)
                if (!is_null($sql[$i]))
                    $sql[$i]= base64_decode($sql[$i]);

            $reg_date= $sql[0];
            $this->situs= $sql[1];
            $db_name= $sql[2];

            for ($i= 3; $i < $max_config_rows; ++$i) {
                if (!is_null($sql[$i]))  {
                    @$this->query($sql[$i]);
                    /*
                    if (!is_null($this->error()) && $i == 13) {
                        $_sql= "ALTER TABLE _config MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT";
                        @$this->query($_sql);
                    }
                    */
            }   }
            $sql= "update _config set chronos= '$cronos', situs= '{$_SESSION['origen']}' ";
            $sql.= "where chronos is null and situs is null ";
            @$this->query($sql);

            sleep(5);

            $cronos= date('Y-m-d H:i:s');
            $sql= "update _config set chronos= '$cronos' where reg_fecha = '$reg_date' ";
            @$this->query($sql);

            //* estructura de la tabla _config --------------------------------------------------------- */
            $_config= array();

            $fields= $this->fields("_config");

            if ($nrow) {
                $row= $this->fetch_array($result);
                $_config= array();

                foreach ($fields as $field) {
                    $name= $field['Field'];
                    $type= strtoupper($field['Type']);
                    $length= 0;

                    if ($_SESSION["_DB_SYSTEM"] == "mysql") {
                        $i= strpos($type, "(");
                        $j= strpos($type, ")");

                        $length= substr($type,$i+1,$j-$i-1);
                        $type= substr($type,0,$i);
                    }

                    $value= $row[$name];

                    if ($type == "BOOLEAN" || ($type == "TINYINT" && $length == 1 && $_SESSION["_DB_SYSTEM"] == "mysql"))
                        $value= boolean($value);
                    elseif (array_search($type, $SQL_timetypes) !== false)
                            $value= setNULL_str($value, false);
                    elseif (array_search($type, $SQL_texttypes) !== false)
                            $value= setNULL_str($value, false);
                    elseif (array_search($type, $SQL_numtypes) !== false) {
                        if (is_numeric($value) && $value == 0)
                            $value= setZero($value);
                        else
                            $value= setNULL($value);
                    }

                    $_config[]= array('field'=>$name, 'value'=>boolean2pg($value));
                }

                $sql= "update _config set ";
                $rowdb= null;
                $i= 0;

                foreach ($_config as $rowdb) {
                    $field= $rowdb['field'];
                    $value= $rowdb['value'];

                    if (array_search($field, $array_exeption) !== false)
                        continue;
                    $sql.= $i > 0 ? ", " : " ";
                    $sql.= "$field= $value";
                    ++$i;
                }

                $sql.= " where cronos = '$cronos' ";
                if ($i > 0)
                    $this->query($sql);
            }
            unlink($file);

            if ($this->filename && (strtolower($db_name) != $this->db_name))
                $stop= true;
            if ($stop) { ?>
               <script language="javascript">parent.location.href= '<?=_SERVER_DIRIGER?>index.php';</script>
           <?php }    
        }
    }    

    private function _test_config() {
        if (empty($_SESSION['mac']))
            $this->get_mac();
        $row= $this->_get_config();
        $array= $this->get_n_entity_usuarios();          
        
        $stop= false;
        
        if (stripos($_SESSION['mac'], $row['MAC']) === false)
            $stop= true;
        else
            $_SESSION['mac']= $row['MAC'];
        if ($this->situs != $_SESSION['location'])
            $stop= true;
        if (!empty($row['situs']) && ($_SESSION['location'] != $row['situs'] && $_SESSION['origen'] != $row['situs']))
            $stop= true;
        if ($_SESSION['db_ip'] != $row['ip_db'])
            $stop= true;
        if (strtolower($_SESSION['email_server']) != strtolower($row['email_server']))
            $stop= true;
        if (strtolower($_SESSION['email_app']) != strtolower($row['email_app']))
            $stop= true;
        if ($this->os != $row['os'])
            $stop= true;
        if (strtotime($row['expire']) <= strtotime(date('Y-m-d H:i:s')))
            $stop= true;
        if ((!empty($row['n_entity']) && !empty($this->n_entity)) && $row['n_entity'] < $array[0])
            $stop= true;
        if (!empty($row['n_usuarios']) && $row['n_usuarios'] < $array[1])
            $stop= true;

        if ($stop) { ?>
            <script language="javascript">parent.location.href= '<?=_SERVER_DIRIGER?>index.php';</script>
        <?php }
        else {
            $_SESSION['ip_app']= trim($row['ip_app']);
            if ($this->filename) 
                $this->setLocalProceso();
        }
    }

    private function _test_key() {
        $_MAX_TIME= 1000;
        $_ctime= (int)$_SESSION['_ctime'];

        if ($_ctime > $_MAX_TIME || $_ctime == 0) {          
            $this->_test_config();
            if (!$this->db_name) 
                $this->getDatabaseName();

            $md5= strtoupper($_SESSION['empresa']).$this->ellapse.$_SESSION['ip_app'].$_SESSION['db_ip'].$_SESSION['mac'].strtolower($this->db_name);
            $md5.= strtolower($_SESSION['email_app']).strtolower($_SESSION['email_server']).$this->expire.strtoupper($this->situs).$this->os;
            if (!empty($this->n_entity) && !empty($this->n_usuarios))
                $md5.= $this->n_entity. $this->n_usuarios;
            $md5= md5($md5);
            $sql= "select count(*) from tusuarios where _clave = '$md5'";
            $_result= @$this->query($sql);
            $_cant= $this->fetch_array($_result)[0];

            if (empty($_cant)) {?>
                <script language="javascript">parent.location.href= '<?=_SERVER_DIRIGER?>index.php';</script>
            <?php }
            $_ctime= 0;
        }

        ++$_ctime;
        $_SESSION['_ctime']= $_ctime;
    }
    
    
    private function setLocalProceso() {
        $codigo= $_SESSION['location'];
        $connect=  _TCT_IP;
        $email= $_SESSION['email_app'];

        $sql= "update tprocesos set conectado= '$connect', email= '$email', ip= '$this->ip_app', ";
        $sql.= "codigo= '$codigo', situs= '$codigo' where id = ".$_SESSION['local_proceso_id'];
        $this->query($sql);
    }

    private function _get_config() {
        $row= null;
        $result= $this->query("select * from _config order by chronos desc limit 1");
        if (!$result)
            $result= $this->query("select * from _config limit 1");
        if ($result)
            $row= $this->fetch_array($result);

        $this->ip_app= $row['ip_app'];
        $this->ellapse= $row['ellapse'];
        $this->expire= $row['expire'];
        $this->situs= $row['situs'];
        $this->n_entity= $row['n_entity'];
        $this->n_usuarios= $row['n_usuarios'];
        return $row;
    }

    private function get_n_entity_usuarios() {
        $sql= "select count(*) from tprocesos where if_entity is not null or if_entity = true";
        $result= $this->query($sql);
        $row= $this->fetch_array($result);
        $n_entity= $row[0];

        $sql= "select count(*) from tusuarios where eliminado is null";
        $result= $this->query($sql);
        $row= $this->fetch_array($result);
        $n_usuarios= $row[0];
        return array($n_entity, $n_usuarios);
    } 
    
    private function getDatabaseName() {
        if ($_SESSION["_DB_SYSTEM"] == "mysql")
            $sql= "select database()";
        else {
            $sql= "SELECT table_catalog FROM information_schema.tables ";
            $sql.= "WHERE table_catalog = '{$_SESSION['db_name']}' limit 1;";
        }
        $result= $this->query($sql);
        $row= $this->fetch_array($result);
        $this->db_name= $row[0];
    }
    
    private function get_mac() {
        $mac= null;
        $imac= null;
        $istream= null;
        $j= 0;

        if ($this->os == 'windows') {
            exec("ipconfig /all", $stream);
            $count= count($stream);
            $j= 0;
            for ($i= 0; $i <= $count; $i++) {
                if (isset($stream[$i]))
                    preg_match('/[A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}/', $stream[$i], $istream[$j]);
                else
                    $istream[$k]= null;
                if (!is_null($istream[$j][0]) && strlen($istream[$j][0])) {
                    $mac[]= $istream[$j][0];
                    ++$j;
        }   }   }

        if ($this->os != 'windows') {
            $eth= exec("/sbin/route | grep default | tr -s ' ' | cut -f8 -d ' '");
            $cmdshell= popen("/sbin/ifconfig $eth",'r');
            $line= fread($cmdshell, 2096);
            pclose($cmdshell);
            preg_match("/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/", $line, $mac);
            $txtmac= $mac[0];

            ++$j;
        }

        if ($j == 0)
            $mac[]= 'XZ-0X-XX-X0-ZX-ZZ';

        $_SESSION['mac']= implode(';',$mac);
    }    
}

/*********************************************************************
 * Termina la definicion de las clases
 *********************************************************************/
if (is_null($applay_open_connect) || $apply_open_connect) {
    $clink= null;
    $clink= new Tconnect($_SESSION['db_ip'], $_SESSION['db_name']);
    $clink= empty($clink->error) ? $clink : false;
}

if (empty($_SESSION['current_year'])) {
    $time= new TTime();

    $_SESSION['current_year']= $time->GetYear();
    $_SESSION['current_month']= $time->GetMonth();
    $_SESSION['current_day']= $time->GetDay();
}

if (is_null($_SESSION['id_usuario']) && ($signal != 'lote' && $signal != 'login' && $signal != 'index')) {
?>
    <script language='javascript' type="text/javascript" charset="utf-8">
        alert('Fallo el intento de iniciar la sesión de trabajo. Por favor, consulte a un administrador del sistema o a su administrador de red.');
        parent.location.href= '<?=_SERVER_DIRIGER ?>index.php';
    </script>
<?php } ?>
