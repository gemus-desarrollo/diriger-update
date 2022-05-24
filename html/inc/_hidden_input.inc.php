<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 19/02/15
 * Time: 14:48
 */

$fecha= odbc2time_ampm($evento['fecha']);
$fecha.= " hasta ".odbc2ampm($evento['fecha_fin']);

$email= $obj_user->GetEmail($evento['id_responsable']);
$responsable= $email['nombre'];
if (!empty($email['cargo']))
    $responsable.= ', '.textparse($email['cargo'], true);

$tipo_proceso_user= null;
$id_proceso_user= null;
$acc_planwork= null;
$acc_planaudit= null;
$usuario= null;
if (!empty($evento['id_usuario'])) {
    $obj_user->SetIdUsuario($evento['id_usuario']);
    $obj_user->Set();
    $usuario= $obj_user->GetNombre();
    $cargo= $obj_user->GetCargo();
    $email= $obj_user->Getmail_address();

    $id_proceso_user= $obj_user->GetIdProceso();
    $tipo_proceso_user= $array_procesos_entity[$id_proceso_user]['tipo'];
    $acc_planwork= $obj_user->get_acc_planwork();
    $acc_planaudit= $obj_user->get_acc_planaudit();

    if (!empty($cargo))
        $usuario.= ', '.textparse($cargo, true);
    $usuario.= ' '.odbc2time_ampm($evento['cronos']);

    if (!empty($evento['id_responsable_2']))
        $usuario.= ", <strong>ACTIVIDAD DELEGADA</strong>";

} elseif (!empty($evento['origen_data_asigna'])) {
    $origen_data= $obj_tipo->GetOrigenData('user', $evento['origen_data_asigna']);
    if (!empty($origen_data))
        $usuario= merge_origen_data_user($origen_data)."<br />Fecha y hora: ".odbc2time_ampm($evento['cronos']);
}

$_ifmeeting= empty($evento['id_tipo_reunion']) ? 0 : ($signal == 'anual_plan' ? 2 : 1);
$ifmeeting= is_null($ifmeeting) ? $_ifmeeting : $ifmeeting;

$fixed= false;
if ($_ifmeeting) {
    $obj_matter= new Ttematica($clink);
    $obj_matter->SetIdEvento($evento['id']);
    $fixed= $obj_matter->if_fixed();
}

$if_participant= $ifmeeting ? $obj->if_participant($evento['id'], $_SESSION['id_usuario']) : false;

$descripcion= purge_html(textparse($evento['descripcion'], false), true);

$id_proyecto = 0;
$id_riesgo = 0;
$id_nota = 0;
$id_politica = 0;

if (($signal == 'calendar' || $signal == 'mensual_plan') && !empty($evento['id_tarea'])) {
    $id_tarea= $evento['id_tarea'];

    if (isset($obj_task)) 
        unset($obj_task);
    $obj_task= new Ttarea($clink);
    $obj_task->Set($id_tarea);

    $array_tarea= $obj_task->get_references();

    $descripcion= $obj_task->get_proyectos(null, true, true);
    $descripcion.="<br/>". $obj_task->get_riesgos();
    $descripcion.="<br/>". $obj_task->get_notas();

    $array_tarea['id_proyecto']= $obj_task->GetIdProyecto();
    $id_responsable_proyecto = 0;
    $proyecto = null;

    $id_riesgo= !empty($array_tarea['id_riesgo']) ? $array_tarea['id_riesgo'] : 0;
    $id_nota= !empty($array_tarea['id_nota']) ? $array_tarea['id_nota'] : 0;
    $id_politica= !empty($array_tarea['id_politica']) ? $array_tarea['id_politica'] : 0;
    $id_proyecto= !empty($array_tarea['id_proyecto']) ? $array_tarea['id_proyecto'] : 0;

    if (is_array($obj_task->array_proyectos)) {
        $pry= current($obj_task->array_proyectos);
        $array_tarea['id_proyecto']= $pry['id'];
        $evento['id_proyecto']= $pry['id'];

        $id_proyecto = $pry['id'];
        $id_responsable_proyecto = $pry['id_responsable'];

        $email= $obj_user->GetEmail($pry['id_responsable']);
        $responsable_pry= $email['nombre'];
        if (!empty($email['cargo']))
            $responsable_pry.= ", ".textparse($email['cargo'],true);

        $proyecto = "{$pry['nombre']} / <strong>Responsable del Proyecto: </strong>{$responsable_pry}";
    }
}

if ($signal == 'calendar' && !empty($evento['id_auditoria'])) {
    if (isset($obj_task)) 
        unset($obj_task);
    $obj_task= new Tauditoria($clink);
    $obj_task->Set($evento['id_auditoria']);
    $descripcion= "ALCANCE DE AUDITORÍA:  ".$obj_task->GetLugar();

    $obj_task= new Tauditoria($clink);
}

$secretary= null;
if (!empty($evento['id_secretary'])) {
    $email= $obj_user->GetEmail($evento['id_secretary']);
    $secretary= $email['nombre'];
    $secretary.= !empty($email['cargo']) ? ", {$email['cargo']}" : null;
}
?>

<input type="hidden" id="msg_prs_<?=$evento['id']?>" value="<?=$msg_prs?>" />

<input type="hidden" id="evento_<?=$evento['id']?>" value="<?= textparse($evento['evento'], true) ?>" />
<input type="hidden" id="descripcion_<?=$evento['id']?>" value="<?= textparse($descripcion) ?>" />
<input type="hidden" id="lugar_<?=$evento['id']?>" value="<?= textparse(purge_html($evento['lugar']), true) ?>" />

<input type="hidden" id="responsable_<?=$evento['id']?>" value="<?=$responsable?>" />
<input type="hidden" id="id_responsable_<?=$evento['id']?>" value="<?=$evento['id_responsable'] ?>" />

<input type="hidden" id="id_usuario_<?=$evento['id']?>" value="<?=$evento['id_usuario'] ?>" />
<input type="hidden" id="usuario_<?=$evento['id']?>" value="<?=$usuario?>" />

<input type="hidden" id="acc_planwork_user_<?=$evento['id']?>" value="<?=$acc_planwork ? $acc_planwork : 0?>" />
<input type="hidden" id="acc_planaudit_user_<?=$evento['id']?>" value="<?=$acc_planaudit ? $acc_planaudit : 0?>" />
<input type="hidden" id="id_proceso_user_<?=$evento['id']?>" value="<?=$id_proceso_user?>" />
<input type="hidden" id="tipo_proceso_user_<?=$evento['id']?>" value="<?=$tipo_proceso_user?>" />

<input type="hidden" id="aprobado_<?=$evento['id']?>" value="<?=$evento['aprobado'] ?>" />
<input type="hidden" id="toshow_<?=$evento['id']?>" value="<?=$evento['toshow'] ?>" />

<input type="hidden" id="fecha_<?=$evento['id']?>" value="<?=$fecha?>" />
<input type="hidden" id="cumplimiento_<?=$evento['id']?>" value="<?=$evento['cumplimiento']?>" />
<input type="hidden" id="status_<?=$evento['id']?>" value="<?=$eventos_cump[(int)$evento['cumplimiento']]?>" />

<input type="hidden" id="id_evento_<?=$evento['id']?>" value="<?=$signal != 'anual_plan_audit' ? $evento['id'] : 0?>" />
<input type="hidden" id="id_tarea_<?=$evento['id']?>" value="<?=!empty($evento['id_tarea']) ? $evento['id_tarea'] : 0?>" />
<input type="hidden" id="id_auditoria_<?=$evento['id']?>" value="<?=$signal != 'anual_plan_audit' ? !empty($evento['id_auditoria']) ? $evento['id_auditoria'] : 0 :  $evento['id']?>" />
<input type="hidden" id="id_tematica_<?=$evento['id']?>" value="<?=!empty($evento['id_tematica']) ? $evento['id_tematica'] : 0?>" />

<input type="hidden" id="id_nota_<?=$evento['id']?>" value="<?=$id_nota?>" />
<input type="hidden" id="id_riesgo_<?=$evento['id']?>" value="<?=$id_riesgo?>" />
<input type="hidden" id="id_politica_<?=$evento['id']?>" value="<?=$id_politica?>" />

<input type="hidden" id="ifmeeting_<?=$evento['id']?>" value="<?=$ifmeeting?>" />
<input type="hidden" id="id_secretary_<?=$evento['id']?>" value="<?=$evento['id_secretary']?>" />
<input type="hidden" id="secretary_<?=$evento['id']?>" value="<?=$secretary?>" />
<input type="hidden" id="if_participant_<?=$evento['id']?>" value="<?=$if_participant ? 1 : 0?>" />

<input type="hidden" id="id_proyecto_<?=$evento['id']?>" value="<?=$id_proyecto ?>" />
<input type="hidden" id="id_responsable_proyecto_<?=$evento['id']?>" value="<?=$id_responsable_proyecto?>" />
<input type="hidden" id="proyecto_<?=$evento['id']?>" value="<?=$proyecto ?>" />

<input type="hidden" id="fecha_origen_<?=$evento['id']?>" value="<?=$fecha_inicio ?>" />
<input type="hidden" id="fecha_termino_<?=$evento['id']?>" value="<?=$fecha_fin ?>" />

<input type="hidden" id="outlook_<?=$evento['id']?>" value="<?=$evento['outlook'] ?>" />

<input type="hidden" id="id_archivo_<?=$evento['id']?>" value="<?=!empty($evento['id_archivo']) ? $evento['id_archivo'] : 0 ?>" />

<input type="hidden" id="if_entity_<?=$evento['id']?>" value="<?=$if_entity ? 1 : 0 ?>" />
<input type="hidden" id="entity_tipo_user_<?=$evento['id']?>" value="<?=$entity_tipo ? $entity_tipo : 0 ?>" />
<input type="hidden" id="if_synchronize_<?=$evento['id']?>" value="<?=$if_synchronize ? 1 : 0 ?>" />
<input type="hidden" id="fixed_<?=$evento['id']?>" value="<?=$fixed ? 1 : 0 ?>" />