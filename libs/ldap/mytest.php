<?php
session_start();                                                                                                                        
$_SESSION['output_signal']= 'shell';

include_once "../../php/class/usuario.class.php";

include ("../../php/class/ldap.class.php";

// Set up all options.
$options = [
    'account_suffix' => 'gemus.cu',
    'base_dn' => 'dc=gemus,dc=cu',
    'domain_controllers' => '192.168.0.1',
    'admin_username' => 'Administrator',
    'admin_password' => 'admin6',
    'real_primarygroup' => '',
    'use_ssl' => false,
    'use_tls' => false,
    'recursive_groups' => true,
    'ad_port' => 389,
    'sso' => ''
];

$adldap = new Tldap();
$error= $adldap->connect($options);

$Usuario = 'Administrator';
$Contrasena = "admin6";

if ($adldap->adldap->authenticate($Usuario,$Contrasena))
{
        echo "auth v4 ok ";
}
else
        echo "auth v4 error";

echo "<br/><br/>";

$collection= $adldap->adldap->user()->all(); 

print_r($collection);
?>
