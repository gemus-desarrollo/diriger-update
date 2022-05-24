<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/24/15
 * Time: 2:04 a.m.
 */

$_SESSION['debug']= 'no';

$url= (empty($signal) || $signal != 'prnt') ? urldecode($_GET['url']) : $_POST['url'];
$time_cookies= time()+2678400;

if (!empty($_POST['font'])) {
    $font= $_POST['font'];
    $font_size= $_POST['font-size'];
    $font_um= $_POST['font-um'];

    $size= $_POST['size'];
    $orientation= $_POST['orientation'];
    $margin_top= $_POST['margin-top'];
    $margin_left= $_POST['margin-left'];

    setcookie('size', $size, $time_cookies);
    setcookie('font', $font, $time_cookies);
    setcookie('font-size', $font_size, $time_cookies);
    setcookie('font-um', $font_um, $time_cookies);
    setcookie('margin-left', $margin_left, $time_cookies);
    setcookie('margin-top', $margin_top, $time_cookies);
    setcookie('orientation', $orientation, $time_cookies);
}
elseif (!empty($_COOKIE['font'])) {
    $font= $_COOKIE['font'];
    $font_size= $_COOKIE['font-size'];
    $font_um= $_COOKIE['font-um'];

    $size= $_COOKIE['size'];
    $orientation= $_COOKIE['orientation'];
    $margin_top= $_COOKIE['margin-top'];
    $margin_left= $_COOKIE['margin-left'];
}

if (empty($_POST['font']) && empty($_COOKIE['font'])) {
    $font= 'Arial, Helvetica, sans-serif';
    $font_size= 13;
    $font_um= 'px';

    $size= 'Carta';
    $orientation= 'portrait';
    $margin_top= 2.5;
    $margin_left= 2.5;

    setcookie('size', $size, $time_cookies);
    setcookie('font', $font, $time_cookies);
    setcookie('font-size', $font_size, $time_cookies);
    setcookie('font-um', $font_um, $time_cookies);
    setcookie('margin-top', $margin_top, $time_cookies);
    setcookie('margin-left', $margin_left, $time_cookies);
    setcookie('orientation', $orientation, $time_cookies);
}
?>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap core CSS -->
        <link href="<?=_SERVER_DIRIGER?>libs/jquery-ui-1.12.1/jquery-ui-1.12.1.min.css" rel="stylesheet">
        <link href="<?=_SERVER_DIRIGER?>libs/bootstrap-4.6.0/css/bootstrap-4.6.0.min.css" rel="stylesheet">
        <link href="<?=_SERVER_DIRIGER?>libs/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">

            <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-3.6.0/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-ui-1.12.1/jquery-ui-1.12.1.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-2.1.4/jquery.bpopup.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/bootstrap-4.6.0/js/bootstrap-4.6.0.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/tooltip-viewport/tooltip-viewport.js"></script>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        
        <link rel="stylesheet" type="text/css" href="<?=_SERVER_DIRIGER?>css/main.css">
        <link rel="stylesheet" type="text/css" href="<?=_SERVER_DIRIGER?>css/custom.css">
        
        <link href="<?=_SERVER_DIRIGER?>libs/windowmove/windowmove.css" media="screen" rel="stylesheet" />
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/windowmove/windowmove.js"></script>

        <script type="text/javascript" charset="utf-8" src="<?=_SERVER_DIRIGER?>js/general.js"></script> 
        
        <style type="text/css">
            @media all {
                body {
                    background: none;
                    margin: 0;
                    padding: 0;
                    font-family: '<?=$font?>';
                    font-size: <?=$font_size?><?=$font_um?>;
                }
                .page-break { 
                    display:none; 
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
                    position: relative;
                    z-index:1;
                    top: 0px;
                    display: block;
                }
                .page {
                    margin: 0;
                    border: none;

                    margin-left: <?= $margin_left?>cm; 
                    width: <?= $array_papel_size[$size][0]?>cm; 
                    font-family: '<?=$font?>';
                    font-size: <?=$font_size?><?=$font_um?> !important;                    
                    position: relative;
                }
                .page table {
                    font-family: '<?=$font?>';
                    font-size: <?=$font_size?><?=$font_um?> !important;                       
                }
                hr {
                    border: 1px solid #000;
                    width: 100%;
                    line-height: 1px;
                }
                table {
                    border: none;
                }
                th {
                    color: black;
                    font-weight: bold;
                    border: 1px black solid;
                    padding: 5px;
                }
                td  {
                    border: 1px black solid;
                    border-top: none;
                    border-right: none;
                    vertical-align: text-top !important;
                    text-align: left;
                    padding: 5px;
                }
                img {
                    border: none;
                }
                h1 {
                    font-weight: bolder; 
                    font-size: 1em;
                }
                .title-header {
                    width: 100%;
                    text-align: center !important;
                    margin: 20px;
                    font: 1.4em;
                    font-weight: bold; 
                }
                .plhead {
                    text-align: center !important;
                    vertical-align: middle;
                    border-left: none;
                    padding: 5px;
                }
                .plinner {
                    border: 1px black solid;
                    border-top: none;
                    border-left: none;
                    vertical-align: top!important;
                    text-align: left;
                    padding: 1px;
                    min-height: 30px;
                    min-width: 40px;
                    padding: 5px;
                }

                .signal {
                    text-align: center;
                    min-width: 70px !important;
                }

                .none-bottom {
                    border-bottom: none !important;
                }
                .bottom {
                    border-left: 1px black solid !important;
                }
                .none-right {
                    border-right: none !important;
                }
                .none-left {
                    border-left: none !important;
                }
                .left {
                    border-left: 1px black solid !important;
                }
                .top {
                    border-top: 1px black solid !important;
                }
                .right {
                    border-right: 1px black solid;
                }
                .inner {
                    border: 1px solid black;
                    vertical-align: text-top;
                    text-align: left;
                    padding: 4px;
                }
                .in {
                    border-left: none;
                    border-bottom: none;
                }
                .border {
                    border: 1px black solid;
                }
                .none-border {
                    border: none !important;
                }

                .month {
                    min-width: 30px;
                    border-left: none;
                    border-top: none;
                }
                .real {
                    text-align: right !important;
                }
                .comment {
                    font-size: 0.8em !important;
                    font-style: oblique;
                }                
            }
            
            @media screen {
                body {
                    background: none;
                    margin: 0;
                    padding: 0;
                    font-family: '<?=$font?>';
                    font-size: <?=$font_size?><?=$font_um?>;
                    
                    overflow: scroll;
                }                
            }
            
            @media print {
                .page-break {
                    display:block; 
                    page-break-before:always; 
                }
                #div-ajax-panel {
                    display: none;
                }
                nav {
                    display: none;
                } 
                .page-header {
                    margin-top: 5px;
                }                 
                @page {
                    size: <?=$size?> <?= $orientation?>;

                    font-size: <?=$font_size?><?=$font_um?>;
                    margin-top: <?=$margin_top?>cm;
                    margin-left: <?=$margin_left?>cm;
                }
                
                nav {
                    display: none;
                }  
            }
            
            @media screen {
                .page-header {
                    margin-top: 60px;
                }
                nav {
                    padding: 4px 20px;
                }
            }
        </style>

        <script language="javascript">
            function imprimir() {
                window.print();
            }
            function mostrar() {
                displayFloatingDiv('div-ajax-panel', "CONFIGURAR DOCUMENTO", 60, 0, 10, 10);
            }
            
            $(document).ready(function(ok) {
                InitDragDrop();
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/wz_tooltip/wz_tooltip.js"></script>
        
        <nav class="navbar navbar-default navbar-fixed-top hidden-print">
            <div class="nav navbar-right pull-right">
                <button class="btn btn-danger" onClick="mostrar();">
                    <i class="fa fa-font"></i>Formato
                </button>
                <button class="btn btn-info ml-1" onClick="imprimir()">
                    <i class="fa fa-print"></i>Imprimir
                </button>
            </div>
        </nav>

        <div class="page page-header">
            <div class="container-fluid center">
                <?php
                $widthpage= $_COOKIE['orientation'] == 'portrait' ? $array_papel_size[$_COOKIE['size']][0] : $array_papel_size[$_COOKIE['size']][1];
                include "{$_SESSION['virtualhost_base_dir']}client_images/page_top.htm";
                ?>   
            </div> 
        </div>    