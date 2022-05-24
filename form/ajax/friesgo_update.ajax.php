<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/riesgo.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'edit') 
    $action= 'add';
if ($action == 'add') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

$time= new TTime();

$year= $_GET['year'];
$month= $_GET['month'];

$time->SetYear($year);
$time->SetMonth($month);
$day= $time->longmonth();
$time->SetDay($day);

$id_riesgo= $_GET['id_riesgo'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['locl_proceso_id'];

$obj= new Triesgo($clink);
$obj->SetIdRiesgo($id_riesgo);
$obj->SetYear($year);
$obj->SetMonth($month);

$obj->Set();
$id_riesgo_code= $obj->get_id_riesgo_code();
$estado= $obj->GetEstado();
$fecha= $obj->get_kronos();
$obj->SetIdProceso($id_proceso);
$array= $obj->getRiesgo_reg($id_riesgo);
$nivel= $obj->getNivel($array['frecuencia'], $array['impacto']);
?>

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         

        <script language="javascript">
            var nivel= Array();
            nivel[1]= new Array(0, 1, 1, 2, 3, 4);
            nivel[2]= new Array(0, 1, 2, 3, 4, 5);
            nivel[3]= new Array(0, 2, 3, 4, 5, 6);        
            nivel[4]= new Array(0, 3, 4, 5, 6, 7);
            nivel[5]= new Array(0, 4, 5, 6, 7, 7);

        var nivel_array= new Array('','TRIVIAL','BAJO','MODERADO','SIGNIFICATIVO','ALTO','MUY ALTO','SEVERO');

        function validar() {
            var form= document.forms['friesgo_update'];

            if (form.frecuencia.value == 0) {
                alert('Por favor, especifique un valor cuantitativo para la probabilidad de manifestación del riesgo.'); 
                return;
            }	 

            if (form.impacto.value == 0) {
                alert('Por favor, especifique un valor cuantitativo para el daño a producirse de manifestarse el riesgo.'); 
                return;
            }

            if (!Entrada(form.descripcion.value)) {
                alert('Debe introducir las observaciones para el estado actual en la gestión de este riesgo.'); 
                return;
            } 

            if (!valida_estado()) 
                return;

            ejecutar('register');
        }

        function refreshnivel() {
            var form= document.forms['friesgo_update'];

            if (form.frecuencia.value > 0 && form.impacto.value > 0) {
                _nivel= nivel[form.frecuencia.value][form.impacto.value];
                document.getElementById('div-nivel').innerHTML= nivel_array[_nivel];
            }
        }


        function valida_estado() {
            var form= document.forms['friesgo_update'];

            refreshnivel();

             if (!Entrada(form.fecha.value)) {
                alert('Introduzca la fecha a la que corresponde el registro.');
                return false;
             } else if (!isDate_d_m_yyyyy(form.fecha.value)) {
                alert('Fecha con formato incorrecto. (d/m/yyyy) Ejemplo: 01/01/2010');
                return false; 
             }

            if (form.frecuencia.value == 0 || form.impacto.value == 0) {
                text= "No podrá modificar el estado del riesgo hasta que no asigne los nuevos valores ";
                text+= "a la frecuencia o probabilidad de ocurrencia y a la severidad o impacto."
                alert(text);
                form.estado.value= form.id_estado.value;

                return false;
            }

            if ((form.estado.value == 3 || form.estado.value == 4) && _nivel > 3) {
                text= "Existe incongruencia. Solo es posible eliminar o mitigar los riesgos cuyo nivel ";
                text+= "de riesgo este clasificado como 'TRIVIAL', 'BAJO' o 'MODERADO'.";
                alert(text);
                form.estado.value= 2;

                document.getElementById('div-nivel').innerHTML= nivel_array[_nivel];

                return false;
            }

            return true;	
        }
        </script>

        <script type="text/javascript">	
            $(document).ready(function() {
                focusin=function(_this) {       
                   tabId= $(_this).parents('* .tabcontent');         
                   $(".tabcontent").hide();
                   $('#nav-'+tabId.prop('id')).addClass('active');
                   tabId.show();
                   $(_this).focus();
               }

            //When page loads...
            $(".tabcontent").hide(); //Hide all content
            $("ul.nav li:first a").addClass("active").show(); //Activate first tab
            $(".tabcontent:first").show(); //Show first tab content

            //On Click Event
            $("ul.nav li a").click(function() {
                $("ul.nav li a").removeClass("active"); //Remove any "active" class
                $(this).addClass("active"); //Add "active" class to selected tab
                $(".tabcontent").hide(); //Hide all tab content

                var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
                $("#" + activeTab).fadeIn(); //Fade in the active ID content
                //         $("#" + activeTab + " .form-control:first").focus();
                return false;
            });             
                
            $('#div_fecha').datepicker({
                format: 'dd/mm/yyyy'
            }); 
                
            tinymce.init({
                selector: '#descripcion',
                theme: 'modern',
                height: 160,
                language: 'es',               
                plugins: [
                   'advlist autolink lists link image charmap print preview anchor textcolor',
                   'searchreplace visualblocks code fullscreen',
                   'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify '+
                        '| bullist numlist outdent indent | removeformat | help',

                content_css: '../css/content.css'
            });               
                
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });	
    </script>

    <div class="card card-primary">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag">REGISTRO</div>            
                <div class="col-1 m-0 ">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                            <i class="fa fa-close"></i>
                        </a>    
                    </div>                      
                </div>            
            </div>
        </div>
        
        <div class="card-body info-panel form">
                <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                <?php if ($action != 'list') { ?>
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Observaciones</a></li>
                <?php } ?>
                <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Registros Anteriores</a></li>
            </ul>

            <form id="friesgo_update" name="friesgo_update" class="form-horizontal" action='javascript:validar()'  method=post>
                <input type="hidden" name="exect" value="<?= $action ?>" />
                <input type="hidden" name="id" value="<?= $id_riesgo ?>" />
                <input type="hidden" name="id_code" value="<?= $id_riesgo_code ?>" />
                <input type="hidden" name="id_proceso" value=<?= $id_proceso ?> />
                <input type="hidden" name="proceso" value="<?= $id_proceso ?>" />
                <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />
                <input type="hidden" name="year" value="<?= $year ?>" />
                <input type="hidden" name="month" value="<?= $month ?>" />
                <input type="hidden" name="menu" value="riesgo_update" />
        
                <?php if ($action != 'list') { ?>
                <!-- tab1 -->
                    <div class="tabcontent " id="tab1">
                        <label class="alert alert-info">Título: <?= $obj->GetNombre() ?></label> 
                        
                        <div class="form-group row">
                            <div class="row col-4">
                                <label class="col-form-label col-3">
                                    Estado:
                                </label>
                                <div class="col-9">
                                    <input type="hidden" name="id_estado" value="<?= $estado ?>" />

                                    <select name="estado" id="estado" class="form-control" onchange="valida_estado()">
                                        <?php for ($i = 1; $i < 5; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ((int) $i == (int) $estado) echo "selected" ?>><?= $estado_riesgo_array[$i] ?></option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row col-4">
                                <label class="col-form-label col-4">
                                    Probalilidad:
                                </label>
                                <div class="col-8">
                                    <select name="frecuencia" id="frecuencia" class="form-control" onChange="refreshnivel()">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i = 1;  $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $array['frecuencia']) echo "selected" ?>><?= $frecuencia_array[$i] ?></option>
                                    <?php } ?>
                                    </select>          
                                </div>
                            </div>
                            <div class="row col-4">
                                <label class="col-form-label col-4">
                                    Severidad:
                                </label>
                                <div class="col-8">
                                    <select name="impacto" id="impacto" class="form-control" onChange="refreshnivel()">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i = 1; $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $array['impacto']) echo "selected" ?>><?= $impacto_array[$i] ?></option>
                                    <?php } ?>
                                    </select>            
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="row col-6">
                                <label class="col-form-label col-4">
                                    Nivel de deteción:
                                </label>
                                <div class="col-6">
                                    <select name="deteccion" id="deteccion" class="form-control">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i = 1; $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $array['deteccion']) echo "selected"; ?>><?= $deteccion_array[$i] ?></option>
                                    <?php } ?>
                                    </select>    
                                </div>
                            </div>

                            <div class="row col-6">
                                <label class="col-form-label col-sm-5 col-md-5 col-lg-4">
                                    Nivel del Riesgo:
                                </label>
                                <div class="col-6">
                                    <div id="div-nivel" class="alert alert-danger" style="padding: 4px; margin: 4px;"><?= $nivel_array[$nivel] ?></div>
                                </div>
                            </div>                             
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Fecha de registro: 
                            </label>
                            <div class="col-3">
                                <div class="input-group date" id="div_fecha" data-date-language="es">
                                    <input type="text" id="fecha" name="fecha" class="form-control date" value="<?= odbc2date($fecha) ?>" readonly />
                                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                </div>  
                            </div>
                        </div>                             
                    </div> <!-- tab1 --> 
                    
                    
                    <!-- tab2 -->
                    <div class="tabcontent " id="tab2">
                        <div class="col-xs-12 col-12">
                            <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
                        </div> 
                    </div> <!-- tab2 --> 
                <?php } ?>
                    
                    
                <!-- tab3 -->         
                    <div class="tabcontent" id="tab3" >
                        <table id="table" class="table table-striped"
                            data-toggle="table"
                            data-height="320"
                            data-search="true"
                            data-show-columns="true"> 
                            <thead>
                                <tr>
                                    <th>ESTADO</th>
                                    <th>NIVEL</th>
                                    <th>DETECCIÓN</th>
                                    <th>FECHA Y HORA</th>
                                    <th>RESPONSABLE</th>
                                    <th>OBSERVACIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $obj->SetIdProceso($id_proceso);
                                $result = $obj->getAvance($id_riesgo, true);

                                while ($row = $clink->fetch_array($result)) {
                                    ?>
                                    <tr>
                                        <td><?= $estado_riesgo_array[$row['estado']] ?></td>
                                        <td>
                                            <?php
                                            $nivel = $obj->getNivel($row['frecuencia'], $row['impacto']);
                                            echo $nivel_array[$nivel] . ' (' . $nivel . ')';
                                            ?>
                                        </td>
                                        <td><?php echo $deteccion_array[$row['deteccion']] . ' (' . $row['deteccion'] . ')'; ?></td>
                                        <td><?= odbc2date($row['cronos']) ?></td>
                                        <td><?= $row['responsable']; ?></td>
                                        <td><?= $row['observacion'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                    </table><!-- tab3 -->
                    </div>                        
        
                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action != 'list') { ?> <button class="btn btn-primary" type="submit"> Aceptar</button><?php } ?>  
                    <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                </div>

                <div id="_submited" class="submited" align="center" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
                </div>                         
            </form> 
        </div>
    </div>    
     


