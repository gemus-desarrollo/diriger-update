<?php
/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier
 * Date: 06/05/2021
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";    

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/base.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

require_once 'repare_duplicate_register_table.inc';

$table_name= !empty($_POST['table_name']) ? $_POST['table_name'] : null;
$index_name= !empty($_POST['index_name']) ? $_POST['index_name'] : null;
$column_basic= !empty($_POST['column_basic']) ? $_POST['column_basic'] : null;
$plus_text= !empty($_POST['plus_text']) ? $_POST['plus_text'] : null;

$obj= new Trepare_index($clink);
$obj->SetTableName($table_name);
$obj->SetIndexName($index_name);
$obj->SetPlusText($plus_text);

$array_selected= $execute >= 2 ? $obj->list_selected() : null;

?>

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
            body {
                overflow: scroll;
            }
            .container {
                overflow: visible;
            }
            .jumbotron {
                padding: 30px 20px;
                margin-top: 30px;
            }
        </style>

        <script type="text/javascript">
            var array_selected_columns= [];
            var nchecked= 0;

            function _build_sql() {
                var sql;

                sql= "CREATE UNIQUE INDEX "+$('#index_name').val() + " ON " + $('#table_name').val() + " (";
                i= 0;
                for (col in array_selected_columns) {
                    if (!array_selected_columns[col])
                        continue;
                    ++i;
                    if (i > 1) 
                        sql+= ", ";
                    sql+= array_selected_columns[col][0];
                    sql+= parseInt(array_selected_columns[col][1]) > 0 ? '('+array_selected_columns[col][1]+')' : '';    
                }
                sql+= ');';

                if (i > 0)
                    $('#sql-index').html(sql);
                else 
                    $('#sql-index').html('');    

                $('#cant_selected').val(i);
            }

            function build_sql() {
                var nchecked= 0;

                $('.chk_selected').each(function() {
                    ipos= $(this).attr('id').indexOf('-');
                    index= $(this).attr('id').substring(ipos+1, $(this).attr('id').length);
                    length= $('#icolumn_length-'+index).val();

                    if ($(this).is(':checked')) {
                        ++nchecked;
                        array_selected_columns[index]= [$(this).val(), length];
                    } 
                });

                $('#cant_selected').val(nchecked);
                _build_sql();
            }

            function validar(flag) {
                var nchecked= 0;
                var basic_check= false;

                if (!Entrada($('#table_name').val())) {
                    alert("Debe especificar el nombre de la tabla que será revizada.");
                    return;
                }

                if (flag >= 2) {
                    if (!Entrada($('#index_name').val())) {
                        alert("Debe especificar el nombre del índice unico o restricción que pretende crear");
                        return;
                    }
                    
                    for (i= 1; i <= $('#cant_columns').val(); i++) {
                        if ($('#icolumn_chk-'+i).is(':checked'))
                            ++nchecked;
                        if ($('#icolumn_select-'+i).is(':checked')) {
                            basic_check= true;
                            $('#column_basic').val($('#icolumn_select-'+i).val());
                        }
                    }
                }

                if (flag >= 2) {
                    if (nchecked == 0) {
                        alert("Debe selecionar los campos que formaran el índice único o restricción.");
                        return;
                    }
                    if (!basic_check) {
                        alert("Debe selecionar un campo texto a ser modificado para eliminar la duplicidad");
                        return; 
                    }                    
                }

                if (flag == 3) {
                    if (!Entrada($('#plus_text').val())) {
                        alert("Debe escribir el caracter o el texto a agregar para modificar el campo selecionado y asi eliminar la duplicidad.");
                        return;
                    }
                }

                document.forms[0].action= 'repare_duplicate_register_table.php?execute='+flag;
                document.forms[0].submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                var length;
                var ipos;
                var index;

                build_sql();

                $('.chk_selected').click(function() {
                    ipos= $(this).attr('id').indexOf('-');
                    index= $(this).attr('id').substring(ipos+1, $(this).attr('id').length);
                    length= $('#icolumn_length-'+index).val();

                    if ($(this).is(':checked')) {
                        ++nchecked;
                        array_selected_columns[index]= [$(this).val(), length];
                    } else {
                        --nchecked;
                        array_selected_columns[index]= false;
                    }
                    
                    $('#cant_selected').val(nchecked);
                    _build_sql();
                });

                $('.radio_selected').click(function() {
                    $('#column_basic').val($(this).val());
                });
            });
        </script>
    </head>

    <body>
        <div class="container">
            <form class="horizontal" action="#" method="POST"> 
                <input type="hidden" id="cant_selected" name="cant_selected" value="0" />
                <input type="hidden" id="column_basic" name="column_basic" value="<?=$column_basic?>" />

                <div class="jumbotron">
                    <div class="form-group row">
                        <label class="col-2">
                            Nombre de la tabla
                        </label>
                        <div class="col-6">
                            <input type="text" class="form-control" id="table_name" name="table_name" value="<?=$table_name?>" />
                            <small id="table_name_help" class="form-text text-muted">Nombre de la tabla que sera revizada.</small>                            
                        </div>
                    </div> 
                    <button type="button" class="btn btn-md btn-primary" onclick="validar(1)">
                        Siguiente >
                    </button>
                </div>

                <div class="jumbotron">
                    <div class="form-group row">
                        <label class="col-3">
                            Nombre de la restricción o índice
                        </label>
                        <div class="col-6">
                            <input type="text" class="form-control" id="index_name" name="index_name" value="<?=$index_name?>" />
                            <small id="index_name_help" class="form-text text-muted">Nombre del índice o restricción que se creará.</small>                            
                        </div>
                    </div> 

                    <label>
                        Listado de los campos
                    </label>
                    <div class="col-12">
                        <table class="table">
                            <thead>
                                <th scope="col">Nombre</th>
                                <th scope="col">Selecionado</th>
                                <th scope="col">A modificar</th>
                                <th scope="col">Longitud</th>
                            </thead>
                            <tbody>
                                <?php 
                                    $array_columns= null;
                                    if ($execute >= 1)
                                        $array_columns= $obj->get_columns();

                                    $i= 0;
                                    foreach ($array_columns as $col)  {
                                        ++$i;
                                ?>  <tr>
                                        <td>
                                            <?=$col['name']?>
                                        </td>
                                        <td>
                                            <?php 
                                            $checked= array_key_exists($col['name'], $array_selected) ? "checked='checked'" : ""; 
                                            ?>
                                            <input type="checkbox" class="chk_selected" id="icolumn_chk-<?=$i?>" name="icolumn_chk-<?=$i?>" value="<?=$col['name']?>" <?=$checked?> />
                                        </td>
                                        <td>
                                            <?php 
                                            $checked= $col['name'] == $column_basic ? "checked='checked'" : ""; 
                                            ?>                                            
                                            <input type="radio" class="radio_selected" id="icolumn_select-<?=$i?>" name="icolumn_select" value="<?=$col['name']?>" <?=$checked?> />
                                        </td>
                                        <td>
                                            <input type="text" id="icolumn_length-<?=$i?>" name="icolumn_length" value="<?=$array_selected[$col['name']]['length']?>" />
                                        </td>                                        
                                    </tr>    
                                <?php            
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" id="cant_columns" name="cant_columns" value="<?=$i?>" />

                    <div id="sql-index" class="alert alert-success">
                        
                    </div>

                    <div class="form-group row">
                        <label class="col-3">
                            Carácter o texto que sera agregado</br>Por defecto aparecerá el texto '-copy' al final
                        </label>
                        <div class="col-1" style="min-width: 100px;">
                            <input type="text" class="form-control" id="plus_text" name="plus_text" value="<?=!empty($plus_text) ? $plus_text : "-copy"?>" />                         
                        </div>
                    </div> 

                    <button type="button" class="btn btn-md btn-primary" onclick="validar(2)">
                        Siguiente >
                    </button>
                </div>

                <div class="jumbotron">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    id
                                </th>
                                <?php 
                                foreach ($array_selected as $col)  { 
                                    if ($col['name'] == 'id')
                                        continue;
                                ?>
                                    <th>
                                        <?=$col['name']?>
                                    </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($execute >= 2)  {
                                $array_registers= $obj->list_duplicate();

                                foreach ($array_registers as $row) {
                                    ?>
                                    <tr>
                                        <td>
                                            <?=$row['id']?>
                                        </td>
                                        <?php
                                        reset($array_selected);
                                        foreach ($array_selected as $col)  {
                                        ?>                                         
                                            <td>
                                                <?=$row[$col['name']]?>
                                            </td>
                                        <?php } ?>
                                        </tr>
                                    <?php    
                                }
                            }
                            ?>
                        </tbody>    
                    </table>  

                    <button type="button" class="btn btn-md btn-primary" onclick="validar(3)">
                        Siguiente >
                    </button>
                </div>
            </form>


            <h4>Avance del sistema </h4>
            <div id="progressbar-0" class="progress-block">
                <div id="progressbar-0-alert" class="alert alert-danger">
                    En espera para iniciar
                </div>            
                <div id="progressbar-0-" class="progress progress-striped active">
                    <div id="progressbar-0-bar" class="progress-bar bg-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>                  
            </div>                  
        </div>        
    </body>
</html>  

<div class="container mt-3">
    <div class="col-12">
        <?php 
        if ($execute == 3)  {
            $error= $obj->modify_duplicate();

            if (empty($error)) 
                $error= $obj->create_unique_index();

            if (empty($error)) {
            ?>
                <div class="alert alert-success">
                    La generación del índice único o restrición se ha generado con exíto.
                </div>
        <?php    
            } else {
        ?>
                <div class="alert alert-danger">
                    Se ha producido un error. No se ha creado el índice único o restrición.<p> <?=$obj->error?>.</p>
                </div>    
        <?php        
            }
        }   
        ?>
    </div>
</div>

