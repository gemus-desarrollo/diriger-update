<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 // mostrar las unidades organizativas ubicadas laterar a la rama de jerarquia. Ejemplo grupos subordinados a direcciones
$show_dpto= !is_null($show_dpto) ? $show_dpto : false;

$id_list_prs= !is_null($id_list_prs) ? $id_list_prs : null;
$tipo_list_prs= !is_null($tipo_list_prs) ? $tipo_list_prs : null;
$order_list_prs= !empty($order_list_prs) ? $order_list_prs : 'eq_desc';

// arreglo de tipos de procesos que estan restingidos. no se muestran
$restric_prs= !is_null($restric_prs) ? $restric_prs : null;
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
// muestra solo los que estan en la entidad
$only_entity= !is_null($only_entity) ? $only_entity : false;

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y')); 
if (is_null($use_copy_tprocesos) || !isset($use_copy_tprocesos)) 
    $use_copy_tproceso= false;
$obj_prs->set_use_copy_tprocesos($use_copy_tprocesos);
!empty($id_list_prs) ? $obj_prs->SetIdProceso($id_list_prs) : $obj_prs->SetIdUsuario(null);
if (!empty($tipo_list_prs)) 
    $obj_prs->SetTipo($tipo_list_prs);

$_restrict_prs= min($restrict_prs);
$result_prs= $obj_prs->listar_in_order($order_list_prs, $show_dpto, $_restrict_prs);
if($show_dpto)
    $result_prs= $obj_prs->sort_array_procesos($result_prs);  
?>


<li class="navd-dropdown">
    <a class="dropdown-toggle" href="#navbarProcesos" data-toggle="collapse" aria-expanded="false">
        <i class="fa fa-industry"></i>Unidades Organizativas<b class="caret"></b>
    </a>
    <input type="hidden" id="proceso" name="proceso" value="<?=$id_select_prs?>" />  
    
    <ul class="navd-dropdown-menu" id="navbarProcesos">
        <li class="nav-item">
            <a href="#" class="<?php if ($id_select_prs == -1) echo "active"?>" onclick="_dropdown_prs(-1)" title="Todas las Unidades Organizativas">
                Todas las Unidades Organizativas ... 
            </a>
        </li>  
          
        <?php
        $top_list_option= "seleccione........";
        $id_list_prs= null;
        $order_list_prs= 'eq_asc_desc';
        $reject_connected= !is_null($reject_connected) ? $reject_connected : false;
        $id_select_prs= !is_null($id_select_prs) ? $id_select_prs : $id_proceso;
        $in_building= !is_null($in_building) ? $in_building : false;                             
        ?>  

        <?php
        foreach ($result_prs as $row) {
            if ($only_entity && ($row['id'] != $_SESSION['id_entity'] && $row['id_entity'] != $_SESSION['id_entity']))
                continue;
            if ($_SESSION['id_entity'] != $_SESSION['local_proceso_id']) {
                if ((empty($row['id_entity']) && $row['id'] != $_SESSION['id_entity']) or (!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity']))
                    continue;
            } else {
                if (!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity'])
                    continue;
            }

            if ($row['conectado'] == _LAN && (!empty($_restrict_prs) && array_search((int)$row['tipo'], $restrict_prs) !== false)) 
                  continue;
            // $_in_building= ($row['id'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($row['id']) : true;
            $img_conectdo= ($row['conectado'] != _LAN && $row['id'] != $_SESSION['local_proceso_id']) ? "<img  class=\'img-rounded icon\' src=\'"._SERVER_DIRIGER."img/transmit.ico\' alt=\'requiere transmisi??n de datos\' />" : null;
            $tips_title= $row['nombre'];

            if ($show_only_connected && ($row['conectado'] != _LAN && $row['id'] != $_SESSION['local_proceso_id'])) 
                continue;
            if ($reject_connected && ($row['conectado'] == _LAN && !$row['if_entity'])) 
                continue;
            /*
            if ((!$reject_connected && $in_building) && !$_in_building) 
                continue;
             */
            if ((($order_list_prs == 'asc' || $order_list_prs == 'eq_asc') && !empty($tipo_list_prs)) && !if_subordinado($tipo_list_prs, $row['tipo'])) 
                  continue;
            if ((($order_list_prs == 'desc' || $order_list_prs == 'eq_desc') && !empty($tipo_list_prs)) && if_subordinado($tipo_list_prs, $row['tipo'])) 
                  continue;
            if ($only_additive_list_prs && ($row['conectado'] != _LAN && $row['id'] != $_SESSION['local_proceso_id'])) 
                continue;
            if ($break_exept_connected && ($row['tipo'] > $break_exept_connected && $row['conectado'] != _LAN))
                continue;

            if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
            $obj_prs_tmp= new Tproceso($clink);

            if (!empty($row['id_proceso'])) 
                $obj_prs_tmp->Set($row['id_proceso']);
            $proceso_sup= $img_conectdo."<br />";
            $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$row['tipo']].'<br />';
            if (!empty($row['id_proceso'])) 
                $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs_tmp= $obj_prs_tmp->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs_tmp->GetTipo()]."</em>";
            $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$row['conectado']];
            $proceso= $row['nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['tipo']]."</span>";
            $proceso.= ", {$row['inicio']} - {$row['fin']}"
            ?>
        
            <li class="nav-item">
                <a href="#" class="<?php if ($id_select_prs == $row['id']) echo "active"?>" onclick="_dropdown_prs(<?=$row['id']?>)" onmouseover="Tip('<?=$proceso_sup?>')" onmouseout="UnTip()">
                    <img class="img-rounded icon" src='<?=_SERVER_DIRIGER?>img/<?=img_process($row['tipo'])?>' title='<?=$Ttipo_proceso_array[$row['tipo']]?>' />
                    <?= stripslashes($img_conectdo)?>
                    <?=$proceso?>
                </a>
            </li> 
        <?php } ?>
    </ul>
</li>
