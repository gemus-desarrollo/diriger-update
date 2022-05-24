<?php

/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
include_once "../../php/setup.ini.php";

$id_prov= !empty($_GET['id_prov']) ? $_GET['id_prov'] : "0";
$id_mcpo= !empty($_GET['id_mcpo']) ? $_GET['id_mcpo'] : "0";
?>

<select class="form-control" id="municipio" name="municipio">
    <option value="0" <?php if ($id_mcpo == 0) {?>selected="selected"<?php } ?>>Seleccione ... </option>
<?php
    foreach ($Tarray_municipios as $keyP => $municipios) {
        if (!empty($id_prov) && $id_prov != $keyP) 
            continue;
        
        foreach ($municipios[1] as $keyM => $mcpo) {
        ?>
    <option value="<?=$keyM?>" <?php if ($keyM == $id_mcpo) {?>selected="selected"<?php } ?>><?= utf8_encode($mcpo)?></option>
        <?php    
    }   }
?>    
</select>
