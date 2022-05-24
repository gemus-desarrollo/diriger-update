<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/asistencia.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$id_evento= $_GET['id_evento'];
$id_proceso= $_GET['id_proceso'];

$obj= new Tevento($clink);
$obj->SetIdEvento($id_evento);
$obj->Set();
$id_evento_code= $obj->get_id_evento_code();
$asunto= $obj->GetNombre();
$fecha_inicio= $obj->GetFechaInicioPlan();
$id_responsable= $obj->GetIdResponsable();
$id_secretary= $obj->GetIdSecretary();

$if_jefe_meeting= $menu == "tablero" ? false : true;
if ($_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe_meeting= true;
if ($id_secretary == $_SESSION['id_usuario'] || $id_responsable == $_SESSION['id_usuario'])
    $if_jefe_meeting= true;
$if_jefe_meeting= $action == 'add' ? $if_jefe_meeting : false;

$visible= $if_jefe_meeting ? "visible" : "hidden";
$disabled= $if_jefe_meeting ? null : "disabled='yes'";

$obj->SetIdProceso(null);
$obj->set_user_date_ref($fecha_inicio);
$obj->listar_usuarios(false);
$array_usuarios= $obj->array_usuarios;

$obj->SetIdEvento($id_evento);
$obj->listar_grupos();
$array_grupos= $obj->array_grupos;

$obj_grp= new Tgrupo($clink);

foreach ($array_grupos as $grp) {
    $obj_grp->SetIdGrupo($grp['id']);
    $obj_grp->listar_usuarios(false);
    $_array_usuarios= $obj_grp->array_usuarios;

    if (count($_array_usuarios))
        $array_usuarios= array_merge_overwrite((array)$array_usuarios, (array)$_array_usuarios);
}

$obj_assist= new Tasistencia($clink);
$obj_assist->SetIdEvento($id_evento);
$obj_assist->set_id_evento_code($id_evento_code);

$obj_assist->SetIdUsuario(null);
$obj_assist->SetIdProceso(null);

$obj_assist->listar(false);
$array_asistencias= $obj_assist->array_asistencias;

$obj_prs= new Tproceso($clink);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ASISTENCIA Y PARTICIPANTES A LA REUNIÓN</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <link rel="stylesheet" type="text/css" href="../libs/multiselect/multiselect.css" />
        <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <script language='javascript' type="text/javascript">
            function validar() {
                document.forms[0].action= '../php/asistencia.interface.php';
                document.forms[0].submit();
            }

            var oId= 0;
            var ifnew= true;

            function form_guest() {
                displayFloatingDiv('div-ajax-panel', "AGREGAR INVITADO EXTERNO", 60, 0, 5, 10);

                $('#exect').val('add');
                ifnew= true;
            }

            function close_guest() {
                CloseWindow('div-ajax-panel');
            }

            function edit_guest(id) {
                oId= id;
                form_guest();
                ifnew= false;

                $("#nombre").val($("#nombre_"+oId).val());
                $("#cargo").val($("#cargo_"+oId).val());
                $("#entidad").val($("#entidad_"+oId).val());
            }

            function del_guest(id) {
                confirm('Realmente desea eliminar este invitado a la reunión?', function(ok) {
                    if (!ok)
                        return false;
                    else {
                        $('#tab_guest_'+id).val(0);

                        var ids= new Array();
                        ids.push(id);

                        $table.bootstrapTable('remove', {
                            field: 'id',
                            values: ids
                        });

                         for(var i= id; i <= $('#cant_guest').val(); ++i) {
                             if (arrayIndex['-'+i] == 'undefined')
                                 continue;
                             arrayIndex['-'+i]= arrayIndex['-'+i] ? arrayIndex['-'+i] - 1 : 0;
                             maxIndex= arrayIndex['-'+i];
                         }
                         arrayIndex['-'+id]= 'undefined';
                    }
                });
            }

            function validar_guest() {
                if (!Entrada($("#nombre").val())) {
                    $('#nombre').focus();
                    alert("Debe de registrar el NOMBRE Y LOS APELLIDOS del invitado a la reunión");
                    return false;
                }
                if (!Entrada($("#cargo").val())) {
                    $('#cargo').focus();
                    alert("Debe de registrar el CARGO del invitado a la reunión");
                    return false;
                }
                if (!Entrada($("#entidad").val())) {
                    $('#entidad').focus();
                    alert("Debe de registrar la ENTIDAD U ORGANIZACIÓN a la que pertenece el invitado a la reunión");
                    return false;
                }
                return true;
            }

            function limpiar_guest() {
                $("#nombre").val(null)
                $("#cargo").val(null);
                $("#entidad").val(null);
                ifnew= true;
            }

            function add_guest() {
                if (!validar_guest())
                    return;

                var numero= oId;
                var flag= ifnew ? 1 : 2;

                if (ifnew) {
                    numero= parseInt($("#cant_guest").val()) + 1;
                    $("#cant_guest").val(numero);
                    oId= numero;
                }

                var nombre = $("#nombre").val();
                var cargo = $("#cargo").val();
                var entidad = $("#entidad").val();

                var strHtml1= numero;

                var strHtml2 = '<a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="del_guest(' + oId + ');">'+
                            '<i class="fa fa-trash"></i>Eliminar'+
                        '</a>'+
                        '<a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit_guest(' + oId + ');" >'+
                            '<i class="fa fa-edit"></i>Editar'+
                        '</a>';

                var strHtml3= nombre;
                strHtml3+= '<input type="hidden" id="nombre_' + oId + '" name="nombre_' + oId + '" value="' + nombre + '"/>';
                strHtml3+= '<input type="hidden" id="tab_guest_' + oId + '" name="tab_guest_' + oId + '" value="' + flag + '" />';

                var strHtml4 = cargo + '<input type="hidden" id="cargo_' + oId + '" name="cargo_' + oId + '" value="' + cargo + '"/>';

                var strHtml5 = entidad + '<input type="hidden" id="entidad_' + oId + '" name="entidad_' + oId + '" value="' + entidad + '"/>';

                if (ifnew) {
                    index= ++maxIndex;
                    arrayIndex['-'+numero]= index;

                    $table.bootstrapTable('insertRow', {
                        index: index,
                        row: {
                            id: strHtml1,
                            icons: strHtml2,
                            nombre: strHtml3,
                            cargo: strHtml4,
                            entidad: strHtml5
                        }
                    });
                }

                if (!ifnew) {
                    index= arrayIndex['-'+numero];
                    $("#tab_guest_"+oId).val(2);

                    $table.bootstrapTable('updateRow', {
                        index: index,
                        row: {
                            id: strHtml1,
                            icons: strHtml2,
                            nombre: strHtml3,
                            cargo: strHtml4,
                            entidad: strHtml5
                        }
                    });
                }

                ifnew= true;
                limpiar_guest();
                close_guest();
            }

            function set_tab_user(id) {
                $("#tab_user_"+id).val(1);
            }
        </script>

        <script type="text/javascript">
            var $table;
            var row_guest;
            var arrayIndex= new Array();
            var maxIndex=-1;
            var index= -1;

            $(document).ready(function () {
                InitDragDrop();

                $table = $('#table-guest');
                $table.bootstrapTable('append', row_guest);

                <?php if (!is_null($error)) { ?>
                    alert("<?= str_replace("\n", " ", $error) ?>");
                <?php } ?>
            });
        </script>

    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="app-body container">
            <div class="card card-primary">
                <div class="card-header">ASISTENCIA Y PARTICIPANTES A LA REUNIÓN</div>

                <div class="card-body">
                    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Registrados en el Sistema</a></li>
                        <li id="nav-tab2" class="nav-item" title="Formula de cálculo e Interpretación de los valores"><a class="nav-link" href="tab2">Invitados Externos</a></li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()'  method="post">
                        <input type="hidden" id="exect" name=exect value="<?=$action?>" />
                        <input type="hidden" name="menu" value="assist" />

                        <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento?>" />
                        <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />

                        <input type="hidden" id="t_cant_tab_user"  name="t_cant_tab_user" value="0" />

                        <div class="alert alert-info" style="margin-bottom: 0px;">
                            </strong>Reunion:</strong> <?=$asunto?> <strong style="margin-left: 20px;">Fecha y Hora:</strong> <?=odbc2time_ampm($fecha_inicio)?>
                        </div>

                        <!-- Registrados en el Sistema -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <div class="col-12">
                                    <table id="table-assist" class="table table-hover table-striped"
                                           data-toggle="table"
                                           data-search="true"
                                           data-show-columns="true">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>NOMBRE</th>
                                                <th>PROCESO</th>
                                                <th>AUSENTE</th>
                                                <th>INVITADO</th>
                                             </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $j= 0;
                                            $i= 0;

                                            foreach ($array_usuarios as $row) {
                                                $array= $obj_assist->GetAsistencia(null, $row['id']);
                                            ?>
                                            <tr>
                                                <td>
                                                    <?=++$i?>
                                                    <input type="hidden" id="tab_user_<?=$i?>" name="tab_user_<?=$i?>" value="0" />
                                                    <input type="hidden" id="id_user_<?=$i?>" name="id_user_<?=$i?>" value="<?=$row['id']?>" />
                                                    <input type="hidden" id="id_assist_<?=$i?>" name="id_assist_<?=$i?>" value="<?=$array['id']?>" />
                                                </td>
                                                <td>
                                                    <?=$row['nombre']?>
                                                    <?=!empty($row['cargo']) ? textparse($row['cargo']) : null?>
                                                </td>
                                                <td>
                                                <?php
                                                $obj_prs->Set($row['id_proceso']);
                                                echo $obj_prs->GetNombre()."<br />".$Ttipo_proceso_array[$obj_prs->GetTipo()];
                                                ?>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="ausente_<?=$i?>" name="ausente_<?=$i?>" value="1" <?php if ($array['ausente']) echo "checked"?> <?=$disabled?> onchange="set_tab_user(<?=$i?>)" />
                                                </td>
                                                <td>
                                                    <?php
                                                    $_disabled= null;
                                                    if ($action == 'list')
                                                        $_disabled= "disabled='yes'";
                                                    if ($row['id'] == $id_responsable || $row['id'] == $id_secretary)
                                                        $_disabled= "disabled='yes'";
                                                    $_disabled= ($disabled || $_disabled) ? "disabled='yes'" : null;
                                                    ?>
                                                    <input type="checkbox" id="invitado_<?=$i?>" name="invitado_<?=$i?>" value="1" <?php if ($array['invitado']) echo "checked"?> <?=$_disabled?> onchange="set_tab_user(<?=$i?>)" />
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <script type="text/javascript">
                                    $('#t_cant_tab_user').val(<?=$i?>);
                                </script>
                            </div>
                        </div> <!-- Registrados en el Sistema -->


                        <!-- Invitados Externos -->
                        <div class="tabcontent" id="tab2">
                            <div id="toolbar">
                                <button type="button" id="btn_agregar" class="btn btn-success" onclick="form_guest();" style="visibility:<?= $visible ?>">
                                    <i class="fa fa-plus"></i>Agregar
                                </button>
                            </div>

                            <div class="form-group row">
                                <div class="col-12">
                                    <?php
                                    reset($obj_assist->array_asistencias);
                                    $i = 0;
                                    foreach ($obj_assist->array_asistencias as $array) {
                                        if (!empty($array['id_usuario']))
                                            continue;
                                        if ($array['id_usuario'] == $id_responsable)
                                            continue;
                                        if ($array['id_usuario'] == $id_secretary)
                                            continue;
                                        ++$i;
                                        ?>
                                        <input type="hidden" id="id_assist_guest_<?=$i ?>" name="id_assist_guest_<?=$i?>" value="<?= $array['id'] ?>" />
                                        <input type="hidden" id="tab_guest_<?= $i ?>" name="tab_guest_<?= $i ?>" value="1" />
                                    <?php } ?>


                                    <script type="text/javascript">
                                    row_guest= [
                                        <?php
                                        $i= 0;
                                        reset($obj_assist->array_asistencias);
                                        foreach ($obj_assist->array_asistencias as $array) {
                                             if (!empty($array['id_usuario']))
                                                 continue;
                                             ++$i;
                                             if ($i > 1)
                                                 echo ",";
                                            ?>
                                            {
                                               id: <?=$i?>,

                                               <?php if ($if_jefe_meeting) { ?>
                                                    icons: ''+
                                                        '<a class="btn btn-danger btn-sm" href="#" title="Eliminar" onclick="del_guest(<?=$i?>);">'+
                                                            '<i class="fa fa-trash"></i>Eliminar'+
                                                        '</a>'+

                                                        '<a class="btn btn-warning btn-sm" href="#" title="Editar" onclick="edit_guest(<?=$i?>);">'+
                                                            '<i class="fa fa-edit"></i>Editar'+
                                                        '</a> '+
                                                        '',
                                                <?php } ?>

                                                nombre: ''+
                                                    '<?=$array['nombre']?>'+
                                                    '<input type="hidden" id="nombre_<?=$i?>" name="nombre_<?=$i?>" value="<?= textparse($array['nombre'], true)?>" />'+
                                                   '',

                                               cargo: ''+
                                                    '<?=$array['cargo']?>'+
                                                    '<input type="hidden" id="cargo_<?=$i?>" name="cargo_<?=$i?>" value="<?=textparse($array['cargo'], true)?>" />'+
                                                    '',

                                                entidad: ''+
                                                    '<?=$array['entidad']?>'+
                                                    '<input type="hidden" id="entidad_<?=$i?>" name="entidad_<?=$i?>" value="<?= textparse($array['entidad'], true)?>" />'+
                                                    ''
                                            }
                                       <?php } ?>
                                    ];
                                   </script>

                                    <table id="table-guest" class="table table-hover table-striped"
                                           data-toggle="table"
                                           data-height="320"
                                           data-toolbar="#toolbar"
                                           data-unique-id="id"
                                           data-search="true"
                                           data-show-columns="true">
                                        <thead>
                                            <tr>
                                                <th data-field="id">No.</th>
                                                <?php if ($if_jefe_meeting) { ?><th data-field="icons"></th><?php } ?>
                                                <th data-field="nombre">NOMBRE</th>
                                                <th data-field="cargo">CARGO</th>
                                                <th data-field="entidad">ENTIDAD/ORGANIZACIÓN</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    <script type="text/javascript">
                                        maxIndex= <?= $i-1 ?>;

                                        <?php
                                        $k= 0;
                                        for ($j= 1; $j <= $i; ++$j) {
                                        ?>
                                            arrayIndex['-'+<?=$j?>]= <?=$k++?>;
                                        <?php } ?>
                                    </script>

                                    <input type="hidden" id="cant_guest"  name="cant_guest" value="<?= $i ?>" />
                                </div>
                            </div>
                        </div> <!-- Invitados Externos -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($if_jefe_meeting) { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="button" onclick="self.close()">Cerrar</button>
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


<!-- ajax-panel -->
<div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">
    <div class="card card-primary">
        <div class="card-header win-drag">
            INVITADOS
        </div>

        <div class="card-body">
            <div class="form-horizontal">
                <div class="form-group row">
                    <label class="col-form-label col-3">
                        Nombre y Apellidos:
                    </label>
                    <div class="col-9">
                        <input type="text" class="form-control input-sm" id="nombre" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Cargo:
                    </label>
                    <div class="col-10">
                        <input type="text" class="form-control input-sm" id="cargo" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Entidad:
                    </label>
                    <div class="col-10">
                        <input type="text" class="form-control input-sm" id="entidad" />
                    </div>
                </div>
                <div class="btn-block btn-app">
                    <button type="button" class="btn btn-primary" onclick="add_guest()">Aceptar</button>
                    <button type="reset" class="btn btn-warning" onclick="close_guest()">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div> <!-- ajax-panel -->
