<?php
$array_permit_by_form= array("fauditoria", "findicador", "fnota", "fobjetivo_ci",
                                    "friesgo", "fusuario", "flista", "fusuario_prs.ajax");

$restrict_down_prs= !is_null($restrict_down_prs) ? $restrict_down_prs : false;
$flag_restrict_by_form= array_search($name_form, $array_permit_by_form) !== false ? false : true;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-5">
            <legend>Entidades/Unidades/Procesos</legend>
        </div>
        <div class="col-2"></div>
        <div class="col-5">
            <legend>Seleccionados</legend>
        </div>
    </div>

    <script type="text/javascript">
       $(document).ready(function() {
        <?php
        $k= 0;
        reset($result_prs_array);
        foreach ($result_prs_array as $row) {
            if (is_array($array_procesos_init) && !array_key_exists($row['id'], $array_procesos_init))
                continue;
            if (is_array($array_procesos_lista) && !array_key_exists($row['id'], $array_procesos_lista))
                continue;
            if (((!$config->show_group_dpto_plan && !$config->show_prs_plan) && $flag_restrict_by_form) && $row['tipo'] > _TIPO_GRUPO)
                continue;
            if ($row['tipo'] == _TIPO_DEPARTAMENTO && $signal != 'anual_plan_audit')
                continue;
            if ((!$config->show_prs_plan && $flag_restrict_by_form) && $row['tipo'] >= _TIPO_PROCESO_INTERNO) 
                continue;
            if (empty($row['id_entity']) && ($row['id'] != $_SESSION['id_entity'] && $row['id'] != $_SESSION['superior_entity_id']
                    && $row['id_proceso'] != $_SESSION['id_entity']))
                continue;
            if (!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity'])
                continue;
           if (!is_null($restrict_prs) && array_search((int)$row['tipo'], $restrict_prs) !== false) 
                continue;
           if ($id_restrict_prs == $row['id']) 
               continue;
           if ($restrict_up_prs && ($row['tipo'] < $_SESSION['entity_tipo'])) 
               continue;
           if (($restrict_down_prs && (int)$row['conectado'] != _LAN) 
                   && ($row['tipo'] >= $_SESSION['local_proceso_tipo'] && $row['id'] != $_SESSION['local_proceso_id'])) 
               continue;
           if (!is_null($badger) && (empty($badger->other_type_prs) && $row['id'] == $_SESSION['id_entity'])) 
               continue;
           if (!empty($id_prs_restrict) && (int)$row['id'] == $id_prs_restrict) 
               continue;  

           ++$k;
           if ($create_select_input) {
                if ((int)$row['tipo'] != _TIPO_PROCESO_INTERNO 
                    && ((int)$row['conectado'] == _LAN && ($row['id'] != $_SESSION['id_entity'] && $row['id'] != $_SESSION['superior_entity_id']))) 
                    continue;
                if (!empty($id_proceso) && (int)$row['id'] == $id_proceso) 
                    continue;  
                    
               $peso= $array_procesos[$row['id']]['peso'];
               if ((int)$row['tipo'] == _TIPO_PROCESO_INTERNO) {
           ?>    
                    var _multiselect_prs_select<?=$row['id']?>= ""+
                        "<select id='multiselect-prs-select_<?=$row['id']?>' name='multiselect-prs-select_<?=$row['id']?>' class='multiselect form-control input-sm'>"+
                        <?php for ($h= 0; $h < 8; ++$h) { ?>
                            "<option value='<?=$h?>' <?php if ($h == $peso) echo "selected='selected'"?>><?=$Tpeso_inv_array[$h]?></option>"+
                        <?php } ?>
                        "</select>";          
            <?php } else { ?>
                var _multiselect_prs_select<?=$row['id']?>= null;
            <?php    
                }   
            } else {
            ?>    
                var _multiselect_prs_select<?=$row['id']?>= null;
        <?php        
            }
        }    
        ?>      
       
           var data_prs= [
            <?php
            $i = 0;
            $j = 0;
            
            reset($result_prs_array);
            foreach ($result_prs_array as $row) {
                if (is_array($array_procesos_init) && !array_key_exists($row['id'], $array_procesos_init))
                    continue;            
                if (is_array($array_procesos_lista) && !array_key_exists($row['id'], $array_procesos_lista))
                    continue;                
                if (((!$config->show_group_dpto_plan && !$config->show_prs_plan) && $flag_restrict_by_form) && $row['tipo'] > _TIPO_GRUPO)
                    continue;
                if ($row['tipo'] == _TIPO_DEPARTAMENTO && $signal != 'anual_plan_audit')
                    continue;
                if ((!$config->show_prs_plan && $flag_restrict_by_form) && $row['tipo'] >= _TIPO_PROCESO_INTERNO) 
                    continue;                
                if (empty($row['id_entity']) && ($row['id'] != $_SESSION['id_entity'] && $row['id'] != $_SESSION['superior_entity_id']
                             && $row['id_proceso'] != $_SESSION['id_entity']))
                    continue;    
                if (!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity'])
                    continue;      
                if (!is_null($restrict_prs) && array_search((int)$row['tipo'], $restrict_prs) !== false) 
                     continue;
                if ($id_restrict_prs == $row['id']) 
                    continue;
                if ($restrict_up_prs && ($row['tipo'] < $_SESSION['entity_tipo'])) 
                    continue;
                if (($restrict_down_prs && (int)$row['conectado'] != _LAN) 
                        && ($row['tipo'] >= $_SESSION['local_proceso_tipo'] && $row['id'] != $_SESSION['local_proceso_id'])) 
                    continue;
                if (!is_null($badger) && (empty($badger->other_type_prs) && $row['id'] == $_SESSION['id_entity'])) 
                    continue;
                if (!empty($id_prs_restrict) && (int)$row['id'] == $id_prs_restrict) 
                    continue; 
                if ($create_select_input) {
                    if ((int)$row['tipo'] != _TIPO_PROCESO_INTERNO 
                        && ((int)$row['conectado'] == _LAN && ($row['id'] != $_SESSION['id_entity'] && $row['id'] != $_SESSION['superior_entity_id']))) 
                        continue;
                    if (!empty($id_proceso) && (int)$row['id'] == $id_proceso) 
                        continue;  
                }

                if ($create_select_input) {
                    if ($row['tipo'] == _TIPO_PROCESO_INTERNO) 
                        $value= !is_null($array_procesos[$row['id']]['peso']) ? $array_procesos[$row['id']]['peso'] : 'undefined';
                    else 
                        $value =!is_null($array_procesos[$row['id']]) ? 1 : 'undefined';
                }

                if (!$create_select_input)
                    $value = $array_procesos[$row['id']] ? 1 : 0;
  
                ++$j;
                if ((!$create_select_input && $value) || ($create_select_input && $value != 'undefined')) 
                    ++$i;
                $name= "<img class='img-rounded' src='../img/".img_process($row['tipo'])."' />".textparse($row['nombre'], true).", {$Ttipo_proceso_array[$row['tipo']]}, {$row['inicio']}-{$row['fin']}";
                $colom= (int)$j > 1 ? "," : "";
             ?>
                <?=$colom?>["<?=$row['id']?>", "<?=$name?>", <?=$value?>, _multiselect_prs_select<?=$row['id']?>, 0, '']  
            <?php } ?>        
           ];
           
           multiselect('multiselect-prs', data_prs, <?=$create_select_input ? "true" : "false"?>);
       });
    </script>
    
    <div id="multiselect-prs"></div>
    <span style="font-size: 0.9em"><strong>Cantidad:</strong><?=$k?></span>
 </div>
 
<input type="hidden" id="t_cant_multiselect-prs" name="t_cant_multiselect-prs" value=<?=$j?> />
<input type="hidden" id="cant_multiselect-prs" name="cant_multiselect-prs" value=<?=$i?> />
