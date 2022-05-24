<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once _ROOT_DIRIGER_DIR."php/config.inc.php";
include_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/mail.class.php";

include_once _ROOT_DIRIGER_DIR."libs/phpseclib/phpcrypt.class.php";


defined('_EXPORT_DIRIGER_DIR') or define('_EXPORT_DIRIGER_DIR', _DATA_DIRIGER_DIR."export/");
defined('_IMPORT_DIRIGER_DIR') or define('_IMPORT_DIRIGER_DIR', _DATA_DIRIGER_DIR."import/");
defined('_DBSQL_DIRIGER_DIR') or define('_DBSQL_DIRIGER_DIR', _DATA_DIRIGER_DIR."sql/");

class Tfile extends Tbase {
    public $url;
    
    public $size, 
            $type;
    public $array_files, 
            $cant_files, 
            $header;

    public $origen_code;
    public $destino_code;
    public $if_mcrypt;
    
    private $obj_mail;
    
    public function __construct($clink) {
        Tbase::__construct($clink);

        $this->obj_mail= null;
        $this->cant_files= 0;
        $this->clink= $clink;
    }

    public function getFileURL() {
        return $this->url;
    }

    /**
     * @param null $file: nombre del fichero, no incluye la ruta
     */
    public function set_file_url($file= null) {
        $this->filename= is_null($file) ? urldecode($_GET["lote"]) : $file;
        $this->url= _IMPORT_DIRIGER_DIR.$this->filename;

        $len= strlen($this->filename);
        $dlen= stripos($this->filename, ".mcrypt") !== false ? 36 : 29;
        
        $this->origen_code= substr($this->filename, $len-$dlen, 2);
        $this->destino_code= substr($this->filename, $len-($dlen-3), 2);
        $this->year= (int)substr($this->filename, $len-($dlen-6), 4);
    }

    /**
     * @param $dir
     * directorio destino, termiona en /
     */
    public function upload($dir) {
        $this->error= null;
      
        $this->filename= $_FILES["lote"]["name"];
        $_url= $_FILES["lote"]["tmp_name"];
        $this->size= ($_FILES["lote"]["size"] / 1024);
        $this->type= $_FILES["lote"]["type"];
        $this->error= $_FILES["lote"]["error"];

        if ( $this->error > 0) {
            echo "Error: ".$this->error."<br />";
            exit;
        }
        else {
            echo "Upload: ".$this->filename."<br />";
            echo "Type: ".$this->type."<br />";
            echo "Size: ".$this->size." Kb<br />";
            echo "Stored in: ".$_url;
        }
        $cp= $dir.$this->filename;

        $result= move_uploaded_file($_url, $cp);
        if (!$result) 
            return "No ha sido posible carggar el archivo";
    }

    public function decrypt_file($_url) {
        $file_read= $_url.$this->filename;
        if (!strstr($file_read, ".mcrypt")) 
            return true;
        
        Tphpcrypt::$key= md5("{$this->origen_code}==>{$this->destino_code}");
        Tphpcrypt::$iv= md5("DirigerPh.D.GeraudisMustelier");  
        
        $fp = fopen($file_read, 'r');
        if (!$fp)
            return false;
        
        $stream= null;
        while (!feof($fp)) 
            $stream.= fread($fp, 8192);
        fclose($fp);

        $cleantext= Tphpcrypt::decryptRJ256($stream);

        $len= strlen($cleantext);
        $ipad= strrpos($cleantext, "@@@@@");
        $cleantext= substr($cleantext, 0, $ipad);
        
        $file_gz= substr($file_read, 0, strlen($file_read)-7);
        $fp = fopen($file_gz, 'wb');
        fwrite($fp, $cleantext);
        
        fclose($fp);
        return true;
    }  
    
    public function uncompress($_url) {
        $error= null;
        $file_read= $_url.$this->filename;
        $len= stripos($file_read, '.mcrypt') ? 7 : 0;
        $file_read= substr($file_read, 0, strlen($file_read) - $len);
      
     // descomprimiendo el fichero compacto y leyendolo     
        $fp = fopen($file_read, 'rb');
        if (!$fp) {
            $error= "Error en intento de acceso al fichero GZ --->".$_url.$this->filename; 
            return $error;
        }

        fseek($fp, -4, SEEK_END);
        $bsize = fread($fp, 4);
        $isize = end(unpack("V", $bsize));
        fclose($fp);

        $fpgz = @gzopen($file_read, "rb");
        if (!$fpgz) {
            $error= "Error de descompreción del fichero GZ --->".$_url.$this->filename; 
            return $error; 
        }

        $this->filename = substr($this->filename, 0, strrpos($this->filename, ".gz"));
        $fp = fopen($_url.$this->filename, "wb");
        if (!$fp) {
            $error= "Error en intento de escritura del fichero XML --->".$this->filename; 
            return $error;
        }

        while (!gzeof($fpgz)) {
            $data = gzgets($fpgz, 4096);
            fwrite($fp, $data);
        }

        gzclose($fpgz);
        fclose($fp);
        return $error;
    }

    public function encrypt_file() {
        Tphpcrypt::$key= md5("{$this->origen_code}==>{$this->destino_code}");
        Tphpcrypt::$iv= md5("DirigerPh.D.GeraudisMustelier");  
      
        $file_read= $this->url.'.gz';
        $fp = fopen($file_read, 'rb');
        
        $stream= null;
        while (!feof($fp)) 
            $stream.= fread($fp, 8192);
        fclose($fp);

        $stream.= "@@@@@"; //marcar final de la cadena
        $crypttext = Tphpcrypt::encryptRJ256($stream);

        $file_mcrypt= $this->url.'.gz.mcrypt';
        $fp = fopen($file_mcrypt, 'wb');
        fwrite($fp, $crypttext);
        
        fclose($fp);
        unlink($file_read);
        
        return true;
    }    
    
    public function compress() {        
        $fp = fopen($this->url, 'rb');
        $file_url_gz= $this->url.'.gz';
        
        if (file_exists($file_url_gz)) 
            unlink($file_url_gz);
        $fp_gz = @fopen ($file_url_gz, "a+");

        while (!feof($fp)) {
            $data = fread($fp, 10485760);
            $gzdata = gzencode($data, 9);
            fwrite($fp_gz, $gzdata);
        }

        fclose($fp);
        fclose($fp_gz);

        unlink($this->url);
    }
    
    public function delete($_url) {
       unlink($_url);
    }
    
    public function create_backup() {
        $ext= $this->if_mcrypt ? ".gz.mcrypt" : ".gz";
        rename(_IMPORT_DIRIGER_DIR.$this->filename.$ext, _IMPORT_DIRIGER_DIR."~".$this->filename.$ext);
        
        unlink(_IMPORT_DIRIGER_DIR.$this->filename);
        if ($this->if_mcrypt) 
            unlink(_IMPORT_DIRIGER_DIR.$this->filename.'.gz');
    }

    public function send_mail($mail_address) {
        global $config;
        
        $this->error= null;
        if (is_null($mail_address) || !if_address_email($mail_address)) 
            return null;
    	
        $body= "TipoDirigerLote:LOTE&{$_SESSION['empresa']}&{$_SESSION['location']}&{$this->cronos}";
        $ext= $this->if_mcrypt ? ".gz.mcrypt" : ".gz";
        
        if (is_null($this->obj_mail)) 
            $this->obj_mail= new Tmail(); //New instance, with exceptions enabled

        $this->obj_mail->DebubString= null;
        
        // $this->obj_mail->From= $_SESSION['email_app'];
        $this->obj_mail->FromName= $body;
        $this->obj_mail->ContentType= 'multipart/mixed';
        $this->obj_mail->clearAttachments();
        $this->obj_mail->AddAttachment($this->url.$ext, $this->filename.$ext);  // add attachments
    	$this->obj_mail->Subject= $body;
    	$this->obj_mail->MsgHTML($body);

        $this->obj_mail->clearAddresses();
        $this->obj_mail->AddAddress($mail_address, $mail_address);

        $result= $this->obj_mail->Send();
        if (!$result) {
            $this->error= $this->obj_mail->DebubString;
        }
        return $this->error;
    }

    public function send_attachment($mail_address, $url, $filename) {
        global $config;

        if (is_null($mail_address) || !if_address_email($mail_address)) 
            return null;
        $this->error= null;
        $body= "TipoDirigerDocAttach:{$_SESSION['empresa']}&{$_SESSION['location']}&$this->cronos";

        if (is_null($this->obj_mail)) 
            $this->obj_mail= new Tmail(); //New instance, with exceptions enabled

        $this->obj_mail->From= $_SESSION['email_app'];
        $this->obj_mail->FromName= $body;

        $this->obj_mail->ContentType= 'multipart/mixed';
        $this->obj_mail->clearAttachments();
        $this->obj_mail->AddAttachment($url, $filename);         // add attachments
        $this->obj_mail->Subject= $body;
        $this->obj_mail->MsgHTML($body);

        $this->obj_mail->AddAddress($mail_address, $mail_address);

        try {
            $this->obj_mail->Send();
        } catch(Exception $e) {
            $this->error= "Se ha producido un error en el intento de conexción al servidor de correo electrónico. ";
        }
        return $this->error;
    }

    public function OpenSMTP() {
        if (!is_null($this->obj_mail)) {
            $this->CloseSMTP();
            $this->obj_mail= null;
        }
        $this->obj_mail= new Tmail();
    }

    public function CloseSMTP() {
        $this->obj_mail->SmtpClose();
        $this->obj_mail= null;
    }

    protected function existAttachment($part, $inbox, $email_number, $date= null) {
        if (isset($part->parts)) {
            foreach ($part->parts as $partOfPart) {
                $this->existAttachment($partOfPart, $inbox, $email_number, $date= null);
        }   }
        else {
            if (isset($part->disposition)) {
                if (strtoupper($part->disposition) == 'ATTACHMENT') {
                    $this->save_attach($inbox, $part->dparameters[0]->value, $email_number, $date);
        }   }   }
    }

    private function save_attach($inbox, $file, $email_number, $date= null) {
        $pfile= fopen(_IMPORT_DIRIGER_DIR.$file,'w');

    	$body= imap_fetchbody($inbox, $email_number, 2);

    	fwrite($pfile, imap_base64($body));
    	fclose($pfile);

        ++$this->cant_files;
        $this->header.= " Lote: ".$file;

        $array= array('name'=>$file, 'header'=>$this->header, 'date'=>$date, 'timestamp'=>strtotime($date));
        $this->array_files[]= $array;
    }
    
    public function dirfiles($path) {
        global $config;

        $dir_handle = @opendir($path) or die("Unable to open $path");
        $sp = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

        $afiles = array();
        $dates= array();

        $i = 0;
        while ($file = readdir($dir_handle)) {
            if ($file == "." || $file == "..") 
                continue;
            ++$i;
            $afiles[$i]['name'] = $file;
            $array = preg_split('/-/', $file, -1, PREG_SPLIT_OFFSET_CAPTURE);

            $year = null;
            $month = null;
            $day = null;
            foreach ($array as $n) {
                if (is_numeric($n[0]) && is_null($year)) 
                    $year = $n[0];
                elseif (is_numeric($n[0]) && is_null($month)) 
                    $month = $n[0];
                elseif (is_numeric($n[0]) && is_null($day)) 
                    $day = $n[0];
            }
            $afiles[$i]['date'] = "{$year}-{$month}-{$day}";
            // echo "<a href=\"$file\">$file</a><br /><br /><br />";
        }

        foreach ($afiles as $key=>$n) 
            $dates[$key]= $afiles[$key]['date'];
        $dates= array_map('strtotime',$dates);
        array_multisort($dates, SORT_NUMERIC, SORT_DESC, $afiles);

        closedir($dir_handle);
        return $afiles;
    } 
    
    public function backup_log_file($filename) {
        $file= _LOG_DIRIGER_DIR.$filename.".log";
        if (!file_exists($file)) 
            return;
        
        $size= ceil(filesize($file) /1024);
        if ($size < 1024) 
            return;
        
        $date= date('Y-m-d');
        rename($file, _LOG_DIRIGER_DIR."$filename-$date.log");
    }
    
    public function clean_log_dir() {
        $dir_handle = @opendir(_LOG_DIRIGER_DIR) or die("Unable to open "._LOG_DIRIGER_DIR);
        while ($file = readdir($dir_handle)) {
            if ($file == "." || $file == "..") 
                continue;
            $date= date('Y-m-d H:i', filemtime(_LOG_DIRIGER_DIR.$file));
            $diff= diffDate(date('Y-m-d H:i'), $date);
            if ($diff['m'] >= 3) 
                unlink (_LOG_DIRIGER_DIR.$file);
        }
    }
}

?>