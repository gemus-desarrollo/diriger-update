<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<?php
global $stop_by_die;
$stop_by_die= false;

if ($_SESSION['debug'] && $_SESSION['debug'] !== 'update') $_SESSION['debug']= 'no';
?>

<?php if (!$ajax_win) { ?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- Bootstrap core CSS -->
        <link href="<?=_SERVER_DIRIGER?>libs/jquery-ui-1.12.1/jquery-ui-1.12.1.min.css" rel="stylesheet">
        <link href="<?=_SERVER_DIRIGER?>libs/bootstrap-4.6.0/css/bootstrap-4.6.0.min.css" rel="stylesheet">


        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-3.6.0/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-ui-1.12.1/jquery-ui-1.12.1.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/jquery-2.1.4/jquery.bpopup.min.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/bootstrap-4.6.0/js/bootstrap-4.6.0.min.js"></script>

        <link href="<?=_SERVER_DIRIGER?>/libs/alert-panel/alert-panel.css" rel="stylesheet" />
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>/libs/alert-panel/alert-panel.js"></script>

        <link href="<?=_SERVER_DIRIGER?>libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>libs/windowmove/windowmove.js"></script>

        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>js/time.js"></script>
        <script type="text/javascript" src="<?=_SERVER_DIRIGER?>js/general.js"></script>

<?php } ?>

        <style type="text/css">
            <?php if (!$ajax_win) { ?>
            body {
                margin: 0px;
                padding: 0px;
                background-color: #333;
                color: #e0dfdc;
            }
            <?php } else { ?>
            .ajax {
                margin: 0px;
                padding: 0px;
                background-color: #333;
                color: #e0dfdc;
            }
            <?php } ?>

            .ajax-win .container-fluid {
                margin: 0px;
                padding: 0px;

                background: #000;
                color: #ffff;
                font-size: 1.2em;

                position: fixed;
                min-height: 100vh;
                max-height: 100vh;
                top: 0;
                lef: 0;

                opacity: 0.4;
                -moz-opacity: 0.4;
                filter: alpha(opacity=40);
                -khtml-opacity: 0.4;
            }
            h1.ajax-win {
                font-family: "Avant Garde", Avantgarde, "Century Gothic", CenturyGothic, "AppleGothic", sans-serif;
                font-size: 12vw;
                font-weight: bold;
                position: absolute;
                top: 15%;
                width: 100%;
                text-align: center;
                vertical-align: middle;
                text-transform: uppercase;
                text-rendering: optimizeLegibility;
            }
            h1.ajax-win.deepshadow {
                color: #e0dfdc;
                letter-spacing: .1em;
                text-shadow:
                    0 -1px 0 #fff,
                    0 1px 0 #2e2e2e,
                    0 2px 0 #2c2c2c,
                    0 3px 0 #2a2a2a,
                    0 4px 0 #282828,
                    0 5px 0 #262626,
                    0 6px 0 #242424,
                    0 7px 0 #222,
                    0 8px 0 #202020,
                    0 9px 0 #1e1e1e,
                    0 10px 0 #1c1c1c,
                    0 11px 0 #1a1a1a,
                    0 12px 0 #181818,
                    0 13px 0 #161616,
                    0 14px 0 #141414,
                    0 15px 0 #121212,
                    0 22px 30px rgba(0, 0, 0, 0.9);
            }
        </style>

        <style type="text/css">
            .body-log {
                width: 100%;
                height: 100vh;
                overflow: auto;
                background: transparent;
                margin: 0px 0px 0px 0px;
            }
            .body-log table {
                width: 100%;
                max-height: 100%;
                margin: 0px;
                border: none;
                background: transparent;
            }
            .body-log table td {
                border: none;
                padding: 2px;
                text-align: left;
                vertical-align: top;
                text-shadow: 2px 2px 3px rgba(134,234,255,0.6);
            }
            .body-log table tr {
                border-bottom: 1px groove rgba(68,75,167,0.6);
                padding-top: 8px;;
            }
            .body-log table tr td:first-child {
                padding: 3px;
                color: #c77405;
            }
            .body-log table tr td:first-child:hover {
                padding: 3px;
                background: #ff3300!important;
                color: #ffff00;
            }
            .body-log table tr:hover {
                background: #ff3300!important;
                color: #ffff00!important;
                text-shadow: none;

                opacity: 0.8;
                -moz-opacity: 0.8;
                filter: alpha(opacity=80);
                -khtml-opacity: 0.8;
            }
        </style>

        <script type="text/javascript">
            var _mseconds_= 0;
            var _chronometer;

            function setChronometer() {
                ++_mseconds_;
                var mseconds= 0;
                var hours = 0;
                var minutes = 0;
                var seconds = 0;

                seconds= Math.floor(_mseconds_/1000);
                mseconds= _mseconds_ - seconds*1000;
                hours = Math.floor(_mseconds_/(1000*3600));
                minutes = Math.floor(_mseconds_/(1000*60) - hours*60);
                seconds= seconds - hours*3600 - minutes*60;

                if (hours < 10) 
                    hours = '0' + hours;
                if (minutes < 10) 
                    minutes = '0' + minutes; 
                if (seconds < 10) 
                    seconds = '0' + seconds;
                if (mseconds < 10) 
                    mseconds = '00' + mseconds;
                else 
                    if (mseconds < 100) 
                        mseconds = '0' + mseconds;

                $('#chronometer').html(hours+':'+minutes+':'+seconds+':'+mseconds);
            }
            <?php if (!$ajax_win) { ?>
            function closep() {
                if (opener)
                    opener.location.reload();

                self.close();
            }
            <?php } ?>
        </script>

        <script type="text/javascript">
            var cicle;
            var noLine= 0;
            var _moveScroll= true;

            function writeLog(time, line, div) {
                ++noLine;

                var _tr= document.createElement('tr');
                var _td1= document.createElement('td');
                var _td2= document.createElement('td');

                _td1.innerHTML= '<strong>'+noLine+' ---><br/>  '+time+'</strong>';
                _td2.innerHTML= line;
                _tr.appendChild(_td1);
                _tr.appendChild(_td2);

                $('#'+div+' table').append(_tr);

                moveScrollLog(div);
            }

            function moveScrollLog(div) {
                if (!_moveScroll) 
                    return;

                var _this= $('#'+div);
                var _this_inner= $('#'+div+' table');
                _this.scrollTop(_this_inner.innerHeight());
            }
        </script>
        
    <?=$ajax_win ? "" : "</head>"?>

    <?=$ajax_win ? "<div class='ajax-win'>" : "<body class='ajax-win'> <script type='text/javascript' src='"._SERVER_DIRIGER."libs/wz_tooltip/wz_tooltip.js'></script>"?>
        
        <h1 id="chronometer" class="ajax-win deepshadow"></h1>

        <div class="container-fluid">
            <div id="body-log" class="body-log">
                <table>

                </table>
            </div>