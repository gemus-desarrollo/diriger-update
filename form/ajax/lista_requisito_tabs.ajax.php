<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/6/15
 * Time: 7:52 a.m.
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/usuario.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/lista.class.php";
require_once "../../php/class/lista_requisito.class.php";

$_SESSION['debug']= 'no';

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';
$signal= $_GET['signal'];
$menu= $_GET['menu'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$componente= !empty($_GET['componente']) ? $_GET['componente'] : 0;
$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : 0;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : 0;

$obj= new Tlista_requisito($clink);

$obj->SetComponente($componente);
$obj->SetYear($year);
$obj->SetIdLista($id_lista);
$obj->SetCapitulo($capitulo);

$result= $obj->listar();

$obj_prs= new Tproceso($clink);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>   


    <script type="text/javascript">          
        function delete_requisito(id) {
            var text= 'Realmente desea eliminar este requisito de la Lista de Verificación? ';

            confirm(text, function(ok) {
                if (ok) {
                    _this();
                } else {
                    return false;
                } 

                function _this() {
                    ejecutar('delete', id)
                }
            });
        }
    </script>

    <table class="table table-hover table-striped"
          data-toggle="table"
          data-toolbar="#toolbar"
          data-height="600"
          data-search="true"
          data-show-columns="true"> 
       <thead>
           <tr>
               <th data-field="id">No.</th>
               <th data-field="icons"></th>
               <th data-field="peso">Peso</th>
               <th data-field="nombre">Requisitos a Evaluar</th>
               <th data-field="descripcion">Evidencias</th>
               <th data-field="indicacion">Indicaciones al Equipo Evaluador</th>
               <th data-field="registro">Registro</th>
           </tr>
       </thead>

       <tbody>
           <?php while ($row= $clink->fetch_array($result)) { ?>
           <tr>
               <td>
                   <?=$row['numero']?>
               </td>
               <td>
                    <a class="btn btn-warning btn-sm" href="javascript:form_requisito('<?= $action ?>', <?= $row['_id'] ?>);">
                        <i class="fa fa-edit"></i>Editar
                    </a>

                    <?php if ($action != 'list') { ?>
                        <a class="btn btn-danger btn-sm" href="javascript:ejecutar('delete', <?= $row['_id'] ?>)">
                            <i class="fa fa-trash"></i>Eliminar
                        </a>
                    <?php } ?>  
               </td>

               <td>
                    <?= $Tpeso_inv_array[$row['peso']] ?>
               </td>

               <td>
                   <?= textparse($row['nombre'])?>  
               </td>
               <td>
                   <?= textparse($row['evidencia'])?> 
               </td>
               <td>
                   <?= textparse($row['indicacion'])?> 
               </td>
               <td></td>
           </tr>
           <?php } ?>
       </tbody>
   </table>

    <input type="hidden" id="cant" name="cant" value="<?= $i ?>">
