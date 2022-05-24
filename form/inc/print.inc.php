<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/24/15
 * Time: 2:04 a.m.
 */

$url= (empty($signal) || $signal != 'prnt') ? urldecode($_GET['url']) : $_POST['url'];

if(!empty($_POST['font'])) {
    $font= $_POST['font'];
    $font_size= $_POST['font-size'];
    $font_um= $_POST['font-um'];

    $size= $_POST['size'];
    $orientation= $_POST['orientation'];
    $margin_top= $_POST['margin-top'];
    $margin_left= $_POST['margin-left'];

    $_SESSION['size'] = $size;
    $_SESSION['font'] = $font;
    $_SESSION['font-size'] = $font_size;
    $_SESSION['font-um'] = $font_um;
    $_SESSION['margin-left'] = $margin_left;
    $_SESSION['margin-top'] = $margin_top;
    $_SESSION['orientation']= $orientation;
}
elseif(!empty($_SESSION['font'])) {
    $font= $_SESSION['font'];
    $font_size= $_SESSION['font-size'];
    $font_um= $_SESSION['font-um'];

    $size= $_SESSION['size'];
    $orientation= $_SESSION['orientation'];
    $margin_top= $_SESSION['margin-top'];
    $margin_left= $_SESSION['margin-left'];
}

if(empty($_POST['font']) && empty($_SESSION['font'])) {
    $font= 'Arial, Helvetica, sans-serif';
    $font_size= 10;
    $font_um= 'px';

    $size= 'Carta';
    $orientation= 'portrait';
    $margin_top= 2.5;
    $margin_left= 2.5;

    $_SESSION['size'] = $size;
    $_SESSION['font'] = $font;
    $_SESSION['font-size'] = $font_size;
    $_SESSION['font-um'] = $font_um;
    $_SESSION['margin-top'] = $margin_top;
    $_SESSION['margin-left'] = $margin_left;
    $_SESSION['orientation']= $orientation;
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="../../css/dimming.css?version=<?=$_SESSION['update_no']?>" />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/form.css?version=<?=$_SESSION['update_no']?>" />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/table_resumen.css?version=<?=$_SESSION['update_no']?>" />

<script language="javascript" type="text/javascript" src="../../js/jquery.js?version=<?=$_SESSION['update_no']?>"></script>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript" src="../../js/dimmingdiv.js?version=<?=$_SESSION['update_no']?>"></SCRIPT>
<script type="text/javascript" src="../../js/general.js?version=<?=$_SESSION['update_no']?>"></script>

<style>
body {
    background:none;
	margin:0;
	padding:0;
    font-family:<?=$_SESSION['font']?>;
    font-size:<?=$_SESSION['font-size']?><?=$_SESSION['font-um']?>;
}

#div-btn-print {
    width: 100%;

	position:fixed;
	top:0px;
	z-index:100;
	
    padding: 2px;
	display:block;

    border-bottom: 3px outset #000000;
    background:#292929 ;
}

#div-btn-print .btn-print {
    margin-right:4px;
	cursor:pointer!important;

    float: right;
}

#marca_print_diriger {
    margin: 20px;
    width: 800px;
    font-size: 0.9em;
    text-align: left;
    font-variant: annotation;
}


#div-top-separetor {
    width: 100%;
    position:relative;
	z-index:1;
    top:0px;
    display:block;
}

#div-ajax-panel {
    background:white;
    display:none;
    visibility:hidden;
}

@media print {
    body {
        overflow: hidden;
    }

    @page {
        /*
        size: <?=$_SESSION['size'] ?> <?php $_SESSION['orientation'] ?>;
        */
        font-size:<?=$_SESSION['font-size']?><?=$_SESSION['font-um']?>;
        margin-top:<?=$_SESSION['margin-top']?>cm;
        margin-left:<?=$_SESSION['margin-left']?>cm;
    }
	
	#div-btn-print {
		display:none;
	}
	
	#div-top-separetor {
		display:none;
	}

    #div-ajax-panel {
        display: none;
    }

    #div-ajax {
        display: none;
    }

    #dimmer {
        display: none;
    }
}

@media all {
    table {
        border:none;
    }

    #headerpage table {
        border: none!important;
    }

    #headerpage td {
        border: none!important;
    }

    #headerpage {

    }

    #bodypage {

    }

    th {
        color:black;
        font-weight:bolder;
        border:1px black solid;
        padding:5px;
    }

    td  {
        border:1px black solid;
        border-top:none;
        border-right:none;
        vertical-align:text-top!important;
        text-align:left;
        padding:3px;
    }

    img {
        border:none;
    }

    h1 {
        font-weight: bolder; font-size: 1.1em;
    }

    .title-header {
        width: 100%;
        text-align: center!important;
        margin: 20px;
        font-weight: bolder; font-size: 1.1em;
    }

    .plhead {
        text-align: center!important;
        vertical-align: middle;
    }

    .plinner {
        border:1px black solid;
        border-top: none;
        border-left:none;
        vertical-align:top!important;
        text-align:left;
        padding: 1px;
        min-height: 30px;
        min-width: 40px;
    }

    .signal {
        text-align: center!important;
        min-width:40px!important;
    }

    .bottom {border-bottom:none!important;}
    .width_bottom {border-left:1px black solid!important;}

    .right {border-right:none!important;}
    .left {border-left:1px black solid!important;}
    .top {border-top:1px black solid!important;}

    .inner {
        border:1px solid black;
        vertical-align:text-top;
        text-align:left;
        padding:4px;
    }

    .in {
        border-left:none;
        border-bottom:none;
    }

    .noborder {
        border: none!important;
    }

    .right-prn {
        border-right:1px black solid;
    }

    .month {
        min-width:30px;
        border-left: none;
        border-top: none;
    }

    .real {
        text-align: right!important;
    }

    .comment {
        font-size: 0.8em!important;
        font-style: oblique;
    }
}
</style>

<script language="javascript">

document.getElementById('dimmer').style.width= '<?php echo $array_papel_size[$_SESSION['size']][0]?>cm';


function imprimir() {window.print();}

function mostrar() {
    ifnew= true;

    var w, h, l, t;
    l = screen.width/10;
    t = 40;

    $('#div-ajax-panel').css('display','none');
    $('#div-ajax').css('display','none');
    $('#div-ajax-panel').css('display','block');
    $('#div-ajax').css('display','block');

    w = 380; h = 211;

    displayFloatingDiv('div-ajax-panel', "CONFIGURAR DOCUMENTO", w, h, l, t);
}

function close_print() {
    closeFloatingDiv('div-ajax-panel');
    cleanFloatingDiv();
}
</script>
</head>

<body>

<div id="div-btn-print">
	<button class="btn-print" value="Formato de impresión" onClick="mostrar();"><img src="../../img/font.png" alt="imprimir">&nbsp;Formato</button>
    <button class="btn-print" value="Imprimir" onClick="imprimir()"><img src="../../img/_print.png" alt="imprimir">&nbsp;Imprimir</button>
</div>
<div id="div-top-separetor"><br /><br /><br /></div>

<?php
$widthpage= $_SESSION['orientation'] == 'portrait' ? $array_papel_size[$_SESSION['size']][0] : $array_papel_size[$_SESSION['size']][1];
include "{$_SESSION['virtualhost_base_dir']}client_images/page_top.htm";
?>

<?php include('../prnt/prnt.php'); ?>
