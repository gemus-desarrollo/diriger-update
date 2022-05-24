<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once _ROOT_DIRIGER_DIR."inc.php";
$_SESSION['version']= _VERSION_DIRIGER;
?>
    
        <div id="div-ajax-panel" class="card card-primary ajax-panel" data-bind="draganddrop">
             <div class="card-header">
                 <div class="row win-drag">
                     <div class="panel-title ajax-title col-11 win-drag">CONFIGURACIÓN DE PAGINA</div>

                     <div class="col-1 pull-right">
                         <div class="close">
                            <a href= "javascript:HideContent('div-ajax-panel');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                             
                         </div>
                     </div> 
                 </div>              
             </div>

             <div class="card-body">              
                 <form class="form-horizontal" action='<?= $PHP_SELF ?>'  method=post>
                    <input type="hidden" name="signal" id="signal" value="prnt" />
                    <input type="hidden" name="url" id="url" value="<?= $url ?>" />
                    <input type="hidden" name="title" id="title" value="<?= $title ?>" />
                    <input type="hidden" name="param" id="param" value="<?= $param ?>" />

                    <legend class="legendpage" style="margin-top: 4px;">Fuentes</legend>
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 col-md-2">Fuente:</label>
                        <div class="col-sm-6 col-md-6">
                            <select id="font" name="font" class="form-control">
                                <option <?php if (strstr($font,"Arial") !== false) echo "selected='selected'" ?> value="Arial">Arial</option>
                                <option <?php if (strstr($font,"ArialNarrow") !== false) echo "selected='selected'" ?> value="ArialNarrow">Arial Narrow</option>
                                <option <?php if (strstr($font,"Courier") !== false) echo "selected='selected'" ?> value="Courier">Courier</option>
                                <option <?php if (strstr($font,"CourierNew") !== false) echo "selected='selected'" ?> value="CourierNew">Courier New</option>
                                <option <?php if (strstr($font,"Geneva") !== false) echo "selected='selected'" ?> value="Geneva">Geneva</option>
                                <option <?php if (strstr($font,"Georgia") !== false) echo "selected='selected'" ?> value="Georgia">Georgia</option>
                                <option <?php if (strstr($font,"Segoe UI") !== false) echo "selected='selected'" ?> value="Segoe UI">Segoe UI</option>
                                <option <?php if (strstr($font,"Tahoma") !== false) echo "selected='selected'" ?> value="Tahoma">Tahoma</option>
                                <option <?php if (strstr($font,"Terminal") !== false) echo "selected='selected'" ?> value="Terminal">Terminal</option>
                                <option <?php if (strstr($font,"TimesNewRoman") !== false) echo "selected='selected'" ?> value="TimesNewRoman">Times New Roman</option>
                                <option <?php if (strstr($font,"Verdana") !== false) echo "selected='selected'" ?> value="Verdana">Verdana</option>  
                            </select>                       
                        </div>
                    </div>    

                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 col-md-2">Tamaño:</label>
                        <div class="col-sm-3 col-md-3">
                            <select id="font-size" name="font-size" class="form-control">
                                <?php for ($i = 0.5; $i <= 18; $i += 0.5) { ?>
                                    <option <?php if ($i == $font_size) echo "selected='selected'" ?> value="<?= $i ?>"><?= $i ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-3 col-md-3">
                            <select id="font-um" name="font-um" class="form-control">
                                <option <?php if ($font_um == "px") echo "selected='selected'" ?> value="px">pixels</option>
                                <option <?php if ($font_um == "pt") echo "selected='selected'" ?> value="pt">points</option>
                                <option <?php if ($font_um == "mm") echo "selected='selected'" ?> value="mm">mm</option>
                                <option <?php if ($font_um == "pc") echo "selected='selected'" ?> value="pc">picas</option>
                            </select>                         
                        </div>
                    </div>  

                     <legend class="legendpage">Papel:</legend>
                     <div class="form-group row">
                         <label class="col-form-label col-sm-1 col-md-2">Tipo:</label>
                         <div class="col-sm-5 col-md-5">
                             <select id="size" name="size" class="form-control">
                                 <?php while (list($key, $array) = each($array_papel_size)) { ?>
                                     <option <?php if ($key == $size) echo "selected='selected'" ?> value="<?= $key ?>"><?= $key ?> <?="{$array[0]}cm x {$array[1]}cm"?></option>
                                 <?php } ?>
                             </select>                         
                         </div>

                         <label class="col-form-label col-sm-2 col-md-2">Orientación:</label>
                         <div class="col-sm-4 col-md-3">
                             <select id="orientation" name="orientation" class="form-control">
                                 <option <?php if ($orientation == 'portrait') echo "selected='selected'" ?> value="portrait">Vertical</option>
                                 <option <?php if ($orientation == 'landscape') echo "selected='selected'" ?> value="landscape">Horizontal</option>
                             </select>
                         </div>
                     </div>

                     <div class="form-group row">
                         <label class="col-form-label col-sm-1 col-md-1">Margenes</label>

                         <label class="col-form-label col-sm-2 col-md-1">Superior:</label>
                         <div class="col-sm-4 col-md-4">
                             <select id="margin-top" name="margin-top" class="form-control">
                                 <?php for ($i = 0; $i <= 5; $i += .5) { ?>
                                     <option <?php if ($i == $margin_top) echo "selected='selected'" ?> value="<?= $i ?>"><?= $i ?> cm</option>
                                 <?php } ?>
                             </select>
                         </div>

                         <label class="col-form-label col-sm-2 col-md-1">Izquierdo:</label>
                         <div class="col-sm-3 col-md-3">
                             <select id="margin-left" name="margin-left" class="form-control">
                                 <?php for ($i = 0; $i <= 5; $i += .5) { ?>
                                     <option <?php if ($i == $margin_left) echo "selected='selected'" ?> value="<?= $i ?>"><?= $i ?> cm</option>
                                 <?php } ?>
                             </select>                         
                         </div>
                     </div>			      

                    <div class="btn-block btn-app">
                        <button type="submit" class="btn btn-primary" title="Aceptar">Aceptar</button>
                        <button type="button" class="btn btn-warning" onclick="HideContent('div-ajax-panel')" title="Cerrar">Cerrar</button>
                    </div>
                </form>
            </div>
        </div> <!-- div-ajax-panel -->

        <br/><br/>
        <div class="container-fluid page mt-3">
            <div class="center">
                <div id="marca_print_diriger">
                    Generado por Sistema Informático para la Gestión Integrada <strong>Diriger versión <?= $_SESSION['version'] ?></strong>
                </div>                      
            </div>
        </div>
                                
    </body>
</html>
        
        
