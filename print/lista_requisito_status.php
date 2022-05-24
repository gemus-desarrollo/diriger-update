<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/auditoria.class.php";
require_once "../php/class/document.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";

require_once "../php/class/traza.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_auditoria= !is_null($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;
$id_tipo_lista= !is_null($_GET['id_tipo_lista']) && strlen($_GET['id_tipo_lista']) > 0 ? $_GET['id_tipo_lista'] : null;
$id_lista= !is_null($_GET['id_lista']) ? $_GET['id_lista'] : null;
$componente= !is_null($_GET['componente']) && strlen($_GET['componente']) > 0 ? $_GET['componente'] : null;
$id_capitulo= !is_null($_GET['id_capitulo']) && strlen($_GET['id_capitulo']) > 0 ? $_GET['id_capitulo'] : null;

$obj_lista= new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->Set($id_lista);
$id_lista_code= $obj_lista->get_id_code();
$nombre_lista= $obj_lista->GetNombre();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

// ----------- auditoria ------------------------------------
$obj_audit= new Tauditoria($clink);
$obj_audit->SetIdAuditoria($id_auditoria);
$obj_audit->Set();
$auditoria= $obj_audit->GetNombre();

$obj_reg= new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdAuditoria($id_auditoria);
$obj_reg->SetIdProceso($id_proceso);
$obj_reg->SetIdLista($id_lista);
//----------------------------------------------------------

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "APLICACIÓN DE GUÍA DE CONTROL", "Corresponde a periodo año: $year");
?>

<!DOCTYPE html>
<html>

<head>
    <title>LISTADO DE REQUISITOS</title>

    <?php require "inc/print_top.inc.php";?>

    <style type="text/css">
        .colspan {
            background: #c6c7c9 !important;
            color: black!important;
            padding: 4px;
            text-align: center;
            border: none;
            border: 1px dotted #808080;
            font-weight: bold;
            text-align: left;
            border-bottom: 1px solid black;
        }   
    </style>

    <div class="container-fluid center">
        <div class="title-header">
            APLICACIÓN DE GUÍA DE CONTROL <br/>
            <?=$auditoria?> <br/>
            <?=$nombre_lista?> <br/>
            AÑO <?= $year ?>
        </div>    
    </div>

    <!-- Docs master nav -->
    <div class="page center">
        <?php
        $nshow= 0;
        $nhide= 0;
        $_componente= null;                 
        $_id_capitulo= null;
        $_id_tipo_lista= null;

        $obj= new Tlista_requisito($clink);
        $obj->SetIdLista($id_lista);
        $obj->SetYear($year == -1 ? null : $year);
        $obj->listar();
        $nhide= $obj->GetCantidad();
        ?>   

        <table cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead" width="150px">Peso</th>
                    <th class="plhead">Requisitos a Evaluar</th>
                    <th class="plhead">Evidencias</th>
                    <th class="plhead">Indicaciones al Equipo Evaluador</th>
                </tr>
            </thead>

            <tbody>
            <?php 
                for ($_componente = 1; $_componente < _MAX_COMPONENTES_CI; $_componente++) { 
                    if (!empty($componente) && $_componente != $componente)
                        continue;

                    $obj->SetComponente($_componente);
                    $obj->SetIdCapitulo(null);
                    $obj->SetIdTipo_lista(null);
                    $obj->listar();
                    $cant= $obj->GetCantidad();
                    
                    if (empty($cant))
                        continue;
                ?>
                    <tr>
                        <td colspan="5"  class="colspan plinner left right">
                            <?=$_componente.') '. $Tambiente_control_array[$_componente]?>
                        </td>
                    </tr>

                    <?php
                    $numero= $_componente;
                    lista_requisito($_componente, 0);

                    if (isset($obj_tipo1)) unset($obj_tipo1);
                    $obj_tipo1= new Ttipo_lista($clink);

                    $obj_tipo1->SetYear($year == -1 ? null : $year);
                    $obj_tipo1->SetIdLista($id_lista);
                    $obj_tipo1->SetComponente($_componente);
                    $result1= $obj_tipo1->listar();

                    while ($row1= $clink->fetch_array($result1)) {
                        if (!empty($row1['id_capitulo']))
                            continue;
                        $_id_capitulo= $row1['id'];
                        if (!empty($id_capitulo) && $_id_capitulo != $id_capitulo)
                            continue;

                        $obj->SetComponente($_componente);
                        $obj->SetIdTipo_lista($_id_capitulo);
                        $obj->listar();
                        $cant1= $obj->GetCantidad();
                        
                        if (empty($cant1))
                            continue;                            
                    ?>
                        <tr>
                            <td colspan="5"  class="colspan plinner left right">
                                <?=$_componente.",".$row1['numero'].") ". $row1['nombre']?>
                            </td>
                        </tr>

                        <?php
                        $numero= $row1['numero'];
                        lista_requisito($_componente, $_id_capitulo);

                        if (isset($obj_tipo2)) unset($obj_tipo2);
                        $obj_tipo2= new Ttipo_lista($clink);

                        $obj_tipo2->SetYear($year == -1 ? null : $year);
                        $obj_tipo2->SetIdLista($id_lista);
                        $obj_tipo2->SetComponente($_componente);
                        $obj_tipo2->SetIdCapitulo($_id_capitulo);
                        $result2= $obj_tipo2->listar();

                        while ($row2= $clink->fetch_array($result2)) {
                            $_id_subcapitulo= $row2['id'];
                            if (!empty($id_subcapitulo) && $_id_subcapitulo != $id_subcapitulo)
                                continue;

                            $numero= $row2['numero'];
                            lista_requisito($_componente, $_id_subcapitulo);
                        ?>                    
                <?php } } } ?>  
            </tbody>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

<?php  
function lista_requisito($componente, $id_tipo_lista= 0) {
    global $clink;
    global $obj_reg;
    global $Tcriterio_array;
    
    global $year;
    global $id_lista;
    global $id_auditoria;
    global $nshow;
    global $nhide;

    global $numero;
    
    $obj= new Tlista_requisito($clink);
    $obj->SetIdLista($id_lista);
    $obj->SetYear($year == -1 ? null : $year);
    $obj->SetComponente($componente);
    $obj->SetIdTipo_lista($id_tipo_lista);

    $result= $obj->listar();
    $cant= $obj->GetCantidad();
    if (empty($cant))
        return 0;

    $i= 0;
    $nshow= 0;
    $array_ids= array();
    $clink->data_seek($result);
    while ($row= $clink->fetch_array($result)) {
        if (isset($array_ids[$row['_id']]))
            continue;
        $array_ids[$row['_id']]= 1;
        ++$i;
        ++$nshow;
        --$nhide;
    ?>

    <tr>
        <td class="plinner left">
            <?php
            echo $numero.'.'.$row['numero_plus'].')';

            $obj_reg->SetIdAuditoria($id_auditoria);
            $obj_reg->SetIdRequisito($row['_id']);
            $array= $obj_reg->getNota_reg();
            $cumplimiento= !is_null($array['cumplimiento']) ? $array['cumplimiento'] : 0;
            ?>
        </td>

        <td class="plinner">
            <?=$Tcriterio_array[$cumplimiento+1][0]?>
        </td>

        <td class="plinner">
            <?= textparse($row['nombre'])?>
        </td>
        <td class="plinner">
            <?= textparse($row['evidencia'])?>
        </td>
        <td class="plinner">
            <?= textparse($row['indicacion'])?>
        </td>
    </tr>
    <?php 
    }
}
?>