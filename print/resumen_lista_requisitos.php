<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2022
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/document.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_auditoria.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";
require_once "../php/class/traza.class.php";

$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$year = !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_lista = !is_null($_GET['id_lista']) ? $_GET['id_lista'] : null;

$obj_lista = new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->Set($id_lista);
$id_lista_code = $obj_lista->get_id_code();
$nombre_lista = $obj_lista->GetNombre();

$obj_prs = new Tproceso($clink);

$proceso= $_SESSION['entity_nombre'];
$tipo_prs= $_SESSION['entity_tipo'];
$conectado= $_SESSION['entity_conectado'];

$obj_reg = new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdLista($id_lista);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($_SESSION['id_entity']);
$obj_traza->add("IMPRIMIR", "TABLA DE RESULTADOS DE APLICACIÓN DE GUÍA DE CONTROL", "Corresponde a periodo año: $year");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>TABLA DE RESULTADOS DE APLICACIÓN DE GUÍA DE CONTROL</title>

    <?php require "inc/print_top.inc.php";?>

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <style>
        .capitulo {
            padding-left: 20px;
            min-width: 200px;;
        }
        .column {
            width: 50px;
            text-align: center;
        }
    </style>
</head>

<body>
<script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

<!-- Docs master nav -->
<div class="page center">
    <div class="container-fluid">
        <div class="title-header justify-content-center">
        TABLA DE RESULTADOS DE APLICACIÓN DE GUÍA DE CONTROL <br/>
        <?=$nombre_lista?> <br/>
        AÑO <?= $year ?>
        </div>    
    </div>

    <?php 
    $obj_reg = new Tregister_nota($clink);
    $obj_reg->SetYear($year);
    $obj_reg->SetIdLista($id_lista);
    $obj_reg->SetIdProceso($id_proceso= $id_proceso == -1 || empty($id_proceso) ? null : $id_proceso);

    $obj_tipo= new Ttipo_lista($clink);
    $obj_tipo->SetYear($year);
    $obj_tipo->SetIdAuditoria($id_auditoria);
    $obj_tipo->SetIdLista($id_lista);
    $obj_tipo->SetIdProceso($id_proceso= $id_proceso == -1 || empty($id_proceso) ? $_SESSION['id_entity'] : $id_proceso);

    $obj = new Tlista_requisito($clink);
    $obj->SetIdLista($id_lista);
    $obj->SetYear($year);
    $obj->SetIdAuditoria($id_auditoria);
    $obj->SetIdProceso($id_proceso);

    $array_requisitos= array();
    
    for ($componente= 1; $componente < _MAX_COMPONENTES_CI; $componente++) {
        $array_requisitos[$componente][0]= $array_null; 
        
        $id_capitulo= 0;
        $obj->SetComponente($componente);
        $obj->SetIdTipo_lista($id_capitulo);
        $result = $obj->listar(true);
    
        compute_requsitos($result);

        $obj_tipo->SetComponente($componente);
        $result_tipo= $obj_tipo->listar($componente);

        $array_ids= array();
        while ($row_tipo= $clink->fetch_array($result_tipo)) {
            if ($array_ids[$row_tipo['id']])
                continue;
            $array_ids[$row_tipo['id']]= 1;

            if (!empty($row['id_capitulo']))
                continue;
            
            $id_capitulo= $row_tipo['id'];
            $capitulo= $row_tipo['nombre'];

            $obj->SetIdTipo_lista($row_tipo['id']);
            $result = $obj->listar(true);

            compute_requsitos($result);
        }    
    }    
    ?>

    <div class="row col-12 mb-4">
        <table>
            <thead>
                <tr>
                    <th>Aspectos por Componente y Capitulos</th>
                    <th>TOTALES</th>
                    <th class="column">SI</th>
                    <th class="column">NO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($componente= 1; $componente < _MAX_COMPONENTES_CI; $componente++) { 
                ?>
                    <tr>
                        <td>
                            <?=$Tambiente_control_array[$componente]?>
                        </td>
                        <td>
                            <?=$array_requisitos[$componente][0]['total']?>
                        </td>
                        <td>
                            <?=$array_requisitos[$componente][0]['se_cumple']?>
                        </td>
                        <td class="right">
                            <?=$array_requisitos[$componente][0]['no_se_cumple']?>
                        </td>
                    </tr>
                    <?php
                    $obj_tipo->SetComponente($componente);

                    $result_tipo= $obj_tipo->listar($componente);

                    $array_ids= array();
                    while ($row_tipo= $clink->fetch_array($result_tipo)) {
                        if ($array_ids[$row_tipo['id']])
                            continue;
                        $array_ids[$row_tipo['id']]= 1;

                        if (!empty($row_tipo['id_capitulo'])) 
                            continue;
                        
                        $id_capitulo= $row_tipo['id'];
                        $capitulo= $row_tipo['nombre'];
                        ?>
                        <tr>
                            <td class="capitulo">
                                <?=$capitulo?>
                            </td>
                            <td>
                                <?=$array_requisitos[$componente][$id_capitulo]['total']?>
                            </td>
                            <td>
                                <?=$array_requisitos[$componente][$id_capitulo]['se_cumple']?>
                            </td>
                            <td class="right">
                                <?=$array_requisitos[$componente][$id_capitulo]['no_se_cumple']?>
                            </td>
                        </tr>            
                <?php  
                    }
                } 
                ?>

                <tr>
                    <td>TOTALES</td>
                    <td>
                        <?=$array_requisitos[0][0]['total']?>
                    </td>
                    <td>
                        <?=$array_requisitos[0][0]['se_cumple']?>
                    </td>
                    <td class="right">
                        <?=$array_requisitos[0][0]['no_se_cumple']?>
                    </td>                    
                </tr>
            </tbody>
        </table>
    </div>                

<?php require "inc/print_bottom.inc.php";?>

<?php
function compute_requsitos($result) {
    global $clink;

    global $array_requisitos;
    global $componente;
    global $id_capitulo;
    global $obj_reg;
    
    $array_null= array('total'=>0, 'no_procede'=>0, 'no_se_cumple'=>0, 'en_proceso'=>0, 
                        'se_cumple'=>0, 'no_definido'=>0);

    $array_requisitos[$componente][$id_capitulo]= $array_null;

    while ($row= $clink->fetch_array($result)) {
        $obj_reg->SetIdRequisito($row['_id']);
        $array = $obj_reg->getNota_reg(null, null, null, null, false);  
        
        ++$array_requisitos[$componente][$id_capitulo]['total'];                
        ++$array_requisitos[0][0]['total'];

        switch ($array['cumplimiento']) {
            case _NO_PROCEDE:
                ++$array_requisitos[$componente][$id_capitulo]['no_procede'];
                ++$array_requisitos[0][0]['no_procede'];
                break;
            case _NO_SE_CUMPLE:
                ++$array_requisitos[$componente][$id_capitulo]['no_se_cumple'];
                ++$array_requisitos[0][0]['no_se_cumple'];
                break;
            case _EN_PROCESO:
                ++$array_requisitos[$componente][$id_capitulo]['en_proceso'];
                ++$array_requisitos[0][0]['en_proceso'];
                break;
            case _SE_CUMPLE:
                ++$array_requisitos[$componente][$id_capitulo]['se_cumple'];
                ++$array_requisitos[0][0]['se_cumple'];
                break;
        } 
    }
}

?>