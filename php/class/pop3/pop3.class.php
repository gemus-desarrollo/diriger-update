<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/10/14
 * Time: 21:12
 */

class POP3 {
    public $host;
    public $login;
    public $password;
    public $port;
    public $protocol;
    public $ifsecure;
    public $outgoing_no_tls;
    public $Debug;
    public $DebubString;

    public $mbox;
    public $struct;
    public $emails_quan = 0; // inbox (or any other box) emails quantity
    public $current_email_index = 0; // current email index
    public $current_attach_index = 2; // current attachment index
    public $attachment = '';
    public $attachment_filename = '';
    public $array_emails;
    public $processed_emails;
    public $attachment_types = array('ATTACHMENT', 'INLINE');

    public $subject;
    public $date;
    public $from;
    public $html, 
            $body;
    private $uid;

    public function POP3() {
        global $config;

        $this->host = $config->incoming_mail_server;
        $this->port = $config->incoming_port;
        $this->protocol = $config->incoming_protocol;
        $this->ifsecure = $config->incoming_ssl;
        $this->outgoing_no_tls = $config->outgoing_no_tls;
        $this->Debug = null;

        $this->login = base64_decode($config->email_login);
        $this->password = base64_decode($config->email_password);
    }

    public function Connect() {
        $protocol= (strtolower($this->protocol) == 'pop3') ? '/pop3' : '/imap';
        $ssl= null;
        $novalidate= $this->outgoing_no_tls ? '/notls' : '/novalidate-cert';

        switch ($this->ifsecure) {
            case 1 :
                $ssl= '/ssl';
                break;
            case 2 : 
                $ssl= '/tls';
                break;
            default :
                $ssl= null;
        }

        $this->host= '{'.$this->host.':'.$this->port.$protocol.$ssl.$novalidate.'}INBOX';
//	 $this->host= "{192.168.2.3/notls}INBOX";
        $this->mbox = imap_open($this->host, $this->login, $this->password);

        if ($this->Debug) {
            $this->DebubString= "connect chain: $this->host";
            echo $this->DebubString;
            
            $error= imap_errors();
            var_dump($error);
            $this->DebubString.= var_export($error, true);
        }
        
        $this->emails_quan= !empty($this->mbox) ? imap_num_msg($this->mbox) : null;
        if (is_null($this->Debug) && is_null($this->emails_quan)) {
            $this->DebubString= "connect chain: $this->host";
            echo $this->DebubString;
            
            $error= imap_errors();
            var_dump($error);   
            $this->DebubString.= var_export($error, true);
        }
        return $this->emails_quan;
    }

    public function list_inbox() {
        if (empty($this->mbox)) 
            return false;
        $list_emails= imap_search($this->mbox, 'ALL');

        $i= 0;
        foreach ($list_emails as $email_number) {
            $overview= imap_fetch_overview($this->mbox, $email_number, 0);

            $this->array_emails[$i]['email_number']   = $email_number;
            $this->array_emails[$i]['uid']  = $overview[0]->uid;
            $this->array_emails[$i]['seen']   = $overview[0]->seen;
            $this->array_emails[$i]['from']   = $overview[0]->from;
            $this->array_emails[$i]['subject']= $overview[0]->subject;
            $this->array_emails[$i]['date']   = $overview[0]->date;
            $this->array_emails[$i]['size']   = $overview[0]->size;
            ++$i;
        }

        return $i;
    }

    public function FetchMail($uid= null) {
        # Following are number to names mappings
        $codes = array("7bit","8bit","binary","base64","quoted-printable","other");
        $stt = array("Text","Multipart","Message","Application","Audio","Image","Video","Other");
        $realdata= null;
        $pictures = 0;
        $html = "";

        $this->uid= !is_null($uid) ? $uid : $this->current_email_index;

        if (empty($this->mbox) || (is_null($uid) && $this->emails_quan == $this->current_email_index)) 
            return false;

        if (is_null($uid)) {
            $this->current_email_index++;
            $this->current_attach_index = 2;
            $uid= $this->current_email_index;
        }

        $header= imap_headerinfo($this->mbox, $uid);
        $this->subject= $header->subject;
        $this->from= $header->fromaddress;
        $this->date= $header->date;

        $this->struct= imap_fetchstructure($this->mbox, $uid, FT_UID);

        $multi= $this->struct->parts;
        $nparts= count($multi);

        # look at the main part of the email, and subparts if they're present
        for ($p=0; $p<=$nparts; $p++) {
            $text =imap_fetchbody($this->mbox, $uid, $p);

            if ($p ==  0) {
                $it = $stt[$this->struct->type];
                $is = ucfirst(strtolower($this->struct->subtype));
                $ie = $codes[$this->struct->encoding];
            } else {
                $it = $stt[$multi[$p-1]->type];
                $is = ucfirst(strtolower($multi[$p-1]->subtype));
                $ie = $codes[$multi[$p-1]->encoding];
            }

            # Report on the mimetype
            $mimetype = "$it/$is";

            # decode content if it's encoded (more types to add later!)
            if ($ie == "base64") {
                $realdata = imap_base64($text);
            }
            if ($ie == "quoted-printable") {
                $realdata = imap_qprint($text);
            }

            # If it's a .jpg image, save it (more types to add later)
            if ($mimetype == "Image/Jpeg") {
                $pictures++;
                $fho = fopen("imx/mp$pictures.jpg","w");
                fputs($fho, $realdata);
                fclose($fho);
                # And put the image in the report, limited in size
                $this->html .= "<img src=/demo/imx/mp$pictures.jpg width=150><br />";
            }

            # Add the start of the text to the message
            $shorttext = substr($text,0,800);
            if (strlen($text) > 800) $shorttext .= " ...\n";
            $this->html .=  nl2br(htmlspecialchars($shorttext))."<br>";
        }

        $this->body = imap_body($this->mbox, $uid);
        $prettydate = date("jS F Y", $header->udate);

        return true;
    }

    public function HasAttachment() {
        return property_exists($this->struct, 'parts');
    }

    public function FetchAttachment() {
        $this->attachment = '';

        if (empty($this->struct)
            || !property_exists($this->struct, 'parts')
            || !array_key_exists($this->current_attach_index - 1, $this->struct->parts)
        ) {
            return false;
        }

        $parts_count = count($this->struct->parts) + 1;

        while (true) {
            if ($this->current_attach_index > $parts_count) 
                return false;

            $part = $this->struct->parts[$this->current_attach_index - 1];

            if (!property_exists($part, 'disposition') || !in_array(fullUpper($part->disposition), $this->attachment_types)) {
                $this->current_attach_index++;
                continue;
            }

            if (!empty($part->parameters)) {
                $parameters = $part->parameters;
                $fattr = 'NAME';
            } else {
                $parameters = $part->dparameters;
                $fattr = 'FILENAME';
            }

            foreach ($parameters as $parameter) {
                if ($parameter->attribute == $fattr) {
                    $filename = $parameter->value;
                }
            }

            if (empty($filename)) {
                $this->current_attach_index++;
                continue;
            }

            $decoded = imap_mime_header_decode($filename);
            $filename = '';

            foreach ($decoded as $dec) {
                if (!empty($dec->text)) {
                    $encoding = $dec->charset;
                    $fpart = $dec->text;
                    $filename .= $fpart;
                }
            }
            $this->attachment_filename = $filename;

            $this->attachment = imap_fetchbody($this->mbox, $this->current_email_index, $this->current_attach_index);
            $this->attachment = base64_decode($this->attachment);
            $this->current_attach_index++;

            if (empty($this->attachment)) 
                return false;
            return true;
        }

    }

    public function SaveAttachment($save_path, $new_filename = null) {
        if (!empty($this->attachment)) {
            $save_to = $save_path.(!empty($new_filename) ? $new_filename : $this->attachment_filename);
            $bytes_quan = file_put_contents($save_to, $this->attachment);

            if ($bytes_quan !== false) {
                return $save_to;
            }
        }

        return null;
    }

    function GetAttachmentFilename() {
        return $this->attachment_filename;
    }

    public function DeleteMail() {
        if ($this->current_email_index > 0) {
            imap_delete($this->mbox, $this->current_email_index);
        }
    }
    

    function Close() {
        $info= null;
        if ($this->mbox) {
            imap_expunge($this->mbox);
            $info= imap_mailboxmsginfo($this->mbox);
            imap_close($this->mbox, CL_EXPUNGE);
        }
        
       echo "\r\n Mensajes aun en el buzon: ".$info->Nmsgs."\r\n";
    }
} 