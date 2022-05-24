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
require_once "../../php/class/time.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/tarea.class.php";
require_once "../../php/class/riesgo.class.php";
require_once "../../php/class/nota.class.php";
require_once "../../php/class/proyecto.class.php";

$_SESSION['debug']= 'no';

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';
$signal= $_GET['signal'];
$menu= $_GET['menu'];
$year= $_GET['year'];
$id_proceso= $_GET['id_proceso'];

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;

$fecha_inicio= urldecode($_GET['fecha_inicio']);
$fecha_fin= urldecode($_GET['fecha_fin']);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
if ($obj_prs->GetTipo() == _TIPO_PROCESO_INTERNO) 
    $id_proceso= $obj_prs->GetIdProceso_sup();

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$obj_prs->get_procesos_down(null, null, null, true);
$array_procesos= $obj_prs->array_cascade_down;    
$prs_lis= is_array($array_procesos) ? implode (',', $array_procesos) : null;

$obj= new Ttarea($clink);
$obj->SetYear($year);
$obj->SetFechaInicioPlan(date2odbc($fecha_inicio));
$obj->SetFechaFinPlan(date2odbc($fecha_fin));
$obj->SetIdProceso(null);

$result= $obj->listar(true, true, $prs_list);

if ($menu == 'riesgo') {
    $id= $id_riesgo;
    $obj_reg= new Triesgo($clink);
}
if ($menu == 'nota') {
    $id= $id_nota;
    $obj_reg = new Tnota($clink);
}
if ($menu == 'proyecto') {
    $id= $id_proyecto;
    $obj_reg= new Tproyecto($clink);
}

$obj_reg->listar_tareas($id, null, true);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>     


    <script language='javascript'>
        function select_task_chk(id) {
            if (document.getElementById('chk_task_'+id).checked) {
                document.getElementById('cant_task').value= parseInt(document.getElementById('cant_task').value) + 1;
                document.getElementById('chk_task_'+id).value= 1;
            }
            else {
                document.getElementById('cant_task').value= parseInt(document.getElementById('cant_task').value) - 1;
                document.getElementById('chk_task_'+id).value= 0;
            }
        }
    </script>

        <script type="text/javascript">	
        $(document).ready(function() {   
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        }); 
    </script>
    
    <div class="card card-primary">
        <div class="card-header">
            <div class="row">
               <div class="panel-title col-11 win-drag ajax-title">SELECCIONAR TAREAS</div>
                <div class="col-1 pull-right close">
                    <a href= "javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>                      
           </div>    
        </div>    

        <div class="card-body">
            <form id="fadd_tarea" name="fadd_tarea" action='javascript:' method=post style="border:none">
                <input type="hidden" name="exect" value="<?= $action ?>" />
                <input type="hidden" name="id" value="<?= $id ?>" />
                <input type="hidden" name="menu" value="add_tarea" />
                <input type="hidden" id="t_cant_task" name="t_cant_task" value="0" />
                <input type="hidden" id="cant_task" name="cant_task" value="0" />

                <table id="table-plan"class="table table-striped"
                       data-toggle="table"
                       data-height="400"
                       data-search="true"
                       data-show-columns="true"> 
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>TAREA</th>
                            <th>PROCESO</th>
                            <th>RESPONSABLE</th>
                            <th>FECHA INICIO</th>
                            <th>FECHA FIN</th>
                            <th>OBSERVACIÓN</th>
                            <th>PARTICIPANTES</th>
                        </tr>
                    </thead>
         
                    <tbody>
                        <?php
                        $i = 0;
                        $j = 0;

                        while ($row = @$clink->fetch_array($result)) {
                            if (array_key_exists($row['_id'], $obj_reg->array_tareas))
                                continue;
                            ?>
                            <tr>
                                <td valign="top"><?= ++$i ?>
                                    <input type="checkbox" id="chk_task_<?= $row['_id'] ?>" name="chk_task_<?= $row['_id'] ?>" value=0 onclick="select_task_chk(<?= $row['_id'] ?>)" />
                                </td>
                                <td><?= textparse($row['tarea'], false) ?></td>

                                <td>
                                    <?php
                                    $obj_prs->Set($row['id_proceso']);
                                    echo $obj_prs->GetNombre() . '<br/>(' . $Ttipo_proceso_array[$obj_prs->GetTipo()] . ')';
                                    ?>
                                </td>

                                <td><?= $row['responsable'] ?></td>
                                <td><?= odbc2time_ampm($row['fecha_inicio_plan']) ?></td>
                                <td><?= odbc2time_ampm($row['fecha_fin_plan']) ?></td>
                                <td><?= textparse($row['descripcion']) ?></td>
                                <td>
                                    <?php
                                    $string = $obj->get_participantes($row['_id'], 'tarea');
                                    echo $string;
                                    ?>
                                </td>
                            </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <input type="hidden" id="t_cant_task" name="t_cant_task" value="<?=$i?>">


                <div id="_submit" class="submit" align="center" style="width:100%; text-align:center; display:block; margin-top: 10px;">
                <?php
                $visible= 'hidden';
                if ($action == 'update' || $action == 'add') $visible= 'visible';
                ?>
                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="button" onclick="insert_task()" style="visibility: '<?=$visible?>'"> Agregar</button>  
                    <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                </div>

                <div id="_submited" class="submited" align="center" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
                </div> 
        
            </form>
        </div>


