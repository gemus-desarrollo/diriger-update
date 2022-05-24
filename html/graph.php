<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/cell.class.php";


$obj= new Tcell($clink);

$id= $_GET['id'];
$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$obj->SetYear($year);
$obj->SetMonth($month);

$id_tablero= !empty($_GET['tablero']) ? $_GET['tablero'] : 1;

$url= '&id='.$id.'&month='.$month.'&year='.$year.'&day='.$day;

require_once "../php/config.inc.php";

$obj->SetIdIndicador($id);
$obj->SetIndicador();

$url_page= "../html/graph.php?signal=$signal&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

set_page($url_page);
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
    <title>GRAFICOS</title>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/main.css?version=" />
    <link rel="stylesheet" type="text/css" media="screen" href="../css/form.css?version=" />

    <script language='javascript' type="text/javascript" charset="utf-8">
    function loadtablero() {
        var month = document.getElementById('month').value;
        var year = document.getElementById('year').value;
        var tablero = document.getElementById('tablero').value;

        self.location.href = 'tablero.php?tablero=' + tablero + '&month=' + month + '&year=' + year;
    }
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <center>
        <fieldset style="width:700px">
            <legend><?=$obj->GetNombre()?></legend>
            <input id="tablero" type="hidden" value="<?=$id_tablero?>" />
            <input id=day type="hidden" value="<?=$day?>" />
            <input id="month" type="hidden" value="<?=$month?>" />
            <input id="year" type="hidden" value="<?=$year?>" />

            <table cellspacing="4">
                <tr>
                    <td><img src="../php/phpgraph.interface.php?serie=real<?=$url?>" /></td>
                </tr>
                <tr>
                    <td><img src="../php/phpgraph.interface.php?serie=diff<?=$url?>" /></td>
                </tr>
                <tr>
                    <td>
                        <p class="submit" align="center">
                            <input value="Cancelar" type="reset" onclick="self.location.href='<?php prev_page()?>'">
                        </p>
                    </td>
                </tr>
            </table>

        </fieldset>
    </center>

</body>

</html>