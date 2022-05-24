<?php
/**
 * author Geraudis Mustelier
 * copyright 2019
 */

include_once "../lote/php/import.interface.php";

class TcleanMail extends Timport {
    protected $date;
    public $cant_files;
    
    public function __construct($clink= null){
        parent::__construct($clink);
    }
    
    public function read_mails() {
        global $config;
        
        $this->array_mails_to_delete= Array();
        $this->error= null;
        
        $this->pop3= new POP3();
        $error= $this->OpenPOP3();
        
        if (empty($this->pop3->emails_quan)) 
            return null;
        
        reset($this->pop3->array_emails);
        foreach ($this->pop3->array_emails as $email) {
            $delete= false;
            if (stripos($email['subject'], "TipoDirigerLote") === false) {
                $array= array('name'=>$from[1], 'id_code'=>$from[2], 'file'=>$from[5], 'date'=>$from[4], 'header'=>$this->header,
                       'timestamp'=>strtotime($from[4]), 'email_number'=>$email['uid'], 'use_lote'=>$use_lote);
                $this->array_files[]= $array;
                ++$this->cant_files;
                continue;
            }
            
            $delete= false;
            if ($this->pop3->HasAttachment()) {
                if ($this->pop3->FetchAttachment()) {
                    $from= $this->get_attachment_lote_ref();	
                    if (is_null($from)) 
                        $delete= true;					
                    if ($this->validate_proceso($from[3]) == 0) 
                        $delete= true;

                    $last_date= $this->get_last_date_import($from[2]);
  
                    if ($delete) {
                        $array= array('name'=>$from[1], 'id_code'=>$from[2], 'file'=>$from[5], 'date'=>$from[4], 'header'=>$this->header,
                                'timestamp'=>strtotime($from[4]), 'email_number'=>$email['uid'], 'use_lote'=>$use_lote);
                        $this->array_files[]= $array;

                        ++$this->cant_files;
        }   }   }   }   
    }
    
    function delete_mails() {
        reset($this->array_files);
        foreach ($this->array_files as $email) {
            $this->DeleteMail($email['email_number']);
        }
    }
    
}    
