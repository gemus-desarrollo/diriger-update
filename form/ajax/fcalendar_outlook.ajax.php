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
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/auditoria.class.php";
require_once "../../php/class/proceso.class.php";

$_SESSION['debug']= null;

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;

$signal= $_GET['signal'];
$id_proceso= $_GET['id_proceso'];
$_id_proceso= !empty($_GET['_id_proceso']) ? $_GET['_id_proceso'] : NULL;

$id_proceso= !empty($_id_proceso) ? $_id_proceso : $id_proceso;
$id_proceso_code= null;

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $id_proceso_code= $obj_prs->get_id_code();

    $proceso= $obj_prs->GetNombre().' ('.$Ttipo_proceso_array[$obj_prs->GetTipo()].')';
}

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_usuario= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_SESSION['id_usuario'];
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $_SESSION['id_usuario'];
$id_asignado= !empty($_GET['id_asignado']) ? $_GET['id_asignado'] : $_SESSION['id_asignado'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

$print_reject= !empty($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$id= null;
$nombre= null;

$obj= new Tevento($clink);

$id= 0;
if (!empty($id_evento)) {
    $id= $id_evento;
    $obj->SetIdEvento($id_evento);
    $obj->Set();
}

$nombre= !empty($id_evento) ? $obj->GetNombre() : null;
$fecha_inicio= !empty($id_evento) ? $obj->GetFechaInicioPlan() : null;
$fecha_fin= !empty($id_evento) ? $obj->GetFechaFinPlan() : null;

$obj_user= new Tusuario($clink);

$year= $_GET['year'];
$month= $_GET['month'];

$if_chief= false;
if ($_SESSION['nivel'] >= _ADMINISTRADOR || $id_usuario == $id_asignado) 
    $if_chief= true;

?>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['foutlook'];
            var signal= document.forms['foutlook'].signal.value;

            ejecutar('outlook');
        }
    </script>

    <script type="text/javascript">	
        $(document).ready(function() {
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        }); 
    </script>
    
    
<form id="foutlook" name="foutlook" action="javascript:validar()"  method=post>
    <input type="hidden" name="exect" value="set" />
    <input type="hidden" name="id_usuario" value="<?=$_SESSION['id_usuario'] ?>" />
    <input type="hidden" name="id_responsable" value="<?=$_SESSION['id_usuario'] ?>" />
    <input type="hidden" name="id" value="<?=$id?>" />

    <input type="hidden" name="id_proceso" value="<?=$id_proceso?>" />
    <input type="hidden" name="id_proceso_code" value="<?=$id_proceso_code?>" />
   
    <input type="hidden" name="menu" value="fdelete" />

    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />
    <input type="hidden" id="_radio_user" name="_radio_user" value="0" />

    <input type="hidden" id="print_reject" name="print_reject" value="<?=$print_reject?>" />

    <input type="hidden" name="year" id="year" value="<?=$year?>" />
    <input type="hidden" name="month" id="month" value="<?=$month?>" />

    <input type="hidden" name="if_chief" id="if_chief" value="<?=$if_chief?>" />

    <input type="hidden" id="_id_usuario" name="_id_usuario" value="<?=$id_calendar?>" />
    <input type="hidden" id="_id_responsable" name="_id_responsable" value="<?=$id_responsable?>" />
   
    
    <div class="form-horizontal"> 
        <div class="alert alert-info">
            <strong>Actividad: </strong><?=$nombre?><br />
            <div class="row">
                <div class="col-md-6">
                    <strong>Inicio: </strong><?=odbc2date($obj->GetFechaInicioPlan())?>
                </div>
                <div class="col-md-6 pull-left">
                    <strong>Fin: </strong><?=odbc2date($obj->GetFechaFinPlan())?>
                </div>
            </div>
        </div>


        <?php if (!empty($id_evento)) {?>
        <div class="form-group row">
            <label class="col-form-label col-sm-2 col-md-2">
                Aplicar a:
            </label>
            <div class="col-sm-10 col-md-10">
                <select id="extend" name="extend" class="form-control">
                    <option value="A">Solo a esta actividad</option>
                    <option value="U">A esta misma Actividad siempre que aparezca en el mes ...</option>
                    <option value="D">A todas las actividades de este DíA ...</option>
                    <option value="S">A todas las actividades de la SEMANA ...</option>
                    <option value="M">A todas las actividades del MES ...</option>
                </select>                    
            </div>
        </div>    
        <?php } else { ?>
            <input type="hidden" id="extend" name="extend" value="M" />
        <?php } ?>


        <?php if (!empty($id_evento) && $if_chief) { ?>
            <div class="form-group row">
                <div class="checkbox row">
                    <label> 
                        <input type="checkbox" name="radio_user" id="radio_user" value="1" />
                        Enviar a los calendarios de a todos los implicados en las actividad
                    </label>                         
                </div>
            </div>
        <?php } ?>   
            
            
        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>  
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div>           
    </div>        
               

    <?php if (empty($id_evento)) {?>
        <script language='javascript' type="text/javascript" charset="utf-8">
            ejecutar('outlook');
        </script>
    <?php } ?>
