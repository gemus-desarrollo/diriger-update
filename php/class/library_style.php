<?php
/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier Portuondo
 * Date: 29/06/2020
 * Time: 9:48
 */

defined("_LIBRARIES_DIRIGER") or define ("_LIBRARIES_DIRIGER", 1);

global $signal;

function fix_error($error, $text, $divout= null) {
    global $signal;

    if ($signal == 'shell' || ($_SESSION['output_signal'] == 'shell' || $_SESSION['execfromshell'] == 'shell'))
        echo "\n$text ERROR:$error \n";
    if ($_SESSION['output_signal'] == 'webservice' || $_SESSION['execfromshell'] == 'webservice')
        outputmsg ("\n$text ERROR:$error \n");
    $html= "<div class='alert alert-danger'>$text <strong>ERROR: </strong>$error</div>";
?>
    <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
        <script type='text/javascript'>
    <?php } ?>
           writeLog('<?=date('Y-m-d H:i:s')?>', <?=json_encode(nl2br($html), JSON_PRETTY_PRINT)?>, "<?=$divout?>");
    <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
        </script>
    <?php } ?>
<?php
    return $error;
}

function bar_progress($id, $text, $perc, $sleep= 1000) {
    if ($_SESSION['output_signal'] == 'shell' || $_SESSION['output_signal'] == 'webservice') {
        $msg= "$text... ".number_format($perc, 2)."%";
        if ($perc == 0 || $perc == 1)
            outputmsg($msg);
        return;
    }
    $perc*= 100;

    $html.="progressbar({$id},'{$text}',{$perc},0); ";
    echo $html;

    ob_flush();
    flush();
    ob_end_flush();
    ob_start();
}

function bar_progressCSS($id, $text, $perc, $sleep= 1000) {
    $sleep= !is_null($sleep) ? $sleep : 1000;

    if ($_SESSION['output_signal'] == 'shell' || $_SESSION['execfromshell'] == 'webservice') {
        $msg= "$text... ".number_format($perc, 2)."%";
        $perc= round($perc, 1);
        if (    ($perc >= 0 && $perc <= 0.1)
            || ($perc > 0.9 && $perc < 1.1)
            || ($perc > 1.9 && $perc < 2.1)
            || ($perc > 2.9 && $perc < 3.1)
            || ($perc > 3.9 && $perc < 4.1)
            || ($perc > 4.9 && $perc < 5.1)
            || ($perc > 5.9 && $perc < 6.1)
            || ($perc > 6.9 && $perc < 7.1)
            || ($perc > 7.9 && $perc < 8.1)
            || ($perc > 8.9 && $perc < 9.1)
            || ($perc >= 9.9 && $perc <= 1))
            outputmsg($msg);
        return;
    }
    $perc*= 100;
    ?>
    <?php if ((!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice')
            && (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block'])) { ?>
        <script type='text/javascript'>
    <?php } ?>
        <?php if (!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice') { ?>
            progressbarCSS(<?=$id?>,'<?=$text?>',<?=$perc?>,0);
        <?php } ?>
    <?php if ((!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice')
            && (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block'])) { ?>
        </script>
    <?php } ?>

<?php
    ob_flush();
    flush();
    ob_end_flush();
    ob_start();
}

function function_progress($i, $function, $cant, $id) {
    $r= (float)($i) / $cant;
    $_r= $r*100;
    $_r= number_format($_r,1);
    bar_progress($id, "$function: ..... $_r%", $r);
}

function function_progressCSS($i, $function, $cant, $id) {
    $r= (float)($i) / $cant;
    $_r= $r*100;
    $_r= number_format($_r,1);
    bar_progressCSS($id, "$function: ..... $_r%", $r);
}

function point_progress($function, $i, $cant) {
    static $_function;
    static $_lr;

    $r= (float)($i) / $cant;
    $_r= $r*100;
    $_r= number_format($_r,1);

    if (is_null($_function) || $_function != $_function)
        $_function= $function;
    if (empty($_lr) || (int)$_lr != (int)$_r)
        $_lr= $_r;

    if ($_function == $function)
        for ($i= (int)$_lr - (int)$_r; $i < (int)$_r; ++$i)
            echo ".$_r% ";
    else
        echo "<br/> #$function: ..... $_r%";
}

function writeLog($date, $line, $divout) {
    if ($_SESSION['output_signal'] == 'webservice')
        return null;

    if (is_null($date))
        $date= date('Y-m-d H:i');

    if ($_SESSION['output_signal'] == 'shell') {
        echo "$date ----> $line \n";
        return;
    }
    if ($_SESSION['execfromshell'] == 'webservice' || $_SESSION['output_signal'] == 'webservice') {
        outputmsg("$date ----> $line \n");
        return;
    }

    ?>
    <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
        <script type='text/javascript'>
    <?php } ?>
            writeLog('<?=$date?>', "<?= addslashes($line)?>", <?=is_null($divout) ? 'null' : "'$divout'"?>);
    <?php if (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block']) { ?>
        </script>
    <?php } ?>
<?php
}

function alert($text) {
    ?>
    <?php if (!$_SESSION['in_block_javascrpt'] || is_null($_SESSION['in_tag_javascrpt'])) { ?>
        <script type='text/javascript'>
    <?php } ?>
            alert("<?= addslashes($text)?>");
    <?php if (!$_SESSION['in_block_javascrpt'] || is_null($_SESSION['in_tag_javascrpt'])) { ?>
        </script>
    <?php } ?>
<?php
}

function arrow_direction($class) {
    if ($class == 'green')
        return 'fa-long-arrow-up';
    if ($class == 'red')
        return 'fa-long-arrow-down';
    if ($class == 'yellow')
        return 'fa-arrows-v';
    if ($class == 'blank')
        return 'fa-arrows-v';
    return 'fa-arrows-v';
}

function tooltip_alarm($class, $cumulative= false) {
    if ($class == 'blank')
        $tooltip= "No hay valor de Plan o Real para hacer analisis";

    if (!$cumulative) {
        if ($class == 'green')
            $tooltip= "Se esta cumpliendo el plan";
        if ($class == 'aqua' || $class == 'blue')
            $tooltip= "Se ha sobrecumplido lo planificado";
        if ($class == 'red')
            $tooltip= "No se ha cumplido con lo planificado";
        if ($class == 'orange' || $class == 'yellow')
            $tooltip= "Aun no se ha cumplido, pero se esta proximo al cumplimiento";
    }
    if ($cumulative) {
        if ($class == 'green')
            $tooltip= "Se esta cumpliendo el acumulado segun lo planificado";
        if ($class == 'aqua' || $class == 'blue')
            $tooltip= "Se esta sobrecumpliendo el acumulado";
        if ($class == 'red')
            $tooltip= "No se ha cumplido con el acumulado planificado para este corte";
        if ($class == 'orange' || $class == 'yellow')
            $tooltip= "El acumulado aun no se cumple, pero los valores estan cerca a lo planificado";
    }

    return $tooltip;
}

function tooltip_arrow($class, $cumulative) {
    if ($class == 'blank')
        $tooltip= "No existen dos periodos continuos para hacer comparacion de cumplimientos";
    if ($class == 'yellow')
        $tooltip= "El % de cumplimeinto es igual al periodo anterior registrado";

    if ($class == 'green' && !$cumulative)
        $tooltip= "El cumplimiento en este corte es superior al corte anterior registrado";
    if ($class == 'red' && !$cumulative)
        $tooltip= "El cumplimiento en este corte es inferior al corte anterior registrado";

    if ($class == 'green' && $cumulative)
        $tooltip= "El porciento de cumplimiento del acumulado hasta este corte es superior al corte anterior registrado";
    if ($class == 'red' && $cumulative)
        $tooltip= "El porciento de cumplimiento del acumulado hasta este corte es inferior al porciento en el corte anterior registrado";

    return $tooltip;
}

function color_proccess($tipo) {
    $class= null;

    switch ($tipo) {
        case _TIPO_OACE:
        case _TIPO_OSDE:
        case _TIPO_GAE:
            $class= 'osde';
            break;
        case _TIPO_GAE:
            $class= 'grupo';
            break;            
        case _TIPO_EMPRESA:              
            $class= 'grupo';
            break;                  
        case _TIPO_UEB:              
            $class= 'ueb';
            break; 
        case _TIPO_DIRECCION:              
            $class= 'direccion';
            break;
        case _TIPO_GRUPO:    
        case _TIPO_DEPARTAMENTO:              
            $class= 'dpto';
            break; 
        case _TIPO_PROCESO_INTERNO:     
        case _TIPO_ARC:              
            $class= 'arc';
            break;                                     
    }
    return $class;
}