<?php
/**
 * Created by Visual Studio Code.
 * User: PhD. Geraudis Mustelier
 * Date: 11/07/2020
 * Time: 7:16
 */

/*
 * update 2020-06-28 21:14
 */

session_start();

$csfr_token='123abc';
require_once "../../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../../php/config.inc.php";

$apply_open_connect= false;
require_once "../../../php/class/connect.class.php";

require_once "../../../php/class/base.class.php";

require_once "../../../php/class/library.php";
require_once "../../../php/class/library_string.php";
require_once "../../../php/class/library_style.php";

require_once "origen_db.class.php";
require_once "interface.class.php";

global $clink_origen;
global $clink_target;

set_time_limit(0);

define("_NUM_ROWS_INSERT", 2000);

if (!$execute_argv) {
    $execute= is_null($execute) ? $_GET['execute'] : $execute;
    $db_origen= !empty($_GET['db_origen']) ? $_GET['db_origen'] : null;
    $db_target= !empty($_GET['db_target']) ? $_GET['db_target'] : null;
    $code_origen= !empty($_GET['code_origen']) ? strtoupper($_GET['code_origen']) : null;
    $code_target= !empty($_GET['code_target']) ? strtoupper($_GET['code_target']) : null;
} else {
    $execute= !is_null($argv[2]) ? $argv[2]: null;
    $db_origen= !empty($argv[4]) ? $argv[4] : null;
    $db_target= !empty($argv[6]) ? $argv[6] : null;
    $code_origen= !empty($argv[3]) ? strtoupper($argv[3]) : null;
    $code_target= !empty($argv[5]) ? strtoupper($argv[5]) : null;
}

$obj_origen= new Torigen_db($db_origen);
?>

<?php if (!$execute_argv) { ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>FUNDIR DOS BASE DE DATOS</title>

            <?php 
            $dirlibs= "../../../";
            require '../../form/inc/_page_init.inc.php'; 
            ?>

            <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
            <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>

            <link rel="stylesheet" href="../../../libs/windowmove/windowmove.css">
            <script type="text/javascript" src="../../../libs/windowmove/windowmove.js"></script>

            <script type="text/javascript" src="../../../js/home.js"></script>

            <script type="text/javascript" src="../../../js/string.js?version="></script>
            <script type="text/javascript" src="../../../js/general.js?version="></script>

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
            function ejecutar() {
                var db_origen= $('#db_origen').val();
                var db_target= $('#db_target').val();
                var code_origen= $('#code_origen').val();
                var code_target= $('#code_target').val();

                var url= 'index.php?execute=1&db_origen='+db_origen+'&db_target='+db_target;
                url+= '&code_target='+code_target+'&code_origen='+code_origen;
                self.location.href= url;
            }
            </script>
    </head>

    <body>
        <div class="container">
            <div class="card card-primary" style="margin-top: 30px;">
                <div class="card-header">FUNDIR DOS BASE DE DATOS</div>

                <div class="card-body">
                    <div class="col-md-12 col-lg-12">

                        <div class="col-md-6 col-lg-6 form-horizontal">
                            <label class="col-md-12 col-lg-12">
                                Origen:
                            </label>

                            <div class="form-group row">
                                <label class="label-control text col-md-2 col-lg-2">
                                    Codigo:
                                </label>
                                <div class="col-md-3 col-lg-3">
                                    <input type="text" class="form-control" id="code_origen" name="code_origen" value="<?=$code_origen?>" />
                                </div>
                            </div>

                            <div class="form-group row">
                                 <?php
                                 $result= $obj_origen->listar_dbs();
                                 ?>
                                 <label class="label-control text col-md-2 col-lg-2">
                                     Base de datos:
                                 </label>
                                 <div class="col-md-10 col-lg-10">
                                     <select class="form-control" id="db_origen" name="db_origen">
                                         <option value="0">select .... </option>
                                         <?php
                                         while ($row= $clink_target->fetch_array($result)) {
                                         ?>
                                         <option value="<?=$row[0]?>" <?php if ($row[0] == $db_origen) echo "selected='selected'"?>><?=$row[0]?></option>
                                         <?php } ?>
                                     </select>
                                 </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 form-horizontal">
                            <label class="col-md-12 col-lg-12">
                                Destino:
                            </label>

                            <div class="form-group row">
                                <label class="label-control text col-md-2 col-lg-2">
                                    Codigo:
                                </label>
                                <div class="col-md-3 col-lg-3">
                                    <input type="text" class="form-control" id="code_target" name="code_target" value="<?=$code_target?>" />
                                </div>
                            </div>

                            <div class="form-group row">
                                 <label class="label-control text col-md-2 col-lg-2">
                                     Base de datos:
                                 </label>

                                <div class="col-md-10 col-lg-10">
                                    <select class="form-control" id="db_target" name="db_target">
                                        <option value="0">select ... </option>
                                        <?php
                                        $clink_target->data_seek($result);
                                        while ($row= $clink_target->fetch_array($result)) {
                                        ?>
                                        <option value="<?=$row[0]?>" <?php if ($row[0] == $db_target) echo "selected='selected'"?>><?=$row[0]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div id="_submit" class="btn-block btn-app">
                        <?php if (!$execute) { ?>
                        <button class="btn btn-danger" type="button" onclick="ejecutar()">Ejecutar</button>
                        <?php } ?>
                        <button class="btn btn-warning" type="button" onclick="self.location.href='index.php?execute=0'">Cerrar</button>
                    </div>

                    <br/>
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

                    <h4>Avance de la tabla:</h4>
                    <div id="progressbar-1" class="progress-block">
                        <div id="progressbar-1-alert" class="alert alert-warning">
                            Esta operación puede durar varios minutos. Por favor espere ...
                        </div>
                        <div id="progressbar-1-" class="progress progress-striped active">
                            <div id="progressbar-1-bar" class="progress-bar bg-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>
<?php } ?>

<?php
if ($execute) {
    if (empty($db_origen) || empty($db_target)) {
        die("\nERROR: No estan definidas las base de datos.");
    }
    if (empty($code_origen) || empty($code_target)) {
        die("\nERROR: No estan definidas los codigo de las base de datos.");
    }

    if (!$execute_argv) {
        $_SESSION['in_javascript_block']= false;
    }
    if ($_SESSION['output_signal'] == 'shell') {
        echo "\nInicio:".date('Y-m-d H:i')."\n";
    }
    $obj_target= new Tinterface_target($db_target);
    $obj_target->clink_origen= $obj_origen->clink;
    $obj_target->code_origen= $code_origen;
    $obj_target->db_origen= $db_origen;
    $obj_target->code_target= $code_target;

    $obj_target->set_id_procesos();

    $obj_origen->prepare();
    $obj_target->array_tables= $obj_origen->array_tables;
    $obj_target->array_tables_list= $obj_origen->array_tables_list;

    $obj_target->do_list_table();
    $obj_origen->finish();

    if ($_SESSION['output_signal'] == 'shell') {
        echo "\nFin:".date('Y-m-d H:i')."\n";
    }
}
?>
