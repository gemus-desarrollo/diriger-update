 <?php

    while ($row= $clink->fetch_array($result_ind)) {
        $prs= $array_procesos_entity[$row['id_proceso']];
        if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
            continue;
        if (empty($prs['id_entity']) && (!$row['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
            continue;
        if (empty($prs['id_entity']) && (!$row['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
            continue;

        $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
        $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;        
        
        ++$k_ind;

        $id_inductor= $row['_id'];

        $nombre= $row['nombre'];
        $descripcion= $if_top_inductor ? $row['descripcion'] : null;
        $inicio= $row['inicio'];
        $fin= $row['fin'];
        $peso= !$if_top_inductor ? $row['_peso'] : null;

        $obj_peso->init_calcular();
        $obj_peso->SetIdPolitica($id_politica);
        $obj_peso->SetIdObjetivo($id_objetivo);
        $obj_peso->SetIdPerspectiva($id_pespectiva);

        $obj_peso->SetYearMonth($year, $month);
        $value2= $obj_peso->calcular_inductor($row['id_inductor']);

        $array_register= $obj_peso->get_array_register();
        $obj_signal->get_month($_month, $_year);

        $obj_peso->init_calcular();
        $obj_peso->SetIdPolitica($id_politica);
        $obj_peso->SetIdObjetivo($id_objetivo);
        $obj_peso->SetIdPerspectiva($id_pespectiva);
        
        $obj_peso->SetDay(null);
        $obj_peso->SetYearMonth($_year, $_month);
        $value1= $obj_peso->calcular_inductor($row['id_inductor']);

        $id_user= $array_register['id_usuario'];
        $item_reg= $array_register['signal'];

        $responsable= null;
        $observacion= null;

        if ($id_user && $item_reg == 'IND') {
            $observacion= textparse($array_register['observacion'], true);

            $email_user= $obj_user->GetEmail($id_user);
            $responsable= $email_user['nombre'];
            if (!is_null($email_user['cargo'])) 
                $responsable.= ', '.textparse($email_user['cargo'], true);
            $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
        }
    ?>

        <input type="hidden" form="treeForm" id="id_ind_<?=$k_ind?>" value="<?=$id_inductor?>" />
        <input type="hidden" form="treeForm" id="observacion_ind_<?=$k_ind?>" value="<?=$observacion?>" />
        <input type="hidden" form="treeForm" id="registro_ind_<?=$k_ind?>" value="<?=$responsable?>" />
        <input type="hidden" form="treeForm" id="descripcion_ind_<?=$k_ind?>" value="<?=$nombre?>" />
        <input type="hidden" form="treeForm" id="if_entity_ind_<?=$k_ind?>" value="<?=$if_entity?>" />
        
        <input type="hidden" form="treeForm" id="page_inductor_<?=$k_ind?>" name="page_inductor_<?=$id_inductor?>" value="<?=$id_inductor?>" />

        <li id="ind_li_<?=$k_ind?>">
            <div class="ul_ind" onmouseover="this.className='ul_ind rover-ind'" onmouseout="this.className='ul_ind'">
                <div class="div_inner_ul_li" onclick="refresh_ind('ind_ul_<?=$k_ind?>')" >
                    
                    <div class="alarm-block">
                        <i id="img_ind_li_<?=$k_ind?>" class="fa fa-search-plus" title="expandir"></i>
                        
                        <?php
                        $obj_signal->get_alarm($value2);
                        $obj_signal->get_flecha($value2, $value1);    
                        ?>                
                    </div>
                    
                    <?php
                    $nombre= get_short_label($nombre);

                    if (isset($obj_prs)) unset($obj_prs);
                    $obj_prs= new Tproceso($clink);

                    $obj_prs->Set($row['id_proceso']);
                    $tipo_prs= $obj_prs->GetTipo();
                    $proceso= $obj_prs->GetNombre();
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                    $numero= !empty($row['numero']) ? $row['numero'] : $k_ind;
                    ?>
                </div>

                <div class="div_inner_ul_li" onclick="ShowContentItem('ind', <?=$k_ind?>, <?=$item_sup ? "'$item_sup'" : '0' ?>, <?=$id_item_sup ? $id_item_sup : '0'?>);">
                    <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>
                    <span class="flag">No.<?=$numero?></span>&nbsp;<?=$nombre?>

                    <br />
                    <img class="img-rounded" src="../img/<?=img_item_planning('ind')?>" title="<?=$item_planning_array['ind']?>" />
                    <img class="img-rounded" src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>"/><strong class="strong-title"><?=$proceso?></strong>
                    <strong class="strong-title">periodo: </strong><?= "{$row['inicio']}-{$row['fin']}" ?>
                    <?php if (!is_null($peso)) { ?>
                        <strong class="strong-title">Ponderación: <span class="peso"> <?=$Tpeso_inv_array[$peso]?></span></strong>
                    <?php } ?>
                </div>
            </div>


            <ul id="ind_ul_<?=$k_ind?>" style="display:none">
                <?php
                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $result_indi= $obj_peso->listar_indicadores_ref_inductor($row['id_inductor'], true);
                $_cant_indi= $clink->num_rows($result_indi);
                $_cant_indi= !empty($_cant_indi) ? $_cant_indi : 0;
                ?>

                <input type="hidden" form="treeForm" id="_cant_ind_ul_<?=$k_ind?>" value=<?=$_cant_indi?> />

                <?php 
                _tree_indicadores($result_indi, $k_indi, 'ind', $k_ind); 
                ?>
            </ul>
        </li>
    <?php }  ?>
           					