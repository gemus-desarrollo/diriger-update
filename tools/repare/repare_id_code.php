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
    $csfr_token='123abc';
    require_once "../../php/setup.ini.php";
    require_once _PHP_DIRIGER_DIR."config.ini";    

    require_once "../../php/config.inc.php";
    require_once "../../php/class/connect.class.php";
    require_once "../../php/class/base.class.php";
    
    require_once "../../php/class/library.php";
    require_once "../../php/class/library_string.php";
    require_once "../../php/class/library_style.php";
    
} catch (Exception $e) {
    
}

set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

require_once "repare_id.inc";

function get_id_name($id_code_name) {
    $id_name= null;
    $ipos= stripos($id_code_name, "_code");
    $id_name= !empty($ipos) ? substr($id_code_name, 0, $ipos) : null;
    return $id_name;
}

function get_table($id_name) {
    global $array_tables_list;    
    reset($array_tables_list);
    
    $name= substr($id_name, 4);
    
    foreach ($array_tables_list as $table) {
        if (stripos($table, $name) !== false)
            return $table;
    }
    return null;
}

function table_fields(&$table) {
    global $clink;
    
    $sql= "describe {$table['table']}";
    $result= $clink->query($sql);
    
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        if (stripos($row['Field'],'id_') !== false && stripos($row['Field'], '_code') !== false) {
            ++$i;
            $id_name= get_id_name($row['Field']);
            if (empty($id_name))
                continue;
            $table['Fields'][]= array('id_code'=>$row['Field'], 'id'=>$id_name);
    }   }
    
    return $i;
}

function update_field($id, $table, $field, $field_code) {
    $table_origen= get_table($field);
    if (empty($table_origen))
        return null;
    
    $sql= "update $table, $table_origen set {$table}.{$field_code}= {$table_origen}.id_code ";
    $sql.= "where {$table}.id = $id and {$table}.{$field} = {$table_origen}.id; ";
    return $sql;
}

function repare_table($table) {
    global $clink;
    
    $i= 0;
    $j= 0;
    $nums_register= 0;
    foreach ($table['Fields'] as $field) {
        if ($field['id'] == 'id')
            continue;
        
        $sql= "select * from {$table['table']} where {$field['id']} is not null and {$field['id_code']} is null";
        $result= $clink->query($sql);
        $nums_register+= $clink->num_rows($result);
        
        $sql= null;
        while ($row= $clink->fetch_array($result)) {
            $sql.= update_field($row['id'], $table['table'], $field['id'], $field['id_code']);
            
            ++$i;
            ++$j;
            if ($j >= 5000 && $sql) {
                $result= $clink->multi_query($sql);
                $j= 0;
                $sql= null;
                
                $r= (float)$i/$nums_register;
                $_r= number_format($r*100, 3);               
                bar_progressCSS(1, "Procesando registros ... $_r%", $r);
            }
    }   }
    
    if (!empty($sql)) 
        $result= $clink->multi_query($sql);
    bar_progressCSS(1, "Procesando registros ... 100%", 1);
}

function execute_repare_rebuild() {
    global $clink;
    global $num_tables;
    global $array_tables;
    
    init_tables();
    
    $i= 0;
    foreach ($array_tables as $table) {
        ++$i;
        $nums= table_fields($table);
        
        $r= (float)$i/$num_tables;
        $_r= number_format($r*100, 3);
        bar_progressCSS(0, "Procesando tabla:{$table['table']} ... $_r%", $r); 
        
        if ($nums)
            repare_table($table);
    }
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

            <title>REPARAR CAMPO id_code</title>

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
        </head>
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
<?php } ?>
            
            <?php 
            if (!$execute_argv)
                $_SESSION['in_javascript_block']= false;
            
            if ($execute)
                execute_repare_rebuild();
            ?>        

            <?php if (empty($execute) && !$_SESSION['execfromshell']) { ?>
            <button class="btn btn-danger mt-3" onclick="self.location.href='repare_id_code.php?execute=1'">EJECUTAR</button>
            <?php } ?>
            
<?php if (empty($execute_argv)) { ?>            
        </div>

    </body>
</html>
<?php } ?>
