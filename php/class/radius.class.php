<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 11/11/2019
 * Time: 12:18 p.m.
 */


include_once "config_radius.class.php";

$_root_ldap= "../";
if (@file_exists('libs/radius/radius.class.php') )
    $_root_ldap= "";
elseif (@file_exists('../libs/radius/radius.class.php') )
    $_root_ldap= "../";
elseif (@file_exists('../../libs/radius/radius.class.php') )
    $_root_ldap= "../../";

$_dir_ldap= $_root_ldap;
/*=================================================================================*/
require_once $_root_ldap."libs/radius/radius.class.php";


class Tradius extends Tbase {
    public $radius;
    public $debug;

    private $servers;
    private $secret;
    private $domain;

    private $usuario;
    private $clave;


    public function __construct() {
        global $config;

        Tbase::__construct();
        $this->link= null;
    }

    public function  connect($options) {
        $this->domain= $options['domain'];
        $servers_array= preg_split('/[\' \' ,;]/', $options['servers_radius']);
        $array= array();

        foreach ($servers_array as $ip)
            $array[]= $ip;

        // Try to bind.
        $radius = false;

        foreach ($array as $ip) {
            $radius = new Radius($ip, $options['secret']);
            if (!$radius)
                continue;
            // Needed for some devices, and not auto_detected if PHP not runned through a web server
            $radius->SetNasIpAddress($ip);
            // Enable Debug Mode for the demonstration
            $radius->SetDebugMode($this->debug);

            break;
        }

        if ($radius) {
            $this->servers= $ip;
            $this->secret= $options['secret'];
            $this->radius= $radius;
        }

        if (!$radius) {
            $text= "El servidor {$options['servers_radius']} no responden. ";
            return $text;
        }

        if ($radius && (!empty($options['username']) && $options['password'])) {
            if (!$radius->AccessRequest($options['username'], $options['password'])) {
                $text= "No se ha podido autenticar el usuario {$options['username']}@{$options['domain']} en los Servidores $servers.";
                return $text;
            }
        }

        $this->radius= $radius;
        return null;
    }

    public function login($username, $password) {
        $result= $this->radius->AccessRequest($username, $password);
        return $result ? true : false;
    }

    public function login_RADIUS($username, $password) {
        $this->usuario= $username;
        $this->clave= $password;

        $this->dn_array= null;
        $this->nombre= null;
        $this->uid= null;

        $config_radius= new _Tconfig_radius();
        $config_radius->get_servers();

	$user= null;
	$error=  null;
        $user_error= null;
        $error_number= 0;
        $i= 0;
        foreach ($config_radius->array_radius_servers as $options) {
            if ($this->_connect($options)) {
                ++$i;
		$user= $this->radius->AccessRequest($username, $password);
		if ($user)
                    break;
            } else
		$error.= "No se ha podido establecer comunicación con el servidor RADIUS. \n";
        }

        $error_number= $i == 0 ? _LDAP_NO_SERVER : 0;

        if (!$user && empty($error_number)) {
            $error_number= _LDAP_SERVER_NO_USER;
            $user_error= "No se ha podido autoenticar al usuario: $username en ninguno de los servidores RADIUS disponibles. \n";
        }

        return null;
    }

    private function _connect($connect) {
        // Set up all options.
        $options = [
            'servers_RADIUS' => $connect['servers'],
            'secret' => $connect['secret'],
            'admin_username' => $connect['admin'],
            'admin_password' => $connect['passwd'],
        ];

        $result= $this->connect($options);
        return $result ? false : true;
    }

    public function close() {
        unset($this->radius);
        $this->radius= null;
    }
}