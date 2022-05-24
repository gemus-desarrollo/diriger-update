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
require_once "../../php/class/tablero.class.php";
require_once "../../php/class/proyecto.class.php";

require_once "../../php/class/badger.class.php";

$signal= !empty($_POST['signal']) ? $_POST['signal'] : null;

$id_user_restrict= !empty($_POST['id_user_restrict']) ? $_POST['id_user_restrict'] : false;
$restrict_prs= !empty($_POST['restrict_prs']) ? unserialize(stripslashes($_POST['restrict_prs'])) : null;

$use_copy_tusuarios= !empty($_POST['use_copy_tusuarios']) ? $_POST['use_copy_tusuarios'] : false;
$array_usuarios= !empty($_POST['array_usuarios']) ? unserialize(urldecode($_POST['array_usuarios'])) : null;
$array_grupos= !empty($_POST['array_grupos']) ? unserialize(urldecode($_POST['array_grupos'])) : null;

$year= !empty($_POST['year']) ? (int)$_POST['year'] : date('Y'); 
$month= !empty($_POST['month']) ? (int)$_POST['month'] : date('m'); 
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
$id_riesgo= !empty($_POST['id_riesgo']) ? $_POST['id_riesgo'] : null;
$id_nota= !empty($_POST['id_nota']) ? $_POST['id_nota'] : null;
$id_politica= !empty($_POST['id_politica']) ? $_POST['id_politica'] : null;
$id_tarea= !empty($_POST['id_tarea']) ? (int)$_POST['id_tarea'] : null;
$id_proyecto= !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
$id_indicador= !empty($_POST['id_indicador']) ? (int)$_POST['id_indicador'] : null;

global $config;
global $badger;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);

$obj_matter= new Ttematica($clink);

$obj_doc= new Tdocumento($clink);
$obj_doc->SetId($id);
$obj_doc->SetIdDocumento($id);
$obj_doc->SetYear($year);
$obj_doc->SetMonth($month);

$obj_doc->SetIdEvento($id_evento);
$obj_doc->SetIdAuditoria($id_auditoria);
$obj_doc->SetIdTarea($id_tarea);
$obj_doc->SetIdProyecto($id_proyecto);
$obj_doc->SetIdRiesgo($id_riesgo);
$obj_doc->SetIdNota($id_nota);
$obj_doc->SetIdIndicador($id_indicador);

if (!empty($id)) {
    $obj_doc->listar_usuarios();
    $array_usuarios= $obj_doc->array_usuarios;
    
    $obj_doc->listar_grupos();
    $array_grupos= $obj_doc->array_grupos;
}

if (!empty($id_evento) || !empty($id_auditoria)) {
    if (!$config->show_group_dpto_plan) {
        $obj_prs->SetIdProceso($id_proceso);
        $result_prs_array = $obj_prs->listar_in_order('eq_asc_desc', true, _TIPO_DEPARTAMENTO, false, 'asc');
    } else {
        $result_prs_array = $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], _TIPO_DEPARTAMENTO);
    }

} elseif (!empty($id_riesgo) || !empty($id_nota)) {
    if (!$config->show_group_dpto_risk) {
        $obj_prs->SetIdProceso($id_proceso);               
        $result_prs_array = $obj_prs->listar_in_order('eq_asc_desc', true, _TIPO_DEPARTAMENTO, false, 'asc');
    } else {
        $result_prs_array = $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], _TIPO_DEPARTAMENTO);
    }

} elseif (!empty($id_proyecto)) {
    $obj_prs->SetIdProceso($id_proceso);           
    $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, _TIPO_DEPARTAMENTO, false, 'asc');

} else {
    $obj_prs->SetIdProceso($id_proceso);
    $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, null, false);
}
?>

<input type="hidden" id="t_cant_multiselect-users" name="t_cant_multiselect-users" value=0 />
<input type="hidden" id="cant_multiselect-users" name="cant_multiselect-users" value=0 />

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
                if (!empty($id_evento)) {
                    $obj->SetIdEvento($id_evento);
                    if ($ifmeeting) 
                        $obj_matter->SetIdEvento($id_evento);
                }
                
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
                
            } elseif (!empty($id_indicador)) {
                $obj= new Ttablero($clink);
                $obj->SetIdIndicador($id_indicador);
            }

            if ($obj) {
                $obj->SetYear($year);
                $obj->set_user_date_ref($user_ref_date);
                
                if (empty($id_indicador)) {
                    $obj->listar_grupos($year, false);
                    $array_grupos_origen= $obj->array_grupos;
                } else {
                    $obj->listar_grupos_by_indicador();
                    $array_grupos_origen= $obj->array_grupos;
                }   
            } else {
                $obj= new Tgrupo($clink);
                $obj->listar(false);
                $array_grupos_origen= $obj->array_grupos;
                unset($obj);
                $obj= null;
            }
            
            foreach ($array_grupos_origen as $row) {
                $value= $array_grupos[$row['id']] ? 1 : 0;
                ++$j;
                ++$z;
                if ($value) 
                    ++$i;
                $colom= (int)$j > 1 ? "," : "";
            ?>
                 <?=$colom?>['grp<?=$row['id']?>', "<i class='fa fa-users text-danger'></i><?= addslashes($row['nombre'])?>", <?=$value?>, 0, 0, '']
             <?php  
            } 
            
            $id_ref= null;
            $funct= null;
            if (!empty($id_evento)) {
                $id_ref= $id_evento;
                $funct= "evento";
            } 
            elseif (!empty($id_auditoria)) {
                $id_ref= $id_auditoria;
                $funct= "auditoria";
            }    
            elseif (!empty($id_tarea)) {
                $id_ref= $id_tarea;
                $funct= "tarea";
            } 
            elseif (!empty($id_tematica)) {
                $id_ref= $id_evento;
                $funct= "evento";
            }    
            else {
                $id_ref= $id_evento;
                $funct= "evento";
            }
            
            if ($obj) {
                if (empty($id_indicador)) {
                    if (!empty($id_evento) || !empty($id_tarea) || !empty($id_auditoria) && is_null($id_tematica)) {
                        // $obj->listar_usuarios(null, true, $year, false);   
                        $obj->_get_users($id_ref, $obj->array_usuarios);
                    }
                    elseif (!is_null($id_tematica) && !empty($id_tematica)) {
                        // $obj->listar_usuarios(null, null, null, null, true);
                        $obj->_get_users($id_ref, $obj->array_usuarios);
                    } elseif (!empty($id_proyecto)) 
                        $obj->listar_usuarios(null, false); 
                
                } else 
                    $obj->listar_usuarios_by_indicador();
                
                $array_usuarios_origen= $obj->array_usuarios;
                
            } else {
                $obj= new Tusuario($clink);
                $obj->listar(false);
                $array_usuarios_origen= $obj->array_usuarios;
            }
                    
            
            if (isset($obj_user)) unset($obj_user);
            $obj_user= new Tusuario($clink);
            
            reset($result_prs_array);
            $result_prs_array= $obj_prs->sort_array_procesos($result_prs_array);
            
            foreach ($result_prs_array as $row_prs) {  
                $_connect= is_null($row_prs['conectado']) || $row_prs['conectado'] == _LAN ? _LOCAL : _NO_LOCAL;
                if ($row_prs['id'] == $_SESSION['local_proceso_id']) 
                    $_connect= _LOCAL;
                if (!empty($restrict_prs) && array_search($row_prs['tipo'], $restrict_prs) !== false) 
                    continue;

                $obj_user->SetIdProceso($row_prs['id']);
                unset($obj_user->array_usuarios);
                $obj_user->array_usuarios= null;
 
                if ($_connect == _NO_LOCAL) {
                     if ($row_prs['tipo'] >= _TIPO_DIRECCION)
                         $obj_user->listar(false, null, _LOCAL, null, null, false);
                     if ($row_prs['tipo'] < _TIPO_DIRECCION && $row_prs['tipo'] >= $_SESSION['local_proceso_tipo'] && $row_prs['tipo'] != _TIPO_UEB) 
                         $obj_user->listar(false, null, _NO_LOCAL, null, null, true);
                     else 
                         $obj_user->listar(false, null, _NO_LOCAL, null, null, false);
                } else {
                    if (($row_prs['tipo'] <= _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] < _TIPO_UEB) 
                        || ($row_prs['tipo'] >= _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] >= _TIPO_UEB))
                        $obj_user->listar(false, null, _LOCAL, null, null, false);
                    else
                        $obj_user->listar(false, null, _NO_LOCAL, null, null, false);
                }
                
                if (count($obj_user->array_usuarios) == 0) 
                    continue;

               $name= $_connect == _NO_LOCAL ? "<i class='fa fa-wifi text-danger'></i>" : "";
               $name.= "<label>".textparse($row_prs['nombre'], true)."</label>";
               ++$j;
               $colom= (int)$j > 1 ? "," : ""; 
           ?> 

               <?=$colom?>[0, "<?=$name?>", 0, 0, 0, '<?=color_proccess($row_prs['tipo'])?>']

           <?php
            foreach ($obj_user->array_usuarios as $row) {
                if (array_key_exists($row['id'], $array_usuarios_origen) == false)
                    continue;
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

                <?=$colom?>['user<?=$row['id']?>', "<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, 0, <?=$_ifmeeting?>, ''] 

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
 