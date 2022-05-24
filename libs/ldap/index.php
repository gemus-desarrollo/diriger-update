<?php

/**
 * This file contains a working example of adLDAP in operation.
 *
 * @file
 */
/*
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    echo 'Before using this demo, please run <code>composer dump-autoload</code>';
    exit(1);
}
require_once __DIR__.'/../vendor/autoload.php';
*/

require_once 'src/Adldap.php';

require_once('src/Classes/AbstractAdldapBase.php');
require_once('src/Classes/AbstractAdldapQueryable.php');
require_once('src/Classes/AdldapComputers.php');
require_once('src/Classes/AdldapContacts.php');
require_once('src/Classes/AdldapExchange.php');
require_once('src/Classes/AdldapFolders.php');
require_once('src/Classes/AdldapGroups.php');
require_once('src/Classes/AdldapSearch.php');
require_once('src/Classes/AdldapUsers.php');
require_once('src/Classes/AdldapUtils.php');

require_once('src/Connections/Ldap.php');

require_once('src/Exceptions/AdldapException.php');
require_once('src/Exceptions/InvalidQueryOperator.php');
require_once('src/Exceptions/PasswordPolicyException.php');
require_once('src/Exceptions/WrongPasswordException.php');

require_once('src/Interfaces/ConnectionInterface.php');

require_once('src/Objects/AbstractObject.php');
require_once('src/Objects/AccountControl.php');
require_once('src/Objects/Configuration.php');
require_once('src/Objects/Contact.php');
require_once('src/Objects/Folder.php');
require_once('src/Objects/Group.php');
require_once('src/Objects/Mailbox.php');
require_once('src/Objects/Schema.php');
require_once('src/Objects/User.php');

require_once('src/Objects/Ldap/Entry.php');
require_once('src/Objects/Ldap/Schema.php');

require_once('src/Query/Builder.php');
require_once('src/Query/Operator.php');

require_once('src/Traits/LdapFunctionSupportTrait.php');

use adLDAP\adLDAP;
use adLDAP\Exceptions\adLDAPException;

// Set up all options.
$options = [
    'account_suffix' => '',
    'base_dn' => null,
    'domain_controllers' => [''],
    'admin_username' => null,
    'admin_password' => null,
    'real_primarygroup' => '',
    'use_ssl' => false,
    'use_tls' => false,
    'recursive_groups' => true,
    'ad_port' => adLDAP::ADLDAP_LDAP_PORT,
    'sso' => '',
];
// Update options from $_POST.
foreach ($options as $optName => $defaultValue) {
    if (isset($_POST[$optName])) {
        $options[$optName] = $_POST[$optName];
    }
}
// Validate options.
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

// Handle log in.
$username = (!empty($_POST['username'])) ? $_POST['username'] : '';
$info = false;
if ($adldap && !empty($username)) {
    $password = $_POST['password'];
    try {
        $adldap->authenticate($username, $password);
        $info = $adldap->user()->info($username, ['*']);
        if (isset($info[0])) {
            $info = $info[0];
        }
    } catch (\adLDAP\Exceptions\adLDAPException $e) {
        $exception = $e;
    }
}

// Hand everything over to the view for display.
require 'view.html.php';


