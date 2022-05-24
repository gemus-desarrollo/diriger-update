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
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/politica.class.php";
require_once "../../php/class/inductor.class.php";
require_once "../../php/class/indicador.class.php";
require_once "../../php/class/programa.class.php";
require_once "../../php/class/objetivo_ci.class.php";
require_once "../../php/class/proceso.class.php";

$_SESSION['debug']= null;

$id= $_GET['id'];
$_item= $_GET['_item'];
$_item_sup= $_GET['_item_sup'];
$id_sup= $_GET['id_sup'];

$signal= $_GET['signal'];
$id_proceso= $_GET['id_proceso'];
$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$fecha= $day.'/'.$month.'/'.$year;

$id_proceso_code= !empty($id_proceso) ? get_code_from_table('tprocesos', $id_proceso) : null;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;	
$text= null;
$plus= null;


if ($_item == 'pol') {
    $obj= new Tpolitica($clink);
    $text= "Política o Lineamineto";
    if ($signal == 'politica') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'ind') {
    $obj= new Tinductor($clink);
    $text= "Objetivo de Trabajo";
    if ($signal == 'inductor') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'obj') {
    $obj= new Tobjetivo($clink);
    $text= "Objetivo Estratégico";
    if ($signal == 'objetivo') 
        $plus= "y esta es una operación irreversible";	
}

if ($_item == 'per') {
    $obj= new Tperspectiva($clink);
    $text= "Perspectiva";
    if ($signal == 'perspectiva') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'prog') {
    $obj= new Tprograma($clink);
    $text= "Programa";
    if ($signal == 'programa') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'obj_ci') {
    $obj= new Tobjetivo_ci($clink);
    $text= "Objetivo de Control Interno";
    if ($signal == 'objetivo_ci') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'indi') {
    $obj= new Tindicador($clink);
    $text= "Indicador";
    if ($signal == 'indicador') 
        $plus= "y esta es una operación irreversible";
}

if ($_item == 'obj_sup') {
    $obj= new Tobjetivo($clink); 
    $obj->SetIfObjetivoSup();
    if ($signal == 'objetivo_sup') 
        $plus= "y esta es una operación irreversible";  
} 

$obj->SetYear($year);
$obj->Set($id);
$_id_proceso= $obj->GetIdProceso();

$obj_prs= new Tproceso($clink);
$proceso= null;

if (!empty($_id_proceso)) {
    $obj_prs->Set($_id_proceso);
    $proceso= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
}

?>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['fdelete'];

            form._radio_date.value= 0;
            if (document.getElementById('radio_date1').checked) 
                form._radio_date.value= 1;
            if (document.getElementById('radio_date2').checked) 
                form._radio_date.value= 2;	
            if (form.year.value == form.inicio.value) 
                form._radio_date.value= 2;
            if (form._radio_date.value == 0) {
                alert("No ha especificado el periodo de años del que será eliminado el <?= $text ?>");
                return;
            }

            function this_1() {            
                <?php if (empty($_item_sup) || $_item == $_item_sup) { ?>ejecutar('fdelete');<?php } ?>
                <?php if ((!empty($_item_sup) && $id_sup) && $_item != $_item_sup) { ?>eliminar_link('fdelete');<?php } ?>            
            }        
 
            if (form._radio_date.value == 2 || form.year.value == form.inicio.value) {
                confirm("Ha seleccionado la opción de eliminar el <?= $text ?> del sistema <?= $plus ?>. ¿Desea continuar?", function(ok) {
                    if (!ok) 
                        return;
                    else 
                        this_1();
                });
            } else {
                this_1();
            }
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            if (document.getElementById('year').value == document.getElementById('inicio').value) {
                document.getElementById('radio_date2').checked= true;
                document.getElementById('_radio_date').value= 2;
            }            
            
            <?php if (!is_null($error)) { ?>
            alert("<?= str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });
    </script>

    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <div class="row">
                    <div id="win-title" class="panel-title ajax-title win-drag col-10"></div>
                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href= "javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                            
                        </div>
                    </div>      
                </div>
            </div>
            
            <div class="card-body">
                <form id="fdelete" name="fdelete" action="javascript:validar()"  method="post">
                    <input type="hidden" name="exect" value="delete" />	
                    <input type="hidden" name="id" id="id" value="<?=$id?>" />
                    <input type="hidden" name="id_sup" id="id_sup" value="<?=$id_sup?>" />

                    <input type="hidden" name="_item" id="_item" value="<?=$_item ?>" />
                    <input type="hidden" name="_item_sup" id="_item_sup" value="<?=$_item_sup?>" />

                    <input type="hidden" name="id_proceso" value="<?=$id_proceso ?>" />
                    <input type="hidden" name="id_proceso_code" value="<?=$id_proceso_code?>" />

                    <input type="hidden" name="menu" value="fdelete" />	
                    <input type="hidden" name="id_responsable" value="<?=$_SESSION['id_usuario']?>" />
                    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

                    <input type="hidden" id="_radio_date" name="_radio_date" value="0" />
                    <input type="hidden" id="_radio_user" name="_radio_user" value="0" />
                    <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />

                    <input type="hidden" name="inicio" id="inicio" value="<?=$obj->GetInicio()?>"  />
                    <input type="hidden" name="year" id="year" value="<?=$year?>" />
                    <input type="hidden" name="month" id="month" value="<?=$month?>" />
                    <input type="hidden" name="day" id="day" value="<?=$day?>" />

                    <input type="hidden" name="if_chief" id="if_chief" value="<?=$if_chief?>" />
                    
                    <div class="form-group row">
                        <label class="col-form-label col-12">
                            <strong><?=$text?>: </strong><?=$obj->GetNombre()?>
                        </label>
                        <label class="col-form-label col-12">
                            <strong>Origen: </strong><?=$proceso?>
                        </label>
                        <label class="col-form-label col-12">
                            <strong>Periodo: </strong><?php echo $obj->GetInicio().' / '.$obj->GetFin();?>
                        </label>
                        <div class="checkbox">
                            <label>
                                <input type="radio" name="radio_date" id="radio_date1" value="1" />
                                Eliminar el <?=$text?> solo a partir del año <?=$year?>. En los años anteriores se mantendrán las relaciones.                                
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="radio" name="radio_date" id="radio_date2" value="2" /> 
                                Eliminar <?=$text?> de todos los años en los que está definido. Será eliminada toda relación.
                                <?php if (!is_null($plus)) { ?>Este <?=$text?> desaparecerá del sistema. <?php echo $plus;  } ?>                                
                            </label>
                        </div>
                    </div>                    

                    <!-- buttom -->
                    <div id="_submit" class="btn-block btn-app">
                        <button class="btn btn-primary" type="submit">Aceptar</button>
                        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel');">Cancelar</button>
                    </div>

                    <div id="_submited" style="display:none">
                        <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                    </div>        
                </form>   
            </div> <!-- panel-body -->                      
        </div> <!-- panel -->
    </div>    

