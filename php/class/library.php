<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 4/02/15
 * Time: 9:48
 */

define ("_LIBRARIES_DIRIGER", 1);

function chk_checked($value) {
    return (empty($value) ? NULL : "checked");
}

function img_process($tipo) {
    switch($tipo) {
        case 1:
            return "00cecm.ico";
        case 2:
            return "01oac.ico";
        case 3:
            return "02osde.ico";
        case 4:
            return "03gae.ico";
        case 5:
            return "04firm.ico";
        case 6:
            return "05ueb.ico";
        case 7:
            return "06arc.ico";
        case 8:
            return "07team.ico";
        case 9:
            return "08office.ico";
        case 10:
            return "09process.ico";
        case 11:
            return "10arc.ico";
    }
}

function img_item_planning($id) {
    switch($id) {
        case 'pol':
            return "0pol.ico";
        case 'obj':
            return "0obj.ico";
        case 'ind':
            return "0ind.ico";
        case 'indi':
            return "0indi.ico";
    }
}

function index_to_number($index) {
    if (empty($index))
        return null;

    $value= 0;
    $array= preg_split('/[.]/', $index, -1, PREG_SPLIT_NO_EMPTY);
    $n= count($array);
    $n= $n < 3 ? 3 : $n;
    
    for ($i= 0; $i <= $n; $i++) {
        if (is_null($array[$i]))
            continue;
        $value+= $array[$i]*pow(10,2*(3-$i));
    }

    return $value;
}

function get_regdate($year, $month= null, $day= null, $use_last_day= true) {
    if (empty($month))
        $month= 12;
    $t= (int)date('t', strtotime("$year-$month-01"));

    if (empty($day) || (int)$day > $t)
        $day= $use_last_day ? $t : 1;
    $regdate= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
    return $regdate;
}

function merge_origen_data_user($origen_data) {
    global $Ttipo_proceso_array;

    if (strlen($origen_data) < 4)
        return null;
    list($nombre, $cargo, $email, $proceso, $tipo)= explode(":", $origen_data);

    $str= "$nombre ($cargo) ";
    if (!empty($email))
        $str.= "<br />email:$email";
    $str.= "<br />Subordinado a: $proceso, ".$Ttipo_proceso_array[$tipo];

    return $str;
}

function merge_origen_data_process($origen_data, $print_img= false) {
    global $Ttipo_proceso_array;
    global $Ttipo_conexion_array;

    $url= _SERVER_DIRIGER.'img'._SLASH_DIRIGER_DIR;

    if (strlen($origen_data) < 5)
        return null;
    list($proceso1, $tipo1, $conectado1, $codigo1, $proceso2, $tipo2, $conectado2, $codigo2)= explode(":", $origen_data);

    $str= "$proceso1 ".$Ttipo_proceso_array[$tipo1]." conectado por:".$Ttipo_conexion_array[$conectado1];
    if ($print_img)
        echo "<img src='".$url.img_process($tipo1)."' border=0 title='".$Ttipo_proceso_array[$tipo1]."'>";
    if ($print_img && $conectado1 != _NO_LOCAL)
        echo "<img src='".$url."transmit.ico' border=0 title=' Conectado a través de ".$Ttipo_conexion_array[$conectado1]."'>";

    if (strcmp($codigo2, $_SESSION['location']) != 0) {
        $str.= "<br />Subordinado a: $proceso2 ".$Ttipo_proceso_array[$tipo2]." conectado por:".$Ttipo_conexion_array[$conectado2];
        if ($print_img)
            echo "<img src='".$url.img_process($tipo2)."' border=0 title='".$Ttipo_proceso_array[$tipo2]."'>";
        if ($print_img && $conectado2 != _NO_LOCAL)
            echo "<img src='".$url."transmit.ico' border=0 title=' Conectado a través de ".$Ttipo_conexion_array[$conectado2]."'>";
    }

    return $str;
}

function merge_origen_data_participant($origen_data) {
    global $Ttipo_proceso_array;
    global $Ttipo_conexion_array;

    if (strlen($origen_data) < 3)
        return null;
    list($participantes, $proceso, $tipo, $codigo)= explode(":", $origen_data);

    $str= "Proceso: $proceso, ".$Ttipo_proceso_array[$tipo];
    $str.= "<br /> participan: $participantes";

    return $str;
}

/**
 * Busca l llave en un arreglo bidemencional donde el campo $searchKey tiene el valor $id
 * @param $multi_array
 * @param $id
 * @param string $key titulo del campo a buscar
 * @return int|null|string
 */
function array_key_search(&$multi_array, $id1, $searchKey1= 'id', $id2= null, $searchKey2= null) {
    if (!is_array($multi_array))
        return null;

    reset($multi_array);
    foreach ($multi_array as $key => $array) {
        if (empty($searchKey2))
            if ($array[$searchKey1] == $id1)
                return $key;
        else
            if ($array[$searchKey1] == $id1 && $array[$searchKey2] == $id2)
                return $key;
    }

    return null;
}

/*
 * Combina las celdas de un array con las misma llave numericas. Se utiliza como llave el indice ID
 */
function arrayUniqueId($array) {
    if (!is_array($array))
        return $array;

    $result= array();
    foreach ($array as $value) {
        $result[$value['id']]= $value;
    }    
    return $result;
 }
 
/*
 * Combina dos array con llaves numericas. Sobreescribe las celdas. Copia el array2 al array1
 */
function array_merge_overwrite(array $array1= null, array $array2 = null) { 
    $flag = true;

    if (!empty($array2) && is_array($array2))
        reset($array2);

    if (is_null($array1) || count($array1) == 0)
        return $array2;
    if (is_null($array2) || count($array2) == 0)
        return $array1;

    foreach (array_keys($array2) as $key) { 
        if (isset($array2[$key])) { 
            if (isset($array1[$key]) && is_array($array1[$key])) 
                array_merge_overwrite($array1[$key], $array2[$key]); 
            else 
                $array1[$key] = $array2[$key]; 

            $flag = false; 
        } 
    } 
    if ($flag == true) 
        $array1 = $array2; 
    
    return $array1;
} 

/*
 * Combina dos array con llaves numericas. Sobreescribe las celdas. Copia el array2 al array1.
 * Tomando como referencia el campo id
 */
function array_merge_overwrite_by_id(array $array1= null, array $array2 = null) {
    if (!empty($array2) && is_array($array2))
        reset($array2);

    if (is_null($array1) || count($array1) == 0)
        return $array2;   
    
    foreach ($array2 as $item2) {
        $i= 0;
        reset($array1);
        foreach ($array1 as $item1) {
            if ($item1['id'] == $item2['id']) {
                break;
            }
            ++$i;
        } 

        $array1[$i]= $item2;
    }
    reset($array2);
    return $array1;
}
 
function redim_imagen($original, $nueva, $max_ancho, $max_alto, $corte) {
    list($img_anchorig, $img_altorig, $tipo) = getimagesize($original);

    switch ($tipo) {
        case 1:
            $img_orig = imagecreatefromgif ($original);
            break;
        case 2:
            $img_orig = imagecreatefromjpeg($original);
            break;
        case 3:
            $img_orig = imagecreatefrompng($original);
            break;
        case 15:
            $img_orig = imagecreatefromwbmp($original);
            break;
        default:
            die("\nFormato de imagen no soportado");
    }

    $black = @imagecolorallocate ($img_orig, 0, 0, 0);
    $white = @imagecolorallocate ($img_orig, 255, 255, 255);
    $font = 4;

    if ($corte > 0) {
        if (($img_anchorig/$img_altorig) > ($max_ancho/$max_alto)) {
            $img_alto= $max_alto;
            $img_ancho= ($img_anchorig/$img_altorig)*$max_alto;
            $escala= $img_alto/$img_altorig;
            $posx= ($img_anchorig-($max_ancho/$escala))/2;
            $posy= 0;
        } else {
            $img_ancho= $max_ancho;
            $img_alto= ($img_altorig/$img_anchorig)*$max_ancho;
            $escala= $img_alto/$img_altorig;
            $posx= 0;
            $posy= ($img_altorig-($max_alto/$escala))/2;
        }

        $img_nueva= imagecreatetruecolor($max_ancho,$max_alto);
        imagecopyresampled($img_nueva,$img_orig,0,0,$posx,$posy,$max_ancho,$max_alto,$max_ancho/$escala,$max_alto/$escala);

    } else {
        $img_ancho=($img_anchorig/$img_altorig)*$max_alto;
        $img_alto=$max_alto;

        if ($img_ancho > $max_ancho) {
            $img_ancho= $max_ancho;
            $img_alto= ($img_altorig/$img_anchorig)*$max_ancho;
        }

        $img_nueva= imagecreatetruecolor($img_ancho,$img_alto);
        imagecopyresampled($img_nueva,$img_orig,0,0,0,0,$img_ancho,$img_alto,$img_anchorig,$img_altorig);
    }

    chmod($nueva,0777);
    unlink($nueva);
    imagejpeg($img_nueva, $nueva , 90);
    imagedestroy ($img_nueva);
}

function get_work_day($fecha, &$weekday= null) {
    global $day_feriados;

    $time= strtotime($fecha);
    $weekday= date('N',$time);
    if ($weekday == 6)
        return false;
    if ($weekday == 7)
        return false;
    $m_d= date('j/m', $time);
    if (array_search($m_d, $day_feriados) !== false)
        return false;

    return true;
}

function clone_array($array) {
    if (is_null($array) || !is_array($array))
        return null;
    $obj_array = new ArrayObject($array);
    return $obj_array->getArrayCopy();
}

function get_ratio($real, $plan= null, $trend= null, $plan_cot= null) {
    $ratio= null;
    $percent= NULL;

    if ((is_null($plan) || is_null($real)) || ($trend == 3 && is_null($plan_cot)))
        return null;
    if (($trend == 1 || $trend == 3) && empty($plan))
        return null;
    if ($trend == 1)
        $ratio = ((float) ($real - $plan) / $plan);
    if ($trend == 2) {
        if ($real == 0 || $plan == 0) {
            $plan += 1;
            $real += 1;
        }
        $ratio = ((float) ($plan - $real) / $real);
    }
    if ($trend == 3) {
        if ($real > $plan)
            $ratio= ((float)($plan - $real)/$plan);
        elseif ($real < $plan_cot) {
            if ($plan_cot == 0.0) {
                $ratio= -1;
                $percent= 0;
            } else
                $ratio= ((float)($real - $plan_cot)/$plan_cot);
        }
        else
            $ratio= 1;

        if ($plan == 0 && $plan_cot != 0) {
            $plan= $plan_cot;
        }
        else {
            if ($plan != 0 && $plan_cot != 0) {
                $_coti= abs($real - $plan_cot);
                $_cots= abs($real - $plan);
                if ($_coti < $_cots)
                    $plan= $plan_cot;
    }   }   }

    if (!is_null($ratio))
        $ratio= (1+$ratio)*100;
    if (!empty($plan))
        $percent= (((float)$real)/$plan)*100;

    return array('ratio'=>$ratio, 'percent'=>$percent);
}

function memory_usage($text) {
    $mem_usage= memory_get_usage(true);
    $mem_usage= round($mem_usage/1048576,2);

    $mem_peak= memory_get_peak_usage(true);
    $mem_peak= round($mem_peak/1048576,2);

    $msg= "\nMemoria del sistema: $text... ".number_format($mem_usage, 2)."MB ";
    $msg.= "-- Pico maximo:".number_format($mem_peak, 2)."MB \n";
    
    if ($_SESSION['output_signal'] == 'shell' || $_SESSION['output_signal'] == 'webservice') {
        outputmsg($msg);
    } 
    
    if ((!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice')
            && (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block'])) {
    ?>
        <script type='text/javascript'>
    <?php 
    }
     if (!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice') {
     ?> 
        $('#winlog').append(<?=json_encode(nl2br($msg), JSON_PRETTY_PRINT)?>);
        goend();
    <?php     
    }
    if ((!$_SESSION['execfromshell'] && $_SESSION['execfromshell'] != 'webservice')
            && (!is_null($_SESSION['in_javascript_block']) && !$_SESSION['in_javascript_block'])) { 
    ?>
        </script>
    <?php 
    }    
}

function GetFileType($ext) {
    $type= null;

    switch ($ext) {
        case 'doc':
        case 'docx':
            $type= 'application/msword';
            break;
        case 'pdf':
            $type= 'application/pdf';
            break;
        case 'ppt':
        case 'pptx':
            $type= 'application/vnd.ms-powerpoint';
            break;
        case 'xls':
        case 'xlsx':
            $type= 'application/vnd.ms-excel'; 
            break;
        case 'accdb':
        case 'accdbx':
            $type= 'application/msaccess';
            break;
        case 'rar': 
            $type= 'application/gzip';
            break;
        case 'txt':
            $type= 'text/plain'; 
            break;
        case 'html':
        case 'htm':
            $type= 'text/html';
            break;
        case 'mp4':
            $type= 'video/mp4';
            break;
        case 'avi':
            $type= 'video/avi';
            break;
        case 'mov':
            $type= 'video/x-flv';
            break;
        case '3gpp':
            $type= 'video/3gpp';
            break;
        case 'wmv':
            $type= 'video/x-ms-wmv';
            break;
        case 'mpeg':
            $type= 'video/mpeg';
            break;
        case 'wma':
            $type= 'audio/mpeg';
            break;
        case 'gif':
            $type= 'image/gif';
            break;
        case 'jpeg':
            $type= 'image/jpeg';
            break;
        case 'jpg':
            $type= 'image/jpg';
            break;
        case 'bmp':
            $type= 'image/bmp';
            break;
        case 'png':
            $type= 'image/png';
            break;
        case 'tiff':
            $type= 'image/tiff';
            break;
        default:
            $type= 'multipart/form-data';
    }

    return $type;
}

?>
