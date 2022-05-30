<?php 
$k= 0;
$i= 0; 
$j_indi= 0;
$i_indi= 0;
$cant_indi= 0;
$t_cant_indi= 0;

$obj_indi= new Tindicador($clink); 

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetTipo($_SESSION['entity_tipo']);
$result_prs= $obj_prs->listar_in_order('eq_asc_desc', false, _TIPO_DEPARTAMENTO, true, 'desc');

$obj_ind= null;  
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-5">
            <legend>Indicadores</legend>
        </div>
        <div class="col-2"></div>
        <div class="col-5" col-xs-offset-1>
            <legend>Seleccionados</legend>
        </div>
    </div>

    <script type="text/javascript">
    <?php
     foreach ($result_prs as $row_prs) {
        if ($row_prs['tipo'] < $_SESSION['entity_tipo']) 
            continue;

        if (isset($obj_indi)) unset($obj_indi);
        $obj_indi= new Tindicador($clink);
        
        $obj_indi->SetIdProceso($row_prs['id']);
        $obj_indi->SetYear($year);
        $result_ind= $obj_indi->listar();
        $cant= $obj_indi->GetCantidad();

        if ($cant == 0) 
            continue;   
        
        while ($row= $clink->fetch_array($result_ind)) {
            $value= !empty($array_indicadores[$row['_id']]) ? $array_indicadores[$row['_id']] : 0;
            if (is_array($value)) 
                $peso= empty($value['peso']) ? 0 : $value['peso'];
            else 
                $peso= $value;
            
            if ($create_select_input) {         
            ?>
        var _multiselect_inds_select<?=$row['_id']?> = "" +
        "<select id='multiselect-inds-select_<?=$row['_id']?>' name='multiselect-inds-select_<?=$row['_id']?>' class='multiselect form-control input-sm'>" +
        <?php for ($h= 0; $h < 8; ++$h) { ?> 
            "<option value='<?=$h?>' <?php if ($h == $peso) echo "selected='selected'"?>><?=$Tpeso_inv_array[$h]?></option>" +
        <?php } ?> 
        "</select>";
    <?php } else { ?>
    var _multiselect_inds_select<?=$row['_id']?> = null;
    <?php    
            }
    }   } 
    ?>
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        var data_indi = [
            <?php
            $k= 0; 
            $j= 0;
            $i= 0;
            
            $array_ids= array();
            reset($result_prs);
            foreach ($result_prs as $row_prs) {
                if ($row_prs['tipo'] < $_SESSION['entity_tipo']) 
                    continue;
                
                $_connect= is_null($row_prs['conectado']) ? 1 : $row_prs['conectado'];

                if ($row_prs['id'] != $_SESSION['local_proceso_id']) 
                    $_connect= ($_connect != 1) ? 0 : 1;
                else 
                    $_connect= 1;

                if (isset($obj_indi)) 
                    unset($obj_indi);
                $obj_indi= new Tindicador($clink);
                $obj_indi->SetIdProceso($row_prs['id']);
                $obj_indi->SetYear($year);
                $result_indi= $obj_indi->listar();
                $cant= $obj_indi->GetCantidad();

                if ($cant == 0) 
                    continue;
                
                ++$j;
                $colom= (int)$j > 1 ? "," : "";
                $name= $_connect == _LOCAL ? "<i class='fa fa-wifi'></i>" : "";
                $name.= textparse($row_prs['nombre']);
                ?>
            <?=$colom?>[0, "<?=$name?>", 0, 0, 0, '<?=color_proccess($row_prs['tipo'])?>']

            <?php
            while ($row= $clink->fetch_array($result_indi)) {
                if ($row['_id_proceso'] != $row_prs['id'])                    
                    continue;
                /*
                if ($row['_id_proceso'] != $_SESSION['id_entity']) {
                    if (!$obj_indi->test_if_in_proceso($_SESSION['id_entity'], $row['_id']))
                        continue;
                }
                */
                if ($array_ids[$row['_id']])
                    continue;
                $array_ids[$row['_id']]= 1;
                
                $value= !empty($array_indicadores[$row['_id']]) ? $array_indicadores[$row['_id']] : null;
                if (is_array($value)) 
                    $peso= !is_null($value['peso']) ? $value['peso'] : $value['peso'];
                else 
                    $peso= $value;
                
                if (!is_null($value)) 
                    ++$i;
                ++$j;
                ++$k;
                $colom= (int)$j > 1 ? "," : "";
                $obj_prs->Set($row['_id_proceso']);
                $proceso= $obj_prs->GetNombre().',  '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

                $img= $row['_id_proceso'] != $_SESSION['local_proceso_id'] && $obj_prs->GetConectado() != _NO_LOCAL ? 'transmit.ico' : 'process.ico';
                $name= textparse($row['_nombre']) ." ({$row['_inicio']}-{$row['_fin']})";
                $peso= !is_null($peso) ? $peso : "undefined";
                ?>
            <?=$colom?>['ind<?=$row['id']?>', "<img src='../img/<?=$img?>' border=0 /><?=$name?>",
                <?=$peso?>, _multiselect_inds_select<?=$row['_id']?>, 0, '']
            <?php } } ?>
        ];

        multiselect('multiselect-inds', data_indi, <?=$create_select_input ? "true" : "false"?>);
    });
    </script>

    <div id="multiselect-inds"></div>
</div>


<input type="hidden" id="t_cant_multiselect-inds" name="t_cant_multiselect-inds" value="<?= $k ?>" />
<input type="hidden" id="cant_multiselect-inds" name="cant_multiselect-inds" value="<?= $i ?>" />
<?php unset($obj_indi); ?>