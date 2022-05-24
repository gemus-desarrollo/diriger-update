<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/6/15
 * Time: 7:52 a.m.
 */
?>

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>   
        
        
        <script type="text/javascript">
            function _test_cancel(id) {
                confirm('Realmente desea eliminar esta tareas de la gestión de <?= $text_title ?>?', function(ok) {
                    if (ok){
                        eliminar_tarea(id, 0);
                    } else {
                        return false;
                    }
                });
            }
            
            function _test_delete(id) {
                var text= 'Realmente desea eliminar esta tareas del sistema, independientemente del Proyecto, ';
                text+= 'Nota de Hallazgo o cualquier otro tipo de vinculación que tenga en el sistema?';
                confirm(text, function(ok) {
                    if (ok) {
                        eliminar_tarea(id, 1);
                    } else {
                        return false;
                    }                    
                });
            }
        </script>


        <div id="toolbar" class="btn-toolbar" role="form">
            <button type="button" class="btn btn-primary " onclick="add_tarea()" title="Definir una nueva tarea necesaria para la gestión de <?=$text_title?>. Esta tarea será creada en el sistema por primera vez">
                Agregar Nueva Tarea
            </button>
            <?php if ($signal != "proyecto" && $signal != "tablero") {?>
            <button type="button" class="btn btn-primary ml-2 " onclick="mostrar(1)" title="Asociar a la gestión de <?=$text_title?> una tarea ya definida en el sistema">
                Asociar Tarea Existente
            </button>
            <?php } ?>
        </div>


        <table id="table" class="table table-hover table-striped"
               data-toggle="table"
               data-height="460"
               data-toolbar="#toolbar"
               data-search="true"  
               data-show-columns="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>TAREA</th>
                    <th>PROCESO</th>
                    <th>RESPONSABLE</th>
                    <th>INICIO / FIN</th>
                    <th>DESCRIPCIÓN</th>
                    <th>EJECUTANTES</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $obj_user = new Tusuario($clink);
                $obj_user->set_use_copy_tusuarios(false);

                $result = null;
                if (!empty($id))
                    $obj_reg->listar_tareas(null, null, true);

                $i = 0;
                foreach ($obj_reg->array_tareas as $row) {
                    ?>
                    <tr>
                        <td valign="top"><?= ++$i ?>&nbsp;&nbsp;
                            <input type="hidden" id="chk_init_tarea_<?= $row['id'] ?>" name="chk_init_tarea_<?= $row['id'] ?>" value="1" />
                        </td>

                        <td>
                            <?php if ($action != 'list') { ?>
                                <a href="#" class="btn btn-info btn-sm" title="No relacionar la tarea con <?= $text_title ?>" onclick="_test_cancel(<?= $row['id'] ?>)">
                                    <i class="fa fa-unlink"></i>Desvincular
                                </a>
                                <a href="#" class="btn btn-warning btn-sm" title="Editar o Modificar" onclick="editar_tarea(<?= $row['id'] ?>);">
                                    <i class="fa fa-pencil"></i>Editar
                                </a>
                                <a href="#" class="btn btn-danger btn-sm" title="Eliminar la tarea del sistema" onclick="_test_delete(<?= $row['id'] ?>)">
                                    <i class="fa fa-trash"></i>Eliminar
                                </a>                                
                                <br />
                            <?php } ?>

                            <?= $row['nombre'] ?>
                        </td>

                        <td>
                            <?php
                            foreach ($row['procesos'] as $prs) {
                                $obj_prs->Set($prs);
                                echo $obj_prs->GetNombre() . ' (' . $Ttipo_proceso_array[$obj_prs->GetTipo()] . '), <br >';
                            }
                            ?>
                        </td>

                        <td>
                            <?php
                            $array = $obj_user->GetEmail($row['id_responsable']);
                            echo $array['nombre'] . ', ' . textparse($array['cargo']);
                            ?>
                        </td>
                        <td>
                            <?= odbc2time_ampm($row['fecha_inicio']) ?>
                            <br /><br />
                            <?= odbc2time_ampm($row['fecha_fin']) ?>
                        </td>
                        <td>
                            <?= $row['descripcion'] ?>
                        </td>
                        <td>
                            <?php
                            $string = $obj_reg->get_participantes($row['id'], 'tarea');
                            echo $string;

                            $origen_data = $obj_user->GetOrigenData('participant', $row['origen_data']);
                            if (!is_null($origen_data))
                                echo "<br /> " . merge_origen_data_participant($origen_data);
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <input type="hidden" id="cant" name="cant" value="<?= $i ?>">
