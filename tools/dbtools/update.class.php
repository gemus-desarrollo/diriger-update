<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once _ROOT_DIRIGER_DIR."inc.php";
include_once _ROOT_DIRIGER_DIR."php/config.inc.php";
include_once _ROOT_DIRIGER_DIR."tools/dbtools/base_clean.class.php";
include_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";
include_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/code.class.php";

include_once _ROOT_DIRIGER_DIR."php/class/pop3/pop3.class.php";

set_time_limit(0);


class Tupdate_sys extends Tclean {
    protected $fp;
    private $sql_stream;
    protected $beginscript;
    private $last_exec_function;
    private $list_node;

    public $last_update_time;
    public $last_script_time;

    public $winlog;
    private $app_winrar;
    private $package_used;
    public $pop3;

    private $app_winrarap;


    public function __construct($clink, $filename= null) {
        Tclean::__construct($clink);
        $this->set_cronos();

        $this->clink= $clink;
        $this->filename= $filename;
        $this->fecha= null;
        $this->go_exec_sql= true;
        $this->go_exec_funct= true;

        $this->list_node= new Tlist_node();
    }

    public function test_winrar() {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') 
                return true;

        if (is_null($_SESSION['app_winrar'])) {
            if (file_exists("c:/Program Files/WinRAR/winrar.exe")) 
                $this->app_winrar= "c:/\"Program Files\"/WinRAR/winrar.exe";
            if (file_exists("c:/Program Files (x86)/WinRAR/winrar.exe")) 
                $this->app_winrar= "c:/\"Program Files (x86)\"/WinRAR/winrar.exe";
        } else {
            $winrar= $_SESSION['app_winrar'];
            $winrar= str_replace("\"", '', $winrar);

            if (!file_exists(addslashes($winrar))) 
                return false;
            $this->app_winrarap= addslashes($_SESSION['app_winrar']);
        }

        return true;
    }

    public function test_updatemail_exists($debug= null) {
        $this->error= null;

        $this->pop3= new POP3();
        $this->pop3->Debug= !is_null($debug) ? $debug : false;
        $this->pop3->Connect();

        if (!$this->pop3->mbox) {
            $this->error= "Ha fallado la conexión al servidor de correos. Servidor reporta: " . imap_last_error();
            $this->error.= " No ha sido posible comprobar si existen actualizaciones en el buzón. ";
            $this->error.= "Por favor revise la cuenta de correo Diriger para descargar las actualizaciones el sistema.";

            return false;
        }

        if ($this->pop3->emails_quan == 0) 
            return false;

        $cant_mails= $this->pop3->list_inbox();

        $_version= explode(".", _VERSION_DIRIGER);
        $_version= $_version[0] * 100 + $_version[1] * 10 + $_version[2];

        $_update= explode(".", _UPDATE_DIRIGER_NO);
        $_update= $_update[0] * 100 +$_update[1] * 10 + $_update[2];

        $i= 0;
        foreach ($this->pop3->array_emails as $email) {
            if (stripos($email['subject'], "gemus||diriger||") === false) 
                continue;
            $body= explode('||', $email['subject']);
            if (strcmp(strtoupper($body[0]), "GEMUS") != 0 || strcmp(strtoupper($body[1]), "DIRIGER") != 0) 
                continue;

            $version= explode(".",$body[2]);
            $version= $version[0] * 100 + $version[1] * 10 + $version[2];
            $update= explode(".",$body[3]);
            $update= $update[0] * 100 + $update[1] * 10 + $update[2];

            $date= substr($body[4],0,4)."-".substr($body[4],4,2)."-".substr($body[4],6,2);

            if ((int)$version < (int)$_version) 
                continue;
            if ((int)$update < (int)$_update) 
                continue;
            if (strtotime($date) < strtotime(_UPDATE_DATE_DIRIGER)) 
                continue;

            ++$i;
        }

        return empty($i) ? false : true;
    }

    public function get_emails() {
        $this->error= null;
        $this->cant_files= 0;
        if (isset($this->array_files)) unset($this->array_files);
        $this->array_files= array();

        point_progress("Conectandose al servidor de correos", 0, 1);

        $this->pop3= new POP3();
        $this->pop3->Connect();

        if (!$this->pop3->mbox) {
            $this->error= "Ha fallado la conexión al servidor de correos. Servidor reporta: " . imap_last_error()." \n";
            return false;
        }

        if (empty($this->pop3->emails_quan)) {
            $this->error= "El buzón esta vacío. Nada que hacer.";
            return true;
        }

        /**
         * asunto:gemus||diriger||6.3||17.3||20150720
         */
        $cant_mails= $this->pop3->list_inbox();

        $_version= explode(".", _VERSION_DIRIGER);
        $_version= $_version[0] * 100 + $_version[1] * 10 + $_version[2];

        $_update= explode(".", _UPDATE_DIRIGER_NO);
        $_update= $_update[0] * 100 +$_update[1] * 10 + $_update[2];

        $i= 0;
        foreach ($this->pop3->array_emails as $email) {
            ++$i;
            if (stripos($email['subject'], "gemus||diriger||") === false) 
                continue;
            $body= explode('||', $email['subject']);
            if (strcmp(strtoupper($body[0]), "GEMUS") != 0 || strcmp(strtoupper($body[1]), "DIRIGER") != 0) 
                continue;

            $version= explode(".", $body[2]);
            $version= $version[0] * 100 + $version[1] * 10 + $version[2];
            $update= explode(".",$body[3]);
            $update= $update[0] * 100 + $update[1] * 10 + $update[2];

            $date= substr($body[4],0,4)."-".substr($body[4],4,2)."-".substr($body[4],6,2);

            if ((int)$version < (int)$_version) 
                continue;
            if ((int)$update < (int)$_update) 
                continue;
            if (strtotime($date) < strtotime(_UPDATE_DATE_DIRIGER)) 
                continue;

            $this->pop3->current_email_index= $email['uid'];
            $this->pop3->current_attach_index= 2;

            $this->pop3->FetchMail($email['uid']);

            if ($this->pop3->HasAttachment()) {
                if ($this->pop3->FetchAttachment()) {
                    if (is_null($this->pop3->SaveAttachment(_UPDATE_DIRIGER_DIR))) 
                        continue;

                    $attacmentfile= $this->pop3->GetAttachmentFilename();
                    $pack= substr($attacmentfile, 0, stripos($attacmentfile, ".tar.gz"));

                    $array= array('name'=>$attacmentfile, 'pack'=>$pack, 'header'=>$this->header, 'date'=>$date,
                            'timestamp'=>strtotime($date), 'version'=>$version, 'update'=>$update, 'email_number'=>$email['uid']);
                    $this->array_files[]= $array;

                    ++$this->cant_files;
            }   }

            point_progress("Descargando actualizaciones", $i, $cant_mails);
        }

        if ($this->cant_files == 0) {
            $this->error= "No existen nuevas actualizaciones a descargar. Nada que hacer.";
            return true;
        }

        foreach ($this->array_files as $key => $row) {
            $array_timestamp[$key]= $row['timestamp'];
            $array_update= $row['update'];
        }

        array_multisort($array_timestamp, SORT_NUMERIC, SORT_DESC, $array_update, SORT_NUMERIC, SORT_DESC, $this->array_files);
        reset($this->array_files);

        return true;
    }

    public function DeleteMail($index) {
        $this->pop3->current_email_index= $index;
        $this->pop3->DeleteMail();
    }

    function Close() {
        $this->pop3->Close();
    }

    private function update_src($pack) {
        if (!empty($this->package_used[$pack])) 
            return null;

        $os_txt= (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'windows' : 'linux';
        $url= _UPDATE_DIRIGER_DIR;

        if ($os_txt == 'linux') {
            echo "<p>Eliminando las salvas anteriores de archivos {$url}{$pack}.BAK  ...............</p>";
            $exec= "rm -fr {$url}{$pack}.BAK";
            system($exec);

            echo "<p>Descomprimiendo archivo $pack.tar.gz  ...............</p>";
            chdir($url);
            $exec= "tar -xzf {$url}{$pack}.tar.gz";
            system($exec);

            echo "<p>Creando salva de la actualización {$url}{$pack}  ...............</p>";
            $exec= "cp frv {$url}{$pack} {$url}{$pack}.BAK";
            system($exec);            
            
            echo "<p>Creando carpeta fuente de la actualizacion {$url}diriger.update  ...............</p>";
            $exec= "cp frv {$url}{$pack} {$url}diriger.update";
            system($exec);               
            
            if (is_writeable(_ROOT_DIRIGER_DIR)) {            
                echo "<p>Sobrescribiendo sistema con archivos {$url}{$pack}   ...............</p>";
                $exec= "cp -frv {$url}diriger.update/ "._ROOT_DIRIGER_DIR;
                exec($exec, $linecmd);
                foreach ($linecmd as $line) 
                    echo '<br />'.htmlspecialchars(trim($line));
            
                echo "<p>Eliminando archivos de la actualizacion {$url}{$pack}   ...............</p>";    
                $exec= "rm -fr {$url}diriger.update";
                system($exec);                
            }
        }

        if ($os_txt == 'windows') {
            echo "<p>Eliminando salva de archivos {$url}{$pack}.BAK  ...............</p>";
            $exec= str_ireplace("/", "\\", "{$url}{$pack}.BAK");
            $exec= "rd /S /Q $exec";
            system($exec);

            echo "<p>Ejecutando salva de archivos {$url}{$pack}  ...............</p>";
            $exec= "move /Y {$url}{$pack} {$url}{$pack}.BAK";
            system($exec);

            echo "<p>Descomprimiendo archivo $pack.tar.gz  ...............</p>";
            $exec= "$this->app_winrar x {$url}{$pack}.tar.gz {$url}";
            system($exec);

            echo "<p>Sobrescribiendo sistema con archivos {$url}{$pack}   ...............</p>";
            $exec= str_ireplace("/", "\\", "xcopy {$url}{$pack}//* "._ROOT_DIRIGER_DIR);
            $exec.= " /E /F /Y";

            exec($exec, $linecmd);
            foreach ($linecmd as $line) 
                echo '<br />'.htmlspecialchars(trim($line));
        }

        $this->package_used[$pack]= 1;
    }

    public function copy_src($pack) {
        $this->error= null;
        $package= _UPDATE_DIRIGER_DIR."$pack.tar.gz";

        if (file_exists($package)) 
            $this->error= $this->update_src($pack);
        else 
            $this->error= "Al parecer el archivo $pack.tar.gz no existe, o no es posible su lectura";

        return $this->error;
    }

    public function readupdate($readContent= true) {
        $this->fp = file($this->filename);
        $html= null;
        $this->error= null;
        
        if (!$this->fp) {
            $this->go_exec= false;
            $this->error= "Error en intento de acceso al fichero SQL --->".$this->filename;
            if ($_SESSION['output_signal'] != 'shell') 
                $html.= "<div class='alert alert-danger'>{$this->error}</div>";
            else 
                $html.= $this->error;
            
        } else {
            if ($readContent) {
                foreach ($this->fp as $nums => $row) {
                    $line= $row;
                    $line= nl2br($line);
                    $i= strripos((string)$line, "<br />");
                    if ($i !== false) 
                        $line= substr($line, 0, $i);
                    $_num= $nums+1;

                    $html.= "<tr>";
                    $html.=  "<td class=\"colnumber\">{$_nums}</td>";
                    $html.=  "<td><span class=\"coltext\">".htmlspecialchars(addslashes($line))."</span></td>";
                    $html.=  "</tr>";
                }
                
                fclose($this->fp);
                return $html;
            }
        }
        
        return $html;
    }
    
    public function read_sql() {
        $eol= true;
        $this->beginscript= null;
        $beginscript= null;

        reset($this->fp);
        $nlines= 0;
        $flag= true;
        
        foreach ($this->fp as $nums => $row) {
            $row= trim($row)." ";
            
            if ($eol && strstr($row, "/*") !== false) {
                if (strstr($row, '*/')) {
                    $eol = false;
                    ++$nlines;
                    continue;
                }
            }
            if (!$eol && strstr($row, "*/") !== false) {
                $eol = true;
                ++$nlines;
                continue;
            }

            $_row= substr($row,0,4);
            if (strstr($_row, "-- ") !== false && strstr($row, "beginscript:") === false) {
                ++$nlines;
                continue;
            }

            if (strstr($row,"-- ") !== false && strstr($row,"beginscript:") !== false)  {
                $beginscript= substr($row, strpos($row, "beginscript:"), strlen($row));
                $beginscript= trim(substr($beginscript,12,11));
                $flag= $this->test_script($beginscript);
                
                if (!is_null($this->last_script_time)) {
                    $this->beginscript= $beginscript;
                    if (strtotime($beginscript) <= strtotime($this->last_script_time) || strtotime($beginscript) <= strtotime($this->last_update_time))
                        $beginscript= null;
                }
                ++$nlines;
                continue;
            }
            if (!is_null($beginscript) && (strstr($row,"-- ") !== false && strstr($row,"endscript") !== false)) {
                $beginscript= null;
                ++$nlines;
                continue;
            }
            if (!$eol && strstr($row, "-- ") !== false) {
                $eol = true;
                ++$nlines;
                continue;
            }
            if (!strlen($row)) {
                ++$nlines;
                continue;
            }

            if (!is_null($beginscript)) {
                $this->sql_stream[]= array('num'=>$nlines, 'line'=>$row, 'reg_date'=>$beginscript, 'flag'=>$flag);
            }
        }

        $nums= 0; 
        $sql= null; 
        $eol= false;

        foreach ($this->sql_stream as $array) {
            if (!$array['flag']) 
                continue;
            $line= $array['line'];
            $reg_date= !is_null($array['reg_date']) ? $array['reg_date'] : null;

            if ($nums == 0) {
                $nums= $array['num'];
                $reg_date= $array['reg_date'];
            }

            $sql.= $line;

            $pos= strrpos($line, ';');
            $len= strlen(trim($line));
            if ($pos !== false && ((int)$pos + 1) == (int)$len)
                $eol= true;

            if ($eol) {
                $this->list_node->add_steep($reg_date, $nums, $sql, null);
                ++$this->nrows;
                $nums = 0;
                $sql = null;
                $eol = false;
                $reg_date = null;
            }  
        }
    }
    
    public function init_list() {
     /* para pruebas */
   //     $this->list_node->show_list();
   //      exit;
        $this->filename= _DIRIGER_DIR."log/updates/update_".$_SESSION['empresa'].'_'.date('Y').'_'.date('m').'_'.date('d').'_'.date('H').'_'.date('i').'.html';
        $this->pfile= fopen($this->filename, 'w+');
        $this->fileout= $this->pfile;
        
        if (empty($this->pfile)) {
            $this->unlock_system();            
            $error= "No se ha podido crear el fichero {$this->filename} para las actualizaciones. Consulte urgentemente al personal de GEMUS. ";
            $error.= "e-Correo:gemus@nauta .cu;  telefono:58200755 / 53740039";
            ?>
                writeLog('<?=date('Y-m-d H:i:s')?>', "<?="<div class='alert alert-danger'>{$error}</div>"?>", "<?= $this->divout?>");
                alert("<?=$error?>");
            <?php
            return $error;
        }

        $html= "<html><head>";
        $html.= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        $html.= "<title>LOG DEL SISTEMA</title>";
        $html.= "<link rel='stylesheet' type='text/css' media='screen' href='../css/main.css'  />";
        $html.= "<link rel='stylesheet' type='text/css' media='screen' href='../css/form.css'  />";
        $html.= "<link rel='stylesheet' type='text/css' media='screen' href='../tools/lote/css/main.css'  />";
        $html.= "</head><body style='background:none'>";

        $sql= "SET FOREIGN_KEY_CHECKS= 0";
        $this->do_sql_show_error('execute_sql', $sql, false);
        
        fclose($this->pfile);   
        return null;
    }
    
    public function end_list() {
        $this->pfile= fopen($this->filename, 'a+');

        $html= "<br/>**********************************************/";
        $html.= "<br/>Fin de actualizacion: ".date('Y-m-d H:i:s');
        $html.= "<br/>/**********************************************/";  
        $this->show_error($html, null, null);
     
        $html= "<br/></body></html>";
        $this->show_error($html, null, null, false);
        fclose($this->pfile);

        $sql= "SET FOREIGN_KEY_CHECKS= 1";
        $this->do_sql_show_error('execute_sql', $sql, false);        
    }

    public function execute_list() {
        $this->irows= 0;
        $this->nums_rows= $this->list_node->inode;
        $this->init_system();
        $pointer= $this->list_node->top;
        $this->reg_fecha= $this->list_node->top->date;
        $this->beginscript= $this->reg_fecha;
                
        $html.= "<br/>/**********************************************/<br/>";
        $html.= "Inicio de actualizacion: ".date('Y-m-d H:i:s');
        $html.= "<br/>Corresponde al registro: ".$this->reg_fecha;
        $html.= "<br/>/**********************************************/<br/>";

        $this->show_error($html);

        while (!is_null($pointer)) {
            if (!is_null($pointer->sql)) 
                $this->execute_sql($pointer);
            elseif (!is_null($pointer->function)) 
                $this->exec_function($pointer->function, $pointer->remote, $pointer->argv, $pointer->date);

            if (!$this->go_exec_funct) 
                break;
            $pointer= $pointer->next;
        }
    }

    private function parseSQL($sql) {
        $sql= $_SESSION['_DB_SYSTEM'] == 'mysql' ? preg_replace('/"/', '`', $sql) : preg_replace('/"/', '\\"', $sql);
        return $sql;
    }

    private function _exect_sql($node) {
        static $i= 0;
        $sql= $this->parseSQL($node->sql);
        $result= $this->do_sql_show_error('execute_sql', $sql, false);

        if (!is_null($this->error)) {
            $this->go_exec= false;
        }
        
        $count= 0;
        if (stristr($node->sql,'update') || stristr($node->sql,'insert') || stristr($node->sql,'delete') || stristr($node->sql,'alter'))
            $count= $this->clink->affected_rows($result);

        if (empty($this->error_system)) {
            $this->show_error("<strong>OK (affected: $count)</strong><br/><br/>");
        }    
        
        $r= (float)(++$i) / $this->nrows;
        $_r= $r*100; 
        $_r= number_format($_r,1);
        if ($_SESSION['output_signal'] != 'shell') 
            bar_progressCSS(0, "Ejecutando script para actualizar BD ($_r%)....", $r);        
    }
    
    public function execute_sql($node) {
        ++$this->irows;
        $this->pfile= fopen($this->filename, 'a+');
        $this->show_error("<div class=colnumber>Linea:$node->num</div>".htmlspecialchars($node->sql)."<br />");

        if (!is_null($node) && ((!is_null($this->reg_fecha) && strtotime($this->reg_fecha) != strtotime($node->date)) || ($this->irows == $this->nums_rows || $this->irows == 1))) {
            if ($this->irows == 1) {
                $this->set_system('beginscript', $this->reg_fecha); 
                $this->_exect_sql($node);
                if ($this->irows == $this->nums_rows) 
                    $this->set_system(null, null, $this->error);
            } else {
                $this->set_system(null, null, $this->error);
                $this->init_system();
                $this->set_system('beginscript', $this->reg_fecha);
                $this->_exect_sql($node);
                if ($this->irows == $this->nums_rows) 
                    $this->set_system(null, null, $this->error);
            }

            if (strtotime($this->reg_fecha) != strtotime($node->date) || $this->irows == $this->nums_rows) 
                $this->show_error("<div class='alert alert-info'>Registrado: $this->reg_fecha</div>"); 
            
            if (!is_null($node)) {
                $this->reg_fecha= $node->date;
                $this->beginscript= $this->reg_fecha;
            }              
        } else {
            $this->_exect_sql($node);
        }

        fclose($this->pfile);
    }

    public function test_script($beginscript) {
        $sql= "select * from tsystem where action = 'beginscript' and fecha = '$beginscript'";
        $this->do_sql_show_error('test_exec_function', $sql);

        return $this->cant > 0 ? false : true;
    }    

    public function test_function($function, $remote) {
        $name= $function[0];
        $date= $function[1];

        $sql= "select * from tsystem where action = 'exec_functions.$name'";
        $this->do_sql_show_error('test_exec_function', $sql);

        if ($this->cant > 0) 
            return false;
        if ((int)strtotime($this->last_exec_function) > (int)strtotime($date)) 
            return false;

        $this->list_node->add_steep($date, null, null, $name, null, $remote);
        return true;
    }

    private function _exect_function($function, $argv= null) {
        $this->error= null;
        $result= call_user_func($function, $argv);
        
        if (!$this->go_exec_funct) {
            fclose($this->pfile);
            return false;
        } 
        
        if (!is_null($result)) {
            $this->error= $this->clink->error();
            $this->go_exec_funct= false;
            if (empty($remote)) 
                $this->show_error("SQL: $result<br/>ERROR:". $this->clink->error()."<br/><br/>");
            else 
                $this->show_error("ERROR:".$result."<br/><br/>", true);
        } else {
            $this->go_exec_funct= true;
        }        
        
        if (!is_null($result)) 
            return false;
        
        return true;
    }
    
    public function exec_function($function, $remote, $argv= null, $date= null) {
        $this->pfile= fopen($this->filename, 'a+');
        $this->show_error("<strong style='color:red; font-weight: bolder'>Ejecutando funcion $function($argv) .....<br/><br/></strong>");

        if (is_null($this->last_exec_function)) {
            $this->last_exec_function= $this->get_system('exec_functions');
            if (is_null($this->last_exec_function)) 
                $this->last_exec_function= '2014-01-01';
        }

        ++$this->irows;
        bar_progressCSS(1, "$function: Iniciando", 0);

        if ($this->irows == 1) {
            $this->set_system("exec_functions.$function", $date);
            $result= $this->_exect_function($function, $argv);
            if ($this->irows == $this->nums_rows) 
                $this->set_system(null, null, $this->error);
            bar_progressCSS(1, "$function: terminado", 1, null, false); 
            
            if (!$result) 
                return;

        } else {
            $this->set_system(null, null);
            $this->init_system();
            $this->set_system("exec_functions.$function", $date);
              
            $error= call_user_func($function, $argv);
            bar_progressCSS(1, "$function: terminado", 1, null, false);  
            if ($this->irows == $this->nums_rows) 
                $this->set_system(null, null, $this->error);
            if ($error) {
                $this->show_error("ERROR:".$error."<br/><br/>", true);
                return;
            }    
        }

        if (!is_null($date)) 
            $this->reg_fecha= $date;

        fclose($this->pfile);
    }
    
    public function get_days_expire() {
        $sql= "select chronos, expire from _config order by chronos desc";
        $result= $this->do_sql_show_error('get_days_expire', $sql);
        $row= $this->clink->fetch_array($result);
        return $row['expire'];
    }
    
}


/**
 * Class Tnode
 */

class Tnode {
    public $date;
    public $next,$prev;
    public $sql, $function, $argv, $remote;
    public $inode, $num;

    function Tnode() {
        $this->sql= null;
        $this->function= null;
        $this->remote= null;

        $this->next= null;
        $this->prev= null;
    }
}


class Tlist_node extends Tnode {
    public $top, $end, $pointer;
    public $node;
    public $inode;
    private $array;


    public function Tlist_node() {
        $this->top= null;
        $this->inode= 0;
        $this->next= null;
        $this->prev= null;
    }

    public function add_steep($date, $num= null, $sql= null, $function= null, $argv= null, $remote= false) {
        $node= new Tnode();
        $node->date= $date;
        $node->sql= $sql;
        $node->num= $num;
        $node->function= $function;
        $node->argv= $argv;
        $node->remote= $remote;
        $node->inode= $this->inode;

        $this->array[$this->inode]= $node;
        $this->node= &$this->array[$this->inode];

        ++$this->inode;

        if ($this->inode == 1) $this->pull();
        else $this->search();
  //      $this->show_list($this->node);
    }


    private function search() {
        if (strtotime($this->node->date) < strtotime($this->top->date)) {
            $this->push();
            return;
        }

        if (strtotime($this->node->date) >= strtotime($this->end->date)) {
            $this->pull();
            return;
        }
        
        $pointer= $this->top;
        
        while ($pointer != null) {
            if (strtotime($this->node->date) >= strtotime($pointer->date) 
                && strtotime($this->node->date) < strtotime($pointer->next->date)) {
                $this->insert($pointer);
                return;
            }
            
            $pointer= $pointer->next;
        }

        $this->pull();
        return;
    }

    // de cabecera
    private function push() {
        $this->node->next= $this->top;
        $this->top->prev= &$this->node;
        
        $this->top= &$this->node;  
    }
    
    // al final
    private function pull() {
        if ($this->top == null) {            
            $this->top= &$this->node;
            $this->end= &$this->node;
            
            return;
        }

        $this->end->next= &$this->node;
        $this->node->prev= $this->end;
        $this->end= &$this->node;
    }


    // inserta detras del pointer encontrado
    private function insert($pointer) {
        $this->node->prev= $pointer;
        $this->node->next= $pointer->next;
        
        $pointer->next= &$this->node;           
    }


    public function show_list($node= null) {
        if (!is_null($node)) {
            $pointer= $node;
            echo "show_list:$pointer->date | $pointer->num | $pointer->sql | $pointer->function | | $pointer->argv <br/>";
            return;
        }

        $pointer= $this->top;

        while ($pointer != null) {
            echo "$pointer->date | $pointer->num | $pointer->sql | $pointer->function | | $pointer->argv <br/>";
            $pointer= $pointer->next;
        }
    }
}


?>