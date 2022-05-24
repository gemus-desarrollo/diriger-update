<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */
 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
?>

<div id="alert-loading" class="center-block">
    <div class="alert alert-info">
        <img src="../img/loading.gif" width="25" height="25" alt="cargando..." />  
        Por favor espere.....
    </div>
</div>

<?php
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../php/class/phpmailer/class.smtp.php";
require_once "../../php/class/phpmailer/class.phpmailer.php";
require_once "../../php/class/pop3/pop3.class.php";

$test_pop3= $_GET['test'];

$email_login= urldecode($_GET['email_login']);
$email_password= urldecode($_GET['email_password']);

$email_login_smtp= urldecode($_GET['email_login_smtp']);
$email_password_smtp= urldecode($_GET['email_password_smtp']);

$outgoing_port= $_GET['outgoing_port'];
$outgoing_ssl= !empty($_GET['outgoing_ssl']) ? $_GET['outgoing_ssl'] : 0;
$incoming_protocol= $_GET['incoming_protocol'];
$incoming_port= $_GET['incoming_port'];
$incoming_ssl= $_GET['incoming_ssl'];

$mail_method= $_GET['mail_method'];
$smtp_auth= $_GET['smtp_auth'];
$smtp_auth_tls= $_GET['smtp_auth_tls'];

$outgoing_no_tls= $_GET['outgoing_no_tls'];

$hostname= urldecode($_GET['hostname']);
$incoming_mail_server= urldecode($_GET['incoming_mail_server']);
$outgoing_mail_server= urldecode($_GET['outgoing_mail_server']); 

$error= null;
$cant= 0;

$domain= $_SESSION['email_app'];
$domain= substr($domain, stripos($domain, '@')+1);

if (!$test_pop3) {	
    $mail = new PHPMailer(); //New instance, with exceptions enabled

    switch ($mail_method) {
        case 'smtp' : 
            $mail->IsSMTP();
            break;
        case 'mail' : 
            $mail->isMail();
            break;
        case 'sendmail' : 
            $mail->isSendmail();
            break;
        case 'qmail' : 
            $mail->isQmail();
            break;
        default : 
            $mail->IsSMTP();
    }

    $mail->SMTPAuth= $smtp_auth == 1 ? true : false;                       // enable SMTP authentication
    $mail->SMTPAutoTLS= $smtp_auth_tls == 1 ? true : false;                // Whether to enable TLS encryption automatically if a server supports it, even if `SMTPSecure` is not set to 'tls'

    switch ($outgoing_ssl) {
        case 0 : 
            $mail->SMTPSecure= 'smtp'; 
            break;
        case 1 : 
            $mail->SMTPSecure= 'ssl'; 
            break;
        case 2 : 
            $mail->SMTPSecure= 'tls'; 
            break;
        default : 
            $mail->SMTPSecure= 'smtp';
    }

    $mail->Port= $outgoing_port;
    $mail->Host= $outgoing_mail_server;    // SMTP server
    $mail->Helo= $hostname;
    $mail->Password= !empty($email_password_smtp) ? $email_password_smtp : null;      // SMTP server password
    $mail->Username= !empty($email_login_smtp) ? $email_login_smtp : null;            // SMTP server username   
    
    //Custom connection options
    $mail->SMTPOptions = array (
        'ssl' => array(
            'verify_peer'  => false,
            'verify_depth' => false,
            'allow_self_signed' => true
        )
    );

    $email_login= strpos($email_login, '@') ? $email_login : "{$email_login}@{$domain}";
    $email_login_smtp= strpos($email_login_smtp, '@') ? $email_login_smtp : "{$email_login_smtp}@{$domain}";
    
    $mail->AddReplyTo($email_login);
    $mail->AddAddress($email_login_smtp);

    $mail->From       = $email_login_smtp;
    $mail->FromName   = "Sistema Informatico Diriger {$_SESSION['empresa']}";        
    $mail->Subject    = "Prueba de envio de correo. Diriger";

    $body = "Origen: {$_SESSION['empresa']}  Fecha: ".date('d/m/Y H:i:s')."\n\n";
    $mail->MsgHTML($body);
    $mail->AltBody =  $body;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    //Enable SMTP debugging
    // `0` No output
    // `1` Commands
    // `2` Data and commands
    // `3` As 2 plus connection status
    // `4` Low-level data output.
    $mail->SMTPDebug = 4;
    /*
    $mail->Debugoutput = function($str, $level) {
        echo "$str <br/>";
    };
   */
    
    $mail->addAttachment('../../tools/test_file_attachment.xml.gz');    // Optional name  
    
    $result= $mail->Send();
    // echo $result ? "<p>Todo OK</p>" : "<p>Fallo de envio</p>";
    
}

if ($test_pop3) {
    $mail= new POP3();

    $mail->host      = $incoming_mail_server;
    $mail->port      = $incoming_port;
    $mail->protocol  = $incoming_protocol;
    $mail->ifsecure  = $incoming_ssl;
    $mail->outgoing_no_tls= $outgoing_no_tls;
    $mail->Debug= true;
    
    $mail->login    = $email_login;
    $mail->password = $email_password;

    $result= $mail->Connect();
}

sleep(5);
?>

<script type="text/javascript">
    $("#alert-loading").hide();
</script>

<?php if ((!$result && !$test_pop3) || (is_null($result) && $test_pop3)) { ?>
    <div class="alert alert-danger">
        ERROR: El servidor de correo no responde. No se ha establecido la conexión. <?=$result?>
    </div>
<?php } else { ?>
    <?php if (!$test_pop3) { ?>
        <div class="alert alert-success">
            OK: El servidor ha respondido correctamente. El mensaje de prueba ha sido enviado. Por favor, revice el buzón de correo de Diriger.
        </div>
    <?php } else { ?>
        <div class="alert alert-success">
            OK: El servidor ha respondido correctamente. Hay <?=$result?> mensajes en el buzón de Diriger para ser leidos.
        </div>
<?php } } ?>

  <div id="_submit" class="submit btn-block" align="center">
      <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
  </div>

<?php
!$test_pop3 ? $mail->smtpClose() : $mail->Close();
?>