<br/>
<input type="hidden" id="diff_days" value="" />
<input type="hidden" id="_periodic" name="_periodic" value="0" />

<?php if (!$chk_cant_day_block) { ?>
    <input type="hidden" id="periodic0" name="periodic0" value="0" />
    <input type="hidden" id="periodic1" name="periodic1" value="1" />
<?php } ?>

<div class="container">
    <div class="form-horizontal">

        <div class="form-group row">
            <div class="row col-12">
                <div class="form-group row col-lg-6">
                    <label class="col-form-label col-md-2">Inicio:</label>
                    <div class=" col-md-10">
                        <?php
                        $date = date('d/m/Y', strtotime($fecha_inicio));
                        $time = date('h:i A', strtotime($fecha_inicio));
                        ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class='input-group date' id='div_fecha_inicio' data-date-language="es">
                                    <input type='text' id="fecha_inicio" name="fecha_inicio" class="form-control"
                                           readonly value="<?= $date ?>"/>
                                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group bootstrap-timepicker timepicker" id='div_hora_inicio'>
                                    <input type="text" id="hora_inicio" name="hora_inicio"
                                           class="form-control input-small" readonly value="<?= $time ?>"/>
                                    <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row col-lg-6">
                    <label class="col-form-label col-md-2">Fin:</label>
                    <div class="col-md-10">
                        <?php
                        $date = date('d/m/Y', strtotime($fecha_fin));
                        $time = date('h:i A', strtotime($fecha_fin));
                        ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class='input-group date' id='div_fecha_fin' data-date-language="es">
                                    <input type='text' id="fecha_fin" name="fecha_fin" class="form-control" readonly
                                           value="<?= $date ?>"/>
                                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group bootstrap-timepicker timepicker" id='div_hora_fin'>
                                    <input type="text" id="hora_fin" name="hora_fin" class="form-control input-small"
                                           readonly value="<?= $time ?>"/>
                                    <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($chk_cant_day_block) { ?>

            <div class="checkbox" onclick="set_periodic()">
                <label class="checkbox">
                    <input type="radio" name="periodic" id="periodic0"
                           value="0" <?php if (empty($periodic)) echo "checked='checked'" ?> />
                    Es una sola auditoria o acción de control que se realiza por intervalos dentro del periodo
                    seleccionado, en las fechas que se escojan.
                </label>
            </div>

            <div class="checkbox" onclick="set_periodic()">
                <label class="checkbox" onclick="set_periodic()">
                    <input type="radio" name="periodic" id="periodic1"
                           value="1" <?php if ($periodic) echo "checked='checked'" ?> />
                    Se repetirá varias veces en el periodo. Seleccione esta opción cuando está programando un 
                    conjunto de auditorías o controles del mismo tipo, con el mismo alcance, los mismos objetivos, 
                    y con los mismos involucrados.
                </label>
            </div>

            <hr></hr>
        <?php } ?>


        <div id="div-frecuency" class="form-group row">
            <fieldset class="fieldset row col-md-5">
                <legend>
                    Frecuencia:
                </legend>
                <div class="checkbox row col-12">
                    <label class="form-check-label">
                        <input type="radio" name="periodicidad" id="periodicidad0"
                               value=0 <?php if (empty($periodicidad)) echo "checked='checked'" ?> />
                        Ocurre en un día
                    </label>
                </div>
                <div class="checkbox row col-12 mt-1">
                    <label class="form-check-label">
                        <input type="radio" name="periodicidad" id="periodicidad1"
                               value=1 <?php if ($periodicidad == 1) echo "checked='checked'" ?> />
                        Diaria
                    </label>
                </div>
                <div class="checkbox row col-12 mt-1">
                    <label class="form-check-label">
                        <input type="radio" name="periodicidad" id="periodicidad2"
                               value=2 <?php if ($periodicidad == 2) echo "checked='checked'" ?> />
                        Semanal
                    </label>
                </div>
                <div class="checkbox row col-12 mt-1">
                    <label class="form-check-label">
                        <input type="radio" name="periodicidad" id="periodicidad3"
                               value=3 <?php if ($periodicidad == 3) echo "checked='checked'" ?> />
                        Mensual
                    </label>
                </div>
                <div class="checkbox row col-12 mt-1">
                    <label class="form-check-label">
                        <input type="radio" name="periodicidad" id="periodicidad4"
                               value=4 <?php if ($periodicidad == 4) echo "checked='checked'" ?> />
                        Repetición no periodica
                    </label>
                </div>
            </fieldset>

            <div class="form-group row col-7">
                <div id="div-semanal" class="form-group row" style="display:none">
                    <fieldset class="fieldset row">
                        <label class="col-form-label col-4">Repetir todos los: </label>

                        <div class="col-4">
                            <label class="checkbox text col-12">
                                <input type="checkbox" name="dayweek1" id="dayweek1"
                                       value="1" <?php if ($periodicidad == 2 && strpos($dayweek, '1')) echo "checked='checked'"; ?>>
                                Lunes
                            </label>
                            <label class="checkbox text col-12">
                                <input type="checkbox" name="dayweek2" id="dayweek2"
                                       value="2" <?php if ($periodicidad == 2 && strpos($dayweek, '2')) echo "checked='checked'"; ?>/>
                                Martes
                            </label>
                            <label class="checkbox text col-12">
                                <input type="checkbox" name="dayweek3" id="dayweek3"
                                       value="3" <?php if ($periodicidad == 2 && strpos($dayweek, '3')) echo "checked='checked'"; ?>/>
                                Miercoles
                            </label>
                            <label class="checkbox text col-12">
                                <input type="checkbox" name="dayweek4" id="dayweek4"
                                       value="4" <?php if ($periodicidad == 2 && strpos($dayweek, '4')) echo "checked='checked'"; ?>/>
                                Jueves
                            </label>
                        </div>

                        <div class="col-4">
                            <label class="checkbox text col-12">
                                <input type="checkbox" name="dayweek5" id="dayweek5"
                                       value="5" <?php if ($periodicidad == 2 && strpos($dayweek, '5')) echo "checked='checked'"; ?>/>
                                Viernes
                            </label>
                            <label class="checkbox text col-12" onclick="test_weekend(6)">
                                <input type="checkbox" name="dayweek6" id="dayweek6"
                                       value="6" <?php if ($periodicidad == 2 && strpos($dayweek, '6')) echo "checked='checked'"; ?>/>
                                Sabado
                            </label>
                            <label class="checkbox text col-12" onclick="test_weekend(7)">
                                <input type="checkbox" name="dayweek7" id="dayweek7"
                                       value="7" <?php if ($periodicidad == 2 && strpos($dayweek, '7')) echo "checked='checked'"; ?>/>
                                Domingo
                            </label>
                        </div>
                    </fieldset>
                </div> <!-- div-semanal -->

                <div id="div-diaria" class="fieldset row" style="display:none">
                    <?php $day_carga = ($periodicidad == 1) ? $carga : null; ?>
                    <div class="row">
                        <div class="col-2">
                            <label class="col-form-label">Cada:</label>
                        </div>

                        <div class="col-6">
                            <div id="spinner-input_carga1" class="input-group spinner">
                                <input type="text" name="input_carga1" id="input_carga1" class="form-control"
                                       value="<?= !empty($day_carga) ? $day_carga : 0 ?>"/>
                                <div class="input-group-btn-vertical">
                                    <button class="btn btn-default" type="button" data-bind="up">
                                        <i class="fa"><span class="fa fa-caret-up"></span></i>
                                    </button>
                                    <button class="btn btn-default" type="button" data-bind="down">
                                        <i class="fa"><span class="fa fa-caret-down"></span></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <label class="col-form-label">días</label>
                        </div>
                    </div>
                </div>  <!-- div-diaria -->

                <div id="div-mensual" class="form-horizontal fieldset col-12" align="left" style="display:none">
                    <?php
                    if ($periodicidad == 3) {
                        $radio = empty($dayweek) ? 0 : 1;
                        $in_carga = ($radio == 0) ? $carga : null;
                        $sel_carga = ($radio == 1) ? $carga : null;
                    }
                    ?>
                    <div class="form-group row">
                        <div class="col-sm-3" onclick="select_carga(0)">
                            <div class="checkbox">
                                <input type="radio" id="fixed_day0" name="fixed_day"
                                       value="0" <?php if ($radio == 0) echo "checked='checked'" ?> />
                                <label class="col-form-label ml-1">El día</label>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div id="spinner-input_carga4" class="input-group spinner">
                                <input type="text" class="form-control" name="input_carga4" id="input_carga4"
                                       value="<?= !empty($in_carga) ? $in_carga : 0 ?>" <?php if ($radio == 1) echo "disabled='disabled'" ?> />
                                <div class="input-group-btn-vertical">
                                    <button class="btn btn-default" type="button" data-bind="up">
                                        <i class="fa"><span class="fa fa-caret-up"></span></i>
                                    </button>
                                    <button class="btn btn-default" type="button" data-bind="down">
                                        <i class="fa"><span class="fa fa-caret-down"></span></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <label class="col-form-label">de cada mes</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2" onclick="select_carga(1)">
                            <div class="checkbox">
                                <input type="radio" name="fixed_day" id="fixed_day1"
                                       value="1" <?php if ($radio == 1) echo "checked='checked'" ?>/>
                                <label class="col-form-label ml-1">El</label>
                            </div>
                        </div>

                        <div class="row col-md-7">
                            <div class="col-6">
                                <select id="sel_carga" name="sel_carga"
                                        class="form-control" <?php if ($radio == 0) echo "disabled='disabled'" ?>>
                                    <option value=0>...</option>
                                    <?php for ($i = 1; $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $sel_carga) echo "selected='selected'"; ?> ><?= $frecuency_eventos[$i] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <select id="dayweek0" name="dayweek0"
                                        class="form-control" <?php if ($radio == 0) echo "disabled='disabled'" ?>>
                                    <option value=0>...</option>
                                    <?php for ($i = 1; $i < 8; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                                onclick="test_weekend(<?= $i ?>)" <?php if ($i == $dayweek) echo "selected='selected'"; ?> ><?= $dayNames[$i] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="col-form-label">de cada mes</label>
                        </div>

                    </div>
                </div>  <!-- div-mensual -->
            </div>
        </div>
    </div>


    <div id="div-freeday" class="form-horizontal">
        <div class="form-group row">
            <div class="col-6">
                <fieldset class="fieldset">
                    <legend>
                        Opción de días no laborables
                    </legend>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="saturday" id="saturday"
                                   value="1" <?= empty($saturday) ? '' : "checked='checked'" ?> />
                            Los SÁBADOS se trabajará
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="sunday" id="sunday"
                                   value="1" <?= empty($sunday) ? '' : "checked='checked'" ?> />
                            Los DOMINGOS se trabajará
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="freeday" id="freeday"
                                   value="1" <?= empty($freeday) ? '' : "checked='checked'" ?> />
                            Los FERIADOS se trabajará
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="col-6">
                <?php if (!empty($chk_date_block)) { ?>
                    <fieldset class="fieldset">
                        <legend>
                            Fechas de chequeo o cumplimiento
                        </legend>

                        <div class="radio">
                            <label>
                                <input type="radio" id="chk_date0" name="chk_date"
                                    value="0" <?= empty($chk_date) ? "checked='checked'" : '' ?> />
                                Solo en la fecha de término de la tarea
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" id="chk_date1" name="chk_date"
                                    value="1" <?= !empty($chk_date) ? "checked='checked'" : '' ?> />
                                En todas las fechas que han sido planificada o con la periodicidad planificada
                            </label>
                        </div>
                    </fieldset>
                <?php } ?>

                <?php if (!empty($chk_cant_day_block)) { ?>
                    <div class="form-group row">
                        <div class="col-10">
                            <label class="col-form-label" id="label_cant_days" style="display:none;">
                                Cantidad de días que durará cada acción de control o auditoría:
                            </label>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="text" id="cant_days" name="cant_days" style="display:none"
                                maxlength="2" value="<?= $cant_days ?>"/>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>    
    </div>

</div> <!-- container -->

