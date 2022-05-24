<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 18/02/15
 * Time: 16:30
 */

$id_perspectiva= (!empty($id_perspectiva) && $signal == 'tablero') ? $id_perspectiva : 0;

if (isset($obj_prs)) unset($obj_prs);
$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

if (isset($obj_proj)) unset($obj_proj);
$obj_proj= new Tproyecto($clink);

$obj_prs= new Tproceso_item($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$array_indicadores= $obj_prs->listar_indicadores();

$i= 0;
while ($row= $clink->fetch_array($result_indi)) {
    $prs= $array_procesos_entity[$row['_id_proceso']];
    $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
    $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;  

    if ($id_entity != $_SESSION['id_entity']) {
        if (!array_key_exists($row['_id'], $array_indicadores))
            continue;
    }    

    ++$i;
    $peso= $row['_peso'];
    $obj->SetIdIndicador($row['_id']);
    $obj->_init();
    $obj->SetIndicador($row['_id']);
    $array_indicadores[$row['_id']]= $row['_id'];

    $error= $obj_cal->_validar_code($row['id_code']);
    if ($error) {
    ?>    
    <div class="col-4">
        <div class="alert alert-danger">
            Error en la formula del indicador <strong><?=$row['nombre']?></strong>
        </div>  
    </div>
    <?php        
        continue;
    }
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

    $nombre= $row['_nombre'];
    $descripcion= $obj->GetDescripcion();

    $id_user_plan= $obj->GetIdUserPlan();
    $origen_user_plan= merge_origen_data_user($obj->GetOrigenData('user_plan'));
    $observacion_plan= $obj->GetObservacionPlan();
    $registro_plan= '&nbsp;';

    if (!empty($id_user_plan)) {
        $user= $obj_user->GetEmail($id_user_plan);
        if (is_array($user)) 
            $origen_user_plan= $user['nombre'].', '.textparse($user['cargo']);
    }

    if (!empty($obj->row_plan[0]['id_usuario'])) {
        $usr= $obj_user->GetEmail($obj->row_plan[0]['id_usuario']);
        $registro_plan= $usr['nombre'].', '.textparse($usr['cargo']).',  '.odbc2time_ampm($obj->row_plan[0]['cronos']);

    } elseif (!empty($obj->row_plan[0]['origen_data'])) 
        $registro_plan= merge_origen_data_user($obj->row_plan[0]['origen_data']).'<br />fecha y hora: '.odbc2time_ampm($obj->row_plan[0]['cronos']);

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

    $id_unidad= $obj->GetIdUnidad();
    $obj_unidad->Set($id_unidad);
    
    if (!is_null($real) || !is_null($plan)) 
        $unidad= $obj_unidad->GetNombre();
    else 
        $unidad= "";

    if (is_null($real) && !is_null($observacion_real)) 
        $value= 'INFO (ver)';
    if (is_null($real) && is_null($observacion_real)) 
        $value= '&Oslash;';

    $alarm= $obj->GetAlarm();
    $arrow= $obj->GetFlecha();
    $alarm_cumulative= $cumulative ? $obj->GetAlarm_cumulative() : NULL;
    $arrow_cumulative= $cumulative ? $obj->GetFlecha_cumulative() : NULL;

    $fecha_real= $obj->GetFecha_real();
    $strfecha_real= $obj->GetStrFecha_real();
    $fecha_plan= $obj->GetFecha_plan();
    $strfecha_plan= $obj->GetStrFecha_plan();

    $updated_real= $obj->updated;
    $updated_plan= $obj->updated_plan;

    $obj_prs->Set($row['_id_proceso']);
    $proceso= $obj_prs->GetNombre().',  '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

    if ($obj_prs->GetConectado() != _NO_LOCAL && $row['_id_proceso'] != $_SESSION['local_proceso_id']) 
        $img= 'transmit.ico';
    else 
        $img= 'process.ico';
    
    $proyecto= null;
    if (!empty($row['id_proyecto'])) {
        $obj_proj->Set($row['id_proyecto']);
        $proyecto= $obj_proj->GetNombre();

        $proj_inicio= date('m/Y', strtotime($obj_proj->GetFechaInicioPlan()));
        $proj_fin= date('m/Y', strtotime($obj_proj->GetFechaFinPlan()));       

        $proyecto= "<strong>PROYECTO:</strong> $proyecto /  $proj_inicio - $proj_fin"; 
    }
    ?>
    <input type="hidden" id="trend_<?=$row['_id']?>" value="<?=$trend?>"  />
    <input type="hidden" id="cumulative_<?=$row['_id']?>" value="<?=$cumulative?>"  />
    <input type="hidden" id="formulated_<?=$row['_id']?>" value="<?=$formulated?>"  />

    <input type="hidden" id="id_user_real_<?=$row['_id']?>" value="<?=$id_user_real?>"  />
    <input type="hidden" id="id_user_plan_<?=$row['_id']?>" value="<?=$id_user_plan?>"  />

    <input type="hidden" id="registro_real_<?=$row['_id']?>" value="<?=$registro_real?>"  />
    <input type="hidden" id="registro_plan_<?=$row['_id']?>" value="<?=$registro_plan?>"  />

    <input type="hidden" id="valor_real_<?=$row['_id']?>" value="<?=$real?>" />
    <input type="hidden" id="valor_plan_<?=$row['_id']?>" value="<?=$plan?>" />

    <input type="hidden" id="valor_acumulado_real_<?=$row['_id']?>" value="<?=$acumulado_real?>"  />
    <input type="hidden" id="valor_acumulado_plan_<?=$row['_id']?>" value="<?=$acumulado_plan?>"  />

    <input type="hidden" id="observacion_real_<?=$row['_id']?>" value="<?=$observacion_real?>" />
    <input type="hidden" id="observacion_plan_<?=$row['_id']?>" value="<?=$observacion_plan?>" />

    <input type="hidden" id="responsable_real_<?=$row['_id']?>" value="<?=$origen_user_real?>" />
    <input type="hidden" id="responsable_plan_<?=$row['_id']?>" value="<?=$origen_user_plan?>" />

    <input type="hidden" id="nombre_<?=$row['_id']?>" value="<?= textparse($nombre, true)?>" />
    
    <input type="hidden" id="id_proyecto_<?=$row['id_proyecto']?>" value="<?=$proyecto?>" />
    
    <input type="hidden" id="if_entity_<?=$row['_id']?>" value="<?=$if_entity?>" />

    <?php
    $_class= null;
    $_str= null;

    if ($signal == 'proceso' && boolean($row['_critico'])) {
        $_class= "box-critical";
        $_str= "Indicador crítico para la eficacia del proceso. Su incumplimiento hace ineficaz al proceso";
    }

    $str= str_replace("\n","",$row['descripcion']);
    $str= str_replace("\r","",$str);
    $str= $_str."Origen de Datos:".$proceso." Descripción:".$str;

    $show_plan= true;
    /*
    $show_plan= false;
    if (((int)date('m', strtotime($fecha_plan)) <= (int)$month && (int)date('Y', strtotime($fecha_plan)) == (int)$year)
        || ((int)date('Y', strtotime($fecha_plan)) < (int)$year)) $show_plan= true;
    */
    ?>

    <div class="indicador-content inner">
        <div class="indicador box box-solid box-default">
            <div class="box-header border-bottom <?=$_class?>">
                <div class="row col-12">
                    <div class="row col-11 ml-0">
                        <div class="row ml-0 mr-0">
                            <div class="row col-12">
                                <?php if ($formulated) { ?>
                                    <i class="fa fa-calculator"></i>
                                <?php } ?> 
                                <?= $nombre ?>
                                (<div class="badge"><?=$unidad?></div>)
                            </div>                     

                            <div class="row col-12">
                                <img class="img-rounded" src="../img/<?= $img ?>" /> 
                                <div class="text"><?=$proceso?></div> 
                            </div>

                            <?php if (!empty($proyecto))  { ?>
                                <div class="col text">
                                    <?=$proyecto?>
                                </div> 
                            <?php } ?>
                        </div>

                        <?php if ($signal == 'proceso') { ?>
                            <div class="row col-12" style="margin-top: 6px;">
                                <label class="badge badge-info" style="font-size: 0.95em;">
                                    Impacto: <?=$Tpeso_inv_array[$peso]?>
                                </label>                         
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-1 mr-0"> 
                        <a href="javascript:ShowContent(<?=$row['_id']?>,'win-board-signal', <?=$id_perspectiva?>)">
                            <i class="fa fa-wrench fa-3x"></i>
                        </a> 
                    </div>
                </div>
            </div>

            <div class="box-body">              
                <div class="row">
                    <div class="col-6">
                        <div class="row col-12 border-bottom align-content-center">
                            Corte
                        </div> 

                        <div class="row description-block border-right">
                            <div class="row col-6 clearfix float-left">
                                <table class="signal">
                                    <tr>
                                        <td>
                                            <?php if($alarm == 'red') { ?>
                                            <div class="spinner-grow text-danger" role="status">
                                                <span class="sr-only">alerta...</span>
                                            </div>    
                                            <?php } else { ?>
                                            <div class="alarm-cicle bg-<?=$alarm?>" title="<?= tooltip_alarm($alarm, false)?>"></div>
                                            <?php } ?>
                                        </td>
                                        <td class="signal">
                                            <div class="alarm-arrow vertical bg-<?=$arrow?>" title="<?= tooltip_arrow($arrow, false)?>">
                                                <i class="fa <?=arrow_direction($arrow)?>"></i>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-6 clearfix float-right">
                                <div class="description-header text">
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">R/P </div>
                                        <?php
                                        if ($show_plan) 
                                            echo $percent;
                                        ?>                                        
                                    </div>
                                </div>
                                <div class="description-text">
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">R </div>
                                            <?php
                                            if (!$formulated || ($formulated && $obj->not_null_real_found)) 
                                                echo $real;
                                            ?>
                                    </div>
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">P </div>
                                        <?php
                                            echo $criterio . "&nbsp;";
                                            if (!$formulated || ($formulated && $obj->not_null_plan_found)) {
                                                if (!is_null($plan_cot)) 
                                                    echo "$plan_cot .. ";
                                                if (!is_null($plan)) 
                                                    echo $plan; 
                                            }    
                                        ?>
                                    </div>
                                </div>                               
                            </div>
                        </div>
                    </div>

                    <?php if ($cumulative) { ?>
                    <div class="col-6">   
                        <div class="row border-bottom center-block">
                            Acumulado
                        </div> 

                        <div class="row description-block">
                            <div class="row col-5 clearfix float-left">
                                <table class="signal">
                                    <tr>
                                        <td class="signal">
                                            <?php if($alarm_cumulative == 'red') { ?>
                                                <div class="spinner-grow text-danger" role="status">
                                                    <span class="sr-only">alerta...</span>
                                                </div>    
                                            <?php } else { ?>
                                                <div class="alarm-cicle bg-<?=$alarm_cumulative?>" title="<?= tooltip_alarm($alarm_cumulative, true)?>"></div>
                                            <?php } ?>
                                        </td>
                                        <td class="signal">
                                            <div class="alarm-arrow vertical bg-<?=$arrow_cumulative?>" title="<?= tooltip_arrow($arrow_cumulative, true)?>">
                                                <i class="fa <?=arrow_direction($arrow_cumulative)?>"></i>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-6 clearfix float-right">
                                <div class="description-header text">
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">R/P </div> 
                                        <?= $percent_cumulative ?>                                          
                                    </div>
                                </div>
                                <div class="description-text">
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">R </div>
                                        <?php 
                                            if (!$formulated || ($formulated && $obj->not_null_acumulado_real_found)) 
                                                echo $acumulado_real; 
                                        ?>
                                    </div>
                                    <div class="row">
                                        <div class="badge bg-gray pull-left">P </div>
                                        <?php
                                            echo $criterio . "&nbsp;";
                                            if (!$formulated || ($formulated && $obj->not_null_acumulado_plan_found)) {
                                                if (!is_null($acumulado_plan_cot)) 
                                                    echo "acumulado_$plan_cot .. ";
                                                if (!is_null($acumulado_plan)) 
                                                    echo $acumulado_plan;
                                            }
                                        ?>                                          
                                    </div>
                                </div>                               
                            </div>
                        </div>
                    </div> 
                    <?php } ?>
                    
                    <?php if (is_array($obj->null_real_found) || is_array($obj->null_plan_found)) { ?>
                    <div class="col-12">
                        <strong>NULOS: </strong>
                        <?php
                        $j= 0;
                        $array_nulls= array_merge($obj->null_real_found, $obj->null_plan_found);
                        $array_nulls= array_unique($array_nulls, SORT_NUMERIC);
                        foreach ($array_nulls as $id) {
                            ++$j;
                            if ($j > 1)
                                echo " <strong style='font-weight:bolder'>||</strong> ";
                            $obj_indi->Set($id);
                            echo $obj_indi->GetNombre();
                            $obj_prs->Set($obj_indi->GetIdProceso());
                            echo ", ".$obj_prs->GetNombre(); 
                        } 
                    ?>
                    </div>
                    <?php } ?>
                    
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <ul class="nav nav-stacked">
                    <li>
                        Carga:
                        <?php
                        if ($updated_real) {
                            $class= "enfecha";
                            $blink= "$strfecha_real";
                        } else {
                            $class= "fuera_fecha";
                            $blink= "<blink>$strfecha_real</blink>";
                        }
                        if ($strfecha_real == "actualizar") {
                            $class= "update_fecha";
                            $blink= "<blink>$strfecha_real</blink>";
                        }
                        ?> 
                        <span class="badge <?=$class?>">
                            <?=$blink?>
                        </span>
                        <span class="pull-right badge"><?=$periodo_inv[$carga]?></span>
                    </li>
                    <li>
                        Corte:
                        <?php
                        $blink= null;
                        if ($updated_plan) {
                            $class= "enfecha";
                            $blink= "$strfecha_plan";
                        } else {
                            $class= "fuera_fecha";
                            $blink= "<blink>$strfecha_plan</blink>";
                        }
                        if ($strfecha_plan == "actualizar") {
                            $class= "update_fecha";
                            $blink= "<blink>$strfecha_plan</blink>";
                        }
                    ?>                     
                        
                        <span class="badge <?=$class?>">
                            <?=$blink?>
                        </span>
                        <span class="pull-right badge"><?=$periodo_inv[$periodicidad]?></span>

                    </li>
                </ul>
            </div>
        </div><!-- /.box -->
    </div>

<?php } ?>