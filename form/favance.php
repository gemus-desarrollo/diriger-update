<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/tarea.class.php";


$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
}
else {
    $obj= new Ttarea($clink);
}

$id= $obj->GetIdPerspectiva();
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>PLANIFICACI"N DE TAREA</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link href="../libs/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
    <script src="../libs/bootstrap-switch/bootstrap-switch.min.js"></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            if (!Entrada($('#nombre').val())) {
                $('#nombre').focus(focusin($('#nombre')));
                alert('Introduzca el nombre de la tarea');
                return;
            }

            if ($('#responsable').val() == 0) {
                $('#responsable').focus(focusin($('#responsable')));
                alert('Selecione el responsable');
                return;
            }

            if (!Entrada($('#descripcion').val())) {
                $('#descripcion').focus(focusin($('#descripcion')));
                alert('Introduzca la descripción');
                return;
            }

            if (!Entrada($('#fecha_inicio').val())) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
                alert('Introduzca la fecha de inicio de la tarea');
                return;
            }

            if (!Entrada($('#fecha_final').val())) {
                $('#fecha_final').focus(focusin($('#fecha_final')));
                alert('Introduzca la fecha en que deberá culminar la tarea');
                return;
            }

            document.forms[0].action= '../php/tarea.interface.php';
            document.forms[0].submit();
        }
    </script>

    <script type="text/javascript">
        var focusin;
        $(document).ready(function() {
            $('#div_fecha_inicio').datetimepicker({
               format: 'DD/MM/YYYY H:mm A',
               minDate: '01/01/<?=$init_year?> 00:01',
               maxDate: '31/12/<?=$end_year?> 23:59',
               autoclose: true,
               inline: true,
               sideBySide: true
           });
           $('#div_fecha_fin').datetimepicker({
               format: 'DD/MM/YYYY H:mm A',
               minDate: '01/01/<?=$init_year?> 00:01',
               maxDate: '31/12/<?=$end_year?> 23:59',
               autoclose: true,
               inline: true,
               sideBySide: true
           });
            $('#div_fecha_inicio').click(function(){
               $(this).data("DateTimePicker").show();
            });
            $('#div_fecha_inicio').on('change', function() {
                validar_interval(1);
            });

            $('#div_fecha_fin').click(function(){
               $(this).data("DateTimePicker").show();
            });
            $('#div_fecha_fin').on('change', function() {
                validar_interval(2);
            });

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error))?>");
            <?php } ?>
        });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body container">
        <div class="card card-primary">
            <div class="card-header">AUDITORIAS</div>
            <div class="card-body">

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?><button class="btn btn-primary" type="submit">Aceptar</button><?php } ?>
                            <button class="btn btn-warning" type="reset" onclick="self.location.href='../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&'">Cancelar</button>
                            <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div>  <!-- container -->
    </body>
</html>


<center>

    <fieldset>
        <legend>TAREA</legend>
        <form name="ftarea" action='javascript:validar()' class='form colours' method=post>
            <input type=hidden name=exect value=<?= $action ?> />
            <input type=hidden name=id value=<?= $id ?> />
            <input type=hidden name=menu value=avance />


            <table cellspacing="4">
                <tr>
                    <td width="87"><label for="nombre">Tarea:</label></td>
                    <td colspan="3">Programación del modulo</td>
                </tr>

                <tr><td><label for="nombre">Responsable:</label></td>
                    <td colspan="3">Geraudis Mustelier</td>
                </tr>

                <tr>
                    <td><label for="nombre">Programación:</label></td>
                    <td colspan="3">Inicio: 4/6/2007 Finaliza: 5/7/2009</td>
                </tr>
                <tr>
                    <td>Ultima actualizacion</td>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td><label for="descripcion">Cumplimiento:</label> </td>
                    <td colspan="3"> <input name="cumplimiento" id="cumplimiento" class="texta" style="width:50px;"><strong>&nbsp;%</strong></td></tr>

                <tr>
                    <td height="104"><label for="descripcion">Observación:</label> </td>
                    <td colspan="3">
                        <textarea name="descripcion" rows="8" id="descripcion" class="texta"><?php echo $obj->GetDescripcion(); ?></textarea></td>
                </tr>

                <tr>
                    <td width="87"><label for="nombre">Inició?:</label></td>
                    <td>&nbsp;
                        <select name="inicio" id="inicio" class="texta" style="width:40px">
                            <option value="0">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </td>
                    <td></td>
                    <td width="168"></td>
                </tr>

                <tr><td width="87"><label for="nombre">Terminó?:</label></td>
                    <td>&nbsp;

                        <select name="fin" id="fin" class="texta" style="width:40px">
                            <option value="0">NO</option>
                            <option value="1">SI</option>
                        </select>
                        <br /></td><td width="42"></td>
                    <td></td>
                </tr>


                <tr>
                    <td></td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3"><p class="submit" align="center">
                        <?php if ($action == 'update' || $action == 'add') { ?> <input value="Aceptar" type="submit">&nbsp;  <?php } ?>
                        <input value="Cancelar" type="reset" onclick="self.location.href='../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&'"></p>
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>

</center>

</body>
</html>

<?php if (!is_null($error)) { ?>
<script language='javascript' type="text/javascript" charset="utf-8">alert("<?php echo $error ?>")</script>
<?php } ?>


