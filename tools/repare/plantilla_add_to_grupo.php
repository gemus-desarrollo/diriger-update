<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 06/05/2020
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';

try {
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
    
} catch (Exception $e) {
    
}

set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

function execute_plantilla_add_to_grupo() {
    global $clink;
    
    $sql= "select * from tprocesos where tipo = "._TIPO_GRUPO;
    $result= $clink->query($sql);
    $nums_register= $clink->num_rows($result);

    $obj_user= null;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        unset($obj_user);
        $obj_user= new Tusuario($clink);
        $obj_user->SetIdEntity(null);
        $obj_user->SetIdProceso($row['id']);
        $array_usuarios= $obj_user->listar(false);

        foreach ($array_usuarios as $id_user => $user) {
            $obj_user->SetIdUsuario($id_user);
            $obj_user->Set();
            $obj_user->update_proceso($row['id'], $row['id_code']);
        }

        $r= (float)$i/$nums_register;
        $_r= number_format($r*100, 3);               
        bar_progressCSS(0, "Procesando registros ... $_r%", $r);        
    }

    bar_progressCSS(0, "Procesando registros ... 100%", 1); 
}

?>

<?php if (empty($execute_argv)) { ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>CREAR PLANTILLA A NIVEL DE GRUPOS DE TRABAJO</title>

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

    <body>
        
        <div class="container body">
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
<?php } ?>
            
            <?php 
            if (!$execute_argv)
                $_SESSION['in_javascript_block']= false;
            
            if ($execute)
            execute_plantilla_add_to_grupo();
            ?>        

            <?php if (empty($execute) && !$_SESSION['execfromshell']) { ?>
            <button class="btn btn-danger" onclick="self.location.href='plantilla_add_to_grupo.php?execute=1'">EJECUTAR</button>
            <?php } ?>
            
<?php if (empty($execute_argv)) { ?>            
        </div>

    </body>
</html>
<?php } ?>
