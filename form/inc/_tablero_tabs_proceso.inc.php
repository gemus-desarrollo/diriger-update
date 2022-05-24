<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/21/15
 * Time: 7:15 p.m.
 */

++$j;

if (isset($obj_prs)) unset($obj_prs);
$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

if (empty($id_proceso)) 
    $id_proceso= $array['id'];
if ($id_proceso == $array['id']) 
    $pos= $j;

$_in_building= ($array['id'] != $_SESSION['local_proceso_id']) ? $obj_prs->get_if_in_building($array['id']) : true;

$img_conectdo= ($array['conectado'] != _NO_LOCAL && ($array['id'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src=\'../img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;
$img_tipo= "<img src=\'"._SERVER_DIRIGER."img/".img_process($array['tipo'])."\' title=\'".$Ttipo_proceso_array[$array['tipo']]."\' />" ;
$tips_title= $array['nombre'];

$tmp_str= str_replace("\n","",nl2br($array['descripcion']));
$tmp_str= str_replace("\r","",$tmp_str);

$proceso_sup= null;
if (!empty($array['id_proceso'])) {
    $obj_prs->Set($array['id_proceso']);
    $proceso_sup= $img_tipo."&nbsp;".$img_conectdo."<br />";
    $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$array['tipo']].'<br />';
    $proceso_sup.= "<p>$tmp_str</p>";
    $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs= $obj_prs->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs->GetTipo()]."</em>";
    $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$array['conectado']];
}

$function= !is_null($function) ? $function : "_dropdown_prs";
?>

<li class="nav-item <?php if ($array['id'] == $id_proceso) echo "active" ?>"  onmouseover="Tip('<?=$proceso_sup?>')" onmouseout="UnTip()">
    <a href="#" onclick="<?=$function?>(<?=$array['id']?>)" onmouseover="Tip('<?=$proceso_sup?>')" onmouseout="UnTip()">
        <?php if ($array['conectado'] != _LAN && $array['id'] != $_SESSION['local_proceso_id']) { ?>
        <i class="fa fa-wifi"></i>
        <?php } ?>
        <img class="img-rounded ico" src="<?=_SERVER_DIRIGER?>img/<?=img_process($array['tipo'])?>" />
        <?=$array['nombre']?>
    </a>
</li>