<?php
/**
 * author Geraudis Mustelier
 * copyright 2019
 */

include_once _ROOT_DIRIGER_DIR."php/config.inc.php";
include_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
include_once "./file.class.php";


class TcleanDisk {
    public $dir;
    public $split;
    public $pattern;

    public $max_size_dir;

    private function get_time($item) {
        $i= stripos($item, $this->pattern);
        if (!$i)
            return false;
        $date_str= substr($item, $i-16, 16);
        $array= preg_split("/$this->split/", $date_str);
        $date= "{$array[0]}-{$array[1]}-{$array[2]} {$array[3]}:{$array[4]}";
        return (int)strtotime($date);
    }

    private function listar(){;
        $pdir = opendir($this->dir);
        $files = array();
        
        while ($item = readdir($pdir)) {
            if ($item != "." && $item != "..")
                if (is_dir($dir.$item)) 
                    $this->listar("$this->dir/$item/");
                else {
                    $date= $this->get_time($item);
                    if (!$date)
                        continue;
                    $files[$date]= $item;
                }    
        }
        closedir($pdir);

        arsort($files);
        return $files;
    }

    private function cleandir() {
        $n= 0;
        $totalsize= 0;
        $deletedsize= 0;
        $remainsize= 0;
        $deletedn= 0;
        $filesdeleted= "";
        $files= $this->listar();

        foreach ($files as $size => $item) {
            ++$n;
            $totalsize+= filesize("$this->dir/$item");
            if ($remainsize <= $this->max_size_dir)
                $remainsize+= filesize("$this->dir/$item");
            else {
                if (unlink("$this->dir/$item")) {
                    ++$deletedn;
                    $deletedsize+= $size;
                    $filesdeleted.= "$item; ";
            }   }
        }

        echo "\nFecha:".date("Y-m-d H:i")."\n";
        echo "\n<p>PATH:$this->dir ===> Eliminados:$deletedn  Borrado:$deletedsize LISTADO:$filesdeleted restantes:$remainsize </p>\n";
    } 
    
    public function cleandik() {
        $this->dir= _DATA_DIRIGER_DIR."export";
        $this->pattern= ".xml";
        $this->split= "_";
        $this->cleandir();

        $this->dir= _DATA_DIRIGER_DIR."import";
        $this->pattern= ".xml";
        $this->split= "_";
        $this->cleandir();

        $this->dir= _DATA_DIRIGER_DIR."sql";
        $this->pattern= ".sql";
        $this->split= "-";
        $this->cleandir();        
    }
            
}
