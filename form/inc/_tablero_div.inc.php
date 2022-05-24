<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 18/02/15
 * Time: 20:44
 */

?>

    <div id="win-board-signal" class="card card-primary ajax-panel win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-title" class="panel-title win-drag col-11 m-0"></div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href= "javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>                           
                    </div>
                </div>                
            </div>            
        </div>

        <div class="card-body">
             <div id="win-board-signal-project-icon" class="btn-toolbar">
                <a class="btn btn-app btn-info btn-sm" title="ver gráficos" onclick="grafico_indicador()">
                    <i class="fa fa-bar-chart"></i>Graficar
                </a>  
                <a id="img_planning" class="btn btn-app btn-primary btn-sm" title="planificar" onclick="plan_indicador()" >
                    <i class="fa fa-flask"></i>Planificar
                </a>  
                <a id="img_register" class="btn btn-app btn-primary btn-sm" title="ingresar datos reales" onclick="real_indicador()" >
                    <i class="fa fa-calculator"></i>Registrar
                </a>  
                <a class="btn btn-app btn-info btn-sm" title="adjuntar documentos al indicador" onclick="showWindow('<?=$action?>')" >
                    <i class="fa fa-file-text"></i>Documentos
                </a>                   
                <a id="img_edit" class="btn btn-app btn-warning btn-sm" title="editar" onclick="edit_indicador('<?=$signal?>','<?=$action?>')" >
                    <i class="fa fa-edit"></i>Editar
                </a>  

                <?php if ($action != 'list') { ?>
                <a id="img_delete" class="btn btn-app btn-danger btn-sm" title="eliminar indicador y sus datos" onclick="delete_indicador('<?=$signal?>')" >
                        <i class="fa fa-trash"></i>Eliminar
                </a>  
                <?php } ?>  
             </div>  

            <div class="badge bg-blue" style="font-size: 1.5em; color:white;">REAL</div>
            
            <div class="box-comments">
                <div class="box-comment">
                    <div class="comment">Registro:</div>
                    <div class="comment-text" id="registro_real"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Valor:</div>
                    <div class="comment-text" id="valor_real"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Observaciones:</div>
                    <div class="comment-text" id="observacion_real"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Responsable:</div>
                    <div class="comment-text" id="responsable_real"></div>
                </div>
            </div>  
            
            <div class="badge bg-blue"style="font-size: 1.5em; color:white;">PLAN</div>

            <div class="box-comments">    
                <div class="box-comment">
                    <div class="comment">Registro:</div>
                    <div class="comment-text" id="registro_plan"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Plan:</div>
                    <div class="comment-text" id="valor_plan"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Observaciones:</div>
                    <div class="comment-text" id="observacion_plan"></div>
                </div>
                <div class="box-comment">
                    <div class="comment">Responsable:</div>
                    <div class="comment-text" id="responsable_plan"></div>
                </div>
            </div>
        </div>
    </div>    



    <div id="bit" class="loggedout-follow-normal" style="width: 60%;">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>
        <div id="bitsubscribe">
            <div class="row">
                <div class="col-6">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src="../img/alarm-dark.ico">
                            Sobrecumplido al 110% o m&aacute;s
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-blue.ico">
                            Sobrecumplido al 105% o m&aacute;s, menor que el 110% de Sobrecumplimiento
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-green.ico">
                            Éxito. Estado de cumplimiento igual o mayor que 95% y menor que el 105% de Sobrecumplimiento
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-yellow.ico">
                            Cumplimiento igual o mayor que 90% y menor que el 95%
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-orange.ico">
                            Estado de cumplimiento mayor o igual al 85% y menor que 90% 
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-red.ico">
                            Fracaso. Cumplimiento menor 85%
                        </li>
                    </ul>
                </div>    
                <div class="col-6">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src="../img/arrow-green.ico">
                            Mejora referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-yellow.ico">
                            Sin Cambios referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-red.ico">
                            Empeora referido al periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/arrow-blank.ico">
                            No hay datos en periodo anterior
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-blank.ico">
                            No existen datos
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/alarm-null.ico">
                            No hay valor del Plan o Criterio de Éxito
                        </li>
                    </ul>
                </div>    
            </div>

            <label class="text">
                <sup class="font-size:1.2em!important">*</sup>Los valores que aparecen en esta leyenda son los que se utilizan por defecto. cada indicador puede tener sus valores de escala especificos.
            </label>

        </div> <!-- bitsubscribe -->
    </div> <!-- bit -->
    

    <div id='div-ajax-graph-select-panel' class="card card-primary ajax-panel div-ajax-graph-select-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-graph-select-panel');">
                            <i class="fa fa-close"></i>
                        </a>    
                   </div>                      
                </div>
            </div>
        </div>
        <div id='div-ajax-graph-select' class='panel-body'>
        </div>
    </div> 
    
