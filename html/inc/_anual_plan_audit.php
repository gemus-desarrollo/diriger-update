<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */
?>

<?php
if (isset($obj_prs)) unset($obj_prs);
$obj_prs= new TProceso($clink);
$obj_event= new Tauditoria($clink);
$obj_event->SetYear($year);

if (!empty($id_proceso)) {
    $j= 0;

    $count= $obj->listyear(null);

    foreach ($obj->array_auditorias as $evento) {
        $fecha= odbc2time_ampm($evento['fecha']);
        $email= $obj_user->GetEmail($evento['id_responsable']);
        $responsable= textparse($email['nombre']).', '.textparse($email['cargo']);

        $usuario= null;
        if (!empty($evento['id_usuario'])) {
            $email= $obj_user->GetEmail($evento['id_usuario']);
            $usuario= $email['nombre'].', '.textparse($email['cargo']).' '.odbc2time_ampm($evento['cronos']);

        } elseif (!empty($evento['origen_data_asigna'])) {
            $origen_data= $obj_tipo->GetOrigenData('user', $evento['origen_data_asigna']);
            if (!empty($origen_data))
                $usuario= merge_origen_data_user($origen_data)."<br />Fecha y hora: ".odbc2time_ampm($evento['cronos']);
        }

        $obj_prs->Set($evento['id_proceso']);

        $text= $obj_prs->GetNombre();
        $evento['evento']= textparse($text).'<br/>'.$Ttipo_proceso_array[$obj_prs->GetTipo()];

        $ifmeeting= null;
        $obj_signal->test_if_synchronize($evento);
        $if_synchronize= $obj_signal->if_synchronize;
        $entity_event= $obj_signal->test_if_entity($evento);
        $if_entity= $entity_event[0];
        $entity_tipo= $entity_event[1];
        include "inc/_hidden_input.inc.php";

        ++$nums_id_show;
        $array_id_show.= ($nums_id_show > 1) ? ",".$evento['id'] : $evento['id'];
    }
}
?>

<div id="scheduler-container">
    <table id="scheduler" cellspacing=0 cellpadding=3>
      <thead>
      <tr>
        <th rowspan="2" class="plhead">No.</th>
        <th rowspan="2" class="plhead"><?=$td1?></th>
        <th rowspan="2" class="plhead"><?=$td2?></th>
        <th rowspan="2" class="plhead"><?=$td3?></th>
        <th colspan="12" class="plhead">MESES</th>
        <th rowspan="2" class="plhead">PARTICIPANTES</th>
        <th rowspan="2" class="plhead">OBJETIVOS</th>
        <th rowspan="2" class="plhead">OBSERVACIONES</th>
      </tr>

      <tr>
        <th class="plhead">E</th>
        <th class="plhead">F</th>
        <th class="plhead">M</th>
        <th class="plhead">A</th>
        <th class="plhead">M</th>
        <th class="plhead">J</th>
        <th class="plhead">J</th>
        <th class="plhead">A</th>
        <th class="plhead">S</th>
        <th class="plhead">O</th>
        <th class="plhead">N</th>
        <th class="plhead">D</th>
        </tr>
      </thead>

      <tbody>

<?php

if (!empty($id_proceso)) {
    $j= 0;

    for ($i= 1; $i < _MAX_TIPO_NOTA_ORIGEN; ++$i) {
        if ($tipo_plan == _PLAN_TIPO_AUDITORIA && ($i != _NOTA_TIPO_AUDITORIA_EXTERNA && $i != _NOTA_TIPO_AUDITORIA_INTERNA))
            continue;
        if ($tipo_plan == _PLAN_TIPO_SUPERVICION && ($i == _NOTA_TIPO_AUDITORIA_EXTERNA || $i == _NOTA_TIPO_AUDITORIA_INTERNA))
            continue;

        $ktotal= 0;
        $count= $obj->listyear($i);
        if ($count == 0)
            continue;
        ?>
        <tr>
            <td colspan="18" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$Ttipo_nota_origen_array[$i]?></div>
            </td>
        </tr>
        <?php

        reset($obj->array_procesos);

        foreach ($obj->array_procesos as $prs) {
            $count= $obj->listyear($i, null, $prs['id']);
            if ($count == 0)
                continue;

            $obj_signal->array_auditorias= $obj->array_auditorias;

            foreach ($obj->array_auditorias as $evento) {
                if (!is_null($evento['id_auditoria']))
                    continue;
                if ($evento['periodic'] && empty($evento['hit']))
                    continue;

                $memo= $evento['memo'];

                $obj_event->Set($evento['id']);
                $fecha_inicio= $obj_event->GetFechaInicioPlan();
                $fecha_fin= $obj_event->GetFechaFinPlan();

                if (isset($obj_audit)) unset ($obj_audit);
                $obj_audit= new Ttipo_auditoria($clink);
                $obj_audit->SetId($evento['id_tipo_auditoria']);
                $obj_audit->SetIdTipo_auditoria($evento['id_tipo_auditoria']);
                $obj_audit->Set();
                $auditoria= $obj_audit->GetNombre();
                ++$ktotal;
                $title= "{$Ttipo_nota_origen_array[$evento['origen']]}  /  $auditoria";
            ?>

                <tr >
                    <td class="plinner" style="background-color:#EAF4FF"><?=++$j?></td>
                    <td class="plinner" style="min-width:200px;">
                        <?php
                        $evento['evento']= $prs['nombre'].'<br/>'.$Ttipo_proceso_array[$prs['tipo']];
                        $evento['id_proceso']= $prs['id'];
                        $obj_signal->write_html($evento, false, true, false);
                        ?>
                    </td>

                    <td class="plinner"><?=$auditoria?></td>

                    <td class="plinner">
                       <?php
                       $email= $obj_user->GetEmail($evento['id_responsable']);
                       if (!empty($evento['jefe_auditor']))
                           echo $evento['jefe_auditor'].'<br/>';
                       echo textparse($email['nombre']).', '.textparse($email['cargo']);
                       ?>
                    </td>

                    <?php for ($k= 1; $k < 13; ++$k) { ?>
                        <td class="plinner" valign="middle" align="center" style="min-width:30px;">
                            <?php $obj_signal->do_list($evento, $k, _YEAR); ?>
                        </td>
                    <?php } ?>

                    <td class="plinner">
                    <?php
                    $obj->set_user_date_ref($evento['fecha_inicio']);
                    $array= $obj->get_participantes($evento['id'], 'auditoria', null, null, $id_proceso);
                    echo $array;

                    $origen_data= $obj->GetOrigenData('participant', $evento['origen_data_asigna']);
                    if (!is_null($origen_data))
                        echo "<br /> ".merge_origen_data_participant($origen_data);
                    ?>
                    </td>
                    <td class="plinner"><?= textparse(purge_html($evento['objetivos'], false), false)?></td>
                    <td class="plinner"><?=textparse(purge_html($memo, false), false)?></td>
                </tr>

          <?php
            } }

            if ($ktotal == 0) {
          ?>

          <tr>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
            <td class="plinner">&nbsp;</td>
          </tr>

<?php } } } ?>

      </tbody>
    </table>
</div>
