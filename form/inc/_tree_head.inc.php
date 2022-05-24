<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 8/03/15
 * Time: 12:31
 */

?>

<?php require 'inc/_page_init.inc.php'; ?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<link rel="stylesheet" type="text/css" href="../css/general.css?version=">
<link rel="stylesheet" type="text/css" href="../css/table.css?version=">
<link rel="stylesheet" type="text/css" href="../css/custom.css?version=">

<link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
<script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

<script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
<script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

<link rel="stylesheet" type="text/css" href="../css/widget.css">
<script type="text/javascript" src="../js/widget.js"></script>

<script type="text/javascript" src="../js/windowcontent.js"></script>
<script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

<link rel="stylesheet" type="text/css" href="../css/resumen.css?" />
<script language="javascript" type="text/javascript" src="../js/resumen.js?version=">
</script>

<link rel="stylesheet" type="text/css" href="../css/tablero.css?version=" />

<script type="text/javascript" src="../js/form.js"></script>

<script language="javascript">
parent.app_menu_functions = true;

function _dropdown_prs(id) {
    $('#proceso').val(id);
    refreshp(0);
}

function _dropdown_year(year) {
    $('#year').val(year);
    refreshp(0);
}

function _dropdown_month(year) {
    $('#month').val(year);
    refreshp(0);
}

function show_politica_filter() {
    displayFloatingDiv('div-filter', false, 70, 0, 10, 10);
}

$(document).ready(function() {
    InitDragDrop();

    <?php if (!is_null($error)) { ?>
    alert("<?= str_replace("\n", " ", $error) ?>");
    <?php } ?>
});
</script>