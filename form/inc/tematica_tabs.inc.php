
<div id="div-ajax-panel" class="ajax-panel">
    <div id="div-ajax">
        <script type="text/javascript" language="javascript">array_usuarios= Array();</script>

      <table width="600px" cellspacing=4>
            <tr>
                <td>
                    <label>Número:</label>
                    <input type="text" id="numero_matter" name="numero_matter" class="texta smartspinner" style="width: 60px;" value="" />
                </td>

             <?php if($ifaccords) { ?>
                <td valign="bottom" style="vertical-align: text-bottom" width="220px">
                    <label>Fecha de cumplimiento: </label>
                    <input id="fecha_matter" name="fecha_matter" class="texta" style="width:60px;" readonly="readonly">&nbsp;<img src="../img/cal.gif" onclick="javascript:NewCssCal('fecha_matter','ddMMyyyy')" style="cursor:pointer"  alt="Click aqui para seleccionar la fecha"/>
                </td>
            <?php } ?>

                <td valign="bottom" style="vertical-align: text-bottom">
                    <label>Hora:</label>
                    <select id="hora_matter" name="hora_matter" class="texta" style="width:45px;">
                        <?php
                        for($i= 11; $i >= 0; --$i) {
                            if($i == 11) $h= 12;
                        ?>
                            <option value="<?=str_pad($h, 2, "0", STR_PAD_LEFT); ?>"><?=str_pad($h, 2, "0", STR_PAD_LEFT); ?></option>
                            <?php
                            $h= 12 - $i;
                        }
                        ?>
                    </select>
                    :
                    <select id="minute_matter" name="minute_matter" class="texta" style="width:45px;">
                        <?php for($i= 0; $i <= 59; $i+=5) { ?>
                        <option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?=str_pad($i, 2, "0", STR_PAD_LEFT); ?></option>
                        <?php } ?>
                    </select>

                    <select id="am_matter" name="am_matter" class="texta" style="width:45px;">
                        <option value="AM">AM</option>
                        <option value="M">M</option>
                        <option value="PM">PM</option>
                    </select>
            </td>
        </tr>

        <tr>
          <td valign="top" colspan="<?=$ifaccords ? 3 : 2?>">
              <label>Responsable:</label>

            <?php
            $obj_user= new Tusuario($clink);
            if($badger->freeassign) $obj_user->set_use_copy_tusuarios(false);
            else $obj_user->set_use_copy_tusuarios(true);

            $obj_user->SetIdProceso(null);
            $obj_user->set_user_date_ref($fecha_inicio);
            $result_user= $obj_user->listar();

            while($row_user= $clink->fetch_array($result_user)) {
            ?>

              <script type="text/javascript" language="javascript">
                array_usuarios[<?=$row_user['_id'] ?>]= "<?=addslashes($row_user['nombre']).'<br />'.addslashes($row_user['cargo']) ?>";
                </script>
            <?php } ?>

              <select name="usuario_matter" id="usuario_matter" class="texta" style="width:580px;">
                  <option value="0">selecciona...</option>

                <?php
                $clink->data_seek($result_user);
                while($row_user= $clink->fetch_array($result_user)) {
                ?>
                  <option value="<?=$row_user['_id'] ?>"><?=addslashes($row_user['nombre']).', '.addslashes($row_user['cargo']) ?></option>
                  <?php  }  ?>
              </select>
          </td>
          </tr>

        <tr>
          <td colspan="<?=$ifaccords ? 3 : 2?>"><label><?=$title_th?>:</label>
          <textarea name="observacion_matter" id="observacion_matter" class="texta" style="width:570px;" rows=7></textarea>
          </td>
        </tr>

          <tr>
            <td align="right" style="padding: 10px" colspan="<?=$ifaccords ? 2 : 1?>">
            <button onclick="add_matter()" title="Agregar nueva <?=$ifaccords ? "acuerdo" : "tematica"?>">Aceptar</button>
            <button onclick="close_matter()" title="Cerrar">Cerrar</button>
            </td>
          </tr>

        </table>
    </div>
</div>

<?php
$if_jefe_meeting= $menu == "tablero" ? false : true;
if($_SESSION['nivel'] >= _SUPERUSUARIO) $if_jefe_meeting= true;
if($id_secretary == $_SESSION['id_usuario'] || $id_responsable == $_SESSION['id_usuario']) $if_jefe_meeting= true;
$hide= $menu == 'tablero' ? null : "hide";
?>

<!-- plan tematico -->
<div class="tabcontent <?=$hide?>" id="cont-7-1" style="border:none">
    <div style="text-align:left">
        <div id="idem-evento" style="padding: 8px">
          <label>Reunión: </label><?=$asunto?><br />
          <label>Fecha/Hora: </label><?=odbc2time_ampm($fecha_inicio)?>
          <hr style="width: 100%" />
        </div>

        <?php if($if_jefe_meeting) { ?>
        <button id="btn_agregar" type="button" onclick="form_matter(); set_form_spinit();" style="visibility:<?=$visible?>; margin-bottom:5px; cursor: pointer"><img src="../img/_add.png" border="0" style="vertical-align:bottom; margin-right:5px">Agregar</button>
        <?php } ?>
        <button id="btn-print" type="submit" onclick="submit_matter(1);" style="visibility:<?=$visible?>; margin-bottom:5px; cursor: pointer"><img src="../img/_print.png" border="0" style="vertical-align:bottom; margin-right:5px">Imprimir</button>

        <?php if($menu == 'tablero') {?>
          <button id="btn-print" type="submit" onclick="print_acta();" style="visibility:<?=$visible?>; margin-bottom:5px; cursor: pointer">
              <img src="../img/_print.png" border="0" style="vertical-align:bottom; margin-right:5px">
              <img src="../img/acta.png" border="0" style="vertical-align:bottom; margin-right:5px;">
              Imprimir Acta
          </button>
        <?php } ?>
    </div>

    <?php
    $i= 0;
    while($row= $clink->fetch_array($result)) {
        ++$i;
    ?>
        <input type="hidden" id="tab_matter_<?=$i?>" name="tab_matter_<?=$i?>" value="1">
        <input type="hidden" id="id_matter_<?=$i?>" name="id_matter_<?=$i?>" value="<?=$row['id']?>">

    <?php if($row['ifaccords']) { ?><input type="hidden" id="tab_accords_<?=$i?>" name="tab_accords_<?=$i?>" value="1">
    <?php } } ?>

	<div id="tableContainer" class="tableContainer">
        <table id="tablewitdhrollover" style="border-collapse: separate;" class="dynamicTable scrollTable" scrollingtableheight="280px" cellspacing="0">
            <thead class="fixedHeader">
            <tr>
                <th>No.</th>
                <th><?=$title_th?></th>
                <th>Fecha/Hora</th>
                <th>Responsable</th>
            </tr>
             </thead>
    
            <tbody  id="tbDetalle" class="scrollContent">
                <?php
                $array_class= array('');
                $max_numero_accords= $numero;
                $clink->data_seek($result);
    
                $i= 0;
                $k= 0;
                while($row= $clink->fetch_array($result)) {
                    ++$i;
                    ++$k;
                    $numero= !empty($row['numero']) ? $row['numero'] : $k;
                    if($numero > $max_numero_accords) $max_numero_accords= $numero;
                ?>
                <tr id="rowDetalle_<?=$i?>">
                    <td id="tdDetalle_1_<?=$i; ?>" valign="top"><?=$numero?></td>
    
                    <td id="tdDetalle_2_<?=$i?>">
                        <?php if($if_jefe_meeting) { ?>
                            <?php if($row['id_proceso'] == $id_proceso) {?>
                            <img src="../img/_drop.png" alt="Eliminar" title="Eliminar" onclick="del_matter(<?=$i?>);" style="cursor:pointer; visibility: <?=$visible?>" /> &nbsp
                            <?php } ?>

                            <img src="../img/_edit.png" alt="Editar" title="Editar" onclick="edit_matter(<?=$i?>);" style="cursor:pointer; visibility: <?=$visible?>" />
    
                            <?php if($ifaccords) { ?>
                            &nbsp&nbsp&nbsp<img src="../img/user_edit.png" title="registrar situación o cumplimiento" width="16px" height="16px" style="cursor: pointer" onclick="edit_accords(<?=$i?>,0);" />
                            <?php } ?>
                            <?php if(!empty($row['id']) && !$ifaccords) {?>
                            &nbsp&nbsp&nbsp<img src="../img/debate.png" title="describir las intervenciones en el debate de la temática" width="16px" height="16px" style="cursor: pointer" onclick="add_debate(<?=$i?>);" />
                            <?php } ?>
                        <?php } ?>
    
                        <br /><?=$row['observacion']?>
    
                        <?php if($ifaccords) { ?>
                        <br /><label class="alarm <?=$eventos_cump_class[$row['cumplimiento']]?>" id="cumplimiento_text_<?=$i?>"><?=$eventos_cump[$row['cumplimiento']]?></label>
                        <?php } ?>
    
                        <input type="hidden" id="matter_<?=$i?>" name="matter_<?=$i?>" value="<?=$row['observacion']?>"/>
                        <input type="hidden" id="numero_matter_<?=$i?>" name="numero_matter_<?=$i?>" value="<?=$numero?>"/>
    
                        <?php if($ifaccords) { ?>
                        <input type="hidden" id="cumplimiento_<?=$i?>" name="cumplimiento_<?=$i?>" value="<?=$row['cumplimiento']?>" />
                        <input type="hidden" id="time_accords_<?=$i?>" name="time_accords_<?=$i?>" value="<?=odbc2time_ampm($row['_fecha_inicio_plan']) ?>" />
                        <input type="hidden" id="id_responsable_eval_<?=$i?>" name="id_responsable_eval_<?=$i?>" value="<?=$row['id_responsable_eval']?>" />
                        <input type="hidden" id="observacion_accords_<?=$i?>" name="observacion_accords_<?=$i?>" value="<?=$row['evaluacion']?>" />
                        <?php } ?>
    
                    </td>
    
                    <td id="tdDetalle_3_<?=$i?>" valign="top">
                        <input type="hidden" id="time_matter_<?=$i?>" name="time_matter_<?=$i?>" value="<?=odbc2time_ampm($row['_fecha_inicio_plan'], null, true)?>" />
                        <?=$ifaccords ? odbc2time_ampm($row['fecha_inicio_plan']) : odbc2ampm($row['fecha_inicio_plan'])?>
                    </td>
    
                    <td id="tdDetalle_4_<?=$i?>">
                        <?php
                        $mail= $obj_user->GetEmail($row['id_responsable']);
                        echo $mail['nombre'].'<br />'.$mail['cargo'];
                        ?>
                        <input type="hidden" id="id_usuario_matter_<?=$i?>" name="id_usuario_matter_<?=$i?>" value="<?=$row['id_responsable'] ?>" />
                    </td>

                </tr>
                <?php } ?>
            </tbody>
        </table>
    
        <input type="hidden" id="cant_matter" name="cant_matter" value="<?=$i?>">
        <script language="javascript">max_numero_accords=<?=$max_numero_accords?>;</script>
    
        <div style="visibility:hidden"><a title="Dynamic Table - A javascript table sort widget." href="http://127.0.0.1"><img alt="Quick and easy table sorting powered by Dynamic Table" src="http://127.0.0.1/links/linkImage5.gif" border="0"></a></div>
    </div>     
</div>


<?php
$result= null;
$array_accords= null;
if($menu == 'tablero' && $ifaccords) {
    $obj_event= new Tevento($clink);
    $obj_event->SetIdEvento($id_evento);
    $obj_event->SetIdProceso($id_proceso);
    $array_accords= $obj_event->getPrevAccords();
}
?>

<?php if($menu == 'tablero' && $ifaccords) { ?>
    <!-- acuedos para revizar Revision de Acuerdos anteriores -->
    <div class="tabcontent hide" id="cont-8-1" style="border:none">
        <div style="text-align:left; padding-top: 10px;">
            <button id="btn-print" type="submit" onclick="submit_matter(2);" style="visibility:<?=$visible?>; margin-bottom:5px; cursor: pointer"><img src="../img/_print.png" border="0" style="vertical-align:bottom; margin-right:5px">Imprimir</button>

            <?php if($menu == 'tablero') {?>
              <button id="btn-print" type="submit" onclick="print_acta();" style="visibility:<?=$visible?>; margin-bottom:5px; cursor: pointer">
                  <img src="../img/_print.png" border="0" style="vertical-align:bottom; margin-right:5px">
                  <img src="../img/debate.png" border="0" style="vertical-align:bottom; margin-right:5px;">
                  Imprimir Acta
              </button>
            <?php } ?>
        </div>

        <?php
        $i= 0;
        foreach($array_accords as $row) {
            ++$i;
        ?>
            <input type="hidden" id="id_matter_prev_<?=$i?>" name="id_matter_prev_<?=$i?>" value="<?=$row['id']?>">
            <input type="hidden" id="tab_accords_prev_<?=$i?>" name="tab_accords_prev_<?=$i?>" value="<?=$row['cumplimiento']?>">
        <?php } ?>

        <div id="tableContainer" class="tableContainer">
            <table id="tablewitdhrollover_prev" style="border-collapse: separate;" class="dynamicTable scrollTable" scrollingtableheight="280px" cellspacing="0">
                <thead class="fixedHeader">
                <tr>
                    <th>No.</th>
                    <th>Acuedo</th>
                    <th>Cumplimiento</th>
                    <th>Responsable</th>
                   </tr>
                 </thead>

                <tbody  id="tbDetalle_prev" class="scrollContent">
                    <?php
                    $array_class= array('');
                    reset($array_accords);

                    $i= 0;
                    foreach($array_accords as $row) {
                        ++$i;
                        $numero= !empty($row['numero']) ? $row['numero'] : $i;
                    ?>
                    <tr id="rowDetalle_prev_<?=$i?>">
                        <td id="tdDetalle_prev_1_<?=$i; ?>" valign="top"><?=$numero?></td>

                        <td id="tdDetalle_prev_2_<?=$i?>">
                            <span style="font-style: oblique; text-decoration: underline;">
                             <?php
                             $obj_event->Set($row['id_evento']);
                             echo $obj_event->GetNombre();
                             // echo " No.".$obj_event->GetNumero();
                             ?>
                             </span>
                             <?php
                             echo "   ".$meses_array[date('n', strtotime($obj_event->GetFechaInicioPlan()))];
                             echo ", ".date('j', strtotime($obj_event->GetFechaInicioPlan()));
                             ?>
                            <br />
                            <?php if($if_jefe_meeting) { ?>
                                &nbsp<img src="../img/user_edit.png" title="registrar situación o cumplimiento" width="16px" height="16px" style="cursor: pointer" onclick="edit_accords(<?=$i?>,1);" />
                            <?php } ?>

                            <br /><?=$row['observacion']?>
                            <br /><label class="alarm <?=$eventos_cump_class[$row['cumplimiento']]?>" id="cumplimiento_text_prev_<?=$i?>"><?=$eventos_cump[$row['cumplimiento']]?></label>

                            <input type="hidden" id="numero_matter_prev_<?=$i?>" name="numero_matter_prev_<?=$i?>" value="<?=$numero?>"/>

                            <input type="hidden" id="cumplimiento_prev_<?=$i?>" name="cumplimiento_prev_<?=$i?>" value="<?=$row['cumplimiento']?>" />
                            <input type="hidden" id="time_accords_prev_<?=$i?>" name="time_accords_prev_<?=$i?>" value="<?=odbc2time_ampm($row['fecha_inicio'])?>" />
                            <input type="hidden" id="id_responsable_eval_prev_<?=$i?>" name="id_responsable_eval_prev_<?=$i?>" value="<?=$row['id_responsable_eval']?>" />
                            <input type="hidden" id="observacion_accords_prev_<?=$i?>" name="observacion_accords_prev_<?=$i?>" value="<?=$row['evaluacion']?>" />
                        </td>

                        <td id="tdDetalle_prev_3_<?=$i?>" valign="top">
                            <input type="hidden" id="time_matter_prev_<?=$i?>" name="time_matter_prev_<?=$i?>" value="<?=odbc2time_ampm($row['fecha_inicio'])?>">
                            <?=odbc2time_ampm($obj_event->GetFechaInicioPlan())?>
                        </td>

                        <td id="tdDetalle_prev_4_<?=$i?>">
                            <?php
                            $mail= $obj_user->GetEmail($row['id_responsable']);
                            echo $mail['nombre'].'<br />'.$mail['cargo'];
                            ?>
                            <input type="hidden" id="id_usuario_matter_prev_<?=$i?>" name="id_usuario_matter_prev_<?=$i?>" value="<?=$row['id_responsable'] ?>" />
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <input type="hidden" id="cant_matter_prev" name="cant_matter_prev" value="<?=$i?>">

            <div style="visibility:hidden"><a title="Dynamic Table - A javascript table sort widget." href="http://127.0.0.1"><img alt="Quick and easy table sorting powered by Dynamic Table" src="http://127.0.0.1/links/linkImage5.gif" border="0"></a></div>
        </div>
    </div>
<?php } ?>