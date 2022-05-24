    
    <div id='div-ajax-panel' class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                            <i class="fa fa-close"></i>
                        </a>    
                   </div>                      
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row col-12">
                <div class="form-group row">
                    <label class="col-form-label col-2">Desde:</label>
                    <div class="col-4">
                        <div id="div_date_init" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control" id="date_init" name="date_init" readonly value="<?=$date_init?>"  />
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>                            
                    </div>

                    <label class="col-form-label col-2">Hasta:</label>
                    <div class="col-4">
                        <div id="div_date_end" class="input-group date" data-date-language="es">
                            <input type="datetime" class="form-control" id="date_end" name="date_end" value="<?=$date_end?>" readonly />
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>                            
                    </div> 
                </div>                    
            </div> 
            
            <div class="row">
                <hr />
            </div>
            
            
            <div class="row col-12">
                <div class="col-3">
                    <div class="row">
                        <div class="form-group row col-12">
                            <label class="checkbox text col-12">
                                <input type="radio" name="if_output" id="if_output0" value="0" <?php if ($if_output == 0) echo "checked='checked'" ?> />
                                Todos los Registros                                            
                            </label>                                   
                            <label class="checkbox text col-12">
                                <input type="radio" name="if_output" id="if_output1" value="1" <?php if ($if_output == 1) echo "checked='checked'" ?> />
                                Entradas (<strong>RE</strong>)
                            </label>

                            <label class="checkbox text col-12">
                                <input type="radio" name="if_output" id="if_output2" value="2" <?php if ($if_output == 2) echo "checked='checked'" ?> />
                                Salidas (<strong>RS</strong>)                                          
                            </label>                             
                        </div>
                        
                        
                        <hr class="divider" />
                        <div class="form-group row">
                            <label class="col-form-label col-12">
                                Palabras Claves, separadas por coma (,):
                            </label> 
                        </div> 
                        
                        <div class="col-12">
                            <textarea id="keywords" class="form-control" name="keywords"><?=$keywords?></textarea>
                        </div>  
                        <hr class="divider" />  
                    </div>
                </div>

                        
                <div class="col-9">
                    <div class="form-horizontal">
                        <div class="form-group row">
                            <label class="col-form-label col-3">
                                    Organismos:
                                </label>                                     

                            <div class="col-md-9">
                                <select class="form-control" id="organismo" name="organismo" onchange="refreshp()">
                                    <option value="0">Seleccione ... </option>
                                    <?php 
                                    $obj_org= new Torganismo($clink);
                                    $result_org= $obj_org->listar();
                                    
                                    while ($row= $clink->fetch_array($result_org)) { ?>
                                    <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_organismo) echo "selected='selected'"?>><?=$row['nombre']?></option>
                                    <?php } ?>
                                </select>                                       
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-3">Lugar:</label>
                            <div class="col-md-9 col-sm-9">
                                <input type="text" id="lugar" name="lugar" class="form-control ui-autocomplete-input" autocomplete="yes" value="<?=$lugar?>" />
                            </div>                                    
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Internos:
                            </label>
                            <div class="col-10">
                                <select class="form-control" id="responsable" name="responsable">
                                    <option value="0">Seleccione ... </option>

                                    <?php
                                    $obj_user= new Tusuario($clink);
                                    $result= $obj_user->listar();
                                    while ($row= $clink->fetch_array($result)) {
                                        if ($row['nivel'] < _PLANIFICADOR) 
                                            continue;
                                    ?>
                                        <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_responsable) echo "selected='selected'"?>>
                                            <?php
                                            $name= stripslashes($row['nombre']);
                                            if (!empty($row['cargo'])) 
                                                $name.= ", {$row['cargo']}";
                                            echo $name;
                                            ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>   
                        
                        <div class="form-group row">
                            <label class="col-form-label col-5">
                                Externos (El nombre o el apellido contiene) <br/> (separado por comas):
                            </label>
                            <div class="col-7">
                                <input type="hidden" id="persona" name="persona" value="0" />
                                <input type="text" class="form-control" id="persona_keywords" name="persona_keywords" value="<?=$persona_keywords?>" />
                            </div>
                        </div>   
  
                        <div class="form-group row">
                            <label class="col-form-label col-6">
                                Números, solo números separados por coma (,):
                            </label> 
                        
                            <div class="col-6">
                                <input type="text" id="numero_keywords" class="form-control" name="numero_keywords" value="<?=$numero_keywords?>" />
                            </div>  
                        </div> 
                    </div>
                </div>    
                </div>
                
                <div class="btn-block btn-app">
                    <button type="button" class="btn btn-primary" onclick="refreshTab(0)">Filtrar</button>
                    <button type="button" class="btn btn-warning" onclick="form_filter(0)">Cerrar</button>
                </div>  
            </div>    
        </div>



