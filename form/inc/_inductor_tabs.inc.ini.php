<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/02/15
 * Time: 19:17
 */

$j_objt= 0;
$i_objt= 0;
$cant_objt= 0;
$t_cant_objt= 0;
?>

<input type="hidden" name="cant_objt" id="cant_objt" value="0" />
<input type="hidden" name="t_cant_objt" id="t_cant_objt" value="0" />

<script language="javascript">
    document.getElementById("cant_objt").value= 0;
    document.getElementById("t_cant_objt").value= 0;

    function set_cant_objt(id) {
        var nvalue= parseInt(document.getElementById('cant_objt').value);

        if(parseInt(document.getElementById('select_objt'+id).value) > 0 && parseInt(document.getElementById('init_objt'+id).value) == 0) 
            ++nvalue;
        if(parseInt(document.getElementById('select_objt'+id).value) == 0 && parseInt(document.getElementById('init_objt'+id).value) > 0) 
            --nvalue;

        document.getElementById('cant_objt').value= nvalue;
    }
</script>

<div id="div-inductores" class="info-div" style="display:block;">

    <table width="780" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="5" class="td_title"><?php echo $title_obj?></td>
        </tr>
        <tr>
            <td width="30" class="td_title">No.</td>
            <td width="120" class="td_title">Ponderación</td>
            <td class="td_title">Objetivos de Trabajo</td>
        </tr>
    </table>

    <div class="info-panel">



