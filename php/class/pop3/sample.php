<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/10/14
 * Time: 21:15
 */



//$hostname = '{192.168.2.3/notls}INBOX';

$hostname = '{192.168.2.3:110/pop3/novalidate-cert}INBOX';
$username = 'diriger@eti.biocubafarma.cu';
$password = 'D1r1g3r';
 
 
$inbox = imap_open($hostname,$username,$password) or die('Ha fallado la conexi�n: ' . imap_last_error());
 
 
$emails = imap_search($inbox,'ALL');
 
if ($emails) {
   
  $salida = '';
   
  foreach ($emails as $email_number) {    
    $overview = imap_fetch_overview($inbox,$email_number,0);
    $salida.= '<p-->Tema: '.$overview[0]->subject.'';
    $salida.= 'De: '.$overview[0]->from.'<p></p>';    
  }
   
  echo $salida;
 
} 
 
imap_close($inbox);




// This script saves all attachments from all emails into a $save_to folder with original filenames
/*
require './pop3.class.php";

$save_to = '/tmp';

$mbox = new POP3();

if (!$mbox->Connect()) {
    die('Unable to establish connection with mailbox');
}

while ($mbox->FetchMail()) {
    if ($mbox->HasAttachment()) {
        while ($mbox->FetchAttachment()) {
            $data = $mbox->SaveAttachment($save_to);
        }
    }

    //$mbox->DeleteMail();
}

$mbox->Close();
*/
?>