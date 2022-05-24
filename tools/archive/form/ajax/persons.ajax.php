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

include_once "../../php/class/ref_archivo.class.php";
include_once "../../php/class/archivo.class.php";

$id_persona= !empty($_GET['id_persona']) ? $_GET['id_persona'] : 0;

$result['id']= 0;
$result['telefono']= '';
$result['movil']= '';
$result['email']= '';

$result['provincia']= 0;
$result['municipio']= 0;
$result['direccion']= '';

$result['noIdentidad']= '';
$result['nombre']= '';

$result['lugar']= '';
$result['id_organismo']= 0;
$result['cargo']= '';

if (!empty($id_persona)) {
    $obj_ref = new Tref_archivo($clink);
    $obj_ref->Set($id_persona);

    if (empty($obj_ref->error) && $obj_ref->GetCantidad() > 0) {
        $result['id'] = $id_persona;
        $result['telefono'] = $obj_ref->GetTelefono();
        $result['movil'] = $obj_ref->GetMovil();
        $result['email'] = $obj_ref->GetMail_address();

        $result['provincia'] = $obj_ref->GetProvincia();
        $result['municipio'] = $obj_ref->GetMunicipio();
        $result['direccion'] = $obj_ref->GetDireccion();

        $result['noIdentidad'] = $obj_ref->GetNoIdentidad();
        $result['nombre'] = $obj_ref->GetNombre();
        
        $result['lugar'] = $obj_ref->GetLugar();
        $result['id_organismo'] = $obj_ref->GetIdOrganismo();
        $result['cargo'] = textparse($obj_ref->GetCargo());
    }
}

$result= json_encode($result);
echo $result;
exit;