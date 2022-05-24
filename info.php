<?php
session_start();

$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="img/gemus_logo.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>INFORMACIÓN DEL SISTEMA</title>

    <?php
    $dirlibs= ""; 
    require 'form/inc/_page_init.inc.php' 
    ?>

</head>

<body class="container" style="margin-top: 30px;">
    <div>
        <?php
            $os_txt= strtolower(php_uname('s'));  
            if (strpos($os_txt,'win') !== false)
                 $os_txt= 'windows';
             elseif (strpos($os_txt,'bsd') !== false)
                 $os_txt= 'FreeBSD';
             else
                 $os_txt= 'linux';            
        
            $date= null;
            if ($os_txt == 'windows') {
                exec("date /t", $stream);
                $date.= $stream[0];
                $stream= null;
                exec("time /t", $stream);
                $date.= " {$stream[0]}";
            } else {
                exec("date", $stream);
                $date.= $stream[0];
            }
            ?>
        <div class="alert alert-success">
            <strong>Fecha y hora (Sistema):</strong> <?=$date?>
        </div>
        <div class="alert alert-info">
            <strong>Fecha y hora (PHP):</strong> <?=date('Y-m-d H:i:s')?>
        </div>
        <?php
            $clink = new mysqli("127.0.0.1", $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
            $result= $clink->query("select now();");
            $row=  $result->fetch_array(MYSQLI_NUM);
            ?>
        <div class="alert alert-warning">
            <strong>Fecha y hora (MySQL):</strong> <?=$row[0]?>
        </div>
    </div>

</body>

</html>