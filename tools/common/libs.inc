<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

global $signal;

function fix_error($error, $text) {
    global $signal;

    if($signal == 'shell') echo "\r\n$text ERROR:$error \r\n";

    return $error;
}

function bar_progress($id, $text, $perc=1, $sleep= 5000) {
    if($_SESSION['output_signal'] == 'shell') {
        $msg= "$text... ".number_format($perc, 2)."%";
        if($perc == 0 || $perc == 1) outputmsg($msg);

        return;
    }

    $_width= 400;
    $left= $_width * $perc;
    usleep($sleep);
    
    $html= "<script language='javascript' type='text/javascript' charset='utf-8'>";
    $html.= "document.getElementById('dt-{$id}').innerHTML='{$text}';";
    $html.= "document.getElementById('bar_progress-{$id}').style.left='{$left}px';";
    $html.= "</script>";
    echo $html;

    flush();
    ob_flush();          
}

function function_progress($i, $function, $cant, $id) {
    $r= (float)($i) / $cant;
    $_r= $r*100; $_r= number_format($_r,1);
    bar_progress($id, "$function: ..... $_r%", $r, 4000);
}

function point_progress($function, $i, $cant) {
    static $_function;
    static $_lr;

    $r= (float)($i) / $cant;
    $_r= $r*100; $_r= number_format($_r,1);

    if(is_null($_function) || $_function != $_function) $_function= $function;
    if(empty($_lr) || (int)$_lr != (int)$_r) $_lr= $_r;

    if($_function == $function) for($i= (int)$_lr - (int)$_r; $i < (int)$_r; ++$i) echo ".$_r% ";
    else echo "<br/> #$function: ..... $_r%";
}
?>