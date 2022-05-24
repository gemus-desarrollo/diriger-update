/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function BootstrapSpinnerButton(id, min, max) {
    var btn= $('#'+id+' .btn');
    var input= $('#'+id+' input');
    var action;
    var i;
    
    btn.on('click', function() {
        if ($(this).attr('data-bind') == 'up') {
            i = parseInt(input.val(), 10) + 1;
            i= i > max ? max : i;
            input.val(i);
        } else {
            i = parseInt(input.val(), 10) - 1;
            i= i < min ? min : i;           
            input.val(i);
        }  
    });
    btn.mousedown(function() {
        if ($(this).attr('data-bind') == 'up')
            action= setInterval(function() {
            i = parseInt(input.val(), 10) + 1;
            i= i > max ? max : i;
            input.val(i);
            }, 100);
        else 
            action= setInterval(function() {
            i = parseInt(input.val(), 10) - 1;
            i= i < min ? min : i;           
            input.val(i);
            }, 100);                        
    });
    btn.mouseup(function() {
        clearInterval(action);
    });                
}
