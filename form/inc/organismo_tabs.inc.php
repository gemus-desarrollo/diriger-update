<?php
$id = $id_evento;

$obj_org= new Torganismo($clink);
$obj_org->SetYear($year);
$obj_org->SetIdEvento($id);

$array_organismos= $obj_org->listar(true, true);
$result_array_organismos= $obj_org->listar_organismos_by_evento();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-5">
            <legend>Instituciones</legend>
        </div>
        <div class="col-2"></div>
        <div class="col-5">
            <legend>Seleccionados</legend>
        </div>
    </div>

    <script type="text/javascript">
       $(document).ready(function() {       
           var data_org= [
            <?php
            $k= 0;
            $i = 0;
            $j = 0;
            
            foreach ($array_organismos as $row) {
                $value = $result_array_organismos[$row['id']] ? 1 : 0;
                ++$j;
                $name= textparse($row['nombre'], true);
                $colom= (int)$j > 1 ? "," : "";
             ?>
                <?=$colom?>["<?=$row['id']?>", "<?=$name?>", <?=$value?>, undefined, 0, '']  
            <?php } ?>        
           ];
           
           multiselect('multiselect-org', data_org, false);
       });
    </script>
    
    <div id="multiselect-org"></div>
    <span style="font-size: 0.9em"><strong>Cantidad:</strong><?=$k?></span>
 </div>
 
<input type="hidden" id="t_cant_multiselect-org" name="t_cant_multiselect-org" value=<?=$j?> />
<input type="hidden" id="cant_multiselect-org" name="cant_multiselect-org" value=<?=$i?> />
