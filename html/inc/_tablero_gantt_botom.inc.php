
<div id="div-ajax-exec" class="ajax">
</div>

<div id="bit" class="loggedout-follow-normal" style="width: 30%">
    <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>
        <div id="bitsubscribe">
            <div class="row">
                <div class="col-3">
                    <div class="alarm-box small bg-aqua"></div>
                </div>
                <label class="text col-8">
                En espera para iniciar 
                </label>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="alarm-box small bg-green"></div>
                </div>
                <label class="text col-8">
                    Ejecutandose en tiempo / Cumplida
                </label>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="alarm-box small bg-orange"></div>
                </div>
                <label class="text col-8">
                    Detenida o Suspendida
                </label>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="alarm-box small bg-red"></div>
                </div>
                <label class="text col-8">
                    Atrazada o Incumplida
                </label>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="alarm-box small bg-yellow"></div>
                </div>
                <label class="text col-8">
                    Desactualizada
                </label>
            </div>
        </div>    
    </div><!-- #bitsubscribe -->
</div>
<!-- #bit -->

<!-- div-ajax-panel -->
<div id="win-board-signal" class="card card-primary win-board" data-bind="draganddrop">
    <div class="card-header">
        <div class="row">
            <div id="win-ptitle" class="panel-title ajax-title col-11 m-0 win-drag"></div>
            <div class="col-1 m-0 pull-right">
                <div class="close">
                    <a href= "javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div id="win-board-signal-icon" class="btn-toolbar">
            <?php if ($signal == 'jkanban') { ?>
                <a id="img_column" class="btn btn-app btn-secondary btn-sm d-md-inline-block" onclick="mostrar('column')" title="Mover a columna Kanban">
                <i class="fa fa-th"></i>Mover
            </a>            
            <?php } ?>
            <a id="img_dox" class="btn btn-app btn-info btn-sm d-md-inline-block" onclick="mostrar('docs')" title="Gestión de documentos y actas adjuntos">
                <i class="fa fa-file-text"></i>Documentos
            </a>
            <a id="img_register" class="btn btn-app btn-primary btn-sm d-md-inline-block" onclick="mostrar('register')" title="registrar registrar situación o cumplimiento y horas dedicadas">
                <i class="fa fa-check"></i>Registrar
            </a>
            <a id="img_edit" class="btn btn-app btn-warning btn-sm d-md-inline-block" onclick="enviar_tarea('edit')" title="Editar la tarea">
                <i class="fa fa-pencil"></i>Editar
            </a>
            <a id="img_delete" class="btn btn-app btn-danger btn-sm d-md-inline-block" onclick="enviar_tarea('delete')" title="eliminar">
                <i class="fa fa-trash"></i>Eliminar
            </a>

            <?php if ($signal == 'gantt') { ?>
            <?php if ($cant_task > 1) { ?>
            <a id="img_depend" class="btn btn-app btn-warning btn-sm" onclick="mostrar('depend')" title="Realaciones de dependencias de la tarea de otras la tarea">
                <i class="fa fa-retweet"></i>Dependencias
            </a>
            <?php } ?>
            <a id="img_hit" class="btn btn-app btn-warning btn-sm" onclick="mostrar('hit')" title="Planificacion de hitos">
                <i class="fa fa-star"></i>Hitos
            </a>
            <?php } ?>
        </div>

        <div class="col-12">
            <div class="list-group">
                <div class="list-group-item">
                    <label class="text" id=""></label>
                </div>
                <div class="list-group-item">
                    <strong>Tarea</strong>: <label class="text" id="p_tarea"></label>
                </div>
                <div class="list-group-item">
                    <strong>Responsable</strong>: <label class="text" id="p_responsable"></label>
                </div>
                <div class="list-group-item">
                    <strong>Inicio planificado</strong>: <label class="text" id="p_fecha_inicio"></label>
                </div>
                <div class="list-group-item">
                    <strong>Fin planificado</strong>: <label class="text" id="p_fecha_fin"></label>
                </div>
                <div class="list-group-item">
                    <strong>% Avance</strong>: <label class="text" id="p_avance"></label>
                </div>
            </div>
        </div>

        <div class="col-12">
            <label style="font-weight: bold;">Descripción</label>: <p id="p_descripcion"></p>
        </div>        
    </div>
</div>

<!-- div-ajax-panel -->
<div id='div-ajax-panel' class="card card-primary ajax-panel" data-bind="draganddrop">
    <div class="card-header">
        <div class="row win-drag">
            <div class="panel-title ajax-title col-11 win-drag"></div>
            <div class="col-1">
                <div class='close'>
                    <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id='div-ajax' class='card-body'>
    </div>
</div>

<!-- div-ajax-panel-kanban -->
<div id='div-ajax-panel-kanban' class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title ajax-title col-11 win-drag"></div>
                <div class="col-1">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel-kanban');">
                            <i class="fa fa-close"></i>
                        </a>
                   </div>
                </div>
            </div>
        </div>

        <div class='card-body'>
            <ul class="nav nav-tabs mt-1">
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Color</a></li>
            </ul>

            <div class="tabcontent" id="tab1">
                <div class="form-horizontal mt-3">
                    <div class="form-group row col-12">
                        <label class="col-form-label col-2">
                            Titulo:
                        </label>
                        <div class="col-8">
                            <input type="text" id="nombre" name="nombre" class="form-control" />
                        </div>
                    </div>            

                    <div class="form-group row col-12">
                        <label class="col-form-label col-2">
                            Descripción:
                        </label>                    
                        <div class="col-10">
                            <textarea id="descripcion" name="descripcion" class="form-control" style="height: 250px;"></textarea>
                        </div>
                    </div>  
                </div>              
            </div>

            <div class="tabcontent" id="tab2">
                <div class="row col-12">
                    <label class="col-form-label col-2">
                        Color:
                    </label> 
                    <div id="color-color" class="col-2">
                    </div>
                    <div id="color-class" class="col-4 mb-6 clearfix">
                    </div>
                </div>

                <label class="col-form-label col-12 tex-warning">
                    selecione el color:
                </label> 
                <div class="col-12">
                    <div class="color-charts container">
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f9ebea"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f2d7d5"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e6b0aa"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d98880"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #cd6155"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #c0392b"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a93226"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #922b21"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7b241c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #641e16"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fdedec"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fadbd8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f5b7b1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f1948a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ec7063"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e74c3c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #cb4335"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #b03a2e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #943126"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #78281f"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f5eef8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ebdef0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d7bde2"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #c39bd3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #af7ac5"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #9b59b6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #884ea0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #76448a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #633974"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #512e5f"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f4ecf7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e8daef"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d2b4de"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #bb8fce"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a569bd"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #8e44ad"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7d3c98"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #6c3483"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #5b2c6f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #4a235a"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #eaf2f8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d4e6f1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a9cce3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7fb3d5"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #5499c7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2980b9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2471a3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1f618d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1a5276"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #154360"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #ebf5fb"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d6eaf8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #aed6f1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #85c1e9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #5dade2"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #3498db"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2e86c1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2874a6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #21618c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1b4f72"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #e8f8f5"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d1f2eb"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a3e4d7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #76d7c4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #48c9b0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1abc9c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #17a589"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #148f77"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #117864"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #0e6251"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #e8f6f3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d0ece7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a2d9ce"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #73c6b6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #45b39d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #16a085"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #138d75"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #117a65"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #0e6655"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #0b5345"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #e9f7ef"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d4efdf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a9dfbf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7dcea0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #52be80"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #27ae60"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #229954"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1e8449"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #196f3d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #145a32"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #eafaf1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d5f5e3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #abebc6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #82e0aa"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #58d68d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2ecc71"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #28b463"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #239b56"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1d8348"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #186a3b"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fef9e7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fcf3cf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f9e79f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f7dc6f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f4d03f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f1c40f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d4ac0d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #b7950b"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #9a7d0a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7d6608"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fef5e7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fdebd0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fad7a0"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f8c471"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f5b041"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f39c12"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d68910"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #b9770e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #9c640c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7e5109"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fdf2e9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fae5d3"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f5cba7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f0b27a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #eb984e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e67e22"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ca6f1e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #af601a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #935116"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #784212"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fbeee6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f6ddcc"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #edbb99"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e59866"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #dc7633"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d35400"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ba4a00"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a04000"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #873600"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #6e2c00"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #fdfefe"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #fbfcfc"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f7f9f9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f4f6f7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f0f3f4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ecf0f1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d0d3d4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #b3b6b7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #979a9a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7b7d7d"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f8f9f9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #f2f3f4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e5e7e9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d7dbdd"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #cacfd2"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #bdc3c7"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #a6acaf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #909497"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #797d7f"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #626567"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f4f6f6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #eaeded"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d5dbdb"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #bfc9ca"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #aab7b8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #95a5a6"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #839192"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #717d7e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #5f6a6a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #4d5656"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #f2f4f4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #e5e8e8"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #ccd1d1"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #b2babb"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #99a3a4"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #7f8c8d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #707b7c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #616a6b"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #515a5a"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #424949"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #ebedef"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d6dbdf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #aeb6bf"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #85929e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #5d6d7e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #34495e"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2e4053"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #283747"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #212f3c"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1b2631"></div>
                            </div>
                        </div>
                        <div class="color-group">
                            <div class="color-block">
                                <div style="background-color: #eaecee"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #d5d8dc"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #abb2b9"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #808b96"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #566573"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #2c3e50"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #273746"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #212f3d"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #1c2833"></div>
                            </div>
                            <div class="color-block">
                                <div style="background-color: #17202a"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
            
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="add_column_ajax()">Aceptar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel-kanban')">Cancelar</button>
            </div>            
        </div>
    </div>