<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 3/02/15
 * Time: 5:49
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/peso.class.php";
require_once "class/code.class.php";
require_once "../form/class/list.signal.class.php";

require_once "class/perspectiva.class.php";
require_once "class/inductor.class.php";
require_once "class/politica.class.php";
require_once "class/objetivo.class.php";

require_once "class/proceso_item.class.php";

require_once "class/indicador.class.php";
require_once "class/programa.class.php";
require_once "class/registro.class.php";
require_once "class/cell.class.php";

require_once "class/peso_calculo.class.php";

require_once "class/data.class.php";
require_once "class/data_formulated.class.php";

/*
Se utiliza a la hora de graficar mantener el valor del $date_below_cutoff cuando se trabaja con otras magnitudes
que no son indicadores. Ejemplo las perspectivas que utilizan varios indicadores
*/
$array_date_bellow_cutoff= null;

// Callback function for Y-scale to get 1000 separator on labels
function separator1000($aVal) {
    return number_format($aVal);
}
function separator1000_usd($aVal) {
    return '$'.number_format($aVal);
}
function formated_value($value) {
    global $decimal;
    global $sign;
    return number_format($value, $decimal,'.','').' '.$sign;
}

$decimal= 0;
$sign= "%";
?>