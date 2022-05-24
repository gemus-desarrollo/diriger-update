<?php

/**
 * @author muste
 * @copyright 2014
 */

include_once "../../../php/class/DBServer.class.php";

global $nivel_user;
global $uplink;

class Tconnect extends DBServer {
    public function __construct($host, $database= null, $ifnewlink = false) {
        DBServer::__construct($host, $database, $_SESSION['db_user'], $_SESSION['db_pass'], $ifnewlink);
    }
}

if ($nivel_user >= _SUPERUSUARIO) {
    $uplink= new Tconnect($_SESSION['db_ip'], $_SESSION['db_name'], true);
    $uplink= is_null($uplink->error) ? $uplink : false;
}
