<?php

/**
 * @author mustelier
 * @copyright 2014
 */

ini_set("display_errors", "On");
ini_set("error_reporting", E_ALL); // para el desarrollo
?>
 
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>LEER LA MAC DEL SISTEMA</title>
    </head>

    <body>
        <?=date("Y-m-d H:i:s")?>
        <br/><br/>
        
        <?php
        $mac= null;
        $txtmac= null;
        $os_txt= null;
        $istream= array();

        $os_txt= strtolower(php_uname('s'));
        if (strpos($os_txt,'win') !== false)
            $os_txt= 'windows';
        elseif (strpos($os_txt,'bsd') !== false)
            $os_txt= 'FreeBSD';
        else
            $os_txt= 'linux';

        $pc= php_uname('n');

        if ($os_txt == 'windows') {
            exec("ipconfig /all", $stream);
            $count= count($stream);
            $k= 0;
            for ($i= 0; $i <= $count; $i++) {
                $istream[$k]= null;
                if (isset($stream[$i]))
                    preg_match('/[A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}[:-][A-Fa-f0-9]{2}/', $stream[$i], $istream[$k]);

                if (is_null($istream[$k]) || (!is_null($istream[$k])&& !isset($istream[$k][0]))) 
                    continue;

                    if (!is_null($istream[$k][0])) {
                        if (strlen($istream[$k][0])) {
                            if ($k > 0)
                                $txtmac.= "<br/>";
                            $txtmac.= $istream[$k][0];
                            ++$k;
                }   } 
            }
            
            if ($k == 0)
                $txtmac= "<strong style='color:red'>No se ha podido identificar la MAC de la tarjeta de red</strong><br />";
        }

        if ($os_txt != 'windows') {
            // captura la mac en linux ---------------------------------------------------------
            $eth= exec("/sbin/route | grep default | tr -s ' ' | cut -f8 -d ' '");              
            $cmdshell= popen("/sbin/ifconfig $eth",'r');
            $line= fread($cmdshell, 2096);
            pclose($cmdshell);
            preg_match("/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/", $line, $mac);
            $txtmac= $mac[0];          
        }
        ?>
        
        <strong>Direcciones fisicas de las tarjetas de red (MAC):</strong><br/> <?=$txtmac?>
        <p><strong>Nombre de la la maquina:</strong> <?=$pc?></p>
        <p><strong>Sistema Operativo:</strong> <?=$os_txt?>  (<?=$os_txt?>)</p>

    </body>   
</html>
