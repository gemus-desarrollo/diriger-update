<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

include_once "phpmailer/class.smtp.php";
include_once "phpmailer/class.phpmailer.php";

 class Tmail extends PHPMailer {
    public $className;
    public $evento,
            $usr_tarjet,
            $responsable,
            $cargo,
            $periodicidad,
            $descripcion,
            $lugar,
            $observacion;
    public $fecha_inicio,
            $fecha_fin;

    public $DebubString;


    public function __construct() {
        global $config;

        parent::__construct(); //New instance, with exceptions enabled

    	$this->SMTPAuth= $config->smtp_auth;            // enable SMTP authentication
    	$this->SMTPAutoTLS= $config->smtp_auth_tls;     // Whether to enable TLS encryption automatically if a server supports it, even if `SMTPSecure` is not set to 'tls'.
        $this->ContentType= 'multipart/mixed';

    	$this->Port= $config->outgoing_port;

        $this->DebubString= null;

        switch ($config->mail_method) {
            case 'smtp' :
                $this->IsSMTP();
                break;
            case 'mail' :
                $this->isMail();
                break;
            case 'sendmail' :
                $this->isSendmail();
                break;
            case 'qmail' :
                $this->isQmail();
                break;
            default :
                $this->IsSMTP();
        }
        switch ($config->outgoing_ssl) {
            case 0 :
                $this->SMTPSecure= 'smtp';
                break;
            case 1 :
                $this->SMTPSecure= 'ssl';
                break;
            case 2 :
                $this->SMTPSecure= 'tls';
                break;
            default :
                $this->SMTPSecure= 'smtp';
        }

        $this->Host       = $config->outgoing_mail_server;    // SMTP server
        $this->Username   = base64_decode($config->email_login_smtp);    // SMTP server username
    	$this->Password   = base64_decode($config->email_password_smtp);    // SMTP server password
        $this->Helo       = $config->hostname;

        $domain= substr($_SESSION['email_app'], stripos($_SESSION['email_app'], '@')+1);

        $email_login= base64_decode($config->email_login);
        $email_login_smtp= base64_decode($config->email_login_smtp);

        $email_login= strpos($email_login, '@') ? $email_login : "{$email_login}@{$domain}";
        $email_login_smtp= strpos($email_login_smtp, '@') ? $email_login_smtp : "{$email_login_smtp}@{$domain}";

        $this->From       = $email_login;
        $this->FromName   = "Sistema Informatico Diriger {$_SESSION['entity_nombre']}";

        //Custom connection options
        $this->SMTPOptions = array (
            'ssl' => array(
                'verify_peer'  => false,
                'verify_depth' => false,
                'allow_self_signed' => true
            )
        );

        $this->SMTPKeepAlive = true;

        //Ask for HTML-friendly debug output
        $this->Debugoutput = 'html';

        //Enable SMTP debugging
        // `0` No output
        // `1` Commands
        // `2` Data and commands
        // `3` As 2 plus connection status
        // `4` Low-level data output.
        $this->SMTPDebug = 4;

        $this->Debugoutput = function($str, $level) {
            $this->DebubString.= "$str\n";
        };

        $this->CharSet= "utf-8";
    }

    public function _AddReplayTo($mail, $name) {
        if (!is_null($mail))
            $this->AddReplyTo($mail, $name);
    }
    public function _AddAddress($mail, $name) {
        if (!is_null($mail))
            $this->AddAddress($mail, $name);
    }
    public function _SetFromName($from= null, $name= null) {
        $this->From= !is_null($from) ? $from : $_SESSION['email_app'];
        $this->FromName= !is_null($name) ? $name : "Sistema Informático Diriger {$_SESSION['entity_nombre']}";
    }

    public function body_event($option) {
        global $reapet_evento_array;
        $this->Subject = !empty($this->Subject) ? $this->Subject : "Diriger le informa: $option actividad.";

        $body= "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        $body.= "<title>AVISO DE Diriger</title><style type='text/css'>";
        $body.= "body,td,th{background-color:#F0F3FF;font-family:Tahoma,Verdana,Impact; font-size:11px;}";
        $body.= ".style2 {color:#B90000;font-weight:bold;font-style:italic;top:auto;}";
        $body.= ".style3 {color:#5151FF;font-weight:bold;font-weight:bold;}";
        $body.= "</style></head><body><table widtd='100%' border='0' cellpadding=2>";
        $body.= "      <tr><td colspan='4' valign=middle><div align=center><strong>{$_SESSION['entity_nombre']}</strong></div></td></tr>";
        $body.= "      <tr><td colspan='4'><hr /></td></tr>";
        if ($this->className == "Tauditoria")
            $body.= "      <tr><td colspan='4' align=left class=style3>$option Auditoría:</b></td></tr>";
        else
            $body.= "      <tr><td colspan='4' align=left class=style3>$option Evento:</b></td></tr>";
        $body.= "      <tr><td colspan='4' align='left'>$this->evento</td></tr>";
        $body.= "     <tr><td colspan=4><hr /></td></tr>";
        $body.= "      <tr><td class=style3>Lugar o Alcance: </td><td colspan=3>$this->lugar</td></tr>";
        $body.= "      <tr><td class='style3'>Fecha/Hora Inicio: </td><td>$this->fecha_inicio</td><td class='style3'>&nbsp;&nbsp;Fecha/Hora Fin: </td><td>$this->fecha_fin</td></tr>";
        $body.= "      <tr><td class='style3'>Periodicidad</td><td colspan=3>{$reapet_evento_array[$this->periodicidad]}</td></tr>";
        $body.= "      <tr><td class='style3'>Responsable:</td><td colspan=3>$this->responsable, $this->cargo</td></tr>";
        $body.= "      <tr><td class='style3'>Descripci&oacute;n:</td><td colspan=3>$this->descripcion</td></tr>";
        $body.= "      <tr><td colspan=4><hr /></td></tr>";

        $body.= "	  <tr><td class=style2 colspan=4>Diriger versión "._VERSION_DIRIGER. "</td></tr>";

        $body.= "     <tr><td colspan=4>".date('d/m/Y H:i')."</td></tr>";
        $body.= "</table></body></html>";

        $this->MsgHTML($body);
    }

    public function body_event_mail($option) {
        $this->Subject = !empty($this->Subject) ? $this->Subject : "Diriger le informa el ".fullUpper($option)." de una actividad.";

        $body= "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        $body.= "<title>AVISO DE Dirger</title><style>";
        $body.= "body,td,th{background-color:#F0F3FF;font-family:Tahoma,Verdana,Impact; font-size:11px;}";
        $body.= ".style2 {color:#B90000;font-weight:bold;font-style:italic;top:auto;}";
        $body.= ".style3 {color:#5151FF;font-weight:bold;font-weight:bold;}";
        $body.= "</style></head><body>";
        $body.= "<strong align=center>{$_SESSION['entity_nombre']}</strong><br/>";
        $body.= "<p align=center>SE LE INFORMA QUE AL USUARIO $this->usr_target SE LE HA $option LA TAREA O ACTIVIDAD.</p><br/>";
        $body.= "  <strong>Datos de la tarea:</strong><br />";
        $body.= "  <span class=style3>Tarea:</span>$this->evento<br/><br/>";
        $body.= "  <span class=style3>Fecha: </span><br/>$this->fecha_inicio<br/>";
        $body.= "  <span class=style3>Lugar:</span><br/>$this->lugar<br/>";
        $body.= "  <span class=style3>Descripción:</span><br/>$this->descripcion<br/>";
        $body.= "  <strong>Fueron dados los siguientes argumentos:</strong><br />$this->observacion<br />";

        $body.= "  <span class=style2>Diriger versión "._VERSION_DIRIGER. "</span></p></body></html>";

        $this->MsgHTML($body);
    }

    public function body_event_mail_block($option) {
        $this->Subject = !empty($this->Subject) ? $this->Subject : "Diriger le informa el ".fullUpper($option)." de una actividad.";

        $body= "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        $body.= "<title>AVISO DE Dirger</title><style>";
        $body.= "body,td,th{background-color:#F0F3FF;font-family:Tahoma,Verdana,Impact; font-size:11px;}";
        $body.= ".style2 {color:#B90000;font-weight:bold;font-style:italic;top:auto;}";
        $body.= ".style3 {color:#5151FF;font-weight:bold;font-weight:bold;}";
        $body.= "</style></head><body>";
        $body.= "<strong align=center>{$_SESSION['entity_nombre']}</strong><br/>";
        $body.= "<p align=center>SE LE INFORMA QUE AL USUARIO $this->usr_target SE LE HA <strong>$option</strong> LA TAREA O ACTIVIDAD.</p>";

        $body.= "<br/><br/><strong>ACTIVIDAD:</strong> <p>$this->evento</p>";

        if (date('Y-m-d', strtotime($this->fecha_inicio)) != date('Y-m-d', strtotime($this->fecha_fin)))
            $body.= "<strong>FECHA:</strong><p>Todas las fechas desde el día $this->fecha_inicio hasta el día $this->fecha_fin</p>";
        else
           $body.= "<span class=style3>FECHA:</span><p>Para la fecha $this->fecha_inicio</p>";

        $body.= "<strong>Fueron dados los siguientes argumentos:</strong><p>$this->observacion</p>";

        $body.= "<p><span class=style2>Diriger versión "._VERSION_DIRIGER. "</span></p></body></html>";

        $this->MsgHTML($body);
    }

    public function body_plan_evalute($value, $_value, $observacion= null) {
        $this->Subject = !empty($this->Subject) ? $this->Subject : "Diriger le informa incoherencia en la evaluación del PLan Individual de $this->usr_tarjet";

        $body= "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        $body.= "<title>AVISO DE Dirger</title><style>";
        $body.= "body,td,th{background-color:#F0F3FF;font-family:Tahoma,Verdana,Impact; font-size:11px;}";
        $body.= ".style2 {color:#B90000;font-weight:bold;font-style:italic;top:auto;}";
        $body.= ".style3 {color:#5151FF;font-weight:bold;font-weight:bold;}";
        $body.= "</style></head><body>";
        $body.= "<strong align=center>{$_SESSION['entity_nombre']}</strong><br/>";
        $body.= "<p align=center>SE LE INFORMA QUE AL USUARIO $this->usr_target SE LE HA DADO UNA EVALUACIÓN DEL DESEMPENO INCOHERENTE.</p>  <br />";
        $body.= "  <strong>Datos de la evaluación:</strong><br />";
        $body.= "  <span class=style3>Mes:</span>$this->month<br/><br/>";
        $body.= "  <span class=style3>Año: </span><br/>$this->year<br/>";
        $body.= "  <span class=style3>Evaluación propuesta por el sistema o asignada anteriormente:</span><br/>".$value."<br/>";
        $body.= "  <span class=style3>Nueva evaluación asignada:</span><br/>$_value Asignada por: $this->responsable<br/>";
        $body.= "  <strong>Fueron dados los siguientes argumentos:</strong><br />$this->observacion<br />";
        $body.= "  <strong>Diriger reporta:</strong><br />$observacion<br />";

        $body.= "<p><span class=style2>Diriger versión "._VERSION_DIRIGER. "</span></p></body></html>";

        $this->MsgHTML($body);
    }
 }

function if_address_email($email){
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? FALSE : TRUE;
}

?>
