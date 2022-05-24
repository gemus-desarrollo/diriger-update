<?php
include_once "../../../php/class/base.class.php";

class Tread extends Tbase {
    private $uplink;

    private $obj;
    private $array_mails_to_delete;
    private $array_files;

    public $signal;
    public $error;

    private $total_error;

    public function __construct($uplink){
        Tbase::__construct($uplink);

        $this->obj= new Timport($uplink);
        $this->action= 'import';
        $this->divout= "win-log";

        $this->uplink= $uplink;
        $this->_Init();
    }

    public function _Init() {
        global $uplink;
        global $signal;
        global $execfromshell;
        global $observacion;

        $this->uplink= $uplink;
        $this->signal= $signal;
        $this->observacion= $observacion;
        $this->execfromshel= $execfromshell;
    }

    public function read_from_mails () {
        $this->array_mails_to_delete= Array();
        $this->error= null;

        bar_progressCSS(0, "Descargando ficheros lotes adjuntos a los correos electrónicos  ... ", 0.3);
        $this->error= $this->obj->OpenPOP3();

        if (is_null($this->error))
            $this->error= $this->obj->get_lotes();

        if (!is_null($this->error)) {
            if (!$this->execfromshell) {
                $text= textparse($this->error);
                if ($_SESSION['output_signal'] != 'shell')
                    writeLog(date('Y-m-d H:i'), "<div class='alert alert-danger text'>{$text}</div>", 'winlog');
                else
                    echo "\nERROR:".date('Y-m-d H:i')." ---> {$this->error}\n";
            } else
                point_progress($this->error, 0, 0);
        }

        return $this->error;
    }

    public function import_attachment () {
        $text= "Cargando al servidor documentos recividos .......";
        bar_progressCSS(0, $text, 0.7, 0);
        $this->obj->get_documentos();

        $error= null;
        $this->total_error= $this->error;
        if (is_null($this->total_error)) {
            $i= 0;
            foreach ($this->obj->array_files as $file) {
                ++$i;
                $found= null;
                if ($file['use_lote']) {
                    bar_progressCSS(0, "Procesando lote ".$file['name']." .................  ", (float)$i/$this->obj->cant_files);
                    $found= !$this->init_import($file);

                    if (!$found) {
                        $error= $this->import_lote($file['file']);

                        if (!empty($error)) {
                            $this->total_error.= "<br/><br/>.$error";

                            if (!$this->execfromshell) {
                                $text= textparse($error);
                                if ($_SESSION['output_signal'] != 'shell')
                                    writeLog(date('Y-m-d H:i'), "<div class='alert alert-danger text'>{$text}</div>", 'winlog');
                                else
                                    echo "\nERROR:".date('Y-m-d H:i')." ---> {$error}\n";
                }   }   }   }

                if (is_null($error) || $found) {
                    $this->obj->DeleteMail($file['email_number']);
                    $this->array_mails_to_delete[]= $file['file'];
                }
            }

            if (is_null($this->total_error))
                bar_progressCSS(0, "Al parecer todo la importaciones (email) han sido correctas", 0.8);
            else
                bar_progressCSS(0, "Al parecer NO todos los lotes han podido ser importados", 1);

            return $this->total_error;
        }

        $this->obj->ClosePOP3();

        return $error;
    }

    /**
     * Borrado del buzon los ficheros ya descargados
     */
    public function delete_mails() {
        $i= 0;
        if (!is_null($this->total_error))
            return;

        bar_progressCSS(0, "Limpiando el buzón de correos ... ", 0.8);

        $count= $this->obj->cant_files;
        reset($this->obj->array_files);
        foreach ($this->obj->array_files as $file) {
            ++$i;
            if (array_search($file['file'], $this->array_mails_to_delete) !== false)
                $this->obj->DeleteMail($file['email_number']);
            bar_progressCSS(1, "Borrando adjuntos de lotes anteriores: {$file['file']}", (float)$i/$count);
        }
        $this->obj->ClosePOP3();

        bar_progressCSS(0, "Limpiando el buzón de correos", 1);
    }

    /**
     * Borrado del disco duro los ficheros ya descargados
     */
    public function cleanDisk () {
        global $config;

        bar_progressCSS(0,"Limpiando disco duro. Liberando espacio ... ", 0.9);
        $path= _IMPORT_DIRIGER_DIR;
        $afiles= $this->obj->dirfiles($path);
        $count= count($afiles);
        $nfiles= $count;

        $sp = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

        $i= 0;
        foreach ($afiles as $file) {
            ++$i;
            $diff = s_datediff('m', date_create($file['date']), date_create(date('Y-m-d')));
            if ($diff > $config->monthpurge || $nfiles > 2880) {
                --$nfiles;
                bar_progressCSS(1, "Borrando ficheros de lotes anteriores: {$file['name']}", (float)$i/$count);
                unlink("{$path}{$sp}{$file['name']}");
        }   }
    }

    private function init_import($file= null) {
        if (is_null($this->obj))
            $this->obj= new Timport($this->uplink);
        $this->obj->action= 'import';
        $this->obj->divout= "win-log";

        if ($this->obj->if_lote_loaded($file)) {
            $msgshell= "Este lote: $file ya ha sido leido y cargado. ";
            $text= textparse($msgshell);

            if ($_SESSION['output_signal'] != 'shell')
                writeLog(date('Y-m-d H:i'), "<div class='alert alert-danger text'>$text</div>", 'win-log');
            else
                echo "\nERROR:".date('Y-m-d H:i')." ---> {$msgshell}\n";

            bar_progressCSS(0, "Este lote ya habia sido procesado ", 1, 0);
            return false;
        }

        return true;
    }

    public function import_lote($file) {
        $this->debug_time('import_lote');
        $this->action= "import";
        $this->obj->action= $this->action;
        $this->obj->divout= $this->divout;
        
        $this->obj->set_file_url($file);
        $this->obj->SetObservacion($this->observacion);

        if ($this->signal == 'shell') {
            $msgshell= "\r\nCargando lote: $file \r\n Inicio:".date('Y-m-d H:i:s');
            bar_progressCSS(0, "$msgshell ... ", 0.1, 0);
        }

        $text= "Descomprimiendo archivo .......";
        bar_progressCSS(0, $text, 0.2, 0);

        $result= true;
        $this->obj->if_mcrypt= strpos($file, ".mcrypt") ? true : false;
        if ($this->obj->if_mcrypt)
            $result= $this->obj->decrypt_file(_IMPORT_DIRIGER_DIR);

        if (!$result) {
            $text= "Error de configuración. No es posible desencriptar el lote.";
            $error= $text;
            bar_progressCSS(0, $text, 1, 0);
            if (!is_null($error))
                return fix_error($error, $text, $this->obj->divout);
        }

        $error= $this->obj->uncompress(_IMPORT_DIRIGER_DIR);
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        $text= "Creando tablas temporales .......";
        bar_progressCSS(0, $text, 0.3, 0);
        $error= $this->obj->set_import();
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        $text= "Leyendo archivo .......";
        bar_progressCSS(0, $text, 0.5, 0);
        $error= $this->obj->read_lote();
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        $text= "Cargando datos desde el archivo lote al servidor.......";
        bar_progressCSS(0, $text, 0.7, 0);

        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);

        $error= $this->obj->upload_data();
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        $this->end_lote();
        $this->debug_time('import_lote');
    }

    public function end_lote() {
        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);

        $text= "Resguardando archivo lote .......";
        bar_progressCSS(0, $text, 0.8, 0);
        $error= $this->obj->create_backup();
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        $this->uplink->RefreshLink();
        $this->obj->SetLink($this->uplink);

        $this->obj->action= "import";
        $error= $this->obj->_reg();
        if (!is_null($error))
            return fix_error($error, $text, $this->obj->divout);

        bar_progressCSS(0, "Nada más que hacer ", 1, 0);
        return $error;
    }

}









