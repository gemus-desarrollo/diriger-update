<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */

$array_target= explode('||', urldecode($_GET['target']));
?>

 <select name="target" id="target" class="form-control">
     <option value=0>Seleccione ... </option>
     <?php foreach ($array_target as $row) { ?>
         <option value="<?= $row ?>"><?= $row ?></option>
     <?php } ?>
</select>

