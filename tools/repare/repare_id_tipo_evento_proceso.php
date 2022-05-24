<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 06/05/2020
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/base.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";


set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$init_year= (int)date('Y')-1;
$end_year= $init_year+5;

function execute_id_tipo_evento_proceso($year) {
    global $clink;
    
    $sql= "select distinct teventos.id, teventos.id_tipo_evento as _id_tipo_evento, "; 
    $sql.= "teventos.id_tipo_evento_code as _id_tipo_evento_code, teventos.indice as _indice, "; 
    $sql.= "teventos.cronos, tproceso_eventos_$year.id as _id, tproceso_eventos_$year.id_tipo_evento, ";
    $sql.= "tproceso_eventos_$year.id_tipo_evento_code, tproceso_eventos_$year.indice, tproceso_eventos_$year.cronos ";
    $sql.= "from teventos, tproceso_eventos_$year where teventos.id = tproceso_eventos_$year.id_evento ";
    $sql.= "and ((teventos.id_tipo_evento != tproceso_eventos_$year.id_tipo_evento ";
    $sql.= "or teventos.indice != tproceso_eventos_$year.indice) and (teventos.cronos = tproceso_eventos_$year.cronos)) ";

    $result= $clink->query($sql);
    $nums_register= $clink->num_rows($result);

    $sql= null;
    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        ++$j;
        $id_tipo_evento= setNULL($row['_id_tipo_evento']);
        $id_tipo_evento_code= setNULL_str($row['_id_tipo_evento_code']);
        $indice= setNULL($row['_indice']);

        $sql.= "update tproceso_eventos_$year set id_tipo_evento = $id_tipo_evento, id_tipo_evento_code= $id_tipo_evento_code, ";
        $sql.= "indice= $indice where id = {$row['_id']}; ";

        if ($j >= 500) {
            $clink->multi_query($sql);

            $j= 0;
            $sql= null;
            $r= (float)$i/$nums_register;
            $_r= number_format($r*100, 3);               
            bar_progressCSS(0, "Procesando registros ... $_r%", $r);              
        }
    }
    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(0, "Procesando registros ... 100%", 1); 
}

?>

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>REPARAR id_tipo_evento EN tproceso_eventos</title>

            <?php 
            $dirlibs= "../../";
            require '../../form/inc/_page_init.inc.php'; 
            ?>    

            <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
            <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  

            <script type="text/javascript" src="../../js/home.js"></script>

            <script type="text/javascript" src="../../js/string.js?version="></script>
            <script type="text/javascript" src="../../js/general.js?version="></script>
        
            <style type="text/css">
                /* DEFINITION LIST PROGRESS BAR */
                .progress-block .alert {
                    margin-bottom: 6px!important;
                }  
                label.label {
                    font-size: 1.2em!important;
                    letter-spacing: 0.2em;
                }
                .textlog {
                    font-size: 10px!important;
                }
                .body {
                    margin: 40px 40px 0px 40px;
                    background: white;
                    padding: 20px;
                    
                    overflow-y: no-display;
                }
            </style>

            <script type="text/javascript">
                function validar() {
                    var year= $('#year').val();
                    self.location.href= 'repare_id_tipo_evento_proceso.php?execute=1&year='+year;
                }
            </script>

    <body>
        
        <div class="container body">
            <div class="form-group row col-12">
                <label class="col-form-label col-1">
                    Año:
                </label>
                <div class="col-2">
                    <select id="year" name="year" class="form-control">
                        <?php for ($i= $init_year; $i <= $end_year; $i++) { ?>
                        <option value="<?=$year?>"><?=$year?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <h4>Avance del sistema </h4>
            <div id="progressbar-0" class="progress-block">
                <div id="progressbar-0-alert" class="alert alert-success">
                    En espera para iniciar
                </div>            
                <div id="progressbar-0-" class="progress progress-striped active">
                    <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>                  
            </div>         
            
            <?php 
            $_SESSION['in_javascript_block']= false;
            
            if ($execute)
            execute_id_tipo_evento_proceso($year);
            ?>        

            <?php if (empty($execute) && !$_SESSION['execfromshell']) { ?>
            <button class="btn btn-danger mt-3" onclick="validar()">EJECUTAR</button>
            <?php } ?>
                      
        </div>

    </body>
</html>
