<?php
$array_proyectos= array();

$obj_pry= new Tproyecto($clink);
$obj_pry->SetYear($year);

$obj_pry->SetIdUsuario($_SESSION['id_usuario']);
$obj_pry->GetProyectosUser(false);
$array_proyectos= array_merge_overwrite($array_proyectos, $obj_pry->array_proyectos);

if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
    $obj_pry->SetIdUsuario(null);
    $obj_pry->SetIdProceso($_SESSION['id_entity']);
    $obj_pry->GetProyectosProceso();

    $array_proyectos= array_merge_overwrite($array_proyectos, $obj_pry->array_proyectos);
}

if ($acc == _ACCESO_BAJA && $_SESSION['usuario_proceso_id'] != $_SESSION['id_entity']) {
    $obj_pry->SetIdUsuario(null);
    $obj_pry->SetIdProceso($_SESSION['usuario_proceso_id']);
    $obj_pry->GetProyectosProceso();

    $array_proyectos= array_merge_overwrite($array_proyectos, $obj_pry->array_proyectos);
}

$cant_pry= 0;
foreach ($array_proyectos as $pry) {
    if (!empty($id_programa) && $pry['id_programa'] == $id_programa)
        continue;
    ++$cant_pry; 
    if (empty($id_proyecto))    
        $id_proyecto= $pry['id'];
}
reset($array_proyectos);

if (!empty($id_proyecto)) {
    $obj_proj = new Tproyecto($clink);
    $obj_proj->SetIdProyecto($id_proyecto);
    $obj_proj->Set();
    $nombre_pry= $obj_proj->GetNombre();
    $id_responsable = $obj_proj->GetIdResponsable();
    $id_proceso = $obj_proj->GetIdProceso();

    $obj_user = new Tusuario($clink);
    $email = $obj_user->GetEmail($id_responsable);
    $responsable = $email['nombre'];
    $pRes = $email['usuario'];
    if (!empty($email['cargo']))
        $responsable .= ", " . textparse($email['cargo']);
    $nombre_proj = $obj_proj->GetNombre();

    $pStart = $obj_proj->GetFechaInicioPlan();
    $pEnd = $obj_proj->GetFechaFinPlan();

    $pComp = 0;
} 

$if_jefe = false;
if (!empty($id_proyecto) && $array_proyectos[$id_proyecto]['id_responsable'] == $_SESSION['id_usuario']) 
    $if_jefe = true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe = true;

if (!empty($id_proyecto)) {
    $obj_prs= new Tproceso_item($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdPrograma($id_programa);
    $obj_prs->SetIdProyecto($id_proceso);
    $array_procesos= $obj_prs->GetProcesosProyecto();

    if ($acc == _ACCESO_BAJA && array_key_exists($_SESSION['usuario_proceso_id'], $array_procesos) == true)
        $if_jefe = true;
    if ($id_responsable == $_SESSION['id_usuario'])
        $if_jefe = true;    
}
?>

<script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>


<div id="navbar-secondary">
    <nav class="navd-content">  
        <div class="navd-container">
            <div id="dismiss" class="dismiss">
                <i class="fa fa-arrow-left"></i>
            </div>           
            <a href="#" class="navd-header">
                SEGUIMIENTO A LOS PROYECTOS
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navd-collapse">
                    <?php if ($if_jefe) { ?>
                    <?php if (empty($id_proyecto)) { ?>
                    <li id="li-add-proyecto" class="nav-item d-none d-lg-block">
                        <a class="icon" href="javascript:add_project()" title="nuevo proyecto">
                            <i class="fa fa-plus"></i>Nuevo proyecto
                        </a>
                    </li>
                    <?php } else { ?>
                    <li class="nav-item d-none d-lg-block">
                        <a class="icon" href="javascript:add_tarea()" title="nuevo proyecto">
                            <i class="fa fa-plus"></i>Agregar tarea
                        </a>
                    </li>
                    <?php } } ?>

                    <?php if ($if_jefe) { ?>
                    <li class="navd-dropdown d-none d-md-block">
                        <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                            <i class=" fa fa-cog"></i>Opciones
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarOpciones">
                            <li class="nav-item">
                                <a class="icon" href="javascript:add_project()" title="nuevo proyecto">
                                    <i class="fa fa-plus"></i>Nuevo proyecto
                                </a>
                            </li>

                            <?php if (!empty($id_proyecto)) { ?>
                            <li class="nav-item">
                                <a href="#" onclick="edit_project()" title="modificar/editar proyecto">
                                    <i class="fa fa-pencil"></i>Editar proyecto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="icon" href="javascript:add_tarea()" title="nuevo proyecto">
                                    <i class="fa fa-plus"></i>Agregar tarea
                                </a>
                            </li>
                            <?php if ($signal == "jkanban") { ?>
                            <li class="nav-item">
                                <a href="#" onclick="displayWindow('div-ajax-panel');" title="nueva columna">
                                    <i class="fa fa-plus"></i>Agregar columna
                                </a>
                            </li>
                            <?php 
                                }  
                            }  
                            ?>
                        </ul>
                    </li>
                    <?php } ?>

                    <?php
                    if (!is_null($array_proyectos) && $cant_pry > 0) {
                    ?>
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarProyectos" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-tasks"></i>Proyectos
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarProyectos">
                            <?php
                            reset($array_proyectos);
                            foreach ($array_proyectos as $pry) {
                                if (!empty($id_programa) && $pry['id_programa'] == $id_programa)
                                    continue;

                                $str = str_replace("\n", "", nl2br($pry['descripcion']));
                                $str = str_replace("\r", "", $str);
                            ?>
                            <li class="nav-item <?php if ($pry['id'] == $id_proyecto) echo "active" ?>">
                                <a href="#" onclick="loadproyecto(<?= $pry['id'] ?>)" title="<?= $str ?>">
                                    <?= $pry['nombre'] ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>

                    <?php
                    $id_proceso = $id_tablero;

                    $obj_prog = new Tprograma($clink);
                    $obj_prog->SetYear($year);
                    $obj_prog->SetIdProceso($_SESSION['id_entity']);
                    $result = $obj_prog->listar();
                    $cant_prog = $obj_prog->GetCantidad();

                    if ($cant_prog > 0) {
                    ?>
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarProgramas" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-product-hunt"></i>Programas<b class="caret"></b>
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarProgramas">
                            <?php while ($row = $clink->fetch_array($result)) { ?>
                            <li class="<?php if ($row['_id'] == $id_programa) echo "active" ?>">
                                <a href="#" onclick="_dropdown_prog(<?= $row['_id'] ?>)"
                                    title="<?= "{$row['_nombre']} {$row['_inicio']}/{$row['_fin']}" ?>">
                                    <?= "{$row['_nombre']} {$row['_inicio']}/{$row['_fin']}" ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>

                    <?php
                    $use_select_year = true;
                    $use_select_month = true;
                    $use_select_day = true;
                    require "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <li class="nav-item d-none d-lg-block">
                        <a href="#" class="" onclick="imprimir()">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">                            
                        <li class="nav-item">
                            <a href="#" onclick="open_help_window('../help/manual.html')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>
                </div>
            </div>  
        </div>      
    </nav>
</div>

<div id="navbar-third" class="app-nav d-none d-md-block">
    <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">   
        <li class="col">
            <label class="badge badge-success">
                <?= (int)$day ?> de <?= $meses_array[(int)$month] ?>, <?= $year ?>
            </label>
        </li>
        <li class="col">
            <div class="row">
                <label class="label ml-3">Proyecto:</label>
                <label class="badge badge-danger">
                    <?= $nombre_proj ?>
                </label>            
            </div>
        </li>

        <li class="col">
            <div class="row">
                <label class="label ml-3">Responsable:</label>
                <label class="badge badge-warning">
                    <?= $responsable ?>
                </label>
            </div>
        </li>
    </ul> 
</div>


<input type="hidden" id="menu" name="menu" value="tablero" />
<input type="hidden" id="signal" name="signal" value="<?=$signal?>" />

<input type="hidden" name="exect" id="exect" value="<?= $if_jefe ? 'add' : 'list' ?>" />

<input type="hidden" id="id_calendar" name="id_calendar" value="<?= $id_calendar ?>" />

<input type="hidden" id="id_proyecto" name="id_proyecto" value="<?= $id_proyecto ?>" />
<input type="hidden" id="proyecto" name="proyecto" value="<?= $id_proyecto ?>" />

<input type="hidden" id="id_programa" name="id_programa" value="<?= $id_programa ?>" />
<input type="hidden" id="programa" name="programa" value="<?= $id_programa ?>" />

<input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
<input type="hidden" id="proceso" name="proceso" value="<?= $id_proceso ?>" />

<input type="hidden" id="fecha_origen" name="fecha_origen"
    value="<?= $pStart ? date('d/m/Y', strtotime($pStart)) : null ?>" />
<input type="hidden" id="fecha_termino" name="fecha_termino"
    value="<?= $pEnd ? date('d/m/Y', strtotime($pEnd)) : null ?>" />

<input type="hidden" id="year" name="year" value="<?= $year ?>" />
<input type="hidden" id="month" name="month" value="<?= $month ?>" />
<input type="hidden" id="day" name="day" value="<?= $day ?>" />

<input type="hidden" id="tarea" name="tarea" value="" />
<input type="hidden" id="id_tarea" name="id_tarea" value="" />
<input type="hidden" id="descripcion" name="descripcion" value="" />
<input type="hidden" id="responsable" name="responsable" value="" />
<input type="hidden" id="fecha_inicio" name="fecha_inicio" value="" />
<input type="hidden" id="fecha_plan" name="fecha_plan" value="" />
<input type="hidden" id="avance" name="avance" value="" />
<input type="hidden" id="numero" name="numero" value="" />

<input type="hidden" id="if_jefe" name="if_jefe" value="<?= $if_jefe ? 1 : 0 ?>" />