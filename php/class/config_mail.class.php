<?php

/**
 * Description of config_mail
 *
 * @author mustelier
 */

$not_load_config_class= true;
include_once "config.class.php";

class _Tconfig_mail extends _Tconfig {

    public function __construct($clink = null) {
        parent::__construct($clink);
    }

    public function get_servers() {
        $this->read_file();

        $this->email_login= base64_decode($this->email_login);
        $this->email_password= base64_decode($this->email_password);
        $this->email_login_smtp= base64_decode($this->email_login_smtp);
        $this->email_password_smtp= base64_decode($this->email_password_smtp);
    }

    public function set_servers() {
        $this->email_user_same_pop3_smtp= $_POST['email_user_same_pop3_smtp'];

        $this->email_login= base64_encode($_POST['email_login']);
        $this->email_password= base64_encode($_POST['email_password']);
        $this->email_login_smtp= base64_encode($_POST['email_login_smtp']);
        $this->email_password_smtp= base64_encode($_POST['email_password_smtp']);

        $this->outgoing_port = $_POST['outgoing_port'];
        $this->incoming_protocol = $_POST['incoming_protocol'];
        $this->incoming_port = $_POST['incoming_port'];

        $this->outgoing_ssl = $_POST['outgoing_ssl'];
        $this->incoming_ssl = $_POST['incoming_ssl'];
        $this->smtp_auth = $_POST['smtp_auth'];
        $this->smtp_auth_tls = $_POST['smtp_auth_tls'];
        $this->http_access= $_POST['http_access'];
        $this->outgoing_no_tls = $_POST['outgoing_no_tls'];

        $this->mail_method = $_POST['mail_method'];
        $this->hostname = $_POST['hostname'];
        $this->incoming_mail_server = $_POST['incoming_mail_server'];
        $this->outgoing_mail_server = $_POST['outgoing_mail_server'];

        $this->off_mail_server = $_POST['off_mail_server'];

        $this->type_synchro= $_POST['type_synchro'];
        $this->time_synchro= ampm2odbc($_POST['time_synchro']);
        $this->timesynchro= ampm2odbc($_POST['timesynchro']);

        $this->save_param(false);
    }
}

