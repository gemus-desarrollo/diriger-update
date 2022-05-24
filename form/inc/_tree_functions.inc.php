<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 2/03/15
 * Time: 8:00
 */

/**
 * @param $result_obj
 * @param $k_obj
 * @param $k_ind
 * @param $k_indi
 * @param $level 0:son los objetivos esrategicos del primer nivel
 * @param int $k_obj_sup
 * @param bool true:$if_obj_sup si el objetivo que genero la consulta es un objetivo superior
 * @param bool $if_pol false: no es un lineamiento
 */
function _tree_objetivo($result_obj, &$k_obj, &$k_ind, &$k_indi, $level, $k_obj_sup= 0, $if_obj_sup= false, $if_pol= false) {
    global $clink;
    global $id_proceso, 
           $id_proceso_code;
    global $year;
    global $month;
    global $day;

    global $item_planning_array;
    global $Ttipo_proceso_array;
    global $Tpeso_inv_array;
    global $obj_indi;
    global $obj_peso;
    global $if_obj_sup_LOCAL;
    global $array_procesos_entity;

    $obj_signal= new Tlist_signals($clink);
    $obj_signal->SetYear($year);
    $obj_signal->SetMonth($month);
    
    $obj= new Tobjetivo($clink);  
    $obj_user= new Tusuario($clink);
    $obj_prs= new Tproceso($clink);
    
    $_k_obj= 0; 
    $_k_obj_sup= 0;

    while ($row_obj= $clink->fetch_array($result_obj)) {
        $prs= $array_procesos_entity[$row_obj['id_proceso']];
        if (!empty($prs['id_entity']) && ((!$if_obj_sup_LOCAL && $prs['id_entity'] != $_SESSION['id_entity'])
                                        || ($if_obj_sup_LOCAL && $prs['id'] != $_SESSION['superior_proceso_id'])))
            continue;
        if (empty($prs['id_entity']) && (!$row_obj['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
            continue;
        if (empty($prs['id_entity']) && (!$row_obj['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
            continue;

        $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
        $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;
        
        ++$k_obj;
        if ($level == 0 && $if_obj_sup) 
            ++$k_obj_sup;

        $id_objetivo= $row_obj['_id'];
        $peso= $row_obj['_peso'];
        $obj->Set($id_objetivo);
        $nombre= $obj->GetNombre();

        $obj_peso->SetIdProceso($id_proceso);
        $obj_peso->set_id_proceso_code($id_proceso_code);
        
        $obj_peso->SetDay($day);
        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->compute_traze= true;
        $value2= $obj_peso->calcular_objetivo($id_objetivo);
        $array_register= $obj_peso->get_array_register();

        $obj_signal->get_month($_month, $_year);
        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($_year, $_month);
        $obj_peso->SetDay(null);
        $value1= $obj_peso->calcular_objetivo($id_objetivo);

        $id_user= $array_register['id_usuario'];
        $item_reg= $array_register['signal'];

        $responsable= null;
        $observacion= null;

        if ($id_user && $item_reg == 'OBJ') {
            $observacion= textparse($array_register['observacion'], true);

            $email_user= $obj_user->GetEmail($id_user);
            $responsable= $email_user['nombre'];
            if (!is_null($email_user['cargo'])) 
                $responsable.= ', '.textparse($email_user['cargo'], true);
            $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
        }
        ?>

        <?php
        $_item_sup_obj= 0;

        if (!$if_pol) {
            $_item_obj= ($level == 0 && $if_obj_sup) ? "obj_sup" : "obj";

            if ($level == 0) 
                $_item_sup_obj= 0;
            else 
                $_item_sup_obj= ($level == 1 && $if_obj_sup) ? "'obj_sup'" : "'obj'";

            $_k_obj= ($level == 0 && $if_obj_sup) ? $k_obj_sup : $k_obj;
            $_k_obj_sup= ($level == 0) ? 0 : $k_obj_sup;
        }
        else {
            $_item_sup_obj= "'pol'";
            $_item_obj= 'obj';
            $_k_obj= ($level == 0 && $if_obj_sup) ? $k_obj_sup : $k_obj;
            $_k_obj_sup= $k_obj_sup;
        }
        ?>

        <input type="hidden" form="treeForm" id="id_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$id_objetivo?>" />
        <input type="hidden" form="treeForm" id="observacion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$observacion?>" />
        <input type="hidden" form="treeForm" id="registro_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$responsable?>" />
        <input type="hidden" form="treeForm" id="descripcion_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$nombre?>" />
        <input type="hidden" form="treeForm" id="if_entity_<?=$_item_obj?>_<?=$_k_obj?>" value="<?=$if_entity?>" />

        <input type="hidden" id="page_objetivo_<?=$_item_obj?>_<?=$_k_obj?>" name="page_objetivo_<?=$id_objetivo?>" value="<?=$id_objetivo?>" />
        
        <li id="<?=$_item_obj?>_li_<?=$_k_obj?>">
            <div class=ul_pol onmouseover="this.className='ul_pol rover-obj'" onmouseout="this.className='ul_pol'">
                <div class="div_inner_ul_li" onclick="refresh_<?=$_item_obj?>('<?=$_item_obj?>_ul_<?=$_k_obj?>')">
                    
                    <div class="alarm-block">
                        <i id="img_<?=$_item_obj?>_li_<?=$_k_obj?>" class="fa fa-search-plus" title="expandir"></i>
                        <div class="alarm-block">
                        <?php
                        $obj_signal->get_alarm($value2);
                        $obj_signal->get_flecha($value2, $value1);
                        ?>      
                        </div>                  
                    </div>

                    <?php
                    $nombre= get_short_label($nombre);

                    $obj_prs->Set($row_obj['id_proceso']);
                    $tipo_prs= $obj_prs->GetTipo();
                    $proceso= $obj_prs->GetNombre();
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                    $numero= !empty($row_obj['numero']) ? $row_obj['numero'] : $_k_obj;
                    ?>
                </div>

                <div class="div_inner_ul_li" onclick="ShowContentItem('<?=$_item_obj?>', <?=$_k_obj?>, <?=$_item_sup_obj?>, <?=$_k_obj_sup?>);">
                    <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>
                    <span class="flag">No.<?=$numero?> </span><?=$nombre?>

                    <br />
                    <img class="img-rounded" src="../img/<?=img_item_planning('obj')?>" title="<?=$item_planning_array['obj']?>" />
                    <img class="img-rounded" src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>"/><strong class="strong-title"><?=$proceso?></strong>
                    
                    <strong class="strong-title">periodo: </strong><?="{$row_obj['inicio']}-{$row_obj['fin']}"?>
                    <?php if (!is_null($peso)) { ?>
                    <strong class="strong-title">Ponderación: <span class="peso"> <?=$Tpeso_inv_array[$peso]?></span></strong>
                    <?php } ?>

                    <?php if (!$if_obj_sup_LOCAL) { ?>
                        <strong class="strong-title">lineamientos:</strong>
                        <?php
                        $obj->SetIdProceso(null);
                        $array= $obj->get_politicas($row_obj['_id']);

                        $j= 0;
                        foreach ($array as $cell) {
                            if ($j > 0) {
                                echo ", ";
                            }
                            echo ' L'.$cell['numero'].' ';
                            ++$j;
                        }
                    }
                    ?>
                </div>
            </div>

            <?php
            $obj_peso->SetYear($year);
            $obj_peso->SetIdProceso(null);
            $iresult_obj= $obj_peso->listar_objetivos_ref_objetivo_sup($id_objetivo);
            $_cant_obj_li= $obj_peso->GetCantidad();

            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);
            $obj_peso->SetIdProceso($id_proceso);

            $result_ind= $obj_peso->listar_inductores_ref_objetivo($id_objetivo, true);
            $_cant_obj= $obj_peso->GetCantidad();

            $t_k_obj= $k_obj;
            ?>

            <input type="hidden" form="treeForm" id="_cant_<?=$_item_obj?>_ul_<?=$_k_obj?>" value=<?=$_cant_obj_li?> />

            <?php if ($_cant_obj_li > 0 || $_cant_obj > 0) { ?>
                <ul id="<?=$_item_obj?>_ul_<?=$_k_obj?>" style="display:none">
                    <?php
                    if ($_cant_obj_li > 0) {
                        $_if_obj_sup_LOCAL= $if_obj_sup_LOCAL;
                        $if_obj_sup_LOCAL= false;
                        
                        _tree_objetivo($iresult_obj, $k_obj, $k_ind, $k_indi, $level+1, $k_obj_sup, $if_obj_sup);

                        $if_obj_sup_LOCAL= $_if_obj_sup_LOCAL;
                    }
                        
                    if ($_cant_obj > 0) {
                        $_cant_obj= $_cant_obj_li + $clink->num_rows($result_ind);
                    ?>
                        <script language="javascript">
                            $("#_cant_<?=$_item_obj?>_ul_<?=$_k_obj?>").val(<?=$_cant_obj?>);
                        </script>
                    <?php } ?>

                    <?php
                    $if_top_inductor= false;
                    $id_item_sup= $t_k_obj;
                    $item_sup= 'obj';
                    include "_tree_inductor.inc.php";
                    ?>
                </ul>
            <?php } ?>
        </li>
    <?php
    }

    $obj_peso->SetYearMonth($year, $month);
}


function _tree_indicadores($result_indi, &$k_indi, $item_sup, $k_sup) {
    global $config;
    global $clink;
    global $year;
    global $month;
    global $Ttipo_proceso_array;
    global $item_planning_array;
    global $Tpeso_inv_array;
    global $array_procesos_entity;
    global $obj_peso;
    global $obj_signal;
    
    $obj_user= new Tusuario($clink);
    $obj_indi= new Tindicador($clink);
    
    $_year= null;
    $_month= null;

    $obj_prs= new Tproceso_item($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdProceso($_SESSION['id_entity']);
    $array_indicadores= $obj_prs->listar_indicadores();

    while ($row_indi= $clink->fetch_array($result_indi)) {
        $prs= $array_procesos_entity[$row_indi['_id_proceso']];
        $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
        $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;  
        
        if ($id_entity != $_SESSION['id_entity']) {
            if (!array_key_exists($row_indi['_id'], $array_indicadores))
                continue;
        }
        
        $id= $row_indi['_id'];
        $peso= $row_indi['_peso'];

        $obj_indi->SetYear($year);
        $obj_indi->SetIdProceso(null);
        $obj_indi->Set($id);
        $trend= $obj_indi->GetTrend();

        $nombre= $obj_indi->GetNombre();
        $cumulative= $obj_indi->GetIfCumulative();
        $cumulative= !empty($cumulative) ? 1 : 0;

        $formulated= $obj_indi->GetIfFormulated();
        $formulated= !empty($formulated) ? 1 : 0;

        // el indicador no esta definido en el ano selecionado
        if (is_null($nombre)) 
            continue;

        ++$k_indi;
?>
        <li>
        <?php
        $obj_peso->init_calcular();
        $obj_peso->compute_traze= true;
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->SetIdIndicador($id);

        $_array= $obj_peso->calcular_indicador($id);

        $_array2= null;
        if (!empty($obj_signal)) {
            $obj_signal->get_month($_month, $_year);
            $obj_peso->SetYearMonth($_year, $_month);

            $_array2= $obj_peso->calcular_indicador($id);
            $arrow_array= $obj_peso->GetArrow($id, $year, $month, $_year, $_month);
            $_array['arrow']= $arrow_array['arrow'];
            $_array['arrow_cumulative']= $arrow_array['arrow_cumulative'];
        }

        $real= !is_null($_array['real']) ? $obj_indi->formated_value($_array['real']) : null;
        $plan= !is_null($_array['plan']) ? $obj_indi->formated_value($_array['plan']) : null;

        $acumulado_real= !is_null($_array['acumulado_real']) ? $obj_indi->formated_value($_array['acumulado_real']) : null;
        $acumulado_plan= !is_null($_array['acumulado_plan']) ? $obj_indi->formated_value($_array['acumulado_plan']) : null;
        $acumulado_plan_cot= !is_null($_array['acumulado_plan_cot']) ? $obj_indi->formated_value($_array['acumulado_plan_cot']) : null;

        $id_user_plan= $_array['id_user_plan'];
        $origen_user_plan= $_array['origen_user_plan'];

        if (!empty($id_user_plan)) {
            $user= $obj_user->GetEmail($id_user_plan);
            if (is_array($user)) 
                $origen_user_plan= $user['nombre'].', '.textparse($user['cargo']);
        }

        $id_user_real= $_array['id_user_real'];
        $origen_user_real= $_array['origen_user_real'];

        if (!empty($id_user_real)) {
            $user= $obj_user->GetEmail($id_user_real);
            if (is_array($user)) 
                $origen_user_real= $user['nombre'].', '.textparse($user['cargo']);
        }
     ?>

     <input type="hidden" form="treeForm" id="trend_<?=$id?>" value="<?=$trend?>"  />

     <input type="hidden" form="treeForm" id="id_user_real_<?=$id?>" value="<?=$id_user_real?>"  />
     <input type="hidden" form="treeForm" id="id_user_plan_<?=$id?>" value="<?=$id_user_plan?>"  />

     <input type="hidden" form="treeForm" id="registro_real_<?=$id?>" value="<?=$_array['registro_real']?>"  />
     <input type="hidden" form="treeForm" id="registro_plan_<?=$id?>" value="<?=$_array['registro_plan']?>"  />

     <input type="hidden" form="treeForm" id="cumulative_<?=$id?>" value="<?=$cumulative ? 1 : 0?>"  />
     <input type="hidden" form="treeForm" id="formulated_<?=$id?>" value="<?=$formulated ? 1 : 0?>"  />
     <input type="hidden" form="treeForm" id="valor_real_<?=$id?>" value="<?=$_array['real']; ?>"  />
     <input type="hidden" form="treeForm" id="valor_plan_<?=$id?>" value="<?=$_array['plan']; ?>"  />
     <input type="hidden" form="treeForm" id="valor_acumulado_real_<?=$id?>" value="<?=$_array['acumulado_real']?>"  />
     <input type="hidden" form="treeForm" id="valor_acumulado_plan_<?=$id?>" value="<?=$_array['acumulado_plan']?>"  />
     <input type="hidden" form="treeForm" id="valor_acumulado_plan_cot_<?=$id?>" value="<?=$_array['acumulado_plan_cot']?>"  />

     <input type="hidden" form="treeForm" id="observacion_real_<?=$id?>" value="<?=$_array['observacion_real']?>" />
     <input type="hidden" form="treeForm" id="observacion_plan_<?=$id?>" value="<?=$_array['observacion_plan']?>" />

     <input type="hidden" form="treeForm" id="responsable_real_<?=$id?>" value="<?=$origen_user_real?>" />
     <input type="hidden" form="treeForm" id="responsable_plan_<?=$id?>" value="<?=$origen_user_plan?>" />

     <input type="hidden" form="treeForm" id="nombre_<?=$id?>" value="<?=$nombre?>" />
     
     <input type="hidden" form="treeForm" id="if_entity_indi_<?=$id?>" value="<?=$if_entity?>" />

     <?php
     $obj_prs->Set($row_indi['id_proceso']);
     $tipo_prs= $obj_prs->GetTipo();
     $proceso= $obj_prs->GetNombre();
     $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
     ?>

     <div class="ul_ind" <?php if (!$config->hide_values) { ?>onclick="ShowContentItem('indi', <?=$id?>, '<?=$item_sup?>', <?=$k_sup ?>);"<?php } ?> 
                                                            accesskey=""onmouseover="this.className='ul_ind rover-indi'" onmouseout="this.className='ul_ind'">
         <div class="alarm-block">
             <div class="alarm-cicle small bg-<?=$_array['alarm']?>" title='<?= tooltip_alarm($_array['alarm'], false)?>'></div>
             <div class="alarm-arrow vertical small bg-<?=$_array['arrow']?>" title='<?= tooltip_arrow($_array['arrow'], false)?>'>
                 <i class="fa <?=arrow_direction($_array['arrow'])?>"></i>
             </div>
             
             <?php if ($cumulative) { ?>
                <div class="alarm-cicle small bg-<?=$_array['alarm_cumulative']?>" title='<?= tooltip_alarm($_array['alarm_cumulative'], true)?>'></div>
                <div class="alarm-arrow vertical small bg-<?=$_array['arrow_cumulative']?>" title='<?= tooltip_arrow($_array['arrow_cumulative'], true)?>'>
                    <i class="fa <?=arrow_direction($_array['arrow_cumulative'])?>"></i>
                </div>  
             <?php } ?>
         </div>
        
         <div class="alarm-text">
            <span class="flag"> No.<?=$k_indi?></span>
            <span class="_value">
                <?php 
                if (!is_null($_array['ratio'])) {
                    $data= "(".number_format($_array['ratio'], 1,'.','')."%) ";
                    if (!$config->hide_values) 
                        $data.= "Real/Plan: ".$real."/".$plan;
                    echo $data;
                }    
                ?> 
            </span>

            <?php if ($cumulative) { ?>
                <span class="res-name">Acumulado:</span>
                <span class="_value">
                    <?php 
                    if (!is_null($_array['ratio_cumulative'])) {
                        $data= "(".number_format($_array['ratio_cumulative'], 1,'.','')."%) ";
                        if (!$config->hide_values) 
                            $data.= "Real/Plan: ".$acumulado_real."/".$acumulado_plan;
                        echo $data;
                    }    
                    ?> 
                </span>
            <?php } ?>

            <span class="res-name"><?=$nombre?></span>

            <br />
            <img class="img-rounded" src="../img/<?=img_item_planning('indi')?>" title="<?=$item_planning_array['indi']?>" />
           <?php if ($cumulative) { ?>
               <img class="img-rounded" src="../img/calculator-self.ico" title="Indicador acumulativo" />
           <?php } ?>
            <?php if ($formulated) {?>
               <img class="img-rounded" src="../img/calculator-add.ico" title="Calculado por el sistema" />
           <?php } ?>
            <img class="img-rounded" src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>"/><strong class="strong-title"><?=$proceso?></strong>
            <strong class="strong-title">periodo: </strong><?="{$row_indi['inicio']}-{$row_indi['fin']}" ?>
            <?php if (!is_null($peso)) { ?>
               <strong class="strong-title">Ponderación: <span class="peso"> <?= $Tpeso_inv_array[$peso]?></span></strong>
           <?php } ?>            
         </div>
     </div>

 </li>

<?php
    }

    $obj_peso->SetYearMonth($year, $month); 
}
?>