<?php

function write_html($result_indi) {
    global $obj_user;
    global $array_criterio;
    global $clink;
    global $year;
    global $month;
    global $day;

    $obj= new Tcell($clink); 
    $obj->SetDay($day);
    $obj->SetMonth($month);
    $obj->SetYear($year);

    $obj_um= new Tunidad($clink);
    $i= 0;
    while ($row= $clink->fetch_array($result_indi)) {
        ++$i;
        $critico= $row['_critico'];
        $obj->SetIdIndicador($row['_id']);
        $obj->SetIndicador($row['_id']);
        $array_indicadores[$row['_id']]= $row['_id'];

        $obj->fix_year= true;
        $obj->fix_interval= true;
        $obj->Set();

        $periodicidad= $obj->GetPeriodicidad();
        $carga= $obj->GetCarga();
        $trend= $obj->GetTrend();
        $criterio= $array_criterio[$trend];
        $cumulative= $obj->GetIfCumulative();
        $cumulative= !empty($cumulative) ? 1 : 0;

        $formulated= $obj->GetIfFormulated();
        $formulated= !empty($formulated) ? 1 : 0;

        if (empty($trend) || empty($periodicidad)) 
            $trend= null;

        $nombre= textparse($obj->GetNombre());
        $descripcion= $obj->GetDescripcion();

        $id_user_plan= $obj->GetIdUserPlan();
        $origen_user_plan= merge_origen_data_user($obj->GetOrigenData('user_plan'));
        $observacion_plan= $obj->GetObservacionPlan();
        $registro_plan= '&nbsp;';

        if (!empty($id_user_plan)) {
            $user= $obj_user->GetEmail($id_user_plan);
            if (is_array($user)) 
                $origen_user_plan= $user['nombre'].', '.textparse($user['cargo']);;
        }

        if (!empty($obj->row_plan[0]['id_usuario'])) {
            $usr= $obj_user->GetEmail($obj->row_plan[0]['id_usuario']);
            $registro_plan= $usr['nombre'].', '.textparse($usr['cargo']);
            $registro_plan.= ',  '.odbc2time_ampm($obj->row_plan[0]['cronos']);

        } elseif (!empty($obj->row_plan[0]['origen_data'])) {
            $registro_plan= merge_origen_data_user($obj->row_plan[0]['origen_data']);
            $registro_plan.= '<br />fecha y hora: '.odbc2time_ampm($obj->row_plan[0]['cronos']);
        }

        $plan= $obj->GetPlan();
        if (!is_null($plan)) 
            $plan= number_format($plan, $obj->GetDecimal(),'.','');

        $plan_cot= $obj->GetPlan_cot();
        if (!is_null($plan_cot)) 
            $plan_cot= number_format($plan_cot, $obj->GetDecimal(),'.','');

        $acumulado_plan= $cumulative ? $obj->GetAcumuladoPlan() : NULL;
        if (!is_null($acumulado_plan)) 
            $acumulado_plan= number_format($acumulado_plan, $obj->GetDecimal(),'.','');
        $acumulado_plan_cot= $cumulative ? $obj->GetAcumuladoPlan_cot() : NULL;
        if (!is_null($acumulado_plan_cot)) 
            $acumulado_plan_cot= number_format($acumulado_plan_cot, $obj->GetDecimal(),'.','');

        $id_user_real= $obj->GetIdUserReal();
        $origen_user_real= merge_origen_data_user($obj->GetOrigenData('user_real'));
        $observacion_real= $obj->GetObservacionReal();
        $registro_real= '&nbsp;';

        if (!empty($id_user_real)) {
            $user= $obj_user->GetEmail($id_user_real);
            if (is_array($user)) 
                $origen_user_real= $user['nombre'].', '.textparse($user['cargo']);
        }

        if (!empty($obj->row_real[0]['id_usuario'])) {
            $usr= $obj_user->GetEmail($obj->row_real[0]['id_usuario']);
            $registro_real= $usr['nombre'].', '.textparse($usr['cargo']);
            $registro_real.= '  '.odbc2time_ampm($obj->row_real[0]['cronos']);

        } elseif (!empty($obj->row_real[0]['origen_data'])) {
            $registro_real= merge_origen_data_user($obj->row_real[0]['origen_data']);
            $registro_real.= '<br />fecha y hora: '.odbc2time_ampm($obj->row_real[0]['cronos']);
        }

        $real= $obj->GetReal();
        if (!is_null($real)) 
            $real= number_format($real, $obj->GetDecimal(),'.','');
        $acumulado_real= $cumulative ? $obj->GetAcumuladoReal() : null;
        if (!is_null($acumulado_real)) 
            $acumulado_real= number_format($acumulado_real, $obj->GetDecimal(),'.','');

        $percent= $obj->GetPercent();
        if (!is_null($percent)) 
            $percent=number_format($percent, 1,'.','').'%';

        $ratio= $obj->GetRatio();
        if (!is_null($ratio)) 
            $ratio= ' (<strong>Cump:</strong>'.number_format($ratio, 1,'.','').'%';

        $percent_cumulative= $cumulative ? $obj->GetPercent_cumulative() : null;
        if (!is_null($percent_cumulative)) 
            $percent_cumulative= number_format($percent_cumulative, 1,'.','').'%';

        $ratio_cumulative= $cumulative ? $obj->GetRatio_cumulative() : null;
        if (!is_null($ratio_cumulative)) 
            $ratio_cumulative= ' (<strong>Cump:</strong>'.number_format($ratio_cumulative, 1,'.','').'%';

        $obj_um->SetIdUnidad($obj->GetIdUnidad());
        $obj_um->Set();
        $unidad= $obj_um->GetNombre();

        if (is_null($real) && !is_null($observacion_real)) 
            $value= 'INFO (ver)';
        if (is_null($real) && is_null($observacion_real)) 
            $value= '&Oslash;';

        $alarm= $obj->GetAlarm();
        $arrow= $obj->GetFlecha();
        $alarm_cumulative= $cumulative ? $obj->GetAlarm_cumulative() : null;
        $arrow_cumulative= $cumulative ? $obj->GetFlecha_cumulative() : null;
?>
        <br />
        <table width='612px' border='0'>
            <tr>
                <td class="none-border" align="left">
                    <strong><?=$nombre?></strong> 
                    <?=$critico ? " <b>**</b><spam style='font-style:oblique'>indicador critico</spam>" : ""?>
                </td>
            </tr>
            <tr>
                <td class="none-border">

                    <table>
                        <tr>
                            <td class="none-border">
                                Ejecucion mensual:

                                <table id="tb-resume" cellspacing='0' cellpadding='0'>
                                    <thead>
                                        <tr>
                                            <th class="plhead left">U</th>
                                            <th class="plhead">Plan<br/><?=$corte?></th>
                                            <th class="plhead">Real</th>
                                            <th class="plhead">%</th>
                                            <th class="plhead">Estado</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td class="plinner left"><?=$unidad?></td>
                                            <td class="plinner"><?="$plan"?></td>
                                            <td class="plinner"><?=$real?></td>
                                            <td class="plinner"><?=$percent?></td>
                                            <td class="plinner">
                                                <div class="alarm-block row">
                                                   <div class="col-6">
                                                       <div class="alarm-cicle bg-<?=$alarm?>" title="<?= tooltip_alarm($alarm, true)?>"></div>
                                                   </div>
                                                   <div class="col-3">
                                                       <div class="alarm-arrow vertical bg-<?=$arrow?>" title="<?= tooltip_arrow($arrow, true)?>">
                                                       <i class="fa <?=arrow_direction($arrow_cumulative)?>"></i>
                                                    </div>
                                                   </div>                           
                                                </div> 
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </td>

                            <td class="none-border" style="width:20px!important;"></td>

                            <td class="none-border">
                                <?php if ($cumulative) { ?>
                                    Acumulado Anual:

                                    <table id="tb-resume" border='1' cellspacing='0' cellpadding='0'>
                                        <thead>
                                            <tr>
                                                <th class="plhead left">U</th>
                                                <th class="plhead">Plan</th>
                                                <th class="plhead">Real</th>
                                                <th class="plhead">%</th>
                                                <th class="plhead">Estado</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td class="plinner left"><?=$unidad?></td>
                                                <td class="plinner"><?=$acumulado_plan?></td>
                                                <td class="plinner"><?=$acumulado_real?></td>
                                                <td class="plinner"><?=$percent_cumulative?></td>
                                                <td class="plinner">
                                                    <div class="alarm-block row">
                                                       <div class="col-6">
                                                           <div class="alarm-cicle bg-<?=$alarm_cumulative?>" title="<?= tooltip_alarm($alarm_cumulative, true)?>"></div>
                                                       </div>
                                                       <div class="col-3">
                                                           <div class="alarm-arrow vertical bg-<?=$arrow_cumulative?>" title="<?= tooltip_arrow($arrow_cumulative, true)?>">
                                                           <i class="fa <?=arrow_direction($arrow_cumulative)?>"></i>
                                                        </div>
                                                       </div>                           
                                                    </div>  
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>

            <tr>
                <td class="none-border" colspan="2">
                    <?php
                    if ($id_user_plan != _USER_SYSTEM) { ?>
                        <u>Plan. Observaciones:</u><br />
                    <?php
                        echo "<em>$registro_plan</em><br /><br />"; 
                        echo "$observacion_plan";
                    }    
                    ?>
                </td>
            </tr>

            <tr>
                <td class="none-border" colspan="2">
                    <?php if ($id_user_real != _USER_SYSTEM) { ?>
                        <u>Real. Observaciones:</u><br />
                    <?php
                        echo "<em>$registro_real</em><br /><br />";
                        echo "$observacion_real";
                    }    
                    ?>
                </td>
            </tr>

            <tr>
                <td class="none-border" colspan="2"><hr /></td>
            </tr>

        </table>
<?php
    }
}
?>
