<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";

require_once "../../php/class/nota.class.php";
require_once "../../php/class/riesgo.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'nota';
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;
$fecha_final_real= !empty($_GET['fecha_final_real']) ? urldecode($_GET['fecha_final_real']) : null;

$obj= null;

if ($signal == 'nota') {
    $obj= new Tnota($clink);
    $obj->SetIdNota($id_nota);
    
    if (!empty($id_nota)) 
        $result= $obj->listar_causas();
}

if ($signal == 'riesgo') {
    $obj= new Triesgo($clink);
    $obj->SetIdRiesgo($id_riesgo);
    
    if (!empty($id_riesgo)) 
        $result= $obj->listar_causas();
}
?>

    <!-- Bootstrap core CSS --> 
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         
 

    <div id="toolbar-causa" class="col-lg-12">
        <button type="button" class="btn btn-primary" onclick="add_causa()" title="">
            Agregar
        </button>         
    </div>
    
    <table id="table-causa" class="table table-hover table-striped"
           data-toggle="table"
           data-height="400"
           data-toolbar="#toolbar-causa"
           data-search="true"  
           data-show-columns="true">
        <thead>
            <tr>
                <th width="40px">No.</th>
                <th width="120px"></th>
                <th>FECHA</th>
                <th>DESCRIPCIÓN</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $i = 0;
            while ($row = @$clink->fetch_array($result)) {
                $id = $row['id_causa'];
            ?>
                <tr>
                    <td>
                        <?= ++$i ?>
                    </td>

                    <td>
                        <?php if ($action != 'list' && (($id_nota && empty($fecha_final_real)) || $id_riesgo)) { ?> 
                            <a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="delete_causa(<?= $id ?>)">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <a href="#" class="btn btn-warning btn-sm" title="Editar o Modificar" onclick="edit_causa(<?= $id ?>);">
                                <i class="fa fa-edit"></i>Editar
                            </a>   
                        <?php } ?>                
                    </td>

                    <td>
                        <?= odbc2date($row['fecha']) ?>
                        <input type="hidden" id="fecha_reg_<?= $id ?>" name="fecha_reg_<?= $id ?>" value="<?= odbc2date($row['fecha']) ?>" />
                    </td>

                    <td>
                        <?= textparse($row['descripcion']) ?>
                        <input type="hidden" id="causa_<?= $id ?>" name="causa_<?= $id ?>" value="<?= textparse($row['descripcion'], true) ?>" />
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <input type="hidden" id="cant_causa" name="cant_causa" value="<?= $i?>">         