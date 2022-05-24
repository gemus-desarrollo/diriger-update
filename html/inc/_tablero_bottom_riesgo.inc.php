<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/21/15
 * Time: 2:28 p.m.
 */

?>

    <!-- info-panel -->
    <div id="info-panel-plan" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title col-11 win-drag">
                    Estado del Plan de Prevención
                </div>
                <div class="col-1 close pull-right">
                    <div class="close">
                        <a href="#" onclick="CloseWindow('info-panel-plan')">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body info-panel ">
            <div class="list-group">
                <a href="#" class="list-group-item">
                    <legend>Aprobación</legend>

                    <p>
                        <strong>Aprueba: </strong>
                        <?php
                        if (!is_null($array_aprb))
                            echo textparse ($array_aprb['nombre']).', '.textparse($array_aprb['cargo']);
                        ?>
                    </p>

                    <p><strong>En fecha: </strong><?=odbc2time_ampm($date_aprb)?></p>
                    <p><strong>Observación: </strong></p>

                    <?= textparse(purge_html($objetivos, false)) ?>
                </a>
            </div>
        </div>
    </div>  <!-- info-panel -->

