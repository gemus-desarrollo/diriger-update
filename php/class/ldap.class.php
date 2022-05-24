<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 11/11/2016
 * Time: 7:46 a.m.
 */


defined('_LDAP_NO_SERVER') or define('_LDAP_NO_SERVER', 1);
defined('_LDAP_SERVER_NO_USER') or define('_LDAP_SERVER_NO_USER', 2);

include_once "config_ldap.class.php";
include_once "usuario.class.php";

$_root_ldap= "../";
if (@file_exists('libs/ldap/adLDAP.php'))
    $_root_ldap= "";
elseif (@file_exists('../libs/ldap/adLDAP.php'))
    $_root_ldap= "../";
elseif (@file_exists('../../libs/ldap/adLDAP.php'))
    $_root_ldap= "../../";

$_dir_ldap= $_root_ldap;
/*=================================================================================*/
require_once $_root_ldap."libs/ldap/adLDAP.php";
require_once $_root_ldap."libs/radius/radius.class.php";

$_root_ldap= $_dir_ldap;
require_once $_root_ldap."libs/ldap/collections/adLDAPCollection.php";
require_once $_root_ldap."libs/ldap/collections/adLDAPComputerCollection.php";
require_once $_root_ldap."libs/ldap/collections/adLDAPContactCollection.php";
require_once $_root_ldap."libs/ldap/collections/adLDAPGroupCollection.php";
require_once $_root_ldap."libs/ldap/collections/adLDAPUserCollection.php";

require_once $_root_ldap."libs/ldap/classes/adLDAPComputers.php";
require_once $_root_ldap."libs/ldap/classes/adLDAPContacts.php";
require_once $_root_ldap."libs/ldap/classes/adLDAPExchange.php";
require_once $_root_ldap."libs/ldap/classes/adLDAPFolders.php";
require_once $_root_ldap."libs/ldap/classes/adLDAPGroups.php";;
require_once $_root_ldap."libs/ldap/classes/adLDAPUsers.php";
require_once $_root_ldap."libs/ldap/classes/adLDAPUtils.php";

/*=====================================================================================*/
/*
use adLDAP\adLDAP;
use adLDAP\Exceptions\adLDAPException;
*/


require_once "radius.class.php";


class Tldap extends Tusuario {
    public $adldap;
    public $radius;

    private $servers;
    private $port;
    private $domain;
    private $clave;
    private $dn_array;
    private $uid;
    private $utf8;

    public $count_users;
    public $count_groups;

    private $if_radius;
    private $use_radius_login;
    private $config_ldap;

    public function GetClave() {
        return $this->clave;
    }
    public function get_dn_array() {
        return $this->dn_array;
    }
    public function get_uid() {
        return $this->uid;
    }

    public function __construct() {
        global $config;

        Tusuario::__construct();
        $this->link= null;
        $this->radius= new Tradius();
    }

    private function _connect($connect) {
        $this->if_radius= false;
        $this->radius->debug= false;

        $options = [
            'servers_radius' => $connect['servers_radius'],
            'secret' => $connect['secret'],
            'username' => null,
            'password' => null
        ];

        if ($this->use_radius_login) {
            if ($connect['use_radius_login']) {
                $result= $this->radius->connect($options);
                if (is_null($result))
                    $this->if_radius= true;
            }

            if ($connect['use_ldap_not_login'])
                return $result ? false : true;
            elseif ($result)
                return false;
        }

        $options = [
            'account_suffix' => $connect['domain'],
            'base_dn' => $connect['cn'],
            'domain_controllers' => $connect['servers'],
            'admin_username' => $connect['admin'],
            'admin_password' => $connect['passwd'],
            'real_primarygroup' => '',
            'use_ssl' => $connect['ssl'] ? true : false,
            'use_tls' => $connect['tls'] ? true : false,
            'recursive_groups' => true,
            'ad_port' => $connect['port'],
            'sso' => '',
        ];

        if (!$connect['use_ldap_not_login'])
            $result= $this->connect($options);

        return $result ? false : true;
    }

    public function connect($options) {
        $servers_array= preg_split('/[\' \' ,;]/', $options['domain_controllers']);
        $array= array();
        $domain= $options['account_suffix'];

        foreach ($servers_array as $ip)
            $array[]= $ip;

        // Validate options.
        $options['account_suffix']= "@".$options['account_suffix'];
        $options['domain_controllers']= $array;
        $options['domain_controllers'] = array_filter($options['domain_controllers']);

        // Try to bind.
        $adldap = false;
        $exception = false;
        if (is_array($options['domain_controllers']) && !empty($options['domain_controllers'][0])) {
            try {
                $adldap = new adLDAP($options);
                // To pass through to the form:
                $options['base_dn'] = $adldap->getBaseDn();
                $options['ad_port'] = $adldap->getPort();

            } catch (adLDAPException $e) {
                $exception = $e;
            }
        }

        if ($adldap) {
            $this->servers= $array;
            $this->port= $options['ad_port'];
            $this->domain= $domain;
            $this->adldap= $adldap;
        }

        $servers= implode(',', $array);
        if (!$adldap) {
            $error= "Los Controladores de Dominio $servers no responden. ";
            return $error;
        }

        if ($adldap && !$adldap->getLdapBind()) {
            $error= "No se ha podido autenticar el usuario de administración {$options['admin_username']}@{$domain} en el dominio {$domain}.";
            $error.= "Servidores LDAP $servers.";
            return $error;
        }

        $this->adldap= $adldap;
        return null;
    }

    private function set_user($user) {
        $this->dn_array= null;
        $this->nombre= null;
        $this->uid= null;

        if (!$user)
            return;

        $collection = $this->adldap->user()->all();

        for ($i= 0; $i < $collection["count"]; $i++) {
            if (strtolower($collection[$i]['samaccountname'][0]) == strtolower($this->usuario)) {
                $user= $collection[$i];
                break;
            }
        }

        $this->nombre= $this->utf8 ? utf8_encode($user['displayname'][0]) : $user['displayname'][0];
        $this->uid= bin2hex($user['objectguid'][0]);
        $this->dn_array= array('usuario'=>$user['samaccountname'][0], 'nombre'=>$this->nombre, 'observacion'=>$user['description'][0],
                                'ou'=>$user['dn_array'][0], 'uid'=>$this->uid, 'givenname'=>$user['givenname'][0]);
    }


    public function login($username, $password, $id_entity= null) {
        $this->use_radius_login= true;
        $this->usuario= $username;
        $this->clave= $password;

        $config_ldap= new _Tconfig_ldap();
        $config_ldap->get_servers();

        $user= null;
        $error=  null;
        $user_error= null;
        $error_number= 0;
        $i= 0;
        foreach ($config_ldap->array_ldap_servers as $options) {
            if (!empty($id_entity) && $id_entity != $options['proceso'])
                continue;

            if ($this->_connect($options)) {
                ++$i;
                $this->id_proceso= $options['proceso'];
                $this->utf8= $options['utf8'] ? true : false;

                if ($options['use_radius_login'] && $this->if_radius) {
                    $result= $this->radius->login($username, $password);
                    if (!$result)
                        continue;
                }
                if (!$options['use_radius_login'] || ($options['use_radius_login'] && !$options['use_ldap_not_login'])) {
                    $user= $this->adldap->authenticate($username, $password);
                    if ($user)
                        break;
                } else {
                    $user= true;
                    break;
                }
            } else
		        $error.= "No se ha podido establecer comunicación con el servidor {$options['domain']}. \n";
        }

        $error_number= $i == 0 ? _LDAP_NO_SERVER : 0;

        if (!$user && empty($error_number)) {
            $error_number= _LDAP_SERVER_NO_USER;
            $user_error= "No se ha podido autoenticar al usuario: $username en ninguno de los servidores ";
            $user_error.= "disponibles. \n";
        }

        if($error_number == _LDAP_NO_SERVER) {
            $user_error.= "No hay servidor LDAP disponible para la entidad seleccionada. \n";
        }

        if (!$user) {
            $error.= $user_error;
            return array($error_number, $error);
        }

        if ($user)
            $this->set_user($user);

        return null;
    }


    public function login_ldap($username, $password) {
        $this->use_radius_login= false;
        $this->usuario= $username;
        $this->clave= $password;

        $config_ldap= new _Tconfig_ldap();
        $config_ldap->get_servers();

	$user= null;
	$error=  null;
        $user_error= null;
        $error_number= 0;
        $i= 0;
        foreach ($config_ldap->array_ldap_servers as $options) {
            if ($this->_connect($options)) {
                ++$i;
                $this->id_proceso= $options['proceso'];
                $this->utf8= $options['utf8'] ? true : false;

                $user= $this->adldap->authenticate($username, $password);

                if ($user)
                    break;
            } else
		$error.= "No se ha podido establecer comunicación con el servidor {$options['domain']}. \n";
        }

        $error_number= $i == 0 ? _LDAP_NO_SERVER : 0;

        if (!$user && empty($error_number)) {
            $error_number= _LDAP_SERVER_NO_USER;
            $user_error= "No se ha podido autoenticar al usuario: $username en ninguno de los servidores ";
            $user_error.= "disponibles. \n";
        }
        if ((!$found && !$user) || !$user) {
            $error.= $user_error;
            return array($error_number, $error);
        }

        $this->set_user($user);

        return null;
    }

    public function close() {
        unset($this->adldap);
        $this->adldap= null;
    }

    public function list_users() {
        $array_users= null;
        $collection= $this->adldap->user()->all();

        for ($i= 0; $i < $collection["count"]; $i++) {
            $array= array('usuario'=>$collection[$i]['samaccountname'][0], 'nombre'=>$collection[$i]['displayname'][0], 
                        'observacion'=>$collection[$i]['description'][0], 'ou'=>$collection[$i]['dn_array'][0], 
                        'uid'=> bin2hex($collection[$i]['objectguid'][0]), 'mail'=>$collection[$i]['mail'][0],
                        'givenname'=>$collection[$i]['givenname'][0]);
            $array_users[bin2hex($collection[$i]['objectguid'][0])]= $array;
            ++$this->count_users;
        }

        return $array_users;
    }

    public function list_groups() {
        $array_grupos= null;
        $collection= $this->adldap->group()->all();

        for ($i= 0; $i < $collection["count"]; $i++) {
            $array= array('grupo'=>$collection[$i]['samaccountname'][0], 'observacion'=>$collection[$i]['description'][0],
                        'ou'=>$collection[$i]['dn_array'][0], 'uid'=> bin2hex($collection[$i]['objectguid'][0]), 
                        'givenname'=>$collection[$i]['givenname'][0]);
            $array_grupos[bin2hex($collection[$i]['objectguid'][0])]= $array;
            ++$this->count_groups;
        }

        return $array_grupos;
    }
}