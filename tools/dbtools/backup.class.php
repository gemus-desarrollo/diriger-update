<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "update.class.php";
include_once "../common/file.class.php";
include_once "../../php/class/usuario.class.php";

defined('_DBSQL_DIRIGER_DIR') or define('_DBSQL_DIRIGER_DIR', _DATA_DIRIGER_DIR."sql/");

class Tbackup extends Tupdate_sys {
    private $other_system;

    private $url_backup;
    /**
     * Especifica si sera exportada toda la base de datos para una restauracion futura
     * de lo contrario solo se exportan las tablas de datos para ser utilizado en otra PC
     * con el sistema instalado     *
     * @var ifmove
     */
    private $ifmove;
    private $_clave;
    public $file;

    private $hay_Zlib;

    public function set_ifmove($id) {
        $this->ifmove = $id;
    }

    public function set_other_system($id = true) {
        $this->other_system = $id;
    }

    public function __construct($clink, $filename= null) {
        $this->clink= $clink;
        Tupdate_sys::__construct($clink);

        $this->ifmove= false;
        $this->_clave;

        $this->file= new Tfile($clink);
        $this->hay_Zlib = !@function_exists('gzopen') ? false : true;
        
        $os_txt= strtolower(php_uname('s'));
        $this->other_system= strpos($os_txt,'win') === false ? true : false;
    }

    public function export() {
        global $config;
        $msg= null;
        $msg_error= null;

        $backup= ($this->ifmove) ? 'copy' : 'backup';
        $this->filename= "$backup-{$_SESSION['db_name']}-".date('Y-m-d-H-i').".sql";
        $this->url = _DBSQL_DIRIGER_DIR . $this->filename;
        if (!empty($config->url_backup))
            $this->url_backup= $config->url_backup. $this->filename;

        $this->file->filename= $this->filename;
        $this->file->url = $this->url;

        $msg= "Iniciado backup en fecha: ".date('Y-m-d H:i:s')."<br/>";
        $command= $this->other_system ? "mysqldump --single-transaction" : "C:\wamp64\bin\mariadb\mariadb10.5.4\bin\mysqldump.exe";
        $command.= " --opt -h localhost -u {$_SESSION['db_user']} --password=\"{$_SESSION['db_pass']}\" {$_SESSION['db_name']} ";
        $command.= $this->other_system ? "> $this->url && gzip $this->url" : "> $this->url";
        exec($command, $output = array(), $worked);

        switch ($worked) {
            case 0:
                $msg.= "La base de datos <b>{$_SESSION['db_name']}</b> se ha almacenado correctamente en la siguiente ruta ".getcwd()."/$this->url_backup</b>";
                break;
            case 1:
                $msg_error= "Se ha producido un error al exportar <b>{$_SESSION['db_name']}</b> a ".getcwd()."/$this->url</b>";
                break;
            case 2:
                $msg_error= "Se ha producido un error de exportación, compruebe la siguiente información: <br/><br/>";
                $msg_error.= "<table><tr><td>Nombre de la base de datos:</td><td><b>{$_SESSION['db_name']}</b></td></tr>";
                $msg_error.= "<tr><td>Nombre de usuario MySQL:</td><td><b>{$_SESSION['db_user']}</b></td></tr>";
                $msg_error.= "<tr><td>Contraseña MySQL:</td><td><b>NOTSHOWN</b></td></tr><tr><td>Nombre de host MySQL:</td><td><b>{$_SESSION['db_ip']}</b></td></tr></table>";
                break;
        }

        $msg.= $msg_error;
        if ($_SESSION['output_signal'] == 'shell') {
            $msg= preg_replace('</tr>', "\n", $msg);
            $msg= preg_replace('<br/>', "\n", $msg);
            $msg= strip_tags($msg);
        }
        $this->writeLog($msg);

        if (is_null($msg_error)) {
            $this->finish_export();

            if ($_SESSION['output_signal'] == 'shell')
                $this->writeLog("\nFinalizado en fecha:".date('Y-m-d H:i:s')."\n\n");
            else
                $this->writeLog("<br/>Finalizado en fecha:".date('Y-m-d H:i:s')."<br/><br/>");
        }
    }

    private function finish_export() {
        global $config;
        
        if (!$this->other_system) {
            if ($this->hay_Zlib)
                $this->compress_backup();

            if (!$this->hay_Zlib && function_exists("gzencode"))
                $this->file->compress();
            else {
                if ($this->hay_Zlib && file_exists($this->url.".dat"))
                    rename($this->url.".dat", $this->url.".gz");
            }
            unlink($this->url);
        }
        
        if (!empty($config->url_backup)) {
            if (copy($this->url.'.gz', $this->url_backup.'.gz'))
                return null;
            else {
                $msg= "No se ha podido copiar el backup a la carpeta {$config->url_backup}";
                $this->writeLog($msg);
                return null;
            }
        }
        return null;
    }
    
    private function compress_backup() {
        if (!$this->hay_Zlib)
            return null;

        $line= null;
        $fp= fopen($this->url, 'r');
        $zp= gzopen($this->url . ".dat", 'w6');

        while (!feof($fp)) {
            $line= fread($fp, 8192);
            gzwrite($zp, $line);
        }

        fclose($fp);
        gzclose($zp);
    }

    public function get_backup($filename) {
        global $config;
        $msg_error= null;
        $msg= "Iniciado proceso de restaura en fecha: ".date('Y-m-d H:i:s')."<br/>";

        $command = "mysql -h {$_SESSION['db_ip']} -u {$_SESSION['db_user']} --password=\"{$_SESSION['db_pass']}\" {$_SESSION['db_name']} < $filename";
        exec($command, $output = array(), $worked);
        switch ($worked) {
            case 0:
                $msg.= "Los datos del archivo <b>$filename</b> se han importado correctamente a la base de datos <b>{$_SESSION['db_name']}</b>";
                break;
            case 1:
                $msg_error= "Se ha producido un error durante la importación. Por favor, compruebe si el archivo está en la misma carpeta que este script. ";
                $msg_error.= "Compruebe también los siguientes datos de nuevo: <br/><br/>";
                $msg_error.= "<table><tr><td>Nombre de la base de datos MySQL:</td><td><b>{$_SESSION['db_name']}</b></td></tr>";
                $msg_error.= "<tr><td>Nombre de usuario MySQL:</td><td><b>{$_SESSION['db_user']}</b></td></tr>";
                $msg_error.= "<tr><td>Contraseña MySQL:</td><td><b>NOTSHOWN</b></td></tr><tr><td>Nombre de host MySQL:</td><td><b>{$_SESSION['db_ip']}</b></td></tr>";
                $msg_error.= "<tr><td>Nombre de archivo de la importación de MySQL:</td><td><b>$this->url</b></td></tr></table>";
                break;
        }

        $msg.= $msg_error;
        if ($_SESSION['output_signal'] == 'shell') {
            $msg= preg_replace('</tr>', "\n", $msg);
            $msg= preg_replace('<br/>', "\n", $msg);
            $msg= strip_tags($msg);
        }
        $this->writeLog($msg);

        if (is_null($msg_error)) {
            if ($_SESSION['output_signal'] == 'shell')
                $this->writeLog("\nFinalizado en fecha:".date('Y-m-d H:i:s')."\n\n");
            else
                $this->writeLog("<br/>Finalizado en fecha:".date('Y-m-d H:i:s')."<br/><br/>");
        }
    }
}
?>