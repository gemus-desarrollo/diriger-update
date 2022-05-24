<?php

/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */


session_start();
include_once "../../../../php/setup.ini.php";
include_once "../../../../php/class/config.class.php";

include_once "../../../../php/config.inc.php";
include_once "../../../../php/class/base.class.php";
include_once "../../../../php/class/connect.class.php";

include_once "../../../../php/class/usuario.class.php";
include_once "../../../../php/class/document.class.php";
include_once "../../php/class/persona.class.php";

$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;

$result['id']= 0;
$result['telefono']= null;
$result['movil']= null;
$result['email']= null;

$result['provincia']= null;
$result['municipio']= null;
$result['direccion']= null;

$result['noIdentidad']= null;
$result['nombre']= null;

$result['id_organismo']= null;
$result['cargo']= null;

if (!empty($id_usuario)) {
    $obj_user = new Tusuario($clink);
    $obj_user->SetIdUsuario($id_usuario);
    $obj_user->Set();

    if (empty($obj_user->error) && $obj_user->GetCantidad() > 0) {
        $result['id']= $id_usuario;
        $result['email'] = $obj_user->GetMail_address();
        $result['noIdentidad'] = $obj_user->GetNoIdentidad();
        $result['nombre'] = $obj_user->GetNombre();
        $result['cargo'] = textparse($obj_user->GetCargo());
    }
    
    $obj_pers= new Tpersona($clink);
    $obj_pers->SetIdResponsable($id_usuario);
    $obj_pers->Set();
    
    $result['provincia']= $obj_pers->GetProvincia();
    $result['municipio']= $obj_pers->GetMunicipio();
    $result['direccion']= $obj_pers->GetDireccion(); 
    $result['id_organismo']= $obj_pers->GetIdOrganismo();
}

$result= json_encode($result);
echo $result;
exit;