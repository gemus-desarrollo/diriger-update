<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/14
 * Time: 7:16
 */

session_start();

$_SESSION['output_signal']= 'form';

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/base.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

require_once "../dbtools/update.functions.php";
require_once "../dbtools/repare.class.php";

set_time_limit(0);

$execute= $_GET['execute'];
$year= !empty($_POST['year']) ? $_POST['year'] : null;

function _update_proceso_year_copy($id, $row) {
    global $clink;
    global $year;

    $id_tipo_evento= setNULL($row['id_tipo_evento']);
    $id_tipo_evento_code= setNULL_str($row['id_tipo_evento_code']);
    $indice= setNULL($row['indice']);

    $sql= "update tproceso_eventos_$year set empresarial = {$row['empresarial']}, id_tipo_evento= $id_tipo_evento, ";
    $sql.= "id_tipo_evento_code= $id_tipo_evento_code, indice= $indice where id = $id; ; ";

    return $sql;
}

function _insert_proceso


function _get_proceso_evento_copy($id) {
    global $clink;
    global $year;

    $sql= "select * from tproceso_eventos_$year where id_evento = $id order by cronos desc";
    $result= $clink->query($sql);
    $cant= $clink->num_rows($result);
    if (empty($cant))
        return null;

    $array_ids= null;
    while ($row= $clink->fetch_array($result)) {
        if ($array_ids[$row['id_evento']][$row['id_proceso']])
            continue;
        $array_ids[$row['id_evento']][$row['id_proceso']]= 1;

        $array= array('id'=>$row['id'], 'id_proceso'=>$row['id_proceso'], 'id_evento'=>$row['id_evento'],
            'empresarial'=>$row['empresarial'], 'id_tipo_evento'=>$row['id_tipo_evento'],'indice'=>$row['indice']);
        $array_eventos[$row['id']]= $array;
    }    
    return $array_eventos;
}

function repare_copy_year() {
    global $clink;
    global $year;

    $_year= $year-1;
    $sql= "select id, empresarial, id_tipo_evento, id_tipo_evento_code, indice, copyto ";
    $sql.= "from teventos where empresarial >= 2 and copyto is not null ";
    $sql.= "and year(fecha_inicio_plan) = $_year and id_proceso = {$_SESSION['id_entity']} ";
    $result= $clink->query($sql);
    $nrecords= $clink->num_rows($result);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        preg_match('/[0-9]{4}/', $row['copyto'], $year_copy);
        $year_copy=  (int)$year_copy[0];
        preg_match('/[0-9]{10}/', $row['copyto'], $id_copy);
        $id_copy=  (int)$id_copy[0];
        
        $array_eventos= _get_proceso_evento_copy($id_copy);
        if (is_null($array_eventos))
            continue;

        $sql= null;
        foreach ($array_eventos as $array) {
            if ($row['empresarial'] != $array['empresarial']) {
                $sql.= _update_proceso_year_copy($array['id'], $row);
            }
        }
        if ($sql) 
            $clink->multi_query($sql);

        $r= (float)$i/$nrecords;
        $_r= number_format($r*100, 3);               
        bar_progressCSS(0, "Procesando registros ... $_r%", $r);              
    }

    $sql= "update tproceso_eventos_$year, teventos set tproceso_eventos_$year.indice = teventos.indice ";
    $sql.= "where tproceso_eventos_$year.id_evento = teventos.id and tproceso_eventos_$year.indice is null";
    $result= $clink->query($sql);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ARREGLAR CAPITULOS EN tproceso_eventos</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>

        <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>                

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
                margin: 10px 10px 0px 10px;
                background: white;
                padding: 20px;

                overflow-y: no-display;
            }
            body {
                background: white;
            }            
        </style> 
        
        <script type="text/javascript">
        function ejecutar() {
            var form= document.forms[0];
            var year= $("#year").val();

            if (!Entrada(form.year.value)) {
               alert('Debe insertar el anno destino.');
               return;
            }

            var url= "repare_copy_evento_to_year.php?execute=1&year="+year;
            form.action= url;
            form.submit();  
        }
        </script>
    </head>

    <body>            
        <div class="app-body container-fluid table" style="padding: 30px;">

            <form class="form-horizontal" action="javascript:ejecutar()" method="POST">
                <div class="form-group row">
                    <label class="col-form-label col-1 pull-left">
                        Anno: 
                    </label>
                    <div class="col-md-2 pull-left">
                        <input class="form-control" name="year" id="year" value="<?=$year?>" />
                    </div>
                </div>

                <h4>Avance del sistema </h4>
                <div id="progressbar-0" class="progress-block">
                    <div id="progressbar-0-alert" class="alert alert-success">

                    </div>            
                    <div id="progressbar-0-" class="progress progress-striped active">
                        <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only"></span>
                        </div>
                    </div>                  
                </div>  

                <?php if (empty($execute)) { ?>
                <button class="btn btn-danger mt-3" type="submit" >EJECUTAR</button>
                <?php } ?>                  

            </form>       
            <?php 

            if ($execute) {
                $_SESSION['in_javascript_block']= false;
                repare_copy_year();
            }    
            ?>
          
        </div>
    </body>
</html>

    
    