<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2018
 */

 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/proceso_item.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/tematica.class.php";
require_once "../../php/class/document.class.php";
require_once "../../php/class/tarea.class.php";
require_once "../../php/class/auditoria.class.php";
require_once "../../php/class/proyecto.class.php";

require_once "../../php/class/badger.class.php";


$signal= !empty($_POST['signal']) ? $_POST['signal'] : null;

$id_user_restrict= !empty($_POST['id_user_restrict']) ? $_POST['id_user_restrict'] : false;
$restrict_prs= !empty($_POST['restrict_prs']) ? unserialize(stripslashes($_POST['restrict_prs'])) : null;

$use_copy_tusuarios= !empty($_POST['use_copy_tusuarios']) ? $_POST['use_copy_tusuarios'] : false;
$array_usuarios= !empty($_POST['array_usuarios']) ? unserialize(urldecode($_POST['array_usuarios'])) : null;
$array_grupos= !empty($_POST['array_grupos']) ? unserialize(urldecode($_POST['array_grupos'])) : null;

$id= !empty($_POST['id']) ? (int)$_POST['id'] : null; 
$year= !empty($_POST['year']) ? (int)$_POST['year'] : date('Y'); 
$user_ref_date= !empty($_POST['user_ref_date']) ? $_POST['user_ref_date'] : date('Y-m-d H:i:s'); 

$tipo_plan= !empty($_POST['tipo_plan']) ? $_POST['tipo_plan'] : null; 
$if_jefe= !is_null($_POST['if_jefe']) ? $_POST['if_jefe'] : null; 

$id_proceso= !empty($_POST['id_proceso']) ? (int)$_POST['id_proceso'] : $_SESSION['id_entity'];

$id= !is_null($_POST['id']) ? (int)$_POST['id'] : null;
$ifaccords= !is_null($_POST['ifaccords']) ? $_POST['ifaccords'] : false;
$id_evento= !empty($_POST['id_evento']) ? (int)$_POST['id_evento'] : null;
$ifmeeting= !empty($_POST['ifmeeting']) ? 1 : 0;
$id_tematica= !is_null($_POST['id_tematica']) ? (int)$_POST['id_tematica'] : null;
$id_auditoria= !empty($_POST['id_auditoria']) ? $_POST['id_auditoria'] : null;
$id_tarea= !empty($_POST['id_tarea']) ? (int)$_POST['id_tarea'] : null;
$id_proyecto= !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;

global $badge;
global $badger;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);

unset($obj_prs);
$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);

$obj_matter= new Ttematica($clink);

$tipo_plan ? $obj_prs->set_use_copy_tprocesos(true) : $obj_prs->set_use_copy_tprocesos(false);

switch ($tipo_plan) {
    case _PLAN_TIPO_ACTIVIDADES_ANUAL :
    case _PLAN_TIPO_ACTIVIDADES_MENSUAL :
    case _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL :
    case _PLAN_TIPO_MEETING :
        $badger->set_planwork();
        break;
    case _PLAN_TIPO_AUDITORIA :
        $badger->set_planaudit();
        break;
    case _PLAN_TIPO_MEDIDAS :
        $badger->set_planheal();
        break;
    case _PLAN_TIPO_PROYECTO :
        $badger->set_planproject();
        break;
    case _PLAN_TIPO_PREVENCION :
    case _PLAN_TIPO_SUPERVICION :
        $badger->set_planrisk();
        break;
    default :
        $id_proceso= $_SESSION['id_entity'];
        $tipo= (int)$_SESSION['entity_tipo'];       
        $badger= null;
        break;
} 

$obj_prs->set_acc(!is_null($badger->acc) ? $badger->acc : null);
$id_usuario= $_SESSION['id_usuario'];

$obj_prs->if_copy_tprocesos= $badger->if_copy_tprocesos;
$obj_prs->set_use_copy_tprocesos(true);

$obj_prs->SetIdProceso(null);
$obj_prs->SetTipo(null);

$badge->freeassign == _TO_ALL_ENTITIES ? $obj_prs->SetIdEntity(null) : $obj_prs->SetIdEntity($_SESSION['id_entity']);

if ($tipo_plan) {
    $result_prs_array = $obj_prs->listar(false, null, 'asc', 'eq_asc_desc', _TIPO_DEPARTAMENTO); 
} else {
    if ($_SESSION['nivel'] >= _SUPERUSUARIO)
        $result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', true, null, false, 'asc');
    if ($_SESSION['nivel'] == _ADMINISTRADOR) {
        $obj_prs->SetIdProceso($_SESSION['usuario_proceso_id']);
        $result_prs_array= $obj_prs->listar_in_order('eq_desc', true, null, false, 'asc');
    }
    if ($_SESSION['nivel'] <= _ADMINISTRADOR) {
        $result_prs_array= $obj_prs->get_procesos_down_cascade($_SESSION['id_usuario'], $_SESSION['usuario_proceso_id'], null);
    }  
}
?>

<input type="hidden" id="t_cant_multiselect-users" name="t_cant_multiselect-users" value="0" />
<input type="hidden" id="cant_multiselect-users" name="cant_multiselect-users" value="0" />

 <div class="container-fluid">
     <div class="row">
        <div class="col-5">
            <legend>Usuarios y grupos</legend>
        </div>
        <div class="col-2"></div>
        <div class="col-5">
            <legend>Seleccionados</legend>
        </div>
     </div>

     <script type="text/javascript" charset="utf-8">
       $(document).ready(function() {
           var data_users= [
           <?php 
            $i = 0;
            $j = 0;
            $z = 0;
            $obj = null;
            
            if (!empty($id_evento) && empty($id_tematica)) {
                $obj = new Tevento($clink);
                if (is_null($id) && !empty($id_evento)) {
                    $obj->SetIdEvento($id_evento);
                    if ($ifmeeting) 
                        $obj_matter->SetIdEvento($id_evento);
                } elseif (!empty($id))
                    $obj->SetIdEvento($id);
                
            } elseif (!empty($id_tematica)) {
                $obj= new Ttematica($clink);
                $obj->SetYear($year);
                $obj->SetIdEvento($id_evento);
                $obj->SetIdTematica($id_tematica);
                
            } elseif (!empty($id_auditoria)){
                $obj = new Tauditoria($clink);
                $obj->SetIdAuditoria($id_auditoria);
                
            } elseif (!empty($id_tarea)){
                $obj = new Ttarea($clink);
                $obj->SetIdTarea($id_tarea);
                
            } elseif (!empty($id_proyecto)){
                $obj = new Tproyecto($clink);
                $obj->SetIdProyecto($id_proyecto);
            }

            if ($obj) {
                $obj->SetYear($year);
                $obj->set_user_date_ref($user_ref_date);
                
                if ((!is_null($id) && !empty($id_evento)) || !is_null($id_tematica)) 
                    $array_grupos= null;
                else {
                    $obj->listar_grupos($year, false);
                    $array_grupos= $obj->array_grupos;
                }    
            } 
 
            if (is_null($id_tematica)) {
                if (($badge->freeassign || (!empty($badger->acc) && $badger->acc != 1)) || array_key_exists($_SESSION['id_entity'], $result_prs_array)) {
                    $obj_grp= new Tgrupo($clink);
                    $badge->freeassign == _TO_ALL_ENTITIES ? $obj_grp->SetIdEntity(null) : $obj_grp->SetIdEntity($_SESSION['id_entity']);
                    $result_grp= $obj_grp->listar();

                    while ($row= $clink->fetch_array($result_grp)) {
                        $value= $array_grupos[$row['_id']] ? 1 : 0;
                        ++$j;
                        ++$z;
                        if ($value) 
                            ++$i;
                        $colom= (int)$j > 1 ? "," : "";

                        $nombre= addslashes($row['nombre']);
                        if ($badge->freeassign == _TO_ALL_ENTITIES && $row['id_entity'] != $_SESSION['id_entity'])
                            $nombre.= " - ".addslashes($array_procesos_entity[$row['id_entity']]['nombre']);
                    ?>
                         <?=$colom?>['grp<?=$row['_id']?>', "<i class='fa fa-users text-danger'></i><?= $nombre?>", <?=$value?>, 0, 0, '']
                     <?php  
                    } 
                }   
            }

            if ($obj) {
                if ((!empty($id) || ((is_null($id) && !empty($id_evento)) || !empty($id_tarea) || !empty($id_auditoria))) && is_null($id_tematica)) 
                    $obj->listar_usuarios(null, true, $year, false);   
                elseif ((!is_null($id_tematica) && !empty($id_tematica)) || !empty($id_indicador))
                    $obj->listar_usuarios(null, null, null, null, true);
                elseif (!empty($id_proyecto) && empty($id_indicador)) 
                    $obj->listar_usuarios(null, false); 

                $array_usuarios= $obj->array_usuarios;
            }
            
            $array_usuarios_evento= null;
            if ((!is_null($id) || !is_null($id_tematica)) && !empty($id_evento)) 
                $array_usuarios_evento= $obj->get_usuarios_array_from_evento($id_evento, null, true);
           
            if (isset($obj_user)) unset($obj_user);
            $obj_user= new Tusuario($clink);
 
            if (is_null($result_prs_array) || count($result_prs_array) == 0) {
               $name= $_connect == _NO_LOCAL ? "<i class='fa fa-wifi text-danger'></i>" : "";
               $name.= "<label>".textparse($_SESSION['empresa'], true)."</label>";
               ++$j;
               $colom= (int)$j > 1 ? "," : ""; 
           ?> 

               <?=$colom?>[0,"<?=$name?>",0,0,0, '<?=color_proccess($_SESSION['entity_tipo'])?>']   
                   
                <?php  
                $obj_user->if_copy_tusuarios= $badger->if_copy_tusuarios;
                $obj_user->set_use_copy_tusuarios(true);
                $obj_user->listar(false);

                foreach ($obj_user->array_usuarios as $row) {
                    if (is_array($array_usuarios_evento)) {
                        if (array_key_exists($row['id'], $array_usuarios_evento) == false)
                            continue;
                    }
                    if (empty($row['nombre'])) 
                        continue;
                    if ($row['id'] == _USER_SYSTEM) 
                        continue;
                    if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict) 
                        continue;

                    $value = $array_usuarios[$row['id']] ? 1 : 0;  

                    $_ifmeeting= 0;
                    if ($value && $ifmeeting) 
                        $_ifmeeting= $obj_matter->get_if_attached_usuario($row['id']) ? 1 : 0;

                    ++$j; 
                    ++$k;
                    $colom= (int)$j > 1 ? "," : "";
                    if ($value) 
                        ++$i;
                    $name= textparse($row['nombre'], true);
                    $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo'], true) : "";
            ?> 

                    <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, 0, <?=$_ifmeeting?>, ''] 

           <?php } } ?> 
            
            <?php
            reset($result_prs_array);
            $result_prs_array= $obj_prs->sort_array_procesos($result_prs_array);

            $k= 0;         
            foreach ($result_prs_array as $row_prs) { 
                if ($badge->freeassign < _TO_ALL_ENTITIES) {
                    if ($row_prs['tipo'] <= $_SESSION['entity_tipo'] 
                        && (($row_prs['id'] != $_SESSION['superior_entity_id'] && $row_prs['id'] != $_SESSION['id_entity'])
                            && (!empty($row_prs['id_proceso']) && $row_prs['id_proceso'] != $_SESSION['id_entity'])))
                        continue; 
                    if ($row_prs['id'] == $_SESSION['superior_entity_id']) 
                        continue;
                    if (($signal == "evento" || $signal == "auditoria" || $signal == "tarea" || $signal == "proyecto") 
                            && ($row_prs['id'] == $_SESSION['superior_entity_id'])) 
                        continue;
                    if (!empty($restrict_prs) && $row_prs['tipo'] == $restrict_prs) 
                        continue;                    
                }

                $_connect= is_null($row_prs['conectado']) || $row_prs['conectado'] == _LAN ? _LOCAL : _NO_LOCAL;
                if ($row_prs['id'] == $_SESSION['local_proceso_id']) 
                    $_connect= _LOCAL; 
                
                $obj_user->SetIdProceso($row_prs['id']);
                unset($obj_user->array_usuarios);
                $obj_user->array_usuarios= null;
 
                if ($_connect == _NO_LOCAL) {
                     if ($row_prs['tipo'] <= _TIPO_DEPARTAMENTO)
                         $obj_user->listar(false, null, _LOCAL, null, null, false);
                     if ($row_prs['tipo'] > _TIPO_DEPARTAMENTO && $row_prs['tipo'] >= $_SESSION['entity_tipo'] && $row_prs['tipo'] != _TIPO_UEB) 
                         $obj_user->listar(false, null, _NO_LOCAL, null, null, true);
                     else 
                         $obj_user->listar(false, null, _NO_LOCAL, null, null, false);
                } else {
                    
                    if ($badge->freeassign == _TO_ALL_ENTITIES || ((!empty($row_prs['id_entity']) && $row_prs['id_entity'] == $_SESSION['id_entity']) 
                            || (empty($row_prs['id_entity']) && $row_prs['id'] == $_SESSION['id_entity']))) {
                        
                        if ($row_prs['tipo'] <= _TIPO_PROCESO_INTERNO && (($row_prs['tipo'] <= _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] < _TIPO_UEB) 
                                || ($row_prs['tipo'] == _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] == _TIPO_UEB)
                                || ($row_prs['tipo'] > _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] > _TIPO_UEB)))
                            $obj_user->listar(false, null, _LOCAL, null, null, false);
                        else 
                            $obj_user->listar(false, null, _NO_LOCAL, null, null, false);
                        
                    } else {
                        $obj_user->SetIdUsuario($row_prs['id_responsable']);
                        $obj_user->Set();
                        $array = array('id' => $obj_user->GetId(), 'nombre' => $obj_user->GetNombre(), 'email' => $obj_user->GetMail_address(), 
                            'cargo' => $obj_user->GetCargo(), 'origen_data' => null, 'eliminado' => null, 'usuario' => $obj_user->GetUsuario(),
                            '_id' => null, 'id_proceso'=> $obj_user->GetIdProceso(), 'situs'=> null);  
                        $obj_user->array_usuarios[$row_prs['id_responsable']]= $array;
                    }
                }
        
                if (count($obj_user->array_usuarios) == 0) 
                    continue;
  
               $name= $_connect == _NO_LOCAL ? "<i class='fa fa-wifi text-danger'></i>" : "";

               $nombre= textparse($row_prs['nombre'], true);
               if ($badge->freeassign == _TO_ALL_ENTITIES && (!empty($row_prs['id_entity'] ) && $row_prs['id_entity'] != $_SESSION['id_entity']))
                   $nombre.= " - ".textparse($array_procesos_entity[$row_prs['id_entity']]['nombre']);

               $name.= "<label>$nombre</label>";
               ++$j;
               $colom= (int)$j > 1 ? "," : ""; 
           ?> 

               <?=$colom?>[0,"<?=$name?>",0,0,0, '<?=color_proccess($row_prs['tipo'])?>']

           <?php
            foreach ($obj_user->array_usuarios as $row) {
                if (is_array($array_usuarios_evento)) {
                    if (array_key_exists($row['id'], $array_usuarios_evento) == false)
                        continue;
                }
                if (empty($row['nombre'])) 
                    continue;
                if ($row['id'] == _USER_SYSTEM) 
                    continue;
                if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict) 
                    continue;

                $value = $array_usuarios[$row['id']] ? 1 : 0;  
                
                $_ifmeeting= 0;
                if ($value && $ifmeeting) 
                    $_ifmeeting= $obj_matter->get_if_attached_usuario($row['id']) ? 1 : 0;

                ++$j; 
                ++$k;
                $colom= (int)$j > 1 ? "," : "";
                if ($value) 
                    ++$i;
                $name= textparse($row['nombre'], true);
                $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo'], true) : "";
           ?> 

                <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, 0, <?=$_ifmeeting?>, ''] 

           <?php } } ?>     

           ];

           multiselect('multiselect-users', data_users);

           $("#t_cant_multiselect-users").val(<?= $j ?>);
           $("#cant_multiselect-users").val(<?= $i ?>);
       }); 

    </script>        

    <div id="multiselect-users"></div>
    <span style="font-size: 0.9em"><strong>grupo:</strong><?=$z?>  <strong>usuarios:</strong><?=$k?></span>
 </div>
 