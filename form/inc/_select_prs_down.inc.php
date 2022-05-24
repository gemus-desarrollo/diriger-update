<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/02/15
 * Time: 11:36
 */

/**
 * @reject_connected: true: rechaze a todos los que no estan dentro de la intranet
 * @in_building: true: rechaza las unidades no subordinadas directamente pero si subordinadas a otras estrcutura fuera de la intranet.
 * @show_only_connected: mustra solo las que estan conectadas
 */

$id_list_prs= !is_null($id_list_prs) ? $id_list_prs : null;
$tipo_list_prs= !is_null($tipo_list_prs) ? $tipo_list_prs : null;
$order_list_prs= !empty($order_list_prs) ? $order_list_prs : 'eq_desc';

// rechaza todos los que estan conectado, o sea los que no estan en la intranet
$reject_connected= !is_null($reject_connected) ? $reject_connected : false;
// muestra solo los que estan conectados, estan fuera de la intranet
$show_only_connected= !is_null($show_only_connected) ? $show_only_connected : false;
//muestra los que estan en la intranet y los que no estan estan en la intranet pero tienen subordinacion directa
$in_building= !is_null($in_building) ? $in_building : false;
// muestran solo los que estan en la intranet
$only_additive_list_prs= !is_null($only_additive_list_prs) ? $only_additive_list_prs : null;
// muestras por debajo del corte solo si esta conectado, si no esta en la intranet
$break_exept_connected= !empty($break_exept_connected) ? $break_exept_connected : null;

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

if ($_SESSION['nivel'] >= _SUPERUSUARIO) 
    $array_procesos= $obj_prs->listar_in_order('eq_asc_desc', true);
else 
    $array_procesos= $obj_prs->get_procesos_down_cascade($_SESSION['id_usuario']);
?>


<?php 
$array_ids= array();
foreach ($array_procesos as $row) {
    if ($array_ids[$row['id']]) 
        continue;
    $array_ids[$row['id']]= $row['id'];
?>
    <input type="hidden" id="proceso_code_<?=$row['id']?>" name="proceso_code_<?=$row['id']?>" value="<?=$row['id_code'] ?>" />
    <input type="hidden" id="proceso_conectado_<?=$row['id']?>" name="proceso_conectado_<?=$row['id']?>" value="<?=$row['conectado']?>" />    
<?php } ?>

<?php reset($array_procesos); ?>

<select id="proceso" name="proceso" class="form-control input-sm" onchange="refreshp(1)">
    <option value="0" <?php if (empty($id_select_prs)) echo "selected='selected'";?>><?= $top_list_option ?></option>

    <?php
    $array_ids= array();
    foreach ($array_procesos as $row) {
        if ($array_ids[$row['id']]) 
            continue;
        $array_ids[$row['id']]= $row['id']; 
        
        if ($row['tipo'] < $_SESSION['entity_tipo']) 
            continue;
        if ($row['conectado'] == _LAN && ($restrict_prs && array_search($row['tipo'], $restrict_prs) !== false))
            continue;

        $_in_building= ($row['id'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($row['id']) : true;

        $img_conectdo= ($row['conectado'] != _NO_LOCAL && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src=\'../img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;
        $img_tipo= "<img src=\'../img/".img_process($row['tipo'])."\' title=\'".$Ttipo_proceso_array[$row['tipo']]."\' />" ;
        $tips_title= $row['nombre'];

        if ($show_only_connected && ($row['conectado'] == _NO_LOCAL && ($row['id'] != $_SESSION['local_proceso_id'] || !$_in_building))) 
            continue;
        if ($reject_connected && ($row['conectado'] != _NO_LOCAL && $row['id'] != $_SESSION['local_proceso_id'])) 
            continue;
        if ((!$reject_connected && $in_building) && !$_in_building) 
            continue;
        if ((!is_null($only_additive_list_prs) && $only_additive_list_prs) && ($row['conectado'] != _NO_LOCAL && $row['id'] != $_SESSION['local_proceso_id'])) 
            continue;
        if ((!is_null($break_exept_connected) && $break_exept_connected) && ($row['tipo'] > $break_exept_connected && $row['conectado'] == _NO_LOCAL)) 
            continue;

        if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
        $obj_prs_tmp= new Tproceso($clink);

        if (!empty($row['id_proceso'])) 
            $obj_prs_tmp->Set($row['id_proceso']);
        $proceso_sup= $img_tipo."&nbsp;".$img_conectdo."<br />";
        $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$row['tipo']].'<br />';
        if (!empty($row['id_proceso'])) 
            $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs_tmp= $obj_prs_tmp->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs_tmp->GetTipo()]."</em>";
        $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$row['conectado']];
        $proceso= $row['nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['tipo']]."</span>";
    ?>
        <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_select_prs) echo "selected='selected'"; ?>><?=$proceso?></option>
    <?php } ?>
</select>

<?php unset($obj_prs); ?>