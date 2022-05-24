<?php

$year= 2020;
$array_procesos= array();
$array_numeros= array();
$nums_register= 0;

$cronos= date('Y-m-d H:i:s');
$location= $_SESSION['location'];
        
function init_archive() {
    global $clink;
    global $year;
    global $array_procesos;
    global $array_numeros;
    global $nums_register;
    
    $sql= "select id, if_output, numero, id_proceso from tarchivos ";
    $sql.= "where id_proceso != {$_SESSION['local_proceso_id']} and year(cronos) = $year order by numero asc";
    $result= $clink->query($sql);
    
    $i= 0;
    $array_procesos= array();
    $array_numeros= array();
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $array_procesos[$row['id_proceso']]= array($row['id_proceso'], 'rs'=>null, 're'=>null);
        $array= array($row['id'], boolean($row['if_output']), $row['numero'], $row['id_proceso'], null);
        $array_numeros[$row['id']]= $array;
    }
    
    $nums_register= $i;
}

function order_number_archive() {
    global $array_procesos;
    global $array_numeros;
    
    foreach ($array_procesos as $id_proceso => $prs) {
        reset($array_numeros);
        $i_rs= 0;
        $i_re= 0;
        foreach ($array_numeros as $id => $row) {
            if ($row[3] == $id_proceso) {
                if ($row[1]) {
                    ++$i_rs;
                    $array_numeros[$id][4]= $i_rs;
                } else {
                    ++$i_re;
                    $array_numeros[$id][4]= $i_re;
            }   } 
        }
            
        $array_procesos[$id_proceso]['rs']= $i_rs; 
        $array_procesos[$id_proceso]['re']= $i_re; 
    }
}

function update_tarchivos() {
    global $clink;
    global $nums_register;
    global $array_numeros;
    
    $i= 0;
    $j= 0;
    $sql= null;
    reset($array_numeros);
    foreach ($array_numeros as $array) {
        $sql.= "update tarchivos set numero = {$array[4]} where id = {$array[0]}; ";
        
        ++$i;
        ++$j;
        if ($j >= 5000 && $sql) {
            $result= $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)$i/$nums_register;
            $_r= number_format($r*100, 3);               
            bar_progressCSS(0, "Procesando registros ... $_r%", $r);
        }        
    }
    
    if (!empty($sql)) 
        $result= $clink->multi_query($sql);
    bar_progressCSS(0, "Procesando registros ... 100%", 1);    
}

function _update_tseries($serie, $id_proceso, $numero) {
    global $clink;
    global $year;
    global $cronos;
    global $location;    
    
    $id_usuario= _USER_SYSTEM;
    
    $id_proceso_code= get_code_from_table("tprocesos", $id_proceso);
    $sql= "insert into tseries (serie, year, numero, id_proceso, id_proceso_code, id_usuario, cronos, situs) ";
    $sql.= "values ('$serie', $year, $numero, $id_proceso, '$id_proceso_code', $id_usuario, '$cronos', '$location'); ";
    $result= $clink->query($sql);

    if (!$result) {
        $sql= "update tseries set numero= $numero, id_usuario= $id_usuario, cronos= '$cronos' ";
        $sql.= "where serie = '$serie' and id_proceso_code= '$id_proceso_code'; ";
        $result= $clink->query($sql);
    }    
}

function update_tseries() {
    global $array_procesos; 
    
    $sql= null;
    reset($array_procesos);
    foreach ($array_procesos as $id_proceso => $row) {
        _update_tseries('RS', $id_proceso, $row['rs']);
        _update_tseries('RE', $id_proceso, $row['re']);
    }
}


function execute_repare_archive() {
    init_archive();
    order_number_archive();
    
    update_tarchivos();
    update_tseries();
}


