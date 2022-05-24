<?php
$id_evento= $_GET['id_evento'];
$signal= $_GET['signal'];
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

?>

<form id="fcopy" name="fcopy" action="#" method=post style="margin:4px; border:none; padding:4px;">
    <input type=hidden name=exect value=set />
    <input type=hidden name=id value=<?=$id_evento?> />

    <input type="hidden" id="day" name="day" value="<?=$day?>" />
    <input type="hidden" id="month" name="month" value="<?=$month?>" />
    <input type="hidden" id="year" name="year" value="<?=$year?>" />

    <input type=hidden name=menu value=fcopy />

    <div id="_submit" class="submit" align="center" style="width:100%; text-align:center; display:block">

    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
        ........
    </div>

</form>

<script language="javascript" type="text/javascript">
ejecutar('repro');
</script>