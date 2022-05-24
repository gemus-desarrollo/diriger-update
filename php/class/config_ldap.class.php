<?php

/**
 * Description of config_ldap
 *
 * @author PhD. Geraudis Mustelier
 * Date: 12/12/2019
 * Time: 11:00 AM
 */

$not_load_config_class= true;
include_once "config.class.php";

class _Tconfig_ldap extends _Tconfig {
    public $array_ldap_servers;
    public $cant_ldap_servers;

    public function __construct($clink = null) {
        parent::__construct($clink);
        $this->clink= $clink;
    }

    public function get_servers() {
        $this->read_file();

        $this->array_ldap_servers= !empty($this->ldap_nameserver) ? array() : null;
        if (is_null($this->array_ldap_servers))
            return;

        $servers= explode('||',$this->ldap_nameserver);

        $i= 0;
        foreach ($servers as $srv) {
            if (!strlen($srv))
                continue;

            $credential= explode('|',$srv);

            list($tmp, $id, $ip, $domain, $port, $tls, $admin, $passwd, $cn, $utf8, $ssl, $use_radius_login,
                    $servers_radius, $secret, $admin_radius, $passwd_radius, $use_ldap_not_login)= $credential;

            $array= array('proceso'=> $id, 'servers'=> base64_decode($ip), 'domain'=> base64_decode($domain),
                    'port'=> base64_decode($port), 'ssl'=> boolean($ssl), 'tls'=> boolean($tls), 'utf8'=>boolean($utf8),
                    'admin'=> base64_decode(isNULL_str($admin)), 'passwd'=> base64_decode(isNULL_str($passwd)),
					'cn'=> base64_decode(isNULL_str($cn)), 'servers_radius'=> base64_decode(isNULL_str($servers_radius)),
					'secret'=> base64_decode(isNULL_str($secret)), 'admin_radius'=> base64_decode(isNULL_str($admin_radius)),
					'passwd_radius'=> base64_decode(isNULL_str($passwd_radius)),
                    'use_radius_login'=> boolean($use_radius_login), 'use_ldap_not_login'=> boolean($use_ldap_not_login));
                $this->array_ldap_servers[$i++]= $array;
        }

        $this->cant_ldap_servers= $i;
    }

    public function set_servers() {
        $this->ldap_nameserver= null;
        $this->cant_ldap_servers= $_POST['cant_'];

        $this->block_no_ldap_login= boolean($_POST['block_no_ldap_login']);
        $this->ldap_login= boolean($_POST['ldap_login']);
        $this->mail_use_ldap= boolean($_POST['mail_use_ldap']);

        if ($this->block_no_ldap_login)
            $this->clean_password ();

        for ($i= 0; $i <= $this->cant_ldap_servers; $i++) {
            if (empty($_POST['proceso'.$i]) || !boolean($_POST['proceso'.$i]))
                continue;

            $id= $_POST['proceso'.$i];
            $servers= base64_encode(trim($_POST['servers'.$i]));
            $domain= base64_encode(trim($_POST['domain'.$i]));
            $port= base64_encode($_POST['port'.$i]);

            $tls= $_POST['ldap_tls'.$i] ? "true" : "false";
            $ssl= $_POST['ldap_ssl'.$i] ? "true" : "false";
            $utf8= $_POST['ldap_utf8'.$i] ? "true" : "false";

            $admin= base64_encode($_POST['admin'.$i]);
            $passwd= base64_encode($_POST['passwd'.$i]);
            $cn= base64_encode($_POST['cn'.$i]);

            $use_radius_login= $_POST['use_radius_login'.$i] ? "true" : "false";
            $servers_radius= !empty($_POST['servers_radius'.$i]) ? base64_encode($_POST['servers_radius'.$i]) : 'NULL';
            $secret= !empty($_POST['secret'.$i]) ? base64_encode($_POST['secret'.$i]) : 'NULL';
            $admin_radius= !empty($_POST['admin_radius'.$i]) ? base64_encode($_POST['admin_radius'.$i]) : 'NULL';
            $passwd_radius= !empty($_POST['passwd_radius'.$i]) ? base64_encode($_POST['passwd_radius'.$i]) : 'NULL';
            $use_ldap_not_login= $_POST['use_ldap_not_login'.$i] ? "true" : "false";

            $string= "|{$id}|{$servers}|{$domain}|{$port}|{$tls}|{$admin}|{$passwd}|{$cn}|{$utf8}|{$ssl}";
            $string.= "|{$use_radius_login}|{$servers_radius}|{$secret}|{$admin_radius}|{$passwd_radius}";
            $string.= "|{$use_ldap_not_login}";
            $this->ldap_nameserver.= "||{$string}";
        }

        $this->save_param(false);
    }

    private function clean_password() {
        $sql= "update tusuarios set clave= null where id != 1";
        $result= $this->clink->query($sql);
    }
}

