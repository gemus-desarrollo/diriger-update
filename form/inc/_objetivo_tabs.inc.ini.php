    <?php
    /**
     * Created by Visual Studio Code.
     * User: muste
     * Date: 24/02/15
     * Time: 19:17
     */

    $j_obji= 0;
    $i_obji= 0;
    $cant_obji= 0;
    $t_cant_obji= 0;
    ?>

    <script language="javascript">
        function set_cant_obji(id) {
            var nvalue= parseInt($('#cant_obji').val());

            if (parseInt($('#select_obji'+id).val()) > 0 && parseInt($('#init_obji'+id).val()) == 0) 
                ++nvalue;
            if (parseInt($('#select_obji'+id).val()) == 0 && parseInt($('#init_obji'+id).val()) > 0) 
                --nvalue;
            $('#cant_obji').val(nvalue);
        }
    </script>

    <legend>
        Ponderación del Impacto. Objetivos Estratégicos de las Direcciones o Procesos directamente subordinados
    </legend>
