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
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/riesgo.class.php";
require_once "../../php/class/nota.class.php";

$_SESSION['debug']= null;

$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;

$signal= $_GET['signal'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : 0;
$day= !empty($_GET['day']) ? $_GET['day'] : 0;

$fecha= $day.'/'.$month.'/'.$year;

$id_proceso_code= !empty($id_proceso) ? get_code_from_table('tprocesos', $id_proceso) : null;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;	
$text= null;
$plus= null;


if (!empty($id_riesgo)) {
    $obj= new Triesgo($clink);
    $text= "Riesgo";
    $plus= "y esta es una operación irreversible";
    $signal= "riesgo";
}

if (!empty($id_nota)) {
    $obj= new Tnota($clink);
    $text= "Nota de Hallago";
    $plus= "y esta es una operación irreversible";
    $signal= "nota";
}

$id= !empty($id_riesgo) ? $id_riesgo : $id_nota;

$obj->SetYear($year);
$obj->Set($id);
$_id_proceso= $obj->GetIdProceso();

$obj_prs= new Tproceso($clink);
$proceso= null;

if (!empty($id_proceso)) {
    $obj_prs->Set($id_proceso);
    $proceso= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
    $id_proceso_code= $obj->get_id_proceso_code();
}
?>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['fdelete'];
            
            if ($('#radio_prs1').is(':checked')) 
                $('#_radio_prs').val(1);
            if ($('#radio_prs2').is(':checked')) 
                $('#_radio_prs').val(2);
                    
            if ($('#_radio_prs').val() == 0) {
                alert("No ha especificado las Unidades Organizativas de las que será eliminado el <?= $text ?>");
                return;
            }

            function _this() {
                form.action= '../php/<?=$signal?>.interface.php';
                form.submit();                        
            }

            if ($('#_radio_prs').val() == 2 && $('#_id_proceso').val() == $('#id_proceso').val()) {
                confirm("Ha seleccionado la opción de eliminar el <?= $text ?> del sistema <?= $plus ?>. ¿Desea continuar?", function(ok) {
                    if (!ok) return;
                    else _this();
                });
            } else {
                _this();
            }
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() { 
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
                <form id="fdelete" name="fdelete" action="javascript:validar()"  method=post>
                    <input type="hidden" name="exect" value="delete" />	
                    <input type="hidden" name="id" id="id" value="<?=$id?>" />
                    <input type="hidden" name="id_riesgo" id="id_riesgo" value="<?=$id_riesgo?>" />
                    <input type="hidden" name="id_nota" id="id_nota" value="<?=$id_nota?>" />

                    <input type="hidden" name="_id_proceso" value="<?=$_id_proceso ?>" />
                    <input type="hidden" name="id_proceso" value="<?=$id_proceso ?>" />
                    <input type="hidden" name="id_proceso_code" value="<?=$id_proceso_code?>" />

                    <input type="hidden" name="menu" value="tablero" />	
                    <input type="hidden" name="id_responsable" value="<?=$_SESSION['id_usuario']?>" />
                    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

                    <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />

                    <input type="hidden" name="year" id="year" value="<?=$year?>" />
                    <input type="hidden" name="month" id="month" value="<?=$month?>" />
                    <input type="hidden" name="day" id="day" value="<?=$day?>" />
                    
                    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />
                    
                    <div class="form-group row">
                        <label class="col-form-label col-xs-12 col-12">
                            <strong><?=$text?>:</strong> <?=$obj->GetNombre()?>
                        </label>
                        <label class="col-form-label col-xs-12 col-12">
                            <strong>Titulo:</strong> <?=$proceso?>
                        </label>
                        <div class="checkbox">
                            <label>
                                <input type="radio" name="radio_prs" id="radio_prs1" value="1" />
                                Eliminar solo de la Unidad Organizativa <strong><?=$proceso?></strong>.                                
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="radio" name="radio_prs" id="radio_prs2" value="2" /> 
                                Eliminar de la Unidad Organizativa <?=$proceso?> y de <strong>todas sus Unidades y procesos subordinados</strong>. 
                                <?php if ($id_proceso == $_id_proceso) { ?>Este <?=$text?> desaparecerá del sistema. <?php echo $plus;  } ?>                                
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

