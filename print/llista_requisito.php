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
require_once "../php/class/usuario.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/traza.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : 0;
$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : 0;
$componente= !empty($_GET['componente']) ? $_GET['componente'] : 0;
$id_capitulo= !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : 0;
$id_subcapitulo= !empty($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : 0;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$obj_lista= new Tlista($clink);
$obj_lista->SetIdLista($id_lista);
$obj_lista->Set();
$nombre_lista= $obj_lista->GetNombre();

$obj= new Tlista_requisito($clink);
$obj->SetIdLista($id_lista);
$obj->SetYear($year);

$obj_prs= new Tproceso($clink);

$_id_proceso= empty($_GET['id_proceso']) || $_GET['id_proceso'] == -1 ? $_SESSION['id_entity'] : $_GET['id_proceso'];

if (!empty($_id_proceso)) {
   $obj_prs->SetIdProceso($_id_proceso);
   $obj_prs->Set();
   $proceso= $obj_prs->GetNombre();
   $conectado= $obj_prs->GetConectado();
   $tipo_prs= $obj_prs->GetTipo();
}

$obj_tipo1= new Ttipo_lista($clink);
$obj_tipo1->SetYear($year);
$obj_tipo1->SetIdLista($id_lista);

$obj_tipo2= new Ttipo_lista($clink);
$obj_tipo2->SetYear($year);
$obj_tipo2->SetIdLista($id_lista);

$obj_tipo3= new Ttipo_lista($clink);
$obj_tipo3->SetYear($year);
$obj_tipo3->SetIdLista($id_lista);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "GUÍA DE CONTROL", "Corresponde a periodo año: $year");
?>

<html>

<head>
    <title>GUÍA DE CONTROL</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="page center">
        <table class="center none-border" width="100%">
            <?php if (!empty($array_aprb)) { ?>
            <tr>
                <td class="none-border">
                    Aprobado por: <?=$array_aprb['cargo']?><br />
                    <span style="margin-left: 70px"><?=$array_aprb['nombre']?></span><br />
                    <?php if (!is_null($array_aprb['firma'])) {?>
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>"
                        border="0" />
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td class="center none-border">
                    <h1>GUÍA DE CONTROL <?=$nombre_lista?> <br/> AÑO <?=$year?></h1>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="page center">
        <?php $colspan= 16; ?>
        <table class="center" width="100%">
            <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead">ASPECTOS A VERIFICAR</th>

                    <?php if ($id_auditoria && $evaluado) { ?>
                    <th class="plhead">EVALUACIÓN</th>
                    <th class="plhead">OBSERVACIÓN</th>
                    <th class="plhead">REGISTRO</th>

                    <?php } else { ?>
                    <th class="plhead">INDICACIÓN</th>
                    <?php } ?>
                </tr>
            </thead>

            <tbody>
                <?php
                for ($_componente= 1; $_componente < _MAX_COMPONENTES_CI; ++$_componente) {
                    if (!empty($componente) && $_componente != $componente)
                        continue; 
                    $ktotal= 0;

                    $print_bar0= false;
                    $print_bar1= false;
                    $print_bar2= false;

                    $header0= null;
                    $header1= null;
                    $header2= null;

                    $obj->SetComponente($_componente);
                    $obj->SetIdTipo_lista(0);
                    $header0=  number_format_to_roman($_componente).'. '.$Tambiente_control_array[$_componente];
                    $numero= $_componente;
                    $num_rows= anual_plan($header0, null, null);

                    $result1= $obj_tipo1->listar($_componente, 0);
                    $k= 0;
                    while ($row1= $clink->fetch_array($result1)) {
                        if (!empty($row1['id_capitulo']))
                            continue; 
                        $_id_capitulo= $row1['id'];
                        if (!empty($id_capitulo) && $_id_capitulo != $id_capitulo)
                            continue;

                        ++$k;
                        $obj->SetIdTipo_lista($_id_capitulo);
                        $print_bar1= false;
                        $numero= $row1['numero'];
                        $header1= (!empty($numero) ? "$numero) " : "{$_componente}.{$k}) "). " {$row1['nombre']}";
                        
                        $num_rows1= anual_plan($header0, $header1, null);

                        $result2= $obj_tipo2->listar($_componente, $_id_capitulo);
                        $z= 0;
                        while ($row2= $clink->fetch_array($result2)) {
                            $_id_subcapitulo= $row2['id'];
                            if (!empty($id_subcapitulo) && $_id_subcapitulo != $id_subcapitulo)
                                continue;
                            ++$z;
                            $obj->SetIdTipo_lista($_id_subcapitulo);
                            $print_bar2= false;
                            $numero= $row2['numero'];
                            $header2= "$numero) {$row2['nombre']}";
                            
                            $num_rows2= anual_plan($header0, $header1, $header2);
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>

    <?php
    if ($id_proceso > 0) {
        $obj_prs= new Tproceso($clink);
        $obj_prs->Set($id_proceso);
        $id_responsable= $obj_prs->GetIdResponsable();
        $nombre_prs= $obj_prs->GetNombre();
    }

    if ($id_proceso == -1) {
        $nombre_prs= "Todas las Unidades Organizativas";
    }
    
    $obj_user= new Tusuario($clink);
    $email= $obj_user->GetEmail($_SESSION['id_usuario']);
    ?>

    <div class="page" style="margin-top: 60px;">
        <table>
            <tr>
                <td class="none-border" width="70%"></td>
                <td class="none-border">
                    <div class="container-fluid pull-right">
                        <strong>Elaborado por:</strong><br />
                        <?=$nombre_prs?><br />
                        <?=$email['nombre']?><br />
                        <?=$email['cargo'] ? "{$email['cargo']}<br />" : ""?>
                        <?php if (!is_null($email['firma'])) { ?>
                        <img id="img"
                            src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_responsable?>"
                            border="0" />
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

<?php
function anual_plan($header0, $header1= null, $header2= null) {
    global $clink;
    global $obj;
    global $ktotal;

    global $print_bar0;
    global $print_bar1;
    global $print_bar2;
    global $numero;

    global $id_proceso;
    global $id_auditoria;
    global $evaluado;

    $obj->SetIdProceso($id_proceso > 0 ? $id_proceso : null);
    $result= $obj->listar();
    $cant= $obj->GetCantidad();

    if (empty($cant))
        return 0;
    ?>

    <?php if ($cant > 0 && !$print_bar0) { ?>
    <tr>
        <td colspan="19" class="colspan plinner left top">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header0?></div>
        </td>
    </tr>
    <?php
        $print_bar0= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header1) && !$print_bar1) { ?>
    <tr>
        <td colspan="19" class="colspan plinner left top">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header1?></div>
        </td>
    </tr>
    <?php
        $print_bar1= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header2) && !$print_bar2) { ?>
    <tr>
        <td colspan="19" class="colspan plinner left">
            <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header2?></div>
        </td>
    </tr>
    <?php
        $print_bar2= true;
    }
    ?>

    <?php
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$ktotal;
    ?>

    <tr>
        <td class="plinner left"><?= $numero.".".$row['numero'] ?></td>

        <td class="plinner" style="min-width: 350px;">
            <?= textparse($row['nombre'])?>
        </td>

        <?php
        if ($id_auditoria && $evaluado) {
            // $obj_reg->SetIdRequisito();
            // $row_cump= $obj_reg->getLista_reg();
        ?>
            <td class="plinner">
                <?=$row_cump['cumplimiento']?>
            </td>
            <td class="plinner">
                <?= textparse($row_cump['observacion'])?>
            </td>
            <td class="plinner">
                <?= time2odbc($row_cump['reg_fecha'])?>
            </td>
        <?php } else { ?>
            <td class="plinner">
                <?= textparse($row['indicacion']) ?>
            </td>
        <?php } ?>
    </tr>

    <?php
    }

    return $j;
}
?>