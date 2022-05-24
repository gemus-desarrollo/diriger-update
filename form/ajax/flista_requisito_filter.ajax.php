<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/config.inc.php";

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';
$componente= !empty($_GET['componente']) ? $_GET['componente'] : null;
$id_capitulo= !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : null;
$id_capitulo= !empty($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : null;

$year= $_GET['year'];
$inicio= $_GET['inicio'];
$fin= $_GET['fin'];
?>

<div id="div-panel-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-title" class="ajax-title win-drag col-11 m-0">FILTRADO</div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-panel-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-1">
                        Año:
                    </label>
                    <div class="col-2">
                        <select id= "_year" name="_year" class="form-control">
                            <option value="-1">Todos ...</option>
                            <?php for($iyear=$inicio; $iyear <= $fin; $iyear++) {?>
                                <option value="<?=$iyear?>" <?php if($year == $iyear) { ?>selected="selected"<?php } ?>><?=$iyear?></option>
                            <?php } ?>
                        </select>                          
                    </div>
                              
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Componente:
                    </label>
                    <div class=" col-6">
                        <select id="componente" name="componente" class="form-control" onchange="refresh_ajax_select('', 0, 0);">
                            <option value="0">... </option>
                            <?php for ($i = 1; $i < _MAX_COMPONENTES_CI; ++$i) { ?>
                            <option value="<?= $i ?>" <?php if ($i == $componente) echo "selected='selected'" ?>>
                                <?= $Tambiente_control_array[$i] ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Capítulo:
                    </label>
                    <div id="ajax-capitulo" class="col-10">
                        <select id="capitulo" name="capitulo" class="form-control">
                            <option value="0"> ... </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Epigrafe:
                    </label>
                    <div id="ajax-subcapitulo" class="col-10">
                        <select id="subcapitulo" name="subcapitulo" class="form-control">
                            <option value="0"> ... </option>
                        </select>
                    </div>
                </div>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action == 'update' || $action == 'add') { ?>
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <?php } ?>

                    <button class="btn btn-warning" type="reset" onclick="filtrar()">Filtrar</button>
                    <button class="btn btn-danger" type="button"
                        onclick="CloseWindow('div-panel-filter');">Cerrar</button>
                </div>
            </div>

        </div>
    </div><!-- panel-requisito -->