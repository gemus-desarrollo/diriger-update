<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/cell.class.php";


$id= $_GET['id'];
$year= $_GET['year'];
$month= $_GET['month'];

$serie= $_GET['serie'];
if (empty($serie)) $serie= 'real';

$obj= new Tcell($clink);

$obj->SetIdIndicador($id);
$obj->SetYear($year);
$obj->SetMonth($month);

$obj->getdata_monthly();


//Include phpMyGraph5.0.php
require_once "class/phpMyGraph5.0.php";

//Set config directives
$cfg['height'] = 200;
$cfg['width'] = 640;
$cfg['average-line-visible']= 0;
$cfg['title-font-size']= 6;

if ($serie == 'real') {
 $cfg['title'] = 'Real';
 $data= $obj->data_real;
}
elseif ($serie == 'diff') {
    $cfg['title'] = 'Real - Plan';
    $data= $obj->data_diff;
}

if (!isset($data)) {
    $cfg['average-line-visible']= 1;
    $cfg['title'] = 'No se dispone de datos';

    //Set data
    $data = array(
        'Ene' => 0,
        'Feb' => 0,
        'Mar' => 0,
        'Abr' => 0,
        'May' => 0,
        'Jun' => 0,
        'Jul' => 0,
        'Ago' => 0,
        'Sept' => 0,
        'Oct' => 0,
        'Nov' => 0,
        'Dic' => 0
    );
}

 //Create phpMyGraph instance
 $graph = new phpMyGraph();

 //Parse
   $graph->parseVerticalColumnGraph($data, $cfg);

?>