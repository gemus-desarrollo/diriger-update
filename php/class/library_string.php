<?php

/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier Portuondo
 * Date: 29/06/2020
 * Time: 9:48
 */

defined("_LIBRARIES_DIRIGER") or define ("_LIBRARIES_DIRIGER", 1);

function fullUpper($string){
    return strtr(strtoupper($string), array(
       "à" => "À",
       "è" => "È",
       "ì" => "Ì",
       "ò" => "Ò",
       "ù" => "Ù",
       "á" => "Á",
       "é" => "É",
       "í" => "Í",
       "ó" => "Ó",
       "ú" => "Ú",
       "â" => "Â",
       "ê" => "Ê",
       "î" => "Î",
       "ô" => "Ô",
       "û" => "Û",
       "ç" => "Ç"
    ));
}

function setNULL_str($id, $addslash = true, $db_linked = true){
    global $clink;
    global $uplink;

    $addslash = !is_null($addslash) ? $addslash : true;
    $db_linked = !is_null($db_linked) ? $db_linked : true;
    $link = $clink ? $clink : ($uplink ? $uplink : null);

    if (empty($id) || strlen($id) == 0)
        $str = 'NULL';
    else{
        $id = preg_replace("#<!--\[if gte mso (.?)\]>(.*?)<!\[endif\]-->#is", '', $id);
        $id = preg_replace("#<!--\[if gte mso (.?)(.?)\]>(.*?)<!\[endif\]-->#is", '', $id);
        $id = preg_replace("#mso-(.*?):(.*?)(>)#is", '', $id);

        if ($addslash){
            if ($db_linked){
                if ($_SESSION["_DB_SYSTEM"] == "mysql" || empty($_SESSION["_DB_SYSTEM"])) {
                    $string = !is_null($link) ? mysqli_real_escape_string($link->dblink, $id) : $id;
                    $id = $string ? $string : addslashes($id);
                } else
                    pg_escape_string($id);
            } else
                $id = addslashes($id);
        }

        $str = "'$id'";
    }

    return $str;
}

function setNULL_blob($id){
    if (empty($id) || strlen($id) == 0)
        $str = 'NULL';
    else{
        $id = bin2hex($id);
        $str = "'$id'";
    }
    return $str;
}

function setNULL_utf8_encode($id){
    if (empty($id) || strlen($id) == 0)
        $str = 'NULL';
    else{
        $id = addslashes($id);
        $id = preg_replace('/\n/m', '\\n', $id);
        $id = preg_replace('/\r/m', '\\r', $id);
        // $id = utf8_encode($id);

        $str = "'$id'";
    }
    return $str;
}

function setNULL($id, $zero = false){
    if (!$zero)
        $id = (is_null($id) || (empty($id) && (string) $id !== (string) 0)) ? 'NULL' : $id;
    else
        $id = (is_null($id) || empty($id)) ? 0 : $id;
    return $id;
}

function setNULL_empty($id){
    $id = empty($id) ? 'NULL' : $id;
    return $id;
}

function setZero($id){
    $id = empty($id) ? '0' : $id;
    return $id;
}

function setNAN($id){
    return !is_null($id) ? $id : "NAN";
}

function setNULL_equal_sql($id, $is_string = false){
    if (is_null($id))
        $sql = 'is null ';
    else {
        $sql = $is_string ? "= '$id' " : "= $id ";
    }
    return $sql;
}

function setNULL_empty_equal_sql($id, $is_string = false){
    if (is_null($id) || empty($id))
        $sql = 'is null ';
    else {
        $sql = $is_string ? "= '$id' " : "= $id ";
    }
    return $sql;
}

function setNULL_undefined($id) {
    $id = !is_null($id) ? strtolower($id) : null;
    if ($id == 'undefined' || $id == 'undef' || $id == 'null' || is_null($id))
        return null;
    else
        return $id;
}

function isNULL_str($id){
    return ($id == 'NULL' || $id == 'null') ? null : $id;
}

function number_format_to_roman($num){
    $rnum = null;
    if ($num < 0 || $num > 9999)
        return -1;

    $r_ones = array(1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX");
    $r_tens = array(1 => "X", 2 => "XX", 3 => "XXX", 4 => "XL", 5 => "L", 6 => "LX", 7 => "LXX", 8 => "LXXX", 9 => "XC");
    $r_hund = array(1 => "C", 2 => "CC", 3 => "CCC", 4 => "CD", 5 => "D", 6 => "DC", 7 => "DCC", 8 => "DCCC", 9 => "CM");
    $r_thou = array(1 => "M", 2 => "MM", 3 => "MMM", 4 => "MMMM", 5 => "MMMMM", 6 => "MMMMMM", 7 => "MMMMMMM", 8 => "MMMMMMMM", 9 => "MMMMMMMMM");

    $ones = $num % 10;
    $tens = ($num - $ones) % 100;
    $hundreds = ($num - $tens - $ones) % 1000;
    $thou = ($num - $hundreds - $tens - $ones) % 10000;
    $tens = $tens / 10;
    $hundreds = $hundreds / 100;
    $thou = $thou / 1000;

    if ($thou)
        $rnum .= $r_thou[(int) $thou];
    if ($hundreds)
        $rnum .= $r_hund[(int) $hundreds];
    if ($tens)
        $rnum .= $r_tens[(int) $tens];
    if ($ones)
        $rnum .= $r_ones[(int) $ones];

    return $rnum;
}

/**
 * Purga el html que es generado por el componenete tinyeditor
 *
 */
function purge_html($v, $purge_all = true){
    if (is_null($v) || strlen($v) == 0)
        return null;

    $init = strpos($v, '<ol><li>');
    $end = strrpos($v, '</li></ol>');

    if ($init !== false){
        $length = $end > 0 ? $end - $init : null;
        $v = substr($v, $init, $length);
        $v = preg_replace("</li><li>", '<br />', $v);
    }

    $init = null;
    $end = null;
    $init = strpos($v, '<ul><li>');
    $end = strrpos($v, '</li></ul>');

    if ($init !== false){
        $length = $end > 0 ? $end - $init : null;
        $v = substr($v, $init, $length);
        $v = preg_replace("</li><li>", '<br />', $v);
    }

    $v = preg_replace("#<span class=\"apple-style-span\">(.*)<\/span>#", '$1', $v);
    $v = preg_replace("# class=\"apple-style-span\"#", '', $v);
    $v = preg_replace("#<span style=\"(.*?)\">#", '', $v);
    $v = preg_replace("#<br style=\"(.*?)\">#", '', $v);
    $v = preg_replace("#<br>#", '<br />', $v);
    $v = preg_replace("#<br ?\/?>$#", '', $v);
    $v = preg_replace("#^<br ?\/?>#", '', $v);
    $v = preg_replace("#(<img [^>]+[^\/])>#", '$1 />', $v);
    $v = preg_replace("#<b\b[^>]*>(.*?)<\/b[^>]*>#", '<strong>$1</strong>', $v);
    $v = preg_replace("#<i\b[^>]*>(.*?)<\/i[^>]*>#", '<em>$1</em>', $v);
    $v = preg_replace("#<u\b[^>]*>(.*?)<\/u[^>]*>#", '<span style="text-decoration:underline">$1</span>', $v);
    $v = preg_replace("#<(b|strong|em|i|u) style=\"font-weight:normal;?\">(.*)<\/(b|strong|em|i|u)>#", '$2', $v);
    $v = preg_replace("#<(b|strong|em|i|u) style=\"(.*)\">(.*)<\/(b|strong|em|i|u)>#", '<span style="$2"><$4>$3</$4></span>', $v);
    $v = preg_replace("#<span style=\"font-weight: normal;?\">(.*)<\/span>#", '$1', $v);
    $v = preg_replace("#<span style=\"font-weight: bold;?\">(.*)<\/span>#", '<strong>$1</strong>', $v);
    $v = preg_replace("#<span style=\"font-style: italic;?\">(.*)<\/span>#", '<em>$1</em>', $v);
    $v = preg_replace("#<span style=\"font-weight: bold;?\">(.*)<\/span>|<b\b[^>]*>(.*?)<\/b[^>]*>#", '<strong>$1</strong>', $v);
    $v = preg_replace("#<span style=\"text-decoration:underline\">(.*?)</span>#", '$1', $v);

    $v = preg_replace("#\r\n#", ' ', $v);
    $v = preg_replace("#<span style=\"(.*?)\">(.*)</span>#", '$2', $v);
    $v = preg_replace("#<div(.*?)>(.*)</div>#", '$2', $v);
    $v = preg_replace("#<!--\[if gte mso (.?)\]>(.*?)<!\[endif\]-->#is", '', $v);
    $v = preg_replace("#<!--\[if gte mso (.?)(.?)\]>(.*?)<!\[endif\]-->#is", '', $v);

    //   $v= preg_replace("#rn?\/?#", '', $v);
    $v = preg_replace("#<p class=\"(.*?)\"?>(.*?)</p>#", '<p>$2</p>', $v);

    if ($purge_all){
        /*
          $v= preg_replace("#<strong>(.*?)</strong>#", '$1', $v);
          $v= preg_replace("#<strike>(.*?)</strike>#", '$1', $v);
          $v= preg_replace("#<sup>(.*?)</sup>#", '$1', $v);
          $v= preg_replace("#<sub>(.*?)</sub>#", '$1', $v);
          $v= preg_replace("#<ul>(.*?)</ul>#", '$1', $v);
          $v= preg_replace("#<li>(.*?)</li>#", '$1', $v);
          $v= preg_replace("#<p>(.*?)</p>#", '$1', $v);
          $v= preg_replace("#<h1>(.*?)</h1>#", '$1', $v);
         */
        $v = strip_tags($v);
    }

    return $v;
}

function outputmsg($msg, $fixdate = true, $tofile = true){
    if (empty(strlen($msg)))
        return;

    $date = $fixdate ? date('Y-m-d H:i:s') : '';
    $msg = "\r\n$msg:  " . $date;

    if (!empty($_SESSION['logfile']) && ($tofile || ($_SESSION['execfromshell'] == 'webservice' || $_SESSION['output_signal'] == 'webservice'))){
        $file = _ROOT_DIRIGER_DIR . 'log' . _SLASH_DIRIGER_DIR . $_SESSION['logfile'];
        $fp = fopen($file, 'a+');
        if ($fp)
            fwrite($fp, $msg);
        fclose($fp);
    } else
    if ($_SESSION['execfromshell'] && $_SESSION['output_signal'] != 'webservice')
        echo $msg;
}

function CleanNonACIIchar($text){
    $input = '/([(\x00-\x1f)*]|[(\xa9-\xdf)*]|[(\xfe-\xff)*]|[(\x91-\x96)*])*$/i'; //el rango de caracteres que no queremos
    $text_output = preg_replace($input, '', $text . " ");
    $text_output = preg_replace("#mso-(.*?):(.*?)(>)#is", '', $text_output);

    $search = array("\x00", "\x0a", "\x0d", "\x1a", "\x09");
    $replace = array('\0', '\n', '\r', '\Z', '\t');
    $text_output = str_replace($search, $replace, $text_output);

    $search = array('\n', '\r');
    $replace = array('<br />');
    $text_output = str_replace($search, $replace, $text_output);
    $text_output = trim($text_output);
    return $text_output;
}

function textparse($text, $addslaches = false, $purgehtml = false) {
    global $config;
    if (is_null($text))
        return null;
    $text= $config->charset == 'utf8' ? utf8_encode($text) : $text;
    $text = str_replace('"', '`', $text);
    $text = str_replace('\'', '`', $text);
    
    if ($addslaches)
        $text = addslashes($text);

    if ($purgehtml){
        $text = CleanNonACIIchar($text);
        $text = strip_tags($text);
    }

    return $text;
}

/*
 * limpia la cadena de caracteres html y codigo php
 */
function clean_string($string) {
    if (empty($string))
        return false;
    $string= get_magic_quotes_gpc() ?  stripslashes($string) : $string;
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}