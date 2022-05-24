<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */
?>

    <div class="d-flex d-flex-horizontal" style="margin-top:6px;">
        <div class="btn-group">
            <button class="btn btn-app btn-xs btn-<?=$calendar_type == 0 ? "success" : "grey"?>" onclick="set_calendar_type(0, 0)">
                <i class="fa fa-calendar"></i> MES
            </button>
            <button class="btn btn-app btn-xs btn-<?=$calendar_type == 1 ? "success" : "grey"?>" onclick="set_calendar_type(1, 0)">
                <i class="fa fa-calendar-check-o"></i> SEMANA
            </button>
            <button class="btn btn-app btn-xs btn-<?=$calendar_type == 2 ? "success" : "grey"?>" onclick="set_calendar_type(2, 0)">
                <i class="fa fa-calendar-times-o"></i> DÍA
            </button>
        </div>
        <div class="btn-group">
            <button class="btn btn-app btn-xs btn-default" onclick="set_calendar_type(<?=$calendar_type?>, -1)">
                <i class="fa fa-backward"></i> ANTERIOR
            </button>
            <button class="btn btn-app btn-xs btn-default" onclick="set_calendar_type(<?=$calendar_type?>, 1)">
                <i class="fa fa-forward"></i> SIGUIENTE
            </button>
        </div>
    </div>


