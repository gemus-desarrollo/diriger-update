<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 18/02/15
 * Time: 20:44
 */

?>

<div id="win-board-signal" style="z-index:1000">

    <div id="win-head">
        <div id="win-title"></div>
        <div class="win-icon-head">
            &nbsp;<img class="icon" src="../img/_close.png" title="cerrar ventana" onclick="HideContent('win-board-signal'); return true;" />
        </div>
    </div>

    <div class="panel-icons">
        <table align="right"><tr>
            <td></td>
            <td class="td-icon" onclick="grafico_indicador()" ><img class="icon" src="../img/chart_bar.png" title="ver gráficos" /><br/>Graficar</td>
            <td ><div class="td-icon" onclick="plan_indicador()" id="icon_plan"><img class="icon" src="../img/hourglass_add.png" title="planificar" /><br/>Planificar</div></td>
            <td ><div class="td-icon" onclick="real_indicador()" id="icon_real"><img class="icon" src="../img/calculator_add.png" title="ingresar datos reales" /><br/>Registrar</div></td>
            <td class="td-icon" onclick="edit_indicador('<?=$signal?>','<?=$action?>')" ><img class="icon" src="../img/_edit.png" title="editar" /><br/>Editar</td>

            <?php if($action != 'list') { ?>
                <td class="td-icon" onclick="delete_indicador('<?=$signal?>')" ><img class="icon" src="../img/_drop.png" title="eliminar indicador y sus datos" /><br/>Eliminar</td>
            <?php } ?>
        </tr></table>
            <br /><br />
    </div>

    <div class="win-body">
        <strong>REAL:</strong><br />
        <u>Registro</u>: <div class="win-label" id="registro_real"></div>
        <u>Valor</u>: <div class="win-label" id="valor_real"></div>
        <u>Observaciones:</u><div class="win-label" id="observacion_real"></div>
        <u>Responsable:</u> <div class="win-label" id="responsable_real"></div>

        <hr />
        <strong>PLAN:</strong><br />
        <u>Registro</u>: <div class="win-label" id="registro_plan"></div>
        <u>Plan</u>: <div class="win-label" id="valor_plan"></div>
        <u>Observaciones:</u> <div class="win-label" id="observacion_plan"></div>
        <u>Responsable:</u> <div class="win-label" id="responsable_plan"></div>
    </div>
</div>


<div style="bottom: -225px;" id="bit" class="loggedout-follow-normal"><a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>
    <div id="bitsubscribe">

        <table width="100%">
            <tr><td><img src="../img/alarm-dark.png"></td>
                <td width="40%">Sobrecumplido al 110% o m&aacute;s</td>
                <td>&nbsp;</td>
                <td><img src="../img/arrow-green.png"></td>
                <td colspan="4" width="40%">Mejora referido al periodo anterior</td>
            </tr>
            <tr>
                <td><img src="../img/alarm-blue.png"></td>
                <td>Sobrecumplido al 105% o m&aacute;s, menor que el 110% de Sobrecumplimiento</td>
                <td>&nbsp;</td>
                <td><img src="../img/arrow-yellow.png"></td>
                <td colspan="4">Sin Cambios referido al periodo anterior</td>
            </tr>
            <tr><td><img src="../img/alarm-green.png"></td>
                <td>Éxito. Estado de cumplimiento igual o mayor que 95% y menor que el 105% de Sobrecumplimiento</td>
                <td>&nbsp;</td>
                <td><img src="../img/arrow-red.png"></td>
                <td colspan="4">Empeora referido al periodo anterior</td>
            </tr>
            <tr><td><img src="../img/alarm-yellow.png"></td>
                <td>Cumplimiento igual o mayor que 90% y menor que el 95%</td>
                <td>&nbsp;</td>
                <td><img src="../img/arrow-blank.png"></td>
                <td colspan="4">No hay datos en periodo anterior</td>
            </tr>

            <tr><td height="41"><img src="../img/alarm-orange.png"></td>
                <td>Estado de cumplimiento mayor o igual al 85% y menor que 90% </td>
                <td>&nbsp;</td>
                <td><img src="../img/alarm-blank.png"></td>
                <td colspan="4">No existen datos</td>
            </tr>

            <tr><td><img src="../img/alarm-red.png"></td>
                <td>Fracaso. Cumplimiento menor 85%</td>
                <td>&nbsp;</td>
                <td><img src="../img/alarm-null.png"></td>
                <td colspan="4">No hay valor del Plan o Criterio de Éxito</td></tr>

            <tr><td colspan="8"><sup>*</sup>Los valores que aparecen en esta leyenda son los que se utilizan por defecto. cada indicador puede tener sus valores de escala especificos. </td></tr>

        </table>

    </div><!-- #bitsubscribe -->
</div><!-- #bit -->


<form action='javascript:' class="intable" method=post>
    <input type=hidden name=exect value=<?=$action?> />
    <input type=hidden name=menu id="menu" value=tablero />
    <input type=hidden name=action id=action value=<?=$action?> />
    <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />

    <input type="hidden" id="id_indicador" name="id_indicador" value=0 />
    <input type="hidden" id="id_persp" name="id_persp" value=0 />
    <input type="hidden" id="id_user_real" name="id_user_real" value=0 />
    <input type="hidden" id="id_user_plan" name="id_user_plan" value=0 />
    <input type="hidden" id="trend" name="trend" value=0 />

    <input type="hidden" id="cumulative" name="cumulative" value="" />
    <input type="hidden" id="formulated" name="formulated" value="" />

    <input type="hidden" id="id_usuario" name="id_usuario" value=<?=$_SESSION['id_usuario'] ?> />
    <input type="hidden" id="nivel" name="nivel" value=<?=$_SESSION['nivel']?> />

    <input id=tablero type="hidden" value="<?=$id_tablero?>" />
    <input id=actual_year type="hidden" value="<?=$actual_year?>" />

<!-- Panel -->
    <?php
    $use_select_year= true;
    $use_select_month= true;
    $use_select_day= true;
    include_once('_toppanel_select_div.inc.php');
    ?>
 <!--panel -->

    <script language="javascript">set_pos_tab(<?=$pos?>);</script>

    <script type="text/javascript" src="../js/wz_tooltip.js?version=<?=$_SESSION['update_no']?>"></script>


