<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 4/2/15
 * Time: 4:16 a.m.
 */

$j_indi= 0;
$i_indi= 0;
$cant_indi= 0;
$t_cant_indi= 0;
?>

<input type="hidden" name="cant_indi" id="cant_indi" value="<?=$j_indi?>" />
<input type="hidden" name="t_cant_indi" id="t_cant_indi" value="<?= $i_indi?>" />

<script language="javascript">
    $('#cant_indi').val(0);
    $('#t_cant_indi').val(0);

    function set_cant_indi(id) {
        var nvalue= parseInt($('#cant_indi').val());

        if (parseInt($('#select_indi'+id).val()) > 0 && parseInt($('#init_indi'+id).val()) == 0) ++nvalue;
        if (parseInt($('#select_indi'+id).val()) == 0 && parseInt($('#init_indi'+id).val()) > 0) --nvalue;

        $('#cant_indi').val(nvalue);
    }
</script>