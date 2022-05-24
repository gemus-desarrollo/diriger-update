<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 9/03/15
 * Time: 12:22
 */

$obj_inductor= new Tinductor($clink);
$obj_inductor->SetYear($year);
if (!empty($id_perspectiva)) $obj_inductor->SetIdPerspectiva($id_perspectiva);
$with_null_perspectiva= !empty($id_perspectiva) ? _PERSPECTIVA_NOT_NULL : _PERSPECTIVA_ALL;

$result_inductor= $obj_inductor->listar($with_null_perspectiva);
?>

<select name="inductor" id="inductor" class="texta" style="width:700px; height:30px;" onchange="javascript:refreshp(0)">
    <option value="0" style="min-height:30px;">Todos .....</option>
    <?php
    while ($row= $clink->fetch_array($result_inductor)) {
        $obj_prs->Set($row['id_proceso']);
        $nombre_prs= $obj_prs->GetNombre().',  '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
        ?>
        <option style="min-height:20px;" value="<?=$row['_id']?>" <?php if ($row['_id'] == $id_inductor) echo "selected='selected'"; ?>>No.<?php echo $row['_numero'].' '.$row['_nombre'].', '.$row['inicio'].'-'.$row['fin'].',  '.$nombre_prs ?></option>
    <?php } ?>
</select>