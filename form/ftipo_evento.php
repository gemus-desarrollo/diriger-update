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
require_once "../php/class/tipo_evento.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttipo_evento($clink);
}

$year = !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year= date('Y');

$_inicio= $year - 5;
$_fin= $year + 5;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$empresarial= !empty($_GET['empresarial']) ? $_GET['empresarial'] : $obj->GetIfEmpresarial();
$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$inicio= !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
$inicio= !empty($_GET['inicio']) ? $_GET['fin'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

if (empty($year))
    $year= date('Y');
if (empty($inicio))
    $inicio= $year;
if (empty($fin))
    $fin= $year;

if (empty($empresarial))
    $empresarial= _FUNCIONAMIENTO_INTERNO;
$id_subcapitulo= !is_null($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : null;

$obj->SetIdProceso($id_proceso);

$max_year= null;
if ($action == 'add' && !empty($id_subcapitulo)) {
    $obj->Set($id_subcapitulo);
    $max_year= $obj->GetYear();
    $nombre= null;
    $descripcion= null;
}

if (is_null($id_subcapitulo))
    $id_subcapitulo= $obj->GetIdSubcapitulo();

if ($action != 'add' && !empty($id_subcapitulo)) {
    $obj_temp= new Ttipo_evento($clink);
    $obj_temp->SetIdProceso($id_proceso);
    $obj_temp->Set($id_subcapitulo);
    $max_year= $obj_temp->GetYear();
}

$id= $action == 'update' ? $obj->GetIdTipo_evento() : 0;
$numero= null;

if ($action == 'add') {
    $obj->SetIfEmpresarial($empresarial);
    if (!empty($id_subcapitulo))
        $obj->SetIdSubcapitulo($id_subcapitulo);
}

$_fin= ($action == 'update' || ($action == 'add' && !empty($id_subcapitulo))) ? $max_year ? $max_year : $_fin : $_fin;

$_subcapitulo0= $obj->GetSubcapitulo0();
$_subcapitulo1= $obj->GetSubcapitulo1();

if ($action != 'add') {
    $numero= $obj->GetNumero();
    $_fin = $max_year ? $max_year : $_fin;
} else {
    $obj->SetIfEmpresarial($empresarial);
    $obj->SetIdSubcapitulo(!empty($id_subcapitulo) ? $id_subcapitulo : null);
    $numero= $obj->fix_numero()['numero'];
}

list($_empresarial, $subcapitulo0, $subcapitulo1) = preg_split('/\./', $numero);
$capitulo= $empresarial-1;

if ($action == 'add') {
    if (empty($id_subcapitulo))
        ++$subcapitulo0;
    else
        ++$subcapitulo1;
}

if (empty($subcapitulo0))
    $subcapitulo0= 1;

$show_subcapitulo= !empty($id_subcapitulo) ? true : false;

$url_page= "../form/ftipo_evento.php?signal=$signal&action=$action&menu=tipo_evento&exect=$action";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>TIPO DE ACTIVIDADES EMPRESARIALES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../libs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
        .point {
            margin:3px;
            font-size:1.3em;
            font-weight:bold;
            color:black;
        }
        #ajax-numero {
            border: none;
        }
    </style>

    <script language="javascript">
        function set_capitulo(val) {
            var capitulo= parseInt($("#empresarial").val());
            capitulo= capitulo <= 0 ? 0 : --capitulo;
            $('#capitulo').val(capitulo);
        }

        function set_subcapitulo0(val) {
            $('#subcapitulo0').val(val);
        }

        function set_subcapitulo1(val) {
            $('#subcapitulo1').val(val);
        }

        function set_numero() {
            var capitulo= parseInt($("#empresarial").val());
            capitulo= capitulo <= 0 ? 0 : --capitulo;
            set_capitulo(capitulo);
        }

        function validar() {
            if (!Entrada($('#nombre').val())) {
                $('#nombre').focus(focusin($('#nombre')));
                alert('Introduzca el nombre o título que tendra el nuevo tipo de evento a definir');
                return;
            }

            var numero= $('#capitulo').val()+'.'+$('#subcapitulo0').val();

            <?php if (!empty($subcapitulo1)) { ?>
            if ($('#subcapitulo1').val() > 0)
                numero+= '.'+$('#subcapitulo1').val();
            <?php } ?>

            $('#numero').val(numero);

            document.forms[0].action= '../php/interface.php';
            document.forms[0].submit();
        }

        function refreshp(index) {
            var nombre= encodeURI($('#nombre').val());
            var descripcion= encodeURI($('#descripcion').val());
            var action= $('#exect').val();
            var empresarial= $('#empresarial').val();
            var id_proceso= $('#id_proceso').val();
            var id_subcapitulo= index ? $('#subcapitulo').val() : 0;

            var url= 'ftipo_evento.php?action='+action+'&empresarial='+empresarial+'&id_subcapitulo='+id_subcapitulo;
            url+= '&descripcion=' + descripcion + '&nombre=' + nombre + '&id_proceso=' + id_proceso;
            self.location.href= url;
        }

    </script>

    <script type="text/javascript">
        var focusin;
        $(document).ready(function() {
            new BootstrapSpinnerButton('spinner-capitulo',1,5);
            new BootstrapSpinnerButton('spinner-subcapitulo0',1,255);
            new BootstrapSpinnerButton('spinner-subcapitulo1',1,255);

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error))?>");
            <?php } ?>
        });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

       <div class="app-body form">
            <div class="container">
             <div class="card card-primary">
                 <div class="card-header">TIPO DE ACTIVIDADES EMPRESARIALES</div>
                 <div class="card-body">

                     <form class="form-horizontal" action='javascript:validar()'  method=post>
                         <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />

                         <input type="hidden" name="id" value="<?= $id ?>" />
                         <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                         <input type="hidden" name="menu" value="tipo_evento" />
                         <input type="hidden" id="year" name="year" value="<?= $year ?>" />

                         <input type="hidden" id="numero" name="numero" value="<?= $numero ?>" />

                         <div class="form-group row">
                             <label class="col-form-label col-md-2">
                                 Nombre:
                             </label>
                             <div class=" col-md-10">
                                 <textarea id="nombre" name="nombre" class="form-control" rows="2"><?=$nombre?></textarea>
                             </div>
                         </div>
                         <div class="form-group row">
                             <label class="col-form-label col-md-2">
                                 Capítulo:
                             </label>
                             <div class=" col-md-10">
                                 <select id="empresarial" name="empresarial" class="form-control" onchange="refreshp(0)" >
                                     <?php for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                                         <option value="<?= $i ?>" <?php if ($i == $empresarial) echo "selected='selected'" ?>><?= $tipo_actividad_array[$i] ?></option>
                                     <?php } ?>
                                 </select>
                             </div>
                         </div>
                         <?php if ($show_subcapitulo) { ?>
                             <div class="form-group row">
                                 <label class="col-form-label col-md-2">
                                     SubCapítulo:
                                 </label>
                                 <div class="col-md-10">
                                     <?php
                                     $obj= new Ttipo_evento($clink);
                                     $result= $obj->listar($empresarial, 0);
                                     ?>
                                     <select id="subcapitulo" name="subcapitulo" class="form-control" style="width:560px;" onchange="refreshp(1)" >
                                         <?php while ($row= $clink->fetch_array($result)) { ?>
                                             <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_subcapitulo) echo "selected='selected'" ?>><?=$row['nombre']?></option>
                                         <?php } ?>
                                     </select>
                                 </div>
                             </div>
                         <?php } ?>

                         <div class="form-group row">
                             <label class="col-form-label col-2">
                                 Número:
                             </label>

                             <div class="row col-6">
                                 <div class="row col-12">
                                    <div class="col-3 input-group">
                                        <div id="spinner-capitulo" class="input-group spinner">
                                            <input type="text" name="capitulo" id="capitulo" class="form-control" value="<?= $capitulo ?>" readonly>
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up" disabled>
                                                    <i class="fa">
                                                        <span class="fa fa-caret-up"></span></i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down" disabled>
                                                    <i class="fa">
                                                        <span class="fa fa-caret-down"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3 input-group">
                                        <div id="spinner-subcapitulo0" class="input-group spinner">
                                            <input type="text" name="subcapitulo0" id="subcapitulo0" class="form-control" value="<?= $subcapitulo0 ?>" >
                                            <div class="input-group-btn-vertical">
                                                <button class="btn btn-default" type="button" data-bind="up">
                                                    <i class="fa">
                                                        <span class="fa fa-caret-up"></span></i>
                                                </button>
                                                <button class="btn btn-default" type="button" data-bind="down">
                                                    <i class="fa">
                                                        <span class="fa fa-caret-down"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($subcapitulo1)) { ?>
                                         <div class="col-3 input-group">
                                             <div id="spinner-subcapitulo1" class="input-group spinner">
                                                 <input type="text" name="subcapitulo1" id="subcapitulo1" class="form-control" value="<?= $subcapitulo1 ?>" >
                                                 <div class="input-group-btn-vertical">
                                                     <button class="btn btn-default" type="button" data-bind="up">
                                                         <i class="fa">
                                                             <span class="fa fa-caret-up"></span></i>
                                                     </button>
                                                     <button class="btn btn-default" type="button" data-bind="down">
                                                         <i class="fa">
                                                             <span class="fa fa-caret-down"></span>
                                                         </i>
                                                     </button>
                                                 </div>
                                             </div>
                                         </div>
                                    <?php } ?>
                                </div>
                             </div>


                         </div>
                         <div class="form-group row">
                             <label class="col-form-label col-2">
                                 Válido desde:
                             </label>
                             <div class="col-2">
                                 <select name="inicio" id="inicio" class="form-control input-sm" onchange="refreshpage()">
                                     <?php for ($i= $_inicio; $i <= $_fin; ++$i) { ?>
                                         <option value="<?=$i?>" <?php if ($i == $inicio) echo "selected='selected'"; ?>><?=$i?></option>
                                     <?php } ?>
                                 </select>
                             </div>
                             <label class="col-form-label col-1">
                                 hasta:
                             </label>
                             <div class="col-2">
                                 <select name="fin" id="fin" class="form-control input-sm" onchange="refreshpage()">
                                     <?php for ($i= $_inicio; $i <= $_fin; ++$i) { ?>
                                         <option value="<?=$i?>" <?php if ($i == $fin) echo "selected='selected'"; ?>><?=$i?></option>
                                     <?php } ?>
                                 </select>
                             </div>
                         </div>


                         <div class="form-group row">
                             <label class="col-form-label col-2">
                                 Descripción:
                             </label>
                             <div class="col-10">
                                 <textarea name="descripcion" rows="4" id="descripcion" class="form-control"><?=$descripcion?></textarea>
                             </div>
                         </div>

                           <!-- buttom -->
                           <div class="btn-block btn-app">
                                <?php if ($action == 'update' || $action == 'add') { ?>
                                     <button type="submit" class="btn btn-primary">Aceptar</button>
                                 <?php } ?>
                                <button type="reset" class="btn btn btn-warning" onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                           </div>
                         </form>

                     </div><!-- panel-body -->
                 </div><!-- panel -->
             </div>  <!-- container -->

       </div>

    </body>
</html>
