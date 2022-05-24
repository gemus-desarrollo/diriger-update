<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    $j_objt= 0;
    $i_objt= 0;
    $cant_objt= 0;
    $t_cant_objt= 0;
    ?>

    <script language="javascript">
        var nvalue= 0;
        
        function set_cant_objt(id) {
            var nvalue= parseInt($('#cant_objt').val());

            if (parseInt($('#select_objt'+id).val()) > 0 && parseInt($('#init_objt'+id).val()) == 0) 
                ++nvalue;
            if (parseInt($('#select_objt'+id).val()) == 0 && parseInt($('#init_objt'+id).val()) > 0) 
                --nvalue;

            $('#cant_objt').val(nvalue);
        }
    </script>
