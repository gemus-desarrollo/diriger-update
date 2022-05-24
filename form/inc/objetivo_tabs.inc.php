            <div class="info-panel">
            	
                <?php
                $obj_objetivo= new Tobjetivo($clink);
                if (!empty($id_proceso)) 
                    $obj_objetivo->SetIdProceso($id_proceso);
                $obj_objetivo->SetYear($year);
                if (!empty($inicio)) 
                    $obj_objetivo->SetInicio($inicio);
                if (!empty($fin)) 
                    $obj_objetivo->Setfin($fin);

                $result= $obj_objetivo->listar();
                ?>
                	
                <table width="100%">
                 <?php   
                    $i= 0; 
                    $j= 0;		
                    while ($row= @$clink->fetch_array($result)) {
                        $class= ($i%2 == 0) ? 'roweven' : '';

                        $_inicio= $row['inicio'];
                        $_fin= $row['fin'];
		
                        $obj_prs->Set($row['id_proceso']);
                        if ($tipo < $obj_prs->GetTipo()) 
                            continue; // solo los objetivos de la instancias superiores
                        $proceso= $obj_prs->GetNombre().'  '. $Ttipo_proceso_array[ $obj_prs->GetTipo()];
						
                        $value= $array_pesos[$row['_id']];
                        if (empty($value)) 
                            $value= 0;

                        ++$i;
                        if ($value > 0) 
                            ++$j;
				?> 

                 <tr class="row <?php echo $class ?>" id="id_<?= $i?>" onMouseOver="rowOver(this)" onMouseOut="rowOut(this)">
                 	
                    <td width="30px"><?=$i ?></td>
                    
                    <td width="120px">
                    
                    <select name="select_obj<?= $row['_id']?>" id="select_obj<?= $row['_id']?>" class="texta weight" onchange="set_cant_obj(<?php echo $row['_id']?>)" >
                        <?php for ($k= 0; $k < 8; ++$k) { ?>
                            <option value="<?= $k ?>" <?php if ($k == $value) echo "selected='selected'"?> ><?= $Tpeso_inv_array[$k]?></option>
                        <?php } ?>
               	    </select>
                                   
                    <input type="hidden" name="init_obj<?= $row['_id'] ?>" id="init_obj<?php echo $row['_id']?>" value="<?= $value ?>" />
                    <input type="hidden" name="id_obj_code<?= $row['_id'] ?>" id="id_code<?= $row['_id'] ?>" value="<?= $row['_id_code'] ?>" />

                    </td>
                    <td class="row-info-panel"><?php echo $row['_nombre'].'  <br /><em>('.$_inicio.' - '.$_fin.")  $proceso </em>" ?></td>
                 </tr>
                 <?php } ?>
                 
                </table>
			
                <?php if ($i == 0) { ?> 
                <script language="javascript">box_alarm("En el sistema no existen Objetivos Estratégicos Generales definidos para el escenario en el que está trabajando.");</script>
                <?php } ?>

            
            </div>
       
        <input type="hidden" name="cant_obj" id="cant_obj" value="<?php echo $j?>" />
        <input type="hidden" name="t_cant_obj" id="t_cant_obj" value="<?php echo $i?>" /> 
        
        
  <script language="javascript">
    function set_cant_obj(id) {
        var nvalue= parseInt(document.getElementById('cant_obj').value);

        if (parseInt(document.getElementById('select_obj'+id).value) > 0 && parseInt(document.getElementById('init_obj'+id).value) == 0) 
            ++nvalue;
        if (parseInt(document.getElementById('select_obj'+id).value) == 0 && parseInt(document.getElementById('init_obj'+id).value) > 0) 
            --nvalue;

        document.getElementById('cant_obj').value= nvalue;
	}
  </script>        